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
            //log.debug(props);
            this.instanceprops=props;
            this.changeplanclass=props.changeplanclass;
            this.siteprefix=props.siteprefix;
            this.gocbcheckoutclass = props.gocbcheckoutclass;
            this.gocbmanageclass = props.gocbmanageclass;
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
                event.preventDefault();
                that.controls.monthlyplans.toggleClass('block_poodllclassroom_hidden');
                that.controls.yearlyplans.toggleClass('block_poodllclassroom_hidden');
            });

            //set up checkout links
            $.getScript('https://js.chargebee.com/v2/chargebee.js', function(){
                var chargebee = Chargebee.init({'site': that.siteprefix});

                //checkout pop open
                $("." + that.gocbcheckoutclass).on("click", function() {
                    event.preventDefault();
                    var clickedthis = this;
                    var currency = $(this).data('currency');
                    var billinginterval = $(this).data('billinginterval');
                    var planid = $(this).data('planid');
                    var schoolid = $(this).data('schoolid');
                    var currentsubid = $(this).data('currentsubid');
                    var method = $(this).data('method');//'get_checkout_existing' or ''
                    chargebee.openCheckout({
                        hostedPage: function() {
                            // Hit the end point that returns hosted page object as response
                            // serverside we generate a hosted page object , and that auth's and provides deets for CB
                            // https://apidocs.chargebee.com/docs/api/hosted_pages#checkout_new_subscription
                            var promises = ajax.call([{
                                methodname: 'block_poodllclassroom_' + method,
                                args: {planid: planid, currency: currency, billinginterval: billinginterval, schoolid: schoolid, currentsubid: currentsubid }
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
                });//on checkout click

                //on manage subscription click
                $("[data-cbaction='ssp']").on("click", function() {
                    event.preventDefault();
                    var clickedthis = this;
                    var upstreamownerid = $(this).data('upstreamownerid');
                    var upstreamsubid = $(this).data('upstreamsubid');
                    switch($(this).data('type')){
                        case 'paymentsources':
                            var sectiontype = Chargebee.getPortalSections().PAYMENT_SOURCES;
                            break;

                        case 'billinghistory':
                            var sectiontype = Chargebee.getPortalSections().BILLING_HISTORY;
                            break;

                        case 'subdetails':
                            var sectiontype = Chargebee.getPortalSections().SUBSCRIPTION_DETAILS;
                            break;

                        case 'billingaccount':
                        default:
                            var sectiontype = false;

                    }
                    //set the portal session within chargebee
                    chargebee.setPortalSession(function(){
                        var promises = ajax.call([{
                            methodname: 'block_poodllclassroom_create_portal_session',
                            args: {upstreamownerid: upstreamownerid}
                        }]);
                        return promises[0];
                    });

                    //create the portal;
                    var cbPortal = chargebee.createChargebeePortal();

                    var callbacks ={
                        loaded: function() {
                            console.log("manage portal opened");
                        },

                        close: function() {
                            console.log("manage portal closed");
                        },
                        visit: function(sectionType) {
                            console.log("manage portal visit");
                        },
                        paymentSourceAdd: function() {
                            console.log("manage portal PSA");
                        },
                        paymentSourceUpdate: function() {
                            console.log("manage portal PSU");
                        },
                        paymentSourceRemove: function() {
                            console.log("manage portal PSR");
                        },
                        subscriptionChanged: function(data) {
                            console.log("manage portal sc");
                        },
                        subscriptionCustomFieldsChanged: function(data) {
                            console.log("manage portal scfc");
                        },
                        subscriptionCancelled: function(data) {
                            console.log("manage portal sub cancelled");
                        },
                        subscriptionPaused: function(data) {
                            console.log("manage portal sub paused");
                        },
                        subscriptionPauseRemoved: function(data) {
                            console.log("manage portal sub pause removed");
                        },
                        subscriptionResumed: function(data) {
                            console.log("manage portal sub resumed");
                        },
                        subscriptionReactivated: function(data) {
                            console.log("manage portal sub reactivated");
                        }
                    };
                    if(sectiontype!==false) {
                        var opts ={
                            sectionType: sectiontype,
                            params: {
                                //only meaningful if we are managing a subscription
                                subscriptionId: upstreamsubid
                            }
                        }
                        cbPortal.openSection(opts, callbacks);
                    }else{
                        var forwardOptions={sectionType: Chargebee.getPortalSections().ACCOUNT_DETAILS};
                       // cbPortal.open(callbacks,forwardOptions);
                        cbPortal.open(callbacks);
                    }

                });//on manage subscription click

            });//end of get script

        }//end of reg events
    };//end of returned object
});//total end

