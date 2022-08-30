<?php
/**
 * Instance counter class.
 *
 * @since      1.0.0
 *
 * @package    Display_Post_Types
 */

namespace Display_Post_Types\Frontend\Inc;

/**
 * Instance counter.
 *
 * @package    Display_Post_Types
 * @author     vedathemes <contact@vedathemes.com>
 */
class Instance_Counter {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    object
	 */
	protected static $instance = null;

	/**
	 * Podcast instance counter.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    int
	 */
	private $counter = null;

	/**
	 * Check if there is at least one instance of DPT.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    bool
	 */
	private $has_dpt = false;

	/**
	 * Check if there is at least one instance with slider style.
	 *
	 * @since  2.0.0
	 * @access private
	 * @var    bool
	 */
	private $has_slider = false;

	/**
	 * Check if there is at least one instance with masonry style.
	 *
	 * @since  2.0.0
	 * @access private
	 * @var    bool
	 */
	private $has_mason = false;

	/**
	 * Check number of instances of DPT.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    int
	 */
	private $dpt_count = 0;

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->counter = wp_rand( 1, 1000 );
	}

	/**
	 * Return current instance of a key.
	 *
	 * @since  1.0.0
	 *
	 * @return int
	 */
	public function get() {
		$this->has_dpt         = true;
		return $this->counter += 1;
	}

	/**
	 * Return total number of DPT instances.
	 *
	 * @since  1.8.0
	 *
	 * @return int
	 */
	public function count() {
		return $this->dpt_count += 1;
	}

	/**
	 * Check if there is at least one instance of DPT.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function has_dpt() {
		return $this->has_dpt;
	}

	/**
	 * Check if there is at least one instance of DPT with Slider style.
	 *
	 * @since 2.0.0
	 */
	public function has_slider() {
		return $this->has_slider;
	}

	/**
	 * Check if there is at least one instance of DPT with Masonry style.
	 *
	 * @since 2.0.0
	 */
	public function has_mason() {
		return $this->has_mason;
	}

	/**
	 * Set if there is at least one instance of DPT with Slider style.
	 *
	 * @since 2.0.0
	 */
	public function set_slider() {
		$this->has_slider = true;
	}

	/**
	 * Set if there is at least one instance of DPT with Masonry style.
	 *
	 * @since 2.0.0
	 */
	public function set_mason() {
		$this->has_mason = true;
	}

	/**
	 * Returns the instance of this class.
	 *
	 * @since  1.0.0
	 *
	 * @return object Instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
