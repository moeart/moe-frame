<?php
/**
 * Framework-level global middleware dispatcher.
 *
 * Middleware files are loaded from ../middleware/*.php by public/index.php.
 * A middleware file returns either one definition or a list of definitions:
 *
 * return [
 *     'map' => ['routes' => ['#^/api/secure(?:/.*)?$#']],
 *     'before' => function (&$ctx) { ... },
 *     'after' => function (&$ctx) { ... },
 * ];
 *
 * $ctx contains reqheaders, reqbody, cookie, respbody, respheaders,
 * set_cookies, status and stop. Set stop=true in before to short-circuit
 * the controller and use respbody as the response body.
 */
class MoeMiddlewareResponseComplete extends Exception {}

class MoeGlobalMiddleware {

    private static $definitions = [];
    private static $capturing = false;

    /**
     * Load every PHP middleware definition under one directory.
     */
    public static function loadDirectory($directory) {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');
        if ($files === false) {
            return;
        }
        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $definition = require $file;
            self::registerDefinition($definition, basename($file));
        }
    }

    /**
     * Register one definition or a numerically indexed definition list.
     */
    private static function registerDefinition($definition, $source) {
        if (!is_array($definition)) {
            return;
        }

        if (array_key_exists('map', $definition) || array_key_exists('routes', $definition)) {
            $definition['_source'] = $source;
            self::$definitions[] = $definition;
            return;
        }

        foreach ($definition as $item) {
            if (is_array($item)) {
                $item['_source'] = $source;
                self::$definitions[] = $item;
            }
        }
    }

    /**
     * True only while Router has opened a response capture buffer.
     */
    public static function isCapturing() {
        return self::$capturing;
    }

    /**
     * Replace exit/die used by MoeApps response helpers while capturing.
     */
    public static function completeResponse() {
        if (self::$capturing) {
            throw new MoeMiddlewareResponseComplete();
        }
    }

    /**
     * Invoke a controller through all matching global middleware definitions.
     */
    public static function dispatch($controller, $registeredRoute) {
        $context = self::newContext($registeredRoute);
        $matched = self::matchingDefinitions($context['path']);

        if (count($matched) === 0) {
            return call_user_func($controller);
        }

        self::$capturing = true;
        $body = '';
        try {
            self::runPhase($matched, 'before', $context);

            ob_start();
            if (empty($context['stop'])) {
                call_user_func($controller);
            }
            $body = ob_get_clean();
        } catch (MoeMiddlewareResponseComplete $exception) {
            if (ob_get_level() > 0) {
                $body = ob_get_clean();
            }
        } finally {
            self::$capturing = false;
        }

        if (empty($context['stop'])) {
            $context['respbody'] = $body;
        } elseif ($context['respbody'] === null) {
            $context['respbody'] = '';
        }

        self::runPhase(array_reverse($matched), 'after', $context);
        self::applyResponse($context);
        print (string)$context['respbody'];
    }

    /**
     * Build a stable middleware context from the current HTTP request.
     */
    private static function newContext($registeredRoute) {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $path = parse_url($requestUri, PHP_URL_PATH);
        if ($path === null || $path === false || $path === '') {
            $path = '/';
        }

        return [
            'route' => $registeredRoute,
            'path' => $path,
            'uri' => $requestUri,
            'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET',
            'reqheaders' => self::requestHeaders(),
            'reqbody' => file_get_contents('php://input'),
            'cookie' => isset($_COOKIE) ? $_COOKIE : [],
            'respbody' => null,
            'respheaders' => [],
            'set_cookies' => [],
            'status' => null,
            'stop' => false,
        ];
    }

    /**
     * Match definitions using their map.routes (or legacy top-level routes) regex list.
     */
    private static function matchingDefinitions($path) {
        $matched = [];
        foreach (self::$definitions as $definition) {
            $map = isset($definition['map']) && is_array($definition['map']) ? $definition['map'] : $definition;
            $routes = isset($map['routes']) ? $map['routes'] : [];
            if (!is_array($routes)) {
                $routes = [$routes];
            }

            foreach ($routes as $regex) {
                if (!is_string($regex) || $regex === '') {
                    continue;
                }
                if (@preg_match($regex, $path) === 1) {
                    $matched[] = $definition;
                    break;
                }
            }
        }
        return $matched;
    }

    /**
     * Execute a phase in declaration order; after phase callers reverse the stack.
     */
    private static function runPhase($definitions, $phase, &$context) {
        foreach ($definitions as $definition) {
            if (!isset($definition[$phase]) || !is_callable($definition[$phase])) {
                continue;
            }
            $result = call_user_func_array($definition[$phase], [&$context]);
            if (is_array($result)) {
                $context = $result;
            }
        }
    }

    /**
     * Apply status, response headers and Set-Cookie headers after after-middleware runs.
     */
    private static function applyResponse($context) {
        if ($context['status'] !== null) {
            http_response_code((int)$context['status']);
        }

        if (is_array($context['respheaders'])) {
            foreach ($context['respheaders'] as $name => $values) {
                if (!is_string($name) || $name === '') {
                    continue;
                }
                header_remove($name);
                if (!is_array($values)) {
                    $values = [$values];
                }
                foreach ($values as $value) {
                    header($name . ': ' . $value, false);
                }
            }
        }

        if (is_array($context['set_cookies'])) {
            foreach ($context['set_cookies'] as $cookie) {
                self::sendCookie($cookie);
            }
        }
    }

    /**
     * Emit one Set-Cookie header from a portable array representation.
     */
    private static function sendCookie($cookie) {
        if (!is_array($cookie) || empty($cookie['name'])) {
            return;
        }

        $value = array_key_exists('value', $cookie) ? $cookie['value'] : '';
        $line = rawurlencode($cookie['name']) . '=' . rawurlencode($value);
        if (!empty($cookie['expires'])) {
            $line .= '; Expires=' . gmdate('D, d M Y H:i:s T', (int)$cookie['expires']);
        }
        if (!empty($cookie['max_age'])) {
            $line .= '; Max-Age=' . (int)$cookie['max_age'];
        }
        if (!empty($cookie['path'])) {
            $line .= '; Path=' . $cookie['path'];
        }
        if (!empty($cookie['domain'])) {
            $line .= '; Domain=' . $cookie['domain'];
        }
        if (!empty($cookie['secure'])) {
            $line .= '; Secure';
        }
        if (!empty($cookie['httponly'])) {
            $line .= '; HttpOnly';
        }
        if (!empty($cookie['samesite'])) {
            $line .= '; SameSite=' . $cookie['samesite'];
        }
        header('Set-Cookie: ' . $line, false);
    }

    /**
     * Read request headers in both web-server and CLI test environments.
     */
    private static function requestHeaders() {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                return $headers;
            }
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') !== 0) {
                continue;
            }
            $name = str_replace('_', '-', strtolower(substr($key, 5)));
            $name = implode('-', array_map('ucfirst', explode('-', $name)));
            $headers[$name] = $value;
        }
        return $headers;
    }
}
?>
