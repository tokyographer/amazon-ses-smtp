<?php

if (is_admin() && ases_is_license_valid()) {
    require_once ASES_PLUGIN_DIR . 'includes/ses-mailer.php';
}

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

        <?php settings_errors(); ?>
        <?php
        if (isset($_GET['email_sent'])) {
            echo "<div class='notice notice-success is-dismissible'><p>✅ Test email sent successfully.</p></div>";
        } elseif (isset($_GET['email_fail'])) {
            echo "<div class='notice notice-error is-dismissible'><p>❌ Failed to send test email. Check SES credentials.</p></div>";
        } elseif (isset($_GET['email_error'])) {
            echo "<div class='notice notice-error is-dismissible'><p>❌ Error sending email. " . esc_html($_GET['msg'] ?? '') . "</p></div>";
        }
        ?>

        <form method="post" action="options.php">
            <?php
            settings_fields('ases_settings_group');
            do_settings_sections('amazon-ses-smtp');
            submit_button();
            ?>
        </form>

        <hr>

        <h2>Send Test Email</h2>
        <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="ases_send_test_email">
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
    register_setting('ases_settings_group', 'ases_smtp_host');
    register_setting('ases_settings_group', 'ases_license_key');
    register_setting('ases_settings_group', 'ases_license_email');

    add_settings_section('ases_main', '', null, 'amazon-ses-smtp');

    add_settings_field('smtp_user', 'SMTP Username', function () {
        $disabled = !ases_is_license_valid() ? 'disabled' : '';
        echo '<input type="text" name="ases_smtp_user" value="' . esc_attr(get_option('ases_smtp_user')) . '" class="regular-text" ' . $disabled . '>';
    }, 'amazon-ses-smtp', 'ases_main');

    add_settings_field('smtp_pass', 'SMTP Password', function () {
        $disabled = !ases_is_license_valid() ? 'disabled' : '';
        echo '<input type="password" name="ases_smtp_pass" value="' . esc_attr(get_option('ases_smtp_pass')) . '" class="regular-text" ' . $disabled . '>';
    }, 'amazon-ses-smtp', 'ases_main');

    add_settings_field('smtp_host', 'Amazon SES Region Endpoint', function () {
        $disabled = !ases_is_license_valid() ? 'disabled' : '';
        $selected = get_option('ases_smtp_host');
        $regions = [
            'email-smtp.us-east-1.amazonaws.com' => 'US East (N. Virginia)',
            'email-smtp.us-west-2.amazonaws.com' => 'US West (Oregon)',
            'email-smtp.eu-west-1.amazonaws.com' => 'EU (Ireland)',
            'email-smtp.ap-south-1.amazonaws.com' => 'Asia Pacific (Mumbai)',
            'email-smtp.ap-northeast-1.amazonaws.com' => 'Asia Pacific (Tokyo)',
            'email-smtp.ap-northeast-2.amazonaws.com' => 'Asia Pacific (Seoul)',
            'email-smtp.ap-southeast-1.amazonaws.com' => 'Asia Pacific (Singapore)',
            'email-smtp.ap-southeast-2.amazonaws.com' => 'Asia Pacific (Sydney)',
            'email-smtp.eu-central-1.amazonaws.com' => 'EU (Frankfurt)',
            'email-smtp.sa-east-1.amazonaws.com' => 'South America (São Paulo)'
        ];
        echo '<select name="ases_smtp_host" class="regular-text" ' . $disabled . '>';
        foreach ($regions as $host => $label) {
            $is_selected = selected($selected, $host, false);
            echo "<option value='$host' $is_selected>$label ($host)</option>";
        }
        echo '</select>';
    }, 'amazon-ses-smtp', 'ases_main');

    add_settings_field('license_email', 'License Email', function () {
        echo '<input type="email" name="ases_license_email" value="' . esc_attr(get_option('ases_license_email')) . '" class="regular-text">';
    }, 'amazon-ses-smtp', 'ases_main');

    add_settings_field('license_key', 'License Key', function () {
        echo '<input type="text" name="ases_license_key" value="' . esc_attr(get_option('ases_license_key')) . '" class="regular-text">';
    }, 'amazon-ses-smtp', 'ases_main');
}

add_action('admin_post_ases_send_test_email', 'ases_send_test_email_handler');
function ases_send_test_email_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $to = sanitize_email($_POST['ases_test_email'] ?? '');
    if (empty($to)) {
        wp_redirect(admin_url('options-general.php?page=amazon-ses-smtp&email_error=1'));
        exit;
    }

    $user = get_option('ases_smtp_user');
    $pass = get_option('ases_smtp_pass');
    $host = get_option('ases_smtp_host');

    if (empty($user) || empty($pass) || empty($host)) {
        wp_redirect(admin_url('options-general.php?page=amazon-ses-smtp&email_fail=1'));
        exit;
    }

    add_action('phpmailer_init', function ($phpmailer) use ($user, $pass, $host) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = $host;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = 587;
        $phpmailer->Username   = $user;
        $phpmailer->Password   = $pass;
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->From       = $user;
        $phpmailer->FromName   = 'Amazon SES SMTP Plugin';
    });

    try {
        $sent = wp_mail($to, 'Amazon SES Test Email', 'This is a test email sent via Amazon SES SMTP plugin.');
        if ($sent) {
            wp_redirect(admin_url('options-general.php?page=amazon-ses-smtp&email_sent=1'));
        } else {
            wp_redirect(admin_url('options-general.php?page=amazon-ses-smtp&email_fail=1'));
        }
    } catch (Exception $e) {
        wp_redirect(admin_url('options-general.php?page=amazon-ses-smtp&email_error=1&msg=' . urlencode($e->getMessage())));
    }
    exit;
}
