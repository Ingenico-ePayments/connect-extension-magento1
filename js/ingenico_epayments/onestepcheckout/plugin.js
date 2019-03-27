document.addEventListener("DOMContentLoaded", function(event) {
    /**
     * OneStepCheckout does not call window.payment.save().
     * Therefore, we hook into the OSC form submit function to do that.
     */
    let form = $('onestepcheckout-form');
    form.submit = form.submit.wrap(function(submit){
        Promise.resolve(window.payment.save()).then(function() {
            submit();
        }, function(error) {
            console.warn(error);
            submit();
        });
    });
});
