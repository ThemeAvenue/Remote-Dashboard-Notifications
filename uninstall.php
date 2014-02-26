<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/* Delete channels and post types */
rn_delete_custom_terms( 'rn-channel' );
rn_delete_custom_terms( 'rn-pt' );
rn_delete_notifications();

/**
 * Detete all terms of a taxonomy
 *
 * @link http://wordpress.stackexchange.com/questions/119229/how-to-delete-custom-taxonomy-terms-in-plugins-uninstall-php
 */
function rn_delete_custom_terms( $taxonomy ) {

	global $wpdb;

	$query = 'SELECT t.name, t.term_id
	FROM ' . $wpdb->terms . ' AS t
	INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
	ON t.term_id = tt.term_id
	WHERE tt.taxonomy = "' . $taxonomy . '"';

	$terms = $wpdb->get_results($query);

	foreach( $terms as $term ) {
		wp_delete_term( $term->term_id, $taxonomy );
	}

}

/**
 * Delete all the notifications saved in DB
 */
function rn_delete_notifications() {

	global $wpdb;

	$args = array(
		'posts_per_page'		 => -1,
		'post_type' 			 => 'notification',
		'post_status' 			 => array( 'any', 'auto-draft' ),
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'cache_results' 		 => false
	);

	$posts = new WP_Query( $args );

	if( isset( $posts->posts ) && is_array( $posts->posts ) ) {

		foreach( $posts->posts as $post )
			wp_delete_post( $post->ID, true );

	}

}