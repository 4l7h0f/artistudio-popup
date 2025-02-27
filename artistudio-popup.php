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
require_once __DIR__ . '/vendor/autoload.php';

// Initialize the plugin
use ArtiStudio\Popup\Plugin;

Plugin::get_instance();