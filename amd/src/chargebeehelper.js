/* jshint ignore:start */
define(['jquery','core/log','core/ajax'], function($, log, ajax) {

    "use strict";
    log.debug('cbhelper: initialising');

    return {

        instanceprops: null,
        changeplanclass: '',
        siteprefix: '',
        controls: {},

        //pass in config, and register any events
        init: function(props){
            log.debug(props);
            this.instanceprops=props;
            this.changeplanclass=props.changeplanclass;
            this.siteprefix=props.siteprefix;
            this.registercontrols();
            this.registerevents();
        },

        registercontrols: function(){
            this.controls.togglebutton = $('a.monthlyyearly');
            this.controls.monthlyplans = $('div.monthly');
            this.controls.yearlyplans = $('div.yearly');
        },

        registerevents: function() {
            var that = this;

            //set up toggle button
            this.controls.togglebutton.on('click',function(){
                that.controls.monthlyplans.toggle();
                that.controls.yearlyplans.toggle();
            });

            //set up checkout links
            $.getScript('https://js.chargebee.com/v2/chargebee.js', function(){
                var chargebee = Chargebee.init({'site': that.siteprefix});
                $("." + that.changeplanclass).on("click", function() {
                    event.preventDefault();
                    var planid = $(this).data('planid');
                    chargebee.openCheckout({
                        hostedPage: function() {
                            // Hit your end point that returns hosted page object as response
                            // This sample end point will call checkout new api
                            // https://apidocs.chargebee.com/docs/api/hosted_pages#checkout_new_subscription
                            // If you want to use paypal, go cardless and plaid, pass embed parameter as false
                            // Now we can continue...
                            var promises = ajax.call([{
                                methodname: 'block_poodllclassroom_get_checkout_existing',
                                args: {planid: planid}
                            }]);
                            return promises[0];
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

