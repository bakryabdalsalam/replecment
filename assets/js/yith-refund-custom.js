jQuery(document).ready(function($) {
    $(document.body).on('ywcars_request_window_created', function() {
        if ($('#ywcars_refund_type').length === 0) {
            var refundTypeHtml = '<div class="ywcars_block" id="ywcars_refund_type">' +
                                 '<label for="ywcars_refund_type">Choose Request Type:</label><br>' +
                                 '<label><input type="radio" name="ywcars_refund_type_choice" value="refund" checked> Refund</label><br>' +
                                 '<label><input type="radio" name="ywcars_refund_type_choice" value="replacement"> Replacement</label>' +
                                 '</div>';
            $('#ywcars_form').prepend(refundTypeHtml);
        }

        if ($('#ywcars_hidden_refund_type').length === 0) {
            $('#ywcars_form').append('<input type="hidden" id="ywcars_hidden_refund_type" name="ywcars_hidden_refund_type" value="refund">');
        }

        $('input[name="ywcars_refund_type_choice"]').on('change', function() {
            var selectedValue = $(this).val();
            $('#ywcars_hidden_refund_type').val(selectedValue);
        });

        $('#ywcars_form').on('submit', function(e) {
            e.preventDefault();

            var refundReplaceChoice = $('#ywcars_hidden_refund_type').val();
            var orderId = $('input[name="ywcars_form_order_id"]').val();

            var formData = new FormData(this);
            formData.append('action', 'yith_custom_refund_replace'); // Add this line to specify the action
            formData.append('request_type', refundReplaceChoice);
            formData.append('nonce', ywcars_params.nonce);

            $.ajax({
                url: ywcars_params.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert('There was an error processing your request.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });
    });
});
