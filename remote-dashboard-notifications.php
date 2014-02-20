<?php
/**
 * Remote Dashobard Notifications.
 *
 * This plugin allows to push any notification to the WordPress dashboard.
 * This allows easy communication with the clients.
 *
 * @package   Remote Dashboard Notifications
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2013 ThemeAvenue
 *
 * @wordpress-plugin
 * Plugin Name:       Remote Dashboard Notifications
 * Plugin URI:        http://themeavenue.net
 * Description:       Pushes notifications to the WordPress dashboard
 * Version:           1.0.0
 * Author:            ThemeAvenue
 * Author URI:        http://themeavenue.net
 * Text Domain:       wpas-notification
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/ThemeAvenue/Remote-Dashboard-Notifications
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'RDN_URL', plugin_dir_url( __FILE__ ) );
define( 'RDN_PATH', plugin_dir_path( __FILE__ ) );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-remote-notifications.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-custom-post-type.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
register_activation_hook( __FILE__, array( 'Remote_Notifications', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Remote_Notifications', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
add_action( 'plugins_loaded', array( 'Remote_Notifications', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/**
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() /*&& ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )*/ ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-remote-notifications-admin.php' );

	add_action( 'plugins_loaded', array( 'Remote_Notifications_Admin', 'get_instance' ) );

}