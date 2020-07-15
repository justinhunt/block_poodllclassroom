define(['jquery','core/log'], function($,log) {
    "use strict"; // jshint ;_;

    log.debug('block_poodllclassroom dialogs: initialising');
    //any functions from here can be used : http://fancyapps.com/fancybox/3/docs/#api
    //just wrap them here to make it more convenient to call from elsewhere in the mod

    return {
        openModal: function (selector){
            $(selector).modal('show');
        },
        closeModal: function (selector){
            $(selector).modal('hide');
        }
    };//end of return object
});