<?php
/**
 * Plugin Name: Amazon SES SMTP for WordPress
 * Plugin URI: https://mediared.es
 * Description: Send emails from your WordPress site using Amazon SES. The free version allows sending test emails only. A license is required to activate full functionality.
 * Version: 1.0.0
 * Author: Mediared.es
 * Author URI: https://mediared.es
 * Text Domain: amazon-ses-smtp
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Constants
define('ASES_PLUGIN_VERSION', '1.0.0');
define('ASES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ASES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include files
require_once ASES_PLUGIN_DIR . 'includes/license-check.php';
require_once ASES_PLUGIN_DIR . 'admin/settings-page.php';
require_once ASES_PLUGIN_DIR . 'includes/license-validate.php';

// Load plugin textdomain
add_action('plugins_loaded', function () {
    load_plugin_textdomain('amazon-ses-smtp', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Plugin init
add_action('init', 'ases_init');
if (is_admin() && isset($_POST['ases_test_email'])) {
    $to = sanitize_email($_POST['ases_test_email']);
    $sent = wp_mail($to, 'SES Test Email', 'This is a test email sent using Amazon SES SMTP plugin.');
    add_action('admin_notices', function () use ($sent, $to) {
        if ($sent) {
            echo "<div class='notice notice-success is-dismissible'><p>Test email sent to $to.</p></div>";
        } else {
            echo "<div class='notice notice-error is-dismissible'><p>Failed to send test email.</p></div>";
        }
    });
}
function ases_init() {
    if (is_admin()) {
        // Show settings page
        add_action('admin_menu', 'ases_add_settings_page');
    } else {
        // Only use SES if license is valid
        if (ases_is_license_valid()) {
            require_once ASES_PLUGIN_DIR . 'includes/ses-mailer.php';
        }
    }
}