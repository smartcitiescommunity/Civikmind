$( document ).ready(function() {
   var urlAjax = CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.mreporting+"/ajax/homepage_link.php";
   $.post( urlAjax, function( data ) {
      $('#c_menu #menu').append( data );
   });
});
