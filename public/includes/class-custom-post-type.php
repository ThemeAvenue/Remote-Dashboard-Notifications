<?php
/**
 * Custom Post Type Registration.
 *
 * @package   Contract Builder
 * @author    ThemeAvenue <contact@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2013 ThemeAvenue
 */

/**
 * Register custom post types
 *
 * @package Contract Builder
 * @author  Julien Liabeuf <julien@liabeuf.fr>
 * @version 1.0.0
 */
class TAV_Custom_Post_Type {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	public function __construct( $name = false, $args = array(), $labels = array(), $domain = 'remote-notifications' ) {

		/**
		 * A name for the custom post type is the minimum required.
		 * If no name is defined, we can't proceed with the registration.
		 */
		if( $name ) {

			$this->cpt_name 	   = sanitize_text_field( $name );
			$this->cpt_name_plural = $this->cpt_name . 's';
			$this->cpt_slug 	   = sanitize_title( $name );
			$this->labels 		   = $labels;
			$this->args 		   = $args;
			$this->domain 		   = $domain;

			if( !post_type_exists( $this->cpt_slug ) ) {

				add_action( 'init', array( $this, 'register_post_type' ) );
				add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

			}

		}

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
	 * Get the post type labels
	 *
	 * @since 1.0.0
	 * @return (array) Post type labels
	 */
	public function get_labels() {

		$singular = $this->cpt_name;
		$plural   = $this->cpt_name_plural;

		/* Set the default labels */
		$labels = array(
			'name'                  => _x( $plural, 'post type general name', $this->domain ),  
			'singular_name'         => _x( $singular, 'post type singular name', $this->domain ),  
			'add_new'               => __( 'Add New', $this->domain ),  
			'add_new_item'          => sprintf( __( 'Add New %s', $this->domain ), $singular ),  
			'edit_item'             => sprintf( __( 'Edit %s', $this->domain ), $singular ),  
			'new_item'              => sprintf( __( 'New %s', $this->domain ), $singular ),  
			'all_items'             => sprintf( __( 'All %s', $this->domain ), $plural ),  
			'view_item'             => sprintf( __( 'View %s', $this->domain ), $singular ),  
			'search_items'          => sprintf( __( 'Search %s', $this->domain ), $plural ),  
			'not_found'             => sprintf( __( 'No %s found', $this->domain ), strtolower( $singular ) ),  
			'not_found_in_trash'    => sprintf( __( 'No %s found in Trash', $this->domain ), strtolower( $plural ) ),   
			'parent_item_colon'     => '',  
			'menu_name'             => $plural
		);

		return array_merge( $labels, $this->labels );

	}

	/**
	 * Get post type arguments
	 *
	 * @since 1.0.0
	 * @return (array) Post type arguments
	 */
	public function get_arguments() {

		$args = array(
			'labels'             => $this->get_labels(),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor' )
		);

		return array_merge( $args, $this->args );

	}

	/**
	 * Custom updated messages
	 */
	function updated_messages( $messages ) {

		global $post, $post_ID;

		$singular = $this->cpt_name;

		$messages[$this->cpt_slug] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( "$singular updated. <a href='%s'>View $singular</a>", 'your_text_domain'), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'your_text_domain'),
			3 => __( 'Custom field deleted.', 'your_text_domain'),
			4 => __( "$singular updated.", 'your_text_domain'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( "$singular restored to revision from %s", 'your_text_domain'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( "$singular published. <a href='%s'>View $singular</a>", 'your_text_domain'), esc_url( get_permalink($post_ID) ) ),
			7 => __( "$singular saved.", 'your_text_domain' ),
			8 => sprintf( __( "$singular submitted. <a target='_blank' href='%s'>Preview $singular</a>", 'your_text_domain'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( "$singular scheduled for: <strong>%1$s</strong>. <a target='_blank' href='%2$s'>Preview $singular</a>", 'your_text_domain'),
			// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( "$singular draft updated. <a target='_blank' href='%s'>Preview $singular</a>", 'your_text_domain'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}

	/**
	 * Register the new post type
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		register_post_type( $this->cpt_slug, $this->get_arguments() );

	}

}