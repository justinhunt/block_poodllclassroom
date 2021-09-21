/* jshint ignore:start */
define(['jquery','core/log','block_poodllclassroom/clipboard'], function($, log, clipboard) {

    "use strict";
    log.debug('clipboard helper: initialising');

    return {

        //pass in config, and register any events
        init: function(props){
            this.registerevents();
        },

        registerevents: function() {
            var that = this;
            var cj = new clipboard('.poodllclassroom_clipboardbutton');

            cj.on('success', function (e) {

                var copied = $(e.trigger).parent().parent().find('.bpc_copied');
                copied.show();

              //  e.clearSelection();
            });


        }//end of reg events
    };//end of returned object
});//total end

