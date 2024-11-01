<?php
namespace EburyLabs\Hreflang;

/**
 * Class PostMetabox
 */
class PostMetabox
{
    private $type;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        //add meta box to both pages and posts admin page
        add_action('add_meta_boxes', array(&$this, 'addMetaBox'));
    }
    
    /**
     * addMetaBox
     */
    public function addMetaBox($post_type)
    {
        $this->type = $post_type;
        
        // Limit meta box to certain post types.
        $post_types = array('post', 'page');
        
        $icon = '<img src="'.WPHREFLANG_URL.'assets/eburylabs.png" align="top" />';
        $loading = '';
        
        if (in_array($post_type, $post_types)) {
            add_meta_box(
                'some_meta_box_name',
                $icon."&nbsp;".__('EburyLabs SEO Hreflang mapping', 'textdomain')."&nbsp;".$loading,
                array($this, 'renderMetaBoxContent'),
                $post_type,
                'advanced',
                'high'
            );
        }
    }
    
    /**
     * renderMetaBoxContent
     */
    public function renderMetaBoxContent()
    {
        //get the post category
        $cat = @get_the_category()[0]->term_id;
        if ($cat=='') {
            $cat='null';
        }
        $function_call = "hreflang.loadposts";
        
        /*if ($this->type=="post") {
            $function_call .= "loadposts";
        } else {
            $function_call .= "loadpages";
        }*/
        
        $function_call .= "(this.value, ".get_the_ID().", \"wphpmb\", ".$cat.");";
        
        echo "<div style='display:inline'>";
        
        echo "<div style='display:inline-block; padding:0 10px 10px 10px'>";
        echo __('Select a site to search for post: ', 'textdomain')."<select onchange='jQuery(\"#wphpmb-search\").val(\"\"); ".$function_call."'>";
        echo "<option>Select...</option>";
        echo "<option>".str_repeat("-", 15)."</option>";
        foreach (\EburyLabs\Hreflang::$general_settings['general_option'] as $site) {
            $data = explode(",", $site);
            echo "<option value=\"".$data[0]."\">".$data[1]."</option>";
        }
        echo "</select>";
        echo "</div>";
        
        echo "<div style='display:inline-block; padding:0 10px 10px 10px'>";
        echo "<input id='wphpmb-check' type=\"checkbox\" CHECKED /> Match to mapped category";
        echo "</div>";
        
        echo "<div style='display:inline-block; padding:0 10px 10px 10px'>";
        echo "<input id='wphpmb-date' type=\"checkbox\" CHECKED /> Same date";
        echo "</div>";
        
        echo "<div style='display:inline-block; padding:0 10px 10px 10px'>";
        echo "Filter on content:&nbsp;<input id='wphpmb-search' type=\"text\" DISABLED />";
        echo "</div>";
        
        echo "<div style='display:inline;'>";
        echo "<button id=\"hreflang-reload\" onclick='".$function_call."' type=\"button\" class=\"button\" DISABLED>";
        echo "<img src=\"".WPHREFLANG_URL."assets/reload.png\" /></button>";
        echo "</div>";
        
        echo "<div id=\"hreflang-loading\" style=\"position:absolute;top:-30px;left:0;display:none;width:100%;text-align:center\"><img src=\"".WPHREFLANG_URL."assets/loading.gif\" align=\"absmiddle\" /></div>";
        
        echo "</div>";
        echo "<hr />";
        
        //show current mapped page(s) to the current post/page
        echo "<div id='wphpmm'>";
        $metaheader = new \EburyLabs\Hreflang\MetaHeader();
        $check = htmlentities($metaheader->renderMetas());
        if ($check != "") {
            echo "<pre style='color:#666'>"; 
            echo " Currently mapped: (as it would show in 'view-source')\n";
            print_r($check); 
            echo "</pre>";
        }
        echo "</div>";
        
        //the search result div
        echo "<div id='wphpmb'></div>";
    }
}
