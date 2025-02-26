<?php
/**
 * Plugin Name:     Artistudio Popup
 * Plugin URI:      https://althof.online
 * Description:     A Wordpres Plugin using Vue
 * Author:          Muhamad Arief Rachman
 * Author URI:      https://althof.online
 * Text Domain:     artistudio-popup
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Artistudio_Popup
 */


 if (!defined('ABSPATH')) {
    exit; 
}

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'Artistudio\\Popup\\';
    $base_dir = __DIR__ . '/includes/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
Artistudio\Popup\Popup_Main::get_instance();
