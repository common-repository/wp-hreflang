<?php
/**
 *
 *      ##########
 *      ##      ##
 *      ##      ##  AdminMenu.class.php
 *      ##      ##
 *  ### ## #######
 *  ##  ## 2016
 *  ###### E B U R Y  -  L A B S
 *
 * Admin Menu
 *
 * Sets up the admin panel menu entry.
 *
 * Wordpress functions used:
 * add_action               https://developer.wordpress.org/reference/functions/add_action/
 * is_admin                 https://codex.wordpress.org/Function_Reference/is_admin
 * add_menu_page            https://developer.wordpress.org/reference/functions/add_menu_page/
 * wp_nonce_field           https://codex.wordpress.org/Function_Reference/wp_nonce_field
 * settings_fields          https://codex.wordpress.org/Function_Reference/settings_fields
 * do_settings_sections     https://codex.wordpress.org/Function_Reference/do_settings_sections
 * submit_button            https://codex.wordpress.org/Function_Reference/submit_button
 *
 *
 * @package EburyLabs\Hreflang
 * @since   1.0.1
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD license
 * @author  M. Donkers
 *
 */
namespace EburyLabs\Hreflang;

/**
 * Class AdminMenu
 */
class AdminMenu
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', array(&$this, 'adminMenu'));
    }
       
    /**
     * adminMenu
     *
     * If loaded in admin, start adding the menu entry for this plugin
     */
    public function adminMenu()
    {
        if (is_admin()) {
            
            add_menu_page(
                'Hreflang Settings',
                \EburyLabs\Hreflang::$plugin_name,
                'manage_options',
                'wp-hreflang',
                array($this, 'loadOptions'),
                WPHREFLANG_URL.'assets/eburylabs.png'
            );

        }
    }
    
    /**
     * loadOptions
     *
     * Start the admin page for the plugin
     */
    public function loadOptions()
    {
        
        $tab = isset($_GET['tab']) ? $_GET['tab'] : \EburyLabs\Hreflang::$general_settings_key;
        
        echo "<h2><img src='".WPHREFLANG_URL."assets/eburylabs.png' /> EburyLabs SEO Hreflang Plugin admin</h2>";
        echo "<div class='wrap'>";
        
        $this->pluginOptionsTabs();

        echo "<form method='post' name='wphreflangform' action='options.php'>";

        wp_nonce_field('update-options');
        settings_fields($tab);
        do_settings_sections($tab);
        
        submit_button();
        
        echo "</form>";
        echo "</div>";
    }
    
    /**
     * pluginOptionsTabs
     *
     * Create the tabs for the plugin admin area
     */
    private function pluginOptionsTabs()
    {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : \EburyLabs\Hreflang::$general_settings_key;
        
        echo '<h2 class="nav-tab-wrapper">';
        
        foreach (\EburyLabs\Hreflang::$plugin_settings_tabs as $tab_key => $tab_caption) {
            
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            $key = \EburyLabs\Hreflang::$plugin_options_key;
            
            echo "<a class='nav-tab ".$active."' href='?page=".$key."&tab=".$tab_key."'>";
            echo $tab_caption;
            echo "</a>";
            
        }
        echo '</h2>';
    }
}
