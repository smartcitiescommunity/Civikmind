<?php

include_once (GLPI_ROOT."/inc/based_config.php");

if (!defined("GLPI_MOD_DIR")) {
   define("GLPI_MOD_DIR", GLPI_ROOT . "/plugins/mod");
}

function deleteAll($str) {
    //It it's a file.
    if (is_file($str)) {
        //Attempt to delete it.
        return unlink($str);
    }
    //If it's a directory.
    elseif (is_dir($str)) {
        //Get a list of the files in this directory.
        $scan = glob(rtrim($str,'/').'/*');
        //Loop through the list of files.
        foreach($scan as $index=>$path) {
            //Call our recursive function.
            deleteAll($path);
        }
        //Remove the directory itself.
        return @rmdir($str);
    }
}


if(is_file(GLPI_ROOT.'/index.php.bak')) {
		
	//exec('rm '.GLPI_ROOT.'/index.php');
	array_map('unlink', array_filter((array) glob("/index.php")));
	rename(GLPI_ROOT.'/index.php.bak', GLPI_ROOT.'/index.php');
	
	rename(GLPI_ROOT.'/css_compiled/css_styles.min.css.bak', GLPI_ROOT.'/css_compiled/css_styles.min.css');
	deleteAll(GLPI_ROOT.'/css_compiled/css_ind.css');
	
	//exec('rm '.GLPI_ROOT.'/pics/favicon.ico');
	deleteAll(GLPI_ROOT.'/pics/bg');
		

}


?>
