<?php
namespace ArtiStudio\Popup\Frontend;

class PopupRenderer
{
    public static function init()
    {
        add_action('wp_footer', [self::class, 'render_popup']);
    }

    public static function render_popup()
    {
        // Get the current page ID or slug
        global $post;
        $current_page = $post ? $post->ID : '';

        // Fetch all popups
        $popups = get_posts([
            'post_type' => 'artistudio_popup',
            'numberposts' => -1,
        ]);

        // Filter popups for the current page
        foreach ($popups as $popup) {
            $popup_page = get_post_meta($popup->ID, '_artistudio_popup_page', true);

            // Check if the popup should be displayed on the current page
            if ($popup_page == $current_page || $popup_page == $post->post_name) {
                self::display_popup($popup);
            }
        }
    }

    private static function display_popup($popup)
    {
        ?>
        <div id="artistudio-popup" class="popup">
            <div class="popup-content">
                <h2><?php echo esc_html($popup->post_title); ?></h2>
                <div><?php echo wp_kses_post($popup->post_content); ?></div>
                <button class="close-popup">Close</button>
            </div>
        </div>
        <style>
            .popup {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                z-index: 1000;
            }

            .close-popup {
                margin-top: 10px;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const closeButton = document.querySelector('.close-popup');
                const popup = document.querySelector('.popup');

                if (closeButton && popup) {
                    closeButton.addEventListener('click', function () {
                        popup.style.display = 'none';
                    });
                }
            });
        </script>
        <?php
    }
}