<div class="attendee-list">
    <input type="text" id="attendee-search" class="attendee-search"
    placeholder="Search attendees (minimum 3 characters)..."
    value="<?php echo esc_attr($search_term); ?>">
    
    <table class="attendee-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="firstname">
                    First Name 
                    <span class="dashicons <?php echo $sort_info['column'] === 'firstname' 
                        ? ($sort_info['direction'] === 'asc' 
                            ? 'dashicons-arrow-up' 
                            : 'dashicons-arrow-down')
                        : ''; ?>">
                    </span>
                </th>
                <th class="sortable" data-sort="lastname">
                    Last Name 
                    <span class="dashicons <?php echo $sort_info['column'] === 'lastname' 
                        ? ($sort_info['direction'] === 'asc' 
                            ? 'dashicons-arrow-up' 
                            : 'dashicons-arrow-down')
                        : ''; ?>">
                    </span>
                </th>
                <th class="sortable" data-sort="cpy_name"> Primary Cast
    <span class="dashicons <?php echo $sort_info['column'] === 'cpy_name' 
        ? ($sort_info['direction'] === 'asc' 
            ? 'dashicons-arrow-up' 
            : 'dashicons-arrow-down')
        : ''; ?>">
    </span>
</th>
                <th>Other Casts</th>
                <th class="sortable" data-sort="cpy_country">
                    Country 
                    <span class="dashicons <?php echo $sort_info['column'] === 'cpy_country' 
                        ? ($sort_info['direction'] === 'asc' 
                            ? 'dashicons-arrow-up' 
                            : 'dashicons-arrow-down')
                        : ''; ?>">
                    </span>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paged_attendees as $attendee): ?>
                <tr class="attendee-item">
                    <td><?php echo esc_html($attendee['firstname']); ?></td>
                    <td><?php echo esc_html($attendee['lastname']); ?></td>
                    <td><?php echo esc_html($attendee['cpy_name'] ?? ''); ?></td>
                    <td><?php echo is_array($attendee['free_field40']) ? esc_html(implode(', ', $attendee['free_field40'])) : ''; ?></td>
                    <td><?php echo esc_html($attendee['cpy_country'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <div class="nav-links">
                <?php
                // Get current URL parameters
                $params = $_GET;
                
                // First and Previous buttons
                if ($current_page > 1):
                    $params['aidloom_page'] = 1;
                    $first_url = add_query_arg($params);
                    $params['aidloom_page'] = $current_page - 1;
                    $prev_url = add_query_arg($params);
                ?>
                    <a href="<?php echo esc_url($first_url); ?>" class="first-page">
                        <span class="dashicons dashicons-controls-skipback"></span>
                    </a>
                    <a href="<?php echo esc_url($prev_url); ?>" class="prev-page">
                        <span class="dashicons dashicons-controls-back"></span>
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end = min($total_pages, $current_page + 2);

                if ($start > 1): 
                    $params['aidloom_page'] = 1;
                ?>
                    <a href="<?php echo esc_url(add_query_arg($params)); ?>" class="page-numbers">1</a>
                    <span class="pagination-dots">...</span>
                <?php endif;

                for ($i = $start; $i <= $end; $i++): 
                    $params['aidloom_page'] = $i;
                    $url = add_query_arg($params);
                ?>
                    <a href="<?php echo esc_url($url); ?>" 
                       class="page-numbers <?php echo ($i === $current_page) ? 'current' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor;

                if ($end < $total_pages): 
                    $params['aidloom_page'] = $total_pages;
                ?>
                    <span class="pagination-dots">...</span>
                    <a href="<?php echo esc_url(add_query_arg($params)); ?>" class="page-numbers">
                        <?php echo $total_pages; ?>
                    </a>
                <?php endif; ?>

                <?php 
                // Next and Last buttons
                if ($current_page < $total_pages):
                    $params['aidloom_page'] = $current_page + 1;
                    $next_url = add_query_arg($params);
                    $params['aidloom_page'] = $total_pages;
                    $last_url = add_query_arg($params);
                ?>
                    <a href="<?php echo esc_url($next_url); ?>" class="next-page">
                        <span class="dashicons dashicons-controls-forward"></span>
                    </a>
                    <a href="<?php echo esc_url($last_url); ?>" class="last-page">
                        <span class="dashicons dashicons-controls-skipforward"></span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="pagination-info">
                Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
            </div>
        </div>
    <?php endif; ?>
</div>