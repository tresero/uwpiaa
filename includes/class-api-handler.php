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
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $this->filter_attendees($body['data'] ?? []);
    }
// field56 is opt in
    private function filter_attendees($attendees) {
        return array_filter($attendees, function($attendee) {
            return $attendee['registration_status'] === 'Complete'
                   &&  $attendee['free_field56'] === true;
        });
    }
}