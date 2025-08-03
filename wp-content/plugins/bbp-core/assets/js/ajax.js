/**
 * @package ama-core
 * Ama Ajax actions for forums and everything necessary
 */
;(function ($) {
    'use strict'
    $(document).ready(function () {
  
      let ajax_url      = bbpc_localize_script.ajaxurl
  
      $('.single-filter-item a').on('click', function (e) {
        e.preventDefault();

        var data_id     = $(this).parent().parent().parent().attr('data_id');        
        let forum_value = $(this).attr('data-forum');
      
        $('.forum-post-widget[data_id='+data_id+'] .single-filter-item a').removeClass('data-active');
        $(this).addClass('data-active');
  
        $.ajax({
          url: ajax_url,
          method: 'POST',      
          data: {
            action: 'bbpc_ajax_forum',
            data_forum: forum_value,
          },
          beforeSend: function () {
            $('.forum-post-widget[data_id="'+data_id+'"] #aj-post-filter-widget').html(
              '<?xml version="1.0" encoding="utf-8"?>\n' +
              '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">\n' +
              '<circle class="bbpc-preloader" cx="50" cy="50" r="18" stroke-width="2" stroke="#11a683" stroke-dasharray="28.274333882308138 28.274333882308138" fill="none" stroke-linecap="round">\n' +
              '  <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>\n' +
              '</circle>\n' +
              '</svg>',
            )
          },
          success: function (data) {
            $('.forum-post-widget[data_id="'+data_id+'"] #aj-post-filter-widget').html(data)
          },
          error: function () {
            console.log('Oops! Something wrong, try again!')
          }
          ,
        });
      });
      
      function bbp_forums() {
        $('.collapse-btn-wrap').on('click', function (e) {
          var data_id = $(this).parent('.more-communities').attr('data_id');
      
          e.preventDefault();
          $(this).toggleClass('active');
          $('.more-communities[data_id="'+data_id+'"] > .collapse-wrap[data_id="'+data_id+'"]').slideToggle(500);
        });
      }
      bbp_forums();      
    });
  })(jQuery);