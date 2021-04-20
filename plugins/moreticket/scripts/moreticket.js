/**
 * moreticket
 *
 * @param  options
 */
(function ($) {
    $.fn.moreticket = function (options) {

        var object = this;
        init();

        /**
         * Start the plugin
         */
      function init() {
          object.params = new Array();
          object.params['lang'] = '';
          object.params['root_doc'] = '';

          object.countSubmit = 0;

         if (options !== undefined) {
             $.each(options, function (index, val) {
               if (val != undefined && val != null) {
                   object.params[index] = val;
               }
             });
         }
      }

        /**
         * moreticket_injectWaitingTicket
         */
        this.moreticket_injectWaitingTicket = function () {

            // On UPDATE/ADD side
            $(document).ready(function () {
                var tickets_id = object.urlParam(window.location.href, 'id');
                // only in ticket form
                if (location.pathname.indexOf('front/ticket.form.php') > 0
                    && (object.params.use_solution || object.params.use_waiting || object.params.use_question)) {

                        if (tickets_id == 0 || tickets_id == undefined) {
                            setTimeout(function () {
                                object.createTicket(tickets_id);
                            }, 100);
                        }
                        $("#tabspanel + div.ui-tabs").on("tabsload", function () {
                            if (tickets_id == 0 || tickets_id == undefined) {
                                // object.createTicket(tickets_id);
                            } else {
                                setTimeout(function () {
                                    object.updateTicket(tickets_id);
                                    object.updateTicketTask(tickets_id);
                                }, 100);
                            }
                        });

                }
            });
        };




         //################## On ADD side ################################################################
         /**
         * createTicket
         */
         this.createTicket = function (tickets_id) {
            $.ajax({
               url: object.params.root_doc + '/ajax/ticket.php',
               data: {'tickets_id': tickets_id, 'action': 'showForm', 'type': 'add'},
               type: "POST",
               dataType: "html",
               success: function (response, opts) {
                   var requester = response;

                   var status_bloc = $("select[name='status']");

                  if (status_bloc !== undefined) {
                      status_bloc.parent().append(requester);

                      // ON DISPLAY : Display or hide waiting type
                     if ($("#moreticket_waiting_ticket") != undefined && $("#moreticket_close_ticket") != undefined) {

                         // WAITING TICKET
                        if (status_bloc.val() === object.params.waiting && object.params.use_waiting) {
                            $("#moreticket_waiting_ticket").css({'display': 'block'});
                        } else {
                            $("#moreticket_waiting_ticket").css({'display': 'none'});
                        }
                           // CLOSE TICKET
                           var show_solution = false;
                        if (object.params.solution_status != null && object.params.solution_status != '') {
                            $.each($.parseJSON(object.params.solution_status), function (index, val) {
                              if (index == status_bloc.val()) {
                                  show_solution = true;
                              }
                            });
                        }
                        if (show_solution && object.params.use_solution) {
                            $("#moreticket_close_ticket").css({'display': 'block'});
                        } else {
                            $("#moreticket_close_ticket").css({'display': 'none'});
                        }

                           // ONCLICK : Display or hide waiting type
                           status_bloc.change(function () {
                               // WAITING TICKET
                              if (status_bloc.val() == object.params.waiting && object.params.use_waiting) {
                                  $("#moreticket_waiting_ticket").css({'display': 'block'});
                              } else {
                                 $("#moreticket_waiting_ticket").css({'display': 'none'});
                              }

                                 // CLOSE TICKET
                                 var show_solution = false;
                              if (object.params.solution_status != null && object.params.solution_status != '') {
                                 $.each($.parseJSON(object.params.solution_status), function (index, val) {
                                    if (index == status_bloc.val()) {
                                        show_solution = true;
                                    }
                                 });
                              }
                              if (show_solution && object.params.use_solution) {
                                 $("#moreticket_close_ticket").css({'display': 'block'});
                              } else {
                                 $("#moreticket_close_ticket").css({'display': 'none'});
                              }
                           });
                     }
                  }
               }
             });
         };

         //################## On UPDATE side ################################################################

         /**
         * updateTicket
         *
         * @param tickets_id
         */
         this.updateTicket = function (tickets_id) {
            //Inject Waiting ticket data
            $.ajax({
               url: object.params.root_doc + '/ajax/ticket.php',
               data: {'tickets_id': tickets_id, 'action': 'showForm', 'type': 'update'},
               type: "POST",
               dataType: "html",
               success: function (response, opts) {
                  if ($("#moreticket_waiting_ticket").length != 0) {
                      $("#moreticket_waiting_ticket").remove();
                  }
                     var requester = response;

                     var status_bloc = $("select[name='status']");

                  if (status_bloc != undefined && status_bloc.length != 0) {
                     status_bloc.parent().append(requester);

                     // ON DISPLAY : Display or hide waiting type
                     if ($("#moreticket_waiting_ticket") != undefined) {
                         // WAITING TICKET
                        if (status_bloc.val() == object.params.waiting) {
                            $("#moreticket_waiting_ticket").css({'display': 'block'});
                        } else {
                            $("#moreticket_waiting_ticket").css({'display': 'none'});
                        }

                         // ONCHANGE : Display or hide waiting type
                         status_bloc.change(function () {
                             // WAITING TICKET
                           if (status_bloc.val() == object.params.waiting) {
                               $("#moreticket_waiting_ticket").css({'display': 'block'});
                           } else {
                               $("#moreticket_waiting_ticket").css({'display': 'none'});
                           }
                         });
                     }
                  }
               }
             });
         };

         /**
         * updateTicket
         *
         * @param tickets_id
         */
         this.updateTicketTask = function (tickets_id) {
            // Launched on each complete Ajax load
            $(document).ajaxComplete(function (event, xhr, option) {
                setTimeout(function () {
                    // We execute the code only if the ticket form display request is done
                  if (option.url != undefined) {
                     if (option.url.indexOf("ajax/timeline.php") > 0) {

                         object.injectBlocWaitingTask(tickets_id);
                     }
                  }
                })

            });

         };

         /**
         * injectBlocWaitingTask
         *
         * @param tickets_id
         */
         this.injectBlocWaitingTask = function (tickets_id) {
            //Inject Waiting ticket data
            $.ajax({
               url: object.params.root_doc + '/ajax/ticket.php',
               data: {'tickets_id': tickets_id, 'action': 'showFormTicketTask', 'type': 'update'},
               type: "POST",
               dataType: "html",
               success: function (response, opts) {
                  if ($("#moreticket_waiting_ticket_task").length != 0) {
                      $("#moreticket_waiting_ticket_task").remove();
                  }
                   if ($("#isQuestion").length != 0) {
                       $("#isQuestion").remove();
                   }
                     var requester = response;

                     var status_bloc_task = $("#x-split-button");

                  if (status_bloc_task != undefined && status_bloc_task.length != 0) {
                     if ($("#moreticket_waiting_ticket_task").length != 0) {
                         $("#moreticket_waiting_ticket_task").remove();
                     }
                      if ($("#isQuestion").length != 0) {
                          $("#isQuestion").remove();
                      }
                     status_bloc_task.parent().append(requester);
                     $("#x-split-button input[type='radio']").each(function (index, value) {
                        if ($(this).is(':checked')) {
                           if ($(this).val() == 4) {
                               $("#moreticket_waiting_ticket_task").css({
                                    'display': 'block',
                                    'clear': 'both',
                                    'text-align': 'center'
                                    });
                           } else {
                              $("#moreticket_waiting_ticket_task").css({'display': 'none'});
                           }
                        }
                     });
                      $("#x-split-button input[type='radio']").change(function () {
                        if ($(this).is(':checked')) {
                           if ($(this).val() == 4) {
                               $("#moreticket_waiting_ticket_task").css({
                                    'display': 'block',
                                    'clear': 'both',
                                    'text-align': 'center'
                                    });
                           } else {
                              $("#moreticket_waiting_ticket_task").css({'display': 'none'});
                           }
                        }
                      });
                  }
               }
             });
         }

         /**
         * moreticket_urgency
         */
         this.moreticket_urgency = function () {
            // On UPDATE/ADD side
            $(document).ready(function () {
                var tickets_id = object.urlParam(window.location.href, 'id');
                // only in ticket form
               if ((location.pathname.indexOf('front/ticket.form.php') > 0
                    || location.pathname.indexOf('helpdesk.public.php') > 0
                    || location.pathname.indexOf('tracking.injector.php') > 0)
                    && object.params.use_urgency) {
                  if (tickets_id == 0 || tickets_id == undefined) {
                      object.createTicket_urgency(tickets_id);
                  }
                     //else {
                     //     object.updateTicket_urgency(tickets_id);
                     // }
                     $("#tabspanel + div.ui-tabs").on("tabsload", function() {
                        setTimeout(function() {
                           if (tickets_id == 0 || tickets_id == undefined) {
                               // object.createTicket_urgency(tickets_id);
                           } else {
                               object.updateTicket_urgency(tickets_id);
                           }
                        }, 300);
                     });

               }
            });
         };

         this.createTicket_urgency = function (tickets_id) {
            $.ajax({
               url: object.params.root_doc + '/ajax/ticket.php',
               data: {'tickets_id': tickets_id, 'action': 'showFormUrgency', 'type': 'add'},
               type: "POST",
               dataType: "html",
               success: function (response, opts) {
                   var requester = response;

                   var urgency_bloc = $("select[name='urgency']");

                  if (urgency_bloc != undefined) {
                      urgency_bloc.parent().append(requester);
                      // ON DISPLAY : Display or hide urgency type
                     if ($("#moreticket_urgency_ticket") != undefined) {
                         // URGENCY TICKET
                        if (inarray(urgency_bloc.val(), object.params.urgency_ids)
                             && object.params.use_urgency) {
                            $("#moreticket_urgency_ticket").css({'display': 'block'});
                        } else {
                            $("#moreticket_urgency_ticket").css({'display': 'none'});
                        }

                           // ONCLICK : Display or hide urgency type
                           urgency_bloc.change(function () {
                               // URGENCY TICKET
                              if (inarray(urgency_bloc.val(), object.params.urgency_ids)
                                   && object.params.use_urgency) {
                                  $("#moreticket_urgency_ticket").css({'display': 'block'});
                              } else {
                                 $("#moreticket_urgency_ticket").css({'display': 'none'});
                              }
                           });
                     }
                  }
               }
             });
         };

         this.updateTicket_urgency = function (tickets_id) {
            $.ajax({
               url: object.params.root_doc + '/ajax/ticket.php',
               data: {'tickets_id': tickets_id, 'action': 'showFormUrgency', 'type': 'update'},
               type: "POST",
               dataType: "html",
               success: function (response, opts) {
                  if ($("#moreticket_urgency_ticket").length != 0) {
                      $("#moreticket_urgency_ticket").remove();
                  }
                     var requester = response;

                     var urgency_bloc = $("select[name='urgency']");

                  if (urgency_bloc != undefined) {
                     urgency_bloc.parent().append(requester);

                     // ON DISPLAY : Display or hide urgency type
                     if ($("#moreticket_urgency_ticket") != undefined) {
                         // URGENCY TICKET
                        if (inarray(urgency_bloc.val(), object.params.urgency_ids)) {
                            $("#moreticket_urgency_ticket").css({'display': 'block'});
                        } else {
                            $("#moreticket_urgency_ticket").css({'display': 'none'});
                        }

                         // ONCHANGE : Display or hide urgency type
                         urgency_bloc.change(function () {
                             // URGENCY TICKET
                           if (inarray(urgency_bloc.val(), object.params.urgency_ids)) {
                               $("#moreticket_urgency_ticket").css({'display': 'block'});
                           } else {
                               $("#moreticket_urgency_ticket").css({'display': 'none'});
                           }
                         });
                     }
                  }
               }
             });
         };

         this.moreticket_solution = function () {
            $(document).ready(function () {
                // Only in ticket.php
               if (location.pathname.indexOf('ticket.form.php') > 0) {
                   // get tickets_id
                   var tickets_id = object.urlParam(window.location.href, 'id');
                   //only in edit form
                  if (tickets_id == undefined || tickets_id == 0) {
                      return;
                  }

                     // Launched on each complete Ajax load
                     $(document).ajaxComplete(function (event, xhr, option) {
                        setTimeout(function () {
                            // We execute the code only if the ticket form display request is done
                           if (option.data != undefined) {
                               var tid;
                               // Get the right tab
                              if (object.urlParam(option.data, 'type') == 'Solution'
                                   && (option.url.indexOf("ajax/timeline.php") != -1 || option.url.indexOf("ajax/viewsubitem.php") != -1)) {

                                  var solId = object.urlParam(option.data, '&id');

                                 if (solId == 0 || solId == undefined) {
                                     $.ajax({
                                          url: options.root_doc + '/ajax/ticket.php',
                                          type: "POST",
                                          dataType: "html",
                                          data: {
                                             'tickets_id': tickets_id,
                                             'tickettasks_id': tid,
                                             'action': 'showFormSolution'
                                          },
                                          success: function (response, opts) {
                                             if (object.params.div_kb) {
                                                 var inputAdd = $("select[name='_sol_to_kb']");
                                             } else {
                                                 var inputAdd = $("select[id^='dropdown_solutiontypes_id']");
                                             }
                                                var solutionForm = inputAdd.closest("tr");
                                             if ($("div[name='duration_solution_" + tid + "']").length == 0) {
                                                 $(response).insertAfter(solutionForm);
                                             }

                                                var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
                                             while (scripts = scriptsFinder.exec(response)) {
                                                 eval(scripts[1]);
                                             }
                                          }
                                        });
                                 }
                              }

                           }
                        }, 100);
                     }, this);
               }
            });
         };

         function inarray(value, tab) {
            response = false;
            $.each(tab, function (key, value2) {
               if (value == value2) {
                   response = true;
               }
            });
             return response;
         };

         /**
         *  Get the form values and construct data url
         *
         * @param object form
         */
         this.getFormData = function (form) {
            if (typeof (form) !== 'object') {
                var form = $("form[name='" + form + "']");
            }

            return object.encodeParameters(form[0]);
         };

         /**
         * Encode form parameters for URL
         *
         * @param array elements
         */
         this.encodeParameters = function (elements) {
            var kvpairs = [];

            $.each(elements, function (index, e) {
               if (e.name != '') {
                  switch (e.type) {
                     case 'radio':
                     case 'checkbox':
                        if (e.checked) {
                            kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
                        }
                          break;
                     case 'select-multiple':
                         var name = e.name.replace("[", "").replace("]", "");
                         $.each(e.selectedOptions, function (index, option) {
                             kvpairs.push(encodeURIComponent(name + '[' + option.index + ']') + '=' + encodeURIComponent(option.value));
                         });
                           break;
                     default:
                         kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
                           break;
                  }
               }
            });

            return kvpairs.join("&");
         };

         /**
         * Get url parameter
         *
         * @param string url
         * @param string name
         */
         this.urlParam = function (url, name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
            if (results == null || results == undefined) {
                return 0;
            }

            return results[1];
         };

         /**
         * Is IE navigator
         */
         this.isIE = function () {
            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {      // If Internet Explorer, return version number
                return true;
            }

            return false;
         };

         return this;
    }
}(jQuery));
