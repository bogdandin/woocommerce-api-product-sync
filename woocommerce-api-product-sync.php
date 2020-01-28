<?php
/*
Plugin Name: WooCommerce API Product Sync with Multiple Stores
Description: This plugin can sync automatically product from one WooCommerce web store (shop) to the other WooCommerce web stores (shops) when product add/update.
Version: 1.5.0
Author: Obtain Infotech
Author URI: https://www.obtaininfotech.com/
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a constant variable for plugin path.
 */
define( 'WC_API_MPS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/*
 * This is a file for includes core functionality.
 */
include_once WC_API_MPS_PLUGIN_PATH . 'includes/includes.php';

/*
 * This is a function that run during active plugin
 */
if ( ! function_exists( 'wc_api_mps_activation' ) ) {
    register_activation_hook( __FILE__, 'wc_api_mps_activation' );
    function wc_api_mps_activation() {
        
        $sync_type = get_option( 'wc_api_mps_sync_type' );
        if ( ! $sync_type ) {
            update_option( 'wc_api_mps_sync_type', 'auto' );
        }
        
        $authorization = get_option( 'wc_api_mps_authorization' );
        if ( ! $authorization ) {
            update_option( 'wc_api_mps_authorization', 'query' );
        }
        
        $stock_sync = get_option( 'wc_api_mps_stock_sync' );
        if ( ! $stock_sync ) {
            update_option( 'wc_api_mps_stock_sync', 1 );
        }
    }
}