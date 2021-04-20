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

include('../../../inc/includes.php');

Html::header_nocache();

$preferences = new PluginActivityPreference();

Session::checkLoginUser();

switch ($_POST ['action']) {
   case 'add_manager_view':
      $preferences->showAddManagerView();
      break;
   case 'add_manager':
      if ($_POST['manager_id'] == 0) {
         $message = __('Please select a manager', 'activity');
      } else {
         $preferences->fields['users_id']          = Session::getLoginUserID();
         $preferences->fields['users_id_validate'] = $_POST['manager_id'];
         $idPreferences                            = $preferences->add($preferences->fields);
         if ($idPreferences) {
            $preferences->addManagerToView($_POST['manager_id']);
            $message = __('Manager sucessfully added.', 'activity');
         } else {
            $message = __('An error happend while adding the manager', 'activity');
         }
      }
      $preferences->showMessage($message);

      break;
   case 'delete_manager':
      $message = __('An error happend while deleting the manager', 'activity');
      if ($_POST['manager_id'] != 0) {
         $preferences->getFromDBByCrit(['users_id_validate' => $_POST['manager_id'],
                                        'users_id'          => Session::getLoginUserID()]);
         if (isset($preferences->fields['id']) && $preferences->fields['id'] > 0) {
            $oldId = $preferences->fields['users_id_validate'];
            if ($preferences->delete($preferences->fields)) {
               $preferences->removeManagerFroMView($oldId);
               $message = __('Manager sucessfully deleted.', 'activity');
            }
         }
      }
      $preferences->showMessage($message);

      break;

   default :
      break;
}
