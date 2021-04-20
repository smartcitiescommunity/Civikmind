<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 moreticket plugin for GLPI
 Copyright (C) 2013-2016 by the moreticket Development Team.

 https://github.com/InfotelGLPI/moreticket
 -------------------------------------------------------------------------

 LICENSE

 This file is part of moreticket.

 moreticket is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 moreticket is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with moreticket. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

$plugin = new Plugin();
if ($plugin->isActivated("moreticket")) {

   $config = new PluginMoreticketConfig();

   if (isset($_POST["update"])) {
      if (isset($_POST['solution_status'])) {
         $_POST['solution_status'] = json_encode($_POST['solution_status']);
      } else {
         $_POST['solution_status'] = "";
      }

      $dbu = new DbUtils();
      if (isset($_POST['urgency_ids'])) {
         $_POST['urgency_ids'] = $dbu->exportArrayToDB($_POST['urgency_ids']);
      } else {
         $_POST['urgency_ids'] = $dbu->exportArrayToDB([]);
      }

      $config->update($_POST);
      //Update singelton
      PluginMoreticketConfig::getConfig(true);
      Html::redirect($_SERVER['HTTP_REFERER']);

   } else {
      Html::header(PluginMoreticketConfig::getTypeName(), '', "plugins", "moreticket");
      $config->showForm();
      Html::footer();
   }

} else {
   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div align='center'><br><br>";
   echo "<i class='fas fa-exclamation-triangle fa-4x' style='color:orange'></i><br><br>";
   echo "<b>" . __('Please activate the plugin', 'moreticket') . "</b></div>";
   Html::footer();
}
