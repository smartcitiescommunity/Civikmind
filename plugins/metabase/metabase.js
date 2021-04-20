$(function() {

   // do like a jquery toggle but based on a parameter
   $.fn.toggleFromValue = function(val) {
      if (val === 1
          || val === "1"
          || val === true) {
         this.show();
      } else {
         this.hide();
      }
   };

   $(document).on("click", ".metabase_collection_list label", function() {
      $(this).toggleClass('expanded');
   });

   $(document).on("click", "a.extract", function() {
      var id = $(this).data('id');
      var type = $(this).data('type');
      $('<div></div>').dialog({
         modal: true,
         open: function (){
            $(this).load(CFG_GLPI.root_doc + '/' + GLPI_PLUGINS_PATH.metabase + '/ajax/extract_json.php', {
               'id': id,
               'type': type
            });
         },
         height: 800,
         width: '80%'
      });
   });
});
