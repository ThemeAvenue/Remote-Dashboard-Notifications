<?php
/**
 * Remote Dashobard Notifications.
 *
 * @package   Remote Dashobard Notifications
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2013 ThemeAvenue
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package Remote Dashobard Notifications
 * @author  Julien Liabeuf <julien@liabeuf.fr>
 */
class Remote_Notifications {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *""
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * @TODO - Rename "plugin-name" to the name your your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'remote-notifications';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'register_channel' ) );
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'template_redirect', array( $this, 'endpoint' ) );

		// Register custom post types
		$notifications = new TAV_Custom_Post_Type( 'Notification', array('menu_icon' => 'dashicons-format-chat' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 *@return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * Create API endpoint inside the custom post type template
	 *
	 * @since 1.0.0
	 */
	public function endpoint() {

		global $wp_query;

		if( is_archive() && isset( $wp_query->query_vars['post_type'] ) && 'notification' == $wp_query->query_vars['post_type'] ) {

			$single = RDN_PATH . '/public/views/archive-notification.php';

			if( file_exists( $single ) ) {

				include( $single );
				exit;

			}

		}

	}

	/**
	 * Register the "Channels" taxonomy
	 *
	 * @since 1.0.0
	 */
	public function register_channel() {

		$labels = array(
			'name'              => _x( 'Channels', 'taxonomy general name' ),
			'singular_name'     => _x( 'Channel', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Channels' ),
			'all_items'         => __( 'All Channels' ),
			'parent_item'       => __( 'Parent Channel' ),
			'parent_item_colon' => __( 'Parent Channel:' ),
			'edit_item'         => __( 'Edit Channel' ),
			'update_item'       => __( 'Update Channel' ),
			'add_new_item'      => __( 'Add New Channel' ),
			'new_item_name'     => __( 'New Channel Name' ),
			'menu_name'         => __( 'Channels' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'channel' ),
		);

		register_taxonomy( 'rn-channel', array( 'notification' ), $args );

	}

	/**
	 * Register the post type taxonomy
	 *
	 * This taxonomy will be used to limit notices
	 * display on specific post types only.
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		$labels = array(
			'name'              => _x( 'Post Types Limitation', 'taxonomy general name' ),
			'singular_name'     => _x( 'Post Type', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Post Types' ),
			'all_items'         => __( 'All Post Types' ),
			'parent_item'       => __( 'Parent Post Type' ),
			'parent_item_colon' => __( 'Parent Post Type:' ),
			'edit_item'         => __( 'Edit Post Type' ),
			'update_item'       => __( 'Update Post Type' ),
			'add_new_item'      => __( 'Add New Post Type' ),
			'new_item_name'     => __( 'New Post Type Name' ),
			'menu_name'         => __( 'Post Types' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'post-type' ),
		);

		register_taxonomy( 'rn-pt', array( 'notification' ), $args );

	}

}