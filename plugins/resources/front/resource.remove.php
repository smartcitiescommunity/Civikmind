<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

if (Session::getCurrentInterface() == 'central') {
   Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType());
} else {
   Html::helpHeader(PluginResourcesResource::getTypeName(2));
}

if (empty($_POST["date_end"])) {
   $_POST["date_end"] = date("Y-m-d");
}

$resource        = new PluginResourcesResource();
$checklistconfig = new PluginResourcesChecklistconfig();

if (isset($_POST["removeresources"]) && $_POST["plugin_resources_resources_id"] != 0) {

   $date     = date("Y-m-d H:i:s");
   $CronTask = new CronTask();
   $CronTask->getFromDBbyName("PluginResourcesEmployment", "ResourcesLeaving");

   $input["id"]       = $_POST["plugin_resources_resources_id"];
   $input["date_end"] = $_POST["date_end"];
   if (($_POST["date_end"] < $date)
       || ($CronTask->fields["state"] == CronTask::STATE_DISABLE)) {
      $input["is_leaving"]               = "1";
      $input["date_declaration_leaving"] = date('Y-m-d H:i:s');
   } else {
      $input["is_leaving"]               = "0";
      $input["date_declaration_leaving"] = null;
   }
   $input["plugin_resources_leavingreasons_id"] = $_POST["plugin_resources_leavingreasons_id"];
   $input["withtemplate"]                       = "0";
   $input["users_id_recipient_leaving"]         = Session::getLoginUserID();
   $input['send_notification']                  = 1;
   $resource->update($input);

   //test it
   $resource->getFromDB($_POST["plugin_resources_resources_id"]);
   $resources_checklist = PluginResourcesChecklist::checkIfChecklistExist($_POST["plugin_resources_resources_id"], PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT);
   if (!$resources_checklist) {
      $checklistconfig->addChecklistsFromRules($resource, PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT);
   }

   Session::addMessageAfterRedirect(__('Declaration of resource leaving OK', 'resources'));
   Html::back();

} else {
   if ($resource->canView() || Session::haveRight("config", UPDATE)) {
      //show remove resource form
      $resource->showResourcesToRemove();
   }
}

if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
