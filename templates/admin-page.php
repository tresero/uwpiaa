<div class="wrap">
    <h2>Idloom Events Settings</h2>
    <form method="post" action="options.php">
        <?php settings_fields('idloom-settings-group'); ?>
        <table class="form-table">
            <tr>
                <th>API Key</th>
                <td><input type="text" name="idloom_api_key" value="<?php echo esc_attr(get_option('idloom_api_key')); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th>Event ID</th>
                <td><input type="text" name="idloom_event_id" value="<?php echo esc_attr(get_option('idloom_event_id')); ?>" class="regular-text"></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>