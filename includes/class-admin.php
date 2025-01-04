<?php
class IdloomAdmin {
    private $api_handler;

    public function __construct($api_handler) {
        $this->api_handler = $api_handler;
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_clear_idloom_cache', array($this, 'clear_cache'));
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

        add_submenu_page(
            'idloom-settings',
            'Cache Status',
            'Cache Status',
            'manage_options',
            'idloom-cache',
            array($this, 'cache_status_page')
        );
    }

    public function settings_page() {
        require_once plugin_dir_path(__FILE__) . '../templates/admin-page.php';
    }

    public function cache_status_page() {
        require_once plugin_dir_path(__FILE__) . '../templates/cache-status.php';
    }

    public function clear_cache() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('clear_idloom_cache_nonce');
        
        $this->api_handler->clear_cache();
        
        wp_redirect(add_query_arg(
            array('page' => 'idloom-cache', 'cleared' => '1'),
            admin_url('admin.php')
        ));
        exit;
    }

    public function get_cache_info() {
        $event_uid = get_option('idloom_event_id');
        $cache_key = 'idloom_attendees_' . md5($event_uid);
        
        return array(
            'primary_cache' => get_transient($cache_key),
            'backup_cache' => get_transient($cache_key . '_backup'),
            'primary_expiry' => get_option('_transient_timeout_' . $cache_key),
            'backup_expiry' => get_option('_transient_timeout_' . $cache_key . '_backup'),
            'cache_key' => $cache_key
        );
    }
}