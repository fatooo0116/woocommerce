<?php
/**
 * Nosara Tracks for Woo
 */

require_once dirname( __FILE__ ) . '/class-wc-tracks.php';
require_once dirname( __FILE__ ) . '/class-wc-tracks-event.php';
require_once dirname( __FILE__ ) . '/class-wc-tracks-client.php';

/**
 * Send a Tracks event when a product is updated.
 *
 * @param int   $product_id Product id.
 * @param array $post WordPress post.
 */
function woocommerce_tracks_product_updated( $product_id, $post ) {
	if ( 'product' !== $post->post_type ) {
		return;
	};
	$properties = array(
		'product_id' => $product_id,
	);

	WC_Tracks::record_event( 'update_product', $properties );
}

/**
 * Add actions to apply Tracks where needed.
 */
function track_woo_usage() {
	if ( ! class_exists( 'WC_Tracks' ) ) {
		return;
	}

	/**
	 * Don't track users who haven't opted-in to tracking or if a filter
	 * has been applied to turn it off.
	 */
	$allow_tracking = 'yes' === get_option( 'woocommerce_allow_tracking' ) &&
		apply_filters( 'woocommerce_apply_user_tracking', true );

	if ( $allow_tracking ) {
		wp_enqueue_script( 'woo-tracks', '//stats.wp.com/w.js', array(), gmdate( 'YW' ), true );
	}

	$early_return = $allow_tracking ? 'false' : 'true';
	wc_enqueue_js( "
		window.wcSettings = window.wcSettings || {};
		window.wcSettings.recordEvent = function( event, eventProperties ) {
			if ( " . $early_return . " ) {
				return;
			}
			window._tkq = window._tkq || [];
			window._tkq.push( [ 'recordEvent', event, eventProperties ] );
		}
	" );

	add_action( 'edit_post', 'woocommerce_tracks_product_updated', 10, 2 );
}

add_action( 'init', 'track_woo_usage' );
