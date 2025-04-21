<?php

function ases_add_settings_page() {
    add_options_page(
        'Amazon SES SMTP',
        'Amazon SES SMTP',
        'manage_options',
        'amazon-ses-smtp',
        'ases_render_settings_page'
    );
}

function ases_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Amazon SES SMTP Settings</h1>

        <?php
        // Handle license activation
        if (isset($_POST['ases_activate_license']) && check_admin_referer('ases_activate_license_action')) {
            $email = sanitize_email($_POST['ases_license_email']);
            $key = sanitize_text_field($_POST['ases_license_key']);
            $result = ases_validate_license($email, $key);

            add_action('admin_notices', function () use ($result) {
                if ($result['success']) {
                    echo "<div class='notice notice-success is-dismissible'><p>✅ License activated successfully. Expires: " . date('Y-m-d H:i', $result['expires']) . "</p></div>";
                } else {
                    echo "<div class='notice notice-error is-dismissible'><p>❌ License activation failed: " . esc_html($result['error']) . "</p></div>";
                }
            });
        }
        ?>

        <form method="post" action="options.php">
            <?php
            settings_fields('ases_settings_group');
            do_settings_sections('amazon-ses-smtp');
            submit_button();
            ?>
        </form>

        <form method="post">
            <p>
                <?php wp_nonce_field('ases_activate_license_action'); ?>
                <button type="submit" name="ases_activate_license" class="button button-primary">Validate License</button>
            </p>
        </form>
        
        <?php
        $license_data = get_option('ases_license_data');
        if ($license_data && isset($license_data['expires'])):
            $expired = time() > intval($license_data['expires']);
            ?>
            <div class="notice <?= $expired ? 'notice-error' : 'notice-success' ?>">
                <p>
                    License Status: <?= $expired ? '❌ Expired' : '✅ Active' ?><br>
                    Bound Domain: <strong><?= esc_html($license_data['domain'] ?? '—') ?></strong><br>
                    Product: <strong><?= esc_html($license_data['product_id'] ?? '—') ?></strong><br>
                    Expires: <?= date('Y-m-d H:i', intval($license_data['expires'])) ?>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($license_data): ?>
            <h2>Debug Info</h2>
            <p>This information can be shared with support:</p>
            <textarea readonly style="width:100%; height:150px;"><?=
                esc_textarea(json_encode([
                    'email' => $license_data['email'] ?? '',
                    'product_id' => $license_data['product_id'] ?? '',
                    'domain' => $license_data['domain'] ?? '',
                    'license_key' => substr($license_data['license_key'] ?? '', 0, 4) . '****',
                    'expires' => date('c', intval($license_data['expires'] ?? 0)),
                ], JSON_PRETTY_PRINT))
            ?></textarea>
        <?php endif; ?>
        
        <hr>

        <h2>Send Test Email</h2>
        <form method="post">
            <input type="email" name="ases_test_email" placeholder="Email address" required>
            <button type="submit" class="button">Send Test Email</button>
        </form>

        <?php if (!ases_is_license_valid()) : ?>
            <div class="notice notice-warning">
                <p><strong>Note:</strong> Sending normal emails via Amazon SES requires a valid license.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

add_action('admin_init', 'ases_register_settings');
function ases_register_settings() {
    register_setting('ases_settings_group', 'ases_smtp_user');
    register_setting('ases_settings_group', 'ases_smtp_pass');
    register_setting('ases_settings_group', 'ases_license_key');
    register_setting('ases_settings_group', 'ases_license_email');

    add_settings_section('ases_main', '', null, 'amazon-ses-smtp');

    add_settings_field('smtp_user', 'SMTP Username', function () {
        $disabled = !ases_is_license_valid() ? 'disabled' : '';
        echo '<input type="text" name="ases_smtp_user" value="' . esc_attr(get_option('ases_smtp_user')) . "\" class=\"regular-text\" $disabled>";
    }, 'amazon-ses-smtp', 'ases_main');

    add_settings_field('smtp_pass', 'SMTP Password', function () {
        $disabled = !ases_is_license_valid() ? 'disabled' : '';
        echo '<input type="password" name="ases_smtp_pass" value="' . esc_attr(get_option('ases_smtp_pass')) . "\" class=\"regular-text\" $disabled>";
    }, 'amazon-ses-smtp', 'ases_main');

    add_settings_field('license_email', 'License Email', function () {
        echo '<input type="email" name="ases_license_email" value="' . esc_attr(get_option('ases_license_email')) . '" class="regular-text">';
    }, 'amazon-ses-smtp', 'ases_main');

    add_settings_field('license_key', 'License Key', function () {
        echo '<input type="text" name="ases_license_key" value="' . esc_attr(get_option('ases_license_key')) . '" class="regular-text">';
    }, 'amazon-ses-smtp', 'ases_main');
}

register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('ases_weekly_revalidate_license')) {
        wp_schedule_event(time(), 'weekly', 'ases_weekly_revalidate_license');
    }
});

register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('ases_weekly_revalidate_license');
});

add_action('ases_weekly_revalidate_license', 'ases_revalidate_license');

function ases_revalidate_license() {
    $email = get_option('ases_license_email');
    $key   = get_option('ases_license_key');

    if ($email && $key) {
        ases_validate_license($email, $key);
    }
}