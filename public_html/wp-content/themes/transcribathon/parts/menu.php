<?php
/**
 * Part Name: Default Menu
 */

$ubermenu_active = function_exists( 'ubermenu' );
$max_mega_menu_active = function_exists( 'max_mega_menu_is_enabled' ) && max_mega_menu_is_enabled( 'primary' );
$nav_classes = array( 'site-navigation' );
if ( ! $ubermenu_active && ! $max_mega_menu_active ) {
	$nav_classes[] = 'main-navigation';
}
$nav_classes[] = 'primary';

if ( siteorigin_setting( 'navigation_use_sticky_menu' ) ) {
	$nav_classes[] = 'use-sticky-menu';
}

if ( siteorigin_setting( 'navigation_mobile_navigation' ) ) {
	$nav_classes[] = 'mobile-navigation';
}
$logo_in_menu = siteorigin_setting( 'layout_masthead' ) == 'logo-in-menu';
?>
<nav role="navigation" class="<?php echo implode( ' ', $nav_classes) ?>">
    <div class="_transcribathon_mainnav">
	<!--Updated Navbar -->

        <?php
            // Allways home of transcribathon
	    	$theme_sets = get_theme_mods();
	    	//echo "<div class='nav-left-side'>";
	    	echo "<a href=\"".network_home_url()."\" class=\"_transcribathon_logo\"></a>";
	    	if(!is_main_site()) {
	    		if(!is_home()){
	    			echo "<a href=\"".get_home_url()."\" class=\"_transcribathon_partnerlogo\" id=\"_transcribathon_partnerlogo\" >"; vantage_display_logo(); echo "</a>";
	    		}else{
	    			echo "<span class=\"_transcribathon_partnerlogo\" id=\"_transcribathon_partnerlogo\">"; vantage_display_logo(); echo "</span>";
	    		}
	    	}
			//echo "</div>";
			//echo "<div class='nav-right-side'>";

	    echo "\n<ul id=\"_transcribathon_topmenu\" class=\"menu\">\n";
	    	echo "<li><a href=\"".network_home_url()."contact/\" class=\"contact-area\">Contact Us</a></li>";
	    	echo "<li id=\"projects\" class=\"menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children projects\">Projects\n";
	    		$sites = get_sites(array('site__not_in'=>array('1'),'deleted'=>0));
	    		echo "<ul class=\"sub-menu\" style=\"display: none; opacity: 0;\">\n";
	    			$i=1;
	    			foreach($sites as $s){
	    				echo "<li id=\"projects-".$i."\" class=\"menu-item menu-item-type-post_type menu-item-object-page projects-".$i." top_nav_point-".$s->blog_id."\"><a href=\"https://".$s->domain.$s->path."\">".get_blog_details($s->blog_id)->blogname."</a></li>\n";
	    				$i++;
	    			}
	    		echo "</ul>\n";
	    	echo "</li>\n";
	    	//echo "<li id=\"projects\" class=\"menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children projects\">\n";
	    	//echo "</li>\n";
	    	if (is_user_logged_in()){
	    		echo "<li id=\"account-menu\" class=\"menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children\">".wp_get_current_user()->user_login."\n";
	    			echo "<ul class=\"sub-menu\" style=\"display: none; opacity: 0;\">\n";
	    				echo "<li id=\"account\" class=\"menu-item menu-item-type-post_type menu-item-object-page account-menu-item\"><a href=\"".network_home_url()."account\">Account</a></li>\n";
	    				echo "<li id=\"profile\" class=\"menu-item menu-item-type-post_type menu-item-object-page account-menu-item\"><a href=\"".network_home_url()."profile\">Profile</a></li>\n";
	    				echo "<li id=\"logout\" class=\"menu-item menu-item-type-post_type menu-item-object-page account-menu-item\"><a href=\"".network_home_url()."logout\">Logout</a></li>\n";
	    			echo "</ul>\n";
	    		echo "</li>\n";
	    		}
	    	else {
	    		echo "<li id=\"register\" class=\"menu-item menu-item-type-post_type menu-item-object-page\">\n";
	    			echo "<a href=\"".network_home_url()."register/ \">Register</a>";
	    		echo "</li>\n";
	    		echo "<li id=\"default-lock-login\" class=\"menu-item menu-item-type-post_type menu-item-object-page\">\n";
	    			echo "<a id=\"login\" href=\"#\">Login</a>";
	    		echo "</li>\n";
	    	}
			echo "<li class='menu-item' style='top:3px;'><a href='".get_europeana_url()."/tutorial-english/'><i class=\"fal fa-question-circle\" style='font-size:20px;bottom:10px;'></i></a></li>";

	//echo "<li>";
	// echo '<li id="help-tutorials" class="help-tutorials">';
	// echo '<a href="#" class="tutorial-model" title="Tutorial"><i class="fal fa-question-circle"></i></a>';
	// echo '</li>';
	//echo "</li>";
	echo "</ul>\n";


	// Login modal
	echo '<div id="default-login-container">';
		echo '<div id="default-login-popup">';
			echo '<div class="default-login-popup-header">';
			    echo '<span class="login-title"> LOGIN </span>';
				echo '<span class="item-login-close">&times;</span>';
			echo '</div>';
			echo '<div class="default-login-popup-body">';
				$login_post = get_posts( array(
					'name'    => 'default-login',
					'post_type'    => 'um_form',
				));
				echo do_shortcode('[ultimatemember form_id="'.$login_post[0]->ID.'"]');
			echo '</div>';
			echo '<div class="default-login-popup-footer">';
			echo '</div>';
		echo '</div>';
	echo '</div>';



// Help tab
     ?>
    </div>

	<!-- <div class="full-container">-->
		<?php if($logo_in_menu) : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="logo"><?php vantage_display_logo(); ?></a>
		<?php endif; ?>


		<?php if( $ubermenu_active ): ?>
			<?php ubermenu( 'main' , array( 'theme_location' => 'primary' ) ); ?>
		<?php else: ?>
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'link_before' => '<span class="icon"></span>' ) ); ?>
		<?php endif; ?>
		<div id='search-form'>
			<form action='<?php echo get_europeana_url(); ?>/documents/' method='get'>
				<input type='text' id='storySearch' name='q' placeholder='search' class='search-text'>
				<input type="submit" value="🔎︎" class='search-submit theme-color-background' style='color:white;'>
		    </form>
		</div>

	<!--</div>-->
</nav><!-- .site-navigation .main-navigation -->
