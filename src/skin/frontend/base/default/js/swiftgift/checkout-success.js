(function() {
    $(document).on('dom:loaded', function() {
        var sg_ml_btn = $$('.swiftgift-checkout-magiclink-button');
        var sg_ml_input = $$('.swiftgift-checkout-magiclink-input');
        if (sg_ml_btn.length > 0 && sg_ml_input.length > 0 && $(sg_ml_input[0]).value) {
            var cbjs = new ClipboardJS('.swiftgift-checkout-magiclink-button', {
                text: function(t) {
                    return $(sg_ml_input[0]).value;
                }
            });
            cbjs.on('success', function(e) {
                alert('Magic link copied to clipboard.');
            });
        }        
    });
})();
