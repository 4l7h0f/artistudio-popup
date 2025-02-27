<?php
namespace ArtiStudio\Popup;

use ArtiStudio\Popup\Admin\CustomPostType;
use ArtiStudio\Popup\Frontend\PopupRenderer;

class Plugin {
    use Singleton;

    protected function __construct() {
        // Initialize admin and frontend functionality
        CustomPostType::get_instance();
        PopupRenderer::get_instance();

        // Register REST API endpoint
        add_action('rest_api_init', [$this, 'register_rest_endpoints']);
    }

    public function register_rest_endpoints() {
        register_rest_route('artistudio/v1', '/popup', [
            'methods' => 'GET',
            'callback' => [$this, 'get_popup_data'],
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ]);
    }

    public function get_popup_data() {
        // Fetch popup data from the database
        $popups = get_posts([
            'post_type' => 'artistudio_popup',
            'numberposts' => -1,
        ]);

        return array_map(function ($popup) {
            return [
                'title' => $popup->post_title,
                'description' => $popup->post_content,
                'page' => get_post_meta($popup->ID, '_artistudio_popup_page', true),
            ];
        }, $popups);
    }
}