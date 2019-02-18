<?php
/**
 * Nosara Tracks for Woo
 */

require_once dirname( __FILE__ ) . '/class-wc-tracks.php';
require_once dirname( __FILE__ ) . '/class-wc-tracks-event.php';
require_once dirname( __FILE__ ) . '/class-wc-tracks-client.php';

/**
 * This class adds actions to track usage of WooCommerce.
 *
 * @class   WC_Site_Tracing
 * @package WooCommerce/Classes
 */

class WC_Site_Tracing {
	/**
	 * Send a Tracks event when a product is updated.
	 *
	 * @param int   $product_id Product id.
	 * @param array $post WordPress post.
	 */
	public static function woocommerce_tracks_product_updated( $product_id, $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		};
		$properties = array(
			'product_id' => $product_id,
		);

		WC_Tracks::record_event( 'update_product', $properties );
	}

	public static function is_tracking_enabled() {
		/**
		 * Don't track users who haven't opted-in to tracking or if a filter
		 * has been applied to turn it off.
		 */
		if ( 'yes' !== get_option( 'woocommerce_allow_tracking' ) &&
			! apply_filters( 'woocommerce_apply_user_tracking', true ) ) {
			return false;
		}

		if ( ! class_exists( 'WC_Tracks' ) ) {
			return false;
		}

		return true;
	}

	public static function init() {
		if ( ! self::is_tracking_enabled() ) {
			return;
		}

		add_action( 'edit_post', 'woocommerce_tracks_product_updated', 10, 2 );
	}
}

add_action( 'init', array( 'WC_Site_Tracing', 'init' ) );
