<?php
class IdloomAdmin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('idloom-settings-group', 'idloom_event_id');
        register_setting('idloom-settings-group', 'idloom_api_key');
    }

    public function add_admin_menu() {
        add_menu_page(
            'Idloom Settings',
            'Idloom Settings',
            'manage_options',
            'idloom-settings',
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        require_once plugin_dir_path(__FILE__) . '../templates/admin-page.php';
    }
}