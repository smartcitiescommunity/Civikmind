pluginNewsCloseAlerts = function() {
   $(document).on("click", "a.plugin_news_alert-close",function() {
      var alert = $(this).parent(".plugin_news_alert");
      var id    = alert.attr('data-id');
      var ajax_baseurl = CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.news+"/ajax";
      $.post(ajax_baseurl+"/hide_alert.php", {'id' : id})
         .done(function() {
            alert.remove();
         });
   });
};

pluginNewsToggleAlerts = function() {
   $(document).on("click", ".plugin_news_alert-toggle",function() {
      var alert = $(this).parent(".plugin_news_alert");
      alert.toggleClass('expanded');
   });
}

$(document).ready(function() {
   pluginNewsCloseAlerts();
   pluginNewsToggleAlerts();

   $(".glpi_tabs").on("tabsload", function(event, ui) {
      pluginNewsCloseAlerts();
      pluginNewsToggleAlerts();
   });
});