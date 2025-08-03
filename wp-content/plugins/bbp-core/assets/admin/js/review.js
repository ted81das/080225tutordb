jQuery(document).ready(function () {

    // Show review popup
    jQuery("#bbpc_notify_review a").on("click", function () {
        const thisElement = this;
        const fieldValue = jQuery(thisElement).attr("data");
        const freeLink = "https://wordpress.org/support/plugin/bbp-core/reviews/#new-post";
        let hidePopup = false;
        if (fieldValue == "rateNow") {
            window.open(freeLink, "_blank");
        } else {
            hidePopup = true;
        }

        jQuery
        .ajax({
          dataType: 'json',
          url: bbp_core_local_object.ajaxurl,
          type: "post",
          data: {
            action: "bbpc_notify_save_review",
            field: fieldValue,
            nonce: bbp_core_local_object.nonce,
          },
        })
        .done(function (result) {
            if (hidePopup == true) {
                jQuery( "#bbpc_notify_review .notice-dismiss" ).trigger( "click" );
            }
        })
        .fail(function (res) {
            if (hidePopup == true) {
                console.log(res.responseText);
                jQuery( "#bbpc_notify_review .notice-dismiss" ).trigger( "click" );
            }
        });
    })
})