(function($){
    $(document).ready(function(){
        
        $(document).on('click', '.is-subscribed a', function(e){   
            e.preventDefault();         
            $(this).text( "Unsubscribing..." );    
            $('.bbpc-unsubscribe-link').text( "Unsubscribing..." );
            setTimeout(function(){
                $( ".bbp__success-subscribe" ).hide();
            }
            , 1100);
        });
        
        $(document).on('click', '.show_subscribe span:not(.is-subscribed) a', function(){            
            $( ".show_subscribe span:not(.is-subscribed) a" ).text( "Subscribing..." );
        });

        // if has class
        if ( $( ".show_subscribe .is-subscribed" ).length ) {
            $( ".post-header" ).before( '<div class="alert alert-success bbp__success-subscribe"><div class="mailIcon"><svg width="60" height="60" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path data-name="envelope-Filled" d="M10.36 11.71 3.19 6.62A4.5 4.5 0 0 1 7 4.5h10a4.5 4.5 0 0 1 3.81 2.11l-7.15 5.08a3 3 0 0 1-3.3.02ZM21.48 8.6l-6.68 4.74a5.082 5.082 0 0 1-2.81.85 4.968 4.968 0 0 1-2.76-.84L2.52 8.6c-.01.13-.02.27-.02.4v6A4.507 4.507 0 0 0 7 19.5h10a4.507 4.507 0 0 0 4.5-4.5V9c0-.13-.01-.27-.02-.4Z" style="fill:#4c52f1"/></svg></div><span>You are subscribed to this forum, and will receive emails for future topic activity.</span> <a class="bbpc-unsubscribe-link" data-forum-id='+bbpc_localize_script.bbpc_subscribed_forum_id+' href="javascript:void()">Unsubscribe</a> from '+bbpc_localize_script.bbpc_subscribed_forum_title+'</div>' );
        }
        
        $('.bbpc-unsubscribe-link').click(function(){
            let data_id = $(this).attr('data-forum-id');
            $('#subscribe-'+data_id+' a').click().text( "Unsubscribing..." );
            $(this).text( "Unsubscribing..." );
        });

    });
})(jQuery);