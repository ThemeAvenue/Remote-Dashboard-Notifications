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

	public function __construct( $name = false, $args = array(), $labels = array() ) {

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

			if( !post_type_exists( $this->cpt_slug ) ) {

				add_action( 'init', array( $this, 'register_post_type' ) );

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
			'name'                  => _x( $plural, 'post type general name' ),  
			'singular_name'         => _x( $singular, 'post type singular name' ),  
			'add_new'               => _x( 'Add New', strtolower( $singular ) ),  
			'add_new_item'          => __( 'Add New ' . $singular ),  
			'edit_item'             => __( 'Edit ' . $singular ),  
			'new_item'              => __( 'New ' . $singular ),  
			'all_items'             => __( 'All ' . $plural ),  
			'view_item'             => __( 'View ' . $singular ),  
			'search_items'          => __( 'Search ' . $plural ),  
			'not_found'             => __( 'No ' . strtolower( $singular ) . ' found'),  
			'not_found_in_trash'    => __( 'No ' . strtolower( $plural ) . ' found in Trash'),   
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
	 * Register the new post type
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		register_post_type( $this->cpt_slug, $this->get_arguments() );

	}

}