<?php
namespace EburyLabs;

/**
 * Class Hreflang
 */
class Hreflang
{
    
    /**
     * @var string $plugin_name
     */
    public static $plugin_name = "Hreflang";
    
    /**
     * @var array $general_settings
     */
    public static $general_settings;
    
    /**
     * @var string $general_settings_key
     */
    public static $general_settings_key = 'hreflang_general_settings';
    
    /**
     * @var array $adv_settings
     */
    public static $adv_settings;
    
    /**
     * @var string $adv_settings_key
     */
    public static $adv_settings_key = 'hreflang_mapping_settings';
    
    /**
     * @var string $plugin_options_key
     */
    public static $plugin_options_key = 'wp-hreflang';
    
    /**
     * @var array $plugin_settings_tabs
     */
    public static $plugin_settings_tabs = array();
    
    /**
     * @var array $sites
     */
    public static $sites;
    
    /**
     * Constuctor.
     */
    public function __construct()
    {
        //populate the sites variable with all sites available
        \EburyLabs\Hreflang::$sites = wp_get_sites();

        //check if this class has been initiated from wordpress or directly
        $this->checkDirectCall();
        
        //initialize and setup the plugin
        new Hreflang\InitPlugin;
        
        //setup Ajax endpoints
        new Hreflang\Ajax;
        
        //include our metabox in posts and pages
        new Hreflang\PostMetabox;
        
        //include our head meta tag renderer
        new Hreflang\MetaHeader;
        
        //load our assets
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array(&$this, 'loadAssets'));
        }
    }
    
    /**
     * loadAssets
     */
    public function loadAssets()
    {
        wp_enqueue_script('hreflang', WPHREFLANG_URL.'assets/hreflang.js');
        wp_enqueue_style('hreflang', WPHREFLANG_URL.'assets/hreflang.css');
    }
    
    /**
     * checkDirectCall
     */
    private function checkDirectCall()
    {
        //check if a Wordpress related file/function is calling this class,
        //raise a 403 if called directly from outside Wordpress
        if (!function_exists("add_filter")) {
            header("Status: 403 Forbidden");
            header("HTTP/1.1 403 Forbidden");
            die();
        }
    }
}
