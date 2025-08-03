(function ($) {
  'use strict';

  /*------------ Cookie functions and color js ------------*/
  function createCookie(name, value, days) {
    var expires = '';
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + value + expires + '; path=/';
  }

  function readCookie(name) {
    var nameEQ = name + '=';
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  function eraseCookie(name) {
    createCookie(name, '', -1);
  }

  var prefersDark =
    window.matchMedia &&
    window.matchMedia('(prefers-color-scheme: dark)').matches;
  var selectedNightTheme = readCookie('body_dark');

  if (
    selectedNightTheme == 'true' ||
    (selectedNightTheme === null && prefersDark)
  ) {
    applyNight();
    $('.dark_mode_switcher').prop('checked', true);
  } else {
    applyDay();
    $('.dark_mode_switcher').prop('checked', false);
  }

  function applyNight() {
    if ($('.js-darkmode-btn .ball').length) {
      $('.js-darkmode-btn .ball').css('left', '45px');
    }
    $('body').addClass('body_dark');
  }

  function applyDay() {
    if ($('.js-darkmode-btn .ball').length) {
      $('.js-darkmode-btn .ball').css('left', '4px');
    }
    $('body').removeClass('body_dark');
  }

  $('.dark_mode_switcher').change(function () {
    if ($(this).is(':checked')) {
      applyNight();
      createCookie('body_dark', true, 999);
    } else {
      applyDay();
      createCookie('body_dark', false, 999);
    }
  });

  // Filter Select
  $('select').niceSelect();

  // Sidebar Tabs [COOKIE]
  $(document).on('click', '.tab-menu .easydocs-navitem', function () {
    const target = $(this).attr('data-rel');
    const $siblings = $(this).siblings();

    if (!$(this).hasClass('is-active')) {
      $siblings.removeClass('is-active');
      $(this).addClass('is-active');

      $('#' + target)
        .fadeIn('slow')
        .siblings('.easydocs-tab')
        .hide();

      createCookie('eazydocs_doc_current_tab', target, 999);
    }
  });

  // Restore last active tab
  function keep_last_active_doc_tab() {
    const lastTab = readCookie('eazydocs_doc_current_tab');
    if (lastTab) {
      const $tab = $(`.tab-menu .easydocs-navitem[data-rel="${lastTab}"]`);
      if (!$tab.hasClass('is-active')) {
        $tab.siblings().removeClass('is-active');
        $tab.addClass('is-active');

        $('.easydocs-tab-content .easydocs-tab').removeClass('tab-active');
        $(`#${lastTab}`).addClass('tab-active');
      }
    }
  }
  keep_last_active_doc_tab();

  $('.tab-menu .easydocs-navitem .parent-delete').on('click', function () {
    return false;
  });

  $(document).ready(function () {

    if ( $('#bbpc-search').length ) {
      $('#bbpc-search').on('keyup', function () {
        var value = $(this).val().toLowerCase();
        $('.easydocs-accordion-item').filter(function () {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
      });

      // Dropdown Classic UI Filter
      let bbpc_classic_ui = document.getElementById('bbpc_classic_ui');

      function swithToLink() {
        window.location.href = this.value;
      }

      bbpc_classic_ui.onchange = swithToLink;
    }
  })

  $(document).ready(function (e) {
    function t(t) {
      e(t).bind('click', function (t) {
        t.preventDefault();
        e(this).parent().fadeOut();
      });
    }

    e('.header-notify-icon').click(function () {
      var t = e(this)
        .parents('.easydocs-notification')
        .children('.easydocs-dropdown')
        .is(':hidden');
      e('.easydocs-notification .easydocs-dropdown').hide();
      e('.easydocs-notification .header-notify-icon').removeClass('active');
      if (t) {
        e(this)
          .parents('.easydocs-notification')
          .children('.easydocs-dropdown')
          .toggle()
          .parents('.easydocs-notification')
          .children('.header-notify-icon')
          .addClass('active');
      }
    });
    e(document).bind('click', function (t) {
      var n = e(t.target);
      if (!n.parents().hasClass('easydocs-notification'))
        e('.easydocs-notification .easydocs-dropdown').hide();
    });
    e(document).bind('click', function (t) {
      var n = e(t.target);
      if (!n.parents().hasClass('easydocs-notification'))
        e('.easydocs-notification .header-notify-icon').removeClass('active');
    });

    // CREATE FORUM
    function create_forum() {
      $(document).on('click', '#bbpc-forum', function (e) {
        e.preventDefault();    
        Swal.fire({
          title: bbp_core_local_object.create_forum_title,
          input: 'text',
          showCancelButton: true,
          inputAttributes: {
            name: 'bbp_forum_title',
          },
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: bbp_core_local_object.ajaxurl,
              type: 'POST',
              data: {
                action: 'bbp_create_forum',
                bbp_forum_title: result.value, 
                bbpc_nonce: bbp_core_local_object.nonce
              },
              beforeSend: function () {
                Swal.fire({
                  title: 'Creating Forum...',
                  icon: 'question',
                  allowOutsideClick: false,
                  showConfirmButton: true,
                  didOpen: () => {
                    Swal.showLoading();
                  },
                });
              },
              success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                  Swal.fire({
                    title: 'Error!',
                    text: response.data,
                    icon: 'error',
                  });
                }
              },
              error: function () {
                Swal.fire({
                  title: 'Error!',
                  text: 'Something went wrong. Please try again.',
                  icon: 'error',
                });
              },
            });
          }
        });
      });
    }    
    create_forum();
    
    // CREATE TOPIC
    function create_topic() {
      $(document).on('click', '#bbpc-topic', function (e) {
        e.preventDefault();
        let forumID = $(this).attr('bbp_forum_id');
        Swal.fire({
          title: bbp_core_local_object.create_topic_title,
          input: 'text',
          showCancelButton: true,
          inputAttributes: {
            name: 'bbp_topic_title',
          },
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: bbp_core_local_object.ajaxurl,
              type: 'POST',
              data: {
                action: 'bbp_create_topic',
                bbp_topic_title: result.value,
                forum_id: forumID, 
                bbpc_nonce: bbp_core_local_object.nonce
              },
              beforeSend: function () {
                Swal.fire({
                  title: 'Creating Topic...',
                  icon: 'question',
                  allowOutsideClick: false,
                  showConfirmButton: true,
                  didOpen: () => {
                    Swal.showLoading();
                  },
                });
              },
              success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                  Swal.fire({
                    title: 'Error!',
                    text: response.data,
                    icon: 'error',
                  });
                }
              },
              error: function () {
                Swal.fire({
                  title: 'Error!',
                  text: 'Something went wrong. Please try again.',
                  icon: 'error',
                });
              },
            });
          }
        });
      });
    }
    create_topic();

    // DELETE FORUM
    function delete_forum() {
      $('.forum-delete').on('click', function (e) {
        e.preventDefault();        
        var forumID = $(this).attr('bbp_forum_id');   
        Swal.fire({
          title: bbp_core_local_object.forum_delete_title,
          text: bbp_core_local_object.forum_delete_desc,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: bbp_core_local_object.ajaxurl,
              type: 'POST',
              data: {
                action: 'bbp_delete_forum',
                forum_id: forumID, 
                bbpc_nonce: bbp_core_local_object.nonce
              },
              beforeSend: function () {
                Swal.fire({
                  title: 'Deleting Forum...',
                  icon: 'question',
                  allowOutsideClick: false,
                  showConfirmButton: false,
                  didOpen: () => {
                    Swal.showLoading();
                  },
                });
              },
              success: function (response) {
                console.log(response);
                if (response.success) {
                  location.reload();
                } else {
                  console.log(response)
                  Swal.fire({
                    title: 'Error!',
                    text: response.data || 'Unexpected error occurred.',
                    icon: 'error',
                  });
                }
              },
              error: function () {
                Swal.fire({
                  title: 'Error!',
                  text: 'Something went wrong. Please try again.',
                  icon: 'error',
                });
              },
            });
          }
        });
      });
    }    
    delete_forum();
    

    // DELETE TOPIC
    function delete_topic() {
      $('.section-delete').on('click', function (e) {
        e.preventDefault();
        var topicID = $(this).attr('bbp_topic_id'); 
        Swal.fire({
          title: bbp_core_local_object.forum_delete_title,
          text: bbp_core_local_object.topic_delete_desc,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes',
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: bbp_core_local_object.ajaxurl,
              type: 'POST',
              data: {
                action: 'bbp_delete_topic',
                topic_id: topicID, 
                bbpc_nonce: bbp_core_local_object.nonce
              },
              beforeSend: function () {
                Swal.fire({
                  title: 'Deleting Topic...',
                  icon: 'question',
                  allowOutsideClick: false,
                  showConfirmButton: false,
                  didOpen: () => {
                    Swal.showLoading();
                  },
                });
              },
              success: function (response) {
                console.log(response); 
                if (response.success) {
                  location.reload();
                } else {
                  console.log(response)
                  Swal.fire({
                    title: 'Error!',
                    text: response.data || 'Unexpected error occurred.',
                    icon: 'error',
                  });
                }
              },
              error: function () {
                Swal.fire({
                  title: 'Error!',
                  text: 'Something went wrong. Please try again.',
                  icon: 'error',
                });
              },
            });
          }
        });
      });
    }

    delete_topic();

    // Notification pro alert
    $('.easydocs-notification.bbp-core-pro-notification').on(
      'click',
      function (e) {
        e.preventDefault();
        let href = $(this).attr('href');
        let assets = bbp_core_local_object.BBPC_ASSETS;
        Swal.fire({
          title: 'Notification is a Premium feature',
          html:
            '<span class="pro-notification-body-text">You need to Upgrade the Premium Version to use this feature</span><video height="400px" autoplay="autoplay" loop="loop" src="' +
            assets +
            '/videos/noti.mp4"></video>',
          icon: false,
          buttons: false,
          dangerMode: true,
          showCloseButton: true,
          confirmButtonText:
            '<a href="admin.php?page=bbp-core-pricing">Upgrade to Premium</a>',
          footer:
            '<a href="https://spider-themes.net/bbp-core/" target="_blank"> Learn More </a>',
          customClass: {
            title: 'upgrade-premium-heading',
            confirmButton: 'upgrade-premium-button',
            footer: 'notification-pro-footer-wrap',
          },
          confirmButtonColor: '#f1bd6c',
          Borderless: true,
        });
      }
    );
  });

  // Click pending replies count to show pending replies.
  $('[click-target]').click(function () {
    let id = $(this).attr('click-target');
    $(`[click-target=${id}]`).toggleClass('active');
    $(`[reply-target=${id}]`).toggle();
  });

  // Sidebar Tabs [COOKIE]
  $(document).on('click', '[cookie-id]', function () {
    let target = $(this).attr('cookie-id');
    let item = `[cookie-id=${target}]`;
    $('[cookie-id]').removeClass('is-active mixitup-control-active');
    $(item).addClass('is-active mixitup-control-active');
    $(target).fadeIn('slow').siblings('.easydocs-tab').hide();

    let isActiveTab = $(this).hasClass('is-active');
    if (isActiveTab === true) {
      createCookie('bbpc_current_filter', target, 999);
    }

    return true;
  });

  $(document).ready(function () {
    // Keep Last filter item active
    function keepLastFilterActive() {
      let bbpcLastActiveFilter = readCookie('bbpc_current_filter');
      if (bbpcLastActiveFilter) {
        // Tab item
        $('[cookie-id]').removeClass('is-active mixitup-control-active');
        $(`[cookie-id="${bbpcLastActiveFilter}"]`).click();
      }
    }

    keepLastFilterActive();

  });

})(jQuery);

function menuToggle() {
  const toggleMenu = document.querySelector('.easydocs-dropdown');
  toggleMenu.classList.toggle('is-active');
}