<?php


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

if (!defined("GLPI_MOD_DIR")) {
   define("GLPI_MOD_DIR",GLPI_ROOT . "/plugins/mod");
}

class PluginMod extends CommonDBTM {

   static function displayIndicators() {
      global $CFG_GLPI;
      
	   include(GLPI_MOD_DIR.'/inc/indicator.inc.php');
	}	
	

}
?>