/**
 * Add a create new group modal to the page.
 *
 * @module     block_poodllclassroom/modalformhelper
 * @class      modalformhelper
 * @package    block_poodllclassroom
 * @copyright  2020 Justin Hunt <poodllsupport@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'core/str', 'core/modal_factory', 'core/modal_events','core/fragment', 'core/ajax', 'core/yui'],
    function($, log, Str, ModalFactory, ModalEvents, Fragment, Ajax, Y) {

        /**
         * Constructor
         *
         * @param {String} selector used to find triggers for the new group modal.
         * @param {int} contextid
         * @param {String} formname The key/name of the form for this instance
         * @param {Object} callback The function to call after successful deletion (for UI updates)
         *
         * Each call to init gets it's own instance of this class.
         */
        var TheForm = function(selector, contextid, formname,callback) {
            this.contextid = contextid;
            this.formname = formname;
            this.callback = callback;

            //this will init on page load (good if just one or two items)
            //this.init(selector);

            //this will init on item click (better for lots of items)
            this.preinit(selector);
        };

        /**
         * @var {Modal} modal
         * @private
         */
        TheForm.prototype.modal = null;

        /**
         * @var {int} contextid
         * @private
         */
        TheForm.prototype.contextid = -1;

        /**
         * @var {int} itemid
         * @private
         */
        TheForm.prototype.itemid = -1;

        /**
         * @var {string} formname
         * @private
         */
        TheForm.prototype.formname = '';

        /**
         * Initialise the class.
         *
         * @param {String} selector used to find triggers for the new group modal.
         * @private
         * @return {Promise}
         */
        TheForm.prototype.init = function(selector) {
            var triggers = $(selector);
            // Fetch the title string.
            return Str.get_string(this.formname , 'block_poodllclassroom').then(function(title) {
                // Create the modal.
                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: this.getBody({})
                }, triggers);
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;

                // Forms are big, we want a big modal.
                this.modal.setLarge();

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.setBody(this.getBody({}));
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                this.modal.getRoot().on(ModalEvents.shown, function() {
                    this.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                }.bind(this));


                // We catch the modal save event, and use it to submit the form inside the modal.
                // Triggering a form submission will give JS validation scripts a chance to check for errors.
                this.modal.getRoot().on(ModalEvents.save, this.submitForm.bind(this));
                // We also catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));

                return this.modal;
            }.bind(this));
        };

        /**
         * Initialise the class.
         *
         * @param {String} selector used to find triggers for the new group modal.
         * @private
         * @return {Promise}
         */
        TheForm.prototype.preinit = function(selector) {
            var triggers = $(selector);
            var dd=this;
            Str.get_string(this.formname , 'block_poodllclassroom').then(function(title){dd.formtitle=title;});
            $('body').on('click',selector,function(e) {
                //prevent it doing a real click (which will do the non ajax version of a click)
                e.preventDefault();

                dd.itemid=$(this).data('id');

                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: dd.formtitle,
                    body: dd.getBody({})
                }).then(function (modal) {
                    // Keep a reference to the modal.
                    dd.modal = modal;

                    // Forms are big, we want a big modal.
                    dd.modal.setLarge();

                    // We want to reset the form every time it is opened.
                    dd.modal.getRoot().on(ModalEvents.hidden, function() {
                        dd.modal.setBody(dd.getBody({}));
                    }.bind(dd));

                    // We want to hide the submit buttons every time it is opened.
                    dd.modal.getRoot().on(ModalEvents.shown, function () {
                        dd.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                    });


                    // We catch the modal save event, and use it to submit the form inside the modal.
                    // Triggering a form submission will give JS validation scripts a chance to check for errors.
                    dd.modal.getRoot().on(ModalEvents.save, dd.submitForm.bind(dd));
                    // We also catch the form submit event and use it to submit the form with ajax.
                    dd.modal.getRoot().on('submit', 'form', dd.submitFormAjax.bind(dd));
                    dd.modal.show();
                    return dd.modal;
                });

            });//end of on click

        };

        /**
         * @method getBody
         * @private
         * @return {Promise}
         */
        TheForm.prototype.getBody = function(formdata) {
            if (typeof formdata === "undefined") {
                formdata = {};
            }

            // Get the content of the modal.
            var params = {jsonformdata: JSON.stringify(formdata), formname: this.formname, itemid: this.itemid};
            return Fragment.loadFragment('block_poodllclassroom', 'mform', this.contextid, params);
        };

        /**
         * @method handleFormSubmissionResponse
         * @private
         * @return {Promise}
         */
        TheForm.prototype.handleFormSubmissionResponse = function(formData,ajaxresult) {
      //      this.modal.hide();
      //      Y.use('moodle-core-formchangechecker', function() {
      //          M.core_formchangechecker.reset_form_dirty_state();
      //      });

            log.debug(ajaxresult); //this contains what the server returns (eg new item->id etc)
            log.debug(formData); //this contains the original form data

            var payloadobject = JSON.parse(ajaxresult);

            if (payloadobject) {
                log.debug(payloadobject);
                switch(payloadobject.error) {
                    case false:
                        this.modal.hide();
                        // We could trigger an event instead.
                        Y.use('moodle-core-formchangechecker', function() {
                            M.core_formchangechecker.reset_form_dirty_state();
                        });

                        //process formData
                        var dataobject = JSON.parse('{"' + decodeURI(formData).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');
                        this.callback(dataobject,payloadobject.itemid);
                        break;

                    case true:
                    default:
                        //if we had an error and this is upload user, alert user and reload failed rows
                        if(this.formname==='uploaduser') {
                            var thedata = formData.split('&');
                            var usedata ={};
                            for(var i=0; i<thedata.length; i++){
                                if(thedata[i].includes('importdata')){
                                    usedata['importdata'] = payloadobject.importdata;
                                }else{
                                    var pair = thedata[i].split('=');
                                    usedata[decodeURIComponent(pair[0])]=decodeURIComponent(pair[1]);
                                }
                            }
                            this.modal.setBody(this.getBody(usedata));
                            alert(payloadobject.message);
                        }else{
                            this.modal.hide();
                            // We could trigger an event instead.
                            Y.use('moodle-core-formchangechecker', function() {
                                M.core_formchangechecker.reset_form_dirty_state();
                            });
                        }
                        log.debug('that was an error: ');
                }
            }else{
                this.modal.hide();
                // We could trigger an event instead.
                Y.use('moodle-core-formchangechecker', function() {
                    M.core_formchangechecker.reset_form_dirty_state();
                });
            }



        };

        /**
         * @method handleFormSubmissionFailure
         * @private
         * @return {Promise}
         */
        TheForm.prototype.handleFormSubmissionFailure = function(data) {
            //  We need to re-display the form with errors!
            this.modal.setBody(this.getBody(data));
        };

        /**
         * Private method
         *
         * @method submitFormAjax
         * @private
         * @param {Event} e Form submission event.
         */
        TheForm.prototype.submitFormAjax = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();

            var changeEvent = document.createEvent('HTMLEvents');
            changeEvent.initEvent('change', true, true);

            // Prompt all inputs to run their validation functions.
            // Normally this would happen when the form is submitted, but
            // since we aren't submitting the form normally we need to run client side
            // validation.
            this.modal.getRoot().find(':input').each(function(index, element) {
                element.dispatchEvent(changeEvent);
            });

            // Now the change events have run, see if there are any "invalid" form fields.
            var invalid = $.merge(
                this.modal.getRoot().find('[aria-invalid="true"]'),
                this.modal.getRoot().find('.error')
            );

            // If we found invalid fields, focus on the first one and do not submit via ajax.
            if (invalid.length) {
                invalid.first().focus();
                return;
            }

            // Convert all the form elements values to a serialised string.
            var formData = this.modal.getRoot().find('form').serialize();

            // Now we can continue...
            Ajax.call([{
                methodname: 'block_poodllclassroom_submit_mform',
                args: {contextid: this.contextid, jsonformdata: JSON.stringify(formData), formname: this.formname},
                done: this.handleFormSubmissionResponse.bind(this, formData),
                fail: this.handleFormSubmissionFailure.bind(this, formData)
            }]);
        };

        /**
         * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
         *
         * @method submitForm
         * @param {Event} e Form submission event.
         * @private
         */
        TheForm.prototype.submitForm = function(e) {
            e.preventDefault();
            this.modal.getRoot().find('form').submit();
        };

        return /** @alias module:block_poodllclassroom/modalformhelper */ {
            // Public variables and functions.
            /**
             * Attach event listeners to initialise this module.
             *
             * @method init
             * @param {string} selector The CSS selector used to find nodes that will trigger this module.
             * @param {int} contextid The contextid for the course.
             * @param {string} formname The formname for the course.
             * @param {Object} callback The function to call after successful deletion (for UI updates)
             * @return {Promise}
             */
            init: function(selector, contextid, formname, callback) {
                return new TheForm(selector, contextid, formname, callback);
            }
        };
    });