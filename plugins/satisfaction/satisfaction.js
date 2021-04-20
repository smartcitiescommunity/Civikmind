/**
 *
 * @param root_doc
 * @param id
 */
function plugin_satisfaction_load_defaultvalue(root_doc, default_value){
    var value = $('input[name="default_value"]').val();

    if(value > default_value) {
        value = default_value;
    }

    $.ajax({
        url: root_doc+'/ajax/satisfaction.php',
        type: 'POST',
        data: '&action_default_value&default_value='+ default_value + '&value=' + value,
        dataType: 'html',
        success: function (code_html, statut) {
            $('#default_value').html(code_html);
        },

    });
}