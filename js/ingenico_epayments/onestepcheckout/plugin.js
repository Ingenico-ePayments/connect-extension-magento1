document.addEventListener("DOMContentLoaded", function(event) {
    /**
     * OneStepCheckout does not call window.payment.save().
     * Therefore, we hook into the OSC form submit function to do that.
     */
    let form = $('onestepcheckout-form');
    form.submit = form.submit.wrap(function(submit){
        window.payment.save().then(function() {
            submit();
        }, function(error) {
            console.warn(error);
            submit();
        })

    });

    /**
     * Call ePayments controller action after reloading payment methods.
     * Use recursion to wrap new DOM elements' replace method as well.
     */
    (function wrapPaymentMethodReplace() {
        let paymentMethods = $$('div.payment-methods')[0];
        paymentMethods.replace = paymentMethods.replace.wrap(
            function(replace, data) {
                replace(data);
                window.epaymentsController.execute();
                wrapPaymentMethodReplace()
            }
        );
    })()

});
