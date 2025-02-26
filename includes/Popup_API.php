<?php
namespace Artistudio\Popup;

class Popup_API {
    use Trait_Singleton;

    private function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('artistudio/v1', '/popup', [
            'methods' => 'GET',
            'callback' => [$this, 'get_popup_data'],
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ]);
    }

    public function get_popup_data() {
        $popups = get_posts([
            'post_type' => 'artistudio_popup',
            'posts_per_page' => -1,
        ]);
        return array_map(function($popup) {
            return [
                'id' => $popup->ID,
                'title' => $popup->post_title,
                'description' => get_post_meta($popup->ID, '_popup_description', true),
                'page' => get_post_meta($popup->ID, '_popup_page', true),
            ];
        }, $popups);
    }
}