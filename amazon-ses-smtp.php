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

define('ASES_PLUGIN_VERSION', '1.0.0');
define('ASES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ASES_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('plugins_loaded', function () {
    load_plugin_textdomain('amazon-ses-smtp', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

require_once ASES_PLUGIN_DIR . 'includes/license-check.php';
require_once ASES_PLUGIN_DIR . 'admin/settings-page.php';
require_once ASES_PLUGIN_DIR . 'includes/license-validate.php';

if (is_admin()) {
    add_action('admin_menu', 'ases_add_settings_page');

    if (ases_is_license_valid()) {
        require_once ASES_PLUGIN_DIR . 'includes/ses-mailer.php';
    }
}