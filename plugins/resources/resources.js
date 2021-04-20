/**
 * marks all checkboxes inside the given element
 * the given element is usaly a table or a div containing the table or tables
 *
 * @param    container_id    DOM element
 */
function plugin_resources_markCheckboxes( container_id ) {
   var checkboxes = document.getElementById(container_id).getElementsByTagName('input');
   for ( var j = 0; j < checkboxes.length; j++ ) {
      checkbox=checkboxes[j];
      if ( checkbox && checkbox.type == 'checkbox' ) {
         if ( checkbox.disabled == false ) {
            checkbox.checked = true;
         }
      }
   }

   return true;
}


/**
 * marks all checkboxes inside the given element
 * the given element is usaly a table or a div containing the table or tables
 *
 * @param    container_id    DOM element
 */
function plugin_resources_unMarkCheckboxes( container_id ) {
   var checkboxes = document.getElementById(container_id).getElementsByTagName('input');
   for ( var j = 0; j < checkboxes.length; j++ ) {
      checkbox=checkboxes[j];
      if ( checkbox && checkbox.type == 'checkbox' && checkbox.disabled != true) {
         checkbox.checked = false;
      }
   }

   return true;
}

/**
 * Add comment field into items linked to a resource
 */

function plugin_resources_show_item(id,img,new_src) {
   var el;
   var cur_src = img.src.substring(img.src.lastIndexOf("/")+1);

   new_src_test = new_src.substring(new_src.lastIndexOf("/")+1);
   path=new_src.replace(new_src_test,"");

   old_src = path + 'expand.gif';
   if (el = document.getElementById(id)) {

      el.className = (el.className=="plugin_resources_hide") ? "plugin_resources_show" : "plugin_resources_hide";

      if (cur_src == new_src_test) {
         img.src = old_src;
      } else {
         img.src = new_src;
      }
   }
}

/**
 * Add comment field into choices on wizard new resouce
 */

function plugin_resources_show_tab(id) {
   var el;

   if (el = document.getElementById(id)) {

      el.className = (el.className=="plugin_resources_hide") ? "plugin_resources_show" : "plugin_resources_hide";

   }
}

function First2UpperCase(texte) {
   var t = new Array();
   for (j=0; j < texte.length;j++) {
      if (j == 0) {
         t[j] = texte.substr(j,1).toUpperCase();
      } else {
         t[j] = texte.substr(j,1).toLowerCase();
      }
   }
   return t.join('');
}

/**
 *
 * @param root_doc
 * @param id
 */
function plugin_resources_change_action(root_doc, id){
    var resource_id = $("select[name='plugin_resources_resources_id']");

    $.ajax({
         url: root_doc+'/plugins/resources/ajax/resourcechange.php',
         type: 'POST',
         data: '&id=' + id + '&plugin_resources_resources_id='+ resource_id.val(),
         dataType: 'html',
         success: function (code_html, statut) {

            $('#plugin_resources_actions').html(code_html);
            $('#plugin_resources_buttonchangeresources').html("");
         },

      });
}

/**
 *
 * @param root_doc
 * @param id
 */
function plugin_resources_change_resource(root_doc, id){
    var action_id = $("select[name='change_action']");

    $.ajax({
         url: root_doc+'/plugins/resources/ajax/resourcechange.php',
         type: 'POST',
         data: '&id=' + action_id.val() + '&plugin_resources_resources_id='+ id,
         dataType: 'html',
         success: function (code_html, statut) {
            $('#plugin_resources_actions').html(code_html);
            $('#plugin_resources_buttonchangeresources').html("");
         },

      });
}

/**
 *
 * @param root_doc
 * @param id
 */
function plugin_resources_pdf_resource(root_doc, id) {
    $.ajax({
         url: root_doc + '/plugins/resources/ajax/pdfresource.php',
         type: 'POST',
         data: '&plugin_resources_resources_id=' + id,
         dataType: 'html',
         success: function (code_html, statut) {
            $('#resource_pdf').html(code_html);
         },

      });
}

/**
 *
 * @param root_doc
 * @param id
 */
function plugin_resources_security_compliance(root_doc, id) {
    $.ajax({
         url: root_doc + '/plugins/resources/ajax/employee.php',
         type: 'POST',
         data: '&plugin_resources_clients_id=' + id,
         dataType: 'html',
         success: function (code_html, statut) {
            $('#security_compliance').html(code_html);
         },

      });
}
