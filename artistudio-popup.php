<?php
/**
 * Plugin Name:     Artistudio Popup
 * Plugin URI:      https://althof.online
 * Description:     A WordPress Plugin using Vue
 * Author:          Muhamad Arief Rachman
 * Author URI:      https://althof.online
 * Text Domain:     artistudio-popup
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Artistudio_Popup
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use ArtiStudio\Popup\Plugin;

Plugin::getInstance();

// Add admin menu
add_action('admin_menu', function () {
    // Main menu item
    add_menu_page(
        'ArtiStudio Popup', 
        'ArtiStudio Popup',
        'manage_options',
        'artistudio-popup',
        function () {
            echo '<div id="artistudio-popup-admin"></div>';
        },
        'dashicons-admin-site',
        6 
    );

    // Submenu items
    add_submenu_page(
        'artistudio-popup',
        'Info',
        'Info',
        'manage_options',
        'artistudio-popup#/',
        function () {
            echo '<div id="artistudio-popup-admin"></div>';
        }
    );

    add_submenu_page(
        'artistudio-popup',
        'All Popup',
        'All Popup',
        'manage_options',
        'artistudio-popup#/list',
        function () {
            echo '<div id="artistudio-popup-admin"></div>';
        }
    );

    add_submenu_page(
        'artistudio-popup',
        'Add New Popup',
        'Add New Popup',
        'manage_options',
        'artistudio-popup#/create',
        function () {
            echo '<div id="artistudio-popup-admin"></div>';
        }
    );


});

add_action('admin_menu', function () {
    remove_submenu_page('artistudio-popup', 'artistudio-popup');
}, 20);

add_action('wp_ajax_load_editor_scripts', function () {
    // Load all required WordPress scripts
    wp_enqueue_media();
    wp_enqueue_editor();

    // Manually print the scripts
    wp_print_scripts([
        'jquery',
        'media-upload',
        'thickbox',
        'wp-tinymce',
        'editor'
    ]);

    wp_print_styles([
        'thickbox'
    ]);

    // End the request
    wp_die();
});

// Enqueue admin assets
add_action('admin_enqueue_scripts', function ($hook) {
    // Only load on our plugin pages
    if (strpos($hook, 'artistudio-popup') !== false) {
        // Enqueue all necessary WordPress media scripts
        wp_enqueue_media();
        wp_enqueue_editor();
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        // Enqueue your admin script
        wp_enqueue_script(
            'artistudio-popup-admin',
            plugin_dir_url(__FILE__) . 'public/js/admin.js',
            ['jquery', 'wp-editor', 'media-upload', 'thickbox'],
            '1.0',
            true
        );

        wp_enqueue_style(
            'artistudio-popup-admin-style',
            plugin_dir_url(__FILE__) . 'public/css/admin.css',
            [],
            '1.0'
        );

        // Localize script with proper admin URL
        wp_localize_script('artistudio-popup-admin', 'artistudioPopupAdmin', [
            'rest_url' => rest_url('artistudio/v1/'),
            'admin_url' => admin_url(),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
            'per_page' => get_user_meta(get_current_user_id(), 'popups_per_page', true) ?: 10,
        ]);
    }
});

// Enqueue frontend assets
add_action('wp_enqueue_scripts', function () {
    // Enqueue Vue.js (if not already included)
    wp_enqueue_script(
        'vue',
        'https://unpkg.com/vue@3.2.31/dist/vue.global.js',
        [],
        '3.2.31',
        true
    );

    // Enqueue frontend script
    wp_enqueue_script(
        'artistudio-popup-frontend',
        plugin_dir_url(__FILE__) . 'public/js/app.js',
        ['vue'],
        '1.0',
        true
    );

    // Enqueue frontend styles
    wp_enqueue_style(
        'artistudio-popup-frontend-style',
        plugin_dir_url(__FILE__) . 'public/css/app.css',
        [],
        '1.0'
    );
});

// Pass current page ID to frontend
add_action('wp_enqueue_scripts', function () {
    global $post;
    wp_localize_script('artistudio-popup-frontend', 'artistudioPopupFrontend', [
        'rest_url' => rest_url('artistudio/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
        'current_page_id' => $post->ID ?? null,
    ]);
});

// Automatically add popup container to the footer
add_action('wp_footer', function () {
    echo '<div id="artistudio-popup-frontend"></div>';
});

// Page screen options
add_action('load-toplevel_page_artistudio-popup', 'add_popup_screen_options');
function add_popup_screen_options()
{
    $option = 'per_page';
    $args = [
        'label' => 'Popups per page',
        'default' => 10,
        'option' => 'popups_per_page'
    ];
    add_screen_option($option, $args);
}
add_filter('set-screen-option', 'set_popup_screen_option', 10, 3);

function set_popup_screen_option($status, $option, $value)
{
    if ('popups_per_page' === $option) {
        return $value;
    }
    return $status;
}

// sanitize the popup content
add_action('rest_api_init', function () {
    register_rest_route('artistudio/v1', '/sanitize-content', [
        'methods' => 'POST',
        'callback' => 'sanitize_popup_content',
        'permission_callback' => '__return_true'
    ]);
});

function sanitize_popup_content(WP_REST_Request $request)
{
    $content = $request->get_param('content');
    if (empty($content)) {
        return new WP_Error('no_content', 'No content provided', ['status' => 400]);
    }

    // Use WordPress's built-in KSES for sanitization
    $allowed_html = wp_kses_allowed_html('post');
    $sanitized_content = wp_kses_post($content);

    // Additional WordPress content processing
    $sanitized_content = wptexturize($sanitized_content);
    $sanitized_content = convert_smilies($sanitized_content);
    $sanitized_content = wpautop($sanitized_content);
    $sanitized_content = shortcode_unautop($sanitized_content);
    $sanitized_content = do_shortcode($sanitized_content);

    return [
        'sanitized' => $sanitized_content,
        'original_length' => strlen($content),
        'sanitized_length' => strlen($sanitized_content)
    ];
}

// Add a new endpoint for sanitizing popup data
add_action('rest_api_init', function() {
    register_rest_route('artistudio/v1', '/sanitize-popups', [
        'methods' => 'POST',
        'callback' => 'sanitize_popups_data',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
});

function sanitize_popups_data(WP_REST_Request $request) {
    $popups = $request->get_param('popups');
    if (empty($popups) || !is_array($popups)) {
        return new WP_Error('invalid_data', 'Invalid popups data', ['status' => 400]);
    }

    $sanitized_popups = [];
    foreach ($popups as $popup) {
        $sanitized = [
            'id' => absint($popup['id']),
            'title' => sanitize_text_field($popup['title']),
            'description' => wp_kses_post($popup['description']),
            'page' => absint($popup['page']),
            'status' => sanitize_key($popup['status']),
            'date' => sanitize_text_field($popup['date']),
            'formatted_date' => sanitize_text_field($popup['formatted_date'])
        ];
        $sanitized_popups[] = $sanitized;
    }

    return $sanitized_popups;
}