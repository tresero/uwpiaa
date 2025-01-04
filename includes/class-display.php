<?php
class IdloomDisplay {
    private $api_handler;
    private $attendees_per_page = 20;

    public function __construct($api_handler) {
        $this->api_handler = $api_handler;
        add_shortcode('display_attendees', array($this, 'display_attendees_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function display_attendees_shortcode() {
        // 1. Get all attendees first
        $attendees = $this->api_handler->fetch_attendees();
        if (!$attendees) {
            return '<p>No attendees found.</p>';
        }

        // 2. Apply search if present
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        if (!empty($search)) {
            $attendees = array_filter($attendees, function($attendee) use ($search) {
                $searchable = '';
                foreach ($attendee as $value) {
                    if (is_array($value)) {
                        $searchable .= implode(' ', $value) . ' ';
                    } else {
                        $searchable .= $value . ' ';
                    }
                }
                return stripos($searchable, $search) !== false;
            });
            // Reset array keys after filter
            $attendees = array_values($attendees);
        }

        // 3. Apply sorting to entire dataset
        $sort_column = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'lastname';
        $sort_direction = isset($_GET['order']) ? strtolower($_GET['order']) : 'asc';
        $sort_direction = in_array($sort_direction, ['asc', 'desc']) ? $sort_direction : 'asc';

        usort($attendees, function($a, $b) use ($sort_column, $sort_direction) {
            // Get values for comparison
            $a_val = isset($a[$sort_column]) ? $a[$sort_column] : '';
            $b_val = isset($b[$sort_column]) ? $b[$sort_column] : '';
            
            // Handle array values
            if (is_array($a_val)) $a_val = implode(', ', $a_val);
            if (is_array($b_val)) $b_val = implode(', ', $b_val);
            
            // Handle null values
            $a_val = $a_val ?? '';
            $b_val = $b_val ?? '';
            
            // Convert to lowercase for case-insensitive comparison
            $a_val = strtolower((string)$a_val);
            $b_val = strtolower((string)$b_val);
            
            // Compare values
            if ($a_val == $b_val) {
                // If values are equal, use lastname as secondary sort
                return $sort_direction === 'asc' ? 
                    strcasecmp($a['lastname'], $b['lastname']) : 
                    strcasecmp($b['lastname'], $a['lastname']);
            }
            
            $result = strcasecmp($a_val, $b_val);
            return $sort_direction === 'asc' ? $result : -$result;
        });

        // 4. Apply pagination last
        $total_items = count($attendees);
        if ($total_items === 0) {
            return '<div class="attendee-list">' .
                   '<input type="text" id="attendee-search" class="attendee-search" placeholder="Search attendees..." value="' . esc_attr($search) . '">' .
                   '<p>No attendees found matching your search.</p>' .
                   '</div>';
        }

        $current_page = isset($_GET['aidloom_page']) ? max(1, (int)$_GET['aidloom_page']) : 1;
        $total_pages = ceil($total_items / $this->attendees_per_page);
        $current_page = min($current_page, $total_pages);
        $offset = ($current_page - 1) * $this->attendees_per_page;
        
        $paged_attendees = array_slice($attendees, $offset, $this->attendees_per_page);

        // 5. Pass data to template
        $sort_info = [
            'column' => $sort_column,
            'direction' => $sort_direction
        ];
        $search_term = $search;

        ob_start();
        require plugin_dir_path(__FILE__) . '../templates/attendee-list.php';
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('idloom-attendees-style', plugins_url('../assets/css/style.css', __FILE__));
        wp_enqueue_script('idloom-attendees-script', plugins_url('../assets/js/script.js', __FILE__), array('jquery'), '1.0', true);
    }
}