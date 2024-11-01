<?php
/**
 * wp-hreflang-def
 *
 * Main file to define most of the constants
 */

//set our constants
if (!defined("WPHREFLANG_VERSION")) {
    define("WPHREFLANG_VERSION", "1.0.3");
}

//store the complete OS PATH including the filename, to point to this file.
if (!defined("WPHREFLANG_FILE")) {
    define("WPHREFLANG_FILE", str_replace("-def", "", __FILE__));
}

//absolute path OS PATH to our plugin folder
if (!defined("WPHREFLANG_PATH")) {
    define("WPHREFLANG_PATH", plugin_dir_path(WPHREFLANG_FILE));
}
//absolute path URL to our plugin folder
if (!defined("WPHREFLANG_URL")) {
    define("WPHREFLANG_URL", plugins_url()."/".dirname(plugin_basename(__FILE__))."/");
}

/**
 * hreflang_autoloader will autoload the classes which are being used
 *
 * @param $class string name of class to be loaded
 */
function hreflang_autoloader($class)
{
    //namespace allowed to be autoloaded
    $namespaces = [
        "EburyLabs\Hreflang",
        "EburyLabs\Hreflang\InitPlugin",
        "EburyLabs\Hreflang\GeneralSettings",
        "EburyLabs\Hreflang\MappingSettings",
        "EburyLabs\Hreflang\AdminMenu",
        "EburyLabs\Hreflang\PostMetabox",
        "EburyLabs\Hreflang\MetaHeader",
        "EburyLabs\Hreflang\Ajax"
    ];
    
    //fix the path name
    $file = str_replace("\\", "/", WPHREFLANG_PATH . "lib/" .$class.".class.php");
        
    //check classname against namespaces array
    if (in_array($class, $namespaces)) {
        
        //check if the class.php file actually exists
        if (file_exists($file)) {
            require_once($file);
        } else {
            //perhaps we want to warn here?
        }
    }
}
