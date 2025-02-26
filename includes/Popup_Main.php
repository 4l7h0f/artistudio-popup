<?php
namespace Artistudio\Popup;

use Artistudio\Popup\Popup_CPT;
use Artistudio\Popup\Popup_API;
use Artistudio\Popup\Popup_Frontend;

class Popup_Main {
    use Trait_Singleton;

    private function __construct() {
        // Initialize components
        Popup_CPT::get_instance();
        Popup_API::get_instance();
        Popup_Frontend::get_instance();
    }
}