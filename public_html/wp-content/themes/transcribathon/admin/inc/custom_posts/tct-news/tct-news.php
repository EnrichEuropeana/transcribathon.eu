<?php
/*
custom post for "news" (Inorder to add the POSTTYPE: 'news')
*/
function news_register() {
	$labels = array(
		'name' => _x('News messages','post-type: news','transcribathon'),
		'singular_name' => _x('News message','post-type: news','transcribathon'),
		'all_items' => _x('All news','post-type: news','transcribathon'),
		'add_new' => _x('Add a new news message','post-type: news','transcribathon'),
		'add_new_item' => _x('Add a new news message','post-type: news','transcribathon'),
		'edit_item' => _x('Edit news message','post-type: news','transcribathon'),
		'new_item' => _x('New news message','post-type: news','transcribathon'),
		'view_item' => _x('View news message','post-type: news','transcribathon'),
		'search_items' => _x('Search news messages','post-type: news','transcribathon'),
		'not_found' =>  _x('No fitting news message found','post-type: news','transcribathon'),
		'not_found_in_trash' => _x('No fitting news message found in trash','post-type: news','transcribathon'),
		'insert_into_item' => _x('Insert into news message','post-type: news','transcribathon'),
		'uploaded_to_this_item' => _x('Uploaded to this news message','post-type: news','transcribathon'),
		'featured_image' => _x('News image','post-type: news','transcribathon'),
		'set_featured_image' => _x('Set news image','post-type: news','transcribathon'),
		'remove_featured_image' => _x('Remove news image','post-type: news','transcribathon'),
		'use_featured_image' => _x('Use as news image','post-type: news','transcribathon'),
		'parent_item_colon' => _x('Parent news message:','post-type: news','transcribathon'),
		'update_item' => _x('Update news message','post-type: news','transcribathon'),
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => 'news',
		'menu_icon' => 'dashicons-megaphone',
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 5,
		'has_archive' => true,
		'supports' => array('title','thumbnail')
	  ); 
	register_post_type( 'news' , $args );
	
}
add_action('init', 'news_register');


// Init Custom fields
$custom_fields_for_news = array(
	array('name' => 'tct_news_subheadline','type' => 'text'),
	array('name' => 'tct_news_excerpt','type' => 'text'),
	array('name' => 'tct_news_startdate','type' => 'date'),
	array('name' => 'tct_news_enddate','type' => 'date'),
	
);




// Custom Fields
function add_news_fields(){
	global $post,$wpdb,$custom_fields_for_news;
	wp_nonce_field( plugin_basename( __FILE__ ), 'news_nonce' );
	
	// initialize and prepare all custom fields 
	
	//echo "<pre>".print_r($post,true)."</pre>";
	$custom = get_post_custom($post->ID);

	//echo "<pre>".print_r($custom,true)."</pre>";
	for($i=0; $i<sizeof($custom_fields_for_news); $i++){
		if($custom_fields_for_news[$i]['type'] === 'select' || $custom_fields_for_news[$i]['type'] === 'text' || $custom_fields_for_news[$i]['type'] === 'datetime' || $custom_fields_for_news[$i]['type'] === 'date-year-possible' || $custom_fields_for_news[$i]['type'] === 'conditional_select' || $custom_fields_for_news[$i]['type'] === 'conditional_text' || $custom_fields_for_news[$i]['type'] === 'date'){ 	
			if(isset($custom[$custom_fields_for_news[$i]['name']][0]) && trim($custom[$custom_fields_for_news[$i]['name']][0]) != ""){  ${'q'.$custom_fields_for_news[$i]['name']} = $custom[$custom_fields_for_news[$i]['name']][0];  }else{ ${'q'.$custom_fields_for_news[$i]['name']} = ""; }
		}else if($custom_fields_for_news[$i]['type'] === 'bool'){
			if(isset($custom[$custom_fields_for_news[$i]['name']][0]) && trim($custom[$custom_fields_for_news[$i]['name']][0]) != ""){  ${'q'.$custom_fields_for_news[$i]['name']} = 1;  }else{ ${'q'.$custom_fields_for_news[$i]['name']} = 0; }
		}
	}
	
	echo "<div class=\"hell-bereich\">\n";
		echo "<h3  class=\"ln\">". _x('News settings','post-type: news sub-headline in back-end','transcibathon')."</h3>\n"; 
		$x = "tct_news_subheadline"; // tct_news_subheadline
		echo "<label for=\"".$x."\"><strong>"._x('Subheadline','post-type: news sub-headline in back-end','transcibathon')."</strong>:</label>\n";
		echo "<input type=\"text\" id=\"".$x."\" style=\"width:100%;\" name=\"".$x."\" value=\"".esc_html(stripslashes(${'q'.$x}))."\" onchange=\"registerCustomChanges();\" />\n<br />";
		
		$x = "tct_news_excerpt"; // tct_news_excerpt
		echo "<label for=\"".$x."\"><strong>"._x('Excerpt for the news-page','post-type: news sub-headline in back-end','transcibathon')."</strong>: <small><em></em></small></label>\n";
		echo "<textarea id=\"".$x."\" style=\"width:100%; height:98px;\" name=\"".$x."\" onchange=\"registerCustomChanges();\">".esc_html(stripslashes(${'q'.$x}))."</textarea>\n<br />";
		
				
		echo "<br /><hr />\n"; 
		echo "<div class=\"adm_section adm_group\">\n";
			echo "<div class=\"adm_col adm_span_1_of_4\"> \n";
				$x = "tct_news_startdate"; // tct_news_startdate
				echo "<label for=\"".$x."\"><strong>"._x('Start Date','backend: edit newsmessage','transcribathon')."</strong>: </label>\n";
				echo "<input type=\"date\" id=\"".$x."\" style=\"width:100px;\" name=\"".$x."\" value=\"".esc_html(stripslashes(${'q'.$x}))."\" onchange=\"registerCustomChanges();\" class=\"tct-datepicker\" />\n<br /><br />";
			echo "</div>\n";
			echo "<div class=\"adm_col adm_span_1_of_4\"> \n";
				$x = "tct_news_enddate"; // tct_news_enddate
				echo "<label for=\"".$x."\"><strong>"._x('End Date','backend: edit newsmessage','transcribathon')."</strong>: </label>\n";
				echo "<input type=\"date\" id=\"".$x."\" style=\"width:100px;\" name=\"".$x."\" value=\"".esc_html(stripslashes(${'q'.$x}))."\" onchange=\"registerCustomChanges();\" class=\"tct-datepicker\" />\n<br /><br />";
			echo "</div>\n";
			echo "<div class=\"adm_col adm_span_2_of_4\"> \n";
				echo "<p>"._x('Start- and End-Date have no function but to be displayed if set. In case you would like to schedule this news message to appear sometime in the future, please use the publishing-settings in the right column.','backend: Note for Date-Settings at news messages','transcribathon')."</p>\n";
			echo "</div>\n";
		echo "</div>\n";
	echo "</div>\n";
	echo "<br /><hr /><br />\n"; 
	
	echo "<label for=\"post_content\"><strong>"._x('Main news content','backend: edit newsmessage','transcribathon')."</strong>: </label>\n";
	wp_editor( $post->post_content, 'content', $settings = array('textarea_name'=>'content') );

	echo "<input type=\"text\" name=\"custom_changes\" autocomplete=\"off\" id=\"custom_changes\" value=\"0\" />\n";
	
}


// Add custom fields
function init_news_fields(){
	add_meta_box("newsfields", _x('Main settings','post-type: news headline in back-end','transcibathon'), "add_news_fields", "news", "advanced", "high");
}
add_action("admin_init", "init_news_fields"); 


// To include fields when saving the post
function save_custom_fields_for_news(){
	global $post,$custom_fields_for_news;
	
	// For security reasons
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE): return; endif; // No auto-save
	if ((isset($_POST['news_nonce'])) && (!wp_verify_nonce($_POST['news_nonce'],plugin_basename( __FILE__ )))): return; endif; // make sure, the data comes directly from this script
	if(!current_user_can( 'edit_post', @$post->ID )): return; endif; // make sure, the current user has the right to edit this post
	
	// Save
	for($i=0; $i<sizeof($custom_fields_for_news); $i++){
		if($custom_fields_for_news[$i]['type'] === 'select' || $custom_fields_for_news[$i]['type'] === 'text'){ 	
			if(isset($_POST[$custom_fields_for_news[$i]['name']]) && trim($_POST[$custom_fields_for_news[$i]['name']]) != ""){ update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], esc_html(stripslashes($_POST[$custom_fields_for_news[$i]['name']])) );  }else{  update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], NULL );   }
		}else if($custom_fields_for_news[$i]['type'] === 'bool'){
			if(isset($_POST[$custom_fields_for_news[$i]['name']]) && $_POST[$custom_fields_for_news[$i]['name']] == '1'){ update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], 'yes' );  }else{ update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], NULL );  }
		}else if($custom_fields_for_news[$i]['type'] === 'datetime'){
			if(isset($_POST[$custom_fields_for_news[$i]['name']."_date"]) && trim($_POST[$custom_fields_for_news[$i]['name']."_date"]) != ""){
				if(isset($_POST[$custom_fields_for_news[$i]['name']."_time"]) && trim($_POST[$custom_fields_for_news[$i]['name']."_time"]) != ""){  $time = $_POST[$custom_fields_for_news[$i]['name']."_time"]; }else{ $time='00:00:00'; }
				update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], date('Y-m-d H:i:s', strtotime($_POST[$custom_fields_for_news[$i]['name']."_date"]." ".$time)));
			}else{
				update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], NULL);
			}
		}else if($custom_fields_for_news[$i]['type'] === 'date'){
			if(isset($_POST[$custom_fields_for_news[$i]['name']]) && trim($_POST[$custom_fields_for_news[$i]['name']]) != ""){
				update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], date('Y-m-d', strtotime($_POST[$custom_fields_for_news[$i]['name']])));
			}else{
				update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], NULL);
			}
		}else if($custom_fields_for_news[$i]['type'] === 'date-year-possible'){
			if(isset($_POST[$custom_fields_for_news[$i]['name']]) && trim($_POST[$custom_fields_for_news[$i]['name']]) != ""){
				if(strlen(trim($_POST[$custom_fields_for_news[$i]['name']]))<5){
					update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], $_POST[$custom_fields_for_news[$i]['name']]);
				}else if(strlen(trim($_POST[$custom_fields_for_news[$i]['name']]))>4 && strlen(trim($_POST[$custom_fields_for_news[$i]['name']]))<8){
					update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], date('Y-m', strtotime($_POST[$custom_fields_for_news[$i]['name']])));
				}else{
					update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], date('Y-m-d', strtotime($_POST[$custom_fields_for_news[$i]['name']])));
				}
			}else{
				update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], NULL);
			}
		}else if($custom_fields_for_news[$i]['type'] === 'conditional_select'){
			if(isset($_POST[$custom_fields_for_news[$i]['name']]) && trim($_POST[$custom_fields_for_news[$i]['name']]) != "" && trim($_POST[$custom_fields_for_news[$i]['name']]) != trim($custom_fields_for_news[$i]['critical_value'])){
				update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], esc_html(stripslashes($_POST[$custom_fields_for_news[$i]['name']])) );
				update_post_meta($post->ID, $custom_fields_for_news[$i]['critical_field'], NULL );
			}else if(trim($_POST[$custom_fields_for_news[$i]['name']]) == trim($custom_fields_for_news[$i]['critical_value'])){
				update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], 'Other' );
				if(isset($_POST[$custom_fields_for_news[$i]['critical_field']]) && trim($_POST[$custom_fields_for_news[$i]['critical_field']]) != ""){
					update_post_meta($post->ID, $custom_fields_for_news[$i]['critical_field'], esc_html(stripslashes($_POST[$custom_fields_for_news[$i]['critical_field']])) );
				}else{
					update_post_meta($post->ID, $custom_fields_for_news[$i]['critical_field'], NULL );
				}
			}else{
				update_post_meta($post->ID, $custom_fields_for_news[$i]['name'], NULL ); 
				update_post_meta($post->ID, $custom_fields_for_news[$i]['critical_field'], NULL );
			}
		}
	}
	
	
}
add_action('save_post', 'save_custom_fields_for_news');



// Add JS and CSS
function add_custom_css_and_js_for_news() {
	global $pagenow,$post;
    if($pagenow == 'post.php' && "news" == $post->post_type || $pagenow == 'post-new.php' && "news" == $post->post_type){ 
      	wp_enqueue_media();
		wp_register_script('tct-news-js', CHILD_TEMPLATE_DIR.'/admin/inc/custom_posts/js/tct-news.js', array('jquery'));
		wp_enqueue_script('tct-news-js');
		wp_register_style( 'tct-news-css', CHILD_TEMPLATE_DIR.'/admin/inc/custom_posts/css/tct-news.css');
       	wp_enqueue_style( 'tct-news-css' );
		
    }
}
add_action('admin_enqueue_scripts', 'add_custom_css_and_js_for_news');



/* List-View */


// Columns
function adjust_columns_for_news($columns){
 $columns = array(
	"cb" => "<input type=\"checkbox\"/>",
	"title" => _x('Title','post-type: news, List','transcibathon'),
	"tc_newsstart" => _x('Start-Date','post-type: news, List','transcibathon'),
	"tc_newsend" => _x('End-Date','post-type: news, List','transcibathon'),
	"tc_last_user" => _x('User','post-type: news, List','transcibathon'),
  );
  $columns['tct_connected_item_id'] = '';
  return $columns;
}
add_filter("manage_news_posts_columns", "adjust_columns_for_news");

// Content to show in custom columns
function content_of_custom_columns_for_news($column){
  global $post,$wpdb;
  $custom = get_post_custom($post->ID);
  switch ($column) {
		case "tc_newsstart":
			echo $custom['tct_news_startdate'][0];
			break;
		case "tc_newsend":
			echo $custom['tct_news_enddate'][0];
			break;
		case "tc_last_user":
			echo get_the_modified_author();
			break;
  }
}
add_action("manage_news_posts_custom_column", "content_of_custom_columns_for_news");


// Inorder to make individual sortable columns
function make_custom_columns_sortable_for_news( $columns ) {
   // $columns['xxx'] = 'xxx';
    $columns['last_modified'] = 'modified';
    $columns['story_id'] = 'story_id';
    //To make a column 'un-sortable' remove it from the array
    //unset($columns['date']);
    return $columns;
}
add_filter( 'manage_edit-news_sortable_columns', 'make_custom_columns_sortable_for_news' );


function display_custom_title_placeholder_for_news( $title ){
    $screen = get_current_screen();
  	if('news' == $screen->post_type){
        $title = _x('Enter the title of the news message','post-type: news, placeholder for title','transcibathon');
    }
    return $title;
}
add_filter('enter_title_here','display_custom_title_placeholder_for_news' );


/* WP Navigation View ------------------------------------------------------------------------------------------------------------------------------------*/


function add_custom_backend_styles_for_news(){// 1494 / 16265
	echo "<style>\n";
	// main button default
	echo "\n#adminmenu .menu-icon-news,#adminmenu .toplevel_page_nestedpages-news{ background:#".CSTL_MAIN_BG.";}\n";
	echo "\n#adminmenu .menu-icon-news a,#adminmenu .toplevel_page_nestedpages-news a{color:#".CSTL_MAIN_FONTCOLOR.";}\n";
	echo "\n#adminmenu .menu-icon-news a,#adminmenu .toplevel_page_nestedpages-news a:hover div.wp-menu-name{color:#".CSTL_HOVER_FONTCOLOR.";}\n";
	//echo "\n#adminmenu .menu-icon-news div.wp-menu-image:before,#adminmenu .toplevel_page_nestedpages-news div.wp-menu-image:before {content: '\\f488'; color:#".CSTL_MAIN_FONTCOLOR.";}\n";
	echo "\n#adminmenu .menu-icon-news div.wp-menu-image:before,#adminmenu .toplevel_page_nestedpages-news div.wp-menu-image:before {content: '\\f274'; font-family:'FontAwesome'; color:#".CSTL_MAIN_FONTCOLOR.";}\n";
	// main button hover
	echo "\n#adminmenu .menu-icon-news:hover div.wp-menu-image:before,#adminmenu .toplevel_page_nestedpages-news:hover div.wp-menu-image:before{color:#".CSTL_MAIN_FONTCOLOR." !important;}\n";
	echo "\n#adminmenu .menu-icon-news:hover div.wp-menu-name,n#adminmenu .toplevel_page_nestedpages-news:hover div.wp-menu-name{color:#".CSTL_MAIN_FONTCOLOR." !important;}\n";
	// main button: open menu
	echo "\n#adminmenu .menu-icon-news.wp-menu-open div.wp-menu-image:before,#adminmenu .toplevel_page_nestedpages-news.wp-menu-open div.wp-menu-image:before {color:#".CSTL_MAIN_OPEN_FONTCOLOR."; background:#".CSTL_MAIN_OPEN_BG."}\n"; // icon
	echo "\n#adminmenu .menu-icon-news.wp-menu-open div.wp-menu-name ,#adminmenu .toplevel_page_nestedpages-news.wp-menu-open div.wp-menu-name{color:#".CSTL_MAIN_OPEN_FONTCOLOR." !important; background:#".CSTL_MAIN_OPEN_BG.";}\n"; // font
	// flyout-menu / sub-menu
	echo "\n#adminmenu .menu-icon-news ul.wp-submenu,#adminmenu .toplevel_page_nestedpages-news ul.wp-submenu{background:#".CSTL_SUBMENU_BG." !important;}\n";
	echo "\n#adminmenu .menu-icon-news ul li,#adminmenu .toplevel_page_nestedpages-news ul li{ background:#".CSTL_SUBMENU_BG.";}\n";
	echo "\n#adminmenu .menu-icon-news ul li a,#adminmenu .toplevel_page_nestedpages ul li a{color:#".CSTL_MAIN_FONTCOLOR." !important;}\n";
	echo "\n#adminmenu .menu-icon-news ul li a:hover,#adminmenu .toplevel_page_nestedpages ul li a:hover,#adminmenu .toplevel_page_nestedpages ul li:hover a,#adminmenu .toplevel_page_nestedpages ul.wp-submenu li a:hover{color:#".CSTL_HOVER_FONTCOLOR." !important;}\n";
	echo "\n</style>\n"; 	
	
}
add_action( 'admin_head', 'add_custom_backend_styles_for_news' ); 


?>