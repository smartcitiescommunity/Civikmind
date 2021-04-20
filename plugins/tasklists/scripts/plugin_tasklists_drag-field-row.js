/* enable strict mode */
"use strict";

var plugin_tasklists_redipsInit;   // function sets dropMode parameter

// redips initialization
plugin_tasklists_redipsInit = function () {
    // reference to the REDIPS.drag lib
    var rd = REDIPS.drag;
    // initialization
    rd.init();

    rd.event.rowDroppedBefore = function (sourceTable, sourceRowIndex) {
        var pos = rd.getPosition();

        var old_index = sourceRowIndex;
        var new_index = pos[1];
        var type = document.getElementById('plugin_tasklists_tasktypes_id').value;
        // var state = document.getElementById('plugin_tasklists_taskstates_id').value;
        //console.log(container);
        jQuery.ajax({
            type: "POST",
            url: "../ajax/reorder.php",
            data: {
                old_order: old_index + 1,
                new_order: new_index + 1,
                plugin_tasklists_tasktypes_id: type,
                // plugin_tasklists_taskstates_id: state
            }
        })
            .fail(function () {
                return false;
            });
    }
};
