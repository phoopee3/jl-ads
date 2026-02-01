/**
 * JL Ads Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Media uploader for ad images
        $('.jl-ad-upload-image').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $container = $button.closest('.jl-ad-version');
            var $imageInput = $container.find('.jl-ad-image-id');
            var $preview = $container.find('.jl-ad-image-preview');
            var $removeBtn = $container.find('.jl-ad-remove-image');
            
            var mediaUploader = wp.media({
                title: 'Select Ad Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                
                $imageInput.val(attachment.id);
                
                var imageUrl = attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                $preview.html('<img src="' + imageUrl + '" alt="">');
                
                $removeBtn.show();
            });
            
            mediaUploader.open();
        });
        
        // Remove image
        $('.jl-ad-remove-image').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $container = $button.closest('.jl-ad-version');
            var $imageInput = $container.find('.jl-ad-image-id');
            var $preview = $container.find('.jl-ad-image-preview');
            
            $imageInput.val('');
            $preview.html('');
            $button.hide();
        });
        
        // Toggle schedule date fields
        $('input[name="jl_ad_schedule_type"]').on('change', function() {
            var $dateFields = $('.jl-ad-date-fields');
            
            if ($(this).val() === 'scheduled') {
                $dateFields.slideDown();
            } else {
                $dateFields.slideUp();
            }
        });
        
        // Copy shortcode to clipboard
        $('.jl-ad-copy-shortcode').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var shortcode = $button.data('shortcode');
            var $success = $button.siblings('.jl-ad-copy-success');
            
            // Create temporary textarea to copy from
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(shortcode).select();
            
            try {
                document.execCommand('copy');
                $success.fadeIn().delay(1500).fadeOut();
            } catch (err) {
                // Fallback for modern browsers
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(shortcode).then(function() {
                        $success.fadeIn().delay(1500).fadeOut();
                    });
                }
            }
            
            $temp.remove();
        });
        
        // Copy shortcode from list table
        $(document).on('click', '.jl-ad-copy-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var shortcode = $button.data('shortcode');
            
            // Use modern clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shortcode).then(function() {
                    $button.addClass('copied');
                    setTimeout(function() {
                        $button.removeClass('copied');
                    }, 1500);
                });
            } else {
                // Fallback for older browsers
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(shortcode).select();
                
                try {
                    document.execCommand('copy');
                    $button.addClass('copied');
                    setTimeout(function() {
                        $button.removeClass('copied');
                    }, 1500);
                } catch (err) {
                    console.error('Failed to copy shortcode');
                }
                
                $temp.remove();
            }
        });
        
    });
    
})(jQuery);
