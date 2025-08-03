function bbpress_post_vote_link_clicked(post_id, direction) {
    var post_clicked = jQuery('.bbp-voting.bbp-voting-post-'+post_id);
    // Validate you're allowed to vote
    if(post_clicked.hasClass('view-only')) {
        console.log('Post ID ' + post_id + ' is view only.');
        return false;
    }
    // Loading CSS
    post_clicked.css('opacity', 0.5).css('pointer-events', 'none');
    // Ajax data
    var data = {
        'action': 'bbpress_post_vote_link_clicked',
        'post_id': post_id,
        'direction': direction
    };
    jQuery.post(bbpc_localize_script.ajaxurl, data, function(response) {
        if(response.hasOwnProperty('error')) {
            // Error response
            console.log('Voting error:', response.error);
        } else if(!response.hasOwnProperty('score')) {
            // Catch invalid AJAX response
            console.log('SOMETHING WENT WRONG', response);
        } else {
            // Proper response that has score, direction, ups, and downs
            var score = parseInt(response.score);
            direction = parseInt(response.direction);
            var up = parseInt(response.ups);
            var down = parseInt(response.downs);
            console.log('Voted ' + direction, 'post #' + post_id, 'score: ' + score, 'ups: ' + up, 'downs: ' + down);
            jQuery('.bbp-voting.bbp-voting-post-'+post_id).each(function() {
                // Get elements
                var wrapper = jQuery(this);
                var score_el = jQuery(this).find('.score');
                var up_el = jQuery(this).find('.up');
                var down_el = jQuery(this).find('.down');
                // Set elements' html
                score_el.html(score);
                up_el.attr('data-votes', '+' + up);
                down_el.attr('data-votes', down);
                // Change arrow colors
                if(direction > 0) {
                    // Up vote
                    up_el.css('border-bottom-color', '#1e851e');
                    wrapper.removeClass('voted-down').addClass('voted-up');
                } else if (direction < 0) {
                    // Down vote
                    down_el.css('border-top-color', '#992121');
                    wrapper.removeClass('voted-up').addClass('voted-down');
                } else if (direction == 0) {
                    // Remove vote
                    up_el.css('border-bottom-color', 'inherit');
                    down_el.css('border-top-color', 'inherit');
                    wrapper.removeClass('voted-down').removeClass('voted-up');
                }
                // Restore the CSS
                wrapper.css('opacity', 1).css('pointer-events', 'auto');
            });
        }
    });
}

function bbp_voting_select_accepted_answer(post_id) {
    // Ajax data
    var data = {
        'action': 'bbp_voting_select_accepted_answer',
        'post_id': post_id
    };
    jQuery.post(bbpc_localize_script.ajaxurl, data, function(response) {
        console.log('Accepted answer', response);
        if(response) window.location.reload();
    });
}

;(function($) {
    // Fix for BuddyBoss theme grabbing reply excerpt text including vote buttons and score
    $( document ).on(
        'click',
        'a[data-modal-id-inline]',
        function (e) {
            e.preventDefault();
            // Use setTimeout to move the end of the call stack
            setTimeout(function() {
                var bbpress_forums_element = $( e.target ).closest( '.bb-grid' );
                var reply_excerpt_el = bbpress_forums_element.find( '.bbp-reply-form' ).find( '#bbp-reply-exerpt' );
                reply_excerpt_el.html( reply_excerpt_el.html().replace(/^.*\:\:/, '') );
            }, 0);
        }
    );
    
    $(document).ready(function(){

        // Agree/Disagree button AJAX 
        if ($('.bbpc-agree-button, .bbpc-disagree-button').length) {
            $('.bbpc-agree-button, .bbpc-disagree-button').on('click', function() { 

                var dataLogin = $(this).attr('data-login');
                if (dataLogin) {
                    window.location.href = dataLogin;
                    return;
                }

                var topicID = $(this).data('topic');
                var isAgreeButton = $(this).hasClass('bbpc-agree-button');
                var action = isAgreeButton ? 'bbpc_agree' : 'bbpc_disagree';

                var dataType = $(this).attr('data-type');

                // Determine if the button is already active (meaning a toggle click)
                var isActive = $(this).hasClass('active');

                $.ajax({
                    url: bbpc_localize_script.ajaxurl,
                    type: 'POST',
                    data: {
                        action: action,
                        topic_id: topicID,
                        nonce: bbpc_localize_script.nonce
                    },
                    beforeSend: function() {                        
                        if ( dataType === 'like' ) {
                            $('.bbpc-agree-button').append('<span class="bbpc-preloader"></span>');
                        } else {
                            $('.bbpc-disagree-button').append('<span class="bbpc-preloader"></span>');
                        }
                    },
                    success: function(response) {
                        if (response.success) {
                            
                            var agreeCount = response.data.agree_count;
                            if (agreeCount === null || agreeCount === undefined || agreeCount === '') {
                                $('#bbpc-agree-count-' + topicID).text(0);
                            } else {
                                $('#bbpc-agree-count-' + topicID).text(agreeCount);
                            }

                            if ( dataType === 'like' ) {
                                $('.bbpc-agree-button span.bbpc-preloader').remove();
                            } else {
                                $('.bbpc-disagree-button span.bbpc-preloader').remove();
                            }

                            var disagreeCount = response.data.disagree_count;
                            if (disagreeCount === null || disagreeCount === undefined || disagreeCount === '') {
                                $('#bbpc-disagree-count-' + topicID).text(0);
                            } else {
                                $('#bbpc-disagree-count-' + topicID).text(disagreeCount);
                            }
                            
                            // Toggle active classes
                            if (isAgreeButton) {
                                if (isActive) {
                                    // Unvoting: Remove the active class if it was previously active
                                    $('.bbpc-agree-button').removeClass('active');
                                } else {
                                    // Voting: Add active class to agree and remove from disagree
                                    $('.bbpc-agree-button').addClass('active');
                                    $('.bbpc-disagree-button').removeClass('active');
                                }
                            } else {
                                if (isActive) {
                                    // Unvoting: Remove the active class if it was previously active
                                    $('.bbpc-disagree-button').removeClass('active');
                                } else {
                                    // Voting: Add active class to disagree and remove from agree
                                    $('.bbpc-disagree-button').addClass('active');
                                    $('.bbpc-agree-button').removeClass('active');
                                }
                            }
                        }
                    }
                });
            });
        }
        
        $('.bbpc-footer-actions:empty').remove();
        
    });

})(jQuery);