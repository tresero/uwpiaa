<?php
class IdloomAPIHandler {
    private $api_base_url = 'https://idloom.events/api/v4';
    private $cache_key;
    private $cache_expiration = 300; // 5 minutes in seconds
    private $log_file;

    public function __construct() {
        // Set up logging
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/idloom-debug.log';
        
        // Generate unique cache key based on event ID to prevent conflicts
        $event_uid = get_option('idloom_event_id');
        $this->cache_key = 'idloom_attendees_' . md5($event_uid);
        
        // Log initialization
        $this->log_message("IdloomAPIHandler initialized with cache key: {$this->cache_key}");
    }

    private function log_message($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}\n";
        error_log($log_entry, 3, $this->log_file);
    }

    public function fetch_attendees() {
        try {
            $this->log_message("Fetching attendees...");
            
            // Try to get cached data first
            $cached_data = get_transient($this->cache_key);
            if ($cached_data !== false) {
                $this->log_message("Returned " . count($cached_data) . " attendees from cache");
                return $cached_data;
            }

            $event_uid = get_option('idloom_event_id');
            $api_key = get_option('idloom_api_key');

            // Validate required settings
            if (empty($event_uid) || empty($api_key)) {
                $this->log_message("ERROR: Missing required settings (event_uid or api_key)");
                return false;
            }

            $this->log_message("Making API request for event: {$event_uid}");
            
            $response = wp_remote_get($this->api_base_url . '/attendees?' . http_build_query([
                'event_uid' => $event_uid,
                'page_size' => 200,
                'ignore_fields_mapping' => 1
            ]), array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key
                ),
                'timeout' => 15
            ));

            if (is_wp_error($response)) {
                $this->log_message("API request error: " . $response->get_error_message());
                return $this->get_backup_cache();
            }

            $status_code = wp_remote_retrieve_response_code($response);
            $this->log_message("API response status code: {$status_code}");
            
            if ($status_code !== 200) {
                $this->log_message("API error: Non-200 status code");
                return $this->get_backup_cache();
            }

            $body = wp_remote_retrieve_body($response);
            if (empty($body)) {
                $this->log_message("API error: Empty response body");
                return $this->get_backup_cache();
            }

            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->log_message("JSON decode error: " . json_last_error_msg());
                return $this->get_backup_cache();
            }

            if (!isset($data['data']) || !is_array($data['data'])) {
                $this->log_message("Invalid data structure received from API");
                $this->log_message("Response structure: " . print_r($data, true));
                return $this->get_backup_cache();
            }

            $filtered_attendees = $this->filter_attendees($data['data']);
            $this->log_message("Filtered attendees count: " . count($filtered_attendees));
            
            if (!empty($filtered_attendees)) {
                $this->update_caches($filtered_attendees);
                $this->log_message("Updated cache with new data");
            }

            return $filtered_attendees;

        } catch (Exception $e) {
            $this->log_message("Unexpected error: " . $e->getMessage());
            $this->log_message("Stack trace: " . $e->getTraceAsString());
            return $this->get_backup_cache();
        }
    }

    private function filter_attendees($attendees) {
        if (!is_array($attendees)) {
            $this->log_message("Warning: filter_attendees received non-array input");
            return [];
        }

        $filtered = array_filter($attendees, function($attendee) {
            return isset($attendee['registration_status']) 
                && isset($attendee['payment_status'])
                && isset($attendee['free_field56'])
                && $attendee['registration_status'] === 'Complete'
                && $attendee['free_field56'] === true
                && $attendee['payment_status'] === 'Paid';
        });

        $this->log_message("Filtered " . count($attendees) . " attendees down to " . count($filtered));
        return $filtered;
    }

    private function get_backup_cache() {
        $backup = get_transient($this->cache_key . '_backup');
        $this->log_message("Retrieved backup cache: " . ($backup !== false ? "found" : "not found"));
        return $backup !== false ? $backup : [];
    }

    private function update_caches($data) {
        $primary_result = set_transient($this->cache_key, $data, $this->cache_expiration);
        $backup_result = set_transient($this->cache_key . '_backup', $data, HOUR_IN_SECONDS);
        
        $this->log_message("Cache update results - Primary: " . 
            ($primary_result ? "success" : "failed") . 
            ", Backup: " . ($backup_result ? "success" : "failed"));
    }

    public function clear_cache() {
        $this->log_message("Clearing all caches");
        delete_transient($this->cache_key);
        delete_transient($this->cache_key . '_backup');
    }
}