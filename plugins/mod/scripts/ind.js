$(document).ready(function() {

   var ajax_baseurl = '../plugins/mod/ajax';
   var path = document.location.pathname;
   // construct url for plugin pages
   if(path.indexOf('plugins/') !== -1) {
      var plugin_path = path.substring(path.indexOf('plugins'));
      var nb_directory = (plugin_path.match(/\//g) || []).length + 1;
      var ajax_baseurl = Array(nb_directory).join("../") + 'plugins/mod/ajax';
   }

	var pluginModMenu = function () {  
		$("#c_preference").append("<span id='load_ind'></span>");     
		$("#c_preference").css("width",'70%');     
		$("#load_ind").before().load(ajax_baseurl + "/display_ind.php"); 
	};



if (document.getElementById('champRecherche') != null ) {

	pluginModMenu();

}	
	

});
