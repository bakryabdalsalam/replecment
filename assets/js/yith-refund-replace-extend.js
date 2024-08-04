jQuery(document).ready(function($) {
    $('input[name="refund_or_replace"]').change(function() {
        if ($(this).val() === 'replace') {
            // Handle additional logic for replacement if needed
        }
    });
});
