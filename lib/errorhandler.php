<?php
/**
 * MoeFrame Error Handler
 * Handle PHP errors and exceptions with configurable trace options
 */

/**
 * Set PHP Error Handler
 * @return void
 */
function mf_error_handler($errno, $errstr, $errfile, $errline) {
    // Check if MoeApps class exists
    if (!class_exists('MoeApps')) {
        // If MoeApps is not available, use basic error handling
        header('HTTP/1.1 500 Internal Server Error');
        echo "Fatal Error: MoeApps class not available\n";
        exit(-1);
    }
    
    $MoeApps = new MoeApps();
    
    // Get environment configuration with default values
    $mf_trace = false;
    $mf_debug = false;
    
    // Try to get configuration values if E() function is available
    if (function_exists('E')) {
        $config_trace = E('MF_TRACE');
        $config_debug = E('MF_DEBUG');
        
        // Only override defaults if values are explicitly set
        if ($config_trace !== null) {
            $mf_trace = $config_trace;
        }
        if ($config_debug !== null) {
            $mf_debug = $config_debug;
        }
    }
    
    // If both trace and debug are off, show minimal error message
    if (!$mf_trace && !$mf_debug) {
        $MoeApps->abort(500, '', '');
        return true;
    }
    
    // Format error message with trace
    $error_type = '';
    switch ($errno) {
        case E_ERROR: $error_type = 'ERROR'; break;
        case E_WARNING: $error_type = 'WARNING'; break;
        case E_PARSE: $error_type = 'PARSE'; break;
        case E_NOTICE: $error_type = 'NOTICE'; break;
        case E_CORE_ERROR: $error_type = 'CORE_ERROR'; break;
        case E_CORE_WARNING: $error_type = 'CORE_WARNING'; break;
        case E_COMPILE_ERROR: $error_type = 'COMPILE_ERROR'; break;
        case E_COMPILE_WARNING: $error_type = 'COMPILE_WARNING'; break;
        case E_USER_ERROR: $error_type = 'USER_ERROR'; break;
        case E_USER_WARNING: $error_type = 'USER_WARNING'; break;
        case E_USER_NOTICE: $error_type = 'USER_NOTICE'; break;
        case E_STRICT: $error_type = 'STRICT'; break;
        case E_RECOVERABLE_ERROR: $error_type = 'RECOVERABLE_ERROR'; break;
        case E_DEPRECATED: $error_type = 'DEPRECATED'; break;
        case E_USER_DEPRECATED: $error_type = 'USER_DEPRECATED'; break;
        default: $error_type = 'UNKNOWN';
    }
    
    // Build error message with trace
    $trace_info = "$error_type: $errstr in $errfile on line $errline";
    
    // If debug is on, show detailed stack trace
    if ($mf_debug) {
        $backtrace = debug_backtrace();
        $trace_info .= "\n\nStack trace:\n";
        
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
            if (isset($trace['args'])) {
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
            
            $trace_info .= "$i. $class$type$function($args_str) called at [$file:$line]\n";
        }
    } else if ($mf_trace) {
        // If only trace is on, show minimal trace information
        $backtrace = debug_backtrace();
        $trace_info .= "\n\nStack trace:\n";
        
        // Remove the error handler itself from the trace
        array_shift($backtrace);
        
        // Format each trace entry with minimal information
        foreach ($backtrace as $i => $trace) {
            $file = isset($trace['file']) ? $trace['file'] : 'unknown file';
            $line = isset($trace['line']) ? $trace['line'] : 'unknown line';
            $function = isset($trace['function']) ? $trace['function'] : 'unknown function';
            $class = isset($trace['class']) ? $trace['class'] : '';
            $type = isset($trace['type']) ? $trace['type'] : '';
            
            $trace_info .= "$i. $class$type$function() called at [$file:$line]\n";
        }
    }
    
    // Abort with 500 error and detailed trace
    $MoeApps->abort(500, '', $trace_info);
    
    // Return true to prevent PHP's default error handler from running
    return true;
}

/**
 * Set PHP Exception Handler
 * @return void
 */
function mf_exception_handler($exception) {
    // Check if MoeApps class exists
    if (!class_exists('MoeApps')) {
        // If MoeApps is not available, use basic error handling
        header('HTTP/1.1 500 Internal Server Error');
        echo "Fatal Error: MoeApps class not available\n";
        exit(-1);
    }
    
    $MoeApps = new MoeApps();
    
    // Get environment configuration with default values
    $mf_trace = false;
    $mf_debug = false;
    
    // Try to get configuration values if E() function is available
    if (function_exists('E')) {
        $config_trace = E('MF_TRACE');
        $config_debug = E('MF_DEBUG');
        
        // Only override defaults if values are explicitly set
        if ($config_trace !== null) {
            $mf_trace = $config_trace;
        }
        if ($config_debug !== null) {
            $mf_debug = $config_debug;
        }
    }
    
    // If both trace and debug are off, show minimal error message
    if (!$mf_trace && !$mf_debug) {
        $MoeApps->abort(500, '', '');
        return;
    }
    
    // Build exception message with trace
    $exception_message = get_class($exception) . ": " . $exception->getMessage() . " in " . 
                         $exception->getFile() . " on line " . $exception->getLine();
    
    // If debug is on, show detailed stack trace
    if ($mf_debug) {
        $exception_message .= "\n\nStack trace:\n";
        
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
            
            $exception_message .= "$i. $class$type$function($args_str) called at [$file:$line]\n";
        }
    } else if ($mf_trace) {
        // If only trace is on, show minimal trace information
        $exception_message .= "\n\nStack trace:\n";
        
        // Format each trace entry with minimal information
        $trace = $exception->getTrace();
        foreach ($trace as $i => $trace_entry) {
            $file = isset($trace_entry['file']) ? $trace_entry['file'] : 'unknown file';
            $line = isset($trace_entry['line']) ? $trace_entry['line'] : 'unknown line';
            $function = isset($trace_entry['function']) ? $trace_entry['function'] : 'unknown function';
            $class = isset($trace_entry['class']) ? $trace_entry['class'] : '';
            $type = isset($trace_entry['type']) ? $trace_entry['type'] : '';
            
            $exception_message .= "$i. $class$type$function() called at [$file:$line]\n";
        }
    }
    
    // Abort with 500 error and detailed exception trace
    $MoeApps->abort(500, '', $exception_message);
}

/**
 * Set PHP Shutdown Function
 * @return void
 */
function mf_shutdown_function() {
    // Check if there was a fatal error
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING))) {
        // Manually call error handler for fatal errors
        mf_error_handler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}
?>