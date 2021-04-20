<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2011 by the mreporting Development Team.

 https://forge.indepnet.net/projects/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Session::checkLoginUser();
Html::header(__("More Reporting", 'mreporting'), '', 'tools', 'PluginMreportingCommon', 'dashboard_list');
$common = new PluginMreportingCommon();

/*** Regular Tab ***/
$reports = $common->getAllReports();
$tabs = [];
foreach ($reports as $classname => $report) {

   $tabs[$classname]=['title'=>$report['title'],
                      'url'=>Plugin::getWebDir('mreporting')."/ajax/common.tabs.php",
                      'params'=>"target=".$_SERVER['PHP_SELF']."&classname=$classname"];
}

if (count($tabs) > 0) {
   //foreach tabs
   foreach ($tabs as $tab) {
      global $DB;
      $params = (isset($tab['params'])?$tab['params']:'');
      //we get the classname
      $classname = str_replace("target=".$_SERVER['PHP_SELF']."&classname=", '', $params);

      //we found all reports for classname where current profil have right
      $query = "SELECT *
            FROM `glpi_plugin_mreporting_configs`,`glpi_plugin_mreporting_profiles`
            WHERE `glpi_plugin_mreporting_configs`.`id` = `glpi_plugin_mreporting_profiles`.`reports`
            AND `glpi_plugin_mreporting_configs`.`classname` = '$classname'
            AND `glpi_plugin_mreporting_profiles`.`right` = ".READ."
            AND `glpi_plugin_mreporting_profiles`.`profiles_id` = ".$_SESSION['glpiactiveprofile']['id'];

      //for this classname if current user have no right on any reports
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) == 0) {
            //we unset the index
            unset($tabs[$classname]);
         }
      }
   }

   //finally if tabs is empty
   if (empty($tabs)) {
      echo "<div class='center'><br>".__("No report is available !", 'mreporting')."</div>";
   } else {
      echo "<div id='tabspanel' class='center-h'></div>";
      Ajax::createTabs('tabspanel', 'tabcontent', $tabs, 'PluginMreportingCommon');
   }

} else {
   echo "<div class='center'><br>".__("No report is available !", 'mreporting')."</div>";
}

Html::footer();
