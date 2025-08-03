;(function ($) {
    $(document).ready(function () {
        const overlay = $('.bbpc-search-overlay');

        // Show overlay on input or result click
        $('#searchInput, #bbpc-search-result, .bbpc-search-keyword ul li a').on('click', function () {
            overlay.css('display', 'block').addClass('active');
        });

        // Hide overlay
        overlay.on('click', function () {
            overlay.css('display', 'none').removeClass('active');
        });

        // Focus in search input
        $('.bbpc_search_form_wrapper').on('focusin', function () {
            $('.body_dark #searchInput').addClass('input_focused');
            if ($('#bbpc-search-result.ajax-search').length > 0) {
                $('.body_dark #searchInput').addClass('input_focused');
            }
        });

        // Focus out search input
        $('.bbpc_search_form_wrapper').on('focusout', function () {
            if ($('#bbpc-search-result.ajax-search').length > 0) {
                $('.body_dark #searchInput').addClass('input_focused');
            } else {
                $('.body_dark #searchInput').removeClass('input_focused');
            }
        });

        $('#searchInput').on('keyup', function () {
            $('.click_capture').css({ 'opacity': '0', 'visibility': 'hidden' });
        });

        // Debounced AJAX search
        let debounceTimer;
        $('#searchInput').on('keyup', function () {
            $('.not-found-text').hide();
            clearTimeout(debounceTimer);
            const searchInput = $(this).val();
            const ajax_url = bbpc_localize_script.ajaxurl;

            debounceTimer = setTimeout(function () {
                if (searchInput !== '') {
                    $.ajax({
                        url: ajax_url,
                        method: 'POST',
                        data: {
                            action: 'bbpc_search_data_fetch',
                            keyword: searchInput
                        },
                        beforeSend: function () {
                            $('.spinner').show();
                        },
                        success: function (data) {
                            $('#bbpc-search-result').html(data).addClass('ajax-search');
                            $('.spinner').hide();

                            const no_result = $('.tab-item.active.all-active').attr('data-noresult');
                            if (no_result) {
                                const msg = no_result.replace(/-/g, ' ');
                                $('#bbpc-search-result').html('<h5 class="bbpc-not-found-text">' + msg + '</h5>');
                                $('.bbpc-not-found-text').show();
                            }
                        }
                    });
                }
            }, 1000);
        });

        // Keyword click handler — OUTSIDE of previous click
        $('.bbpc-search-keyword ul li a').on('click', function (e) {
            e.preventDefault();
            const content = $(this).text();
            const ajax_url = bbpc_localize_script.ajaxurl;
            $('#searchInput').val(content).focus();

            if (content !== '') {
                $.ajax({
                    url: ajax_url,
                    method: 'POST',
                    data: {
                        action: 'bbpc_search_data_fetch',
                        keyword: content
                    },
                    beforeSend: function () {
                        $('.spinner').show();
                    },
                    success: function (data) {
                        $('#bbpc-search-result').html(data).addClass('ajax-search');
                        $('.spinner').hide();

                        const no_result = $('.tab-item.active.all-active').attr('data-noresult');
                        if (no_result) {
                            const msg = no_result.replace(/-/g, ' ');
                            $('#bbpc-search-result').html('<h5 class="bbpc-not-found-text">' + msg + '</h5>');
                            $('.bbpc-not-found-text').show();
                        }
                    }
                });
            }
        });

        // Clear search input
        $('#searchInput').on('input', function () {
            if (this.value === '') {
                $('#bbpc-search-result').removeClass('ajax-search');
            }
        });

        // Overlay behavior
        $("#searchInput").on('focus', function () {
            $('body').addClass('bbpc-search-overlay');
            $('form.bbpc_search_form_wrapper').css('z-index', '999');
        });

        $(".bbpc-search-overlay").on('click', function () {
            $('body').removeClass('bbpc-search-overlay');
            $('form.bbpc_search_form_wrapper').css('z-index', 'unset');
        });
    });
})(jQuery);
