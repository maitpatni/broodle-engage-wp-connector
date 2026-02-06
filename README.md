# Broodle Engage Connector

**Send WooCommerce order notifications to customers via WhatsApp using Broodle Engage.**

![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4)
![License](https://img.shields.io/badge/License-GPLv2-green)
![Version](https://img.shields.io/badge/Version-3.0.1-0E5ECE)

---

## Overview

Broodle Engage Connector seamlessly integrates your WooCommerce store with WhatsApp messaging, allowing you to send automated order notifications directly to your customers' WhatsApp accounts via [Broodle Engage](https://broodle.host/engage).

## Features

### Automated Order Notifications

Send WhatsApp messages automatically when order status changes. 8 built-in notification types supported out of the box:

| Status | Trigger |
|--------|---------|
| Order Received | New order placed (pending payment) |
| Order Processing | Payment confirmed, order being prepared |
| Order Shipped | Order dispatched |
| Order Delivered | Order delivered to customer |
| Order Completed | Order fulfilled |
| Order Cancelled | Order cancelled by admin or customer |
| Order Failed | Payment failed |
| Order Refunded | Order refunded |

Duplicate prevention ensures the same notification is never sent twice for the same order + template combination.

### Custom Notification Types

Go beyond the 8 built-in types. Add custom notifications via a simple modal:

- **Custom Order Status Events** — Map any WooCommerce order status (including statuses from shipping plugins) to a WhatsApp notification.
- **User Events:**
  - **User Registration** — Send a WhatsApp welcome message when a new customer creates an account.
  - **Password Reset Request** — Notify customers on password reset.
  - **User Login** — Trigger notifications on user login.

Each custom notification type gets the same full configuration — template selection, variable mapping, image upload, and enable/disable toggle.

### Third-Party Shipping Plugin Support

Automatically detects and supports order statuses from popular shipping plugins:

- **ParcelPanel** — `shipped`, `partial-shipped`, `delivered`
- **Advanced Shipment Tracking (AST)** — `ast-shipped`, `ast-delivered`, `ast-out-for-delivery`, `ast-in-transit`, `ast-return-to-sender`
- **ShipStation** — `shipstation-shipped`, `ss-shipped`
- **Generic Statuses** — `out-for-delivery`, `dispatched`, `in-transit`, `ready-for-pickup`, `picked-up`, `delivered-to-customer`

### Auto-Fetching of WhatsApp Templates

Templates are fetched directly from your Broodle Engage inbox — no manual entry required.

- Automatically syncs all **approved** WhatsApp templates from your connected inbox.
- Parses template components: HEADER, BODY, FOOTER, and BUTTONS.
- Extracts variable placeholders (`{{1}}`, `{{2}}`, etc.) from template body and header.
- Connection status indicator with animated pulse showing sync state.
- Template count badge and one-click **Refresh** button to re-sync.

### Visual Template Configuration

A professional template configuration UI with live preview:

- **Template Preview** — See the exact message body, header, footer, and buttons as they'll appear.
- **Variable Highlighting** — Template variables (`{{1}}`, `{{2}}`) highlighted with badges.
- **Language & Category Badges** — Each template shows its WhatsApp language and category.
- **Image Header Detection** — Automatically shows image upload when template has an image header.
- **Button Preview** — URL and phone number buttons displayed with variable indicators.

### Template Variable Mapping

Map each template placeholder to real order data with 25 available options:

| Variable | Description |
|----------|-------------|
| `customer_name` | Full customer name |
| `customer_first_name` | Customer first name |
| `customer_last_name` | Customer last name |
| `customer_email` | Customer email address |
| `order_id` | WordPress order ID |
| `order_number` | WooCommerce order number |
| `order_total` | Formatted order total (e.g., ₹999.00) |
| `order_total_raw` | Raw order total number |
| `order_date` | Order date |
| `order_status` | Current order status |
| `product_names` | Comma-separated product list |
| `product_count` | Number of items in order |
| `shipping_address` | Full shipping address |
| `billing_address` | Full billing address |
| `payment_method` | Payment method used |
| `shipping_method` | Shipping method used |
| `tracking_url` | Shipment tracking URL |
| `tracking_number` | Shipment tracking number |
| `coupon_code` | Applied coupon code |
| `cart_url` | Store cart URL |
| `shop_url` | Store shop URL |
| `my_account_url` | Customer account URL |
| `site_name` | Website name |
| `custom_text` | Free-form custom text or coupon code |

### Per-Template Language Support

- Global default template language setting (e.g., `en_US`, `en`, `hi`, `es`).
- Per-template language override — uses the actual language from WhatsApp template metadata.
- Supports: English, English US, English UK, Spanish, Portuguese (BR), French, German, Italian, Arabic, Hindi, Indonesian.

### Dashboard Widget & Reporting

A branded dashboard widget appears on your WordPress admin home:

- **Connection Status** — Green/yellow/red indicator showing API health.
- **Message Statistics** — Quick stats for Today, Last 7 Days, and Last 30 Days (successful messages).
- **Error Alerts** — Banner warning when failed messages detected in the last 24 hours.
- **Quick Links** — Jump to Logs, Settings, or open the [Broodle Engage Dashboard](https://engage.broodle.one).

### Notification Logs & Analytics

Full logging system on the **Logs** tab:

- **5-Stat Summary Cards** (30-day window): Total, Successful, Failed, Pending, Scheduled.
- **Scheduled Notifications Panel** — View queued delayed notifications with countdown timers and overdue indicators.
- **Notification History Table** — Columns: Date, Order, Phone, Template, Status, Details.
- **Status Badges** — Color-coded: Success (blue), Error (red), Pending (yellow), Retry (blue), Scheduled (purple).
- **Expandable Detail Cards** — Click any log to see:
  - Sent message content preview
  - Template variables grid (variable name + resolved value)
  - Full API response (JSON, pretty-printed)
- **Pagination** for large log histories.
- **Auto-Cleanup** — Configurable log retention (1–365 days, default 30).

### Retry Mechanism & Delayed Notifications

- **Auto-Retry on Failure** — Configurable retry attempts (0–10, default 3) with configurable delay (60–3600 seconds, default 5 minutes).
- **Delayed Notifications** — Set a delay (in minutes) per notification type. Notification is scheduled via WordPress Cron and sent after the delay.
- **Fallback Cron Check** — Background process checks for overdue scheduled notifications every 5 minutes as a safety net.

### Phone Number Handling

- **Configurable Phone Field** — Use billing phone, shipping phone, or any custom order meta field.
- **Fallback Logic** — If shipping phone is empty, falls back to billing phone automatically.
- **Country Code** — Configurable default country code (e.g., `+91`, `+1`).
- **Auto-Formatting** — Strips non-digit characters, prepends country code if missing.
- **Validation** — Requires 10–15 digits for valid WhatsApp delivery.

### Image & Media Support

- **Per-notification header image** — Upload via WordPress Media Library.
- **Thumbnail preview** with select/remove controls.
- **Auto-detection** of media type from URL:
  - Images: jpg, jpeg, png, gif, webp
  - Videos: mp4, avi, mov, webm
  - Documents: pdf, doc, etc.
- Only shown when the selected template has an IMAGE header component.

### API Connection & Diagnostics

- **3-Step Connection Test:**
  1. Validates API token via profile endpoint.
  2. Verifies access to the specified Account ID.
  3. Confirms inbox exists and returns inbox name.
- **Quick Test Message** — Send a `hello_world` template to any phone number directly from settings.
- **Status Diagnostic Page** — Activated via `?wa_diagnostic` URL parameter, lists all registered WooCommerce statuses with icons.

### Developer-Friendly

Hooks and filters for extending the plugin:

- `broodle_engage_api_request_args` — Customize HTTP request arguments.
- `broodle_engage_template_variables` — Modify template variables before sending.
- `broodle_engage_should_process_order` — Control whether an order should trigger notifications.
- `broodle_engage_order_status_options` — Extend available order status options.
- `broodle_engage_phone_field_options` — Add custom phone field options.
- `broodle_engage_notification_sent` — Action fired after successful notification.

### Stability & Safety

- All notification logic wrapped in `try/catch` blocks — never breaks WooCommerce order processing.
- Skips `auto-draft`, `draft`, and `trash` orders.
- Skips processing during payment gateway AJAX requests (Razorpay, etc.).
- Full order object validation before any notification attempt.
- Graceful fallback from delayed to immediate sending if scheduling fails.

### HPOS Compatible

Full support for WooCommerce High-Performance Order Storage:

- Detects HPOS vs legacy post-based orders automatically.
- Uses `woocommerce_order_status_changed` as primary hook.
- Legacy `transition_post_status` fallback for non-HPOS installations.

---

## Requirements

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+
- [Broodle Engage](https://broodle.host/engage) account with WhatsApp inbox configured
- Pre-approved WhatsApp Business API templates

## Installation

### From WordPress Admin

1. Download the latest release ZIP from this repository.
2. Go to **Plugins → Add New → Upload Plugin** in your WordPress admin.
3. Upload the ZIP file and click **Install Now**.
4. Activate the plugin.

### Manual Installation

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/maitpatni/broodle-engage-wp-connector.git broodle-engage-connector
```

Then activate from **Plugins** in WordPress admin.

## Configuration

### 1. API Setup

1. Navigate to **WooCommerce → Broodle Engage** in your WordPress admin.
2. Log in to your [Broodle Engage Dashboard](https://engage.broodle.one) and get your API Access Token.
3. Enter your **API Access Token**, **Account ID**, and **WhatsApp Inbox ID**.
4. Click **Test Connection** to verify all three checks pass.

### 2. Template Configuration

1. Go to the **Templates** tab.
2. Templates are fetched automatically from your Broodle Engage inbox.
3. For each notification type:
   - Toggle the notification **on/off**.
   - Select a WhatsApp template from the dropdown.
   - Map template variables to order data.
   - Optionally attach a header image.
4. Click **Save Configuration**.

### 3. Phone Number Settings

- Choose between **billing** or **shipping** phone number.
- Set your default **country code** (e.g., `+91`).
- The plugin automatically formats numbers for WhatsApp delivery.

## Plugin Structure

```
broodle-engage-connector/
├── broodle-engage-connector.php    # Main plugin file
├── uninstall.php                   # Clean uninstall handler
├── readme.txt                      # WordPress.org readme
├── CHANGELOG.md                    # Version history
├── includes/
│   ├── class-broodle-engage-admin.php          # Admin UI & AJAX handlers
│   ├── class-broodle-engage-api.php            # Broodle Engage API client
│   ├── class-broodle-engage-notifications.php  # Order notification processing
│   ├── class-broodle-engage-settings.php       # Settings management
│   └── class-broodle-engage-logger.php         # Notification logging
├── assets/
│   ├── css/admin.css               # Admin styles
│   ├── js/admin.js                 # Admin scripts
│   └── images/broodle-logo.png     # Brand assets
└── languages/                      # Translation files
```

## Third-Party Service

This plugin connects to the **Broodle Engage API** to send WhatsApp messages. Customer phone numbers, order IDs, and template variables are transmitted when order status changes occur.

- **Service:** [Broodle Engage](https://broodle.host/engage)
- **Dashboard:** [engage.broodle.one](https://engage.broodle.one)
- **API Endpoint:** `https://engage.broodle.one`
- **Terms of Service:** [broodle.host/terms](https://broodle.host/terms)
- **Privacy Policy:** [broodle.host/privacy](https://broodle.host/privacy)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for full version history.

### 3.0.1
- Improved WordPress.org compliance (escaping, sanitization, enqueue versioning)
- Fixed template config persistence across settings saves
- Per-template language support for WhatsApp API

### 3.0.0
- Redesigned Templates tab with expand/collapse status cards
- Template preview with visual variable mapping
- Custom notification statuses support
- Dashboard widget with delivery statistics
- Redesigned Logs tab with stats and filtering

## License

This plugin is licensed under the [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html) or later.

## Support

For support and documentation, visit [Broodle Engage](https://broodle.host/engage) or open an issue in this repository.
