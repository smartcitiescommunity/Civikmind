<?php
/**
 * Install all necessary elements for the plugin
 *
 * @return boolean True if success
 */
function plugin_yagp_install() {

   $migration = new Migration(PLUGIN_YAGP_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginYagp' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'install')) {
            $classname::install($migration);
         }
      }
   }

   return true;
}
/**
 * Uninstall previously installed elements of the plugin
 *
 * @return boolean True if success
 */
function plugin_yagp_uninstall() {

   $migration = new Migration(PLUGIN_YAGP_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginYagp' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'uninstall')) {
            $classname::uninstall($migration);
         }
      }
   }

   return true;
}

function plugin_yagp_updateitem(CommonDBTM $item) {
   if ($item::getType()=="PluginYagpConfig") {
      $input=$item->input;
      if ($input["ticketsolveddate"]==1) {
         Crontask::Register("PluginYagpTicketsolveddate", 'changeDate', HOUR_TIMESTAMP, [
            'state'=>1,
            'mode'  => CronTask::MODE_EXTERNAL
         ]);
      } else if ($input["ticketsolveddate"]==0) {
         Crontask::Unregister("YagpTicketsolveddate");
      }
      if ($input["contractrenew"]==1) {
         Crontask::Register("PluginYagpContractrenew", 'renewContract', DAY_TIMESTAMP, [
            'state'=>1,
            'mode'  => CronTask::MODE_EXTERNAL
         ]);
      } else if ($input["contractrenew"]==0) {
         Crontask::Unregister("YagpContractrenew");
      }
   }
}