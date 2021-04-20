<?php
/*
 * @version $Id: action.php 149 2013-07-10 09:54:40Z tsmr $
 LICENSE

 This file is part of the uninstall plugin.

 Datainjection plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Uninstall plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with uninstall. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   uninstall
 @author    the uninstall plugin team
 @copyright Copyright (c) 2010-2013 Uninstall plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/uninstall
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ('../../../inc/includes.php');

Html::header(__('Transfer'), $_SERVER['PHP_SELF'], "admin", "transfer");

if (!isset($_REQUEST["device_type"])
    || !isset($_REQUEST["model_id"])
    || ($_REQUEST["model_id"] == 0)) {
   Html::back();
}

if (isset($_REQUEST["locations_id"])) {
   $location = $_REQUEST["locations_id"];
} else {
   $location = PluginUninstallPreference::getLocationByUserByEntity($_SESSION["glpiID"],
                                                                    $_REQUEST["model_id"],
                                                                    $_SESSION["glpiactive_entity"]);
}

if (isset($_REQUEST["replace"])) {

   PluginUninstallReplace::replace($_REQUEST["device_type"], $_REQUEST["model_id"],
                                   $_REQUEST['newItems'], $location);

   unset($_SESSION['glpi_uninstalllist']);
   Session::addMessageAfterRedirect(__('Replacement successful', 'uninstall'));

   Html::footer();

   $device_type = $_REQUEST["device_type"];
   Html::redirect($device_type::getSearchURL());
}

$model = new PluginUninstallModel();
$model->getConfig($_REQUEST["model_id"]);

//Case of a uninstallation initiated from the object form
if (isset($_REQUEST["uninstall"])) {

   //Uninstall only if a model is selected
   if ($model->fields['types_id'] == PluginUninstallModel::TYPE_MODEL_UNINSTALL) {
      //Massive uninstallation

      PluginUninstallUninstall::uninstall($_REQUEST["device_type"], $_REQUEST["model_id"],
                                          [$_REQUEST["device_type"]
                                                => [$_REQUEST["id"] => $_REQUEST["id"]]],
                                          $location);
      Html::back();
   } else {
      PluginUninstallReplace::showForm($_REQUEST["device_type"], $_REQUEST["model_id"],
                                       [$_REQUEST["device_type"]
                                             => [$_REQUEST["id"] => $_REQUEST["id"]]],
                                       $location);
      Html::footer();
   }

} else {

   if ($model->fields['types_id'] == PluginUninstallModel::TYPE_MODEL_UNINSTALL) {
      //Massive uninstallation
      if (isset($_SESSION['glpi_uninstalllist'])) {
         PluginUninstallUninstall::uninstall($_REQUEST["device_type"], $_REQUEST["model_id"],
                                             $_SESSION['glpi_uninstalllist'], $location);
      }

      unset($_SESSION['glpi_uninstalllist']);
      Session::addMessageAfterRedirect(__('Uninstallation successful', 'uninstall'));

      Html::footer();

      $device_type = $_REQUEST["device_type"];
      Html::redirect($device_type::getSearchURL());

   } else {
      if (isset($_SESSION['glpi_uninstalllist'])) {
         PluginUninstallReplace::showForm($_REQUEST["device_type"], $_REQUEST["model_id"],
                                          $_SESSION['glpi_uninstalllist'], $location);
      }
      Html::footer();
   }
}
