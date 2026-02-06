=== Broodle Engage Connector ===
Contributors: broodle
Tags: woocommerce, whatsapp, notifications, sms, messaging
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 3.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send WooCommerce order notifications to customers via WhatsApp using Broodle WhatsApp API.

== Description ==

Broodle Engage Connector seamlessly integrates your WooCommerce store with WhatsApp messaging, allowing you to send automated order notifications directly to your customers' WhatsApp accounts.

**Key Features:**

* **Automated Order Notifications**: Send WhatsApp messages for various order statuses including order received, processing, shipped, delivered, completed, cancelled, and failed orders.
* **Template-Based Messaging**: Use pre-approved WhatsApp Business API templates for professional and compliant messaging.
* **Flexible Configuration**: Configure which notifications to send and customize templates for each order status.
* **Comprehensive Logging**: Track all notification attempts with detailed logs and error reporting.
* **Easy Setup**: Simple configuration with your Broodle WhatsApp API credentials.
* **Phone Number Validation**: Automatic phone number formatting and validation.
* **Retry Mechanism**: Automatic retry for failed notifications with configurable retry attempts and delays.

**Supported Order Statuses:**

* Order Received (Pending Payment)
* Order Processing
* Order Shipped (Custom Status)
* Order Delivered (Custom Status)
* Order Completed
* Order Cancelled
* Order Failed
* Order Refunded

**Requirements:**

* WooCommerce 5.0 or higher
* Broodle WhatsApp API account ([Register here](https://engage.broodle.one))
* Pre-approved WhatsApp Business API templates

**Compatibility:**

* ✅ WooCommerce High-Performance Order Storage (HPOS)
* ✅ WordPress Multisite
* ✅ WooCommerce Subscriptions
* ✅ All major WooCommerce extensions

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/broodle-engage-wp-connector` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to WooCommerce > Broodle Engage Connector to configure the plugin.
4. Enter your Broodle WhatsApp API credentials.
5. Configure your WhatsApp templates for each order status.
6. Enable the notifications you want to send.

== Configuration ==

**API Setup:**

1. Register for a Broodle WhatsApp API account at [https://engage.broodle.one](https://engage.broodle.one)
2. Obtain your API key and token from your Broodle dashboard
3. Enter these credentials in the plugin settings

**Template Configuration:**

1. Create WhatsApp Business API templates in your Broodle dashboard
2. Get approval for your templates from WhatsApp
3. Enter the template names in the plugin settings for each order status
4. Configure which notifications you want to enable

**Phone Number Settings:**

* Choose whether to use billing or shipping phone numbers
* Set your default country code
* The plugin will automatically format phone numbers for WhatsApp

== Frequently Asked Questions ==

= Do I need a WhatsApp Business API account? =

Yes, you need a Broodle WhatsApp API account to use this plugin. You can register at [https://engage.broodle.one](https://engage.broodle.one).

= Can I customize the message templates? =

Message templates must be created and approved through the WhatsApp Business API. You can create and manage your templates in your Broodle dashboard.

= What happens if a notification fails? =

The plugin includes automatic retry functionality. Failed notifications will be retried based on your configured retry settings. All attempts are logged for your review.

= Can I disable notifications for specific order statuses? =

Yes, you can enable or disable notifications for each order status individually in the plugin settings.

= Is customer phone number validation included? =

Yes, the plugin automatically validates and formats phone numbers before sending notifications.

== Screenshots ==

1. Plugin settings page - API configuration
2. Template configuration for order statuses
3. Notification logs and statistics
4. Order status notification settings

== Changelog ==

= 3.0.1 =
* Improved WordPress.org compliance (escaping, sanitization, enqueue versioning)
* Removed runtime base64_encode usage for menu icon
* Enhanced Third-Party Service disclosure in readme

= 3.0.0 =
* Redesigned Templates tab with expand/collapse status cards
* Added template preview with variable mapping
* Added custom notification statuses support
* Added dashboard widget with delivery statistics
* Redesigned Logs tab with stats overview and filtering
* Improved UI with brand colors and dashicons
* Added image header support for WhatsApp templates

= 2.0.0 =
* Added comprehensive notification logging with custom database table
* Added retry mechanism for failed notifications
* Added WooCommerce HPOS compatibility
* Added scheduled notifications support
* Improved phone number validation and formatting
* Added diagnostic tools and test notification feature

= 1.0.0 =
* Initial release
* Support for all major WooCommerce order statuses
* Template-based WhatsApp messaging
* Comprehensive logging and error handling
* Automatic retry mechanism
* Phone number validation and formatting

== Upgrade Notice ==

= 3.0.1 =
Improved WordPress.org compliance. Recommended update for all users.

= 3.0.0 =
Major UI redesign with new Templates and Logs interfaces. Dashboard widget added.

= 2.0.0 =
Added logging, retry mechanism, and HPOS compatibility.

= 1.0.0 =
Initial release of Broodle Engage Connector.

== Support ==

For support and documentation, please visit [https://broodle.host](https://broodle.host) or contact our support team.

== Privacy Policy ==

This plugin sends customer phone numbers and order information to the Broodle WhatsApp API service for the purpose of sending notifications. Please ensure you have appropriate consent from your customers and comply with applicable privacy laws.

== Third-Party Services ==

This plugin connects to the **Broodle WhatsApp API** — a third-party external service — in order to send WhatsApp messages to your customers. Data such as customer phone numbers, order IDs, and order details are transmitted to this service whenever a WooCommerce order notification is triggered.

* **Service Provider:** Broodle
* **API Endpoint:** https://engage.broodle.one
* **What data is sent:** Customer phone number, order information, and template variables configured by the store administrator.
* **When data is sent:** Each time an enabled WooCommerce order status change occurs (e.g., order placed, shipped, delivered, cancelled).
* **Service Website:** [https://broodle.host](https://broodle.host)
* **Terms of Service:** [https://broodle.host/terms](https://broodle.host/terms)
* **Privacy Policy:** [https://broodle.host/privacy](https://broodle.host/privacy)

By activating and using this plugin, you agree to the terms and conditions of the Broodle WhatsApp API service. Please ensure you have appropriate consent from your customers before enabling WhatsApp notifications.
