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
            // 'permission_callback' => function() {
            //     return is_user_logged_in();
            // }
            'permission_callback' => '__return_true', // Allow access to all users

        ]);
    }

    public function get_popup_data() {
        $popups = get_posts([
            'post_type' => 'artistudio_popup',
            'posts_per_page' => -1,
        ]);
        return array_map(function($popup) {
            $page_slug = get_post_meta($popup->ID, '_popup_page', true);
            $page = get_page_by_path($page_slug);
    
            return [
                'id' => $popup->ID,
                'title' => $popup->post_title,
                'description' => get_post_meta($popup->ID, '_popup_description', true),
                'page' => [
                    'slug' => $page_slug,
                    'title' => $page ? $page->post_title : '',
                    'url' => $page ? get_permalink($page->ID) : '',
                ],
            ];
        }, $popups);
    }
}