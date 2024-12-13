<?php
class IdloomDisplay {
    private $api_handler;
    private $attendees_per_page = 20;

    public function __construct($api_handler) {
        $this->api_handler = $api_handler;
        add_shortcode('display_attendees', array($this, 'display_attendees_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('idloom-attendees-style', plugins_url('../assets/css/style.css', __FILE__));
        wp_enqueue_script('idloom-attendees-script', plugins_url('../assets/js/script.js', __FILE__), array('jquery'), '1.0', true);
        wp_localize_script('idloom-attendees-script', 'idloomAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    public function display_attendees_shortcode() {
        $attendees = $this->api_handler->fetch_attendees();
        if (!$attendees) {
            return '<p>No attendees found.</p>';
        }

        $current_page = isset($_GET['aidloom_page']) ? (int)$_GET['aidloom_page'] : 1;
        $total_pages = ceil(count($attendees) / $this->attendees_per_page);
        $offset = ($current_page - 1) * $this->attendees_per_page;

        usort($attendees, function($a, $b) {
            return strcmp($a['lastname'], $b['lastname']);
        });

        $paged_attendees = array_slice($attendees, $offset, $this->attendees_per_page);

        ob_start();
        require plugin_dir_path(__FILE__) . '../templates/attendee-list.php';
        return ob_get_clean();
    }
}