

$(document).ready(function () {
    var options = {
        target: '#output1', // target element(s) to be updated with server response 
        beforeSubmit: showRequest, // pre-submit callback 
        success: showResponse  // post-submit callback 
    };

    // bind form using 'ajaxForm' 
    $('#form-add-contract').ajaxForm(options);
});

// pre-submit callback 
function showRequest(formData, jqForm, options) {
    // Does nothing
    return true;
}

// post-submit callback 
function showResponse(responseText, statusText, xhr, $form) {
// Does nothing
}


function showFormAddPDFContract(textTitle, btnYes, btnNo) {
    var mTitle = "<i class='fas fa-info-circle fa-1x'></i>&nbsp;" + textTitle;
    $("#form-add-contract").dialog({
        autoOpen: false,
        height: 200,
        width: 450,
        modal: true,
        open: function () {
            $(this)
                    .parent()
                    .children(".ui-dialog-titlebar")
                    .html(mTitle);
        },
        buttons:
                [{
                        text: btnYes,
                        click: function () {
                            $('#add-form-contract').submit();
                        
                            $(this).dialog("close");
                            $.ajax({
                                method: 'GET',
                                url: ("../ajax/" + "updateDocumentList.php"),

                                data: {
                                    action: "refresh",
                                },

                            }).done(function (data) {
                                $("#tbl_list_pdf_contract").html(data);
                            }).fail(function (jqXHR, textStatus, errorThrown) {
                                window.console.log(textStatus);
                                window.console.log(errorThrown);
                            });

                        }
                    }, {
                        text: btnNo,
                        click: function () {
                            $(this).dialog("close");
                        }
                    }]
    });
    $('#form-add-contract').dialog('open');

}

function showDialog(textTitle, btnName, message) {
    var mTitle = "<i class='fas fa-info-circle fa-1x'></i>&nbsp;" + textTitle;
    $("#custom-dialog").dialog({
        autoOpen: false,
        height: 200,
        width: 450,
        modal: true,
        open: function () {
            $(this)
                    .parent()
                    .children(".ui-dialog-titlebar")
                    .html(mTitle);
        },
        buttons: {
            "OK": function () {
                $(this).dialog("close");
            }
        },
        close: function () {
            $('#custom-dialog').remove();
        }
    });
    $('#custom-dialog').dialog('open');
}

function alertCreateEntity(btnYes, btnNo, textTitle, txtFunction) {
    var mTitle = "<i class='fas fa-exclamation-triangle fa-2x' style='color:orange'></i>&nbsp;" + textTitle + " ! ";
    $("#alert-create-entity").dialog({
        autoOpen: false,
        height: 200,
        width: 450,
        modal: true,
        open: function () {
            $(this)
                    .parent()
                    .children(".ui-dialog-titlebar")
                    .html(mTitle);
        },
        buttons: [
            {
                text: btnYes,
                click: function () {
                    switch (txtFunction) {
                        case "entity-contract":
                            addEntityAndContract();
                            break;
                        case "entity-contact":
                            addEntityAndContact();
                            break;
                        case "entity-intervention":
                            addEntityAndIntervention();
                            break;
                        case "entity-intervention-contract":
                            addEntityInterventionAndContract();
                            break;
                        case "intervention-contract":
                            addInterventionAndContract();
                            break;
                        case "delete-management-type":
                            deleteContractManagementType();
                            break;
                        case "add-all-element":
                            addAllElements();
                            break;
                        case "update-all-element":
                            updateAllElements();
                            break;
                        default:
                            break;
                    }
                    $(this).dialog("close");
                }
            }, {
                text: btnNo,
                click: function () {
                    $(this).dialog("close");
                }
            }],
        close: function () {
            $(this).dialog("close");
            $('#alert-create-entity').remove();
        }
    });

    $('#alert-create-entity').dialog('open');
}


function switchElementsEnableFromCbko(currentCb, idToHide) {
    if (currentCb.checked == true) {
        document.getElementById(idToHide).disabled = true;
    } else {
        document.getElementById(idToHide).disabled = false;
    }
}


function switchElementsEnableFromCb(currentCb, idToHide) {
    if (currentCb.checked == true) {
        document.getElementById(idToHide).style.visibility = "hidden";
    } else {
        document.getElementById(idToHide).style.visibility = "visible";
    }
}

function showCloneTicketTask(options) {
    //################## TICKET TASK ################################################################
    $(document).ready(function () {
        // Only in ticket.php
        if (location.pathname.indexOf('ticket.form.php') > 0) {
            // get tickets_id
            var tickets_id = getUrlParam(window.location.href, 'id');
            //only in edit form
            if (tickets_id == undefined || tickets_id == 0)
                return;

            // Launched on each complete Ajax load 
            $(document).ajaxComplete(function (event, xhr, option) {
                setTimeout(function () {
                    // We execute the code only if the ticket form display request is done 
                    if (option.data != undefined) {
                        var tid;

                        // Get the right tab
                        if (getUrlParam(option.data, 'type') == 'TicketTask'
                                && (option.url.indexOf("ajax/timeline.php") != -1 || option.url.indexOf("ajax/viewsubitem.php") != -1)) {
                            var taskId = getUrlParam(option.data, '&id');
                            var tid = 0;
                            if ((taskId != undefined)) {
                                tid = taskId;
                            }

                            if (tid > 0) {
                                $.ajax({
                                    url: options.root_doc + '/plugins/manageentities/ajax/tickettask.php',
                                    type: "POST",
                                    dataType: "html",
                                    data: {
                                        'tickets_id': tickets_id,
                                        'tickettasks_id': tid,
                                        'action': 'showCloneTicketTask'
                                    },
                                    success: function (response, opts) {
                                        var taskForm = $("input[value='"+tid+"']").next("tr");
                                        if ($("span[name='duplicate_"+tid+"']").length == 0) {
                                            $(response).insertBefore(taskForm);
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
}

function getUrlParam(url, name) {
    var results = new RegExp('[?&]?' + name + '=([^&]+)(&|$)').exec(url);
    if (results != null) {
        return results[1] || 0;
    }

    return undefined;
}

function cloneTicketTask(options) {
    $.ajax({
        url: options.root_doc + '/plugins/manageentities/ajax/tickettask.php',
        type: "POST",
        dataType: "json",
        data: {
            'tickets_id': options.tickets_id,
            'new_date_value': $('#' + options.new_date_id).val(),
            'tickettasks_id': options.tickettasks_id,
            'action': 'cloneTicketTask'
        },
        success: function (json, opts) {
            if (json.tickettasks_id != undefined) {
                window.location.reload();
            }
        }
    });
}

function manageentities_loadPrice(value) {
    document.getElementsByName('price')[0].value = parseInt(value).toFixed(2);
}

function manageentities_loadCriForm(action, modal, params) {
    var formInput;

    if (params.form != undefined) {
        formInput = getManageentitiesFormData($('form[name="' + params.form + '"]'));
    }

    $.ajax({
        url: params.root_doc + '/plugins/manageentities/ajax/cri.php',
        type: "POST",
        dataType: "html",
        data: {
            'action': action,
            'params': params,
            'pdf_action': params.pdf_action,
            'formInput': formInput,
            'modal': modal
        },
        success: function (response, opts) {
            try {
                var json = $.parseJSON(response);
                if (!json.success) {
                    $("#manageentities_cri_error").html(json.message).show().delay(2000).fadeOut('slow');
                }

            } catch (err) {
                $('#' + modal).html(response);

                switch (action) {
                    case 'showCriForm':
                        $('#' + modal).dialog({
                            autoOpen: true,
                            height: params.height,
                            width: params.width,
                            overflow: "none"
                        });
                        break;

                    case 'saveCri':
                        $('#' + modal).dialog('close');
                        window.location.reload();
                        break;
                }
            }
        }
    });
}

function getManageentitiesFormData(form) {
    var unindexed_array = form.serialize();
    var indexed_array = {};

    $.map(unindexed_array.split('&'), function (n, i) {
        indexed_array[n.split('=')[0]] = n.split('=')[1];
    });

    return JSON.stringify(indexed_array);
}

function manageentitiesShowMonth(formId, toupdate, monthNames, year, month) {
    $.each(monthNames, function (index, val) {
        var monthClass = 'manageentities-button manageentities-state-default';
        if (index == 1) {
            monthClass = 'manageentities-button manageentities-state-default manageentities-corner-left';
        }
        
        if (index == 12) {
            monthClass = 'manageentities-button manageentities-state-default manageentities-corner-right';
        }
        if (month == index) {
           monthClass = 'manageentities-button manageentities-state-active';
        } 
        $('<a class="' + monthClass + ' manageentities_href" href="#' + index + '_' + year + '">' + val + '</a>').click(function () {
            if (!$(this).hasClass('manageentities-state-disabled')) {
                $(this).removeClass('manageentities-state-hover');
            }
            searchManageentities(index, formId, year, monthNames);

        }).mousedown(function () {
            $(this).not('.manageentities-state-active')
                    .not('.manageentities-state-disabled')
                    .addClass('manageentities-state-down');
        }).mouseup(function () {
            $(this).removeClass('manageentities-state-down');
        }).hover(
                function () {
                    $(this).not('.manageentities-state-active')
                            .not('.manageentities-state-disabled')
                            .addClass('manageentities-state-hover');
                },
                function () {
                    $(this).removeClass('manageentities-state-hover')
                            .removeClass('manageentities-state-down');
                }
        ).appendTo(toupdate);
        
    });
}
function searchManageentities(index, formId, year, monthNames) {
    $("input[name='year_current']").val(year);
    
    var begin_date = new Date(year, index - 1, 1);
    var end_date = new Date(year, index, 0);
    $("input[name='begin_date']").val(begin_date.yyyymmdd());
    $("input[name='end_date']").val(end_date.yyyymmdd());
    $('#' + formId).submit();
}

function lastYearManagesEntities(formId, element, year, monthNames) {
    var monthClass = 'fc-button fc-button-agendaWeek fc-state-default';
    $('<a class=\"' + monthClass + ' manageentities_href\"  href=\"#' + (year - 1) + '\">' + (year - 1) + '</a>').click(function () {
        if (!$(this).hasClass('fc-state-disabled')) {
            $(this).removeClass('fc-state-hover');
        }
        searchManageentities(12, formId, year-1, monthNames);
    }).mousedown(function () {
        $(this).not('.manageentities-state-active')
                .not('.manageentities-state-disabled')
                .addClass('manageentities-state-down');
    }).mouseup(function () {
        $(this).removeClass('manageentities-state-down');
    }).hover(
            function () {
                $(this).not('.manageentities-state-active')
                        .not('.manageentities-state-disabled')
                        .addClass('manageentities-state-hover');
            },
            function () {
                $(this).removeClass('manageentities-state-hover')
                        .removeClass('manageentities-state-down');
            }
    ).appendTo(element);
}

function nextYearManagesEntities(formId, element, year, monthNames) {
    var monthClass = 'fc-button fc-button-agendaWeek fc-state-default';
    $('<a class=\"' + monthClass + ' manageentities_href\"  href=\"#' + (year + 1) + '\">' + (year + 1) + '</a>').click(function () {
        if (!$(this).hasClass('fc-state-disabled')) {
            $(this).removeClass('fc-state-hover');
        }
        searchManageentities(1, formId, year+1, monthNames);
    }).mousedown(function () {
        $(this).not('.manageentities-state-active')
                .not('.manageentities-state-disabled')
                .addClass('manageentities-state-down');
    }).mouseup(function () {
        $(this).removeClass('manageentities-state-down');
    }).hover(
            function () {
                $(this).not('.manageentities-state-active')
                        .not('.manageentities-state-disabled')
                        .addClass('manageentities-state-hover');
            },
            function () {
                $(this).removeClass('manageentities-state-hover')
                        .removeClass('manageentities-state-down');
            }
    ).appendTo(element);
}


Date.prototype.yyyymmdd = function() {         
                                
        var yyyy = this.getFullYear().toString();                                    
        var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based         
        var dd  = this.getDate().toString();             
                            
        return yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]);
   };  