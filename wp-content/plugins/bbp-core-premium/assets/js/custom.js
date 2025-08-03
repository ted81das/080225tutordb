(function($) {
    'use strict'

    $(document).ready(function() {
        
        // Attachment lightbox
        $('.bbp-attachments li a.bbpc-lightbox').on('click', function(e) {
            e.preventDefault();
            $('body').append($('<div class="bbpc-attachment-lightbox"><div class="bbpc-attachment-lightbox-inner"><span>&times;</span><img src="'+$(this).attr('href')+'"></div></div>'));

            // if click on close button
            $('.bbpc-attachment-lightbox > .bbpc-attachment-lightbox-inner > span').on('click', function() {
                $('.bbpc-attachment-lightbox').remove();
            });

            // if click on outside of image, if click is on image then it will not close
            $('.bbpc-attachment-lightbox').on('click', function(e) {
                if (e.target === this) {
                    $('.bbpc-attachment-lightbox').remove();
                }
            });
        });

        $('.bbpc-nav-notification').click(function(){
            $(this).toggleClass('active');
            $('.bbpc-notification-wrap').click(function(event){
                event.stopPropagation(); 
            });
            
            $(document).mouseup(function(e){
                var container = $(".bbpc-nav-notification");
                if (!container.is(e.target) && container.has(e.target).length === 0){
                    container.removeClass('active');
                }
            });
        });
        
        // Capture clicks on post links
        function bbpc_notification_read(){
            $('.bbpc-notification-link').on('click', function(e) {
                var postID = $(this).data('post-id'); // Get the post ID
                
                // Send an AJAX request to update the post as "read"
                $.ajax({
                    type: 'POST',
                    url: bbpc_localize_script.ajaxurl, // WordPress AJAX handler
                    data: {
                        action: 'bbpc_mark_post_as_read',
                        post_id: postID
                    },
                    success: function(response) {
                    }
                });
            });
        }
        bbpc_notification_read();
        
        // Notification load more by ajax
        $('.bbpc-notification-wrap').on('click', '.bbpc-load-more-notifications', function() {
            const wrap      = $(this).closest('.bbpc-notification-wrap');
            let offset      = parseInt(wrap.attr('data-offset')) || 0;
            const total     = parseInt(wrap.attr('data-total')) || 0;
            const button    = $(this);    
            button.prop('disabled', true).text('Loading...');
    
            $.post(bbpc_localize_script.ajaxurl, {
                action: 'bbpc_load_more_notifications',
                offset: offset,
            }, function(response) {
                if ( response ) {
                    wrap.find('.bbpc-notification-footer').before(response);
                    offset += 3;
                    wrap.attr('data-offset', offset);    
                    if ( offset >= total ) {
                        button.remove();
                    } else {
                        button.prop('disabled', false).text('See previous notifications');
                    }
                    
                    bbpc_notification_read();
                    
                } else {
                    button.remove();
                }
            });
        });
        
    });
})(jQuery);