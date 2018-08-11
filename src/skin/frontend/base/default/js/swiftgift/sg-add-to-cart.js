(function() {
    document.observe("dom:loaded", function() {
        var submitParent = productAddToCartForm.submit.bind({});
        var formElem = $('product_addtocart_form');
        var formDefaultAction = formElem.readAttribute('action');
        productAddToCartForm.submit = function(button, url) {
            var actionUrl = $(button).readAttribute('data-action-url');
            formElem.writeAttribute('action', actionUrl ? actionUrl : formDefaultAction);
            return submitParent.call(this, [button, url]);
        }.bind(productAddToCartForm);
    });
})();
