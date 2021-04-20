
/** 
 *  Load plugin scripts on page start
 */
(function ($) {
    $.fn.manageentities_load_scripts = function () {

        init();

        // Start the plugin
        function init() {
//            $(document).ready(function () {
            var path = 'plugins/manageentities/';
            var url = window.location.href.replace(/front\/.*/, path);
            if (window.location.href.indexOf('plugins') > 0) {
                url = window.location.href.replace(/plugins\/.*/, path);
            }

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
//            });
        }

        return this;
    }
}(jQuery));

$(document).manageentities_load_scripts();
