# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2025-02-05

### 🚀 MAJOR UPDATE - Chatwoot Integration
- ✅ **NEW**: Complete migration from wa.broodle.one to Chatwoot API (engage.broodle.one)
- ✅ **NEW**: Full Chatwoot API integration for sending WhatsApp template messages
- ✅ **NEW**: Support for Chatwoot's enhanced template format with processed_params
- ✅ **NEW**: Automatic contact creation and management via Chatwoot API
- ✅ **NEW**: Contact inbox association for WhatsApp channel

### New Settings
- ✅ **NEW**: Chatwoot Account ID configuration
- ✅ **NEW**: WhatsApp Inbox ID configuration  
- ✅ **NEW**: Template Language selector (en, en_US, es, pt_BR, fr, de, etc.)
- ✅ **NEW**: Template Category selector (UTILITY, MARKETING, AUTHENTICATION)

### API Changes
- ✅ **CHANGED**: API base URL from `wa.broodle.one` to `engage.broodle.one`
- ✅ **CHANGED**: Authentication from Bearer token to `api_access_token` header
- ✅ **CHANGED**: Message sending flow: Contact → Contact Inbox → Conversation with Template
- ✅ **CHANGED**: Template parameters now use Chatwoot's `processed_params` format

### Template Format
- ✅ **ENHANCED**: Support for body parameters with numbered keys (1, 2, 3...)
- ✅ **ENHANCED**: Support for header media (image, video, document)
- ✅ **ENHANCED**: Automatic media type detection from URL

### Backward Compatibility
- ✅ **MAINTAINED**: All existing notification types still supported
- ✅ **MAINTAINED**: Template variable configuration unchanged
- ✅ **MAINTAINED**: Logging and retry mechanisms preserved

## [2.9.8] - 2025-07-15

### 🛡️ CRITICAL STABILITY IMPROVEMENTS
- ✅ **STABILITY**: Comprehensive exception handling to prevent breaking WooCommerce/WordPress
- ✅ **STABILITY**: Added safety checks for all WooCommerce and WordPress core functions
- ✅ **STABILITY**: Payment gateway protection (Razorpay, etc.) - no interference during payment processing
- ✅ **STABILITY**: Database operation safety with try-catch blocks
- ✅ **STABILITY**: Hook priority optimization to prevent conflicts with other plugins
- ✅ **STABILITY**: Ultimate throwable catching for PHP 7+ compatibility

### 🔒 WordPress/WooCommerce Protection
- ✅ **PROTECTED**: Order processing never interrupted by plugin errors
- ✅ **PROTECTED**: Payment gateway operations completely isolated
- ✅ **PROTECTED**: Database failures handled gracefully without breaking functionality
- ✅ **PROTECTED**: All plugin operations wrapped in safety checks

### 🚀 Production Ready Features
- ✅ **RELIABLE**: Plugin will never cause white screen of death
- ✅ **RELIABLE**: All exceptions caught and handled silently
- ✅ **RELIABLE**: Graceful degradation when WooCommerce functions unavailable
- ✅ **RELIABLE**: Safe initialization with comprehensive availability checks

## [2.9.7] - 2025-07-15

### Enhanced Placeholder Handling
- ✅ **IMPROVED**: Use "---" placeholder for missing amount and coupon data instead of empty values
- ✅ **IMPROVED**: Consistent "---" placeholder across all template variables when no data is available
- ✅ **IMPROVED**: Better handling of zero-value orders and empty coupon codes
- ✅ **IMPROVED**: Enhanced test methods to use "---" placeholders

### User Experience
- ✅ **Better UX**: Customers now see "---" instead of blank spaces for missing data
- ✅ **Consistency**: All missing data fields show the same "---" placeholder
- ✅ **Clarity**: Clear indication when data is not available rather than confusing empty fields

## [2.9.6] - 2025-07-15

### CRITICAL FIX - Template Variables Issue
- ✅ **FIXED**: WhatsApp templates receiving default values instead of actual order data
- ✅ **FIXED**: Template variable array index mismatches causing wrong data in wrong positions
- ✅ **FIXED**: Empty variables being skipped, causing template position shifts
- ✅ **FIXED**: Coupon code insertion logic causing variable array corruption

### Template Variable Improvements
- ✅ **Enhanced**: Template variables now maintain exact positions to prevent default value fallback
- ✅ **Enhanced**: Added fallback values for critical fields (customer name, order total, etc.)
- ✅ **Enhanced**: Improved variable cleaning to remove empty trailing elements
- ✅ **Enhanced**: Better validation of template variables before API submission

### Logging Enhancements
- ✅ **Added**: Detailed template variable logging for debugging
- ✅ **Added**: Variable count and type information in logs
- ✅ **Added**: Selected variables configuration logging

## [2.9.5] - 2025-07-15

### Fixed
- Fixed cancelled and failed order notifications not being sent
- Improved HPOS (High-Performance Order Storage) compatibility for order status changes
- Streamlined hook system to use the most reliable WooCommerce hooks

### Changed
- Removed debug logging throughout the plugin (only logs page logging remains)
- Simplified notification hook system for better reliability
- Improved fallback template handling for cancelled and failed orders

### Removed
- All debug error_log statements (except database error logging in logger class)
- Unused debug methods and test functions
- Redundant hook systems that could cause conflicts

## [2.9.4] - 2025-01-15

### Fixed
- **CRITICAL**: Fixed issue where orders with "Shipped" status were hidden from WooCommerce orders list
- Disabled problematic `cleanup_duplicate_statuses()` function that was removing order statuses
- Removed `save_post` hook that could cause conflicts with order saving
- Improved order saving to prevent infinite loops

### Enhanced
- **Wide Plugin Compatibility**: Added support for popular shipping plugin statuses:
  - ParcelPanel: `wc-shipped`, `wc-partial-shipped`, `wc-delivered`
  - Advanced Shipment Tracking: `wc-ast-shipped`, `wc-ast-delivered`, `wc-ast-out-for-delivery`
  - ShipStation: `wc-shipstation-shipped`, `wc-ss-shipped`
  - Common statuses: `wc-out-for-delivery`, `wc-dispatched`, `wc-in-transit`, `wc-ready-for-pickup`
- Improved status mapping with both `wc-` prefixed and clean status names
- Added comprehensive order validation to prevent conflicts with other plugins
- Updated hook priorities to 9999 to ensure compatibility with other plugins
- Enhanced admin interface to show status keys alongside display names
- Added filter `broodle_wa_should_process_order` for third-party plugin integration

### Changed
- Default status mapping now uses `shipped` and `delivered` instead of `completed`
- All hooks now use very low priority (9999) to avoid conflicts
- Improved error handling and logging

## [1.0.0] - 2025-01-12

### Added
- Initial release of Broodle Engage Connector
- WhatsApp notification integration with WooCommerce
- Support for all major order statuses:
  - Order Received (Pending Payment)
  - Order Processing
  - Order Shipped (Custom Status)
  - Order Delivered (Custom Status)
  - Order Completed
  - Order Cancelled
  - Order Failed
  - Order Refunded
- Template-based messaging system
- Comprehensive admin interface with:
  - API configuration settings
  - Template management
  - Notification logs and statistics
  - Help and documentation
- Automatic retry mechanism for failed notifications
- Phone number validation and formatting
- Comprehensive logging system
- Security features:
  - Nonce verification
  - Capability checks
  - Input sanitization
  - Secure API key storage
- Internationalization support
- WordPress coding standards compliance
- WooCommerce compatibility testing

### Security
- Implemented proper nonce verification for all admin forms
- Added capability checks for admin access
- Sanitized all user inputs
- Secure storage of API credentials

### Performance
- Optimized database queries
- Implemented proper caching mechanisms
- Efficient logging system with automatic cleanup

### Documentation
- Comprehensive readme.txt file
- Inline code documentation
- Help section in admin interface
- Installation and configuration guide
