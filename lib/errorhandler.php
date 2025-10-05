<?php
/**
 * MoeFrame Error Handler
 * Handle PHP errors and exceptions with configurable trace options
 */


/**
 * Get environment configuration value
 * @param string $key Configuration key
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value
 */
function mf_env_get($envname, $default = false) {
    $envDotJson = dirname(dirname(__FILE__))."/env.json";
    $envDotJson = json_decode(file_get_contents($envDotJson), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = "env.json Error: ".json_last_error_msg();
        try {
            $MoeApps = new MoeApps();
            $MoeApps->abort(500, '', $errorMsg);
        } catch (Exception $e) {
            // If abort fails, output the error directly
            header('HTTP/1.1 500 Internal Server Error');
            echo "<pre>" . htmlspecialchars($errorMsg) . "</pre>";
        }
    }
    if ($envDotJson && isset($envDotJson[$envname])) {
        $value = $envDotJson[$envname];
        return $value;
    }
    return $default;
}

/**
 * Check if error should be handled by our error handler
 * @param int $errno Error level
 * @return bool Whether to handle this error
 */
function mf_should_handle_error($errno) {
    // Don't handle suppressed errors (@ operator)
    if (error_reporting() === 0) {
        return false;
    }
    
    // Only handle actual errors, not normal HTTP status codes
    $error_levels = array(
        E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, 
        E_COMPILE_ERROR, E_COMPILE_WARNING, E_USER_ERROR,
        E_RECOVERABLE_ERROR
    );
    
    return in_array($errno, $error_levels);
}

/**
 * Set PHP Error Handler
 * @return bool
 */
function mf_error_handler($errno, $errstr, $errfile, $errline) {
    // Check if this error should be handled
    if (!mf_should_handle_error($errno)) {
        return false; // Let PHP's default error handler deal with it
    }
    
    // Check if MoeApps class exists and can be instantiated
    if (!class_exists('MoeApps')) {
        // If MoeApps is not available, use basic error handling
        header('HTTP/1.1 500 Internal Server Error');
        echo "Fatal Error: MoeApps class not available\n";
        echo "Error: $errstr in $errfile on line $errline\n";
        exit(-1);
    }
    
    try {
        $MoeApps = new MoeApps();
    } catch (Exception $e) {
        // If we can't instantiate MoeApps, fall back to basic error handling
        header('HTTP/1.1 500 Internal Server Error');
        echo "Fatal Error: Cannot instantiate MoeApps\n";
        echo "Original Error: $errstr in $errfile on line $errline\n";
        echo "Instantiation Error: " . $e->getMessage() . "\n";
        exit(-1);
    }
    
    // Get environment configuration using mf_env_get
    $mf_trace = mf_env_get('MF_TRACE', false);
    $mf_debug = mf_env_get('MF_DEBUG', false);
    
    // Convert string values to boolean if needed
    if (is_string($mf_trace)) {
        $mf_trace = ($mf_trace === 'true' || $mf_trace === '1' || $mf_trace === 'on');
    }
    if (is_string($mf_debug)) {
        $mf_debug = ($mf_debug === 'true' || $mf_debug === '1' || $mf_debug === 'on');
    }
    
    // Format error message
    $error_type = '';
    switch ($errno) {
        case E_ERROR: $error_type = 'ERROR'; break;
        case E_PARSE: $error_type = 'PARSE'; break;
        case E_CORE_ERROR: $error_type = 'CORE_ERROR'; break;
        case E_CORE_WARNING: $error_type = 'CORE_WARNING'; break;
        case E_COMPILE_ERROR: $error_type = 'COMPILE_ERROR'; break;
        case E_COMPILE_WARNING: $error_type = 'COMPILE_WARNING'; break;
        case E_USER_ERROR: $error_type = 'USER_ERROR'; break;
        case E_RECOVERABLE_ERROR: $error_type = 'RECOVERABLE_ERROR'; break;
        default: $error_type = 'UNKNOWN';
    }
    
    $error_message = "$error_type: $errstr in $errfile on line $errline";
    
    // If both trace and debug are off, show minimal error message
    if (!$mf_trace && !$mf_debug) {
        try {
            $MoeApps->abort(500, '', '');
        } catch (Exception $e) {
            // If abort fails, use basic error display
            header('HTTP/1.1 500 Internal Server Error');
            echo "Internal Server Error";
        }
        return true;
    }
    
    // Build detailed error information based on debug level
    $detailed_info = $error_message;
    
    // If debug is on, show detailed stack trace
    if ($mf_debug) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $detailed_info .= "\n\nStack trace:\n";
        
        // Remove the error handler itself from the trace
        array_shift($backtrace);
        
        // Format each trace entry
        foreach ($backtrace as $i => $trace) {
            $file = isset($trace['file']) ? $trace['file'] : 'unknown file';
            $line = isset($trace['line']) ? $trace['line'] : 'unknown line';
            $function = isset($trace['function']) ? $trace['function'] : 'unknown function';
            $class = isset($trace['class']) ? $trace['class'] : '';
            $type = isset($trace['type']) ? $trace['type'] : '';
            
            $args_str = '';
            if (isset($trace['args']) && $mf_debug) {
                $args = array();
                foreach ($trace['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . substr($arg, 0, 100) . (strlen($arg) > 100 ? '...' : '') . "'";
                    } else if (is_numeric($arg)) {
                        $args[] = $arg;
                    } else if (is_bool($arg)) {
                        $args[] = $arg ? 'true' : 'false';
                    } else if (is_null($arg)) {
                        $args[] = 'null';
                    } else if (is_array($arg)) {
                        $args[] = 'Array(' . count($arg) . ')';
                    } else if (is_object($arg)) {
                        $args[] = get_class($arg) . '(object)';
                    } else {
                        $args[] = gettype($arg);
                    }
                }
                $args_str = implode(', ', $args);
            }
            
            $detailed_info .= "$i. $class$type$function($args_str) called at [$file:$line]\n";
        }
    } else if ($mf_trace) {
        // If only trace is on, show minimal trace information
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $detailed_info .= "\n\nStack trace:\n";
        
        // Remove the error handler itself from the trace
        array_shift($backtrace);
        
        // Format each trace entry with minimal information
        foreach ($backtrace as $i => $trace) {
            $file = isset($trace['file']) ? $trace['file'] : 'unknown file';
            $line = isset($trace['line']) ? $trace['line'] : 'unknown line';
            $function = isset($trace['function']) ? $trace['function'] : 'unknown function';
            $class = isset($trace['class']) ? $trace['class'] : '';
            $type = isset($trace['type']) ? $trace['type'] : '';
            
            $detailed_info .= "$i. $class$type$function() called at [$file:$line]\n";
        }
    }
    
    // Abort with 500 error and detailed information
    try {
        $MoeApps->abort(500, '', $detailed_info);
    } catch (Exception $e) {
        // If abort fails, output the error directly
        header('HTTP/1.1 500 Internal Server Error');
        echo "<pre>" . htmlspecialchars($detailed_info) . "</pre>";
    }
    
    return true;
}

/**
 * Set PHP Exception Handler
 * @return void
 */
function mf_exception_handler($exception) {
    // Check if MoeApps class exists and can be instantiated
    if (!class_exists('MoeApps')) {
        // If MoeApps is not available, use basic error handling
        header('HTTP/1.1 500 Internal Server Error');
        echo "Fatal Error: MoeApps class not available\n";
        echo "Exception: " . $exception->getMessage() . "\n";
        exit(-1);
    }
    
    try {
        $MoeApps = new MoeApps();
    } catch (Exception $e) {
        // If we can't instantiate MoeApps, fall back to basic error handling
        header('HTTP/1.1 500 Internal Server Error');
        echo "Fatal Error: Cannot instantiate MoeApps\n";
        echo "Original Exception: " . $exception->getMessage() . "\n";
        echo "Instantiation Error: " . $e->getMessage() . "\n";
        exit(-1);
    }
    
    // Get environment configuration using mf_env_get
    $mf_trace = mf_env_get('MF_TRACE', false);
    $mf_debug = mf_env_get('MF_DEBUG', false);
    
    // Convert string values to boolean if needed
    if (is_string($mf_trace)) {
        $mf_trace = ($mf_trace === 'true' || $mf_trace === '1' || $mf_trace === 'on');
    }
    if (is_string($mf_debug)) {
        $mf_debug = ($mf_debug === 'true' || $mf_debug === '1' || $mf_debug === 'on');
    }
    
    // Build exception message
    $exception_message = get_class($exception) . ": " . $exception->getMessage() . " in " . 
                         $exception->getFile() . " on line " . $exception->getLine();
    
    // If both trace and debug are off, show minimal error message
    if (!$mf_trace && !$mf_debug) {
        try {
            $MoeApps->abort(500, '', '');
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo "Internal Server Error";
        }
        return;
    }
    
    $detailed_info = $exception_message;
    
    // If debug is on, show detailed stack trace
    if ($mf_debug) {
        $detailed_info .= "\n\nStack trace:\n";
        
        // Format each trace entry
        $trace = $exception->getTrace();
        foreach ($trace as $i => $trace_entry) {
            $file = isset($trace_entry['file']) ? $trace_entry['file'] : 'unknown file';
            $line = isset($trace_entry['line']) ? $trace_entry['line'] : 'unknown line';
            $function = isset($trace_entry['function']) ? $trace_entry['function'] : 'unknown function';
            $class = isset($trace_entry['class']) ? $trace_entry['class'] : '';
            $type = isset($trace_entry['type']) ? $trace_entry['type'] : '';
            
            $args_str = '';
            if (isset($trace_entry['args'])) {
                $args = array();
                foreach ($trace_entry['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . substr($arg, 0, 100) . (strlen($arg) > 100 ? '...' : '') . "'";
                    } else if (is_numeric($arg)) {
                        $args[] = $arg;
                    } else if (is_bool($arg)) {
                        $args[] = $arg ? 'true' : 'false';
                    } else if (is_null($arg)) {
                        $args[] = 'null';
                    } else if (is_array($arg)) {
                        $args[] = 'Array(' . count($arg) . ')';
                    } else if (is_object($arg)) {
                        $args[] = get_class($arg) . '(object)';
                    } else {
                        $args[] = gettype($arg);
                    }
                }
                $args_str = implode(', ', $args);
            }
            
            $detailed_info .= "$i. $class$type$function($args_str) called at [$file:$line]\n";
        }
    } else if ($mf_trace) {
        // If only trace is on, show minimal trace information
        $detailed_info .= "\n\nStack trace:\n";
        
        // Format each trace entry with minimal information
        $trace = $exception->getTrace();
        foreach ($trace as $i => $trace_entry) {
            $file = isset($trace_entry['file']) ? $trace_entry['file'] : 'unknown file';
            $line = isset($trace_entry['line']) ? $trace_entry['line'] : 'unknown line';
            $function = isset($trace_entry['function']) ? $trace_entry['function'] : 'unknown function';
            $class = isset($trace_entry['class']) ? $trace_entry['class'] : '';
            $type = isset($trace_entry['type']) ? $trace_entry['type'] : '';
            
            $detailed_info .= "$i. $class$type$function() called at [$file:$line]\n";
        }
    }
    
    // Abort with 500 error and detailed exception information
    try {
        $MoeApps->abort(500, '', $detailed_info);
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo "<pre>" . htmlspecialchars($detailed_info) . "</pre>";
    }
}

/**
 * Set PHP Shutdown Function
 * @return void
 */
function mf_shutdown_function() {
    // Check if there was a fatal error
    $error = error_get_last();
    if ($error !== null && mf_should_handle_error($error['type'])) {
        // Manually call error handler for fatal errors
        mf_error_handler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

// Register error handlers
set_error_handler('mf_error_handler');
set_exception_handler('mf_exception_handler');
register_shutdown_function('mf_shutdown_function');
?>