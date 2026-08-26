<?php
/**
 * Example global middleware definition.
 *
 * The map allows only the demonstration route. It intentionally does not
 * match /hello or / so existing ExampleApp routes remain unchanged.
 */
return [
    'map' => [
        'routes' => [
            '#^/middleware/crypto$#',
        ],
    ],

    'before' => function (&$ctx) {
        // reqheaders, reqbody and cookie are available before the controller.
        $requestHeaderCount = count($ctx['reqheaders']);
        $requestBodyLength = strlen($ctx['reqbody']);
        $requestCookieCount = count($ctx['cookie']);

        $ctx['respheaders']['X-Moe-Crypto-Middleware'] = 'before';
        $ctx['respheaders']['X-Moe-Request-Headers'] = (string)$requestHeaderCount;
        $ctx['respheaders']['X-Moe-Request-Body-Length'] = (string)$requestBodyLength;
        $ctx['respheaders']['X-Moe-Request-Cookies'] = (string)$requestCookieCount;

        // Demonstrate a direct middleware response without running the controller.
        if (isset($ctx['reqheaders']['X-Moe-Short-Circuit']) && $ctx['reqheaders']['X-Moe-Short-Circuit'] === '1') {
            $ctx['status'] = 202;
            $ctx['respheaders']['Content-Type'] = 'text/plain; charset=utf-8';
            $ctx['respbody'] = 'crypto middleware short-circuit response';
            $ctx['stop'] = true;
        }

        // The framework emits this as a Set-Cookie response header.
        $ctx['set_cookies'][] = [
            'name' => 'moe_crypto_probe',
            'value' => substr(hash('sha256', $ctx['method'] . '|' . $ctx['path'] . '|' . $ctx['reqbody']), 0, 16),
            'path' => '/',
            'max_age' => 300,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    },

    'after' => function (&$ctx) {
        // Existing controller output is available and may be replaced or extended.
        $ctx['respheaders']['X-Moe-Crypto-Middleware'] = 'after';
        $ctx['respbody'] .= "\n<!-- global middleware: crypto matched -->";
    },
];
?>
