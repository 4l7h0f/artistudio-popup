<?php
namespace ArtiStudio\Popup\Admin;

use ArtiStudio\Popup\Singleton;

class CustomPostType {
    use Singleton;

    protected function __construct() {
        add_action('init', [$this, 'register_popup_post_type']);
        add_action('add_meta_boxes', [$this, 'add_popup_meta_box']);
        add_action('save_post', [$this, 'save_popup_meta']);
    }

    public function register_popup_post_type() {
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

    public function add_popup_meta_box() {
        add_meta_box(
            'artistudio_popup_meta',
            'Popup Settings',
            [$this, 'render_popup_meta_box'],
            'artistudio_popup',
            'side'
        );
    }

    public function render_popup_meta_box($post)
    {
        wp_nonce_field('popup_meta_nonce', 'popup_meta_nonce');
        $description = get_post_meta($post->ID, '_popup_description', true);
        $page = get_post_meta($post->ID, '_popup_page', true);

        // Get all published pages
        $pages = get_pages([
            'post_status' => 'publish',
        ]);
        ?>
        <label for="popup_description">Description:</label>
        <textarea id="popup_description" name="popup_description"><?php echo esc_textarea($description); ?></textarea>
        <label for="popup_page">Page:</label>
        <select id="popup_page" name="popup_page">
            <option value="">Select a page</option>
            <?php foreach ($pages as $page_item): ?>
                <option value="<?php echo esc_attr($page_item->post_name); ?>" <?php selected($page, $page_item->post_name); ?>>
                    <?php echo esc_html($page_item->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function save_popup_meta($post_id) {
        if (isset($_POST['artistudio_popup_page'])) {
            update_post_meta($post_id, '_artistudio_popup_page', sanitize_text_field($_POST['artistudio_popup_page']));
        }
    }
}