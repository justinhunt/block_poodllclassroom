/* jshint ignore:start */
define(['jquery','core/log','block_poodllclassroom/clipboard'], function($, log, ajax,clipboard) {

    "use strict";
    log.debug('clipboard helper: initialising');

    return {

        //pass in config, and register any events
        init: function(props){
            this.registerevents();
        },

        registerevents: function() {
            var that = this;
            debugger;
            var cj = new clipboard('.poodllclassroom_clipboardbutton');
        }//end of reg events
    };//end of returned object
});//total end

