function moreticket_urgency(params) {

    var root_doc = params.root_doc;
    var use_urgency = params.use_urgency;
    var urgency_ids = params.urgency_ids;

    //################## On ADD side ################################################################
    $(document).ready(function () {
        // only in ticket form
        if (location.pathname.indexOf('ticket.form.php') > 0
            && use_urgency) {
            $.urlParam = function (name) {
                var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
                if (results != null) {
                    return results[1] || 0;
                }

                return undefined;
            }
            // get tickets_id
            var tickets_id = 0;
            if ($.urlParam('id') != undefined) {
                tickets_id = $.urlParam('id');
            }

            if (tickets_id > 0)
                return;

            // Launched on each complete Ajax load 
            $(document).ajaxComplete(function (event, xhr, option) {
                setTimeout(function () {
                    // We execute the code only if the ticket form display request is done 
                    if (option.url != undefined) {
                        var ajaxTab_param, tid;
                        var paramFinder = /[?&]?_glpi_tab=([^&]+)(&|$)/;

                        // We find the name of the current tab
                        ajaxTab_param = paramFinder.exec(option.url);
                        // Get the right tab
                        if (ajaxTab_param != undefined
                            && (ajaxTab_param[1] == "Ticket$main")) {

                            //Inject Urgency ticket data
                            $.ajax({
                                url: root_doc + '/ajax/ticket.php',
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
                                            if (inarray(urgency_bloc.val(), urgency_ids) && use_urgency) {
                                                $("#moreticket_urgency_ticket").css({'display': 'block'});
                                            } else {
                                                $("#moreticket_urgency_ticket").css({'display': 'none'});
                                            }

                                            // ONCLICK : Display or hide urgency type
                                            urgency_bloc.change(function () {
                                                // URGENCY TICKET 
                                                if (inarray(urgency_bloc.val(), urgency_ids) && use_urgency) {
                                                    $("#moreticket_urgency_ticket").css({'display': 'block'});
                                                } else {
                                                    $("#moreticket_urgency_ticket").css({'display': 'none'});
                                                }
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    }
                }, 200);
            });
        } else if ((window.location.href.indexOf('helpdesk.public.php?create_ticket=1') > 0 ||
            window.location.href.indexOf('tracking.injector.php') > 0)
            && use_urgency) {
            //Inject Urgency ticket data
            $.ajax({
                url: root_doc + '/ajax/ticket.php',
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
                            if (inarray(urgency_bloc.val(), urgency_ids) && use_urgency) {
                                $("#moreticket_urgency_ticket").css({'display': 'block'});
                            } else {
                                $("#moreticket_urgency_ticket").css({'display': 'none'});
                            }

                            // ONCLICK : Display or hide urgency type
                            urgency_bloc.change(function () {
                                // URGENCY TICKET 
                                if (inarray(urgency_bloc.val(), urgency_ids) && use_urgency) {
                                    $("#moreticket_urgency_ticket").css({'display': 'block'});
                                } else {
                                    $("#moreticket_urgency_ticket").css({'display': 'none'});
                                }
                            });
                        }
                    }
                }
            });
        }
    });

    //################## On UPDATE side ################################################################
    $(document).ready(function () {
        // only in ticket form
        if (location.pathname.indexOf('ticket.form.php') > 0
            && use_urgency) {

            $.urlParam = function (name, path) {
                var results = new RegExp('[\?&]?' + name + '=([^&#]*)').exec(path);
                if (results == null || results == undefined) {
                    return 0;
                }

                return results[1];
            }
            // get tickets_id
            var tickets_id = 0;
            if ($.urlParam('id', window.location.href) != undefined) {
                tickets_id = $.urlParam('id', window.location.href);
            }

            if (tickets_id == 0 || tickets_id == undefined)
                return;

            // Launched on each complete Ajax load 
            $(document).ajaxComplete(function (event, xhr, option) {
//                setTimeout(function () {
                // We execute the code only if the ticket form display request is done 
                if (option.url != undefined) {
                    var ajaxTab_param, tid;
                    var paramFinder = /[?&]?_glpi_tab=([^&]+)(&|$)/;

                    // We find the name of the current tab
                    ajaxTab_param = paramFinder.exec(option.url);

                    // Get the right tab
                    if (ajaxTab_param != undefined
                        && (ajaxTab_param[1] == "Ticket$main" || ajaxTab_param[1] == "-1")) {
                        //Inject Urgency ticket data
                        $.ajax({
                            url: root_doc + '/ajax/ticket.php',
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
                                        if (inarray(urgency_bloc.val(), urgency_ids)) {
                                            $("#moreticket_urgency_ticket").css({'display': 'block'});
                                        } else {
                                            $("#moreticket_urgency_ticket").css({'display': 'none'});
                                        }

                                        // ONCHANGE : Display or hide urgency type
                                        urgency_bloc.change(function () {
                                            // URGENCY TICKET 
                                            if (inarray(urgency_bloc.val(), urgency_ids)) {
                                                $("#moreticket_urgency_ticket").css({'display': 'block'});
                                            } else {
                                                $("#moreticket_urgency_ticket").css({'display': 'none'});
                                            }
                                        });
                                    }
                                }
                            }
                        });

                    }

                }
//                }, 200);
            });
        }
    });

}

function inarray(value, tab) {
    response = false;
    $.each(tab, function (key, value2) {
        if (value == value2) {
            response = true;
        }
    });
    return response;
}