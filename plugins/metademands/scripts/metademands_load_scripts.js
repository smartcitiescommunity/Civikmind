/**
 *  Load plugin scripts on page start
 */
(function ($) {
    $.fn.metademands_load_scripts = function () {

        init();
        var object = this;

        // Start the plugin
      function init() {
          var path = 'plugins/metademands/';
          var url = window.location.href.replace(/front\/.*/, path);
         if (window.location.href.indexOf('plugins') > 0) {
             url = window.location.href.replace(/plugins\/.*/, path);
         }

         if (location.pathname.indexOf('front/ticket.form.php') > 0) {
             // Launched on each complete Ajax load
             $(document).ajaxComplete(function (event, xhr, option) {
                 setTimeout(function () {
                     // Get the right tab
                  if (option.url != undefined
                             && (object.urlParam(option.url, '_itemtype') == 'Ticket'
                                     && (object.urlParam(option.url, '_glpi_tab') == 'Ticket$main'
                                        || object.urlParam(option.url, '_glpi_tab') == 'Ticket$1'))
                             && option.url.indexOf("ajax/common.tabs.php") != -1) {

                      object.loadscript(url);
                  }
                 }, 100);
             }, this);

             // Self-service specific case
         } else if (window.location.href.indexOf('helpdesk.public.php?create_ticket=1') > 0 || location.pathname.indexOf('tracking.injector.php') > 0) {
             setTimeout(function () {
                 object.loadscript(url);
             }, 100);
         }
      }

        /**
         * Load script
         *
         * @param string url
         */
        this.loadscript = function (url) {
            // Send data
            $.ajax({
               url: url + 'ajax/loadscripts.php',
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
        };

         /**
         * Get url parameter
         *
         * @param string url
         * @param string name
         */
         this.urlParam = function (url, name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
            if (results == null || results == undefined) {
                return  0;
            }

            return results[1];
         };

         return this;
    }
}(jQuery));

$(document).metademands_load_scripts();
