/**
 *  Load plugin scripts on page start
 */
(function ($) {
   $.fn.activity_load_scripts = function () {

      init();

      // Start the plugin
      function init() {
         //            $(document).ready(function () {
         var path = 'plugins/activity/';
         var url = window.location.href.replace(/front\/.*/, path);
         if (window.location.href.indexOf('plugins') > 0) {
            url = window.location.href.replace(/plugins\/.*/, path);
         }

         // Send data
         $.ajax({
            url: url+'ajax/loadscripts.php',
            type: "POST",
            dataType: "html",
            data: 'action=load',
            success: function (response, opts) {
               var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
               while (scripts = scriptsFinder.exec(response)) {
                  eval(scripts[1]);
               }
            }
         });
         if($("#activity_link").length == 0) {
            $("#c_preference ul #bookmark_link").before("\
                <li id='activity_link'>\
                    <a href='#' id='showLateralMenuLink'>\
                        <i id='activity_icon' class='far fa-activity' title=''  class='button-icon'></i>\
                    </a>\
                </li>");
         }
         //            });
      }

      return this;
   };
}(jQuery));

$(document).activity_load_scripts();
