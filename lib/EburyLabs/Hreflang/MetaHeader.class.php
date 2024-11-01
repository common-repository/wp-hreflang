<?php
namespace EburyLabs\Hreflang;

/**
 *
 */
class MetaHeader
{
    
    private $org_blog;
    private $org_post;
    /**
     *
     */
    public function __construct()
    {
        add_action("wp_head", array(&$this, "renderMetas"), 0);
    }
    
    /**
     *
     */
    public function renderMetas()
    {
        global $post;
        
        $orginal_blog = $this->org_blog = get_current_blog_id();
        $this->org_post = get_the_ID();
        
        $check = $this->getMappings();
        
        $data = "";
        
        if (count($check)>0) {
            
            $data = "\n";
            $data .= "\t\t<!-- wp-hreflang plugin v".WPHREFLANG_VERSION." - https://labs.ebury.rocks (M. Donkers)-->\n";
            
            //echo "<pre>"; print_r($check); echo "</pre>";
            foreach ($check as $key => $chk) {
                
                if ($key !== "recursive") {
                    
                    switch_to_blog($chk['blogid']);

                    $href = get_page_link(intval($chk['postid']));
                    $locale = str_replace("_", "-", strtolower($chk['locale']));

                    $data .= "\t\t<link rel=\"alternate\" href=\"".$href."\" hreflang=\"".$locale."\" />";
                    if ($chk['recursive']) {
                        $data .= " <!-- *r -->";
                    }
                    $data .= "\n";
                }
            }
            
            switch_to_blog($orginal_blog);
            
            //WEB-664, include self-reference
            $data .= "\t\t<link rel=\"alternate\" href=\"".get_page_link($post->ID)."\" hreflang=\"".str_replace("_","-", strtolower(get_locale()))."\" />\n";
            
            $data .= "\t\t<!-- /wp-hreflang plugin -->\n";

        }
        
        if (is_admin()) {
            return $data;
        }
        
        echo $data;
    }
    
    public function getMappings()
    {
        
        $final = null;
        
        $result['normal'] = $this->getMappingsSQL();
        foreach ($result['normal'] as $key => $normal) {
            $result['normal'][$key]['recursive'] = false;
            $final[] = $result['normal'][$key];
        }
        
        $result['recursive'] = $this->getRecursiveSQL();
        $rec_count = count($result['recursive']);
        foreach($result['recursive'] as $key => $recur) {
            //don't include 'yourself', as we add this add the bottom
            if ($recur['blogid'] != $this->org_blog) {
                $result['recursive'][$key]['recursive'] = true;
                $final[] = $result['recursive'][$key];
            } else {
                //extract the source blog for this mapping and add it to the recursive result
                $result['recursive'][$rec_count]['blogid']=$recur['sourceblog'];
                $result['recursive'][$rec_count]['locale']=$recur['sourcelocale'];
                $result['recursive'][$rec_count]['postid']=$recur['sourcepost'];
                $result['recursive'][$rec_count]['recursive']=true;
                $final[] = $result['recursive'][$rec_count];
            }
            
            
        }
                
        return $final;
    }
    
    public function getRecursiveSQL()
    {
        global $wpdb;
                
        $recursive = $wpdb->get_results(
            "SELECT 
                wp_hreflang_post_mapping.t_blogid as blogid,
                wp_hreflang_post_mapping.t_locale as locale,
                wp_hreflang_post_mapping.t_postid as postid,
                wp_hreflang_post_mapping.s_blogid as sourceblog,
                wp_hreflang_post_mapping.s_postid as sourcepost,
                wp_hreflang_post_mapping.s_locale as sourcelocale
            FROM 
                (SELECT 
                    s_blogid as blogid,
                    s_postid as postid 
                FROM 
                    wp_hreflang_post_mapping
                WHERE 
                    t_blogid=".$this->org_blog." AND 
                    t_postid=".$this->org_post."
                ) as org,
                wp_hreflang_post_mapping 
            WHERE 
                wp_hreflang_post_mapping.s_blogid=org.blogid AND 
                wp_hreflang_post_mapping.s_postid=org.postid", ARRAY_A
        );
        
        return $recursive;
    }
    
    public function getMappingsSQL($reverse = false)
    {
        
        global $wpdb;
        
        $table_order = ["t", "s"];
        if ($reverse) {
            $table_order = ["s", "t"];
        }
        
        $mappings = $wpdb->get_results(
            "SELECT 
                ".$table_order[0]."_locale AS locale, ".$table_order[0]."_blogid AS blogid, ".$table_order[0]."_postid AS postid
            FROM 
                wp_hreflang_post_mapping
            WHERE 
                ".$table_order[1]."_blogid=".get_current_blog_id()." AND
                ".$table_order[1]."_postid=".get_the_ID()."",
            ARRAY_A
        );
        
        return $mappings;
        
    }
}
