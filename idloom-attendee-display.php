<?php
/*
Plugin Name: Idloom Events Attendee Display
Description: Displays attendee information from Idloom Events API
Version: 1.0
*/

defined('ABSPATH') or die('No direct access allowed');

require_once plugin_dir_path(__FILE__) . 'includes/class-api-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-display.php';

class IdloomAttendeeDisplay {
    private $api_handler;
    private $admin;
    private $display;

    public function __construct() {
        $this->api_handler = new IdloomAPIHandler();
        $this->admin = new IdloomAdmin();
        $this->display = new IdloomDisplay($this->api_handler);
    }
}

new IdloomAttendeeDisplay();