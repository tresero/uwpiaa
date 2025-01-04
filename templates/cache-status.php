<?php
if (!defined('ABSPATH')) exit;

$cache_info = $this->get_cache_info();
?>
<div class="wrap">
    <h1>Idloom Cache Status</h1>

    <?php if (isset($_GET['cleared'])): ?>
    <div class="notice notice-success">
        <p>Cache cleared successfully!</p>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <h2>Cache Information</h2>
        <table class="widefat striped">
            <tr>
                <th>Cache Type</th>
                <th>Status</th>
                <th>Records</th>
                <th>Expires</th>
            </tr>
            <tr>
                <td>Primary Cache (5 min)</td>
                <td><?php echo $cache_info['primary_cache'] ? '<span style="color:green">Active</span>' : '<span style="color:red">Empty</span>'; ?></td>
                <td><?php echo is_array($cache_info['primary_cache']) ? count($cache_info['primary_cache']) : '0'; ?></td>
                <td><?php echo $cache_info['primary_expiry'] ? date('Y-m-d H:i:s', $cache_info['primary_expiry']) : 'N/A'; ?></td>
            </tr>
            <tr>
                <td>Backup Cache (1 hour)</td>
                <td><?php echo $cache_info['backup_cache'] ? '<span style="color:green">Active</span>' : '<span style="color:red">Empty</span>'; ?></td>
                <td><?php echo is_array($cache_info['backup_cache']) ? count($cache_info['backup_cache']) : '0'; ?></td>
                <td><?php echo $cache_info['backup_expiry'] ? date('Y-m-d H:i:s', $cache_info['backup_expiry']) : 'N/A'; ?></td>
            </tr>
        </table>

        <p>Cache Key: <?php echo esc_html($cache_info['cache_key']); ?></p>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="clear_idloom_cache">
            <?php wp_nonce_field('clear_idloom_cache_nonce'); ?>
            <p>
                <button type="submit" class="button button-primary">Clear Cache</button>
            </p>
        </form>
    </div>

    <?php if (file_exists(WP_CONTENT_DIR . '/uploads/idloom-debug.log')): ?>
    <div class="card" style="margin-top: 20px;">
        <h2>Debug Log</h2>
        <pre style="background: #f0f0f1; padding: 10px; max-height: 300px; overflow: auto;">
        <?php echo esc_html(file_get_contents(WP_CONTENT_DIR . '/uploads/idloom-debug.log')); ?>
        </pre>
    </div>
    <?php endif; ?>
</div>