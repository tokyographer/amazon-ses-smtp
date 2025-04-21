=== Amazon SES SMTP for WordPress ===
Contributors: tokyographer
Tags: smtp, email, amazon ses, license, transactional email
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send WordPress emails through Amazon SES. Send test emails for free. Activate full functionality with a one-time license purchase.

== Description ==

Amazon SES SMTP for WordPress helps you route your emails reliably and securely using Amazon's Simple Email Service. 

**Features:**
* Free version allows sending test emails
* Full SES email functionality with valid license
* Domain binding on license activation
* Secure license validation and JWT token usage
* Weekly revalidation with your license server
* Debug mode for easy support requests

This plugin is built for plugin developers, businesses, and teams using Amazon SES to manage their transactional emails.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/amazon-ses-smtp`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → Amazon SES SMTP
4. Enter your license email and key, then validate
5. Once validated, enter your SMTP credentials and start sending!

== Frequently Asked Questions ==

= Can I use the plugin without a license? =
You can use it to send test emails, but sending WordPress emails via SES requires a valid license.

= Is the license a subscription? =
No. It’s a one-time, lifetime license.

= Is this plugin compatible with other SMTP plugins? =
We recommend disabling other SMTP plugins to avoid conflicts.

== Screenshots ==

1. Settings page for license and credentials
2. Test email sender
3. License status and debug info

== Changelog ==

= 1.0.0 =
* Initial release
* License validation and test email feature
* Amazon SES support with secure mail override

== Upgrade Notice ==

= 1.0.0 =
Initial stable release with SES SMTP support and license validation.