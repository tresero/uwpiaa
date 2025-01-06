<?php
/*
Plugin Name: Idloom Attendees
Description: Display attendees from Idloom Events
Version: 1.2
*/

if (!defined('ABSPATH')) exit;

// Autoload classes
spl_autoload_register(function ($class_name) {
    $class_files = array(
        'IdloomAPIHandler' => 'includes/class-api-handler.php',
        'IdloomAdmin' => 'includes/class-admin.php',
        'IdloomDisplay' => 'includes/class-display.php'
    );

    if (isset($class_files[$class_name])) {
        require_once plugin_dir_path(__FILE__) . $class_files[$class_name];
    }
});

// Initialize plugin
function init_idloom_plugin() {
    $api_handler = new IdloomAPIHandler();
    $admin = new IdloomAdmin($api_handler);
    $display = new IdloomDisplay($api_handler);
}

add_action('plugins_loaded', 'init_idloom_plugin');