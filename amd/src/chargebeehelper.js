/* jshint ignore:start */
define(['jquery','core/log','core/ajax',], function($, log, ajax, chargebee) {

    "use strict";
    log.debug('cbhelper: initialising');

    return {

        instanceprops: null,
        changeplanclass: '',

        //pass in config, and register any events
        init: function(props){
            log.debug(props);
            this.instanceprops=props;
            this.changeplanclass=props.changeplanclass;
            this.register_events();

        },

        register_events: function() {

            $.getScript('https://js.chargebee.com/v2/chargebee.js', function(){
                var chargebee = Chargebee.getInstance();
                $("." + this.changeplanclass).on("click", function() {
                    event.preventDefault();
                    chargebee.openCheckout({
                        hostedPage: function() {
                            // Hit your end point that returns hosted page object as response
                            // This sample end point will call checkout new api
                            // https://apidocs.chargebee.com/docs/api/hosted_pages#checkout_new_subscription
                            // If you want to use paypal, go cardless and plaid, pass embed parameter as false
                            // Now we can continue...
                            var promises = ajax.call([{
                                methodname: 'block_poodllclassroom_get_checkout_existing',
                                args: {}
                            }]);
                            return promises[0];
                            /*
                             return $.ajax({
                                 method: "post",
                                 url: "http://localhost:8000/api/generate_checkout_existing_url"
                             });
                             */
                        },
                        loaded: function() {
                            console.log("checkout opened");
                        },
                        error: function() {
                        },
                        close: function() {
                            console.log("checkout closed");
                        },
                        success: function(hostedPageId) {
                            console.log(hostedPageId);
                            // Hosted page id will be unique token for the checkout that happened
                            // You can pass this hosted page id to your backend
                            // and then call our retrieve hosted page api to get subscription details
                            // https://apidocs.chargebee.com/docs/api/hosted_pages#retrieve_a_hosted_page
                        },
                        step: function(value) {
                            // value -> which step in checkout
                            console.log(value);
                        }
                    });
                });//on click

            });//end of get script



        }//end of reg events
    };//end of returned object
});//total end

