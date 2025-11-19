<?php
class IdloomAPIHandler {
    private $api_base_url = 'https://idloom.events/api/v4';
    private $cache_key;
    private $cache_expiration = 300;
    private $debug = true;

    public function __construct($debug = false) {
        $this->debug = $debug;
        $event_uid = get_option('idloom_event_id');
        $this->cache_key = 'idloom_attendees_' . md5($event_uid);
    }

    private function log_message($message) {
        if ($this->debug && WP_DEBUG) {
            error_log('Idloom: ' . $message);
        }
    }

    private function fetch_page($page = 1) {
        $event_uid = get_option('idloom_event_id');
        $api_key = get_option('idloom_api_key');

        $url = $this->api_base_url . '/attendees?' . http_build_query([
            'event_uid' => $event_uid,
            'page' => $page,
            'ignore_fields_mapping' => 1,
            // Requesting all necessary fields for filtering and display
            'fields' => 'registration_status,payment_status,free_field56,cpy_name,free_field40', 
            'page_size' => 200
        ]);

        $this->log_message("Making API request for page {$page}");
        
        $response = wp_remote_get($url, array(
            'headers' => array('Authorization' => 'Bearer ' . $api_key),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            $this->log_message("API request error: " . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['data'])) {
            $this->log_message("Invalid data received for page {$page}");
            return false;
        }

        return $data;
    }

    public function fetch_attendees() {
        try {
            // Check cache first
            if ($cached_data = get_transient($this->cache_key)) {
                return $cached_data;
            }

            $all_attendees = [];
            
            // Get first page
            $response = $this->fetch_page(1);
            if (!$response || !isset($response['data'])) {
                return $this->get_backup_cache();
            }

            $all_attendees = $response['data'];
            
            // Get total pages from response meta data
            if (isset($response['meta']) && isset($response['meta']['total'])) {
                $total_records = $response['meta']['total'];
            } else {
                $total_records = count($response['data']); // Fallback
            }
            
            // Define HOUR_IN_SECONDS if it's not globally defined (for cache update)
            if (!defined('HOUR_IN_SECONDS')) {
                define('HOUR_IN_SECONDS', 3600);
            }
            
            $total_pages = ceil($total_records / 200);
            $this->log_message("Found total of {$total_records} records across {$total_pages} pages");

            // Fetch remaining pages
            for ($page = 2; $page <= $total_pages; $page++) {
                $page_data = $this->fetch_page($page);
                if ($page_data && isset($page_data['data'])) {
                    $all_attendees = array_merge($all_attendees, $page_data['data']);
                }
            }

            $this->log_message("Retrieved total of " . count($all_attendees) . " records");
            
            $filtered_attendees = $this->filter_attendees($all_attendees);
            if (!empty($filtered_attendees)) {
                $this->update_caches($filtered_attendees);
            }

            return $filtered_attendees;

        } catch (Exception $e) {
            $this->log_message("Error: " . $e->getMessage());
            return $this->get_backup_cache();
        }
    }


    private function filter_attendees($attendees) {
        if (!is_array($attendees)) {
            return [];
        }

        $filtered = array_filter($attendees, function($attendee) {
            return isset($attendee['registration_status']) 
                && isset($attendee['payment_status'])
                && isset($attendee['free_field56']) 
                
                // 1. Must be fully registered
                && $attendee['registration_status'] == 'Form Completed' 
                
                // 2. MUST BE PAID (New condition)
                && $attendee['payment_status'] === 'Paid'
                
                // 3. Must have opted in to the list
                && $attendee['free_field56'] == true; 
                
        });

        $this->log_message("Filtered " . count($attendees) . " attendees down to " . count($filtered));
        return array_values($filtered);
    }

    private function get_backup_cache() {
        return get_transient($this->cache_key . '_backup') ?: [];
    }

    private function update_caches($data) {
        set_transient($this->cache_key, $data, $this->cache_expiration);
        set_transient($this->cache_key . '_backup', $data, HOUR_IN_SECONDS);
    }

    public function clear_cache() {
        delete_transient($this->cache_key);
        delete_transient($this->cache_key . '_backup');
    }
}