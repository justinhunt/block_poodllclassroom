define(['jquery','core/config','core/log','core/ajax','core/templates','core/modal_factory','core/str','core/modal_events',
        'mod_cpassignment/dialogs','core/notification'],
    function($,cfg,log,Ajax, templates, ModalFactory, str, ModalEvents,  dialogs, notification) {
    "use strict"; // jshint ;_;

    log.debug('blockcontroller: initialising');

    return {
        controls: {},
        modulecssclass: null,
        strings: [],


        init: function(props){
            this.modulecssclass = props.modulecssclass;
            this.prepare_html();
            this.register_events();
        },

        init_strings: function(){
            var that = this;
           var strings=['createcourse'];
           for(var i=0; i<strings.length; i++) {
               str.get_string(strings[i],'block_poodllclassroom').then(function (stringdata) {
                   that.strings[i]=stringdata;
               });
           }
        },

        prepare_html: function(){
            this.controls.createcoursestartbutton = $('#' + this.modulecssclass + '_createcourse_btn');
            this.controls.createcoursestartcontainer = $('#' + this.modulecssclass +'_createcourse_cnt');
            this.controls.createcoursestartbutton.show();

        },

        register_events: function(){
            var that =this;

            //recorder dialog show link
            this.controls.createcoursestartbutton.click(function(){
                //clear fields
                /*
                that.controls.itemfilenamefield.val("");
                that.controls.itemidfield.val("");
                that.controls.itemnamefield.val("");
                that.controls.itemsubidfield.val("0");
                */
                dialogs.openModal('#' + that.modulecssclass + '_createcourse_cnt');
            });



            //download links
            /*
            this.controls.rectable.on('click','a[data-type="download"]',function(e){
                    var clickedLink = $(e.currentTarget);
                    var elementid = clickedLink.data('id');
                    that.show_download(that, elementid);
                    return false;
            });
            */

            //delete linkc
            /*
            this.controls.rectable.on('click','a[data-type="delete"]',function(e){
                        var clickedLink = $(e.currentTarget);
                        var elementid = clickedLink.data('id');
                        var audiotitle = $('td.itemname span[data-itemid="'+ elementid+ '"]').data('value');
                        ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: 'Delete Media',
                            body: 'Do you really want to delete audio? <i>' + audiotitle + '</i>',
                        })
                            .then(function(modal) {
                                modal.setSaveButtonText('DELETE');
                                var root = modal.getRoot();
                                root.on(ModalEvents.save, function() {
                                    that.controls.thedatatable.row( clickedLink.parents('tr')).remove().draw();
                                    var itemcount = that.controls.thedatatable.rows().count();
                                    if(!itemcount){
                                        that.controls.noitemscontainer.show();
                                        that.controls.itemscontainer.hide();
                                    }
                                    that.do_delete(elementid);
                                    that.check_item_count(that);
                                });
                                modal.show();
                            });
                        return false;
            });
            */
        }, //en of reg events



        do_resetkey: function(that, moduleid){

            Ajax.call([{
                methodname: 'mod_cpassignment_reset_key',
                args: {
                    moduleid: moduleid,
                },
                done: function (ajaxresult) {
                    var payloadobject = JSON.parse(ajaxresult);
                    if (payloadobject) {
                        switch(payloadobject.success) {
                            case true:
                                var accesskey = payloadobject.message;
                                that.controls.sharebox.val(cfg.wwwroot + '/mod/cpassignment/k.php?k=' + accesskey);
                                break;

                            case false:
                            default:
                                if (payloadobject.message) {
                                    log.debug('message: ' + payloadobject.message);
                                }
                        }
                    }
                },
                fail: notification.exception
            }]);

        },

        do_delete: function(itemid){

            Ajax.call([{
                methodname: 'mod_cpassignment_remove_rec',
                args: {
                    itemid: itemid,
                },
                done: function (ajaxresult) {
                    var payloadobject = JSON.parse(ajaxresult);
                    if (payloadobject) {
                        switch(payloadobject.success) {
                            case true:
                                //all good do nothing
                                break;

                            case false:
                            default:
                                if (payloadobject.message) {
                                    log.debug('message: ' + payloadobject.message);
                                }
                        }
                    }
                },
                fail: notification.exception
            }]);

        },



        insert_new_item: function(that,item){
            that.controls.noitemscontainer.hide();
            that.controls.itemscontainer.show();
            templates.render('mod_cpassignment/itemrow',item).then(
                function(html,js){
                    that.controls.thedatatable.row.add($(html)[0]).draw();
                }
            );
        },



        send_submission: function(subid,filename, itemid, itemname ){
            var that=this;
            var args = {
                    subid: subid,
                    filename: filename,
                    itemname: itemname,
                    itemid: itemid,
                    cmid: that.cmid
                };
            if(this.authmode==='guest'){
                args.accesskey=that.accesskey;
            }else{
                args.accesskey='none';
            }

            Ajax.call([{
                methodname: 'mod_cpassignment_submit_rec',
                args: args,
                done: function (ajaxresult) {
                    var payloadobject = JSON.parse(ajaxresult);
                    if (payloadobject) {
                        switch(payloadobject.success) {
                            case true:
                                var item = payloadobject.item;
                                if(that.authmode==='guest'){
                                    that.acknowledge_receipt(that,item);
                                }else{
                                    that.insert_new_item(that,item);
                                    that.check_item_count(that);
                                }

                                dialogs.closeModal('#' + that.modulecssclass + '_arec_container');
                                that.re_init_recorder(that,that.audiorecid);
                                break;

                            case false:
                            default:
                                if (payloadobject.message) {
                                    log.debug('message: ' + payloadobject.message);
                                }
                                dialogs.closeModal('#' + that.modulecssclass + '_arec_container');
                                that.clear_recorder();
                                that.re_init_recorder(that.audiorecid);

                        }
                    }
                },
                fail: notification.exception
            }]);

        },
    };//end of return object

});