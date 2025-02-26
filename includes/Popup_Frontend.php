<?php
namespace Artistudio\Popup;

class Popup_Frontend {
    use Trait_Singleton;

    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_shortcode('artistudio_popup', [$this, 'render_popup']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('artistudio-popup-vue', plugins_url('assets/js/app.js', __FILE__), [], null, true);
        wp_enqueue_style('artistudio-popup-style', plugins_url('assets/css/style.css', __FILE__));
    }

    public function render_popup() {
        return '<div id="artistudio-popup-app"></div>';
    }
}