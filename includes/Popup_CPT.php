<?php
namespace Artistudio\Popup;

class Popup_CPT
{
    use Trait_Singleton;

    private function __construct()
    {
        add_action('init', [$this, 'register_cpt']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_data']);
    }

    public function register_cpt()
    {
        register_post_type('artistudio_popup', [
            'label' => 'Popups',
            'public' => true,
            'show_in_rest' => true,
        ]);
    }

    public function add_meta_boxes()
    {
        add_meta_box('popup_meta', 'Popup Details', [$this, 'render_meta_box'], 'artistudio_popup');
    }

    public function render_meta_box($post)
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

    public function save_meta_data($post_id) {
        if (!isset($_POST['popup_meta_nonce']) || !wp_verify_nonce($_POST['popup_meta_nonce'], 'popup_meta_nonce')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['popup_description'])) {
            update_post_meta($post_id, '_popup_description', sanitize_text_field($_POST['popup_description']));
        }
        if (isset($_POST['popup_page'])) {
            update_post_meta($post_id, '_popup_page', sanitize_text_field($_POST['popup_page']));
        }
    }
}