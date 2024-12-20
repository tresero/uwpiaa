<?php
class IdloomAPIHandler {
    private $api_base_url = 'https://idloom.events/api/v4';

    public function fetch_attendees() {
        $event_uid = get_option('idloom_event_id');
        $api_key = get_option('idloom_api_key');

        $response = wp_remote_get($this->api_base_url . '/attendees?' . http_build_query([
            'event_uid' => $event_uid,
            'page_size' => 200,
            'ignore_fields_mapping' => 1
        ]), array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key
            )
        ));

        if (is_wp_error($response)) {
            error_log('API request error: ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 429) {
            error_log('API rate limit exceeded. Please try again later.');
            return false;
        } elseif ($status_code >= 400) {
            $error_body = wp_remote_retrieve_body($response);
            error_log('API request failed with status ' . $status_code . ': ' . $error_body);
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return false;
        }

        return $this->filter_attendees($body['data'] ?? []);
    }

    private function filter_attendees($attendees) {
        return array_filter($attendees, function($attendee) {
            return $attendee['registration_status'] === 'Complete'
                   &&  $attendee['free_field56'] === true
                   && $attendee['payment_status'] === 'Paid';
        });
    }
}
