<?php
include ("../../../inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

//not executed in self-service interface & right verification
if ($_SESSION['glpiactiveprofile']['interface'] == "central"
   && (Session::haveRight("ticket", CREATE)
       || Session::haveRight("ticket", UPDATE))) {

   $locale_actor = __('Actor');

   $JS = <<<JAVASCRIPT

   var plugin_url = CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.escalade;

   var ticketEscalation = function() {
      var tickets_id = getUrlParameter('id');

      //only in edit form
      if (tickets_id == undefined) {
         return;
      }

      // if escalade block already inserted
      if ($(".escalade_active").get(0)) {
         return;
      }

      //set active group in red
      $("table:contains('$locale_actor') td:last, .tab_actors .actor-bloc:last")
         .find("a[href*=group]")
         .addClass('escalade_active')
         .last()
         .append(
            $('<div></div>').load(
               plugin_url+'/ajax/history.php',
               {'tickets_id': tickets_id}
            )
         );
   }

   $(document).ready(function() {
      // only in ticket form
      if (location.pathname.indexOf('ticket.form.php') != 0) {
         $(".ui-tabs-panel:visible").ready(function() {
            ticketEscalation();
         })

         $("#tabspanel + div.ui-tabs").on("tabsload", function() {
            setTimeout(function() {
               ticketEscalation();
            }, 300);
         });
      }
   });

JAVASCRIPT;
      echo $JS;
}