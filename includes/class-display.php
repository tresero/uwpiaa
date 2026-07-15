<?php
/**
 * Class responsible for displaying the Idloom attendee list via shortcode.
 */
class IdloomDisplay {
    private $api_handler;
    private $attendees_per_page = 20;

    /**
     * Constructor.
     *
     * @param object $api_handler Instance of the API handler class.
     */
    public function __construct($api_handler) {
        $this->api_handler = $api_handler;

        add_shortcode('display_attendees', array($this, 'display_attendees_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Handles the [display_attendees] shortcode output.
     *
     * @return string HTML output for the attendee list.
     */
    public function display_attendees_shortcode() {
        $attendees = $this->api_handler->fetch_attendees();

        if (!$attendees || !is_array($attendees)) {
            return '<p>No attendees found.</p>';
        }

        /*
         * Search ONLY the visible table columns:
         * First Name, Last Name, Primary Cast, Other Casts, Country.
         */
        $search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';

        if ($search !== '') {
            $search_fields = array(
                'firstname',
                'lastname',
                'cpy_name',
                'free_field40',
                'cpy_country',
            );

            $attendees = array_filter($attendees, function($attendee) use ($search, $search_fields) {
                if (!is_array($attendee)) {
                    return false;
                }

                $searchable = '';

                foreach ($search_fields as $field) {
                    if (!array_key_exists($field, $attendee)) {
                        continue;
                    }

                    $value = $attendee[$field];

                    if (is_array($value)) {
                        $flat_values = array();

                        array_walk_recursive($value, function($item) use (&$flat_values) {
                            if (is_scalar($item)) {
                                $flat_values[] = (string) $item;
                            }
                        });

                        $searchable .= ' ' . implode(' ', $flat_values);
                    } elseif (is_scalar($value)) {
                        $searchable .= ' ' . (string) $value;
                    }
                }

                return stripos($searchable, $search) !== false;
            });

            $attendees = array_values($attendees);
        }

        /*
         * Sort ONLY allowed visible columns.
         */
        $allowed_sort_columns = array(
            'firstname',
            'lastname',
            'cpy_name',
            'cpy_country',
        );

        $sort_column = isset($_GET['sort']) ? sanitize_text_field(wp_unslash($_GET['sort'])) : 'lastname';

        if (!in_array($sort_column, $allowed_sort_columns, true)) {
            $sort_column = 'lastname';
        }

        $sort_direction = isset($_GET['order']) ? strtolower(sanitize_text_field(wp_unslash($_GET['order']))) : 'asc';

        if (!in_array($sort_direction, array('asc', 'desc'), true)) {
            $sort_direction = 'asc';
        }

        usort($attendees, function($a, $b) use ($sort_column, $sort_direction) {
            $a_val = $a[$sort_column] ?? '';
            $b_val = $b[$sort_column] ?? '';

            if (is_array($a_val)) {
                $a_val = implode(', ', array_filter($a_val, 'is_scalar'));
            }

            if (is_array($b_val)) {
                $b_val = implode(', ', array_filter($b_val, 'is_scalar'));
            }

            $a_val = strtolower(trim((string) $a_val));
            $b_val = strtolower(trim((string) $b_val));

            if ($a_val === $b_val) {
                $a_lastname = strtolower(trim((string) ($a['lastname'] ?? '')));
                $b_lastname = strtolower(trim((string) ($b['lastname'] ?? '')));

                $last_cmp = strcmp($a_lastname, $b_lastname);

                if ($last_cmp !== 0) {
                    return $sort_direction === 'asc' ? $last_cmp : -$last_cmp;
                }

                $a_firstname = strtolower(trim((string) ($a['firstname'] ?? '')));
                $b_firstname = strtolower(trim((string) ($b['firstname'] ?? '')));

                $first_cmp = strcmp($a_firstname, $b_firstname);

                return $sort_direction === 'asc' ? $first_cmp : -$first_cmp;
            }

            $cmp = strcmp($a_val, $b_val);

            return $sort_direction === 'asc' ? $cmp : -$cmp;
        });

        /*
         * Pagination after searching and sorting.
         */
        $total_items = count($attendees);

        if ($total_items === 0 && $search !== '') {
            return '<div class="attendee-list">' .
                '<input type="text" id="attendee-search" class="attendee-search" placeholder="Search attendees (minimum 3 characters)..." value="' . esc_attr($search) . '">' .
                '<p>No attendees found matching your search.</p>' .
                '</div>';
        }

        if ($total_items === 0) {
            return '<p>No attendees found.</p>';
        }

        $current_page = isset($_GET['aidloom_page']) ? max(1, (int) $_GET['aidloom_page']) : 1;
        $total_pages = (int) ceil($total_items / $this->attendees_per_page);
        $current_page = min($current_page, $total_pages);
        $offset = ($current_page - 1) * $this->attendees_per_page;

        $paged_attendees = array_slice($attendees, $offset, $this->attendees_per_page);

        $sort_info = array(
            'column' => $sort_column,
            'direction' => $sort_direction,
        );

        $search_term = $search;

        ob_start();

        $template_path = plugin_dir_path(__FILE__) . '../templates/attendee-list.php';

        if (file_exists($template_path)) {
            require $template_path;
        } else {
            echo '<p>Error: Attendee list template file not found.</p>';
        }

        return ob_get_clean();
    }

    /**
     * Enqueues the necessary CSS and JavaScript files for the attendee list display.
     */
    public function enqueue_scripts() {
        wp_enqueue_style('dashicons');

        wp_enqueue_style(
            'idloom-attendees-style',
            plugins_url('../assets/css/style.css', __FILE__),
            array(),
            '1.0'
        );

        wp_enqueue_script(
            'idloom-attendees-script',
            plugins_url('../assets/js/script.js', __FILE__),
            array('jquery'),
            '1.0',
            true
        );
    }
}
