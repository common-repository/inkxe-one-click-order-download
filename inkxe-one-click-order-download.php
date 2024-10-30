<?php
/**
 * Plugin Name: inkXE One Click Order Download
 * Plugin URI: https://inkxe.com/
 * Description: This extension is used to download the order (for orders with inkXE customize product only).
 * Author: inkXE
 * Author URI: https://inkxe.com
 * Text Domain: inkxe-one-click-order-download
 * Version: 1.0.0
 * Requires at least: 3.8
 * Tested up to: 4.7.2
 */

if (! defined ( 'ABSPATH' )) {
	exit ();
}
define ( 'OCOD_PREFIX', 'ocod' );
$activated = true;
if ( function_exists ( 'is_multisite' ) && is_multisite () ) {
	include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( ! is_plugin_active ( 'woocommerce/woocommerce.php' ) ) {
		$activated = false;
	}
} else {
	if ( ! in_array ( 'woocommerce/woocommerce.php', apply_filters ( 'active_plugins', get_option ( 'active_plugins' ) ) ) ) {
		$activated = false;
	}
}
/**
 * Check if WooCommerce is active
 */
if ( $activated ) {
	// Add your custom order download action button (for orders with inkXE customize product only)
	if ( is_plugin_active( 'jck_woothumbs/jck_woothumbs.php' ) ) {
		// Check customize product
		function is_customize_product ( $order_id )
		{
			global $wpdb;
			$id = $order_id;
			$order_items = $wpdb->prefix . "woocommerce_order_items";
			$order_item_meta = $wpdb->prefix . "woocommerce_order_itemmeta";
			$sql = "SELECT order_item_id FROM $order_items WHERE order_id = '".$id."'";
			$items = $wpdb->get_results($sql);
			$is_customize = 0;
			foreach ( $items as $item )
			{
				$item_id = $item->order_item_id;
				$meta_id = $wpdb->get_var( "SELECT meta_id FROM $order_item_meta WHERE meta_key = 'refid' AND meta_value != '' AND order_item_id = '".$item_id."'" );
				if ( $meta_id )
				{
					$is_customize = 1;
				}
			}
			
			return $is_customize;
		}

		function add_custom_order_status_actions_button( $actions, $order ) {
			// Display the button for all orders that have a customize product
			// Get Order ID (compatibility all WC versions)
			$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$is_customize = is_customize_product($order_id);
			if ( $is_customize ) {
				// Set the action button
				$actions['download'] = array(
					'url'       => get_option('siteurl').'/xetool/api/index.php?reqmethod=downloadOrderZipAdmin&order_id=' . $order_id . '&increment_id=' .$order_id,
					'name'      => __( 'Download', 'woocommerce' ),
					'action'    => "view download", // keep "view" class for a clean button CSS
				);
			}
			return $actions;
		}

		// Set Here the WooCommerce icon for your action button
		function add_custom_order_status_actions_button_css() {
			echo '<style>.view.download::after { font-family: woocommerce; content: "\e00a" !important; }.widefat .column-order_actions {width: 120px;}</style>';
		}
		add_action( 'admin_head', 'add_custom_order_status_actions_button_css' );
		//plugin is activated
		add_filter( 'woocommerce_admin_order_actions', 'add_custom_order_status_actions_button', 100, 2 );
	} else {
		function ocod_plugin_error_notice() {
			?>
			<div class="error notice is-dismissible">
				<p><?php _e( 'inkXE Product Designer is not activated. Please install inkXE Product Designer first, to use the inkXE One Click Order Download plugin !!!', 'inkxe-one-click-order-download' ); ?></p>
			</div>
			<?php
		}
		
		add_action ( 'admin_init', OCOD_PREFIX . '_plugin_deactivate' );
		function ocod_plugin_deactivate() {
			deactivate_plugins ( plugin_basename ( __FILE__ ) );
			unset( $_GET['activate'] );
			add_action ( 'admin_notices', OCOD_PREFIX . '_plugin_error_notice' );
		}
	}
} else {
	function ocod_plugin_error_notice() {
		?>
		<div class="error notice is-dismissible">
			<p><?php _e( 'WooCommerce is not activated. Please install WooCommerce first, to use the inkXE One Click Order Download plugin !!!', 'inkxe-one-click-order-download' ); ?></p>
		</div>
		<?php
	}
	
	add_action ( 'admin_init', OCOD_PREFIX . '_plugin_deactivate' );
	function ocod_plugin_deactivate() {
		deactivate_plugins ( plugin_basename ( __FILE__ ) );
		unset( $_GET['activate'] );
		add_action ( 'admin_notices', OCOD_PREFIX . '_plugin_error_notice' );
	}
}

?>