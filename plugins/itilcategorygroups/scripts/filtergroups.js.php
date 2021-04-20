<?php
include ("../../../inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

$idor_token = Session::getNewIDORToken('Group');

$web_dir = Plugin::getWebDir('itilcategorygroups');
$JS = <<<JAVASCRIPT
var groups_url = '{$web_dir}/ajax/group_values.php';
var tickets_id = getUrlParameter('id');

var triggerNewTicket = function() {
   if (getItilcategories_id() == 0) {
      return;

   } else {
      var assign_select_dom_id = $("*[name='_groups_id_assign']").eq(0).attr("id");
      var type = $("select[id^='dropdown_type']").val();

      redefineDropdown(assign_select_dom_id, groups_url, 0, type);
   }
};

var triggerupdateTicket = function() {
   if (getItilcategories_id() == 0) {
      return;
   } else {
      checkDOMChange("select[name='_itil_assign[groups_id]']", function() {
         var assign_select_dom_id = $("select[name='_itil_assign[groups_id]']")[0].id;
         var type = $("select[id^='dropdown_type']").val();

         redefineDropdown(assign_select_dom_id, groups_url, tickets_id, type);
      });
   }
};

var triggerAll = function() {
   if (tickets_id == 'Not found') {
      triggerNewTicket();
   } else {
      $(document).ajaxSend(function( event, jqxhr, settings ) {
         if (settings.url.indexOf("dropdownItilActors.php") > 0
            && settings.data.indexOf("group") > 0
               && settings.data.indexOf("assign") > 0
            ) {
          triggerupdateTicket();
         }
      });
   }
};

var redefineDropdown = function (id, url, tickets_id, type) {
   if (typeof templateResult === "undefined" && typeof formatResult !== "undefined") {
      var templateResult = formatResult;
   }

   $('#' + id).select2({
      width:                   '80%',
      minimumInputLength:      0,
      quietMillis:             100,
      minimumResultsForSearch: {$CFG_GLPI['ajax_limit_count']},
      ajax: {
         url: url,
         dataType: 'json',
         type: 'POST',
         data: function (params, page) {
            query = params;
            return {
               ticket_id:         tickets_id,
               type : type,
               itilcategories_id: getItilcategories_id(),
               searchText: params.term,
               _idor_token: "{$idor_token}"
            };
         },
         results: function (data, page) {
            var more = (data.count >= 100);
            return { results: data.results, more: more };
         }
      },
      templateResult: templateResult,
      initSelection: function (element, callback) {
         var id = $(element).val();
         var defaultid = '0';
         if (id !== '') {
            // No ajax call for first item
            if (id === defaultid) {
              var data = {id: 0,
                        text: "-----"};
               callback(data);
            } else {
               $.ajax(url, {
                  data: {
                     ticket_id: tickets_id,
                     type : type,
                     itilcategories_id: getItilcategories_id()
                  },
                  dataType: 'json',
               }).done(function(data) {
                  if (data.results[0].id == defaultid) {
                     var data = {id: 0, text: "-----"};
                  }
                  callback(data);
               });
            }
         }
      }
   });
};

$(document).ready(function() {
   if (location.pathname.indexOf('ticket.form.php') >= 0) {
      var delayedTrigger = function () {
         setTimeout(function() {
            triggerAll();
         }, 50);
      };

      $(".ui-tabs-panel:visible").ready(delayedTrigger);
      $("#tabspanel + div.ui-tabs").on("tabsload", delayedTrigger);
   }
});
JAVASCRIPT;
echo $JS;
