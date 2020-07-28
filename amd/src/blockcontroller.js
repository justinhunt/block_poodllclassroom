define(['jquery','core/config','core/log','core/ajax','core/templates','core/modal_factory','core/str','core/modal_events',
    'block_poodllclassroom/dialogs','block_poodllclassroom/datatables','block_poodllclassroom/modalformhelper','block_poodllclassroom/modaldeletehelper','core/notification'],
    function($,cfg,log,Ajax, templates, ModalFactory, str, ModalEvents,  dialogs, datatables, mfh, mdh, notification) {
    "use strict"; // jshint ;_;

    log.debug('blockcontroller: initialising');

    return {
        controls: {},
        modulecssclass: null,
        contextid: 0,
        strings: [],


        init: function(props){
            this.modulecssclass = props.modulecssclass;
            this.contextid = props.contextid;
            this.tableid = props.tableid;
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
            this.controls.createuserstartbutton = $('#' + this.modulecssclass + '_createuser_btn');
            this.controls.createcoursestartbutton = $('#' + this.modulecssclass + '_createcourse_btn');
            this.controls.createcoursestartcontainer = $('#' + this.modulecssclass +'_createcourse_cnt');
            this.controls.nocoursescontainer = $('.' + this.modulecssclass + '_nocourses_cont');
            this.controls.coursescontainer = $('.' + this.modulecssclass + '_courselist_cont');
            this.controls.nouserscontainer = $('.' + this.modulecssclass + '_nousers_cont');
            this.controls.userscontainer = $('.' + this.modulecssclass + '_userlist_cont');
            this.controls.theusertable = datatables.getDataTable(this.tableid);
            this.controls.createcoursestartbutton.show();
            log.debug(this.controls);

        },

        register_events: function(){
            var that =this;
            //modal form helper
            mfh.init('#' + this.modulecssclass + '_createuser_btn', this.contextid, 'createuser');
            mfh.init('#' + this.modulecssclass + '_createcourse_btn', this.contextid, 'createcourse');
            mfh.init('.' + this.modulecssclass + '_usereditlink', this.contextid, 'edituser');
            mfh.init('.' + this.modulecssclass + '_courseeditlink', this.contextid, 'editcourse');

            //modal delete helper
            var after_coursedelete= function(itemid) {
                log.debug('after course delete');
                $('#' + that.modulecssclass + '_courseitem_' + itemid).remove();
                var itemcount = $('div.' + that.modulecssclass + '_courseitem').length;
                if(!itemcount){
                    that.controls.nocoursescontainer.show();
                    that.controls.coursescontainer.hide();
                }
                //can we now add courses where before we could not?
                //that.check_course_count(that);
            };
            var after_userdelete= function(itemid) {
                log.debug('after user delete');
                that.controls.theusertable.row('#' + that.modulecssclass + '_user_row_' + itemid).remove().draw();
                var itemcount = that.controls.theusertable.rows().count();
                if(!itemcount){
                    that.controls.nouserscontainer.show();
                    that.controls.userscontainer.hide();
                }
                //can we now add users where before we could not?
               // that.check_user_count(that);
            };
            mdh.init('.' + this.modulecssclass + '_coursedeletelink', this.contextid, 'deletecourse',after_coursedelete);
            mdh.init('.' + this.modulecssclass + '_userdeletelink', this.contextid, 'deleteuser',after_userdelete);

            //modal dialog show link
            /*
            this.controls.createcoursestartbutton.click(function(){
                dialogs.openModal('#' + that.modulecssclass + '_createcourse_cnt');
                log.debug('opened modal');
                return false;
            });

            */


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
                methodname: 'block_poodllclassroom_reset_key',
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
                methodname: 'block_poodllclassroom_remove_rec',
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
            templates.render('block_poodllclassroom/userlistrow',item).then(
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
                methodname: 'block_poodllclassroom_submit_rec',
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