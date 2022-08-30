<?php
if (!defined('ABSPATH')) die('No direct access.');

/**
 * Vimeo Slide
 */
class MetaVimeoSlide extends MetaSlide {

    public $identifier = "vimeo"; // should be lowercase, one word (use underscores if needed)
    public $name;

    /**
     * Register slide type
     */
    public function __construct() {
        $this->name = __("Vimeo", 'ml-slider-pro');

        if (is_admin()) {
            add_filter("media_upload_tabs", array($this, 'custom_media_upload_tab_name'), 999, 1);
            add_action("media_upload_{$this->identifier}", array($this, 'get_iframe'));
            add_action("wp_ajax_create_{$this->identifier}_slide", array($this, 'ajax_create_slide'));
            add_action("metaslider_register_admin_styles", array($this, 'register_admin_styles'), 10, 1);
        }
		
		add_action("metaslider_save_{$this->identifier}_slide", array($this, 'save_slide'), 5, 3);
        add_filter("metaslider_get_{$this->identifier}_slide", array($this, 'get_slide'), 10, 2);

    }

    /**
     * Custom CSS Styling for vimeo slides
     */
    public function register_admin_styles() {

        wp_enqueue_style( "metasliderpro-{$this->identifier}-style", plugins_url( 'assets/style.css' , __FILE__ ), false, METASLIDERPRO_VERSION );

    }

    /**
     * Extract the slide setings
     *
     * @param integer $id Slide ID
     */
    public function set_slide( $id ) {

        parent::set_slide( $id );
        $this->slide_settings = get_post_meta( $id, 'ml-slider_settings', true );

    }

    /**
     * Add extra tabs to the default wordpress Media Manager iframe
     *
     * @param array $tabs existing media manager tabs
     * @return array
     */
    public function custom_media_upload_tab_name( $tabs ) {

        // restrict our tab changes to the MetaSlider plugin page
        if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'metaslider' ) ||
            ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array( $this->identifier ) ) ) ) {

            $newtabs = array(
                $this->identifier => $this->name
            );

            return array_merge( $tabs, $newtabs );
        }

        return $tabs;

    }

    /**
     * Create a new slide and echo the admin HTML
     */
    public function ajax_create_slide() {

        $slider_id = intval( $_POST['slider_id'] );
        $fields['menu_order'] = 9999;
        $fields['video_id'] = $_POST['video_id'];
        $this->create_slide( $slider_id, $fields );
        echo $this->get_admin_slide();
        die(); // this is required to return a proper result

    }

    /**
     * Media Manager tab
     */
    public function vimeo_tab() {

        return $this->get_iframe();

    }

    /**
     * Get the thumbnail URL from the vimeo API
     *
     * @param string $id URL ID
     * @return array|bolean
     */
    private function get_thumb_url( $id ) {

        $thumb = new WP_Http();
        $thumb = $thumb->request( "http://vimeo.com/api/v2/video/$id.php" );

        if ( !is_wp_error( $thumb ) && isset( $thumb['body'] ) ) {
            $body = unserialize( $thumb['body'] );

            if ( isset( $body[0]['thumbnail_medium'] ) ) {
                return $body[0]['thumbnail_medium'];
            }
        }

        return false;

    }

    /**
     * Create a new vimeo slide
     *
     * @param integer $slider_id Slider ID
     * @param array   $fields    Array of slide fields
     * @return integer
     */
    public function create_slide( $slider_id, $fields ) {

        $this->set_slider( $slider_id );

        $postinfo = array(
            'post_title'=> "MetaSlider - Vimeo Thumbnail - {$fields['video_id']}",
            'post_mime_type' => 'image/jpeg',
            'post_status' => 'inherit',
            'post_content' => '',
            'guid' => "http://www.vimeo.com/{$fields['video_id']}",
            'menu_order' => $fields['menu_order'],
            'post_name' => $fields['video_id']
        );

        $thumb_url = $this->get_thumb_url( $fields['video_id'] );
        $vimeo_thumb = false;

        if ( $thumb_url ) {
            $vimeo_thumb = new WP_Http();
            $vimeo_thumb = $vimeo_thumb->request( $thumb_url );
        }

        if ( !$vimeo_thumb || is_wp_error( $vimeo_thumb ) || $vimeo_thumb['response']['code'] != 200 ) {
            $slide_id = wp_insert_attachment( $postinfo );
        } else {
            $attachment = wp_upload_bits( "vimeo_{$fields['video_id']}.jpg", null, $vimeo_thumb['body'] );
            $filename = $attachment['file'];
            $slide_id = wp_insert_attachment( $postinfo, $filename );
            $attach_data = wp_generate_attachment_metadata( $slide_id, $filename );
            wp_update_attachment_metadata( $slide_id, $attach_data );
        }

        if ( method_exists( $this, 'insert_slide' ) ) {
            $slide_id = $this->insert_slide($slide_id, $this->identifier, $slider_id);
            $this->add_or_update_or_delete_meta( $slide_id, 'vimeo_url', "http://www.vimeo.com/{$fields['video_id']}");
        } else {
            $this->add_or_update_or_delete_meta( $slide_id, 'type', $this->identifier );
        }
        // store the type as a meta field against the attachment
        $this->set_slide( $slide_id );
        $this->tag_slide_to_slider();

        return $slide_id;

    }

    /**
     * Admin slide html
     *
     * @return string html
     */
    protected function get_admin_slide() {

        $thumb = "";

        // only show a thumbnail if we managed to download one when the slide was created
        if ( get_post_thumbnail_id( $this->slide->ID ) ) {// new slide format
            $thumb = $this->get_thumb();
        } else if ( strlen( get_attached_file( $this->slide->ID ) ) ) {
            $thumb = $this->get_thumb();
        }

        ob_start();
        echo $this->get_delete_button_html();
        echo $this->get_update_image_button_html();
        do_action('metaslider-slide-edit-buttons', $this->identifier, $this->slide->ID);
        $edit_buttons = ob_get_clean();

        $row  = "<tr id='slide-{$this->slide->ID}' class='slide {$this->identifier} flex responsive'>";
        $row .= "    <td class='col-1'>";
        $row .= "       <div class='metaslider-ui-controls ui-sortable-handle'>";
        $row .= "           <h4 class='slide-details'><span class='vimeo'>Vimeo Slide</span></h4>";
        if (metaslider_this_is_trash($this->slide)) {
            $row .= '<div class="row-actions trash-btns">';
            $row .= "<span class='untrash'>{$this->get_undelete_button_html()}</span>";
            // $row .= ' | ';
            // $row .= "<span class='delete'>{$this->get_perminant_delete_button_html()}</span>";
            $row .= '</div>';
        } else {
            $row .= $edit_buttons;
        }
        $row .= "       </div>";
        $row .= "       <div class='metaslider-ui-inner'>";
        $row .= "           <button class='update-image image-button' data-button-text='" . __("Update slide image", "ml-slider") . "' title='" . __("Update slide image", "ml-slider") . "' data-slide-id='{$this->slide->ID}'>";
        $row .= "           <div class='thumb' style='background-image: url({$thumb})'></div>";
        $row .= "           </button>";
        $row .= "       </div>";
        $row .= "    </td>";
        $row .= "    <td class='col-2'>";
        $row .= "       <div class='metaslider-ui-inner flex flex-col h-full'>";

        if ( method_exists( $this, 'get_admin_slide_tabs_html' ) ) {
            $row .= $this->get_admin_slide_tabs_html();
        } else {
            $row .= "<p>" . __("Please update to MetaSlider to version 3.2 or above.", "ml-slider-pro") . "</p>";
        }

        $row .= "        <input type='hidden' name='attachment[{$this->slide->ID}][type]' value='vimeo' />";
        $row .= "        <input type='hidden' class='menu_order' name='attachment[{$this->slide->ID}][menu_order]' value='{$this->slide->menu_order}' />";
        $row .= "       </div>";
        $row .= "    </td>";
        $row .= "</tr>";

        return $row;

    }


    /**
     * Build an array of tabs and their titles to use for the admin slide.
     */
    public function get_admin_tabs() {

        $slide_id = absint($this->slide->ID);
        $byline_checked = isset($this->slide_settings['byline']) && filter_var($this->slide_settings['byline'], FILTER_VALIDATE_BOOLEAN) ? 'checked=checked' : '';
        $portrait_checked = isset($this->slide_settings['portrait']) && filter_var($this->slide_settings['portrait'], FILTER_VALIDATE_BOOLEAN)? 'checked=checked' : '';
        $title_checked = isset($this->slide_settings['title']) && filter_var($this->slide_settings['title'], FILTER_VALIDATE_BOOLEAN)? 'checked=checked' : '';
        $autoPlay_checked = isset($this->slide_settings['autoPlay']) && filter_var($this->slide_settings['autoPlay'], FILTER_VALIDATE_BOOLEAN)? 'checked=checked' : '';
		$mute_checked = isset($this->slide_settings['mute']) && filter_var($this->slide_settings['mute'], FILTER_VALIDATE_BOOLEAN) ? 'checked=checked' : '';
        $loop_checked = isset($this->slide_settings['loop']) && filter_var($this->slide_settings['loop'], FILTER_VALIDATE_BOOLEAN) ? 'checked=checked' : '';
		$video_url = get_post_meta($slide_id, 'ml-slider_vimeo_url', true);

		$general_tab = "<input style='padding:7px 10px;max-width:500px' class='ms-super-wide' name='attachment[{$slide_id}][vimeo_url]' value='{$video_url}'>";
        $general_tab .= "<ul class='ms-split-li'>
                            <li><label><input type='checkbox' name='attachment[{$slide_id}][settings][title]' {$title_checked}/><span>" . __('Show the title on the video', 'ml-slider-pro') ."</span></label></li>
                            <li><label><input type='checkbox' name='attachment[{$slide_id}][settings][byline]' {$byline_checked}/><span>" . __('Show the user byline on the video', 'ml-slider-pro') ."</span></label></li>
                            <li><label><input type='checkbox' name='attachment[{$slide_id}][settings][portrait]' {$portrait_checked}/><span>" . __('Show the user portrait on the video', 'ml-slider-pro') ."</span></label></li>
                            <li><label><input type='checkbox' name='attachment[{$slide_id}][settings][autoPlay]' {$autoPlay_checked}/><span>" . __('Auto play (may require video to be muted)&lrm;', 'ml-slider-pro') ."</span></label></li>
                            <li><label><input type='checkbox' name='attachment[{$slide_id}][settings][mute]' {$mute_checked}/><span>" . __('Mute', 'ml-slider-pro') ."</span></label></li>
                            <li><label><input type='checkbox' name='attachment[{$slide_id}][settings][loop]' {$loop_checked}/><span>" . __('Loop video', 'ml-slider-pro') ."</span></label></li>
                        </ul>"; // vantage backwards compatibility";

        $tabs = array(
            'general' => array(
                'title' => __("General", "ml-slider-pro"),
                'content' => $general_tab
            )
        );

        return apply_filters("metaslider_vimeo_slide_tabs", $tabs, $this->slide, $this->slider, $this->settings);
    }

    /**
     * Public slide html
     *
     * @return string html
     */
    protected function get_public_slide() {

        wp_enqueue_script('metasliderpro-vimeo-api', METASLIDERPRO_BASE_URL . 'node_modules/@vimeo/player/dist/player.js', array(), METASLIDERPRO_VERSION);

        $settings = get_post_meta($this->slider->ID, 'ml-slider_settings', true);

        if (get_post_meta($this->slide->ID, 'ml-slider_vimeo_url', true)) {
            $url = get_post_meta($this->slide->ID, 'ml-slider_vimeo_url', true);
        } else {
            $url = $this->slide->guid;
        }

        sscanf( parse_url( $url, PHP_URL_PATH ), '/%d', $video_id ); // get the video ID

        if (!(int)$this->settings['height'] || !(int)$this->settings['width']) {
            $ratio = 9 / 16 * 100;
        } else {
            $ratio = $this->settings['height'] / $this->settings['width'] * 100;
        }


        if ( $this->settings['type'] == 'responsive' ) {

            add_filter( 'metaslider_responsive_slider_parameters', array( $this, 'get_responsive_slider_parameters' ), 10, 2 );
            add_filter( 'metaslider_responsive_slider_javascript', array( $this, 'get_responsive_vimeo_javascript' ), 10, 2 );

            return $this->get_responsive_slides_markup( $video_id, $settings, $ratio );
        }

        if ( $this->settings['type'] == 'flex' ) {

            add_filter( 'metaslider_flex_slider_parameters', array( $this, 'get_flex_slider_parameters' ), 10, 2 );
            add_filter( 'metaslider_flex_slider_javascript', array( $this, 'get_flex_vimeo_javascript' ), 10, 2 );

            return $this->get_flex_slider_markup( $video_id, $settings, $ratio );
        }
    }

    /**
     * Build the Vimeo iFrame URL based on the slide settings
     *
     * @param integer $video_id Video ID
     * @return string
     */
    public function get_vimeo_iframe_url($video_id) {

        $url = "https://player.vimeo.com/video/{$video_id}?player_id=vimeo_{$this->slide->ID}";

        foreach(array('byline', 'title', 'portrait', 'loop') as $param) {
			$param_value = isset($this->slide_settings[$param]) ? $this->slide_settings[$param] : 0;
			$param_value = filter_var($param_value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

			$url .= "&{$param}={$param_value}";
        }

        // this filter applied before HTTPS was added directly to the url
        // $url = apply_filters( 'metaslider_vimeo_params', $url, $this->slider->ID, $this->slide->ID );
        $url = apply_filters('metaslider_vimeo_url', $url, $this->slider->ID, $this->slide->ID);

        return $url;
    }

    /**
     * Return the slide HTML
     *
     * @param integer $video_id Video ID
     * @param array   $settings Slider settings
     * @param float   $ratio    Slider ratio
     * @return string
     */
    public function get_responsive_slides_markup( $video_id, $settings, $ratio ) {

        $url = $this->get_vimeo_iframe_url( $video_id );
		$autoPlay = isset($this->slide_settings['autoPlay'] ) && $this->slide_settings['autoPlay'] == 'on' ? '1' : '0';
		$muted = isset($this->slide_settings['mute']) ? (int)filter_var($this->slide_settings['mute'], FILTER_VALIDATE_BOOLEAN) : 0;

        $html  = sprintf("<div style='position:relative;padding-bottom:%s%%;height:0'>", $ratio);
        $html .= "<iframe class='vimeo' data-muted='{$muted}' data-vimeo-autoplay='{$autoPlay}' id='vimeo_{$this->slide->ID}' data-vimeo-width='{$settings['width']}' data-vimeo-height='{$settings['height']}' width='{$settings['width']}' height='{$settings['height']}' src='{$url}' frameborder='0' allowfullscreen></iframe>";
        $html .= "</div>";

        return $html;
    }

    /**
     * Return the slide HTML
     *
     * @param integer $video_id Video ID
     * @param array   $settings Slider settings
     * @param float	  $ratio    Slider ratio
     * @return string
     */
    public function get_flex_slider_markup($video_id, $settings, $ratio) {

        $url = $this->get_vimeo_iframe_url($video_id);
		$autoPlay = isset($this->slide_settings['autoPlay']) ? (int) filter_var($this->slide_settings['autoPlay'], FILTER_VALIDATE_BOOLEAN) : 0;
        $muted = isset($this->slide_settings['mute']) ? (int) filter_var($this->slide_settings['mute'], FILTER_VALIDATE_BOOLEAN) : 0;

		$html = sprintf("<div style='position:relative;padding-bottom:%s%%;height:0'>", $ratio);
        $html .= "<iframe class='vimeo' data-muted='{$muted}' data-vimeo-autoplay='{$autoPlay}' id='vimeo_{$this->slide->ID}' data-vimeo-width='{$settings['width']}' data-vimeo-height='{$settings['height']}' width='{$settings['width']}' height='{$settings['height']}' src='{$url}' frameborder='0' allowfullscreen></iframe>";
        $html .= "</div>";

        // store the slide details
        $attributes = array(
            'class' => "slide-{$this->slide->ID} ms-vimeo",
            'style' => "display: none; width: 100%;"
        );

        $attributes = apply_filters( 'metaslider_flex_slider_li_attributes', $attributes, $this->slide->ID, $this->slider->ID, $this->settings );

        $slide = "<li";
        foreach ( $attributes as $att => $val ) {
			$val = esc_attr($val);
            $slide .= " {$att}='{$val}'";
        }
        $slide .= ">{$html}</li>";
		
		return $slide;
    }

    /**
     * JavaScript to handle video interaction
     *
     * @param string  $javascript Video Javascript
     * @param integer $slider_id  Slider ID
     * @return string
     */
    public function get_flex_vimeo_javascript($javascript, $slider_id)  {

		// This is for the slideshow autoplay, not the video autoplay.
        // This will play the slideshow when the video is paused
        $autoplay = filter_var($this->settings['autoPlay'], FILTER_VALIDATE_BOOLEAN) ?
            "player.on('ended', function() {
				$('#metaslider_{$slider_id}').data('flexslider').manualPause = false;
            	$('#metaslider_{$slider_id}').flexslider('next');
            	$('#metaslider_{$slider_id}').flexslider('play');
			});
			player.on('pause', function() {
				$('#metaslider_{$slider_id}').data('flexslider').manualPause = false;
			})" : '';

		// Pause the slideshow if the options are to autoplay and mute the video,
		// It's paused more liberally later on when play event is fired, but this 
		// is more reliable if the Vimeo API is slow. This also mutes the video if needed
		$html = "$('#metaslider_{$slider_id} iframe.vimeo').each(function(i) {

			if ($(this).closest('li').hasClass('flex-active-slide')) {
				if ($(this).data('vimeoAutoplay') && $(this).data('muted')) {
					$('#metaslider_{$slider_id}').flexslider('pause');
				}
			}

			var video = document.getElementById($(this).attr('id'));
			window['player_{$slider_id}_' + $(this).attr('id')] = new Vimeo.Player(video);

			var player = window['player_{$slider_id}_' + $(this).attr('id')];
			$(this).data('muted') && player.setVolume(0);
			
			{$autoplay}
		});";
		
        // we don't want this filter hanging around if there's more than one slideshow on the page
        remove_filter('metaslider_flex_slider_javascript', array($this, 'get_flex_vimeo_javascript'));

        return $javascript . $html;
    }

    /**
     * Modify the flex slider parameters when a vimeo slide has been added
     *
     * @param array   $options   Slider options
     * @param integer $slider_id Slider ID
     * @return array
     */
	public function get_flex_slider_parameters($options, $slider_id) {

		$options['useCSS'] = 'false';

		// Before a slide transitions
		$options['before'] = isset($options['before']) ? $options['before'] : array();
		$options['before'] = array_merge($options['before'], array(
			"$('#metaslider_{$slider_id} iframe.vimeo').each(function(index) {
				var player = window['player_{$slider_id}_' + $(this).attr('id')];
				player.pause();
			});"
		));

		// After a slide transitions
		$options['after'] = isset($options['after']) ? $options['after'] : array();
		$options['after'] = array_merge($options['after'], array(
			"$('#metaslider_{$slider_id} .flex-active-slide iframe.vimeo[data-vimeo-autoplay=1]').each(function(index) {
				var player = window['player_{$slider_id}_' + $(this).attr('id')];
				$(this).data('vimeoAutoplay') && player.play();
			});"
		));

		// When the slideshow is loaded
		$options['start'] = isset($options['start']) ? $options['start'] : array();
		$options['start'] = array_merge($options['start'], array(
			"$('#metaslider_{$slider_id} iframe.vimeo').each(function() {
				var autoplay = false;
				var player = window['player_{$slider_id}_' + $(this).attr('id')];
				
				if ($(this).data('vimeoAutoplay')) {
					if ($(this).parents('.flex-active-slide').length) {
						autoplay = true;
					}
				}
				player.on('loaded', function() {
					autoplay && player.play();
				});
				player.on('play', function() {
					$('#metaslider_{$slider_id}').flexslider('pause');
					$('#metaslider_{$slider_id}').data('flexslider').manualPause = true;
					$('#metaslider_{$slider_id}').data('flexslider').manualPlay = false;
				});
			});"
		));

        // We don't want this filter hanging around if there's more than one slideshow on the page
        remove_filter('metaslider_flex_slider_parameters', array($this, 'get_flex_slider_parameters'));
        return $options;
    }

    /**
     * JavaScript to handle video interaction
     *
     * @param string  $javascript Vimeo Javascript
     * @param integer $slider_id  Slider ID
     * @return string
     */
    public function get_responsive_vimeo_javascript($javascript, $slider_id) {

        $autoplay = filter_var($this->settings['autoPlay'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';

		$html = "$('#metaslider_{$slider_id} iframe.vimeo').each(function() {
				var autoplay = false;
				var vimeo = $(this);
				var video = document.getElementById(vimeo.attr('id'));
				window['player_{$slider_id}_' + vimeo.attr('id')] = new Vimeo.Player(video);
				var player = window['player_{$slider_id}_' + vimeo.attr('id')];

				/* Set to mute */
				vimeo.data('muted') && player.setVolume(0);
				
				if ({$autoplay}) {
					if (vimeo.parents('.rslides1_on').length) {

						/* When video loads */
						player.on('loaded', function () {
							player.play();
						});

						/* When video ends */
						player.on('ended', function() {
							vimeo.trigger('mouseleave');
						});

						/* When video is paused manually  */
						player.on('pause', function() {
							/* I think the best approach is to just do nothing */
							/* The slideshow will autoplay if the mouse leaves */
							/* If more functionality is needed, use FlexSlider */
						});

						/* If first slide, pause slideshow before loaded event
						if (vimeo.closest('li').hasClass('rslides1_on')) {

							/* ... only if muted & autoplay */
							if (vimeo.data('vimeoAutoplay') && vimeo.data('muted')) {
								vimeo.trigger('mouseenter');
							}
						}

						/* Pause the slideshow */
						player.on('play', function () {
							vimeo.trigger('mouseenter');
						});
				}
			});";

        // we don't want this filter hanging around if there's more than one slideshow on the page
        remove_filter( 'metaslider_responsive_slider_javascript', array( $this, 'get_responsive_vimeo_javascript' ) );

        return $javascript . $html;

    }

    /**
     * Modify the reponsive slider parameters when a vimeo slide has been added
     *
     * @param  array  $options   Slider options
     * @param  string $slider_id Slider ID
     * @return array
     */
    public function get_responsive_slider_parameters($options, $slider_id) {
		
		// If autoslide is true for the slideshow, we have to fake it
		// trigger('mouseenter') and trigger('mouseleave')
		if (filter_var($this->settings['autoPlay'], FILTER_VALIDATE_BOOLEAN)) {
			$options["pause"] = 'true';
		}
		
		$options['before'] = isset($options['before']) ? $options['before'] : array();
		$options['before'] = array_merge($options['before'], array(
			"$('#metaslider_{$slider_id} iframe.vimeo').each(function(index) {
				var player = window['player_{$slider_id}_' + $(this).attr('id')];
				player.pause();
			});"
		));

		$options['after'] = isset($options['after']) ? $options['after'] : array();
		$options['after'] = array_merge($options['after'], array(
			"$('#metaslider_{$slider_id} .rslides1_on iframe.vimeo[data-vimeo-autoplay=1]').each(function(index) {
				var player = window['player_{$slider_id}_' + $(this).attr('id')];
				$(this).data('vimeoAutoplay') && player.play();
			});"
		));

        // We don't want this filter hanging around if there's more than one slideshow on the page
		remove_filter('metaslider_responsive_slider_parameters', array($this, 'get_responsive_slider_parameters'));
		return $options;
    }

    /**
     * Return wp_iframe
     */
    public function get_iframe() {

        return wp_iframe( array( $this, 'iframe' ) );

    }

    /**
     * Media Manager iframe HTML
     */
    public function iframe() {

        do_action("metaslider_vimeo_iframe");

        wp_enqueue_style( 'media-views' );
        wp_enqueue_style( "metasliderpro-{$this->identifier}-styles", plugins_url( 'assets/style.css' , __FILE__ ), false, METASLIDERPRO_VERSION );
        wp_enqueue_script( "metasliderpro-{$this->identifier}-script", plugins_url( 'assets/script.js' , __FILE__ ), array( 'jquery' ), METASLIDERPRO_VERSION );
        wp_localize_script( "metasliderpro-{$this->identifier}-script", 'metaslider_custom_slide_type', array(
                'identifier' => $this->identifier,
                'name' => $this->name
            ) );

        echo "<div class='metaslider'>
                <div class='vimeo'>
                    <div class='media-embed'>
                        <label class='embed-url'>
                            <input type='text' placeholder='' class='vimeo_url ms-super-wide'>
                            <span class='spinner'></span>
                        </label>
                        <div class='embed-link-settings'></div>
                    </div>
                </div>
            </div>
            <div class='media-frame-toolbar'>
                <div class='media-toolbar'>
                    <div class='media-toolbar-primary'>
                        <a href='#' class='button media-button button-primary button-large' disabled='disabled'>" . __("Add to slideshow", "ml-slider-pro") . "</a>
                    </div>
                </div>
            </div>";

    }

    /**
     * Save
     *
     * @param array $fields Fields for adding to menu
     */
    protected function save($fields) {

		// Save the url in case it was updated
		if (isset($fields['vimeo_url']) && !empty($fields['vimeo_url'])) {
			update_post_meta($this->slide->ID, 'ml-slider_vimeo_url', $fields['vimeo_url']);
		}

		// Update the order
        wp_update_post(array(
			'ID' => $this->slide->ID,
			'menu_order' => $fields['menu_order']
		));

		foreach (array('lazyLoad', 'showControls', 'showRelated', 'showInfo', 'mute', 'loop') as $setting) {
			if (!isset($fields['settings'][$setting])) {
				$fields['settings'][$setting] = 'off';
			}
		}

		// Save all the settings fields serialized
        if (isset($fields['settings'])) {
            $this->add_or_update_or_delete_meta($this->slide->ID, 'settings', $fields['settings']);
        }
    }
}
