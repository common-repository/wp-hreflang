<?php
/**
 *
 *      ##########
 *      ##      ##
 *      ##      ##  MappingSettings.class.php
 *      ##      ##
 *  ### ## #######
 *  ##  ## 2016
 *  ###### E B U R Y  -  L A B S
 *
 * MappingSettings
 *
 * The Category Mapping tab on the admin panel of the plugin.
 *
 * Wordpress functions used:
 * add_action               https://developer.wordpress.org/reference/functions/add_action/
 * register_setting         https://codex.wordpress.org/Function_Reference/register_setting
 * add_settings_section     https://codex.wordpress.org/Function_Reference/add_settings_section
 * get_current_blog_id      https://codex.wordpress.org/Function_Reference/get_current_blog_id
 * switch_to_blog           https://codex.wordpress.org/Function_Reference/switch_to_blog
 * get_categories           https://developer.wordpress.org/reference/functions/get_categories/
 *
 * @package EburyLabs\Hreflang
 * @since   1.0.1
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD license
 * @author  M. Donkers
 *
 */
namespace EburyLabs\Hreflang;

/**
 * Class MappingSettings
 */
class MappingSettings
{
    
    /**
     * @var array $stored_mappings Holds all category mappings stored
     * @since   1.0.1
     */
    private $stored_mappings;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        add_action("admin_init", array(&$this, "registerMappingSettings"));
    }
    
    /**
     * registerMappingSettings
     * @since   1.0.1
     */
    public function registerMappingSettings()
    {
        //set tab tile
        \EburyLabs\Hreflang::$plugin_settings_tabs[\EburyLabs\Hreflang::$adv_settings_key] = 'Category Mapping';
        
        //register settings
        register_setting(
            \EburyLabs\Hreflang::$adv_settings_key,
            \EburyLabs\Hreflang::$adv_settings_key
        );
        
        //add section
        add_settings_section(
            'section_advanced',
            'Category mapping',
            array(&$this, 'sectionAdvancedDesc'),
            \EburyLabs\Hreflang::$adv_settings_key
        );
    }
    
    /**
     * Set the Category Mapping message and start building up the sites and categories
     * @since   1.0.1
     */
    public function sectionAdvancedDesc()
    {
        echo 'Category mapping settings for hreflang plugin. These settings are optional
        but it will present the posts of matching categories first, this will 
        speed up to locate a similar post in one of the other multisite sites.<br/><br/>';
        
        //draw up the tab with all selected sites and to map the categories
        $this->fieldAdvancedOption();
    }

    /**
     * Builds up the Category mapping settings tab
     * @since   1.0.1
     */
    public function fieldAdvancedOption()
    {
        
        //get all selected sites
        $selected_sites         = @\EburyLabs\Hreflang::$general_settings['general_option'];
        
        //read our mappings
        $this->stored_mappings  = @\EburyLabs\Hreflang::$adv_settings["cat_mapping"];
        
        //get our orginal blog id
        $original_blog_id       = get_current_blog_id();
        
        //start iterating the sites
        foreach ($selected_sites as $site) {
            
            $data = explode(",", $site);
            //print out the site url
            echo "<h3>".$data[1]."</h3>";
            
            //switch the blog to this site
            switch_to_blog($data[0]);
            
            //fetch all the categories
            $categories = get_categories();
            
            //build our table
            echo "<table class='widefat fixed' cellspacing='0'>";
            foreach ($categories as $category) {
                
                echo "<tr>";
                
                echo "<td class='column-columnname inside' align='right'>";
                echo $category->name;
                echo "</td>";
                
                echo "<td class='column-columnname inside'>";
                echo $this->categoriesSelect("hreflang-".$data[0]."-".$category->term_id, $this->stored_mappings);
                echo "</td>";
                
                echo "</tr>";
                
            }
            
            echo "</table>";
            
        }
        
        //switch back to our orginal blog
        switch_to_blog($original_blog_id);
        
        //show a message stating that no sites have been selected in the general tab
        if (count($selected_sites) === 0) {
            echo "<small style='color:red'>";
            echo "(*) You need to set a leading site and select sites to include on the General Tab ";
            echo "to enable category mapping.";
            echo "</small>";
        }
    }
    
    /**
     * function: categoriesSelect
     *
     * Generates a select element for inclusion in the sites category list to map
     *
     * @since   1.0.1
     *
     * @param $name     string  name to pass as identifier for the select element
     * @param $mapping  array   current stored mappings
     *
     * @return string  the complete select element
     */
    private function categoriesSelect($name, $mapping)
    {
        //set our select element name
        $selname = \EburyLabs\Hreflang::$adv_settings_key."[cat_mapping][".$name."]";

        //build up the select element
        $sel = "<select name='".$selname."'>";
        $sel .= "<option value=\"\"></option>";
        
        foreach (\EburyLabs\Hreflang\GeneralSettings::$leading_categories as $lead_cat) {
            
            $sel .= "<option value=\"".$lead_cat->term_id."\"";
            
            if ($lead_cat->term_id == @$mapping[$name]) {
                $sel .= " SELECTED";
            }
            
            $sel .= ">".$lead_cat->name."</option>";
            
        }
        
        $sel .= "</select>";
        
        //return the select element
        return $sel;
    }
}
