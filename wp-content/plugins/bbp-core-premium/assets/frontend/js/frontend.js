;(function($){ 
    $(document).ready(function(){
        
        // Mini Profile
        $('.bbpc-mini-profile').click(function(){
            $(this).toggleClass('active');

            $('.bbpc-mini-profile-wrapper').click(function(event){
                event.stopPropagation(); 
            });

            $(document).mouseup(function(e){
                var container = $(".bbpc-mini-profile");
                if (!container.is(e.target) && container.has(e.target).length === 0){
                    container.removeClass('active');
                }
            });

            var profile_size = $('.bbpc-mini-profile-wrapper').width();
            var left_spacer  = $('.bbpc-mini-profile-wrapper').offset().left;
            var right_spacer = $(window).width() - ($('.bbpc-mini-profile-wrapper').offset().left + $('.bbpc-mini-profile-wrapper').outerWidth());

            if ( left_spacer < profile_size ) {
                $('.bbpc-mini-profile-wrapper').addClass('position-left');
            }
            if ( right_spacer < profile_size ) {
                $('.bbpc-mini-profile-wrapper').removeClass('position-left');
            }
        });
        
        function bbpc_subscription(){
            $('.subscription-toggle').on('click', function() {
                // Make an AJAX request
                var post_id = $(this).attr('data-bbp-object-id');
                    post_id = parseInt(post_id);
                
                // if this parent not is-subscribed
                if (!$(this).parent().hasClass('is-subscribed')) {            
                    $.ajax({
                        type: 'POST',
                        url: bbpc_localize_script.ajaxurl,
                        data: {
                            action: 'create_notification_post',
                            post_id: post_id
                        },
                        success: function(data) {
                            bbpc_unsubscription();
                            console.log('success');
                        }
                    });
                }
            });
        }
        bbpc_subscription();
        
        function bbpc_unsubscription(){
            $('.subscription-toggle').on('click', function() {
                // Make an AJAX request
                var post_id = $(this).attr('data-bbp-object-id');
                    post_id = parseInt(post_id);
                
                // if this parent not is-subscribed
                if ($(this).parent().hasClass('is-subscribed')) {            
                    $.ajax({
                        type: 'POST',
                        url: bbpc_localize_script.ajaxurl,
                        data: {
                            action: 'remove_notification_post',
                            post_id: post_id
                        },
                        success: function(data) {
                            bbpc_subscription();
                            console.log('success');
                        }
                    });
                }
            });
        }
        bbpc_unsubscription();
        
        function bbpc_solved(){

            $(".bbp-admin-links a[href*='?solve_topic']").on("click", function() {   

                $.ajax({
                    type: 'POST',
                    url: bbpc_localize_script.ajaxurl,
                    data: {
                        action: 'create_notification_by_solve',
                        post_id: bbpc_localize_script.bbpc_current_topic_id
                    },
                    success: function(data) {
                        bbpc_unsolved();
                    }
                });
                
            });   
        }
        bbpc_solved();
        
        function bbpc_unsolved(){
            $(".bbp-admin-links a[href*='?unsolve_topic']").on("click", function() {

                $.ajax({
                    type: 'POST',
                    url: bbpc_localize_script.ajaxurl,
                    data: {
                        action: 'create_notification_by_unsolved',
                        post_id: bbpc_localize_script.bbpc_current_topic_id
                    },
                    success: function(data) {
                        bbpc_solved();
                    }
                });
                
            });   
        }
        bbpc_unsolved();
    });
})(jQuery);

/**
* Voting button for single topic  
* Function to set a cookie
*/

function setCookie(name, value, daysToExpire) {
    var date = new Date();
    date.setTime(date.getTime() + (daysToExpire * 24 * 60 * 60 * 1000));
    var expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + "; " + expires + "; path=/";
}

// Function to check if a cookie exists
function getCookie(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
}

// Function to check if the user has already voted
function hasVoted(postId) {
    return getCookie("voted_" + postId) === "true";
}

// Function to update button text based on voting status
function updateButtonText(postId) {
    var button = document.querySelector('.bbpc-same-topic-btn[data-post-id="' + postId + '"]');
    if (hasVoted(postId)) {
        setTimeout(function(){
        button.classList.add("voted");
        }, 2000);
    }
}

// Function to update vote counter
function updateVoteCounter(postId) {
    var counter = document.querySelector('.bbpc-same-topic-counter[data-post-id="' + postId + '"]');
    var count   = parseInt(counter.textContent);

    if (hasVoted(postId)) {
        count++;
    } else {
        count--;
    }
    counter.textContent = count;
}

function bbpc_same_topic_ajax(postId, value) {
    updateButtonText(postId);
    updateVoteCounter(postId);

    jQuery.ajax({
        type: "POST",
        url: bbpc_localize_script.ajaxurl,
        data: {
            action: 'bbpc_same_topic_voting',
            post_id: postId,
            bbpc_same_topic_voting: value
        },
        success: function (data) {
            console.log(data);
            jQuery('.bbpc-same-topic-btn[data-post-id="' + postId + '"]').removeClass('voted').removeClass('cookie-voted');

            jQuery('.bbpc-same-topic-counter-wrap[data-post-id="' + postId + '"]').text(parseInt(jQuery('.bbpc-same-topic-counter-wrap[data-post-id="' + postId + '"]').text()) + value );

            jQuery('.same-topic-voting-notice').fadeIn();          
            setTimeout(function() {
                jQuery('.same-topic-voting-notice').fadeOut();
            }, 2000);
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
}

document.addEventListener('click', function(event) {
    if (event.target.classList.contains('bbpc-same-topic-btn')) {
        var postId = event.target.getAttribute('data-post-id');
        
        if (!hasVoted(postId)) {
        setCookie("voted_" + postId, "true", 30);                
        bbpc_same_topic_ajax(postId, +1);
        } else {
        document.cookie = "voted_" + postId + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        bbpc_same_topic_ajax(postId, -1);          
        }
    }
});

// Update button text and vote counter on page load
document.addEventListener('DOMContentLoaded', function() {
    var voteButtons = document.querySelectorAll('.bbpc-same-topic-btn');
    for (var i = 0; i < voteButtons.length; i++) {
        var postId = voteButtons[i].getAttribute('data-post-id');
        updateButtonText(postId);
        updateVoteCounter(postId);
    }
});


   