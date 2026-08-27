<?php
/**
 * Ensure a framework-owned storage directory exists before it is returned to
 * application code. Concurrent PHP-CGI/FPM requests may race on first use;
 * the second is_dir() check accepts a directory created by another process.
 *
 * @param string $directory
 * @return string
 */
function MoeEnsureStorageDirectory($directory) {
    $directory = (string) $directory;
    if ($directory === '') {
        throw new RuntimeException('MoeFrame storage directory path cannot be empty.');
    }
    if (is_dir($directory)) {
        return $directory;
    }
    if (!@mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create MoeFrame storage directory: ' . $directory);
    }
    return $directory;
}

/**
 * Environment Get
 * @param $envname: Environment Lable
 */
function E ( $envname ) {

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { // Redefine slashes for Windows or Xnix
        $SLASHES = "\\";
    } else {
        $SLASHES = "/";
    }

    switch ( $envname ) { // Environment List
        
        case 'MOEFRAME_ROOT': return dirname(dirname(__FILE__));
        case 'MOEFRAME_VENDOR': return dirname(dirname(__FILE__)).$SLASHES."vendor";
        case 'MOEFRAME_STORAGE':
            return MoeEnsureStorageDirectory(dirname(dirname(__FILE__)).$SLASHES."storage");
        case 'MOEFRAME_TMP_ROOT':
            return MoeEnsureStorageDirectory(dirname(dirname(__FILE__)).$SLASHES."storage".$SLASHES."tmp");
        default: 
            // Read from env.json file
            $envDotJson = dirname(dirname(__FILE__))."/env.json";
            $envDotJson = json_decode(file_get_contents($envDotJson), true);
            if ($envDotJson && isset($envDotJson[$envname])) {
                $value = $envDotJson[$envname];
                // Process placeholder in string value, e.g. E('MOEFRAME_XXX')
                if (is_string($value)) {
                    $value = preg_replace_callback('/E\(\s*[\'"]([A-Z_]+)[\'"]\s*\)/', function($matches) {
                        return E($matches[1]);
                    }, $value);
                }
                return $value;
            }
            return null;
        
    }

}

/**
 * Import SDK from Vendors
 * @param $sdkName: ${VendorName}/${SdkName}, E.g: moeart/demoSdk
 */
function ImportSdk ( $sdkName ) {

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { // Redefine slashes for Windows or Xnix
        $SLASHES = "\\";
    } else {
        $SLASHES = "/";
    }

    $sdkPath = dirname(dirname(__FILE__)).$SLASHES."vendor".$SLASHES.str_replace('/', DIRECTORY_SEPARATOR, $sdkName).$SLASHES;
    if (file_exists($sdkPath."autoload.php")) {
        require_once $sdkPath."autoload.php";
        return ;
    }
    if (file_exists($sdkPath."bootstrap.php")) {
        require_once $sdkPath."bootstrap.php";
        return ;
    }
}
?>