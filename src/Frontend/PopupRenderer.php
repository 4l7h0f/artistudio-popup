<?php
namespace ArtiStudio\Popup\Frontend;

use ArtiStudio\Popup\Singleton;

class PopupRenderer {
    use Singleton;

    protected function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'render_popup']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'artistudio-popup',
            ARTISTUDIO_POPUP_URL . 'dist/js/app.js',
            [],
            '1.0',
            true
        );
        wp_enqueue_style(
            'artistudio-popup',
            ARTISTUDIO_POPUP_URL . 'dist/css/app.css',
            [],
            '1.0'
        );
    }

    public function render_popup() {
        echo '<div id="artistudio-popup"></div>';
    }
}