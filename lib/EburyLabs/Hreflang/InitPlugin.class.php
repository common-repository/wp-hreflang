<?php
namespace EburyLabs\Hreflang;

/**
 *
 */
class InitPlugin
{
    
    /**
     *
     */
    public function __construct()
    {
        //start the register/deregister plugin hook
        $this->pluginHooks();
        
        //init settings, grab data for plugin from database
        add_action("init", array(&$this, "loadSettings"));
        
        //setup General settings tab
        new GeneralSettings;
        
        //setup Category mapping tab
        new MappingSettings;
        
        //including the plugin admin in the left menu pane of wp-admin
        new AdminMenu;
    }
    
    /**
     *
     */
    //initiate the plugin activate and deactivate hooks
    private function pluginHooks()
    {
        $this->registerPlugin(WPHREFLANG_FILE);
        $this->deregisterPlugin(WPHREFLANG_FILE);
    }
    
    /**
     *
     */
    public function registerPlugin($caller)
    {
        /**
         *  Wordpress function      : register_activation_hook
         *  Codex                   : https://codex.wordpress.org/Function_Reference/register_activation_hook
         */
        register_activation_hook($caller, array(&$this,'onPluginActivated'));
    }
    
    /**
     *
     */
    public function deregisterPlugin($caller)
    {
        /**
         *  Wordpress function      : register_deactivation_hook
         *  Codex                   : https://codex.wordpress.org/Function_Reference/register_deactivation_hook
         */
        register_deactivation_hook($caller, array(&$this,'onPluginDeactivated'));
    }
    
    /**
     *
     */
    public function onPluginActivated()
    {
        //actions to take when the plugin is activated
        
        //create our post mapping table, if the table already exists, then this will fail silently.
        $this->createTable();
    }
    
    /**
     *
     */
    public function onPluginDeactivated()
    {
        //actions to take when the plugin gets deactivated
    }
    
    /**
     *
     */
    public function loadSettings()
    {
        /**
         *  Wordpress function      : get_option
         *  Codex                   : https://codex.wordpress.org/Function_Reference/get_option
         */
        \EburyLabs\Hreflang::$general_settings = (array) get_option(\EburyLabs\Hreflang::$general_settings_key);
        \EburyLabs\Hreflang::$adv_settings = (array) get_option(\EburyLabs\Hreflang::$adv_settings_key);
        
    }
    
    /**
     *
     */
    private function createTable()
    {
        /**
         *  Wordpress class         : wpdb
         *  Codex                   : https://codex.wordpress.org/Class_Reference/wpdb
         */
        global $wpdb;
        
        $result = $wpdb->query("SHOW TABLES LIKE 'wp_hreflang_post_mapping'");
        
        if (!$result) {
            
            $table_name = 'wp_hreflang_post_mapping';

            $sql = "CREATE TABLE `".$table_name."` (
                  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
                  `s_blogid` smallint(5) NOT NULL,
                  `s_postid` mediumint(9) NOT NULL,
                  `s_locale` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `t_blogid` smallint(5) NOT NULL,
                  `t_postid` mediumint(9) NOT NULL,
                  `t_locale` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
                  UNIQUE KEY `id` (`id`),
                  KEY `indexOnAll` (`s_blogid`,`s_postid`,`t_blogid`,`t_postid`),
                  KEY `mappingExists` (`s_blogid`,`s_postid`,`t_blogid`)
                ) ENGINE=InnoDB;";
            
            $wpdb->query($sql);
        }
    }
}
