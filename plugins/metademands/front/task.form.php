<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
include ('../../../inc/includes.php');
Session::checkLoginUser();

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$task = new PluginMetademandsTask();
$tickettask = new PluginMetademandsTicketTask();
$metademandtask = new PluginMetademandsMetademandTask();

if (isset($_POST["add"])) {
   if (isset($_POST['taskType'])) {
      // Check update rights for clients
      $task->check(-1, UPDATE, $_POST);
      $_POST['plugin_metademands_tasks_id'] = isset($_POST['parent_tasks_id']) ? $_POST['parent_tasks_id'] : 0;

      if($_POST['block_use'] == ''){
         $_POST['block_use'] = json_encode([]);
      } else{
         $_POST['block_use'] = json_encode($_POST['block_use']);
      }
      $_POST['type'] = $_POST['taskType'];
      if ($tickettask->isMandatoryField($_POST) && $tasks_id = $task->add($_POST)) {
         if ($_POST['taskType'] == PluginMetademandsTask::TICKET_TYPE) {
            //         $parent_task = $_POST['parent_tasks_id'];
            //         if(!empty($parent_task)){
            //            // Get first child
            //            $first_child_task = $task->getChildrenForLevel($parent_task, 2);
            //            // Child
            //            $task->update(array('id' => $first_child_task, 'plugin_metademands_tasks_id' => $tasks_id));
            //         } else {
            //
            //         }

            $_POST['plugin_metademands_tasks_id'] = $tasks_id;
            $_POST['type'] = Ticket::DEMAND_TYPE;
            $tickettask->add($_POST);
         } else {
            if ($_POST['link_metademands_id']) {
               $metademandtask->add(['plugin_metademands_tasks_id' => $tasks_id,
                   'plugin_metademands_metademands_id' => $_POST['link_metademands_id']]);
            }
         }
      }
   }

   //   PluginMetademandsMetademand::addLog($_POST, PluginMetademandsMetademand::LOG_ADD);
   Html::back();

} else if (isset($_POST['up'])) {
   // Replace current parent task by parent's parent task
   foreach ($_POST["up"] as $tasks_id => $parent_task) {

      $parent_task = key($parent_task);

      if ($task->can($tasks_id, UPDATE)) {
         // Get parent data
         $parentTaskData = new PluginMetademandsTask();
         $parentTaskData->getFromDB($parent_task);
         // Get first child
         //         $first_child_task = $task->getChildrenForLevel($tasks_id, 2);

         // Current
         $task->update(['id' => $tasks_id, 'plugin_metademands_tasks_id' => $parentTaskData->fields['plugin_metademands_tasks_id']]);
         //         // Parent
         //         $task->update(array('id' => $parent_task, 'plugin_metademands_tasks_id' => $tasks_id));
         //         // Child
         //         $task->update(array('id' => $first_child_task, 'plugin_metademands_tasks_id' => $parent_task));
      }
   }

   Html::back();

} else if (isset($_POST['down'])) {
   // Replace current parent task by parent's parent task
   foreach ($_POST["down"] as $tasks_id => $parent_task) {

      $parent_task = key($parent_task);

      if ($task->can($tasks_id, UPDATE)) {
         // Get first child
         $task->getFromDB($tasks_id);
         $first_child_task = $task->getChildrenForLevel($parent_task, $task->fields['level']);
         $first_child_task = array_shift($first_child_task);

         // Get second child
         //         $second_child_task = $task->getChildrenForLevel($first_child_task, 2);
         //
         //         // First child
         //         $task->update(array('id' => $first_child_task, 'plugin_metademands_tasks_id' => $parent_task));
         //         // Current
         //         $task->update(array('id' => $tasks_id, 'plugin_metademands_tasks_id' => $first_child_task));
         //         // Second child
         //         $task->update(array('id' => $second_child_task, 'plugin_metademands_tasks_id' => $tasks_id));

         // Current
         $task->update(['id' => $tasks_id, 'plugin_metademands_tasks_id' => $first_child_task]);
      }
   }

   Html::back();

} else if (isset($_POST['showForMetademands'])) {
   $_SESSION["metademandsHelpdeskSaved"] = $_POST;
   Html::back();

} else {
   $task->checkGlobal(READ);
   Html::header(PluginMetademandsTask::getTypeName(2), '', "helpdesk", "pluginmetademandsmetademand");
   $task->display(['id' => $_GET["id"]]);
   Html::footer();
}