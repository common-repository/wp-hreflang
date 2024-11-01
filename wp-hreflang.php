<?php
/**
 * Plugin Name: WordPress Hreflang
 * Version: 1.0.3
 * Plugin URI:
 * Description: Manage hreflang tags on pages and posts on Multisite Wordpress
 * Author: Marc Donkers, EburyLabs
 * Author URI: https://labs.ebury.rocks/
 * Text Domain: wordpress-hreflang
 * Domain Path: /languages/
 * License: GPL v2 or later
 */

//include our constants and initial declarations
require_once(dirname(__FILE__)."/wp-hreflang-def.php");

//register our autoloader set in wp-hreflang-def
spl_autoload_register("hreflang_autoloader");

//load translation text domain
//load_plugin_textdomain("wordpress-hreflang", false, dirname(plugin_basename(__FILE__))."/languages/");

//get our class up and running
use EburyLabs\Hreflang;

//start the plugin
new Hreflang;
