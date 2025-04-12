<?php

namespace ArtiStudio\Popup;

use ArtiStudio\Popup\Traits\Singleton;
use WP_Error;
use WP_Query;

class Plugin
{
    use Singleton;

    protected function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        // Register REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_endpoints']);
    }

    public function register_popup_post_type()
    {
        register_post_type('artistudio_popup', [
            'labels' => [
                'name' => 'Popups',
                'singular_name' => 'Popup',
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'editor'],
            'show_in_rest' => true,
        ]);

        // Add custom fields for description and page
        register_meta('post', 'popup_description', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);

        register_meta('post', 'popup_page', [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ]);
    }

    public function register_rest_endpoints()
    {
        // Fetch popups with status and pagination
        register_rest_route('artistudio/v1', '/popup', [
            'methods' => 'GET',
            'callback' => function ($request) {
                $args = [
                    'post_type' => 'artistudio_popup',
                    'posts_per_page' => $request->get_param('per_page') ?: 10,
                    'paged' => $request->get_param('page') ?: 1,
                    'orderby' => 'date',
                    'order' => 'DESC',
                ];

                // Add status filter if provided
                $status = $request->get_param('status');
                if ($status) {
                    $args['post_status'] = $status;
                } else {
                    $args['post_status'] = 'any';
                }

                // Month filter
                if ($request->get_param('month')) {
                    $month = $request->get_param('month');
                    $args['date_query'] = [
                        [
                            'year' => substr($month, 0, 4),
                            'month' => substr($month, 5, 2),
                        ],
                    ];
                }

                // Search filter
                if ($request->get_param('search')) {
                    $args['s'] = $request->get_param('search');
                }

                $query = new WP_Query($args);
                $popups = [];

                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $date_format = get_option('date_format');
                        $time_format = get_option('time_format');
                        $formatted_date = wp_date($date_format, get_the_date('U')) . ' at ' . wp_date($time_format, get_the_date('U'));

                        $popups[] = [
                            'id' => get_the_ID(),
                            'title' => get_the_title(),
                            'description' => get_the_content(),
                            'page' => get_post_meta(get_the_ID(), 'popup_page', true),
                            'status' => get_post_status(),
                            'date' => get_the_date('Y-m-d H:i:s'),
                            'formatted_date' => $formatted_date,
                        ];
                    }
                }

                // If ?simple=true, return just the array (for frontend)
                if ($request->get_param('simple') === 'true') {
                    return $popups;
                }

                // Return the total number of posts for pagination
                return [
                    'data' => $popups,
                    'total' => $query->found_posts,
                    'per_page' => $args['posts_per_page'],
                    'current_page' => $args['paged'],
                ];
            },
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);

        // Fetch a single popup post
        register_rest_route('artistudio/v1', '/popup/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_popup_post'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);

        // Create or update a popup post
        register_rest_route('artistudio/v1', '/popup', [
            'methods' => 'POST',
            'callback' => [$this, 'create_or_update_popup'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);

        // Update a popup post
        register_rest_route('artistudio/v1', '/popup/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => function ($request) {
                $params = $request->get_params();
                $post_id = $params['id'];

                // Update the post
                $updated = wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $params['title'],
                    'post_content' => $params['description'],
                    'post_status' => $params['status'],
                ]);

                if ($updated) {
                    // Update custom fields
                    update_post_meta($post_id, 'popup_description', $params['description']);
                    update_post_meta($post_id, 'popup_page', $params['page']);
                    return ['success' => true, 'id' => $post_id];
                } else {
                    return new WP_Error('update_failed', 'Failed to update popup', ['status' => 500]);
                }
            },
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);

        // Restore a trashed popup post
        register_rest_route('artistudio/v1', '/popup/(?P<id>\d+)/restore', [
            'methods' => 'POST',
            'callback' => function ($request) {
                $post_id = $request['id'];

                // Restore the post from trash
                $restored = wp_untrash_post($post_id);

                if ($restored) {
                    return ['success' => true];
                } else {
                    return new WP_Error('restore_failed', 'Failed to restore popup', ['status' => 500]);
                }
            },
            'permission_callback' => function () {
                return current_user_can('delete_posts');
            },
        ]);

        // Permanently delete a trashed popup post
        register_rest_route('artistudio/v1', '/popup/(?P<id>\d+)/delete', [
            'methods' => 'DELETE',
            'callback' => function ($request) {
                $post_id = $request['id'];

                // Permanently delete the post
                $deleted = wp_delete_post($post_id, true);

                if ($deleted) {
                    return ['success' => true];
                } else {
                    return new WP_Error('delete_failed', 'Failed to delete popup', ['status' => 500]);
                }
            },
            'permission_callback' => function () {
                return current_user_can('delete_posts');
            },
        ]);

        // Move a popup post to trash
        register_rest_route('artistudio/v1', '/popup/(?P<id>\d+)/trash', [
            'methods' => 'POST',
            'callback' => function ($request) {
                $post_id = $request['id'];

                // Move the post to trash
                $trashed = wp_trash_post($post_id);

                if ($trashed) {
                    return ['success' => true];
                } else {
                    return new WP_Error('trash_failed', 'Failed to move popup to trash', ['status' => 500]);
                }
            },
            'permission_callback' => function () {
                return current_user_can('delete_posts');
            },
        ]);

        // Delete a popup post
        register_rest_route('artistudio/v1', '/popup/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_popup_post'],
            'permission_callback' => function () {
                return current_user_can('delete_posts');
            },
        ]);

        // Fetch all WordPress pages
        register_rest_route('artistudio/v1', '/pages', [
            'methods' => 'GET',
            'callback' => [$this, 'get_wordpress_pages'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);

        // Fetch all popups
        register_rest_route('artistudio/v1', '/popup', [
            'methods' => 'GET',
            'callback' => function () {
                $popups = get_posts([
                    'post_type' => 'artistudio_popup',
                    'numberposts' => -1, // Fetch all popups
                ]);

                if (empty($popups)) {
                    return new WP_Error('no_popups', 'No popups found', ['status' => 404]);
                }

                // Return the popup data
                return array_map(function ($popup) {
                    return [
                        'id' => $popup->ID,
                        'title' => $popup->post_title,
                        'description' => get_post_meta($popup->ID, 'popup_description', true),
                        'page' => get_post_meta($popup->ID, 'popup_page', true),
                    ];
                }, $popups);
            },
            'permission_callback' => function () {
                return true;
            },
        ]);
    }

    public function get_popup_posts()
    {
        $popups = get_posts([
            'post_type' => 'artistudio_popup',
            'numberposts' => -1,
            'post_status' => 'publish',
        ]);

        if (empty($popups)) {
            return new WP_Error('no_popups', 'No popups found', ['status' => 404]);
        }

        return array_map(function ($popup) {
            return [
                'id' => $popup->ID,
                'title' => $popup->post_title,
                'description' => get_post_meta($popup->ID, 'popup_description', true),
                'page' => get_post_meta($popup->ID, 'popup_page', true),
            ];
        }, $popups);
    }

    public function get_popup_post($request)
    {
        $popup_id = $request['id'];
        $popup = get_post($popup_id);

        if (!$popup || $popup->post_type !== 'artistudio_popup') {
            return new WP_Error('not_found', 'Popup not found', ['status' => 404]);
        }

        return [
            'id' => $popup->ID,
            'title' => $popup->post_title,
            'description' => get_post_meta($popup->ID, 'popup_description', true),
            'page' => get_post_meta($popup->ID, 'popup_page', true),
        ];
    }

    public function create_or_update_popup($request)
    {
        $params = $request->get_params();
        $post_id = $params['id'] ?? 0;

        if ($post_id) {
            // Update existing post
            $updated = wp_update_post([
                'ID' => $post_id,
                'post_title' => $params['title'],
                'post_content' => $params['description'],
                'post_type' => 'artistudio_popup',
                'post_status' => $params['status'],
            ]);

            if (is_wp_error($updated)) {
                return new WP_Error('update_failed', 'Failed to update popup', ['status' => 500]);
            }
        } else {
            // Create new post
            $post_id = wp_insert_post([
                'post_title' => $params['title'],
                'post_content' => $params['description'],
                'post_type' => 'artistudio_popup',
                'post_status' => $params['status'],
            ]);

            if (is_wp_error($post_id)) {
                return new WP_Error('create_failed', 'Failed to create popup', ['status' => 500]);
            }
        }

        // Update custom fields
        update_post_meta($post_id, 'popup_description', $params['description']);
        update_post_meta($post_id, 'popup_page', $params['page']);

        return ['success' => true, 'id' => $post_id];
    }

    public function delete_popup_post($request)
    {
        $post_id = $request['id'];
        if (wp_delete_post($post_id, true)) {
            return ['success' => true];
        } else {
            return new WP_Error('delete_failed', 'Failed to delete popup', ['status' => 500]);
        }
    }

    public function get_wordpress_pages()
    {
        $pages = get_posts([
            'post_type' => 'page',
            'numberposts' => -1,
        ]);

        if (empty($pages)) {
            return new WP_Error('no_pages', 'No pages found', ['status' => 404]);
        }

        return array_map(function ($page) {
            return [
                'id' => $page->ID,
                'title' => $page->post_title,
            ];
        }, $pages);
    }
}