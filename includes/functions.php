<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a function that add ajax call.
 */
if ( ! function_exists( 'wc_api_mps_admin_footer' ) ) {
    add_action( 'admin_footer', 'wc_api_mps_admin_footer' );
    function wc_api_mps_admin_footer() {
        
        $post_id = ( isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0 );
        $licence = get_site_option( 'wc_api_mps_licence' );
        if ( $post_id && $licence ) {
            $post_type = get_post_type( $post_id );
            if ( $post_type == 'product' ) {
                $sync_type = get_option( 'wc_api_mps_sync_type' );
                $disable_auto_sync = get_post_meta( $post_id, 'wc_api_mps_disable_auto_sync', true );
                if ( $sync_type == 'auto' && ! $disable_auto_sync ) {
                    ?>
                        <script type="text/javascript">
                            var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                            jQuery( document ).ready( function( $ ) {
                                var data = {
                                    'action': 'wc_api_mps_auto_sync',
                                    'product_id': <?php echo $post_id; ?>
                                };

                                $.post( ajax_url, data, function( response ) {
                                });
                            });
                        </script>
                    <?php
                    
                }
            }
        }
    }
}

/*
 * This is a function for auto sync callback.
 */
if ( ! function_exists( 'wc_api_mps_auto_sync_callback' ) ) {
    add_action( 'wp_ajax_wc_api_mps_auto_sync', 'wc_api_mps_auto_sync_callback' );
    function wc_api_mps_auto_sync_callback() {
        
        $product_id = ( isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : 0 );
        if ( $product_id ) {
            $stores = get_option( 'wc_api_mps_stores' );
            wc_api_mps_integration( $product_id, $stores );
        }
        
        wp_die();
    }
}

/*
 * This is a function that add meta boxes.
 */
if ( ! function_exists( 'wc_api_mps_add_meta_boxes' ) ) {
    add_action( 'add_meta_boxes', 'wc_api_mps_add_meta_boxes' );
    function wc_api_mps_add_meta_boxes() {
        
        $sync_type = get_option( 'wc_api_mps_sync_type' );
        if ( $sync_type == 'auto' ) {
            add_meta_box( 'wc_api_mps_disable_auto_sync', __( 'WooCommerce API Product Sync' ), 'wc_api_mps_disable_auto_sync_callback', 'product', 'side' );
        } else if ( $sync_type == 'manual' ) {
            add_meta_box( 'wc_api_mps_manual_sync', __( 'WooCommerce API Product Sync' ), 'wc_api_mps_manual_sync_callback', 'product', 'normal' );
        }
    }
}

/*
 * This is a function for disable auto sync.
 */
if ( ! function_exists( 'wc_api_mps_disable_auto_sync_callback' ) ) {
    function wc_api_mps_disable_auto_sync_callback() {

        $post_id = ( isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0 );
        if ( $post_id ) {
            $disable_auto_sync = get_post_meta( $post_id, 'wc_api_mps_disable_auto_sync', true );
            ?>
                <input type="hidden" name="wc_api_mps_disable_auto_sync" value="0" />
                <label><input type="checkbox" name="wc_api_mps_disable_auto_sync" value="1"<?php echo ( $disable_auto_sync ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Disable auto sync?' ); ?></label>
            <?php
        }
    }
}

/*
 * This is a function that run when save post.
 * $post_id variable return post id.
 */
if ( ! function_exists( 'wc_api_mps_save_post' ) ) {
    add_action( 'save_post', 'wc_api_mps_save_post', 10, 1 );
    function wc_api_mps_save_post( $post_id ) {
        
        $post_type = get_post_type( $post_id );
        if ( $post_type == 'product' ) {
            if ( isset( $_REQUEST['wc_api_mps_disable_auto_sync'] ) ) {
                update_post_meta( $post_id, 'wc_api_mps_disable_auto_sync', $_REQUEST['wc_api_mps_disable_auto_sync'] );
            }
            
            $disable_auto_sync = get_post_meta( $post_id, 'wc_api_mps_disable_auto_sync', true );
            $sync_type = get_option( 'wc_api_mps_sync_type' );
            $post_view = ( isset( $_REQUEST['post_view'] ) ? $_REQUEST['post_view'] : '' );
            if ( $post_view == 'list' && $sync_type == 'auto' && ! $disable_auto_sync ) {
                $product_id = $post_id;
                $stores = get_option( 'wc_api_mps_stores' );
                wc_api_mps_integration( $product_id, $stores );
            }
        }
    }
}

/*
 * This is a function that for manual sync callback.
 */
if ( ! function_exists( 'wc_api_mps_manual_sync_callback' ) ) {
    function wc_api_mps_manual_sync_callback() {
        
        $post_id = ( isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0 );
        $stores = get_option( 'wc_api_mps_stores' );
        if ( $post_id && $stores != null ) {
            ?>
                <div id="wc_api_mps-message" style="margin-top: 12px;"></div>
                <label>&nbsp;<input class="wc_api_mps-detail-check-uncheck" type="checkbox" /><?php _e( 'All' ); ?></label>
                <p class="description">&nbsp;<?php _e( 'Select/Deselect all stores.' ); ?></p>
                <div id="wc_api_mps-stores">
                    <?php
                        foreach ( $stores as $store_url => $store_data ) {
                            if ( $store_data['status'] ) {
                                ?><p>&nbsp;<label><input type="checkbox" name="" value="<?php echo $store_url; ?>" /> <?php echo $store_url; ?></label><?php
                            }
                        }
                    ?>
                </div>
                <table>
                    <tr>
                        <td><button type="button" id="wc_api_mps_manual_sync_button" class="button-primary"><?php _e( 'Sync' ); ?></button></td>
                        <td><span class="spinner wc_api_mps_spinner"></span></td>
                    </tr>
                </table>
                <script type="text/javascript">
                    var ajax_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                    jQuery( document ).ready( function( $ ) {
                        $( '.wc_api_mps-detail-check-uncheck' ).on( 'change', function() {
                            var checked = $( this ).prop( 'checked' );
                            $( '#wc_api_mps-stores input[type="checkbox"]' ).each( function() {
                                if ( checked ) {
                                    $( this ).prop( 'checked', true );
                                } else {
                                    $( this ).prop( 'checked', false );
                                }
                            });
                        });
                        
                        $( '#wc_api_mps_manual_sync_button' ).on( 'click', function() {
                            var stores = [];
                            $( '#wc_api_mps-stores input[type="checkbox"]' ).each( function() {
                                if( $( this ).prop( 'checked' ) == true ) {
                                    stores.push( $( this ).val() );
                                }
                            });
                            
                            if ( stores.length !== 0 ) {
                                var data = {
                                    'action': 'wc_api_mps_manual_sync',
                                    'product_id': <?php echo $post_id; ?>,
                                    'stores': stores
                                };
                                
                                $( '#wc_api_mps_manual_sync_button' ).prop( 'disabled', true );
                                $( '.wc_api_mps_spinner' ).addClass( 'is-active' );
                                $.post( ajax_url, data, function( response ) {
                                    $( '#wc_api_mps_manual_sync_button' ).prop( 'disabled', false );
                                    $( '.wc_api_mps_spinner' ).removeClass( 'is-active' );
                                    $( '#wc_api_mps-message' ).html('<div class="notice notice-success is-dismissible"><p>Product successfully synced.</p></div>');
                                });
                            }
                        });
                    });
                </script>
            <?php
        } else {
            ?><p><?php _e( 'No stores found.' ); ?></p><?php
        }        
    }
}

/*
 * This is a function for manual sync products.
 */
if ( ! function_exists( 'wc_api_mps_manual_sync_products_callback' ) ) {
    add_action( 'wp_ajax_wc_api_mps_manual_sync', 'wc_api_mps_manual_sync_products_callback' );
    function wc_api_mps_manual_sync_products_callback() {
        
        $product_id = ( isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : 0 );
        $selected_stores = ( isset( $_REQUEST['stores'] ) ? $_REQUEST['stores'] : array() );
        if ( $product_id && $selected_stores != null ) {
            $stores = get_option( 'wc_api_mps_stores' );
            $wc_api_mps_stores = array();
            foreach ( $selected_stores as $selected_store ) {
                if ( isset( $stores[$selected_store] ) ) {
                    $wc_api_mps_stores[$selected_store] = $stores[$selected_store];
                }
            }
            
            if ( $wc_api_mps_stores != null ) {
                wc_api_mps_integration( $product_id, $wc_api_mps_stores );
            }
        }
        
        wp_die();
    }
}

/*
 * This is a function for API integration.
 * $product_id variable return product id.
 * $stores variable return stores.
 */
if ( ! function_exists( 'wc_api_mps_integration' ) ) {
    function wc_api_mps_integration( $product_id = 0, $stores = array() ) {
        
        if ( $stores != null ) {
            $product_info = wc_get_product( $product_id );
            $product_info_data = $product_info->get_data();
            $data = array();
            if ( isset( $product_info_data['name'] ) ) {
                $data['name'] = $product_info_data['name'];
            }
            
            if ( isset( $product_info_data['slug'] ) ) {
                $data['slug'] = $product_info_data['slug'];
            }
            
            $data['type'] = $product_info->get_type();
            
            if ( isset( $product_info_data['status'] ) ) {
                $data['status'] = $product_info_data['status'];
            }
            
            if ( isset( $product_info_data['featured'] ) ) {
                $data['featured'] = $product_info_data['featured'];
            }
            
            if ( isset( $product_info_data['catalog_visibility'] ) ) {
                $data['catalog_visibility'] = $product_info_data['catalog_visibility'];
            }

            if ( isset( $product_info_data['description'] ) ) {
                $data['description'] = $product_info_data['description'];
            }
            
            if ( isset( $product_info_data['short_description'] ) ) {
                $data['short_description'] = $product_info_data['short_description'];
            }
            
            if ( isset( $product_info_data['sku'] ) ) {
                $data['sku'] = $product_info_data['sku'];
            }
            
            if ( isset( $product_info_data['price'] ) ) {
                $data['price'] = $product_info_data['price'];
            }
            
            if ( isset( $product_info_data['regular_price'] ) ) {
                $data['regular_price'] = $product_info_data['regular_price'];
            }
            
            if ( isset( $product_info_data['sale_price'] ) ) {
                $data['sale_price'] = $product_info_data['sale_price'];
            }
            
            if ( isset( $product_info_data['virtual'] ) ) {
                $data['virtual'] = $product_info_data['virtual'];
            }
            
            if ( isset( $product_info_data['manage_stock'] ) ) {
                $data['manage_stock'] = $product_info_data['manage_stock'];
            }
            
            if ( isset( $product_info_data['stock_quantity'] ) ) {
                $data['stock_quantity'] = $product_info_data['stock_quantity'];
            }
            
            if ( isset( $product_info_data['sold_individually'] ) ) {
                $data['sold_individually'] = $product_info_data['sold_individually'];
            }
            
            if ( isset( $product_info_data['weight'] ) ) {
                $data['weight'] = $product_info_data['weight'];
            }
            
            if ( isset( $product_info_data['purchase_note'] ) ) {
                $data['purchase_note'] = $product_info_data['purchase_note'];
            }
            
            if ( isset( $product_info_data['menu_order'] ) ) {
                $data['menu_order'] = $product_info_data['menu_order'];
            }
            
            if ( isset( $product_info_data['reviews_allowed'] ) ) {
                $data['reviews_allowed'] = $product_info_data['reviews_allowed'];
            }
            
            if ( isset( $product_info_data['date_on_sale_from'] ) ) {
                $date_on_sale_from = (array) $product_info_data['date_on_sale_from'];
                if ( isset( $date_on_sale_from['date'] ) ) {
                    $data['date_on_sale_from'] = $date_on_sale_from['date'];
                } else {
                    $data['date_on_sale_from'] = '';
                }
            }
            
            if ( isset( $product_info_data['date_on_sale_to'] ) ) {
                $date_on_sale_to = (array) $product_info_data['date_on_sale_to'];
                if ( isset( $date_on_sale_to['date'] ) ) {
                    $data['date_on_sale_to'] = $date_on_sale_to['date'];
                } else {
                    $data['date_on_sale_to'] = '';
                }
            }
            
            if ( isset( $product_info_data['backorders'] ) ) {
                $data['backorders'] = $product_info_data['backorders'];
            }
            
            $data['shipping_class'] = $product_info->get_shipping_class();
            
            if ( isset( $product_info_data['product_url'] ) ) {
                $data['external_url'] = $product_info_data['product_url'];
            }
            
            if ( isset( $product_info_data['button_text'] ) ) {
                $data['button_text'] = $product_info_data['button_text'];
            }
            
            $data['dimensions'] = array( 
                'length'    => ( isset( $product_info_data['length'] ) ? $product_info_data['length'] : '' ),
                'width'     => ( isset( $product_info_data['width'] ) ? $product_info_data['width'] : '' ),
                'height'    => ( isset( $product_info_data['height'] ) ? $product_info_data['height'] : '' ),
            );
            
            if ( isset( $product_info_data['category_ids'] ) ) {
                $data['categories'] = $product_info_data['category_ids'];
            }
            
            if ( isset( $product_info_data['tag_ids'] ) ) {
                $data['tags'] = $product_info_data['tag_ids'];
            }
            
            if ( isset( $product_info_data['attributes'] ) ) {
                $data['attributes'] = $product_info_data['attributes'];
            }
            
            if ( isset( $product_info_data['default_attributes'] ) && $product_info_data['default_attributes'] != null ) {
                $default_attributes = array();
                foreach ( $product_info_data['default_attributes'] as $default_attribute_slug => $default_attribute_term_slug ) {
                    $attribute_term = get_term_by( 'slug', $default_attribute_term_slug, $default_attribute_slug );
                    if ( $attribute_term != null ) {
                        $default_attributes[] = array(
                            'name'      => $default_attribute_slug,
                            'option'    => $attribute_term->name,
                        );
                    } else {
                        $default_attributes[] = array(
                            'name'      => $default_attribute_slug,
                            'option'    => $default_attribute_term_slug,
                        );
                    }
                }
                
                $data['default_attributes'] = $default_attributes;
            }
            
            if ( isset( $product_info_data['image_id'] ) && $product_info_data['image_id'] ) {
                $image_id = $product_info_data['image_id'];
                $data['images'][] = array(
                    'id'        => $image_id,
                    'src'       => wp_get_attachment_url( $image_id ),
                    'position'  => 0,
                );
                
                $product_info_data['images'] = $data['images'];
            }
            
            if ( isset( $product_info_data['gallery_image_ids'] ) && $product_info_data['gallery_image_ids'] ) {
                $gallery_image_ids = $product_info_data['gallery_image_ids'];
                if ( $gallery_image_ids != null ) {
                    $position = 1;
                    foreach ( $gallery_image_ids as $gallery_image_id ) {
                        $data['images'][] = array(
                            'id'        => $gallery_image_id,
                            'src'       => wp_get_attachment_url( $gallery_image_id ),
                            'position'  => $position,
                        );
                        
                        $position ++;
                    }
                }
                
                $product_info_data['images'] = $data['images'];
            }
            
            if ( $data['type'] == 'variable' ) {
                $variations = $product_info->get_available_variations();
                if ( $variations != null ) {
                    $available_variations = array();
                    foreach ( $variations as $variation ) {
                        $variation_id = $variation['variation_id'];
                        $available_variations[] = wc_get_product( $variation_id );
                    }

                    $product_info_data['variations'] = $available_variations;
                }
            }
            
            if ( isset( $product_info_data['upsell_ids'] ) ) {
                $data['upsell_ids'] = $product_info_data['upsell_ids'];
            }
            
            if ( isset( $product_info_data['cross_sell_ids'] ) ) {
                $data['cross_sell_ids'] = $product_info_data['cross_sell_ids'];
            }
            
            $meta_fields = wc_api_mps_product_meta_fields( $product_id );
            if ( $meta_fields != null ) {
                $meta_data = array();
                foreach ( $meta_fields as $meta_field ) {
                    $meta_data[] = array(
                        'key'   => $meta_field,
                        'value' => get_post_meta( $product_id, $meta_field, true ),
                    );
                }
                
                $data['meta_data'] = $meta_data;
            }
            
            $wc_api_mps = get_post_meta( $product_id, 'mpsrel', true );
            if ( ! is_array( $wc_api_mps ) ) {
                $wc_api_mps = array();
            }
            
            $temp_data = $data;
            foreach ( $stores as $key => $value ) {
                $data = $temp_data;
                $url = $key;
                $consumer_key = $value['consumer_key'];
                $consumer_secret = $value['consumer_secret'];
                if ( $value['status'] ) {
                    $api = new WC_API_MPS( $url, $consumer_key, $consumer_secret );
                    
                    $exclude_meta_data = $value['exclude_meta_data'];
                    if ( $exclude_meta_data ) {
                        $exclude_meta_data = explode( ',', $exclude_meta_data );
                        if ( is_array( $exclude_meta_data ) ) {
                            if ( isset( $data['meta_data'] ) && $data['meta_data'] != null ) {
                                foreach ( $data['meta_data'] as $meta_data_key => $meta_data_value ) {
                                    if ( in_array( $meta_data_value['key'], $exclude_meta_data ) ) {
                                        unset( $data['meta_data'][$meta_data_key] );
                                    }
                                }
                            }

                            foreach ( $exclude_meta_data as $exclude_meta_key ) {
                                if ( in_array( $exclude_meta_key, $data ) ) {
                                    unset( $data[$exclude_meta_key] );
                                }
                            }
                        }
                    }
                        
                    $store_sync = 1;
                    $exclude_categories_products = ( isset( $value['exclude_categories_products'] ) ? $value['exclude_categories_products'] : array() );
                    if ( isset( $data['categories'] ) && $data['categories'] != null ) {
                        $categories = array();
                        foreach ( $product_info_data['category_ids'] as $category_id ) {
                            if ( in_array( $category_id, $exclude_categories_products ) ) {
                                $store_sync = 0;
                            }
                            
                            if ( $store_sync ) {
                                $categories[]['id'] = wc_api_mps_destination_category_id( $api, $url, $category_id );
                            }
                        }
                        
                        $data['categories'] = $categories;
                    }
                    
                    if ( $store_sync ) {
                        $price_adjustment = $value['price_adjustment'];
                        if ( $price_adjustment ) {
                            $price_adjustment_type = $value['price_adjustment_type'];
                            $price_adjustment_operation = $value['price_adjustment_operation'];
                            $price_adjustment_amount = $value['price_adjustment_amount'];
                            if ( isset( $product_info_data['regular_price'] ) && $product_info_data['regular_price'] ) {
                                if ( $price_adjustment_type == 'percentage' && $price_adjustment_amount ) {
                                    if ( $price_adjustment_operation == 'plus' ) {
                                        $data['regular_price'] = ( $product_info_data['regular_price'] + ( ( $product_info_data['regular_price'] * $price_adjustment_amount ) / 100 ) );
                                        if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                            $data['sale_price'] = ( $product_info_data['sale_price'] + ( ( $product_info_data['sale_price'] * $price_adjustment_amount ) / 100 ) );
                                        }
                                    } else if ( $price_adjustment_operation == 'minus' ) {
                                        $data['regular_price'] = ( $product_info_data['regular_price'] - ( ( $product_info_data['regular_price'] * $price_adjustment_amount ) / 100 ) );
                                        if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                            $data['sale_price'] = ( $product_info_data['sale_price'] - ( ( $product_info_data['sale_price'] * $price_adjustment_amount ) / 100 ) );
                                        }
                                    }
                                    else if ( $price_adjustment_operation == 'multiplied' ) {
                                        $data['regular_price'] = ( $product_info_data['regular_price'] * ( ( $product_info_data['regular_price'] * $price_adjustment_amount ) / 100 ) );
                                        if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                            $data['sale_price'] = ( $product_info_data['sale_price'] * ( ( $product_info_data['sale_price'] * $price_adjustment_amount ) / 100 ) );
                                        }
                                    } else if ( $price_adjustment_operation == 'divided' ) {
                                        $data['regular_price'] = ( $product_info_data['regular_price'] / ( ( $product_info_data['regular_price'] * $price_adjustment_amount ) / 100 ) );
                                        if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                            $data['sale_price'] = ( $product_info_data['sale_price'] / ( ( $product_info_data['sale_price'] * $price_adjustment_amount ) / 100 ) );
                                        }
                                    }
                                } else if ( $price_adjustment_type == 'fixed' && $price_adjustment_amount ) {
                                    if ( $price_adjustment_operation == 'plus' ) {
                                        $data['regular_price'] = $product_info_data['regular_price'] + $price_adjustment_amount;
                                        if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                            $data['sale_price'] = $product_info_data['sale_price'] + $price_adjustment_amount;
                                        }
                                    } else if ( $price_adjustment_operation == 'minus' ) {
                                        $data['regular_price'] = $product_info_data['regular_price'] - $price_adjustment_amount;
                                        if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                            $data['sale_price'] = $product_info_data['sale_price'] - $price_adjustment_amount;
                                        }
                                    }else if ( $price_adjustment_operation == 'multiplied' ) {
                                        $data['regular_price'] = $product_info_data['regular_price'] * $price_adjustment_amount;
                                        if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                            $data['sale_price'] = $product_info_data['sale_price'] * $price_adjustment_amount;
                                        }
                                    } else if ( $price_adjustment_operation == 'divided' ) {
                                    $data['regular_price'] = $product_info_data['regular_price'] / $price_adjustment_amount;
                                    if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                        $data['sale_price'] = $product_info_data['sale_price'] / $price_adjustment_amount;
                                    }
                                }
                                }
                                
                                if ( $data['sale_price'] ) {
                                    $data['price'] = "".$data['sale_price']."";
                                } else {
                                    $data['price'] = "".$data['regular_price']."";
                                }
                                
                                $data['regular_price'] = "".$data['regular_price']."";
                                $data['sale_price'] = "".$data['sale_price']."";
                            }
                        }
                        
                        if ( isset( $data['tags'] ) && $data['tags'] != null ) {
                            $tags = array();
                            foreach ( $product_info_data['tag_ids'] as $tag_id ) {
                                $tags[]['id'] = wc_api_mps_destination_tag_id( $api, $url, $tag_id );
                            }

                            $data['tags'] = $tags;
                        }

                        if ( isset( $data['attributes'] ) && $data['attributes'] != null ) {
                            $attributes = array();
                            foreach ( $product_info_data['attributes'] as $attribute_slug => $attribute_data ) {
                                $attribute_data_details = $attribute_data->get_data();
                                if ( isset( $attribute_data_details['id'] ) && $attribute_data_details['id'] ) {
                                    $destination_attribute = wc_api_mps_destination_attribute_id( $api, $url, $attribute_slug, $attribute_data_details );
                                    if ( $destination_attribute != null ) {
                                        $attributes[] = $destination_attribute;
                                    }
                                } else {
                                    $attributes[] = array(
                                        'id'        => $attribute_data_details['id'],
                                        'name'      => $attribute_data_details['name'],
                                        'position'  => $attribute_data_details['position'],
                                        'visible'   => $attribute_data_details['visible'],
                                        'variation' => $attribute_data_details['variation'],
                                        'options'   => $attribute_data_details['options'],
                                    );
                                }
                            }

                            $data['attributes'] = $attributes;
                        }

                        if ( isset( $data['images'] ) && $data['images'] != null ) {
                            $images = array();
                            foreach( $product_info_data['images'] as $image_data ) {
                                $image_id = $image_data['id'];
                                unset( $image_data['id'] );
                                $destination_image_id = wc_api_mps_get_destination_image_id( $url, $image_id );
                                if ( $destination_image_id ) {
                                    unset( $image_data['src'] );
                                    $image_data['id'] = $destination_image_id;
                                }

                                $images[] = $image_data;
                            }

                            $data['images'] = $images;
                        }

                        if ( isset( $data['upsell_ids'] ) && $data['upsell_ids'] != null ) {
                            $upsell_ids = array();
                            foreach ( $product_info_data['upsell_ids'] as $upsell_id ) {
                                $wc_api_mps_upsell = get_post_meta( $upsell_id, 'mpsrel', true );
                                if ( ! is_array( $wc_api_mps_upsell ) ) {
                                    $wc_api_mps_upsell = array();
                                }

                                if ( isset( $wc_api_mps_upsell[$url] ) ) {
                                    $upsell_ids[] = $wc_api_mps_upsell[$url];
                                }
                            }

                            $data['upsell_ids'] = $upsell_ids;
                        }

                        if ( isset( $data['cross_sell_ids'] ) && $data['cross_sell_ids'] != null ) {
                            $cross_sell_ids = array();
                            foreach ( $product_info_data['cross_sell_ids'] as $cross_sell_id ) {
                                $wc_api_mps_cross_sell = get_post_meta( $cross_sell_id, 'mpsrel', true );
                                if ( ! is_array( $wc_api_mps_cross_sell ) ) {
                                    $wc_api_mps_cross_sell = array();
                                }

                                if ( isset( $wc_api_mps_cross_sell[$url] ) ) {
                                    $cross_sell_ids[] = $wc_api_mps_cross_sell[$url];
                                }
                            }

                            $data['cross_sell_ids'] = $cross_sell_ids;
                        }

                        $destination_product_id = 0;
                        if ( isset( $wc_api_mps[$url] ) ) {
                            $destination_product_id = $wc_api_mps[$url];
                            $product = $api->getProduct( $destination_product_id );
                            if ( ! isset( $product->id ) ) {
                                $destination_product_id = 0;
                            }
                        } else {
                            $old_products_sync_by = get_option( 'wc_api_mps_old_products_sync_by' );
                            if ( $old_products_sync_by == 'sku' && $data['sku'] ) {
                                $products = $api->getProducts( $data['sku'] );
                            } else {
                                $products = $api->getProducts( $data['slug'] );
                            }
                            
                            if ( $products != null && isset( $products[0]->id  ) ) {
                                $destination_product_id = $products[0]->id;
                            }
                        }

                        if ( $destination_product_id ) {
                            $product = $api->updateProduct( $data, $destination_product_id );
                        } else {
                            $product = $api->addProduct( $data );
                            if ( isset( $product->id ) ) {
                                $destination_product_id = $product->id;
                            }
                        }

                        if ( isset( $product->id ) ) {
                            $destination_product_id = $product->id;
                            if ( $product->images != null ) {
                                $destination_images = array();
                                foreach ( $product->images as $destination_image ) {
                                    $destination_images[$destination_image->position] = $destination_image->id;
                                }

                                if ( isset( $product_info_data['images'] ) && $product_info_data['images'] != null ) {
                                    foreach( $product_info_data['images'] as $image_data ) {
                                        $destination_image_id = ( isset( $destination_images[$image_data['position']] ) ? $destination_images[$image_data['position']] : 0 );
                                        if ( $destination_image_id ) {
                                            wc_api_mps_set_destination_image_id( $url, $image_data['id'], $destination_image_id );
                                        }
                                    }
                                }
                            }
                        }

                        if ( $destination_product_id ) {
                            $wc_api_mps[$url] = $destination_product_id;

                            if ( isset( $product_info_data['variations'] ) ) {
                                wc_api_mps_destination_variations( $api, $url, $product_info_data['variations'], $destination_product_id, $value );
                            }
                        }
                    }
                }
            }
            
            update_post_meta( $product_id, 'mpsrel', $wc_api_mps );
        }
    }
}

/*
 * This is a function for destination category id.
 * $api variable return API object.
 * $url variable return store URL.
 * $category_id variable return category id.
 */
if ( ! function_exists( 'wc_api_mps_destination_category_id' ) ) {
    function wc_api_mps_destination_category_id( $api, $url, $category_id ) {
        
        $category = get_term( $category_id );
        $wc_api_mps = get_term_meta( $category_id, 'mpsrel', true );
        if ( ! is_array( $wc_api_mps ) ) {
            $wc_api_mps = array();
        }
        
        $destination_category_id = 0;
        if ( isset( $wc_api_mps[$url] ) ) {
            $destination_category_id = $wc_api_mps[$url];
        } else {
            $categories = $api->getCategories( $category->slug );
            if ( $categories != null && isset( $categories[0]->id  ) ) {
                $destination_category_id = $categories[0]->id;
            }
        }
        
        $data = array(
            'name'          => $category->name,
            'slug'          => $category->slug,
            'description'   => $category->description,
        );
        
        if ( $category->parent ) {
            $data['parent'] = wc_api_mps_destination_category_id( $api, $url, $category->parent );
        } else {
            if ( $destination_category_id ) {
                $data['parent'] = 0;
            }
        }
        
        if ( $destination_category_id ) {
            $category = $api->updateCategory( $data, $destination_category_id );
        } else {
            $category = $api->addCategory( $data );
            if ( isset( $category->id ) ) {
                $destination_category_id = $category->id;
            }
        }
        
        $wc_api_mps[$url] = $destination_category_id;
        update_term_meta( $category_id, 'mpsrel', $wc_api_mps );
        
        return $destination_category_id;
    }
}

/*
 * This is a function for destination tag id.
 * $api variable return API object.
 * $url variable return store URL.
 * $tag_id variable return tag id.
 */
if ( ! function_exists( 'wc_api_mps_destination_tag_id' ) ) {
    function wc_api_mps_destination_tag_id( $api, $url, $tag_id ) {
        
        $tag = get_term( $tag_id );
        $wc_api_mps = get_term_meta( $tag_id, 'mpsrel', true );
        if ( ! is_array( $wc_api_mps ) ) {
            $wc_api_mps = array();
        }
        
        $destination_tag_id = 0;
        if ( isset( $wc_api_mps[$url] ) ) {
            $destination_tag_id = $wc_api_mps[$url];
        } else {
            $tags = $api->getTags( $tag->slug );
            if ( $tags != null && isset( $tags[0]->id  ) ) {
                $destination_tag_id = $tags[0]->id;
            }
        }
        
        $data = array(
            'name'          => $tag->name,
            'slug'          => $tag->slug,
            'description'   => $tag->description,
        );
        
        if ( $destination_tag_id ) {
            $tag = $api->updateTag( $data, $destination_tag_id );
        } else {
            $tag = $api->addTag( $data );
            if ( isset( $tag->id ) ) {
                $destination_tag_id = $tag->id;
            }
        }
        
        $wc_api_mps[$url] = $destination_tag_id;
        update_term_meta( $tag_id, 'mpsrel', $wc_api_mps );
        
        return $destination_tag_id;
    }
}

/*
 * This is a function for destination attribute id.
 * $api variable return API object.
 * $url variable return store URL.
 * $attribute_slug variable return attribute slug.
 * $attribute_data variable return attribute data.
 */
if ( ! function_exists( 'wc_api_mps_destination_attribute_id' ) ) {
    function wc_api_mps_destination_attribute_id( $api, $url, $attribute_slug, $attribute_data ) {
        
        $attribute_id = 0;
        $attributes = $api->getAttributes();        
        if ( $attributes != null ) {
            foreach ( $attributes as $attribute ) {
                if ( $attribute->slug == $attribute_slug ) {
                    $attribute_id = $attribute->id;
                }
            }
            
            if ( ! $attribute_id ) {
                $taxonomy = get_taxonomy( $attribute_slug );
                if ( $taxonomy != null ) {
                    $data = array(
                        'name'          => $taxonomy->labels->singular_name,
                        'slug'          => $attribute_slug,
                    );

                    $attribute = $api->addAttribute( $data );
                    if ( isset( $attribute->id ) ) {
                        $attribute_id = $attribute->id;
                    }
                }
            }
        } else {
            $taxonomy = get_taxonomy( $attribute_slug );
            if ( $taxonomy != null ) {
                $data = array(
                    'name'          => $taxonomy->labels->singular_name,
                    'slug'          => $attribute_slug,
                );
                
                $attribute = $api->addAttribute( $data );
                if ( isset( $attribute->id ) ) {
                    $attribute_id = $attribute->id;
                }
            }            
        }
        
        $destination_attribute = array();
        if ( $attribute_id ) {
            $destination_attribute = array(
                'id'        => $attribute_id,
                'position'  => $attribute_data['position'],
                'visible'   => $attribute_data['visible'],
                'variation' => $attribute_data['variation'],
                'options'   => array(),
            );
            
            if ( $attribute_data['options'] != null ) {
                $attribute_terms = array();
                foreach ( $attribute_data['options'] as $attribute_term_id ) {
                    $attribute_terms[] = wc_api_mps_destination_attribute_term_id( $api, $url, $attribute_term_id, $attribute_id );
                }
                
                $destination_attribute['options'] = $attribute_terms;
            }
        }
        
        return $destination_attribute;
    }
}

/*
 * This is a function for destination attribute term id.
 * $api variable return API object.
 * $url variable return store URL.
 * $attribute_term_id variable return attribute term id.
 * $attribute_id variable return attribute id.
 */
if ( ! function_exists( 'wc_api_mps_destination_attribute_term_id' ) ) {
    function wc_api_mps_destination_attribute_term_id( $api, $url, $attribute_term_id, $attribute_id ) {
        
        $attribute_term = get_term( $attribute_term_id );
        $wc_api_mps = get_term_meta( $attribute_term_id, 'mpsrel', true );
        if ( ! is_array( $wc_api_mps ) ) {
            $wc_api_mps = array();
        }

        $destination_attribute_term_id = 0;
        if ( isset( $wc_api_mps[$url] ) ) {
            $destination_attribute_term_id = $wc_api_mps[$url];
        } else {
            $attribute_terms = $api->getAttributeTerms( $attribute_term->slug, $attribute_id );
            if ( $attribute_terms != null && isset( $attribute_terms[0]->id  ) ) {
                $destination_attribute_term_id = $attribute_terms[0]->id;
            }
        }
        
        $attribute_term_name = $attribute_term->name;
        
        $data = array(
            'name'          => $attribute_term->name,
            'slug'          => $attribute_term->slug,
            'description'   => $attribute_term->description,
        );

        if ( $destination_attribute_term_id ) {
            $attribute_term = $api->updateAttributeTerm( $data, $destination_attribute_term_id, $attribute_id );
        } else {
            $attribute_term = $api->addAttributeTerm( $data, $attribute_id );
            if ( isset( $attribute_term->id ) ) {
                $destination_attribute_term_id = $attribute_term->id;
            }
        }
        
        $wc_api_mps[$url] = $destination_attribute_term_id;
        update_term_meta( $attribute_term_id, 'mpsrel', $wc_api_mps );

        return $attribute_term_name;
    }
}

/*
 * This is a function for destination image id.
 * $url variable return store URL.
 * $image_id variable return image id.
 */
if ( ! function_exists( 'wc_api_mps_get_destination_image_id' ) ) {
    function wc_api_mps_get_destination_image_id( $url, $image_id ) {
        
        $destination_image_id = 0;
        $wc_api_mps = get_post_meta( $image_id, 'mpsrel', true );
        if ( ! is_array( $wc_api_mps ) ) {
            $wc_api_mps = array();
        }
        
        if ( isset( $wc_api_mps[$url] ) ) {
            $destination_image_id = $wc_api_mps[$url];
        }
        
        return $destination_image_id;
    }
}

/*
 * This is a function for set destination image id.
 * $url variable return store URL.
 * $image_id variable return image id.
 * $destination_image_id variable return destination image id.
 */
if ( ! function_exists( 'wc_api_mps_set_destination_image_id' ) ) {
    function wc_api_mps_set_destination_image_id( $url, $image_id, $destination_image_id ) {
        
        $wc_api_mps = get_post_meta( $image_id, 'mpsrel', true );
        if ( ! is_array( $wc_api_mps ) ) {
            $wc_api_mps = array();
        }
        
        $wc_api_mps[$url] = $destination_image_id;        
        update_post_meta( $image_id, 'mpsrel', $wc_api_mps );
    }
}

/*
 * This is a function for destination variations.
 * $api variable return API object.
 * $url variable return store URL.
 * $variations variable return variations.
 * $destination_product_id variable return destination product id.
 */
if ( ! function_exists( 'wc_api_mps_destination_variations' ) ) {
    function wc_api_mps_destination_variations( $api, $url, $variations, $destination_product_id, $value ) {
        
        $destination_product_variations = array();
        $destination_variations = $api->getProductVariations( $destination_product_id );
        if ( $destination_variations != null ) {
            foreach ( $destination_variations as $destination_variation ) {
                $attributes = $destination_variation->attributes;
                if ( $attributes != null ) {
                    $options = '';
                    foreach ( $attributes as $attribute ) {
                        $options .= strtolower( $attribute->option );
                    }
                    
                    if ( $options ) {
                        $options = str_replace( ' ', '-', $options );
                        $destination_product_variations[$options] = $destination_variation->id;
                    }
                }
            }
        }
        
        if ( $variations != null ) {
            foreach ( $variations as $variation ) {
                $product_info = $variation;
                $product_info_data = $product_info->get_data();
                $variation_id = $product_info_data['id'];
                $wc_api_mps = get_post_meta( $variation_id, 'mpsrel', true );
                if ( ! is_array( $wc_api_mps ) ) {
                    $wc_api_mps = array();
                }
                
                $destination_variation_id = 0;
                if ( isset( $wc_api_mps[$url] ) ) {
                    $destination_variation_id = $wc_api_mps[$url];
                    $product_variation = $api->getProductVariation( $destination_product_id, $destination_variation_id );
                    if ( ! isset( $product_variation->id ) ) {
                        $destination_variation_id = 0;
                    }
                } else {
                    $attributes_key = '';
                    if ( isset( $product_info_data['attributes'] ) && $product_info_data['attributes'] != null ) {
                        $attributes_key = implode( '', $product_info_data['attributes'] );
                        $attributes_key = strtolower( $attributes_key );
                        $attributes_key = str_replace( ' ', '-', $attributes_key );
                    }
                    
                    if ( $attributes_key && isset( $destination_product_variations[$attributes_key] ) ) {
                        $destination_variation_id = $destination_product_variations[$attributes_key];
                    }
                }
                
                $data = array();
                if ( isset( $product_info_data['name'] ) ) {
                    $data['name'] = $product_info_data['name'];
                }

                if ( isset( $product_info_data['description'] ) ) {
                    $data['description'] = $product_info_data['description'];
                }

                if ( isset( $product_info_data['sku'] ) ) {
                    $data['sku'] = $product_info_data['sku'];
                }

                if ( isset( $product_info_data['price'] ) ) {
                    $data['price'] = $product_info_data['price'];
                }

                if ( isset( $product_info_data['regular_price'] ) ) {
                    $data['regular_price'] = $product_info_data['regular_price'];
                }

                if ( isset( $product_info_data['sale_price'] ) ) {
                    $data['sale_price'] = $product_info_data['sale_price'];
                }

                if ( isset( $product_info_data['virtual'] ) ) {
                    $data['virtual'] = $product_info_data['virtual'];
                }

                if ( isset( $product_info_data['manage_stock'] ) ) {
                    $data['manage_stock'] = $product_info_data['manage_stock'];
                }

                if ( isset( $product_info_data['stock_quantity'] ) ) {
                    $data['stock_quantity'] = $product_info_data['stock_quantity'];
                }

                if ( isset( $product_info_data['weight'] ) ) {
                    $data['weight'] = $product_info_data['weight'];
                }

                if ( isset( $product_info_data['menu_order'] ) ) {
                    $data['menu_order'] = $product_info_data['menu_order'];
                }

                if ( isset( $product_info_data['date_on_sale_from'] ) ) {
                    $date_on_sale_from = (array) $product_info_data['date_on_sale_from'];
                    if ( isset( $date_on_sale_from['date'] ) ) {
                        $data['date_on_sale_from'] = $date_on_sale_from['date'];
                    } else {
                        $data['date_on_sale_from'] = '';
                    }
                }

                if ( isset( $product_info_data['date_on_sale_to'] ) ) {
                    $date_on_sale_to = (array) $product_info_data['date_on_sale_to'];
                    if ( isset( $date_on_sale_to['date'] ) ) {
                        $data['date_on_sale_to'] = $date_on_sale_to['date'];
                    } else {
                        $data['date_on_sale_to'] = '';
                    }
                }

                if ( isset( $product_info_data['backorders'] ) ) {
                    $data['backorders'] = $product_info_data['backorders'];
                }

                $data['shipping_class'] = $product_info->get_shipping_class();
                
                if ( isset( $product_info_data['visible'] ) ) {
                    $data['visible'] = $product_info_data['visible'];
                }

                $data['dimensions'] = array( 
                    'length'    => ( isset( $product_info_data['length'] ) ? $product_info_data['length'] : '' ),
                    'width'     => ( isset( $product_info_data['width'] ) ? $product_info_data['width'] : '' ),
                    'height'    => ( isset( $product_info_data['height'] ) ? $product_info_data['height'] : '' ),
                );
                
                if ( $product_info_data['attributes'] ) {
                    $attributes = array();
                    foreach ( $product_info_data['attributes'] as $attribute_slug => $attribute_term_slug ) {
                        $attribute_term = get_term_by( 'slug', $attribute_term_slug, $attribute_slug );
                        if ( $attribute_term != null ) {
                            $attributes[] = array(
                                'name'      => $attribute_slug,
                                'option'    => $attribute_term->name,
                            );
                        } else {
                            $attributes[] = array(
                                'name'      => $attribute_slug,
                                'option'    => $attribute_term_slug,
                            );
                        }
                    }
                    
                    if ( $attributes != null ) {
                        $data['attributes'] = $attributes;
                    }
                }
                
                if ( isset( $product_info_data['image_id'] ) && $product_info_data['image_id'] ) {
                    $image_id = $product_info_data['image_id'];
                    $destination_image_id = wc_api_mps_get_destination_image_id( $url, $image_id );
                    if ( $destination_image_id ) {
                        $data['image']['id'] = $destination_image_id;
                    } else {
                        $data['image']['src'] = wp_get_attachment_url( $image_id );
                    }
                }
                
                $price_adjustment = $value['price_adjustment'];
                if ( $price_adjustment ) {
                    $price_adjustment_type = $value['price_adjustment_type'];
                    $price_adjustment_operation = $value['price_adjustment_operation'];
                    $price_adjustment_amount = $value['price_adjustment_amount'];
                    if ( isset( $product_info_data['regular_price'] ) && $product_info_data['regular_price'] ) {
                        if ( $price_adjustment_type == 'percentage' && $price_adjustment_amount ) {
                            if ( $price_adjustment_operation == 'plus' ) {
                                $data['regular_price'] = ( $product_info_data['regular_price'] + ( ( $product_info_data['regular_price'] * $price_adjustment_amount ) / 100 ) );
                                if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                    $data['sale_price'] = ( $product_info_data['sale_price'] + ( ( $product_info_data['sale_price'] * $price_adjustment_amount ) / 100 ) );
                                }
                            } else if ( $price_adjustment_operation == 'minus' ) {
                                $data['regular_price'] = ( $product_info_data['regular_price'] - ( ( $product_info_data['regular_price'] * $price_adjustment_amount ) / 100 ) );
                                if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                    $data['sale_price'] = ( $product_info_data['sale_price'] - ( ( $product_info_data['sale_price'] * $price_adjustment_amount ) / 100 ) );
                                }
                            } else if ( $price_adjustment_operation == 'multiplied' ) {
                                $data['regular_price'] = ( $product_info_data['regular_price'] * ( ( $product_info_data['regular_price'] * $price_adjustment_amount ) / 100 ) );
                                if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                    $data['sale_price'] = ( $product_info_data['sale_price'] * ( ( $product_info_data['sale_price'] * $price_adjustment_amount ) / 100 ) );
                                }
                            }else if ( $price_adjustment_operation == 'divided' ) {
                                $data['regular_price'] = ( $product_info_data['regular_price'] / ( ( $product_info_data['regular_price'] * $price_adjustment_amount ) / 100 ) );
                                if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                    $data['sale_price'] = ( $product_info_data['sale_price'] / ( ( $product_info_data['sale_price'] * $price_adjustment_amount ) / 100 ) );
                                }
                            }
                        } else if ( $price_adjustment_type == 'fixed' && $price_adjustment_amount ) {
                            if ( $price_adjustment_operation == 'plus' ) {
                                $data['regular_price'] = $product_info_data['regular_price'] + $price_adjustment_amount;
                                if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                    $data['sale_price'] = $product_info_data['sale_price'] + $price_adjustment_amount;
                                }
                            } else if ( $price_adjustment_operation == 'minus' ) {
                                $data['regular_price'] = $product_info_data['regular_price'] - $price_adjustment_amount;
                                if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                    $data['sale_price'] = $product_info_data['sale_price'] - $price_adjustment_amount;
                                }
                            } else if ( $price_adjustment_operation == 'multiplied' ) {
                                $data['regular_price'] = $product_info_data['regular_price'] * $price_adjustment_amount;
                                if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                    $data['sale_price'] = $product_info_data['sale_price'] * $price_adjustment_amount;
                                }
                            }
                            else if ( $price_adjustment_operation == 'divided' ) {
                                $data['regular_price'] = $product_info_data['regular_price'] / $price_adjustment_amount;
                                if ( isset( $product_info_data['sale_price'] ) && $product_info_data['sale_price'] ) {
                                    $data['sale_price'] = $product_info_data['sale_price'] / $price_adjustment_amount;
                                }
                            }
                        }

                        if ( $data['sale_price'] ) {
                            $data['price'] = "".$data['sale_price']."";
                        } else {
                            $data['price'] = "".$data['regular_price']."";
                        }

                        $data['regular_price'] = "".$data['regular_price']."";
                        $data['sale_price'] = "".$data['sale_price']."";
                    }
                }
                
                $meta_fields = wc_api_mps_product_variation_meta_fields( $variation_id );
                if ( $meta_fields != null ) {
                    $meta_data = array();
                    foreach ( $meta_fields as $meta_field ) {
                        $meta_data[] = array(
                            'key'   => $meta_field,
                            'value' => get_post_meta( $variation_id, $meta_field, true ),
                        );
                    }

                    $data['meta_data'] = $meta_data;
                }
                
                $exclude_meta_data = $value['exclude_meta_data'];
                if ( $exclude_meta_data ) {
                    $exclude_meta_data = explode( ',', $exclude_meta_data );
                    if ( is_array( $exclude_meta_data ) ) {
                        if ( isset( $data['meta_data'] ) && $data['meta_data'] != null ) {
                            foreach ( $data['meta_data'] as $meta_data_key => $meta_data_value ) {
                                if ( in_array( $meta_data_value['key'], $exclude_meta_data ) ) {
                                    unset( $data['meta_data'][$meta_data_key] );
                                }
                            }
                        }

                        foreach ( $exclude_meta_data as $exclude_meta_key ) {
                            if ( in_array( $exclude_meta_key, $data ) ) {
                                unset( $data[$exclude_meta_key] );
                            }
                        }
                    }
                }
                
                if ( $destination_variation_id ) {
                    $product_variation = $api->updateProductVariation( $data, $destination_product_id, $destination_variation_id );
                } else {
                    $product_variation = $api->addProductVariation( $data, $destination_product_id );
                    if ( isset( $product_variation->id ) ) {
                        $destination_variation_id = $product_variation->id;
                    }
                }
                
                if ( isset( $product_variation->id ) ) {
                    if ( $product_variation->image != null ) {
                        wc_api_mps_set_destination_image_id( $url, $product_info_data['image_id'], $product_variation->image->id );
                    }
                }
                
                $wc_api_mps[$url] = $destination_variation_id;
                update_post_meta( $variation_id, 'mpsrel', $wc_api_mps );
            }
        }
    }
}

/*
 * This is a function that return product meta fields.
 */
if ( ! function_exists( 'wc_api_mps_product_meta_fields' ) ) {
    function wc_api_mps_product_meta_fields( $product_id ) {
        
        global $wpdb;

        $fields = array();
        $results = $wpdb->get_results( "SELECT DISTINCT meta_key FROM ".$wpdb->prefix."postmeta as pm INNER JOIN ".$wpdb->prefix."posts as p ON pm.post_id = p.ID WHERE p.post_type='product' AND p.ID=$product_id" );
        if ( $results != null ) {
            foreach ( $results as $result ) {
                if ( $result->meta_key != '_wc_review_count' &&
                    $result->meta_key != '_wc_rating_count' &&
                    $result->meta_key != '_wc_average_rating' &&
                    $result->meta_key != '_sku' &&
                    $result->meta_key != '_regular_price' &&
                    $result->meta_key != '_sale_price' &&
                    $result->meta_key != '_sale_price_dates_from' &&
                    $result->meta_key != '_sale_price_dates_to' &&
                    $result->meta_key != 'total_sales' &&
                    $result->meta_key != '_tax_status' &&
                    $result->meta_key != '_tax_class' &&
                    $result->meta_key != '_manage_stock' &&
                    $result->meta_key != '_backorders' &&
                    $result->meta_key != '_weight' &&
                    $result->meta_key != '_sold_individually' &&
                    $result->meta_key != '_length' &&
                    $result->meta_key != '_width' &&
                    $result->meta_key != '_height' &&
                    $result->meta_key != '_upsell_ids' &&
                    $result->meta_key != '_crosssell_ids' &&
                    $result->meta_key != '_purchase_note' &&
                    $result->meta_key != '_default_attributes' &&
                    $result->meta_key != '_virtual' &&
                    $result->meta_key != '_downloadable' &&
                    $result->meta_key != '_product_image_gallery' &&
                    $result->meta_key != '_download_limit' &&
                    $result->meta_key != '_download_expiry' &&
                    $result->meta_key != '_stock' &&
                    $result->meta_key != '_stock_status' &&
                    $result->meta_key != '_downloadable_files' &&
                    $result->meta_key != '_product_attributes' &&
                    $result->meta_key != '_wpcom_is_markdown' &&
                    $result->meta_key != '_thumbnail_id' &&
                    $result->meta_key != '_edit_lock' &&
                    $result->meta_key != '_price' &&
                    $result->meta_key != '_children' &&
                    $result->meta_key != '_product_url' &&
                    $result->meta_key != '_button_text' &&
                    $result->meta_key != 'wc_api_mps_disable_auto_sync' &&
                    $result->meta_key != 'mpsrel'
                ) {
                    $fields[] = $result->meta_key;
                }
            }
        }
        
        return $fields;
    }
}

/*
 * This is a function that return product variation meta fields.
 */
if ( ! function_exists( 'wc_api_mps_product_variation_meta_fields' ) ) {
    function wc_api_mps_product_variation_meta_fields( $variation_id ) {
        
        global $wpdb;

        $fields = array();
        $results = $wpdb->get_results( "SELECT DISTINCT meta_key FROM ".$wpdb->prefix."postmeta as pm INNER JOIN ".$wpdb->prefix."posts as p ON pm.post_id = p.ID WHERE p.post_type='product_variation' AND p.ID=$variation_id" );
        if ( $results != null ) {
            foreach ( $results as $result ) {
                if ( $result->meta_key != '_wc_review_count' &&
                    $result->meta_key != '_wc_rating_count' &&
                    $result->meta_key != '_wc_average_rating' &&
                    $result->meta_key != '_sku' &&
                    $result->meta_key != '_regular_price' &&
                    $result->meta_key != '_sale_price' &&
                    $result->meta_key != '_sale_price_dates_from' &&
                    $result->meta_key != '_sale_price_dates_to' &&
                    $result->meta_key != 'total_sales' &&
                    $result->meta_key != '_tax_status' &&
                    $result->meta_key != '_tax_class' &&
                    $result->meta_key != '_manage_stock' &&
                    $result->meta_key != '_backorders' &&
                    $result->meta_key != '_weight' &&
                    $result->meta_key != '_sold_individually' &&
                    $result->meta_key != '_length' &&
                    $result->meta_key != '_width' &&
                    $result->meta_key != '_height' &&
                    $result->meta_key != '_upsell_ids' &&
                    $result->meta_key != '_crosssell_ids' &&
                    $result->meta_key != '_purchase_note' &&
                    $result->meta_key != '_default_attributes' &&
                    $result->meta_key != '_virtual' &&
                    $result->meta_key != '_downloadable' &&
                    $result->meta_key != '_product_image_gallery' &&
                    $result->meta_key != '_download_limit' &&
                    $result->meta_key != '_download_expiry' &&
                    $result->meta_key != '_stock' &&
                    $result->meta_key != '_stock_status' &&
                    $result->meta_key != '_downloadable_files' &&
                    $result->meta_key != '_product_attributes' &&
                    $result->meta_key != '_wpcom_is_markdown' &&
                    $result->meta_key != '_thumbnail_id' &&
                    $result->meta_key != '_edit_lock' &&
                    $result->meta_key != '_price' &&
                    $result->meta_key != '_children' &&
                    $result->meta_key != '_product_url' &&
                    $result->meta_key != '_button_text' &&
                    $result->meta_key != 'wc_api_mps_disable_auto_sync' &&
                    $result->meta_key != 'mpsrel'
                ) {
                    $fields[] = $result->meta_key;
                }
            }
        }
        
        return $fields;
    }
}

if ( ! function_exists( 'wc_api_mps_woocommerce_duplicate_product_exclude_meta' ) ) {
    add_filter( 'woocommerce_duplicate_product_exclude_meta', 'wc_api_mps_woocommerce_duplicate_product_exclude_meta' );
    function wc_api_mps_woocommerce_duplicate_product_exclude_meta( $meta_to_exclude ) {
        
        $meta_to_exclude[] = 'mpsrel';
        
        return $meta_to_exclude;
    }
}

// stock update
if ( ! function_exists( 'wc_api_mps_stock_update' ) ) {
    add_action( 'woocommerce_order_status_processing', 'wc_api_mps_stock_update', 20, 1 );
    add_action( 'woocommerce_order_status_completed', 'wc_api_mps_stock_update', 20, 1 );
    add_action( 'woocommerce_order_status_cancelled', 'wc_api_mps_stock_update', 20, 1 );
    function wc_api_mps_stock_update( $order_id ) {
        
        $stock_sync = get_option( 'wc_api_mps_stock_sync' );
        if ( $stock_sync ) {
            $order = wc_get_order( $order_id );
            $items = $order->get_items();
            if ( $items != null ) {
                foreach ( $items as $item ) {
                    $data = $item->get_data();
                    $product_id = $data['product_id'];
                    $variation_id = $data['variation_id'];
                    wc_api_mps_product_stock_update( $product_id, $variation_id );
                }
            }
        }
    }
}

// product stock update 
if ( ! function_exists( 'wc_api_mps_product_stock_update' ) ) {
    function wc_api_mps_product_stock_update( $product_id, $variation_id ) {
        
        $wc_api_mps = get_post_meta( $product_id, 'mpsrel', true );
        if ( ! is_array( $wc_api_mps ) ) {
            $wc_api_mps = array();
        }
        
        $stock = get_post_meta( $product_id, '_stock', true );
        $stores = get_option( 'wc_api_mps_stores' );
        foreach ( $stores as $key => $value ) {
            $url = $key;
            $consumer_key = $value['consumer_key'];
            $consumer_secret = $value['consumer_secret'];
            if ( $value['status'] ) {
                $api = new WC_API_MPS( $url, $consumer_key, $consumer_secret );
                $destination_product_id = 0;
                if ( isset( $wc_api_mps[$url] ) ) {
                    $destination_product_id = $wc_api_mps[$url];
                    $product = $api->getProduct( $destination_product_id );
                    if ( ! isset( $product->id ) ) {
                        $destination_product_id = 0;
                    }
                }

                if ( $destination_product_id ) {
                    $stock_update_data = array(
                        'stock_quantity'    => $stock,
                    );
                    $api->updateProduct( $stock_update_data, $destination_product_id );

                    if ( $variation_id ) {
                        wc_api_mps_variation_product_stock_update( $api, $url, $variation_id, $destination_product_id );
                    }
                }
            }
        }
    }
}

// variation product stock update
if ( ! function_exists( 'wc_api_mps_variation_product_stock_update' ) ) {
    function wc_api_mps_variation_product_stock_update( $api, $url, $variation_id, $destination_product_id ) {
        
        $wc_api_mps = get_post_meta( $variation_id, 'mpsrel', true );
        if ( ! is_array( $wc_api_mps ) ) {
            $wc_api_mps = array();
        }
        
        $destination_variation_id = 0;
        if ( isset( $wc_api_mps[$url] ) ) {
            $destination_variation_id = $wc_api_mps[$url];
            $product_variation = $api->getProductVariation( $destination_product_id, $destination_variation_id );
            if ( ! isset( $product_variation->id ) ) {
                $destination_variation_id = 0;
            }
        }
        
        if ( $destination_variation_id ) {
            $stock = get_post_meta( $variation_id, '_stock', true );
            $stock_update_data = array(
                'stock_quantity'    => $stock,
            );
            
            $api->updateProductVariation( $stock_update_data, $destination_product_id, $destination_variation_id );
        }
    }
}