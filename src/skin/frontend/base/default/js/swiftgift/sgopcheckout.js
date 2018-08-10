(function() {

    function group_elems_set_checked(elems, name, value) {
        elems.each(function(elem) {
            var elem = $(elem);
            elem.checked = (elem.name === name && elem.value === value);
        });
    }

    function group_elems_get_checked(elems) {
        var result = null;
        elems.each(function(elem) {
            var elem = $(elem);
            if (elem.checked) {
                result = elem;
                throw $break;
            }
        });
        return result;
    }
    
    function show_shipping_var(shipping_vars_elems, value) {
        var show_name = (value === 'swift_gift' ? 'swift_gift' : 'default');
        shipping_vars_elems.each(function(elem) {
            var elem = $(elem);
            if (elem.readAttribute('data-name') === show_name) {
                elem.show();
            } else {
                elem.hide();
            }
        });
    }
    $(document).on('dom:loaded', function(e) {
        var use_for_shipping_name = 'billing[use_for_shipping]';
        var swift_gift_used_name = 'swift_gift_used';
        var swift_gift_used_input = $($$('input[name="'+swift_gift_used_name+'"]')[0]);
        var billing_form = $('co-billing-form');
        
        var sg_shipping_vars = $$('.sg-var');
        var shipping_inputs_elems = $$('input[name="'+use_for_shipping_name+'"]');
        shipping_inputs_elems.push(swift_gift_used_input);
        billing_form.on('change', function(e) {
            var elem = $(e.target);
            if (elem.name === use_for_shipping_name || elem.name === swift_gift_used_name) {
                group_elems_set_checked(shipping_inputs_elems, elem.name, elem.value);
                show_shipping_var(sg_shipping_vars, elem.value);
            }
        });
        if (swift_gift_used_input.checked) {
            group_elems_set_checked(shipping_inputs_elems, swift_gift_used_input.name, swift_gift_used_input.value);
        }
        var elem = group_elems_get_checked(shipping_inputs_elems);
        var value = (elem === null ? '1' : (elem.name === 'swift_gift_used' ? 'swift_gift': elem.value));
        show_shipping_var(sg_shipping_vars, value);

        var sg_info_form = $('sg-info-form');
        if (sg_info_form) {
            var validation = new Validation(sg_info_form);
            sg_info_form.on('submit', function(e) {
                validation.reset();
                if (validation.validate()) {
                    checkout.setLoadWaiting('sg', true);
                    new Ajax.Request(
                        sg_info_form.readAttribute('action'),
                        {
                            method:'post',
                            parameters: Form.serialize(sg_info_form),
                            onSuccess: function(transport) {
                                var response = transport.responseJSON || transport.responseText.evalJSON(true) || {};
                                if (response.success) {
                                    checkout.setLoadWaiting('shipping');
                                    new Ajax.Request(
                                        '/sggift/onepage/getShippingMethods',
                                        {
                                            method:'get',
                                            onSuccess: function(r) {
                                                response.goto_section = 'shipping_method';
                                                response.update_section = {
                                                    'name': 'shipping-method',
                                                    'html': r.responseText
                                                };
                                                checkout.setStepResponse(response);
                                            },
                                            onFailure: checkout.ajaxFailure.bind(checkout),
                                            onComplete: function() {
                                                checkout.setLoadWaiting(false);
                                            }
                                        }
                                    );
                                }
                            },
                            onFailure: checkout.ajaxFailure.bind(checkout),
                            onComplete: function() {
                                checkout.setLoadWaiting(false);
                            }
                        }
                    );
                }
                Event.stop(e);
            });
        }       
    });
})();
