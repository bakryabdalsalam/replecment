jQuery(document).ready(function($) {
    $('#yith-refund-form').on('submit', function(e) {
        e.preventDefault();

        var refundReplaceChoice = $('#refund_replace_choice').val();
        var orderId = $('#order_id').val(); // Assuming there's an order ID field in the form

        $.ajax({
            url: yithRefundCustom.ajax_url,
            type: 'POST',
            data: {
                action: 'yith_custom_refund_replace',
                refund_replace_choice: refundReplaceChoice,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                } else {
                    alert('There was an error processing your request.');
                }
            }
        });
    });
});
