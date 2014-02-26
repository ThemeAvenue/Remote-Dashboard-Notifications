<?php
/**
 * Remote Dashobard Notifications.
 *
 * This class is part of the Remote Dashboard Notifications plugin.
 * This plugin allows you to send notifications to your client's
 * WordPress dashboard easily.
 *
 * Notification you send will be displayed as admin notifications
 * using the standard WordPress hooks. A "dismiss" option is added
 * in order to let the user hide the notification.
 *
 * @package   Remote Dashboard Notifications
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @link 	  http://themeavenue.net/plugin-url
 * @copyright 2014 ThemeAvenue
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class TAV_Remote_Notification_Client {

	/**
	 * Class version.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected static $version = '0.1.0';

	public function __construct( $channel_id = false, $channel_key = false, $server = false ) {

		/* Don't continue during Ajax process */
		if( !is_admin() || defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;

		$this->id  	  = intval( $channel_id );
		$this->key 	  = sanitize_key( $channel_key );
		$this->server = esc_url( $server );
		$this->notice = false;
		$this->cache  = apply_filters( 'rn_notice_caching_time', 6 );

		/* The plugin can't work without those 2 parameters */
		if( false === ( $this->id || $this->key || $this->server ) )
			return;

		/* Call the dismiss method before testing for Ajax */
		if( isset( $_GET['rn'] ) && isset( $_GET['notification'] ) )
			add_action( 'init', array( $this, 'dismiss' ) );

		add_action( 'init', array( $this, 'request_server' ) );

	}

	/**
	 * Send a request to notification server
	 *
	 * The distant WordPress notification server is
	 * queried using the WordPress HTTP API.
	 * 
	 * @since 0.1.0
	 */
	public function request_server() {

		/* Content is false at first */
		$content = get_transient( 'rn_last_notification' );

		/* Set the request response to null */
		$request = null;

		/* If no notice is present in DB we query the server */
		if( false === $content ) {

			/* Prepare the payload to send to server */
			$payload = base64_encode( json_encode( array( 'channel' => $this->id, 'key' => $this->key ) ) );

			/* Get the endpoint URL ready */
			$url = "$this->server?payload=$payload&ver=1";

			/* Query the server */
			$request = wp_remote_get( $url );

			/* If we have a WP_Error object we abort */
			if( is_wp_error( $request ) )
				return;

			/* Check if we have a valid response */
			if( is_array( $request ) && isset( $request['response']['code'] ) && 200 === intval( $request['response']['code'] ) ) {

				/* Get the response body */
				if( isset( $request['body'] ) ) {

					/**
					 * Decode the response JSON string
					 */
					$content = json_decode( $request['body'] );

					/**
					 * Check if the payload is in a usable JSON format
					 */
					if( json_last_error() != JSON_ERROR_NONE ) {
						return false;
					}

					set_transient( 'rn_last_notification', $content, $this->cache*60*60 );

				}			

			}

		}

		/**
		 * If the JSON string has been decoded we can go ahead
		 */
		if( is_object( $content ) ) {

			if( isset( $content->error ) )
				return;

			$this->notice = $content;

			/**
			 * Check if notice has already been dismissed
			 */
			$dismissed = get_option( '_rn_dismissed' );

			if( is_array( $dismissed ) && in_array( $content->slug, $dismissed ) )
				return;

			/**
			 * Add the notice to WP dashboard
			 */
			add_action( 'admin_notices', array( $this, 'show_notice' ) );

		} else {

			return false;

		}

	}

	/**
	 * Display the admin notice
	 *
	 * The function will do some checks to verify if
	 * the notice can be displayed on the current page.
	 * If all the checks are passed, the notice
	 * is added to the page.
	 * 
	 * @since 0.1.0
	 */
	public function show_notice() {

		$content = $this->notice;

		/* If there is no content we abort */
		if( false === $content )
			return;

		/* If the type array isn't empty we have a limitation */
		if( isset( $content->type ) && is_array( $content->type ) && !empty( $content->type ) ) {

			/* Get current post type */
			$pt = get_post_type();

			/**
			 * If the current post type can't be retrieved
			 * or if it's not in the allowed post types,
			 * then we don't display the admin notice.
			 */
			if( false === $pt || !in_array( $pt, $content->type ) )
				return;

		}

		/* Prepare the dismiss URL */
		$url = wp_nonce_url(  add_query_arg( array( 'notification' => $content->slug ), '' ), 'rn-dismiss', 'rn' ); ?>

		<div class="updated rn-notice">
			<a href="<?php echo $url; ?>" class="rn-dismiss-button"><?php _e( 'Dismiss', 'remote-notification' ); ?></a>
			<h2><?php echo $content->title; ?></h2>
			<p><?php echo html_entity_decode( $content->message ); ?></p>
		</div>
		<?php

	}

	/**
	 * Dismiss notice
	 *
	 * When the user dismisses a notice, its slug
	 * is added to the _rn_dismissed entry in the DB options table.
	 * This entry is then used to check if a notie has been dismissed
	 * before displaying it on the dashboard.
	 *
	 * @since 0.1.0
	 */
	public function dismiss() {

		/* Check if we have all the vars */
		if( !isset( $_GET['rn'] ) || !isset( $_GET['notification'] ) )
			return;

		/* Validate nonce */
		if( !wp_verify_nonce( sanitize_key( $_GET['rn'] ), 'rn-dismiss' ) )
			return;

		/* Get dismissed list */
		$dismissed = get_option( '_rn_dismissed', array() );

		/* Add the current notice to the list if needed */
		if( is_array( $dismissed ) && !in_array( $_GET['notification'], $dismissed ) )
			array_push( $dismissed, $_GET['notification'] );

		/* Update option */
		update_option( '_rn_dismissed', $dismissed );

	}

}