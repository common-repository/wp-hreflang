<?php
namespace EburyLabs\Hreflang;

/**
 *
 */
class GeneralSettings
{
    
    public $sel_leading_site;
    public static $leading_categories = null;
    public $sel_all_sites;
    public $results_per_page;
    /**
     *
     */
    public function __construct()
    {
        add_action("admin_init", array(&$this, "registerGeneralSettings"));
    }
    
    /**
     *
     */
    public function registerGeneralSettings()
    {
        //store our leading site variable
        if (isset(\EburyLabs\Hreflang::$general_settings['leading_site'])) {
            $this->sel_leading_site = \EburyLabs\Hreflang::$general_settings['leading_site'];
            
            //store the leading sites' categories
            $original_blog_id = get_current_blog_id(); // get current blog
            switch_to_blog($this->sel_leading_site);
            
            self::$leading_categories = get_categories();
            
            switch_to_blog($original_blog_id);
        }
        
        //store all selected site
        if (isset(\EburyLabs\Hreflang::$general_settings['general_option'])) {
            $this->sel_all_sites = \EburyLabs\Hreflang::$general_settings['general_option'];
        }
        
        //get our set results per page
        if (isset(\EburyLabs\Hreflang::$general_settings['results_per_page'])) {
            $this->results_per_page = \EburyLabs\Hreflang::$general_settings['results_per_page'];
        }
        
        //set tab name
        \EburyLabs\Hreflang::$plugin_settings_tabs[\EburyLabs\Hreflang::$general_settings_key] = 'General';
        
        /**
         *  Wordpress function    : register_setting
         *  Codex                 : https://codex.wordpress.org/Function_Reference/register_setting
         */
        register_setting(
            \EburyLabs\Hreflang::$general_settings_key,
            \EburyLabs\Hreflang::$general_settings_key
        );
        
        /**
         *  Wordpress function    : add_settings_section
         *  Codex                 : https://codex.wordpress.org/Function_Reference/add_settings_section
         */
        add_settings_section(
            'section_general',
            'General Settings',
            array(&$this, 'sectionGeneralDesc'),
            \EburyLabs\Hreflang::$general_settings_key
        );
        
        /**
         *  Wordpress function    : add_settings_field
         *  Codex                 : https://codex.wordpress.org/Function_Reference/add_settings_field
         */
        add_settings_field(
            'leading_site',
            'Select which site should be considered to be leading',
            array(&$this, 'fieldLeadingOption'),
            \EburyLabs\Hreflang::$general_settings_key,
            'section_general'
        );
        
        /**
         *  Wordpress function    : add_settings_field
         *  Codex                 : https://codex.wordpress.org/Function_Reference/add_settings_field
         */
        add_settings_field(
            'general_option',
            'Select which sites to include',
            array(&$this, 'fieldGeneralOption'),
            \EburyLabs\Hreflang::$general_settings_key,
            'section_general'
        );
        
        add_settings_field(
            'results_per_page',
            'How many results per page',
            array(&$this, 'fieldResultsOption'),
            \EburyLabs\Hreflang::$general_settings_key,
            'section_general'
        );
    }
    
    /**
     *
     */
    public function sectionGeneralDesc()
    {
        echo "
            To limit the number of sites hreflang needs to process please select the sites below 
            which should be included when targeting hreflang links from other sites.
        ";
    }
    
    /**
     *
     */
    public function fieldLeadingOption()
    {
        $select_onchange    = "javascript:document.wphreflangform.submit.click()";
        $select_name        = \EburyLabs\Hreflang::$general_settings_key."[leading_site]";
        
        echo "<select style='width:250px;' onchange=\"".$select_onchange."\" name=\"".$select_name."\">";
        
        echo "<option value=\"\">Select a site...</option>";
        echo "<option value=\"\">-----------------------</option>";
        
        foreach (\EburyLabs\Hreflang::$sites as $site) {
            echo "<option value=\"".$site['blog_id']."\"";
            
            if ($site['blog_id']==$this->sel_leading_site) {
                echo " selected";
            }
            echo ">".$site['domain']."</option>";
        }
        
        echo "</select>";
        
        echo "<br/><small> *) Selecting another leading site, will save this form and refresh the page</small>";
    }
    
    /**
     *
     */
    public function fieldGeneralOption()
    {
        //extract only the id's from the selected values array
        $selected_ids = [];
        
        if (count($this->sel_all_sites)>0) {
            foreach ($this->sel_all_sites as $sel) {
                $sel = explode(",", $sel);
                $selected_ids[] = $sel[0];
            }
        }
        
        $select_name = \EburyLabs\Hreflang::$general_settings_key."[general_option][]";
        
        echo "<select style='width:250px;' size='8' name='".$select_name."' multiple>";
        
        foreach (\EburyLabs\Hreflang::$sites as $site) {
            
            //if blog_id is not equal to leading site id
            //there is no need for the leading site to appear in the inclusion list
            if ($this->sel_leading_site!=$site['blog_id']) {
                
                echo "<option value=\"".$site['blog_id'].",".$site['domain']."\"";

                if (in_array($site['blog_id'], $selected_ids)) {
                    echo " selected";
                }
                
                echo ">".$site['domain']."</option>";
            }
        }
        echo "</select>";
        
        echo "<br/><small> *) Hold Ctrl (cmd on Mac) to select multiple sites</small>";
    }
    
    public function fieldResultsOption()
    {
        $input_name = \EburyLabs\Hreflang::$general_settings_key."[results_per_page]";
        
        if ($this->results_per_page=='') {
            $this->results_per_page = 10;
        }
        
        echo "<input type=\"text\" value=\"".$this->results_per_page."\" name=\"".$input_name."\">";
        echo "<br/>";
        echo "<small>*) How many results should be returned when searching for posts/pages</small>";
    }
}
