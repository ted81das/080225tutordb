(function($){
    'use sticky'
    $(document).ready(function() {

        $('body.bbpc-no-pro .st-pro-notice ul li:last-child label input').attr('disabled', true);
        $('body.bbpc-no-pro.bbpc-geo-roles .bbpc-geo-roles-opt').removeClass('st-pro-notice');
        // BBP Core pro notice.
        function bbpc_pro_notice() {
            if ( $('body').hasClass('bbpc-no-pro') ) {
                $('.st-pro-notice').on('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Opps...',
                        html: 'This is a PRO feature. You need to <a href="admin.php?page=bbp-core-pricing"><strong class="upgrade-link">Upgrade&nbsp;&nbsp;➤</strong></a> to the Premium Version to use this feature',
                        icon: "warning",
                        buttons: [false, "Close"],
                        dangerMode: true
                    })
                });
            } else {
                // rmeove class if it has pro notice class
                $('.csf-field').removeClass('st-pro-notice');
            }
        }
        bbpc_pro_notice();

        // Notification pro alert
        $('.easydocs-notification.pro-notification-alert').on('click', function (e) {
            e.preventDefault();
            let href = $(this).attr('href')
            let assets = eazydocs_local_object.EAZYDOCS_ASSETS;
            Swal.fire({
                title: 'Notification is a Premium feature',
                html: '<span class="pro-notification-body-text">You need to Upgrade the Premium Version to use this feature</span><video height="400px" autoplay="autoplay" loop="loop" src="'+assets+'/videos/noti.mp4"></video>',
                icon: false,
                buttons: false,
                dangerMode: true,
                showCloseButton: true,
                confirmButtonText:
                    '<a href="admin.php?page=eazydocs-pricing">Upgrade to Premium</a>',
                footer: '<a href="https://spider-themes.net/eazydocs/" target="_blank"> Learn More </a>',

                customClass: {
                    title: 'upgrade-premium-heading',
                    confirmButton: 'upgrade-premium-button',
                    footer: 'notification-pro-footer-wrap',
                },
                confirmButtonColor: '#f1bd6c',
                Borderless: true,

            })
        });

        // Remove condition if it has pro notice class
        $('.st-pro-notice').attr('data-condition', '');

    })
})(jQuery);