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
        schoolinfo: {},


        init: function(props){
            //pick up opts from html
            var optscontrol = $('#' + props.id).get(0);
            if (optscontrol) {
                var opts = JSON.parse(optscontrol.value);
                $(optscontrol).remove();
            } else {
                //if there is no config we might as well give up
                log.debug('Poodll classroom Controller: No config found on page. Giving up.');
                return;
            }

            this.modulecssclass = opts.modulecssclass;
            this.contextid = opts.contextid;
            this.tableid = opts.tableid;
            this.schoolinfo = opts.schoolinfo;
            this.schoolplan = opts.schoolplan;
            this.prepare_html();
            this.register_events();
            this.check_user_count();
            this.check_course_count();
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
            this.controls.uploaduserstartbutton = $('#' + this.modulecssclass + '_uploaduser_btn');
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
            var after_useradd= function(item, itemid) {
                log.debug('after user add');
                log.debug(item);
                item.id=itemid;
                item.lastaccess = '--:--';
                templates.render('block_poodllclassroom/userlistrow',item).then(
                    function(html,js){
                        that.controls.theusertable.row.add($(html)[0]).draw();
                        //can we now add users where before we could not?
                        that.check_user_count();
                    }
                );
                that.controls.nouserscontainer.hide();
                that.controls.userscontainer.show();

            };
            var after_userupload= function() {
                log.debug('after user upload');
                //Its cheating a bit, but lets just reload
                document.location.reload();
            };
            var after_courseadd= function(item, itemid) {
                log.debug('after course add');
                log.debug(item);
                item.id = itemid;
                item.wwwroot = cfg.wwwroot;
                item.coursename = item.fullname;
                templates.render('block_poodllclassroom/courseitem',item).then(
                    function(html,js){
                        that.controls.coursescontainer.append($(html)[0]);
                        //can we now add users where before we could not?
                        that.check_course_count();
                    }
                );

                that.controls.nocoursescontainer.addClass('block_poodllclassroom_hidden');
                that.controls.coursescontainer.removeClass('block_poodllclassroom_hidden');

            };
            var after_useredit= function(item, itemid) {
                var therow = '#' + that.modulecssclass + '_user_row_' + itemid;
                //c0 = firstname c1= lastname c2=lastaccess
                that.controls.theusertable.cell($(therow + ' .c0')).data(item.firstname);
                that.controls.theusertable.cell($(therow + ' .c1')).data(item.lastname);
            };
            var after_courseedit= function(item, itemid) {
                var tileselector = '#' + that.modulecssclass + '_courseitem_' + itemid;
                var coursenameselector = '.' + that.modulecssclass + '_coursename';
                $(tileselector + ' ' + coursenameselector).text(item.fullname);
            };


            //modal delete helper
            var after_coursedelete= function(itemid) {
                log.debug('after course delete');
                $('#' + that.modulecssclass + '_courseitem_' + itemid).remove();
                var itemcount = $('div.' + that.modulecssclass + '_courseitem').length;
                if(!itemcount){
                    that.controls.nocoursescontainer.removeClass('block_poodllclassroom_hidden');
                    that.controls.coursescontainer.addClass('block_poodllclassroom_hidden');
                }
                //can we now add courses where before we could not?
                that.check_course_count();
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
                that.check_user_count();
            };

            //form helper
            mfh.init('#' + this.modulecssclass + '_createuser_btn', this.contextid, 'createuser',after_useradd);
            mfh.init('#' + this.modulecssclass + '_uploaduser_btn', this.contextid, 'uploaduser',after_userupload);
            mfh.init('#' + this.modulecssclass + '_createcourse_btn', this.contextid, 'createcourse', after_courseadd);
            mfh.init('.' + this.modulecssclass + '_usereditlink', this.contextid, 'edituser', after_useredit);
            mfh.init('.' + this.modulecssclass + '_courseeditlink', this.contextid, 'editcourse', after_courseedit);

            //delete helper
            mdh.init('.' + this.modulecssclass + '_coursedeletelink', this.contextid, 'deletecourse',after_coursedelete);
            mdh.init('.' + this.modulecssclass + '_userdeletelink', this.contextid, 'deleteuser',after_userdelete);

        }, //en of reg events

        check_course_count: function(){
            var coursecount = $('.' + this.modulecssclass + '_courseitem').length;
            if(this.schoolplan.maxcourses > coursecount) {
                this.set_enabled(this.controls.createcoursestartbutton,true);
            }else{
                this.set_enabled(this.controls.createcoursestartbutton,false);
            }
        },

        check_user_count: function(){
            var usercount = $('.' + this.modulecssclass + '_user_row').length;
            if(this.schoolplan.maxusers > usercount) {
                this.set_enabled(this.controls.createuserstartbutton,true);
                this.set_enabled(this.controls.uploaduserstartbutton,true);
            }else{
                this.set_enabled(this.controls.createuserstartbutton,false);
                this.set_enabled(this.controls.uploaduserstartbutton,false);
            }
        },

        set_enabled: function (elem, enabled){
            switch(enabled){
                case true:
                    elem.removeClass('disabled');
                    elem.attr('aria-disabled','false');
                    elem.attr('tabindex','0');
                    break;
                case false:
                    elem.addClass('disabled');
                    elem.attr('aria-disabled','true');
                    elem.attr('tabindex','-1');
            }
        },

        format_date: function(timestamp) {
                var d = new Date(timestamp * 1000);
               var month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2)
                month = '0' + month;
            if (day.length < 2)
                day = '0' + day;

            return [year, month, day].join('-');
        }
    };//end of return object

});