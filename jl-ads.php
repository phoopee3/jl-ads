<?php
/*
Plugin Name: JL Ads
Plugin URI: https://jasonlawton.com/plugins/jl-ads
Description: JL Ads — a lightweight WordPress plugin to manage and display advertisements.
Version: 1.0.0
Author: Jason Lawton (vibe coded)
Author URI: https://jasonlawton.com
Text Domain: jl-ads
Domain Path: /languages
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register the Ad custom post type
 */
function jl_ads_register_post_type() {
    $labels = array(
        'name'                  => _x( 'Ads', 'Post type general name', 'jl-ads' ),
        'singular_name'         => _x( 'Ad', 'Post type singular name', 'jl-ads' ),
        'menu_name'             => _x( 'Ads', 'Admin Menu text', 'jl-ads' ),
        'name_admin_bar'        => _x( 'Ad', 'Add New on Toolbar', 'jl-ads' ),
        'add_new'               => __( 'Add New', 'jl-ads' ),
        'add_new_item'          => __( 'Add New Ad', 'jl-ads' ),
        'new_item'              => __( 'New Ad', 'jl-ads' ),
        'edit_item'             => __( 'Edit Ad', 'jl-ads' ),
        'view_item'             => __( 'View Ad', 'jl-ads' ),
        'all_items'             => __( 'All Ads', 'jl-ads' ),
        'search_items'          => __( 'Search Ads', 'jl-ads' ),
        'parent_item_colon'     => __( 'Parent Ads:', 'jl-ads' ),
        'not_found'             => __( 'No ads found.', 'jl-ads' ),
        'not_found_in_trash'    => __( 'No ads found in Trash.', 'jl-ads' ),
        'featured_image'        => _x( 'Ad Cover Image', 'Overrides the "Featured Image" phrase', 'jl-ads' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'jl-ads' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'jl-ads' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'jl-ads' ),
        'archives'              => _x( 'Ad archives', 'The post type archive label', 'jl-ads' ),
        'insert_into_item'      => _x( 'Insert into ad', 'Overrides the "Insert into post" phrase', 'jl-ads' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this ad', 'Overrides the "Uploaded to this post" phrase', 'jl-ads' ),
        'filter_items_list'     => _x( 'Filter ads list', 'Screen reader text', 'jl-ads' ),
        'items_list_navigation' => _x( 'Ads list navigation', 'Screen reader text', 'jl-ads' ),
        'items_list'            => _x( 'Ads list', 'Screen reader text', 'jl-ads' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'jl-ad' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-megaphone',
        'supports'           => array( 'title' ),
        'show_in_rest'       => false,
    );

    register_post_type( 'jl_ad', $args );
}
add_action( 'init', 'jl_ads_register_post_type' );

/**
 * Enqueue admin scripts and styles
 */
function jl_ads_admin_enqueue_scripts( $hook ) {
    global $post_type;
    
    if ( 'jl_ad' !== $post_type ) {
        return;
    }
    
    wp_enqueue_media();
    wp_enqueue_style( 'jl-ads-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), '1.0.0' );
    wp_enqueue_script( 'jl-ads-admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'jl_ads_admin_enqueue_scripts' );

/**
 * Enqueue frontend scripts
 */
function jl_ads_frontend_enqueue_scripts() {
    wp_enqueue_style( 'jl-ads-frontend', plugin_dir_url( __FILE__ ) . 'css/frontend.css', array(), '1.0.0' );
    wp_enqueue_script( 'jl-ads-frontend', plugin_dir_url( __FILE__ ) . 'js/frontend.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'jl_ads_frontend_enqueue_scripts' );

/**
 * Add meta boxes for Ad versions and scheduling
 */
function jl_ads_add_meta_boxes() {
    // Ad Versions Meta Box
    add_meta_box(
        'jl_ad_versions',
        __( 'Ad Versions', 'jl-ads' ),
        'jl_ads_versions_meta_box_callback',
        'jl_ad',
        'normal',
        'high'
    );
    
    // Ad Schedule Meta Box
    add_meta_box(
        'jl_ad_schedule',
        __( 'Ad Schedule', 'jl-ads' ),
        'jl_ads_schedule_meta_box_callback',
        'jl_ad',
        'side',
        'default'
    );
    
    // Shortcode Meta Box (only for published ads)
    add_meta_box(
        'jl_ad_shortcode',
        __( 'Shortcode', 'jl-ads' ),
        'jl_ads_shortcode_meta_box_callback',
        'jl_ad',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'jl_ads_add_meta_boxes' );

/**
 * Ad Versions meta box callback
 */
function jl_ads_versions_meta_box_callback( $post ) {
    wp_nonce_field( 'jl_ads_save_meta', 'jl_ads_meta_nonce' );
    
    $versions = array(
        'desktop' => __( 'Desktop', 'jl-ads' ),
        'tablet'  => __( 'Tablet', 'jl-ads' ),
        'mobile'  => __( 'Mobile', 'jl-ads' ),
    );
    
    foreach ( $versions as $key => $label ) {
        $image_id = get_post_meta( $post->ID, '_jl_ad_' . $key . '_image', true );
        $url      = get_post_meta( $post->ID, '_jl_ad_' . $key . '_url', true );
        $target   = get_post_meta( $post->ID, '_jl_ad_' . $key . '_target', true );
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
        ?>
        <div class="jl-ad-version" data-version="<?php echo esc_attr( $key ); ?>">
            <h3><?php echo esc_html( $label ); ?></h3>
            
            <div class="jl-ad-field">
                <label><?php _e( 'Ad Image', 'jl-ads' ); ?></label>
                <div class="jl-ad-image-preview">
                    <?php if ( $image_url ) : ?>
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="">
                    <?php endif; ?>
                </div>
                <input type="hidden" name="jl_ad_<?php echo esc_attr( $key ); ?>_image" value="<?php echo esc_attr( $image_id ); ?>" class="jl-ad-image-id">
                <button type="button" class="button jl-ad-upload-image"><?php _e( 'Select Image', 'jl-ads' ); ?></button>
                <button type="button" class="button jl-ad-remove-image" <?php echo ! $image_id ? 'style="display:none;"' : ''; ?>><?php _e( 'Remove Image', 'jl-ads' ); ?></button>
            </div>
            
            <div class="jl-ad-field">
                <label for="jl_ad_<?php echo esc_attr( $key ); ?>_url"><?php _e( 'Link URL', 'jl-ads' ); ?></label>
                <input type="url" id="jl_ad_<?php echo esc_attr( $key ); ?>_url" name="jl_ad_<?php echo esc_attr( $key ); ?>_url" value="<?php echo esc_url( $url ); ?>" class="widefat">
            </div>
            
            <div class="jl-ad-field">
                <label for="jl_ad_<?php echo esc_attr( $key ); ?>_target"><?php _e( 'Link Target', 'jl-ads' ); ?></label>
                <select id="jl_ad_<?php echo esc_attr( $key ); ?>_target" name="jl_ad_<?php echo esc_attr( $key ); ?>_target">
                    <option value="_self" <?php selected( $target, '_self' ); ?>><?php _e( 'Same Window', 'jl-ads' ); ?></option>
                    <option value="_blank" <?php selected( $target, '_blank' ); ?>><?php _e( 'New Window', 'jl-ads' ); ?></option>
                </select>
            </div>
        </div>
        <?php
    }
}

/**
 * Ad Schedule meta box callback
 */
function jl_ads_schedule_meta_box_callback( $post ) {
    $schedule_type = get_post_meta( $post->ID, '_jl_ad_schedule_type', true );
    $start_date    = get_post_meta( $post->ID, '_jl_ad_start_date', true );
    $end_date      = get_post_meta( $post->ID, '_jl_ad_end_date', true );
    
    if ( ! $schedule_type ) {
        $schedule_type = 'always';
    }
    ?>
    <div class="jl-ad-schedule">
        <div class="jl-ad-field">
            <label>
                <input type="radio" name="jl_ad_schedule_type" value="always" <?php checked( $schedule_type, 'always' ); ?>>
                <?php _e( 'Always Running', 'jl-ads' ); ?>
            </label>
        </div>
        
        <div class="jl-ad-field">
            <label>
                <input type="radio" name="jl_ad_schedule_type" value="scheduled" <?php checked( $schedule_type, 'scheduled' ); ?>>
                <?php _e( 'Scheduled', 'jl-ads' ); ?>
            </label>
        </div>
        
        <div class="jl-ad-date-fields" <?php echo $schedule_type !== 'scheduled' ? 'style="display:none;"' : ''; ?>>
            <div class="jl-ad-field">
                <label for="jl_ad_start_date"><?php _e( 'Start Date', 'jl-ads' ); ?></label>
                <input type="date" id="jl_ad_start_date" name="jl_ad_start_date" value="<?php echo esc_attr( $start_date ); ?>">
            </div>
            
            <div class="jl-ad-field">
                <label for="jl_ad_end_date"><?php _e( 'End Date', 'jl-ads' ); ?></label>
                <input type="date" id="jl_ad_end_date" name="jl_ad_end_date" value="<?php echo esc_attr( $end_date ); ?>">
            </div>
        </div>
    </div>
    <?php
}

/**
 * Shortcode meta box callback
 */
function jl_ads_shortcode_meta_box_callback( $post ) {
    if ( $post->post_status !== 'publish' ) {
        echo '<p>' . __( 'Publish this ad to get the shortcode.', 'jl-ads' ) . '</p>';
        return;
    }
    
    $shortcode = '[jl-ad id="' . $post->ID . '"]';
    ?>
    <div class="jl-ad-shortcode-box">
        <input type="text" value="<?php echo esc_attr( $shortcode ); ?>" readonly class="widefat jl-ad-shortcode-input">
        <button type="button" class="button jl-ad-copy-shortcode" data-shortcode="<?php echo esc_attr( $shortcode ); ?>">
            <?php _e( 'Copy Shortcode', 'jl-ads' ); ?>
        </button>
        <span class="jl-ad-copy-success" style="display:none;"><?php _e( 'Copied!', 'jl-ads' ); ?></span>
    </div>
    <?php
}

/**
 * Save meta box data
 */
function jl_ads_save_meta( $post_id ) {
    // Check nonce
    if ( ! isset( $_POST['jl_ads_meta_nonce'] ) || ! wp_verify_nonce( $_POST['jl_ads_meta_nonce'], 'jl_ads_save_meta' ) ) {
        return;
    }
    
    // Check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    // Check permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    // Save ad versions
    $versions = array( 'desktop', 'tablet', 'mobile' );
    
    foreach ( $versions as $version ) {
        // Image
        if ( isset( $_POST[ 'jl_ad_' . $version . '_image' ] ) ) {
            update_post_meta( $post_id, '_jl_ad_' . $version . '_image', absint( $_POST[ 'jl_ad_' . $version . '_image' ] ) );
        }
        
        // URL
        if ( isset( $_POST[ 'jl_ad_' . $version . '_url' ] ) ) {
            update_post_meta( $post_id, '_jl_ad_' . $version . '_url', esc_url_raw( $_POST[ 'jl_ad_' . $version . '_url' ] ) );
        }
        
        // Target
        if ( isset( $_POST[ 'jl_ad_' . $version . '_target' ] ) ) {
            $target = in_array( $_POST[ 'jl_ad_' . $version . '_target' ], array( '_self', '_blank' ) ) ? $_POST[ 'jl_ad_' . $version . '_target' ] : '_self';
            update_post_meta( $post_id, '_jl_ad_' . $version . '_target', $target );
        }
    }
    
    // Save schedule
    if ( isset( $_POST['jl_ad_schedule_type'] ) ) {
        $schedule_type = in_array( $_POST['jl_ad_schedule_type'], array( 'always', 'scheduled' ) ) ? $_POST['jl_ad_schedule_type'] : 'always';
        update_post_meta( $post_id, '_jl_ad_schedule_type', $schedule_type );
    }
    
    if ( isset( $_POST['jl_ad_start_date'] ) ) {
        update_post_meta( $post_id, '_jl_ad_start_date', sanitize_text_field( $_POST['jl_ad_start_date'] ) );
    }
    
    if ( isset( $_POST['jl_ad_end_date'] ) ) {
        update_post_meta( $post_id, '_jl_ad_end_date', sanitize_text_field( $_POST['jl_ad_end_date'] ) );
    }
}
add_action( 'save_post_jl_ad', 'jl_ads_save_meta' );

/**
 * Check if ad should be displayed based on schedule
 */
function jl_ads_should_display( $post_id ) {
    $schedule_type = get_post_meta( $post_id, '_jl_ad_schedule_type', true );
    
    if ( $schedule_type !== 'scheduled' ) {
        return true;
    }
    
    $start_date = get_post_meta( $post_id, '_jl_ad_start_date', true );
    $end_date   = get_post_meta( $post_id, '_jl_ad_end_date', true );
    $today      = current_time( 'Y-m-d' );
    
    // Check start date
    if ( $start_date && $today < $start_date ) {
        return false;
    }
    
    // Check end date
    if ( $end_date && $today > $end_date ) {
        return false;
    }
    
    return true;
}

/**
 * Shortcode callback
 */
function jl_ads_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'id' => 0,
    ), $atts, 'jl-ad' );
    
    $post_id = absint( $atts['id'] );
    
    if ( ! $post_id ) {
        return '';
    }
    
    // Check if post exists and is published
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'jl_ad' || $post->post_status !== 'publish' ) {
        return '';
    }
    
    // Check if ad should be displayed
    if ( ! jl_ads_should_display( $post_id ) ) {
        return '';
    }
    
    $versions = array( 'desktop', 'tablet', 'mobile' );
    $output = '<div class="jl-ad-container" data-ad-id="' . esc_attr( $post_id ) . '">';
    
    foreach ( $versions as $version ) {
        $image_id = get_post_meta( $post_id, '_jl_ad_' . $version . '_image', true );
        $url      = get_post_meta( $post_id, '_jl_ad_' . $version . '_url', true );
        $target   = get_post_meta( $post_id, '_jl_ad_' . $version . '_target', true );
        
        if ( ! $image_id ) {
            continue;
        }
        
        $image_url = wp_get_attachment_image_url( $image_id, 'full' );
        $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
        
        if ( ! $image_url ) {
            continue;
        }
        
        $output .= '<div class="jl-ad-version jl-ad-' . esc_attr( $version ) . '">';
        
        if ( $url ) {
            $output .= '<a href="' . esc_url( $url ) . '" target="' . esc_attr( $target ?: '_self' ) . '">';
        }
        
        $output .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '">';
        
        if ( $url ) {
            $output .= '</a>';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode( 'jl-ad', 'jl_ads_shortcode' );

/**
 * Add custom columns to the Ad list table
 */
function jl_ads_add_columns( $columns ) {
    $new_columns = array();
    
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        
        // Add shortcode, status, and schedule columns after title
        if ( $key === 'title' ) {
            $new_columns['jl_ad_shortcode'] = __( 'Shortcode', 'jl-ads' );
            $new_columns['jl_ad_status'] = __( 'Status', 'jl-ads' );
            $new_columns['jl_ad_schedule'] = __( 'Schedule', 'jl-ads' );
        }
    }
    
    return $new_columns;
}
add_filter( 'manage_jl_ad_posts_columns', 'jl_ads_add_columns' );

/**
 * Populate custom columns in the Ad list table
 */
function jl_ads_custom_column_content( $column, $post_id ) {
    if ( $column === 'jl_ad_shortcode' ) {
        $shortcode = '[jl-ad id="' . $post_id . '"]';
        echo '<div class="jl-ad-shortcode-column">';
        echo '<code class="jl-ad-shortcode-text">' . esc_html( $shortcode ) . '</code>';
        echo '<button type="button" class="jl-ad-copy-btn" data-shortcode="' . esc_attr( $shortcode ) . '" title="' . esc_attr__( 'Copy to clipboard', 'jl-ads' ) . '">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
        echo '</button>';
        echo '</div>';
    }
    
    if ( $column === 'jl_ad_status' ) {
        $is_active = jl_ads_should_display( $post_id );
        
        if ( $is_active ) {
            echo '<span class="jl-ad-status-indicator jl-ad-active" title="' . esc_attr__( 'Active', 'jl-ads' ) . '">';
            echo '<span class="jl-ad-circle jl-ad-circle-green"></span>';
            echo '<span class="jl-ad-status-label">' . __( 'Active', 'jl-ads' ) . '</span>';
            echo '</span>';
        } else {
            echo '<span class="jl-ad-status-indicator jl-ad-inactive" title="' . esc_attr__( 'Inactive', 'jl-ads' ) . '">';
            echo '<span class="jl-ad-circle jl-ad-circle-red"></span>';
            echo '<span class="jl-ad-status-label">' . __( 'Inactive', 'jl-ads' ) . '</span>';
            echo '</span>';
        }
    }
    
    if ( $column === 'jl_ad_schedule' ) {
        $schedule_type = get_post_meta( $post_id, '_jl_ad_schedule_type', true );
        
        if ( $schedule_type !== 'scheduled' ) {
            echo '<span class="jl-ad-status jl-ad-always">' . __( 'Always Running', 'jl-ads' ) . '</span>';
        } else {
            $start_date = get_post_meta( $post_id, '_jl_ad_start_date', true );
            $end_date   = get_post_meta( $post_id, '_jl_ad_end_date', true );
            
            $start_display = $start_date ? date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ) : __( 'No start date', 'jl-ads' );
            $end_display   = $end_date ? date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ) : __( 'No end date', 'jl-ads' );
            
            echo '<span class="jl-ad-status jl-ad-scheduled">';
            echo '<strong>' . __( 'Start:', 'jl-ads' ) . '</strong> ' . esc_html( $start_display ) . '<br>';
            echo '<strong>' . __( 'End:', 'jl-ads' ) . '</strong> ' . esc_html( $end_display );
            echo '</span>';
        }
    }
}
add_action( 'manage_jl_ad_posts_custom_column', 'jl_ads_custom_column_content', 10, 2 );