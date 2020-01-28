<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a class for WooCommerce API.
 */
if ( ! class_exists( 'WC_API_MPS' ) ) {
    class WC_API_MPS {
        
        var $url;
        var $site_url;
        var $consumer_key;
        var $consumer_secret;
        
        function __construct( $url, $consumer_key, $consumer_secret ) {
                        
            $this->url              = rtrim( $url, '/' ).'/wp-json/wc/v2';
            $this->site_url         = $url;
            $this->consumer_key     = $consumer_key;
            $this->consumer_secret  = $consumer_secret;
        }
        
        function authentication() {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products?per_page=1&'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products?per_page=1' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getProducts( $search ) {
            
            $old_products_sync_by = get_option( 'wc_api_mps_old_products_sync_by' );
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                if ( $old_products_sync_by == 'sku' ) {
                    curl_setopt( $ch, CURLOPT_URL, $this->url.'/products?sku='.$search.'&'.$query_string );
                } else {
                    curl_setopt( $ch, CURLOPT_URL, $this->url.'/products?slug='.$search.'&'.$query_string );
                }
                
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                if ( $old_products_sync_by == 'sku' ) {
                    curl_setopt( $ch, CURLOPT_URL, $this->url.'/products?sku='.$search );
                } else {
                    curl_setopt( $ch, CURLOPT_URL, $this->url.'/products?slug='.$search );
                }
                
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getProduct( $product_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function addProduct( $data ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function updateProduct( $data, $product_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getProductVariations( $product_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'/variations?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'/variations' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getProductVariation( $product_id, $variation_product_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'/variations/'.$variation_product_id.'?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'/variations/'.$variation_product_id );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function addProductVariation( $data, $product_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'/variations?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'/variations' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function updateProductVariation( $data, $product_id, $variation_product_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'/variations/'.$variation_product_id.'?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/'.$product_id.'/variations/'.$variation_product_id );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getCategories( $slug ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/categories?slug='.$slug.'&'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/categories?slug='.$slug );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function addCategory( $data ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/categories?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/categories' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function updateCategory( $data, $category_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/categories/'.$category_id.'?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/categories/'.$category_id );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getTags( $slug ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/tags?slug='.$slug.'&'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/tags?slug='.$slug );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function addTag( $data ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/tags?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/tags' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function updateTag( $data, $tag_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/tags/'.$tag_id.'?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/tags/'.$tag_id );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getAttributes() {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function addAttribute( $data ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function updateAttribute( $data, $attribute_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes/'.$attribute_id.'?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes/'.$attribute_id );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function getAttributeTerms( $slug, $attribute_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes/'.$attribute_id.'/terms?slug='.$slug.'&'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes/'.$attribute_id.'/terms?slug='.$slug );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function addAttributeTerm( $data, $attribute_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes/'.$attribute_id.'/terms?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes/'.$attribute_id.'/terms' );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
        
        function updateAttributeTerm( $data, $attribute_term_id, $attribute_id ) {
            
            $authorization = get_option( 'wc_api_mps_authorization' );
            $data = json_encode( $data );
            $header = array(
                'Authorization: Basic '.base64_encode( $this->consumer_key.':'.$this->consumer_secret ),
                'Content-Type: application/json',
            );
            
            $ch = curl_init();
            if ( $authorization == 'query' ) {
                $query_string_parameters = array(
                    'consumer_key'      => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret,
                );
                
                $query_string = http_build_query( $query_string_parameters );
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes/'.$attribute_id.'/terms/'.$attribute_term_id.'?'.$query_string );
                $header = array(
                    'Content-Type: application/json',
                );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            } else {
                curl_setopt( $ch, CURLOPT_URL, $this->url.'/products/attributes/'.$attribute_id.'/terms/'.$attribute_term_id );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            }
            
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            $json_response = curl_exec( $ch );
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            $response = json_decode( $json_response );
            
            if ( isset( $response->code ) ) {                
                $log = "Store URL: ".$this->site_url."\n";
                $log .= "errorCode: ".$response->code."\n";
                $log .= "message: ".$response->message."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            if ( $status == 0 ) {
                $log = "status: ".$status."\n";
                $log .= "Date: ".date( 'Y-m-d H:i:s' )."\n\n";                               

                file_put_contents( WC_API_MPS_PLUGIN_PATH.'debug.log', $log, FILE_APPEND );
            }
            
            return $response;
        }
    }
}