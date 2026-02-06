# Broodle Engage Connector

**Send WooCommerce order notifications to customers via WhatsApp using Broodle WhatsApp API.**

![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4)
![License](https://img.shields.io/badge/License-GPLv2-green)
![Version](https://img.shields.io/badge/Version-3.0.1-0E5ECE)

---

## Overview

Broodle Engage Connector seamlessly integrates your WooCommerce store with WhatsApp messaging, allowing you to send automated order notifications directly to your customers' WhatsApp accounts via the [Broodle WhatsApp API](https://broodle.host).

## Features

- **Automated Order Notifications** — Send WhatsApp messages for order received, processing, shipped, delivered, completed, cancelled, failed, and refunded statuses.
- **Template-Based Messaging** — Use pre-approved WhatsApp Business API templates for professional, compliant messaging.
- **Visual Template Configuration** — Template variable mapping with live preview.
- **Custom Notification Types** — Add custom order statuses or user event triggers beyond the built-in ones.
- **Dashboard Widget** — At-a-glance delivery statistics with success/failure breakdown.
- **Comprehensive Logging** — Track all notification attempts with detailed logs, filtering, and error reporting.
- **Retry Mechanism** — Automatic retry for failed notifications with configurable attempts and delays.
- **Phone Number Validation** — Automatic phone number formatting and country code handling.
- **HPOS Compatible** — Full support for WooCommerce High-Performance Order Storage.

## Requirements

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+
- Broodle WhatsApp API account — [Register here](https://wa.broodle.one)
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
2. Enter your **API Access Token** from your [Broodle dashboard](https://wa.broodle.one).
3. Enter your **Account ID** and **WhatsApp Inbox ID**.
4. Click **Test Connection** to verify.

### 2. Template Configuration

1. Go to the **Templates** tab.
2. Templates are fetched automatically from your Broodle Engage account.
3. For each order status:
   - Toggle the notification **on/off**.
   - Select a WhatsApp template from the dropdown.
   - Map template variables (e.g., `{{1}}` → Customer Name, `{{2}}` → Order ID).
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
│   ├── class-broodle-engage-api.php            # Broodle WhatsApp API client
│   ├── class-broodle-engage-notifications.php  # Order notification processing
│   ├── class-broodle-engage-settings.php       # Settings management
│   └── class-broodle-engage-logger.php         # Notification logging
├── assets/
│   ├── css/admin.css               # Admin styles
│   ├── js/admin.js                 # Admin scripts
│   └── images/broodle-logo.png     # Brand assets
└── languages/                      # Translation files
```

## Supported Order Statuses

| Status | Trigger |
|--------|---------|
| Order Received | New order placed (pending payment) |
| Order Processing | Payment confirmed, order being prepared |
| Order Shipped | Order dispatched (custom WC status) |
| Order Delivered | Order delivered (custom WC status) |
| Order Completed | Order fulfilled |
| Order Cancelled | Order cancelled |
| Order Failed | Payment failed |
| Order Refunded | Order refunded |

## Third-Party Service

This plugin connects to the **Broodle WhatsApp API** to send WhatsApp messages. Customer phone numbers, order IDs, and template variables are transmitted when order status changes occur.

- **API Endpoint:** `https://engage.broodle.one`
- **Service Website:** [broodle.host](https://broodle.host)
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

For support and documentation, visit [broodle.host](https://broodle.host) or open an issue in this repository.
