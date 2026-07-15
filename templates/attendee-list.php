<div class="attendee-list">
    <input
        type="text"
        id="attendee-search"
        class="attendee-search"
        placeholder="Search attendees (minimum 3 characters)..."
        value="<?php echo esc_attr($search_term); ?>"
    >

    <table class="attendee-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="firstname">
                    First Name
                    <span class="dashicons <?php
                        echo $sort_info['column'] === 'firstname'
                            ? ($sort_info['direction'] === 'asc'
                                ? 'dashicons-arrow-up'
                                : 'dashicons-arrow-down')
                            : '';
                    ?>"></span>
                </th>

                <th class="sortable" data-sort="lastname">
                    Last Name
                    <span class="dashicons <?php
                        echo $sort_info['column'] === 'lastname'
                            ? ($sort_info['direction'] === 'asc'
                                ? 'dashicons-arrow-up'
                                : 'dashicons-arrow-down')
                            : '';
                    ?>"></span>
                </th>

                <th class="sortable" data-sort="cpy_name">
                    Primary Cast
                    <span class="dashicons <?php
                        echo $sort_info['column'] === 'cpy_name'
                            ? ($sort_info['direction'] === 'asc'
                                ? 'dashicons-arrow-up'
                                : 'dashicons-arrow-down')
                            : '';
                    ?>"></span>
                </th>

                <th>Other Casts</th>

                <th class="sortable" data-sort="cpy_country">
                    Country
                    <span class="dashicons <?php
                        echo $sort_info['column'] === 'cpy_country'
                            ? ($sort_info['direction'] === 'asc'
                                ? 'dashicons-arrow-up'
                                : 'dashicons-arrow-down')
                            : '';
                    ?>"></span>
                </th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($paged_attendees as $attendee): ?>
                <tr class="attendee-item">
                    <td>
                        <?php echo esc_html($attendee['firstname']); ?>
                    </td>

                    <td>
                        <?php echo esc_html($attendee['lastname']); ?>
                    </td>

                    <td>
                        <?php echo esc_html($attendee['cpy_name'] ?? ''); ?>
                    </td>

                    <td>
                        <?php
                        echo is_array($attendee['free_field40'] ?? null)
                            ? esc_html(implode(', ', $attendee['free_field40']))
                            : '';
                        ?>
                    </td>

                    <td>
                        <?php echo esc_html($attendee['cpy_country'] ?? ''); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <nav
            class="aidloom-pagination"
            aria-label="Attendee list pagination"
        >
            <div class="aidloom-pagination-links">
                <?php
                // Preserve the current URL parameters.
                $params = $_GET;

                // First and Previous buttons.
                if ($current_page > 1):
                    $params['aidloom_page'] = 1;
                    $first_url = add_query_arg($params);

                    $params['aidloom_page'] = $current_page - 1;
                    $prev_url = add_query_arg($params);
                ?>
                    <a
                        href="<?php echo esc_url($first_url); ?>"
                        class="aidloom-page-control aidloom-first-page"
                        aria-label="First page"
                        title="First page"
                    >
                        <span
                            class="dashicons dashicons-controls-skipback"
                            aria-hidden="true"
                        ></span>
                    </a>

                    <a
                        href="<?php echo esc_url($prev_url); ?>"
                        class="aidloom-page-control aidloom-prev-page"
                        aria-label="Previous page"
                        title="Previous page"
                    >
                        <span
                            class="dashicons dashicons-controls-back"
                            aria-hidden="true"
                        ></span>
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end = min($total_pages, $current_page + 2);

                if ($start > 1):
                    $params['aidloom_page'] = 1;
                ?>
                    <a
                        href="<?php echo esc_url(add_query_arg($params)); ?>"
                        class="aidloom-page-number"
                        aria-label="Page 1"
                    >
                        1
                    </a>

                    <span
                        class="aidloom-pagination-dots"
                        aria-hidden="true"
                    >
                        &hellip;
                    </span>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php
                    $params['aidloom_page'] = $i;
                    $url = add_query_arg($params);
                    $is_current = ($i === $current_page);
                    ?>

                    <a
                        href="<?php echo esc_url($url); ?>"
                        class="aidloom-page-number<?php echo $is_current ? ' is-current' : ''; ?>"
                        aria-label="Page <?php echo esc_attr($i); ?>"
                        <?php echo $is_current ? 'aria-current="page"' : ''; ?>
                    >
                        <?php echo esc_html($i); ?>
                    </a>
                <?php endfor; ?>

                <?php
                if ($end < $total_pages):
                    $params['aidloom_page'] = $total_pages;
                ?>
                    <span
                        class="aidloom-pagination-dots"
                        aria-hidden="true"
                    >
                        &hellip;
                    </span>

                    <a
                        href="<?php echo esc_url(add_query_arg($params)); ?>"
                        class="aidloom-page-number"
                        aria-label="Page <?php echo esc_attr($total_pages); ?>"
                    >
                        <?php echo esc_html($total_pages); ?>
                    </a>
                <?php endif; ?>

                <?php
                // Next and Last buttons.
                if ($current_page < $total_pages):
                    $params['aidloom_page'] = $current_page + 1;
                    $next_url = add_query_arg($params);

                    $params['aidloom_page'] = $total_pages;
                    $last_url = add_query_arg($params);
                ?>
                    <a
                        href="<?php echo esc_url($next_url); ?>"
                        class="aidloom-page-control aidloom-next-page"
                        aria-label="Next page"
                        title="Next page"
                    >
                        <span
                            class="dashicons dashicons-controls-forward"
                            aria-hidden="true"
                        ></span>
                    </a>

                    <a
                        href="<?php echo esc_url($last_url); ?>"
                        class="aidloom-page-control aidloom-last-page"
                        aria-label="Last page"
                        title="Last page"
                    >
                        <span
                            class="dashicons dashicons-controls-skipforward"
                            aria-hidden="true"
                        ></span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="aidloom-pagination-info">
                Page <?php echo esc_html($current_page); ?>
                of <?php echo esc_html($total_pages); ?>
            </div>
        </nav>
    <?php endif; ?>
</div>