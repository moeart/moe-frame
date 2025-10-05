<?php
/**
 * Bootstrapping File for phpseclib
 *
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

if (extension_loaded('mbstring')) {
    // 2 - MB_OVERLOAD_STRING
    // mbstring.func_overload is deprecated in php 7.2 and removed in php 8.0.
    if (version_compare(PHP_VERSION, '8.0.0') < 0 && ini_get('mbstring.func_overload') & 2) {
        throw new \UnexpectedValueException(
            'Overloading of string functions using mbstring.func_overload ' .
            'is not supported by phpseclib.'
        );
    }
}
foreach (glob(dirname(__FILE__)."/Crypt/*.php") as $filename) {
    require_once $filename;
}
foreach (glob(dirname(__FILE__)."/Math/*.php") as $filename) {
    require_once $filename;
}
foreach (glob(dirname(__FILE__)."/File/*.php") as $filename) {
    require_once $filename;
}
foreach (glob(dirname(__FILE__)."/Net/*.php") as $filename) {
    require_once $filename;
}
foreach (glob(dirname(__FILE__)."/Net/SFTP/*.php") as $filename) {
    require_once $filename;
}