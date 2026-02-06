/**
 * Admin JavaScript for Broodle WA Connector
 */

(function($) {
    'use strict';

    var BroodleWAAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initAccordions();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;

            // Test API connection
            $(document).on('click', '#test-api-connection', function(e) {
                self.testApiConnection.call(this, e);
            });

            // Quick test message
            $(document).on('click', '#quick-test-message', function(e) {
                self.quickTestMessage.call(this, e);
            });

            // Toggle API key visibility - handled by inline onclick

            // Send test message
            $(document).on('click', '.send-test-message', function(e) {
                self.sendTestMessage.call(this, e);
            });

            // Form validation
            $('form').on('submit', function(e) {
                return self.validateForm.call(this, e);
            });

            // Auto-save settings
            $('.auto-save').on('change', function(e) {
                self.autoSaveSettings.call(this, e);
            });

            // Variables accordion toggle
            $(document).on('click', '.variables-accordion-toggle', function(e) {
                self.toggleVariablesAccordion.call(this, e);
            });
        },

        /**
         * Initialize accordions
         */
        initAccordions: function() {
            // Set initial state for variables sections (collapsed by default)
            $('.variables-accordion-content').hide();
            $('.variables-accordion-toggle').removeClass('active');
        },

        /**
         * Toggle variables accordion
         */
        toggleVariablesAccordion: function(e) {
            e.preventDefault();

            var $toggle = $(this);
            var status = $toggle.data('status');
            var $content = $('#variables-content-' + status);
            var isActive = $toggle.hasClass('active');

            if (isActive) {
                // Close accordion
                $toggle.removeClass('active');
                $content.slideUp(300);
            } else {
                // Open accordion
                $toggle.addClass('active');
                $content.slideDown(300);
            }
        },

        /**
         * Test API connection
         */
        testApiConnection: function(e) {
            e.preventDefault();

            var $button = $(this);
            var $result = $('#api-test-result');
            var apiKey = $('#api_key').val().trim();

            if (!apiKey) {
                BroodleWAAdmin.showResult($result, 'error', 'Please enter API key.');
                return;
            }

            // Show loading state
            $button.addClass('loading').prop('disabled', true);
            BroodleWAAdmin.showResult($result, 'loading', broodle_engage_admin.strings.testing_api);

            // Make AJAX request
            $.ajax({
                url: broodle_engage_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'broodle_engage_test_api',
                    nonce: broodle_engage_admin.nonce,
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        let message = response.data;
                        if (typeof response.data === 'object' && response.data.message) {
                            message = response.data.message;
                        }
                        BroodleWAAdmin.showResult($result, 'success', message);

                        // Log detailed response for debugging
                        if (response.data && response.data.response_data) {
                            console.log('API Test Response Details:', response.data.response_data);
                        }
                    } else {
                        let errorMessage = response.data;
                        if (typeof response.data === 'object' && response.data.message) {
                            errorMessage = response.data.message;
                        }
                        BroodleWAAdmin.showResult($result, 'error', errorMessage || broodle_engage_admin.strings.api_test_failed);
                    }
                },
                error: function(xhr, status, error) {
                    BroodleWAAdmin.showResult($result, 'error', broodle_engage_admin.strings.api_test_failed);
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Quick test message with predefined values
         */
        quickTestMessage: function(e) {
            e.preventDefault();

            var $button = $(this);
            var $result = $('#quick-test-result');
            var apiKey = $('#api_key').val().trim();
            var phoneNumber = $('#quick-test-phone').val().trim();

            if (!apiKey) {
                BroodleWAAdmin.showResult($result, 'error', 'Please enter API key first.');
                return;
            }

            if (!phoneNumber) {
                BroodleWAAdmin.showResult($result, 'error', 'Please enter a phone number.');
                return;
            }

            // Show loading state
            $button.addClass('loading').prop('disabled', true);
            BroodleWAAdmin.showResult($result, 'loading', 'Sending test message...');

            // Make AJAX request
            $.ajax({
                url: broodle_engage_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'broodle_engage_quick_test',
                    nonce: broodle_engage_admin.nonce,
                    phone: phoneNumber
                },
                success: function(response) {
                    if (response.success) {
                        let message = response.data;
                        if (typeof response.data === 'object' && response.data.message) {
                            message = response.data.message;
                        }
                        BroodleWAAdmin.showResult($result, 'success', message || 'Quick test message sent successfully!');

                        // Log detailed response for debugging
                        if (response.data && response.data.response_data) {
                            console.log('Quick Test Response Details:', response.data.response_data);
                        }
                    } else {
                        let errorMessage = response.data;
                        if (typeof response.data === 'object' && response.data.message) {
                            errorMessage = response.data.message;
                        }
                        BroodleWAAdmin.showResult($result, 'error', errorMessage || 'Quick test failed.');
                    }
                },
                error: function() {
                    BroodleWAAdmin.showResult($result, 'error', 'Quick test failed.');
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Send test message
         */
        sendTestMessage: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var status = $button.data('status');
            var templateName = $('input[name="broodle_engage_settings[templates][' + status + ']"]').val();
            var isEnabled = $('input[name="broodle_engage_settings[enabled_notifications][' + status + ']"]').is(':checked');
            
            if (!templateName) {
                alert('Please enter a template name for this status.');
                return;
            }
            
            if (!isEnabled) {
                alert('Please enable notifications for this status first.');
                return;
            }
            
            // Prompt for phone number
            var phone = prompt('Enter a phone number to send test message (with country code):');
            if (!phone) {
                return;
            }
            
            // Validate phone number format
            if (!BroodleWAAdmin.validatePhoneNumber(phone)) {
                alert('Please enter a valid phone number with country code (e.g., +1234567890).');
                return;
            }
            
            // Show loading state
            $button.addClass('loading').prop('disabled', true);
            
            // Make AJAX request
            $.ajax({
                url: broodle_engage_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'broodle_engage_send_test_message',
                    nonce: broodle_engage_admin.nonce,
                    status: status,
                    template: templateName,
                    phone: phone
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data || broodle_engage_admin.strings.test_sent);
                    } else {
                        alert(response.data || broodle_engage_admin.strings.test_failed);
                    }
                },
                error: function() {
                    alert(broodle_engage_admin.strings.test_failed);
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Validate form before submission
         */
        validateForm: function(e) {
            var isValid = true;
            var $form = $(this);
            
            // Clear previous errors
            $form.find('.error').removeClass('error');
            $form.find('.error-message').remove();
            
            // Validate API credentials
            var apiKey = $('#api_key').val().trim();

            if (!apiKey) {
                BroodleWAAdmin.showFieldError($('#api_key'), 'API key is required.');
                isValid = false;
            }
            
            // Validate retry settings
            var retryAttempts = parseInt($('#retry_attempts').val());
            if (retryAttempts < 0 || retryAttempts > 10) {
                BroodleWAAdmin.showFieldError($('#retry_attempts'), 'Retry attempts must be between 0 and 10.');
                isValid = false;
            }
            
            var retryDelay = parseInt($('#retry_delay').val());
            if (retryDelay < 60 || retryDelay > 3600) {
                BroodleWAAdmin.showFieldError($('#retry_delay'), 'Retry delay must be between 60 and 3600 seconds.');
                isValid = false;
            }
            
            // Validate log retention
            var logRetention = parseInt($('#log_retention_days').val());
            if (logRetention < 1 || logRetention > 365) {
                BroodleWAAdmin.showFieldError($('#log_retention_days'), 'Log retention must be between 1 and 365 days.');
                isValid = false;
            }
            
            // Validate country code format
            var countryCode = $('#country_code').val();
            if (countryCode && !countryCode.match(/^\+\d{1,4}$/)) {
                BroodleWAAdmin.showFieldError($('#country_code'), 'Country code must be in format +1234.');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $form.find('.error').first().offset().top - 100
                }, 500);
            }
            
            return isValid;
        },

        /**
         * Auto-save settings
         */
        autoSaveSettings: function() {
            var $field = $(this);
            var fieldName = $field.attr('name');
            var fieldValue = $field.val();
            
            // Show saving indicator
            var $indicator = $('<span class="auto-save-indicator">Saving...</span>');
            $field.after($indicator);
            
            // Simulate auto-save (in real implementation, this would make an AJAX call)
            setTimeout(function() {
                $indicator.text('Saved').fadeOut(2000, function() {
                    $(this).remove();
                });
            }, 1000);
        },

        /**
         * Show result message
         */
        showResult: function($element, type, message) {
            $element.removeClass('success error loading')
                   .addClass(type)
                   .text(message)
                   .css('display', 'inline-block');
        },

        /**
         * Show field error
         */
        showFieldError: function($field, message) {
            $field.addClass('error');
            var $error = $('<span class="error-message">' + message + '</span>');
            $field.after($error);
        },

        /**
         * Validate phone number format
         */
        validatePhoneNumber: function(phone) {
            // Basic validation for international phone number format
            return /^\+\d{10,15}$/.test(phone);
        },

        /**
         * Format phone number
         */
        formatPhoneNumber: function(phone, countryCode) {
            // Remove all non-digit characters except +
            phone = phone.replace(/[^\d+]/g, '');
            
            // If no country code, add default
            if (!phone.startsWith('+')) {
                phone = countryCode + phone;
            }
            
            return phone;
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            type = type || 'success';
            
            var $notice = $('<div class="notice broodle-wa-notice notice-' + type + ' is-dismissible">')
                .append('<p>' + message + '</p>')
                .append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
            
            $('.wrap h1').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
            
            // Handle dismiss button
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut();
            });
        },

        /**
         * Confirm action
         */
        confirmAction: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },

        /**
         * Copy to clipboard
         */
        copyToClipboard: function(text) {
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            
            BroodleWAAdmin.showNotification('Copied to clipboard!', 'success');
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        BroodleWAAdmin.init();
    });

    // Make BroodleWAAdmin globally available
    window.BroodleWAAdmin = BroodleWAAdmin;

})(jQuery);
