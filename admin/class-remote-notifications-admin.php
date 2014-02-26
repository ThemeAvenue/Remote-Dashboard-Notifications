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
		add_action( 'add_meta_boxes', array( $this, 'metabox' ) );
		add_action( 'save_post', array( $this, 'save_settings' ) );

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

	/**
	* Adds a metabox to the side column on the notification screen.
	*/
	public function metabox() {

		add_meta_box( 'rn_settings', __( 'Settings', 'remote-notification' ), array( $this, 'notice_settings' ), 'notification', 'side' );

	}

	/**
	* Prints the metabox content.
	* 
	* @param WP_Post $post The object for the current post/page.
	*/
	public function notice_settings( $post ) {

		wp_nonce_field( 'update_settings', 'rn_settings_nonce', false );

		/*
		* Use get_post_meta() to retrieve an existing value
		* from the database and use the value for the form.
		*/
		$value = get_post_meta( $post->ID, '_rn_settings', true );
		$style = isset( $value['style'] ) ? $value['style'] : '';
		?>

		<label for="rn_style" class="screen-reader-text"><?php _e( 'Notice Style', 'remote-notification' ); ?></label>
		<p><strong><?php _e( 'Notice Style', 'remote-notification' ); ?></strong></p>
		<select id="rn_style" name="rn_settings[style]">
			<option value="default" <?php if( 'default' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Default', 'remote-notification' ); ?></option>
			<option value="success" <?php if( 'success' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Success', 'remote-notification' ); ?></option>
			<option value="info" <?php if( 'info' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Info', 'remote-notification' ); ?></option>
			<option value="warning" <?php if( 'warning' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Warning', 'remote-notification' ); ?></option>
			<option value="danger" <?php if( 'danger' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Danger', 'remote-notification' ); ?></option>
		</select>

	<?php }

	/**
	* When the post is saved, saves our custom data.
	*
	* @param int $post_id The ID of the post being saved.
	*/
	public function save_settings( $post_id ) {

		/*
		* We need to verify this came from the our screen and with proper authorization,
		* because save_post can be triggered at other times.
		*/

		// Check if our nonce is set.
		if ( !isset( $_POST['rn_settings_nonce'] ) )
			return $post_id;

		$nonce = $_POST['rn_settings_nonce'];

		// Verify that the nonce is valid.
		if ( !wp_verify_nonce( $nonce, 'update_settings' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'notification' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize user input.
		$mydata = sanitize_text_field( $_POST['rn_settings'] );

		// Update the meta field in the database.
		update_post_meta( $post_id, '_rn_settings', $_POST['rn_settings'] );

	}
	
}
