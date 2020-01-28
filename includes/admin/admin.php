<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a function that add admin menu
 */
if ( ! function_exists( 'wc_api_mps_add_admin_menu' ) ) {
    add_action( 'admin_menu', 'wc_api_mps_add_admin_menu' );
    function wc_api_mps_add_admin_menu() {
        
        add_menu_page( 'WooCommerce API Product Sync', 'Product Sync', 'manage_options', 'wc_api_mps', 'wc_api_mps_callback', 'dashicons-update' );
        add_submenu_page( 'wc_api_mps', 'Product Sync - Stores', 'Stores', 'manage_options', 'wc_api_mps', 'wc_api_mps_callback' );
        add_submenu_page( 'wc_api_mps', 'Product Sync - Bulk Sync', 'Bulk Sync', 'manage_options', 'wc_api_mps_bulk_sync', 'wc_api_mps_bulk_sync_callback' );
        add_submenu_page( 'wc_api_mps', 'Product Sync - Settings', 'Settings', 'manage_options', 'wc_api_mps_settings', 'wc_api_mps_settings_callback' );
        add_submenu_page( 'wc_api_mps', 'Product Sync - Licence Verification', 'Licence Verification', 'manage_options', 'wc_api_mps_licence_verification', 'wc_api_mps_licence_verification_callback' );
    }
}

/*
 * This is a function that add and list stores.
 */
if ( ! function_exists( 'wc_api_mps_callback' ) ) {
    function wc_api_mps_callback() {        
        
        $page_url = menu_page_url( 'wc_api_mps', 0 );
        
        if ( isset( $_REQUEST['submit'] ) ) {
            $stores = get_option( 'wc_api_mps_stores' );
            if ( ! is_array( $stores ) ) {
                $stores = array();
            }
            
            $stores[$_REQUEST['url']] = array(
                'consumer_key'                  => $_REQUEST['consumer_key'],
                'consumer_secret'               => $_REQUEST['consumer_secret'],
                'status'                        => 1,
                'exclude_categories_products'   => array(),
                'exclude_meta_data'             => '',
                'price_adjustment'              => 0,
                'price_adjustment_type'         => '',
                'price_adjustment_operation'    => '',
                'price_adjustment_amount'       => '',
            );
            
            $api = new WC_API_MPS( $_REQUEST['url'], $_REQUEST['consumer_key'], $_REQUEST['consumer_secret'] );            
            $authentication = $api->authentication();
            if ( isset( $authentication->code ) ) {
                ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e( 'Authentication failure.' ); ?></p>
                    </div>
                <?php
            } else {
                update_option( 'wc_api_mps_stores', $stores );
                ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e( 'Store added successfully.' ); ?></p>
                    </div>
                <?php
            }
        } else if ( isset( $_REQUEST['update'] ) ) {
            if ( ! isset( $_REQUEST['exclude_categories_products'] ) ) {
                $_REQUEST['exclude_categories_products'] = array();
            }
            
            $stores = get_option( 'wc_api_mps_stores' );
            $stores[$_REQUEST['url']] = array(
                'consumer_key'                  => $_REQUEST['consumer_key'],
                'consumer_secret'               => $_REQUEST['consumer_secret'],
                'status'                        => $_REQUEST['status'],
                'exclude_categories_products'   => $_REQUEST['exclude_categories_products'],
                'exclude_meta_data'             => $_REQUEST['exclude_meta_data'],
                'price_adjustment'              => $_REQUEST['price_adjustment'],
                'price_adjustment_type'         => $_REQUEST['price_adjustment_type'],
                'price_adjustment_operation'    => $_REQUEST['price_adjustment_operation'],
                'price_adjustment_amount'       => $_REQUEST['price_adjustment_amount'],
            );
            
            $api = new WC_API_MPS( $_REQUEST['url'], $_REQUEST['consumer_key'], $_REQUEST['consumer_secret'] );            
            $authentication = $api->authentication();
            if ( isset( $authentication->code ) ) {
                ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e( 'Authentication failure.' ); ?></p>
                    </div>
                <?php
            } else {
                update_option( 'wc_api_mps_stores', $stores );
                ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e( 'Store updated successfully.' ); ?></p>
                    </div>
                <?php
            }
        } else if ( isset( $_REQUEST['delete'] ) ) {
            $stores = get_option( 'wc_api_mps_stores' );
            unset( $stores[$_REQUEST['delete']] );
            update_option( 'wc_api_mps_stores', $stores );
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e( 'Store removed successfully.' ); ?></p>
                </div>
            <?php
        } else {
            // nothing
        }
        ?>
            <div class="wrap">
                <h1><?php _e( 'Stores' ); ?></h1>
                <hr>
                <?php
                    $licence = get_site_option( 'wc_api_mps_licence' );
                    if ( $licence ) {
                        if ( isset( $_REQUEST['edit'] ) ) {
                            $stores = get_option( 'wc_api_mps_stores' );
                            $store = $stores[$_REQUEST['edit']];
                            ?>
                                <h2><?php _e( 'Edit store:' ); ?> <?php echo $_REQUEST['edit']; ?></h2>                           
                                <form method="post" action="<?php echo $page_url; ?>">
                                    <table class="form-table">
                                        <tbody>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Status' ); ?></label></th>
                                                <td>
                                                    <input type="hidden" name="status" value="0" />
                                                    <input type="checkbox" name="status" value="1"<?php echo ( $store['status'] ? ' checked="checked"' : '' ); ?> />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Consumer Key' ); ?> <span class="description">(required)</span></label></th>
                                                <td>
                                                    <input type="text" name="consumer_key" value="<?php echo $store['consumer_key']; ?>" class="regular-text code" required />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Consumer Secret' ); ?> <span class="description">(required)</span></label></th>
                                                <td>
                                                    <input type="text" name="consumer_secret" value="<?php echo $store['consumer_secret']; ?>" class="regular-text code" required />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Exclude categories products' ); ?></label></th>
                                                <td>
                                                    <?php
                                                        $product_cat_args = array(
                                                            'taxonomy'      => 'product_cat',
                                                            'hide_empty'    => false,
                                                        );
                                                        $categories = get_terms( $product_cat_args );
                                                        $exclude_categories_products = ( isset( $store['exclude_categories_products'] ) ? $store['exclude_categories_products'] : array() );
                                                        if ( $categories != null ) {
                                                            foreach ( $categories as $category ) {
                                                                $checked = '';
                                                                if ( in_array( $category->term_id, $exclude_categories_products ) ) {
                                                                    $checked = ' checked="checked"';
                                                                }
                                                                ?><label><input type="checkbox" name="exclude_categories_products[]" value="<?php echo $category->term_id; ?>"<?php echo $checked; ?> /> <?php echo $category->name; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<?php
                                                            }
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Exclude Meta Data' ); ?></label></th>
                                                <td>
                                                    <input type="text" name="exclude_meta_data" value="<?php echo $store['exclude_meta_data']; ?>" class="regular-text code" />
                                                    <p class="description"><?php _e( 'Exclude product meta key by comma separated.' ); ?></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Price Adjustment' ); ?></label></th>
                                                <td>
                                                    <input type="hidden" name="price_adjustment" value="0" />
                                                    <input type="checkbox" name="price_adjustment" value="1"<?php echo ( $store['price_adjustment'] ? ' checked="checked"' : '' ); ?> />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Price Adjustment Type' ); ?></label></th>
                                                <td>
                                                    <input type="hidden" name="price_adjustment_type" value="" />
                                                    <fieldset>
                                                        <label><input type="radio" name="price_adjustment_type" value="percentage"<?php echo ( $store['price_adjustment_type'] == 'percentage' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Percentage Amount' ); ?></label><br>
                                                        <label><input type="radio" name="price_adjustment_type" value="fixed"<?php echo ( $store['price_adjustment_type'] == 'fixed' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Fixed Amount' ); ?></label>
                                                    </fieldset>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Price Adjustment Amount' ); ?></label></th>
                                                <td>
                                                    <select name="price_adjustment_operation">
                                                    <?php
                                                        $operations = array(
                                                            'plus'  => '+',
                                                            'minus' => '-',
                                                        );

                                                        foreach ( $operations as $operation_key => $operation_label ) {
                                                            $selected = '';
                                                            if ( $store['price_adjustment_operation'] == $operation_key ) {
                                                                $selected = ' selected="selected"';
                                                            }
                                                            ?><option value="<?php echo $operation_key; ?>"<?php echo $selected; ?>><?php echo $operation_label; ?></option><?php
                                                        }
                                                    ?>
                                                    </select>
                                                    <input type="number" name="price_adjustment_amount" value="<?php echo $store['price_adjustment_amount']; ?>" step="any" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <p>
                                        <input type="hidden" name="url" value="<?php echo $_REQUEST['edit']; ?>" />
                                        <input type='submit' class='button-primary' name="update" value="<?php _e( 'Update store' ); ?>" />
                                    </p>
                                </form>
                            <?php
                        } else {
                            ?>
                                <h2><?php _e( 'Add store' ); ?></h2>                            
                                <form method="post" action="<?php echo $page_url; ?>">
                                    <table class="form-table">
                                        <tbody>                                            
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Store URL' ); ?> <span class="description">(required)</span></label></th>
                                                <td>
                                                    <input type="url" name="url" class="regular-text code" required />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Consumer Key' ); ?> <span class="description">(required)</span></label></th>
                                                <td>
                                                    <input type="text" name="consumer_key" class="regular-text code" required />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label><?php _e( 'Consumer Secret' ); ?> <span class="description">(required)</span></label></th>
                                                <td>
                                                    <input type="text" name="consumer_secret" class="regular-text code" required />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <p><input type='submit' class='button-primary' name="submit" value="<?php _e( 'Add store' ); ?>" /></p>
                                </form>
                                <br>
                                <h2><?php _e( 'Stores' ); ?></h2>
                                <table class="widefat striped">
                                    <thead>
                                        <tr>
                                            <th><?php _e( 'Store URL' ); ?></th>
                                            <th><?php _e( 'Status' ); ?></th>       
                                            <th><?php _e( 'Action' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th><?php _e( 'Store URL' ); ?></th>
                                            <th><?php _e( 'Status' ); ?></th>       
                                            <th><?php _e( 'Action' ); ?></th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php
                                            $stores = get_option( 'wc_api_mps_stores' );
                                            if ( $stores != null ) {
                                                foreach ( $stores as $store => $data ) {
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $store; ?></td>
                                                            <td>
                                                                <?php
                                                                    if ( $data['status'] ) {
                                                                        ?><span class="dashicons dashicons-yes"></span><?php
                                                                    } else {
                                                                        ?><span class="dashicons dashicons-no"></span><?php
                                                                    }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <a href="<?php echo $page_url; ?>&edit=<?php echo $store; ?>"><span class="dashicons dashicons-edit"></span></a>
                                                                <a href="<?php echo $page_url; ?>&delete=<?php echo $store; ?>"><span class="dashicons dashicons-trash"></span></a>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                }
                                            } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="3"><?php _e( 'No stores found.' ); ?></td>
                                                    </tr>
                                                <?php
                                            }
                                        ?>                        
                                    </tbody>
                                </table>
                            <?php
                        }
                    } else {
                        ?>
                            <div class="notice notice-error is-dismissible">
                                <p><?php _e( 'Please verify purchase code.' ); ?></p>
                            </div>
                        <?php
                    }
                ?>
            </div>
        <?php
    }
}

/*
 * This is a function that sync bulk products.
 */
if ( ! function_exists( 'wc_api_mps_bulk_sync_callback' ) ) {
    function wc_api_mps_bulk_sync_callback() {
        
        $record_per_page = 10;
        if ( isset( $_REQUEST['wc_api_mps_record_per_page'] ) ) {
            $record_per_page = (int) $_REQUEST['wc_api_mps_record_per_page'];
        }
        
        if ( isset( $_REQUEST['submit'] ) ) {
            $records = ( isset( $_REQUEST['records'] ) ? $_REQUEST['records'] : array() );
            if ( $records != null ) {
                $selected_stores = ( isset( $_REQUEST['stores'] ) ? $_REQUEST['stores'] : array() );
                $stores = get_option( 'wc_api_mps_stores' );
                $wc_api_mps_stores = array();
                foreach ( $selected_stores as $selected_store ) {
                    if ( isset( $stores[$selected_store] ) ) {
                        $wc_api_mps_stores[$selected_store] = $stores[$selected_store];
                    }
                }
                
                if ( $wc_api_mps_stores != null ) {
                    foreach ( $records as $record ) {
                        $product_id = $record;
                        wc_api_mps_integration( $product_id, $wc_api_mps_stores );
                    }
                }
            }
        }
        
        $page_url = admin_url( '/admin.php?page=wc_api_mps_bulk_sync' );
        $licence = get_site_option( 'wc_api_mps_licence' );
        ?>
            <div class="wrap">               
                <h1><?php _e( 'Bulk Sync' ); ?></h1>
                <hr>
                <?php
                    if ( $licence ) {
                        ?>
                        <form method="post" action="<?php echo menu_page_url( 'wc_zoho_integration', 0 ); ?>">                
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row"><?php _e( 'Product Per Page' ); ?></th>
                                        <td>
                                            <select name="wc_api_mps_record_per_page">
                                            <?php 
                                                $number_options = array( 5, 10, 25, 50 );
                                                foreach ( $number_options as $number_option ) {
                                                    $selected = '';
                                                    if ( $record_per_page == $number_option ) {
                                                        $selected = ' selected="$selected"';
                                                    }
                                                    ?><option value="<?php echo $number_option; ?>"<?php echo $selected; ?>><?php echo $number_option; ?></option><?php
                                                }
                                            ?>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="submit"><input name="filter" class="button button-secondary" value="<?php _e( 'Filter' ); ?>" type="submit"></p>
                        </form>                
                        <form method="post" action="<?php echo menu_page_url( 'wc_zoho_integration', 0 ); ?>">
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <td class="manage-column column-cb check-column"><input type="checkbox"></td>
                                        <th><?php _e( 'Product' ); ?></th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <td class="manage-column column-cb check-column"><input type="checkbox"></td>
                                        <th><?php _e( 'Product' ); ?></th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php 
                                        $paged = ( isset( $_REQUEST['paged'] ) ) ? $_REQUEST['paged'] : 1;
                                        $add_args = array(
                                            'wc_api_mps_record_per_page'  => $record_per_page,
                                        );

                                        $args = array(
                                            'posts_per_page'    => $record_per_page,                                    
                                            'paged'             => $paged,
                                            'post_type'         => 'product',
                                        );

                                        $records = new WP_Query( $args );
                                        if ( $records->have_posts() ) {
                                            $module = get_option( 'wc_api_mps_module' );
                                            while ( $records->have_posts() ) {
                                                $records->the_post();
                                                $record_id = get_the_ID();
                                                ?>
                                                    <tr>
                                                        <th class="check-column"><input type="checkbox" name="records[]" value="<?php echo $record_id; ?>"></th>
                                                        <td class="title column-title page-title">
                                                            <strong><a href="<?php echo get_edit_post_link( $record_id); ?>"><?php echo get_the_title(); ?></a></strong> 
                                                            <?php
                                                                $mpsrel = get_post_meta( $record_id, 'mpsrel', true );
                                                                if ( $mpsrel != null ) {
                                                                    ?><p><strong style="display: inline-block;"><?php _e( 'Synced:' ); ?></strong> <?php echo implode( ', ', array_keys( $mpsrel ) ); ?></p><?php
                                                                }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                                <tr class="no-items">                                       
                                                    <td class="colspanchange" colspan="2"><?php _e( 'No products found.' ); ?></td>
                                                </tr>
                                            <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <?php
                                if ( $records->max_num_pages ) {
                                    ?>
                                        <div class="wc_api_mps-pagination">
                                            <span class="pagination-links">
                                                <?php
                                                $big = 999999999;
                                                $total = $records->max_num_pages;
                                                $paginate_url = admin_url( '/admin.php?page=wc_api_mps_bulk_sync&paged=%#%' );
                                                echo paginate_links( array(
                                                    'base'      => str_replace( $big, '%#%', $paginate_url ),
                                                    'format'    => '?paged=%#%',
                                                    'current'   => max( 1, $paged ),
                                                    'total'     => $total,
                                                    'add_args'  => $add_args,    
                                                    'prev_text' => __( '&laquo;' ),
                                                    'next_text' => __( '&raquo;' ),
                                                ) );
                                                ?>
                                            </span>
                                        </div>
                                    <?php
                                }

                                wp_reset_postdata();
                            ?>
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row"><?php _e( 'Destination Sites' ); ?></th>
                                        <td>
                                            <label><input class="wc_api_mps-check-uncheck" type="checkbox" /><?php _e( 'All' ); ?></label>
                                            <p class="description"><?php _e( 'Select/Deselect all sites.' ); ?></p>
                                            <br>
                                            <fieldset class="wc_api_mps-sites">                                            
                                                <?php
                                                    $stores = get_option( 'wc_api_mps_stores' );
                                                    if ( $stores != null ) {
                                                        foreach ( $stores as $store_url => $store_data ) {
                                                            if ( $store_data['status'] ) {
                                                                ?><p><label><input type="checkbox" name="stores[]" value="<?php echo $store_url; ?>" /> <?php echo $store_url; ?></label><?php
                                                            }
                                                        }
                                                    }
                                                ?>                                                                         				
                                            </fieldset>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="submit">
                                <input type="hidden" name="wc_api_mps_record_per_page" value="<?php echo $record_per_page; ?>" />
                                <input name="submit" class="button button-primary" value="<?php _e( 'Sync' ); ?>" type="submit">
                            </p>
                        </form>
                        <style>
                            .wc_api_mps-pagination {
                                color: #555;
                                cursor: default;
                                float: right;
                                height: 28px;
                                margin-top: 3px;
                            }

                            .wc_api_mps-pagination .page-numbers {
                                background: #e5e5e5;
                                border: 1px solid #ddd;
                                display: inline-block;
                                font-size: 16px;
                                font-weight: 400;
                                line-height: 1;
                                min-width: 17px;
                                padding: 3px 5px 7px;
                                text-align: center;
                                text-decoration: none;
                            }

                            .wc_api_mps-pagination .page-numbers.current {
                                background: #f7f7f7;
                                border-color: #ddd;
                                color: #a0a5aa;
                                height: 16px;
                                margin: 6px 0 4px;
                            }

                            .wc_api_mps-pagination a.page-numbers:hover {
                                background: #00a0d2;
                                border-color: #5b9dd9;
                                box-shadow: none;
                                color: #fff;
                                outline: 0 none;
                            }

                            .wc_api_mps-search-box {
                                margin-bottom: 8px !important;
                            }

                            @media screen and (max-width:782px) {
                                .wc_api_mps-pagination {
                                    float: none;
                                    height: auto;
                                    text-align: center;
                                    margin-top: 7px;
                                }

                                .wc_api_mps-search-box {
                                    margin-bottom: 20px !important;
                                }
                            }
                        </style>
                        <script>
                            jQuery( document ).ready( function( $ ) {
                                $( '.wc_api_mps-check-uncheck' ).on( 'change', function() {
                                    var checked = $( this ).prop( 'checked' );
                                    $( '.wc_api_mps-sites input[type="checkbox"]' ).each( function() {
                                        if ( checked ) {
                                            $( this ).prop( 'checked', true );
                                        } else {
                                            $( this ).prop( 'checked', false );
                                        }
                                    });                   
                                });
                            });
                        </script>
                        <?php
                    } else {
                        ?>
                            <div class="notice notice-error is-dismissible">
                                <p><?php _e( 'Please verify purchase code.' ); ?></p>
                            </div>
                        <?php
                    }
                ?>
            </div>
        <?php
    }
}

/*
 * This is a function for plugin settings.
 */
if ( ! function_exists( 'wc_api_mps_settings_callback' ) ) {
    function wc_api_mps_settings_callback() {
        
        if ( isset( $_REQUEST['submit'] ) ) {
            $request = $_REQUEST;
            unset( $request['page'] );
            unset( $request['submit'] );
            if ( $request != null ) {
                foreach ( $request as $key => $value ) {
                    update_option( $key, $value );
                }
            }
            
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e( 'Settings saved.' ); ?></p>
                </div>
            <?php
        }
        
        $sync_type = get_option( 'wc_api_mps_sync_type' );
        $authorization = get_option( 'wc_api_mps_authorization' );
        if ( ! $authorization ) {
            $authorization = 'header';
        }
        
        $old_products_sync_by = get_option( 'wc_api_mps_old_products_sync_by' );
        if ( ! $old_products_sync_by ) {
            $old_products_sync_by = 'slug';
        }
        
        $stock_sync = get_option( 'wc_api_mps_stock_sync' );
        $licence = get_site_option( 'wc_api_mps_licence' );
        ?>
            <div class="wrap">     
                <h1><?php _e( 'Settings' ); ?></h1>
                <hr>
                <?php
                    if ( $licence ) {
                        ?>
                        <form method="post">                
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row"><?php _e( 'Sync Type' ); ?></th>
                                        <td>
                                            <fieldset>
                                                <label><input type="radio" name="wc_api_mps_sync_type" value="auto"<?php echo ( $sync_type == 'auto' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Auto Sync' ); ?></label><br>
                                                <label><input type="radio" name="wc_api_mps_sync_type" value="manual"<?php echo ( $sync_type == 'manual' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Manual Sync' ); ?></label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e( 'Authorization' ); ?></th>
                                        <td>
                                            <fieldset>
                                                <label><input type="radio" name="wc_api_mps_authorization" value="header"<?php echo ( $authorization == 'header' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Header' ); ?></label><br>
                                                <label><input type="radio" name="wc_api_mps_authorization" value="query"<?php echo ( $authorization == 'query' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Query String Parameters' ); ?></label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e( 'Old Products Sync By' ); ?></th>
                                        <td>
                                            <fieldset>
                                                <label><input type="radio" name="wc_api_mps_old_products_sync_by" value="slug"<?php echo ( $old_products_sync_by == 'slug' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Slug' ); ?></label><br>
                                                <label><input type="radio" name="wc_api_mps_old_products_sync_by" value="sku"<?php echo ( $old_products_sync_by == 'sku' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'SKU' ); ?></label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e( 'Stock Sync?' ); ?></th>
                                        <td>
                                            <input type="hidden" name="wc_api_mps_stock_sync" value="0" />
                                            <input type="checkbox" name="wc_api_mps_stock_sync" value="1"<?php echo ( $stock_sync ? ' checked="checked"' : '' ); ?> />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="submit">
                                <input type="submit" name="submit" class="button button-primary" value="<?php _e( 'Save Changes' ); ?>">
                            </p>
                        </form>
                        <?php
                    } else {
                        ?>
                            <div class="notice notice-error is-dismissible">
                                <p><?php _e( 'Please verify purchase code.' ); ?></p>
                            </div>
                        <?php
                    }
                ?>
                
            </div>
        <?php
    }
}

/*
 * This is a function for licence verification.
 */
if ( ! function_exists( 'wc_api_mps_licence_verification_callback' ) ) {
    function wc_api_mps_licence_verification_callback() {
        
        if ( isset( $_REQUEST['verify'] ) ) {
            if ( isset( $_REQUEST['wc_api_mps_purchase_code'] ) ) {
                update_site_option( 'wc_api_mps_purchase_code', $_REQUEST['wc_api_mps_purchase_code'] );
                
                $data = array(
                    'sku'           => '21672540',
                    'purchase_code' => $_REQUEST['wc_api_mps_purchase_code'],
                    'domain'        => site_url(),
                    'status'        => 'verify',
                    'type'          => 'oi',
                );

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://www.obtaininfotech.com/extension/' );
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                $json_response = curl_exec( $ch );
                curl_close ($ch);
                
                $response = json_decode( $json_response );
                if ( isset( $response->success ) ) {
                    if ( $response->success ) {
                        update_site_option( 'wc_api_mps_licence', 1 );
                    }
                }
            }
        } else if ( isset( $_REQUEST['unverify'] ) ) {
            if ( isset( $_REQUEST['wc_api_mps_purchase_code'] ) ) {
                $data = array(
                    'sku'           => '21672540',
                    'purchase_code' => $_REQUEST['wc_api_mps_purchase_code'],
                    'domain'        => site_url(),
                    'status'        => 'unverify',
                    'type'          => 'oi',
                );

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://www.obtaininfotech.com/extension/' );
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
                $json_response = curl_exec( $ch );
                curl_close ($ch);

                $response = json_decode( $json_response );
                if ( isset( $response->success ) ) {
                    if ( $response->success ) {
                        update_site_option( 'wc_api_mps_purchase_code', '' );
                        update_site_option( 'wc_api_mps_licence', 0 );
                    }
                }
            }
        }    
        
        $wc_api_mps_purchase_code = get_site_option( 'wc_api_mps_purchase_code' );
        ?>
            <div class="wrap">      
                <h2><?php _e( 'Licence Verification' ); ?></h2>
                <hr>
                <?php
                    if ( isset( $response->success ) ) {
                        if ( $response->success ) {                            
                             ?>
                                <div class="notice notice-success is-dismissible">
                                    <p><?php echo $response->message; ?></p>
                                </div>
                            <?php
                        } else {
                            update_site_option( 'wc_api_mps_licence', 0 );
                            ?>
                                <div class="notice notice-error is-dismissible">
                                    <p><?php echo $response->message; ?></p>
                                </div>
                            <?php
                        }
                    }
                ?>
                <form method="post">
                    <table class="form-table">                    
                        <tbody>
                            <tr>
                                <th scope="row"><?php _e( 'Purchase Code' ); ?></th>
                                <td>
                                    <input name="wc_api_mps_purchase_code" type="text" class="regular-text" value="<?php echo $wc_api_mps_purchase_code; ?>" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p>
                        <input type='submit' class='button-primary' name="verify" value="<?php _e( 'Verify' ); ?>" />
                        <input type='submit' class='button-primary' name="unverify" value="<?php _e( 'Unverify' ); ?>" />
                    </p>
                </form>   
            </div>
        <?php
    }
}