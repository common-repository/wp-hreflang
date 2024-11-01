<?php
/**
 *
 *      ##########
 *      ##      ##
 *      ##      ##  Ajax.class.php
 *      ##      ##
 *  ### ## #######
 *  ##  ## 2016
 *  ###### E B U R Y  -  L A B S
 *
 * Ajax
 *
 * functions for hreflang plugin.
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
 *
 */
class Ajax
{
    /**
     * @var int $current_blog_id hold the current blog id
     */
    private $current_blog_id;
        
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->current_blog_id = get_current_blog_id();
        
        //add endpoint for get_posts
        add_action('wp_ajax_get_posts', array(&$this, 'ajaxGetPosts'));
        add_action('wp_ajax_store_mapping', array(&$this, 'ajaxStoreMapping'));
        add_action('wp_ajax_remove_mapping', array(&$this, 'ajaxRemoveMapping'));
    }
    
    /**
     * ajaxRemoveMapping
     */
    public function ajaxRemoveMapping()
    {
        global $wpdb;
        
        //needs two params, source post id, target post id
        $data = unserialize(base64_decode($_POST['payload']));
        $table_name = 'wp_hreflang_post_mapping';
        
        $sql = "DELETE 
                    FROM ".$table_name." 
                WHERE 
                    s_blogid=".$data[0]." AND 
                    s_postid=".$data[1]." AND 
                    t_blogid=".$data[2]."";
        
        $wpdb->query($sql);
        
        echo $data[2].";".$data[1].";".$data[3];
        
        die();
    }
    
    /**
     * ajaxStoreMapping
     */
    public function ajaxStoreMapping()
    {
        global $wpdb;
        
        $data = unserialize(base64_decode($_POST['payload']));
                        
        $target_blog = $data[0];
        $original_post = $data[1];
        $mapping2post = $data[2];
        $locale = $data[3];
        $slocale = $data[4];
        
        $check = $wpdb->get_row(
            "SELECT 
                id 
            FROM 
                wp_hreflang_post_mapping
            WHERE 
                s_blogid=".$this->current_blog_id." AND
                s_postid=".$original_post." AND
                t_blogid=".$target_blog.""
        );
        
        if ($check===null) {

            $wpdb->insert(
                'wp_hreflang_post_mapping',
                [
                    's_blogid' => $this->current_blog_id,
                    's_postid' => $original_post,
                    's_locale' => $slocale,
                    't_blogid' => $target_blog,
                    't_postid' => $mapping2post,
                    't_locale'=> $locale
                ],
                [
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%d',
                    '%s'
                ]
            );
            
            echo $mapping2post;
            
            die();
            
        }
        
        $wpdb->update(
            'wp_hreflang_post_mapping',
            [
                't_postid' => $mapping2post,
                't_locale'=> $locale
            ],
            ['ID' => $check->id],
            [
                '%d',
                '%s'
            ],
            ['%d']
        );
        
        echo $mapping2post;
        die();
    }
    
    /**
     * ajaxGetPosts
     */
    public function ajaxGetPosts()
    {
        
        $mappings = @\EburyLabs\Hreflang::$adv_settings["cat_mapping"];
        
        $source_locale = get_option("WPLANG");
        
        $mapping_found = false;
        
        if ($_POST['cat']!=="" && count($mappings)>1) {
            //trim the array to only have the target blogs entries
            foreach ($mappings as $key => $mapping) {
                if (strstr($key, "hreflang-".$_POST['data']) !== false) {
                    if (trim($mapping) == $_POST['cat']) {
                        $mapping_found = str_replace("hreflang-".$_POST['data']."-", "", $key);
                    }
                }
            }
        }
        
        if (!$mapping_found) {
            echo "<div style='color:#f00;text-align:center; width:100%'>";
            echo "<small>";
            echo "(*) results are <strong>NOT</strong> filtered by category, Either";
            echo "the category has not been mapped or you unchecked the option to filter on categories.";
            echo "</small>";
            echo "</div>";
        }
        
        $post_type = get_post_field('post_type', $_POST['post']);
        $postdate = explode(" ", get_post($_POST['post'])->post_date);
        
        //switch to the requested blog id
        switch_to_blog($_POST['data']);
        
        $posts_per_page = \EburyLabs\Hreflang::$general_settings['results_per_page'];
        if ($posts_per_page=='') {
            $posts_per_page = 10;
        }
        
        //set post query arguments
        $args = array(
            "post_type"     => array('post','page'),
            "post_status"   => 'publish',
            "posts_per_page"=> $posts_per_page,
            "orderby"       => "date",
            "order"         => "DESC",
            "cat"           => $mapping_found,
            "s"             => $_POST['s'],
            "paged"         => $_POST['page'],
        );
        
        $pd = [];
        
        if ($_POST['sd'] === "true") {
            
            $datepart = explode("-", $postdate[0]);
            
            $pd = [array(
                    "year" => $datepart[0],
                    "month" => $datepart[1],
                    "day" => $datepart[2]
                )];
            
            $args["date_query"] = $pd;
        }
        
        //query the db
        $query = new \WP_Query($args);
                
        //create the object containing all data to generate our output
        $obj = [
            "locale"    => get_option("WPLANG"),
            "slocale"   => $source_locale,
            "blogid"    => $_POST['data'],
            "cat"       => $_POST['cat'],
            "totalposts"=> $query->found_posts,
            "posts"     => $this->processPosts($query->posts),
            "totalpages"=> $query->max_num_pages,
            "current"   => $_POST['page'],
            "next"      => $_POST['page']+1,
            "previous"  => $_POST['page']-1,
            "type"      => $post_type
        ];
        
        //if we did find some posts, start to draw our table output
        if ($obj['totalposts']>0) {
            $this->drawTable($obj);
        } else {
            echo "No posts found!";
        }
        
        //switch back to our orginal blog
        switch_to_blog($this->current_blog_id);
        
        //make sure we are finished.
        die();
        
    }
    
    /**
     * processPosts
     */
    public function processPosts($posts)
    {
        if (count($posts) > 0) {
            foreach ($posts as $post) {
                            
                $peek = $this->html2text($post->post_content);
                
                $categories = get_the_category($post->ID);
                $cat=null;
                foreach ($categories as $category) {
                    $cat[] = $category->name;
                }
                $cats = wordwrap(@implode(", ", $cat), 35, "<br/>");
                
                $title = $post->post_title;
                //process the post title
                if (strlen($title)>80) {
                    $title = mb_substr($title, 0, 80)."...";
                }
                
                //process the preview text
                if (strlen($peek)>80) {
                    $peek = mb_substr($peek, 0, 80)."...";
                } else {
                    $peek = "<i style=\"color:#ccc\">No preview content available!</i>";
                }

                $result[] = [
                    "id"    => $post->ID,
                    "title" => $title,
                    "peek"  => $peek,
                    "post_date"=>$post->post_date,
                    "cat"   => $cats,
                    "type"  => $post->post_type,
                    "author"=> get_userdata($post->post_author)->display_name
                ];
            }
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * checkIfAnyIsMapped
     */
    private function checkIfAnyIsMapped()
    {
        global $wpdb;
        
        $sql = "SELECT 
                    t_postid 
                FROM 
                    wp_hreflang_post_mapping 
                WHERE 
                    t_blogid=".$_POST['data']." AND 
                    s_postid=".$_POST['post'];
        
        $result = $wpdb->get_results($sql);
    
        if (count($result)>0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * tableHeader
     *
     * @param array $pdt
     * @return string contains the div with filter options
     */
    private function tableHeader($pdt)
    {
        $data_set = array($this->current_blog_id, $_POST['post'], $_POST['data'], $_POST['cat']);
        $data_set = base64_encode(serialize($data_set));
                
        $hasMapping = $this->checkIfAnyIsMapped();
        
        $posts_per_page = \EburyLabs\Hreflang::$general_settings['results_per_page'];
        if ($posts_per_page=='') {
            $posts_per_page = 10;
        }
        
        //vars for button
        $div        = "hrlbutrmpar";
        $class      = "button ".($hasMapping?"button-primary":"");
        $onclick    = "hreflang.removeMap('".$data_set."')";
        $disabled   = ($hasMapping?"":"DISABLED");
        
        $output = "<div style='display:inline-block; width:50%; text-align:left'>";
        $output .= "<h3>Total posts found: ".$pdt['totalposts'].", Showing ".$posts_per_page." per page.</h3>";
        $output .= "</div>";
        $output .= "<div style='display:inline-block; width:50%; text-align:right;padding-bottom:5px;'>";
        $output .= "<button id='".$div."' class='".$class."' type='button' onclick=\"".$onclick."\" ".$disabled.">";
        $output .= "Remove existing mapping";
        $output .= "</button>";
        $output .= "</div>";
        
        return $output;
    }
    
    /**
     * drawTable
     *
     * @param array $pdt
     * @return null
     */
    private function drawTable($pdt)
    {
        
        echo "<div class='hreflang'>";
        
        //show table header, site selector, and filters
        echo $this->tableHeader($pdt);
        
        echo "<br/>";
        
        if ($pdt['totalpages']>1) {
            $this->paging($pdt);
        }
        
        //start the table output
        echo "<table cellspacing='0' width='100%'>";
        echo "<tbody>";

        $alt=0;
        $mapping_found=false;
        
        foreach ($pdt['posts'] as $post) {
            
            //echo "<pre>"; print_r($post); echo "</pre>";
            
            //check if the post in this iteration is already mapped
            $check = array($this->current_blog_id, $_POST['post'], $_POST['data'], $post['id']);
            $check = $this->checkForMapping($check);
            
            $rowclass = "";
            if ($alt%2) {
                $rowclass .= "alternate";
            }
            
            if ($check) {
                $rowclass .= " rowselected";
            }
            
            $rowopacity="opacity:1.0";
            if ($pdt['type']!=$post['type']) {
                $rowopacity = "opacity:0.3";
            }
            
            echo "<tr class='".$rowclass."' style='".$rowopacity."'>";
            echo "<td class='column-columnname' align='center' width='30'>";
            
            $data_set = array($_POST['data'], $_POST['post'], $post['id'], $pdt['locale'], $pdt['slocale']);
            $data_set = base64_encode(serialize($data_set));
            
            echo "<input name=\"hreflangsel\" value=\"".$post['id']."\" type=\"radio\" 
                onchange=\"hreflang.mappost('".$data_set."');\"";
                        
            if ($check && !$mapping_found) {
                echo " CHECKED";
                //since we can only have one result, we set mapping_found to true
                //so we don't have to keep querying the db
                $mapping_found=true;
            }
            
            echo "/>";
            echo "</td>";

            echo "<td class='column-columnname inside' style='padding-top:3px;'>";
            echo "<strong><a href=\"".get_page_link($post['id'])."\" target=\"_blank\">".$post['title']."</a></strong>";
            echo "</td>";
            
            echo "<td class='column-columnname inside'>";
            echo $post['post_date'];
            echo "</td>";
            
            echo "<td class='column-columnname inside'>";
            echo $post['type'];
            echo "</td>";
            
            echo "<td class='column-columnname inside'>";
            echo $post['cat'];
            echo "</td>";
            
            echo "<td class='column-columnname inside'>";
            echo $post['author'];
            echo "</td>";

            echo "</tr>";

            echo "<tr class='".$rowclass."' style='".$rowopacity."'>";
            echo "<td style='border-bottom:1px solid #CCC'>";
            echo "&nbsp;";
            echo "</td>";

            echo "<td class='column-columnname inside' style='border-bottom:1px solid #CCC'>";
            echo $post['peek'];
            echo "</td>";
            
            echo "<td style='border-bottom:1px solid #CCC'>";
            echo "&nbsp;";
            echo "</td>";
            
            echo "<td style='border-bottom:1px solid #CCC'>";
            echo "&nbsp;";
            echo "</td>";
            
            echo "<td style='border-bottom:1px solid #CCC'>";
            echo "&nbsp;";
            echo "</td>";
            
            echo "<td style='border-bottom:1px solid #CCC'>";
            echo "&nbsp;";
            echo "</td>";

            echo "</tr>";
            $alt++;
        }

        echo "</tbody>";
        echo "</table>";
        
        //enable our remove button
        if ($mapping_found) {
            echo "<script>jQuery('#hrlbutrmpar').attr('disabled', false).addClass('button-primary');</script>";
        }
        
        if ($pdt['totalpages']>1) {
            $this->paging($pdt);
        }
        
        echo "</div>";
    }
    
    /**
     * paging
     *
     * @param array $pdt
     * @return null
     */
    private function paging($pdt)
    {
        if ($pdt['cat']=='') {
            $pdt['cat']='null';
        }
        echo "<div align='center'>";
        if ($pdt['previous']>0) {
            $linkprev = "hreflang.loadposts(".$pdt['blogid'].",".$_POST['post'].",'wphpmb',".$pdt['cat'].",".$pdt['previous'].");";
            echo "<a href=\"javascript:".$linkprev."\">";
            echo "&lt; Previous";
            echo "</a>";
        } else {
            echo "<span style='color:#ccc'>&lt; Previous</span>";
        }

        echo " | Page <strong>".$pdt['current']."</strong> of <strong>".$pdt['totalpages']."</strong> | ";

        if ($pdt['current']<$pdt['totalpages']) {
            $linknext = "hreflang.loadposts(".$pdt['blogid'].",".$_POST['post'].",'wphpmb',".$pdt['cat'].",".$pdt['next'].");";
            echo "<a href=\"javascript:".$linknext."\">";
            echo "Next &gt;";
            echo "</a>";
        } else {
            echo "<span style='color:#ccc'>Next &gt;</span>";
        }
        echo "</div>";
    }
    
    /**
     * checkForMapping
     *
     * @param array $params should contain: [source blog id, source post id, target blog id, target post id]
     * @return boolean
     */
    private function checkForMapping($params = [])
    {
        global $wpdb;
        
        $error = false;
        
        if (!empty($params)) {
            foreach ($params as $param) {
                if (!is_numeric($param)) {
                    $error = true;
                }
            }
            
            if (!$error) {
                $check = $wpdb->get_results(
                    "SELECT 
                        id
                    FROM 
                        wp_hreflang_post_mapping
                    WHERE 
                        s_blogid=".$params[0]." AND
                        s_postid=".$params[1]." AND
                        t_blogid=".$params[2]." AND
                        t_postid=".$params[3]."
                        "
                );
                
                return (count($check)==1?true:false);
            } else {
                return false;
            }
        }
    }
    
    /**
     * html2text
     *
     * @param string $document
     * @return string cleaned up document
     */
    private function html2text($document)
    {
        
        $rules = [
            '@<script[^>]*?>.*?</script>@si',
            '@<[\/\!]*?[^<>]*?>@si',
            '@([\r\n])[\s]+@',
            '@&(quot|#34);@i',
            '@&(amp|#38);@i',
            '@&(lt|#60);@i',
            '@&(gt|#62);@i',
            '@&(nbsp|#160);@i',
            '@&(iexcl|#161);@i',
            '@&(cent|#162);@i',
            '@&(pound|#163);@i',
            '@&(copy|#169);@i',
            '@&(reg|#174);@i',
            '@&#(d+);@e'
        ];
        
        $replace = [
            '',
            '',
            '',
            '',
            '&',
            '<',
            '>',
            ' ',
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            chr(174),
            'chr()'
        ];
        
        return @preg_replace($rules, $replace, $document);
    }
}
