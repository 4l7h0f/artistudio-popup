<?php
namespace ArtiStudio\Popup\Admin;

class PopupPostType
{
    public static function register()
    {
        // Hook into 'init' to ensure $wp_rewrite is available
        add_action('init', [self::class, 'register_post_type']);
        add_action('add_meta_boxes', [self::class, 'add_meta_box']);
        add_action('save_post', [self::class, 'save_meta_box']);
    }

    public static function register_post_type()
    {
        register_post_type('artistudio_popup', [
            'labels' => [
                'name' => 'Popups',
                'singular_name' => 'Popup',
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'editor'],
        ]);
    }

    public static function add_meta_box()
    {
        add_meta_box(
            'artistudio_popup_page',
            'Popup Page',
            [self::class, 'render_meta_box'],
            'artistudio_popup'
        );
    }

    public static function render_meta_box($post)
    {
        // Get the saved page ID for this popup
        $selected_page = get_post_meta($post->ID, '_artistudio_popup_page', true);

        // Get all published pages
        $pages = get_pages([
            'post_status' => 'publish', // Only fetch published pages
        ]);
        ?>
        <label for="artistudio_popup_page">Page to Display Popup:</label>
        <select id="artistudio_popup_page" name="artistudio_popup_page">
            <option value="">— Select a Page —</option>
            <?php
            foreach ($pages as $page) {
                $page_id = $page->ID;
                $page_title = $page->post_title;
                $selected = selected($selected_page, $page_id, false);
                echo "<option value='{$page_id}' {$selected}>{$page_title}</option>";
            }
            ?>
        </select> <?php
    }

    public static function save_meta_box($post_id)
    {
        if (isset($_POST['artistudio_popup_page'])) {
            update_post_meta($post_id, '_artistudio_popup_page', sanitize_text_field($_POST['artistudio_popup_page']));
        }
    }
}