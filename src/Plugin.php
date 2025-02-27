<?php
namespace ArtiStudio\Popup;

use ArtiStudio\Popup\Admin\PopupPostType;
use ArtiStudio\Popup\Frontend\PopupRenderer;

final class Plugin
{
    use Traits\Singleton;

    protected function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        // Register custom post type
        PopupPostType::register();

        // Initialize frontend popup renderer
        PopupRenderer::init();

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Register REST API endpoint
        add_action('rest_api_init', [$this, 'register_rest_endpoints']);
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('artistudio-popup', plugins_url('assets/css/app.css', __FILE__));
        wp_enqueue_script('artistudio-popup', plugins_url('assets/js/app.js', __FILE__), [], null, true);

        // Localize script for REST API nonce
        wp_localize_script('artistudio-popup', 'wpApiSettings', [
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public function register_rest_endpoints()
    {
        register_rest_route('artistudio/v1', '/popup', [
            'methods' => 'GET',
            'callback' => [$this, 'get_popup_data'],
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ]);
    }

    public function get_popup_data()
    {
        // Fetch pop-up data from the database
        $popups = get_posts([
            'post_type' => 'artistudio_popup',
            'numberposts' => -1,
        ]);

        return array_map(function ($popup) {
            return [
                'id' => $popup->ID,
                'title' => $popup->post_title,
                'description' => $popup->post_content,
                'page' => get_post_meta($popup->ID, '_artistudio_popup_page', true),
            ];
        }, $popups);
    }
}