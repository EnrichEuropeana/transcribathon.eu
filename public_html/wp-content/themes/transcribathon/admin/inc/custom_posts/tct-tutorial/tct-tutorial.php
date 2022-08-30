<?php
/*
custom post for "tutorial" (Inorder to add the POSTTYPE: 'tutorial')
*/
function tutorial_register() {
	$labels = array(
		'name' => _x('Tutorial messages','post-type: tutorial','transcribathon'),
		'singular_name' => _x('Tutorial message','post-type: tutorial','transcribathon'),
		'all_items' => _x('All tutorial','post-type: tutorial','transcribathon'),
		'add_new' => _x('Add a new tutorial message','post-type: tutorial','transcribathon'),
		'add_new_item' => _x('Add a new tutorial message','post-type: tutorial','transcribathon'),
		'edit_item' => _x('Edit tutorial message','post-type: tutorial','transcribathon'),
		'new_item' => _x('New tutorial message','post-type: tutorial','transcribathon'),
		'view_item' => _x('View tutorial message','post-type: tutorial','transcribathon'),
		'search_items' => _x('Search tutorial messages','post-type: tutorial','transcribathon'),
		'not_found' =>  _x('No fitting tutorial message found','post-type: tutorial','transcribathon'),
		'not_found_in_trash' => _x('No fitting tutorial message found in trash','post-type: tutorial','transcribathon'),
		'insert_into_item' => _x('Insert into tutorial message','post-type: tutorial','transcribathon'),
		'uploaded_to_this_item' => _x('Uploaded to this tutorial message','post-type: tutorial','transcribathon'),
		'featured_image' => _x('Tutorial image','post-type: tutorial','transcribathon'),
		'set_featured_image' => _x('Set tutorial image','post-type: tutorial','transcribathon'),
		'remove_featured_image' => _x('Remove tutorial image','post-type: tutorial','transcribathon'),
		'use_featured_image' => _x('Use as tutorial image','post-type: tutorial','transcribathon'),
		'parent_item_colon' => _x('Parent tutorial message:','post-type: tutorial','transcribathon'),
		'update_item' => _x('Update tutorial message','post-type: tutorial','transcribathon'),
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => 'tutorial',
		'menu_icon' => 'dashicons-megaphone',
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 5,
		'has_archive' => true,
		'supports' => array('title','thumbnail')
	  ); 
	register_post_type( 'tutorial' , $args );
	
}
add_action('init', 'tutorial_register');


// Init Custom fields
$custom_fields_for_tutorial = array(
	array('name' => 'tct_tutorial_order','type' => 'text'),
	
);

// Custom Fields
function add_tutorial_fields(){
    global $post,$wpdb,$custom_fields_for_tutorial;
    wp_nonce_field( plugin_basename( __FILE__ ), 'tutorial_nonce' );
    
	$custom = get_post_custom($post->ID);

	//echo "<pre>".print_r($custom,true)."</pre>";
	for($i=0; $i<sizeof($custom_fields_for_tutorial); $i++){
		if($custom_fields_for_tutorial[$i]['type'] === 'select' || $custom_fields_for_tutorial[$i]['type'] === 'text' || $custom_fields_for_tutorial[$i]['type'] === 'datetime' || $custom_fields_for_tutorial[$i]['type'] === 'date-year-possible' || $custom_fields_for_tutorial[$i]['type'] === 'conditional_select' || $custom_fields_for_tutorial[$i]['type'] === 'conditional_text' || $custom_fields_for_tutorial[$i]['type'] === 'date'){ 	
			if(isset($custom[$custom_fields_for_tutorial[$i]['name']][0]) && trim($custom[$custom_fields_for_tutorial[$i]['name']][0]) != ""){  ${'q'.$custom_fields_for_tutorial[$i]['name']} = $custom[$custom_fields_for_tutorial[$i]['name']][0];  }else{ ${'q'.$custom_fields_for_tutorial[$i]['name']} = ""; }
		}else if($custom_fields_for_tutorial[$i]['type'] === 'bool'){
			if(isset($custom[$custom_fields_for_tutorial[$i]['name']][0]) && trim($custom[$custom_fields_for_tutorial[$i]['name']][0]) != ""){  ${'q'.$custom_fields_for_tutorial[$i]['name']} = 1;  }else{ ${'q'.$custom_fields_for_tutorial[$i]['name']} = 0; }
		}
	}
    
    $x = "tct_tutorial_order"; 
    echo "<label for=\"".$x."\"><strong>"._x('Order','post-type: tutorial order in back-end','transcibathon')."</strong>:</label>\n";
    echo "<input type=\"text\" id=\"".$x."\" style=\"width:100%;\" name=\"".$x."\" value=\"".esc_html(stripslashes(${'q'.$x}))."\" onchange=\"registerCustomChanges();\" />\n<br />";

	
	echo "<label for=\"post_content\"><strong>"._x('Main tutorial content','backend: edit tutorialmessage','transcribathon')."</strong>: </label>\n";
	wp_editor( $post->post_content, 'content', $settings = array('textarea_name'=>'content') );

	echo "<input type=\"text\" name=\"custom_changes\" autocomplete=\"off\" id=\"custom_changes\" value=\"0\" />\n";
	
}


// Add custom fields
function init_tutorial_fields(){
	add_meta_box("tutorialfields", _x('Main settings','post-type: tutorial headline in back-end','transcibathon'), "add_tutorial_fields", "tutorial", "advanced", "high");
}
add_action("admin_init", "init_tutorial_fields"); 


// To include fields when saving the post
function save_custom_fields_for_tutorial(){
	global $post,$custom_fields_for_tutorial;
	
	// For security reasons
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE): return; endif; // No auto-save
	if ((isset($_POST['tutorial_nonce'])) && (!wp_verify_nonce($_POST['tutorial_nonce'],plugin_basename( __FILE__ )))): return; endif; // make sure, the data comes directly from this script
	if(!current_user_can( 'edit_post', @$post->ID )): return; endif; // make sure, the current user has the right to edit this post	
	
	// Save
	for($i=0; $i<sizeof($custom_fields_for_tutorial); $i++){
		if($custom_fields_for_tutorial[$i]['type'] === 'select' || $custom_fields_for_tutorial[$i]['type'] === 'text'){ 	
			if(isset($_POST[$custom_fields_for_tutorial[$i]['name']]) && trim($_POST[$custom_fields_for_tutorial[$i]['name']]) != ""){ update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], esc_html(stripslashes($_POST[$custom_fields_for_tutorial[$i]['name']])) );  }else{  update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], NULL );   }
		}else if($custom_fields_for_tutorial[$i]['type'] === 'bool'){
			if(isset($_POST[$custom_fields_for_tutorial[$i]['name']]) && $_POST[$custom_fields_for_tutorial[$i]['name']] == '1'){ update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], 'yes' );  }else{ update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], NULL );  }
		}else if($custom_fields_for_tutorial[$i]['type'] === 'datetime'){
			if(isset($_POST[$custom_fields_for_tutorial[$i]['name']."_date"]) && trim($_POST[$custom_fields_for_tutorial[$i]['name']."_date"]) != ""){
				if(isset($_POST[$custom_fields_for_tutorial[$i]['name']."_time"]) && trim($_POST[$custom_fields_for_tutorial[$i]['name']."_time"]) != ""){  $time = $_POST[$custom_fields_for_tutorial[$i]['name']."_time"]; }else{ $time='00:00:00'; }
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], date('Y-m-d H:i:s', strtotime($_POST[$custom_fields_for_tutorial[$i]['name']."_date"]." ".$time)));
			}else{
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], NULL);
			}
		}else if($custom_fields_for_tutorial[$i]['type'] === 'date'){
			if(isset($_POST[$custom_fields_for_tutorial[$i]['name']]) && trim($_POST[$custom_fields_for_tutorial[$i]['name']]) != ""){
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], date('Y-m-d', strtotime($_POST[$custom_fields_for_tutorial[$i]['name']])));
			}else{
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], NULL);
			}
		}else if($custom_fields_for_tutorial[$i]['type'] === 'date-year-possible'){
			if(isset($_POST[$custom_fields_for_tutorial[$i]['name']]) && trim($_POST[$custom_fields_for_tutorial[$i]['name']]) != ""){
				if(strlen(trim($_POST[$custom_fields_for_tutorial[$i]['name']]))<5){
					update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], $_POST[$custom_fields_for_tutorial[$i]['name']]);
				}else if(strlen(trim($_POST[$custom_fields_for_tutorial[$i]['name']]))>4 && strlen(trim($_POST[$custom_fields_for_tutorial[$i]['name']]))<8){
					update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], date('Y-m', strtotime($_POST[$custom_fields_for_tutorial[$i]['name']])));
				}else{
					update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], date('Y-m-d', strtotime($_POST[$custom_fields_for_tutorial[$i]['name']])));
				}
			}else{
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], NULL);
			}
		}else if($custom_fields_for_tutorial[$i]['type'] === 'conditional_select'){
			if(isset($_POST[$custom_fields_for_tutorial[$i]['name']]) && trim($_POST[$custom_fields_for_tutorial[$i]['name']]) != "" && trim($_POST[$custom_fields_for_tutorial[$i]['name']]) != trim($custom_fields_for_tutorial[$i]['critical_value'])){
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], esc_html(stripslashes($_POST[$custom_fields_for_tutorial[$i]['name']])) );
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['critical_field'], NULL );
			}else if(trim($_POST[$custom_fields_for_tutorial[$i]['name']]) == trim($custom_fields_for_tutorial[$i]['critical_value'])){
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], 'Other' );
				if(isset($_POST[$custom_fields_for_tutorial[$i]['critical_field']]) && trim($_POST[$custom_fields_for_tutorial[$i]['critical_field']]) != ""){
					update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['critical_field'], esc_html(stripslashes($_POST[$custom_fields_for_tutorial[$i]['critical_field']])) );
				}else{
					update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['critical_field'], NULL );
				}
			}else{
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['name'], NULL ); 
				update_post_meta($post->ID, $custom_fields_for_tutorial[$i]['critical_field'], NULL );
			}
		}
	}
}
add_action('save_post', 'save_custom_fields_for_tutorial');



// Add JS and CSS
function add_custom_css_and_js_for_tutorial() {
	global $pagenow,$post;
    if($pagenow == 'post.php' && "tutorial" == $post->post_type || $pagenow == 'post-new.php' && "tutorial" == $post->post_type){ 
      	wp_enqueue_media();
		wp_register_script('tct-tutorial-js', CHILD_TEMPLATE_DIR.'/admin/inc/custom_posts/js/tct-tutorial.js', array('jquery'));
		wp_enqueue_script('tct-tutorial-js');
		wp_register_style( 'tct-tutorial-css', CHILD_TEMPLATE_DIR.'/admin/inc/custom_posts/css/tct-tutorial.css');
       	wp_enqueue_style( 'tct-tutorial-css' );
		
    }
}
add_action('admin_enqueue_scripts', 'add_custom_css_and_js_for_tutorial');



/* List-View */


// Columns
function adjust_columns_for_tutorial($columns){
 $columns = array(
	"cb" => "<input type=\"checkbox\"/>",
	"title" => _x('Title','post-type: tutorial, List','transcibathon'),
	"tc_tutorialstart" => _x('Start-Date','post-type: tutorial, List','transcibathon'),
	"tc_tutorialend" => _x('End-Date','post-type: tutorial, List','transcibathon'),
	"tc_last_user" => _x('User','post-type: tutorial, List','transcibathon'),
  );
  $columns['tct_connected_item_id'] = '';
  return $columns;
}
add_filter("manage_tutorial_posts_columns", "adjust_columns_for_tutorial");

// Content to show in custom columns
function content_of_custom_columns_for_tutorial($column){
  global $post,$wpdb;
  $custom = get_post_custom($post->ID);
  switch ($column) {
		case "tc_tutorialstart":
			echo $custom['tct_tutorial_startdate'][0];
			break;
		case "tc_tutorialend":
			echo $custom['tct_tutorial_enddate'][0];
			break;
		case "tc_last_user":
			echo get_the_modified_author();
			break;
  }
}
add_action("manage_tutorial_posts_custom_column", "content_of_custom_columns_for_tutorial");


// Inorder to make individual sortable columns
function make_custom_columns_sortable_for_tutorial( $columns ) {
   // $columns['xxx'] = 'xxx';
    $columns['last_modified'] = 'modified';
    $columns['story_id'] = 'story_id';
    //To make a column 'un-sortable' remove it from the array
    //unset($columns['date']);
    return $columns;
}
add_filter( 'manage_edit-tutorial_sortable_columns', 'make_custom_columns_sortable_for_tutorial' );


function display_custom_title_placeholder_for_tutorial( $title ){
    $screen = get_current_screen();
  	if('tutorial' == $screen->post_type){
        $title = _x('Enter the title of the tutorial message','post-type: tutorial, placeholder for title','transcibathon');
    }
    return $title;
}
add_filter('enter_title_here','display_custom_title_placeholder_for_tutorial' );


/* WP Navigation View ------------------------------------------------------------------------------------------------------------------------------------*/


function add_custom_backend_styles_for_tutorial(){// 1494 / 16265
	echo "<style>\n";
	// main button default
	echo "\n#adminmenu .menu-icon-tutorial,#adminmenu .toplevel_page_nestedpages-tutorial{ background:#".CSTL_MAIN_BG.";}\n";
	echo "\n#adminmenu .menu-icon-tutorial a,#adminmenu .toplevel_page_nestedpages-tutorial a{color:#".CSTL_MAIN_FONTCOLOR.";}\n";
	echo "\n#adminmenu .menu-icon-tutorial a,#adminmenu .toplevel_page_nestedpages-tutorial a:hover div.wp-menu-name{color:#".CSTL_HOVER_FONTCOLOR.";}\n";
	//echo "\n#adminmenu .menu-icon-tutorial div.wp-menu-image:before,#adminmenu .toplevel_page_nestedpages-tutorial div.wp-menu-image:before {content: '\\f488'; color:#".CSTL_MAIN_FONTCOLOR.";}\n";
	echo "\n#adminmenu .menu-icon-tutorial div.wp-menu-image:before,#adminmenu .toplevel_page_nestedpages-tutorial div.wp-menu-image:before {content: '\\f274'; font-family:'FontAwesome'; color:#".CSTL_MAIN_FONTCOLOR.";}\n";
	// main button hover
	echo "\n#adminmenu .menu-icon-tutorial:hover div.wp-menu-image:before,#adminmenu .toplevel_page_nestedpages-tutorial:hover div.wp-menu-image:before{color:#".CSTL_MAIN_FONTCOLOR." !important;}\n";
	echo "\n#adminmenu .menu-icon-tutorial:hover div.wp-menu-name,n#adminmenu .toplevel_page_nestedpages-tutorial:hover div.wp-menu-name{color:#".CSTL_MAIN_FONTCOLOR." !important;}\n";
	// main button: open menu
	echo "\n#adminmenu .menu-icon-tutorial.wp-menu-open div.wp-menu-image:before,#adminmenu .toplevel_page_nestedpages-tutorial.wp-menu-open div.wp-menu-image:before {color:#".CSTL_MAIN_OPEN_FONTCOLOR."; background:#".CSTL_MAIN_OPEN_BG."}\n"; // icon
	echo "\n#adminmenu .menu-icon-tutorial.wp-menu-open div.wp-menu-name ,#adminmenu .toplevel_page_nestedpages-tutorial.wp-menu-open div.wp-menu-name{color:#".CSTL_MAIN_OPEN_FONTCOLOR." !important; background:#".CSTL_MAIN_OPEN_BG.";}\n"; // font
	// flyout-menu / sub-menu
	echo "\n#adminmenu .menu-icon-tutorial ul.wp-submenu,#adminmenu .toplevel_page_nestedpages-tutorial ul.wp-submenu{background:#".CSTL_SUBMENU_BG." !important;}\n";
	echo "\n#adminmenu .menu-icon-tutorial ul li,#adminmenu .toplevel_page_nestedpages-tutorial ul li{ background:#".CSTL_SUBMENU_BG.";}\n";
	echo "\n#adminmenu .menu-icon-tutorial ul li a,#adminmenu .toplevel_page_nestedpages ul li a{color:#".CSTL_MAIN_FONTCOLOR." !important;}\n";
	echo "\n#adminmenu .menu-icon-tutorial ul li a:hover,#adminmenu .toplevel_page_nestedpages ul li a:hover,#adminmenu .toplevel_page_nestedpages ul li:hover a,#adminmenu .toplevel_page_nestedpages ul.wp-submenu li a:hover{color:#".CSTL_HOVER_FONTCOLOR." !important;}\n";
	echo "\n</style>\n"; 	
	
}
add_action( 'admin_head', 'add_custom_backend_styles_for_tutorial' ); 


?>