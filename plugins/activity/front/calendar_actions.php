<?php

/*
  -------------------------------------------------------------------------
  Activity plugin for GLPI
  Copyright (C) 2019 by the Activity Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Activity.

  Activity is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Activity is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Activity. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$activity = new PluginActivityActivity();
$state = 0;
$activity_data = [];

$_POST["entities_id"] = $_SESSION['glpiactive_entity'];

// Get type and id of the event
$type = null;
$id   = 0;
if (isset($_POST['id']) && strpos($_POST['id'], '_') !== false) {
   $eventParts  = explode('_', $_POST['id']);
   $type        = $eventParts[0];
   $_POST['id'] = $eventParts[1];
}

// Convert allDay JS values to interger
if (isset($_POST['allDay'])) {
   if ($_POST['allDay'] === 'false') {
      $_POST['allDay'] = 0;
   } else if ($_POST['allDay'] === 'true') {
      $_POST['allDay'] = 1;
   }
}

// Add
if (isset($_POST["add"])) {
   switch ($type) {
      case PluginActivityActivity::$ACTIVITY_TAG :
         $activity->check(-1, CREATE, $_POST);
         if (!isset($_POST["users_id"]) || empty($_POST["users_id"])) {
            $_POST["users_id"] = Session::getLoginUserID();
         }
         $_POST["actiontime"] = strtotime($_POST["end"]) - strtotime($_POST["begin"]);
         $_POST["is_planned"] = 1;
         unset($_POST["id"]);

         if ($id = $activity->add($_POST)) {
            $state                          = 1;
            $activity->getFromDB($id);
            $activity_data['name']          = $activity->fields['name'];
            $activity_data['activity_type'] = Dropdown::getDropdownName('glpi_plugin_activity_activitytypes', $activity->fields['plugin_activity_activitytypes_id']);
            $activity_data['event_id']      = PluginActivityActivity::$ACTIVITY_TAG.'_'.$id;
            $activity_data['comment']       = $activity->fields['comment'];
            $activity_data['actiontime']    = Html::timestampToString($activity->fields['actiontime'], false);
         }
         break;
   }

   // Update
} else if (isset($_POST["update"])) {
   switch ($type) {
      case PluginActivityActivity::$ACTIVITY_TAG :
         $activity->check($_POST['id'], UPDATE, $_POST);
         if (!isset($_POST["users_id"]) || empty($_POST["users_id"])) {
            $_POST["users_id"] = Session::getLoginUserID();
         }

         // Compute action time
         $_POST["actiontime"] = strtotime($_POST["end"]) - strtotime($_POST["begin"]);
         if ($_POST['allDay']) {
            $hb = date('Y-m-d', strtotime($_POST["begin"]));
            $he = date('Y-m-d', strtotime($_POST["end"]));

            $_POST["actiontime"] = 0;
            for ($i = strtotime($hb); $i <= strtotime($he); $i = $i + 86400) {
               $_POST["actiontime"] += PluginActivityReport::getAllDay();
            }
         }

         $_POST["is_planned"] = 1;

         if (isset($_POST['entities_id'])) {
            unset($_POST['entities_id']);
         }

         if ($activity->update($_POST)) {
            $state                          = 1;
            $activity->getFromDB($_POST['id']);
            $activity_data['activity_type'] = Dropdown::getDropdownName('glpi_plugin_activity_activitytypes', $activity->fields['plugin_activity_activitytypes_id']);
            $activity_data['name']          = $activity->fields['name'];
            $activity_data['comment']       = $activity->fields['comment'];
            $activity_data['event_id']      = $_POST['id'];
            $activity_data['actiontime']    = Html::timestampToString($activity->fields['actiontime'], false);
         }
         break;
   }

   // Move
} else if (isset($_POST["move"])) {
   switch ($type) {
      case PluginActivityActivity::$ACTIVITY_TAG :
         $activity->check($_POST['id'], UPDATE);

         if ($activity->getFromDB($_POST["id"])) {
            // Compute action time
            $_POST["actiontime"] = strtotime($_POST["end"]) - strtotime($_POST["begin"]);
            if ($activity->fields['allDay']) {
               $hb                  = date('Y-m-d H:i:s', strtotime($_POST["begin"]));
               $he                  = date('Y-m-d H:i:s', strtotime($_POST["end"]));
               $_POST["actiontime"] = 0;
               for ($i = strtotime($hb); $i <= strtotime($he); $i = $i + 86400) {
                  $_POST["actiontime"] += PluginActivityReport::getAllDay();
               }
            }

            $_POST["is_planned"]                       = 1;
            $_POST["plugin_activity_activitytypes_id"] = 0;
            unset($_POST["name"]);
            unset($_POST["begin"]);

            if ($activity->update($_POST)) {
               $state                       = 1;
               $activity_data['actiontime'] = Html::timestampToString($activity->fields['actiontime'], false);
               $activity_data['name']       = $activity->fields['name'];
               $activity_data['comment']    = $activity->fields['comment'];
            }
         }
         break;
   }

   // Drop
} else if (isset($_POST["drop"])) {
   switch ($type) {
      case PluginActivityActivity::$ACTIVITY_TAG :
         $activity->check($_POST['id'], PURGE);

         if ($activity->getFromDB($_POST["id"])) {
            $_POST["is_planned"]                       = 1;
            $_POST["plugin_activity_activitytypes_id"] = 0;
            unset($_POST["name"]);

            if ($activity->update($_POST)) {
               $state = 1;
            }
         }
         break;
   }

   // Delete
} else if (isset($_POST["delete"])) {
   switch ($type) {
      case PluginActivityActivity::$ACTIVITY_TAG :
         $activity->check($_POST['id'], PURGE);

         if ($activity->delete($_POST)) {
            $state = 1;
         }
         break;
   }

   // Get activity
} else if (isset($_POST["getActivity"])) {
   switch ($type) {
      case PluginActivityActivity::$ACTIVITY_TAG :
         $activity->check($_POST['id'], UPDATE);

         if (isset($_POST['id']) && $_POST['id'] > 0) {
            $activity->getFromDB($_POST["id"]);

            $activitytype = new PluginActivityActivityType();
            $activitytype->getFromDB($activity->fields['plugin_activity_activitytypes_id']);

            $activity_data['activity_type']    = $activitytype->fields['name'];
            $activity_data['activitytypes_id'] = $activity->fields['plugin_activity_activitytypes_id'];
            $activity_data['is_usedbycra']     = $activity->fields['is_usedbycra'];
            $activity_data['name']             = $activity->fields['name'];
            $activity_data['comment']          = $activity->fields['comment'];

            $state = 1;
         }
         break;
   }

   // Refresh modal
} else if (isset($_POST["refreshModal"])) {
   PluginActivityActivity::showActivityModalForm($_POST['rand']);
   echo "<input type='hidden' id='new_activity_csrf' value='".Session::getNewCSRFToken()."'>";
   return true;
}

header('Content-Type: application/json; charset=UTF-8"');

$message = !empty($_SESSION["MESSAGE_AFTER_REDIRECT"]) ? $_SESSION["MESSAGE_AFTER_REDIRECT"] : '';
echo json_encode(['csrf_token' => Session::getNewCSRFToken(),
                       'message'    => $message,
                       'state'      => $state,
                       'event_data' => $activity_data]);
$_SESSION["MESSAGE_AFTER_REDIRECT"] = "";