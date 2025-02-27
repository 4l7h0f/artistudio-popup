<?php
namespace ArtiStudio\Popup;

trait Singleton {
    private static $instance;

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}