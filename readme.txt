=== Broodle Engage Connector ===
Contributors: broodle, maitpatni
Tags: woocommerce, whatsapp, notifications, order notifications, whatsapp business
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send automated WooCommerce order notifications to customers via WhatsApp using Broodle Engage API powered by Chatwoot.

== Description ==

**Broodle Engage Connector** seamlessly integrates your WooCommerce store with WhatsApp Business API, allowing you to send automated order notifications directly to your customers' WhatsApp.

Built on top of Chatwoot's powerful messaging infrastructure, this plugin lets you keep customers informed about their orders in real time — from order placement to delivery.

= Why Broodle Engage? =

* **No coding required** — Set up in minutes with a simple API key
* **WhatsApp Business API compliant** — Uses approved message templates
* **Conversations stay organized** — Reuses existing Chatwoot conversations instead of creating duplicates
* **Built-in analytics** — UTM tracking on all URLs for attribution

= Key Features =

* **Automated Order Notifications** — Send WhatsApp messages for 8 order statuses: received, processing, shipped, delivered, completed, cancelled, failed, and refunded
* **Template-Based Messaging** — Use pre-approved WhatsApp Business API templates with dynamic variables (customer name, order ID, total, product URLs, etc.)
* **Smart Conversation Reuse** — Automatically finds and reuses existing Chatwoot conversations for the same customer, keeping communication organized
* **Product Featured Image Support** — Optionally include product images in template headers
* **Coupon Code Integration** — Attach coupon codes to failed/cancelled order notifications to recover sales
* **Delayed Notifications** — Schedule notifications with configurable delays
* **Comprehensive Logging** — Track all notification attempts with detailed logs, API responses, and error reporting
* **Dashboard Widget** — At-a-glance delivery statistics (today, 7 days, 30 days) right on your WordPress dashboard
* **UTM Tracking** — All URLs include `utm_source=engage&utm_medium=whatsapp` for analytics
* **Auto-Updates from GitHub** — Receive update notifications and one-click updates directly in WordPress
* **Phone Number Validation** — Automatic formatting with configurable country code
* **Retry Mechanism** — Automatic retry for failed notifications with configurable attempts and delays
* **HPOS Compatible** — Fully compatible with WooCommerce High-Performance Order Storage

= Supported Order Statuses =

* Order Received (Pending Payment)
* Order Processing
* Order Shipped (Custom Status)
* Order Delivered (Custom Status)
* Order Completed
* Order Cancelled
* Order Failed
* Order Refunded

= Template Variables =

Map dynamic data to your WhatsApp templates:

* `first_name`, `last_name`, `full_name` — Customer name
* `order_id` — Order number
* `order_total` — Order total with currency
* `order_items` — List of ordered product names
* `product_url`, `cart_url`, `shop_url` — Store links
* `my_account_url`, `tracking_url`, `payment_url` — Customer action links
* `coupon_code` — Coupon for recovery campaigns

= Requirements =

* WordPress 5.0+
* WooCommerce 5.0+
* PHP 7.4+
* A Broodle Engage account ([Sign up here](https://broodle.host/engage))
* Pre-approved WhatsApp Business API templates

= Compatibility =

* ✅ WooCommerce High-Performance Order Storage (HPOS)
* ✅ WordPress Multisite
* ✅ WooCommerce Subscriptions
* ✅ All major WooCommerce shipping and payment extensions

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/broodle-engage-connector` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Broodle Engage** in the WordPress admin sidebar.
4. Enter your API Access Token, Account ID, and WhatsApp Inbox ID from your [Broodle Engage dashboard](https://engage.broodle.one).
5. Go to the **Templates** tab and configure a WhatsApp template for each order status.
6. Map template variables (e.g., customer name, order ID, order total) to each template.
7. Enable the notifications you want to send.
8. Use the **Test Connection** button to verify your API credentials.

== Configuration ==

= API Setup =

1. Sign up for a Broodle Engage account at [https://broodle.host/engage](https://broodle.host/engage)
2. Log in to your dashboard at [https://engage.broodle.one](https://engage.broodle.one)
3. Create a WhatsApp inbox and note down your **Account ID** and **Inbox ID**
4. Generate an **API Access Token** from Settings → Account
5. Enter these credentials in the plugin settings

= Template Configuration =

1. Create WhatsApp Business API message templates in your Broodle Engage dashboard
2. Wait for template approval from Meta/WhatsApp
3. In the plugin's **Templates** tab, select the approved template for each order status
4. Map variables (body, header, buttons) to your WooCommerce order data
5. Optionally enable product featured image for template headers

= Phone Number Settings =

* Choose between billing or shipping phone number
* Set your default country code (e.g., +91 for India)
* The plugin automatically formats and validates phone numbers

== Frequently Asked Questions ==

= Do I need a WhatsApp Business API account? =

Yes, you need a Broodle Engage account which provides WhatsApp Business API access. Sign up at [https://broodle.host/engage](https://broodle.host/engage).

= How do I create message templates? =

Log in to your Broodle Engage dashboard at [https://engage.broodle.one](https://engage.broodle.one), navigate to Settings → Integrations → WhatsApp, and create templates there. Templates must be approved by Meta before they can be used.

= Can I customize the message content? =

Message templates are created and approved through the WhatsApp Business API. You can use dynamic variables like customer name, order ID, and order total that are automatically populated from your WooCommerce orders.

= What happens if a notification fails? =

The plugin includes automatic retry functionality. Failed notifications will be retried based on your configured retry settings (default: 3 attempts with 5-minute delay). All attempts are logged in the **Logs** tab.

= Can I disable notifications for specific order statuses? =

Yes, you can enable or disable notifications for each order status individually in the Templates tab.

= Does this create a new conversation for every order? =

No. The plugin intelligently reuses existing Chatwoot conversations for the same customer and inbox, keeping all communication in one thread.

= Does it work with custom order statuses? =

Yes, the plugin supports custom order statuses like Shipped and Delivered out of the box, with configurable status mapping.

= How are URLs tracked? =

All URLs sent in WhatsApp messages (product URLs, cart URLs, payment URLs, etc.) automatically include UTM parameters (`utm_source=engage&utm_medium=whatsapp`) for Google Analytics tracking.

= How do plugin updates work? =

The plugin checks GitHub for new releases and shows update notifications in your WordPress admin. You can update with one click, just like any WordPress.org plugin.

== Screenshots ==

1. **Settings Page** — Configure API credentials, phone number settings, and template language
2. **Templates Tab** — Assign WhatsApp templates to order statuses with variable mapping
3. **Template Preview** — See template content with image headers and button variables
4. **Logs Tab** — View notification history with status, timestamps, and API responses
5. **Dashboard Widget** — At-a-glance delivery statistics on WordPress dashboard
6. **Help Tab** — Built-in documentation with variable reference and troubleshooting

== Changelog ==

= 3.1.1 =
* Fixed log timezone display — times now correctly show in WordPress site timezone
* Moved Sign Up and Login links after Deactivate on plugins page

= 3.1.0 =
* Added GitHub-based auto-updater for one-click updates from WordPress admin
* Added "Sign Up for Broodle Engage" and "Login to Dashboard" links on plugins page
* Renamed plugin folder to match GitHub repository name
* Updated dashboard widget: forced white text on header, fixed vertical alignment
* Fixed dashboard heading margin
* Bumped version to 3.1.0

= 3.0.1 =
* Added smart conversation reuse — no more duplicate Chatwoot conversations
* Added UTM tracking parameters to all URLs (`utm_source=engage&utm_medium=whatsapp`)
* Added product featured image checkbox for template headers
* Added product URL variable for template mapping
* Fixed template config not persisting after save
* Fixed template message body display in Chatwoot inbox
* Fixed image headers and button params in Chatwoot messages (error #132012)
* Fixed image_id not persisting across multiple templates
* Redesigned help tab with comprehensive documentation
* Redesigned settings header with blue gradient style
* Redesigned dashboard widget with translucent stat cards
* Fixed critical bug: undefined method `get_phone_number()` → `get_customer_phone()`
* Fixed critical bug: `send_notification_safe()` visibility (private → public)
* Updated API URL to production `https://engage.broodle.one`
* Replaced deprecated `date()` with `gmdate()`
* Improved WordPress.org compliance (escaping, sanitization, enqueue versioning)

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

= 3.1.1 =
Timezone fix for logs display. Recommended update for all users.

= 3.1.0 =
Added auto-updater, plugin action links, and UI improvements.

= 3.0.1 =
Critical bug fixes, conversation reuse, UTM tracking, and UI redesign. Strongly recommended.

= 3.0.0 =
Major UI redesign with new Templates and Logs interfaces. Dashboard widget added.

== Support ==

For support and documentation:

* **Plugin Help Tab** — Built-in documentation available in the Help tab within the plugin
* **Website:** [https://broodle.host](https://broodle.host)
* **Dashboard:** [https://engage.broodle.one](https://engage.broodle.one)
* **GitHub:** [https://github.com/maitpatni/broodle-engage-connector](https://github.com/maitpatni/broodle-engage-connector)

== Privacy Policy ==

This plugin sends customer phone numbers and order information to the Broodle Engage API service for the purpose of sending WhatsApp notifications. Please ensure you have appropriate consent from your customers and comply with applicable privacy laws (GDPR, CCPA, etc.).

== Third-Party Services ==

This plugin connects to the **Broodle Engage API** (powered by Chatwoot) — a third-party external service — in order to send WhatsApp messages to your customers.

= What data is sent =

* Customer phone number
* Customer name
* Order ID, order total, and order item details
* Template variables configured by the store administrator

= When data is sent =

* Each time an enabled WooCommerce order status change occurs (e.g., order placed, shipped, delivered, cancelled)
* When the store administrator sends a test message
* When the plugin checks API connection

= Service Details =

* **Service Provider:** Broodle
* **API Endpoint:** [https://engage.broodle.one](https://engage.broodle.one)
* **Service Website:** [https://broodle.host](https://broodle.host)
* **Terms of Service:** [https://broodle.host/terms](https://broodle.host/terms)
* **Privacy Policy:** [https://broodle.host/privacy](https://broodle.host/privacy)

By activating and using this plugin, you agree to the terms and conditions of the Broodle Engage API service. Please ensure you have appropriate consent from your customers before enabling WhatsApp notifications.
