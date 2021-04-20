<?php

include_once (GLPI_ROOT."/inc/based_config.php");

if (!defined("GLPI_MOD_DIR")) {
   define("GLPI_MOD_DIR", GLPI_ROOT."/plugins/mod");  
}


if(!function_exists("recurse_copy")) {

	function recurse_copy($src,$dst) { 
	    $dir = opendir($src); 
	    
	    if (false === is_dir($dst)) {
	    	@mkdir($dst);
	    }
	    
	    while(false !== ( $file = readdir($dir)) ) { 
	        if (( $file != '.' ) && ( $file != '..' )) { 
	            if ( is_dir($src . '/' . $file) ) { 
	                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	            else { 
	                copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	        } 
	    } 
	    closedir($dir); 
	}

}

//without plugin
if(!is_file(GLPI_ROOT.'/index.php.bak')) {
	
	rename(GLPI_ROOT.'/index.php', GLPI_ROOT.'/index.php.bak');
	copy(GLPI_MOD_DIR.'/src/index.php', GLPI_ROOT.'/index.php');
	
	//css
	rename(GLPI_ROOT.'/css_compiled/css_styles.min.css', GLPI_ROOT.'/css_compiled/css_styles.min.css.bak');
	copy(GLPI_MOD_DIR.'/src/css/css_styles.min.css', GLPI_ROOT.'/css_compiled/css_styles.min.css');
	copy(GLPI_MOD_DIR.'/src/css/css_ind.css', GLPI_ROOT.'/css_compiled/css_ind.css');
	
	//images
	recurse_copy(GLPI_MOD_DIR.'/src/pics/', GLPI_ROOT.'/pics/');

}

	

?>
