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

		add_action( 'create_rn-channel', array( $this, 'create_channel_key' ), 10, 3 );
		add_action( 'delete_rn-channel', array( $this, 'delete_channel_key' ), 10, 3 );

		/* The rest isn't needed during Ajax */
		if( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;

		/**
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = Remote_Notifications::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		add_action( 'rn-channel_edit_form_fields', array( $this, 'show_channel_key' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'metabox' ) );
		add_action( 'save_post', array( $this, 'save_settings' ) );
		add_filter( 'manage_notification_posts_columns', array( $this, 'start_end_dates_columns' ), 10, 1 );
		add_action( 'manage_notification_posts_custom_column' , array( $this, 'start_end_dates_columns_content' ), 10, 2 );

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
	 *
	 * @param int $term_id The taxonomy term ID
	 */
	public function create_channel_key( $term_id ) {

		/* Get a key */
		$key = $this->generate_key();

		/* Save it in DB */
		add_option( "_rn_channel_key_$term_id", $key );

	}

	public function delete_channel_key( $term_id ) {

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

		if( false === $key ) { ?>

			<tr class="form-field">  
				<th scope="row" valign="top">  
					<label><?php _e( 'Channel Key', 'remote-notifications' ); ?></label>  
				</th>  
				<td>
					<?php _e( 'An error occured during key generation. Please delete this channel and recreate it.', 'remote-notifications' ); ?>
				</td>
			</tr>  

			<?php return;

		}    
    	?>
    	<tr class="form-field">  
			<th scope="row" valign="top">  
				<label><?php _e( 'Channel ID', 'remote-notifications' ); ?></label>  
			</th>  
			<td>
				<code><?php echo $term_id; ?></code>
			</td>
		</tr>  

    	<tr class="form-field">  
			<th scope="row" valign="top">  
				<label><?php _e( 'Channel Key', 'remote-notifications' ); ?></label>  
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

		add_meta_box( 'rn_settings', __( 'Settings', 'remote-notifications' ), array( $this, 'notice_settings' ), 'notification', 'side' );

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
		$style = isset( $value['style'] ) ? esc_attr( $value['style'] ) : '';
		?>

		<label for="rn_style" class="screen-reader-text"><?php _e( 'Notice Style', 'remote-notifications' ); ?></label>
		<p><strong><?php _e( 'Notice Style', 'remote-notification' ); ?></strong></p>
		<select id="rn_style" name="rn_settings[style]">
			<optgroup label="<?php _e( 'WordPress Style', 'remote-notifications' ); ?>">
				<option value="updated" <?php if( 'updated' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Updated', 'remote-notifications' ); ?></option>
				<option value="error" <?php if( 'error' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Error', 'remote-notifications' ); ?></option>
			</optgroup>
			<optgroup label="<?php _e( 'Custom Style', 'remote-notifications' ); ?>">
				<option value="success" <?php if( 'success' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Success', 'remote-notifications' ); ?></option>
				<option value="info" <?php if( 'info' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Info', 'remote-notifications' ); ?></option>
				<option value="warning" <?php if( 'warning' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Warning', 'remote-notifications' ); ?></option>
				<option value="danger" <?php if( 'danger' == $style ): ?>selected="selected"<?php endif; ?>><?php _e( 'Danger', 'remote-notifications' ); ?></option>
			</optgroup>
		</select>

		<p><label for="rn_date_start"><strong><?php _e( 'Start Date', 'remote-notifications' ); ?></strong></label></p>
		<input type="date" id="rn_date_start" name="rn_settings[date_start]" value="<?php echo isset( $value['date_start'] ) ? esc_attr( $value['date_start'] ) : ''; ?>">
		<p class="description"><?php _e( 'Leave empty for no start date (will start immediately)', 'remote-notifications' ); ?></p>


		<p><label for="rn_date_end"><strong><?php _e( 'End Date', 'remote-notifications' ); ?></strong></label></p>
		<input type="date" id="rn_date_end" name="rn_settings[date_end]" value="<?php echo isset( $value['date_end'] ) ? esc_attr( $value['date_end'] ) : ''; ?>">
		<p class="description"><?php _e( 'Leave empty for no end date (will never end)', 'remote-notifications' ); ?></p>

	<?php }

	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @return int|bool Meta ID or false on failure
	 */
	public function save_settings( $post_id ) {

		/*
		* We need to verify this came from the our screen and with proper authorization,
		* because save_post can be triggered at other times.
		*/

		// Check if our nonce is set.
		if ( ! isset( $_POST['rn_settings_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['rn_settings_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'update_settings' ) ) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'notification' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize user input.
		$mydata = array_map( 'sanitize_text_field', $_POST['rn_settings'] );

		// Update the meta field in the database.
		return update_post_meta( $post_id, '_rn_settings', $mydata );

	}

	/**
	 * Add start and end dates columns
	 *
	 * @since 1.2.0
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function start_end_dates_columns( $columns ) {

		$new = array();

		foreach ( $columns as $id => $label ) {

			if ( 'date' === $id ) {
				$new['rn_start'] = __( 'Starts', 'remote-notifications' );
				$new['rn_end']   = __( 'Ends', 'remote-notifications' );
			}

			$new[$id] = $label;

			if ( 'title' === $id ) {
				$new['rn_status'] = __( 'Status', 'remote-notifications' );
			}

		}

		return $new;

	}

	/**
	 * Start and end dates columns content
	 *
	 * @since 1.2.0
	 *
	 * @param $column
	 * @param $post_id
	 *
	 * @return void
	 */
	public function start_end_dates_columns_content( $column, $post_id ) {

		$settings = get_post_meta( $post_id, '_rn_settings', true );
		$start = isset( $settings['date_start'] ) ? esc_attr( $settings['date_start'] ) : '';
		$end = isset( $settings['date_end'] ) ? esc_attr( $settings['date_end'] ) : '';

		switch ( $column ) {

			case 'rn_start' :
				echo ! empty( $start ) ? date( get_option( 'date_format' ), strtotime( $start ) ) : '';
				break;

			case 'rn_end' :
				echo ! empty( $end ) ? date( get_option( 'date_format' ), strtotime( $end ) ) : '';
				break;

			case 'rn_status':

				$channel = get_the_terms( $post_id, 'rn-channel' );

				if ( empty( $channel ) ) {
					echo '<strong>' . __( 'Won&#039;t Run', 'remote-notifications' ) . '</strong>';
					echo '<br><em>' . __( 'No channel set', 'remote-notifications' ) . '</em>';
					continue;
				}

				$status = '';

				if ( empty( $start ) || strtotime( $start ) < time() ) {

					if ( empty( $end ) ) {
						$status = __( '<strong>Running</strong> (endless)', 'remote-notifications' );
					} else {

						if ( strtotime( $end ) < time() ) {
							$status = __( 'Ended', 'remote-notifications' );
						} else {
							$status = '<strong>' . __( 'Running', 'remote-notifications' ) . '</strong>';
						}
					}

				} else {
					$status = __( 'Scheduled', 'remote-notifications' );
				}

				echo $status;

				break;

		}
	}
	
}
