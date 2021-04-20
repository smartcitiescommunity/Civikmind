<?php
include ("../../../inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

//not executed in self-service interface & right verification
if ($_SESSION['glpiactiveprofile']['interface'] == "central"
   && (Session::haveRight("ticket", CREATE)
      || Session::haveRight("ticket", UPDATE))) {

   $locale_group_view = __('Group View');

   $JS = <<<JAVASCRIPT

   var plugin_url = CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.escalade;

   var doOnTabChange = function() {
      //intercept ajax load of group tab
      $(document).ajaxComplete(function(event, jqxhr, option) {
         if (option.url == plugin_url+'/ajax/central.php') {
            return;
         }

         if (option.url.indexOf('common.tabs.php') > 0) {
            //delay the execution (ajax requestcomplete event fired before dom loading)
            setTimeout(function () {
               insertEscaladeBlock();
            }, 300);
         }
      });
   }

   var insertEscaladeBlock = function() {
      var selector = ".ui-tabs-panel .tab_cadre_central .top:last" +
         ", .alltab:contains('$locale_group_view') + .tab_cadre_central .top:last";

      // get central list for plugin and insert in group tab
      $(selector).each(function(){
         if (this.innerHTML.indexOf('escalade_block') < 0) {

            //prepare a span element to load new elements
            $(this).prepend("<span id='escalade_block'>test</span>");

            //ajax request
            $("#escalade_block").load(plugin_url+'/ajax/central.php');
         }
      });
   };

   $(document).ready(function() {
      //try to insert directly (if we are on central group page)
      insertEscaladeBlock();

      // try to intercept tabs changes
      $(".ui-tabs-panel:visible").ready(function() {
         doOnTabChange();
      });
      $("#tabspanel + div.ui-tabs").on("tabsload", function() {
         setTimeout(function() {
            doOnTabChange();
         }, 300);
      });
   });

JAVASCRIPT;
   echo $JS;
}
