<div class="attendee-list">
    <input type="text" id="attendee-search" class="attendee-search" placeholder="Search attendees...">
    
    <table class="attendee-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="firstname">First Name <span class="dashicons"></span></th>
                <th class="sortable" data-sort="lastname">Last Name <span class="dashicons"></span></th>
                <th class="sortable" data-sort="free_field27">Primary Cast <span class="dashicons"></span></th>
                <th class="sortable" data-sort="free_field40">Other Casts <span class="dashicons"></span></th>
                <th class="sortable" data-sort="cpy_country">Country <span class="dashicons"></span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paged_attendees as $attendee): ?>
                <tr class="attendee-item">
                    <td><?php echo esc_html($attendee['firstname']); ?></td>
                    <td><?php echo esc_html($attendee['lastname']); ?></td>
                    <td><?php echo esc_html($attendee['free_field27'] ?? ''); ?></td>
                    <td><?php echo is_array($attendee['free_field40']) ? implode(', ', $attendee['free_field40']) : ''; ?></td>
                    <td><?php echo esc_html($attendee['cpy_country'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>