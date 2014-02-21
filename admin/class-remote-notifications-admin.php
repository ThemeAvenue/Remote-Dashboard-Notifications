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
 * This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package Remote Dashobard Notifications
 * @author  Julien Liabeuf <julien@liabeuf.fr>
 */
class Remote_Notifications_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/**
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = Remote_Notifications::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );

		add_action( 'create_rn-channel', array( $this, 'create_channel_key' ), 10, 3 );
		add_action( 'delete_rn-channel', array( $this, 'delete_channel_key' ), 10, 3 );
		add_action( 'rn-channel_edit_form_fields', array( $this, 'show_channel_key' ), 10, 2 );

		/* Register custom metaboxes and options */
		/*$options = array(
			array( 'id' => 'color', 'title' => __( 'Background Color', 'remote-notification' ), 'type' => 'colorpicker' )
		);

		$this->mb = new TAV_Register_Metabox( array( 'id' => 'rn_options', 'title' => __( 'Options', 'remote-notification' ), 'post_type' => 'notification', 'options' => $options ) );*/

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
	 * Associate a key to the term created
	 *
	 * This function will save a key for each term
	 */
	public function create_channel_key( $term_id, $tt_id ) {
    	
    	/* Get a key */
		$key = $this->generate_key();

		/* Save it in DB */
		add_option( "_rn_channel_key_$term_id", $key );

	}

	public function delete_channel_key( $term_id, $tt_id ) {

		/* Save it in DB */
		delete_option( "_rn_channel_key_$term_id" );

	}

	private function generate_key() {

		$length = 16;

		$max = ceil($length / 40);
		$random = '';
		for ($i = 0; $i < $max; $i ++) {
			$random .= sha1(microtime(true).mt_rand(10000,90000));
		}
		return substr($random, 0, $length);
	}

	public function show_channel_key( $tag ) {  

		$term_id = $tag->term_id;
		$key 	 = get_option( "_rn_channel_key_$term_id", false );

		if( false === $key )
			return;
    
    	?>
    	<tr class="form-field">  
			<th scope="row" valign="top">  
				<label><?php _e( 'Channel ID', 'remote-notification' ); ?></label>  
			</th>  
			<td>
				<code><?php echo $term_id; ?></code>
			</td>
		</tr>  

    	<tr class="form-field">  
			<th scope="row" valign="top">  
				<label><?php _e( 'Channel Key', 'remote-notification' ); ?></label>  
			</th>  
			<td>
				<code><?php echo $key; ?></code>
			</td>
		</tr>

    <?php } 
}
