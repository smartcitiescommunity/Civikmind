<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

$AJAX_INCLUDE = 1;

include('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
   // Get AJAX input and load it into $_REQUEST
   $input = file_get_contents('php://input');
   parse_str($input, $_REQUEST);
}

if (!isset($_REQUEST['action'])) {
   Toolbox::logError("Missing action parameter");
   http_response_code(400);
   return;
}
$action = $_REQUEST['action'];

if ($action === 'get_translated_strings') {
   header("Content-Type: application/json; charset=UTF-8", true);
   echo json_encode((PluginTasklistsKanban::getLocalizedKanbanStrings()), JSON_FORCE_OBJECT);
   return;
}

$nonkanban_actions = ['update', 'add_item', 'move_item'];
if (isset($_REQUEST['itemtype'])) {

   $traits = class_uses($_REQUEST['itemtype'], true);
   if (!in_array($_REQUEST['action'], $nonkanban_actions)
       && (!$traits || !in_array('Kanban', $traits))
       && $_REQUEST['itemtype'] != "PluginTasklistsTaskType") {
      // Bad request
      // For all actions, except those in $nonkanban_actions, we expect to be manipulating the Kanban itself.
      Toolbox::logError("Invalid itemtype parameter");
      http_response_code(400);
      return;
   }
   /** @var CommonDBTM $item */
   $itemtype = $_REQUEST['itemtype'];
   $item     = new $itemtype();
}

// Rights Checks
if (isset($itemtype)) {
   if (in_array($action, ['refresh', 'get_switcher_dropdown', 'get_column'])) {
      $itemtoV = $item;
      if ($itemtype == PluginTasklistsTaskType::getType()){
         $itemtoV = new PluginTasklistsTask();
      }
      if (!$itemtoV->canView()) {
         // Missing rights
         http_response_code(403);
         return;
      }
   }
   if (in_array($action, ['update']) && isset($_REQUEST['items_id'])) {
      $item->getFromDB($_REQUEST['items_id']);
      if (!$item->canUpdateItem()) {
         // Missing rights
         http_response_code(403);
         return;
      }
   }
   if (in_array($action, ['add_item'])) {
      if (!$item->canCreate()) {
         // Missing rights
         http_response_code(403);
         return;
      }
   }
}

// Helper to check required parameters
$checkParams = function ($required) {
   foreach ($required as $param) {
      if (!isset($_REQUEST[$param])) {
         Toolbox::logError("Missing $param parameter");
         http_response_code(400);
         die();
      }
   }
};

// Action Processing
if ($_REQUEST['action'] == 'update' && isset($_REQUEST['items_id'])) {
   $checkParams(['column_field', 'column_value']);
   // Update project or task based on changes made in the Kanban
   $item->update([
                    'id'                      => $_REQUEST['items_id'],
                    $_REQUEST['column_field'] => $_REQUEST['column_value']
                 ]);

} else if ($_REQUEST['action'] == 'add_item') {

   $checkParams(['inputs']);
   $item   = new $itemtype();
   $inputs = [];
   parse_str($_REQUEST['inputs'], $inputs);
   $item->add($inputs);

} else if ($_REQUEST['action'] == 'move_item') {

   $checkParams(['card', 'column', 'position', 'kanban']);
   $task = new PluginTasklistsTask();
   if ($task->getFromDB($_REQUEST['card'])) {
      $d                                   = [];
      $d['plugin_tasklists_taskstates_id'] = $_REQUEST['column'];
      $d['id']                             = $_REQUEST['card'];

      if (($task->fields['users_id'] == Session::getLoginUserID() && Session::haveRight("plugin_tasklists", UPDATE))
          || Session::haveRight("plugin_tasklists_see_all", 1)) {
         $task->update($d);
      }
   }

} else if ($_REQUEST['action'] == 'show_column') {

   $checkParams(['column', 'kanban']);
   Item_Kanban::showColumn($_REQUEST['kanban']['itemtype'], $_REQUEST['kanban']['items_id'], $_REQUEST['column']);

} else if ($_REQUEST['action'] == 'hide_column') {

   $checkParams(['column', 'kanban']);
   Item_Kanban::hideColumn($_REQUEST['kanban']['itemtype'], $_REQUEST['kanban']['items_id'], $_REQUEST['column']);

} else if ($_REQUEST['action'] == 'collapse_column') {

   $checkParams(['column', 'kanban']);
   PluginTasklistsItem_Kanban::collapseColumn($_REQUEST['kanban']['itemtype'], $_REQUEST['kanban']['items_id'], $_REQUEST['column']);

} else if ($_REQUEST['action'] == 'expand_column') {

   $checkParams(['column', 'kanban']);
   PluginTasklistsItem_Kanban::expandColumn($_REQUEST['kanban']['itemtype'], $_REQUEST['kanban']['items_id'], $_REQUEST['column']);

} else if ($_REQUEST['action'] == 'move_column') {

   global $DB;
   $checkParams(['column', 'kanban', 'position']);
   $dbu   = new DbUtils();
   $table = $dbu->getTableForItemType('PluginTasklistsStateOrder');

   $stateorder = new PluginTasklistsStateOrder();
   $stateorder->getFromDBByCrit(["plugin_tasklists_taskstates_id" => $_REQUEST["column"], "plugin_tasklists_tasktypes_id" => $_REQUEST["kanban"]["items_id"]]);

   $id_item            = $stateorder->getID();
   $_POST['new_order'] = $_REQUEST['position'];
   $_POST['old_order'] = $stateorder->getField('ranking');
   // Réorganisation de tout les champs
   if ($_POST['old_order'] < $_POST['new_order']) {

      $DB->query("UPDATE $table SET
               `ranking` = `ranking`-1
               WHERE `plugin_tasklists_tasktypes_id` = {$_REQUEST["kanban"]["items_id"]}
               AND `ranking` > {$_POST['old_order']}
               AND `ranking` <= {$_POST['new_order']}");
   } else {

      $DB->query("UPDATE $table SET
               `ranking` = `ranking`+1
               WHERE `plugin_tasklists_tasktypes_id` = {$_REQUEST["kanban"]["items_id"]}
               AND `ranking` < {$_POST['old_order']}
               AND `ranking` >= {$_POST['new_order']}");
   }

   if (isset($id_item) && $id_item > 0) {
      $DB->query("UPDATE $table SET
               `ranking` = {$_POST['new_order']}
               WHERE id = $id_item");
   }
   /*
      Item_Kanban::moveColumn($_REQUEST['kanban']['itemtype'], $_REQUEST['kanban']['items_id'],
         $_REQUEST['column'], $_REQUEST['position']);
   */
} else if ($_REQUEST['action'] == 'refresh') {

   $checkParams(['column_field']);
   // Get all columns to refresh the kanban
   header("Content-Type: application/json; charset=UTF-8", true);
   $columns = $itemtype::getKanbanColumns($_REQUEST['items_id'], $_REQUEST['column_field'], [], true);
   echo json_encode($columns, JSON_FORCE_OBJECT);

} else if ($_REQUEST['action'] == 'get_switcher_dropdown') {

   $values = $itemtype::getAllForKanban();
   $vals   = [];
   foreach ($values as $key => $value) {
      if (PluginTasklistsTypeVisibility::isUserHaveRight($key)) {
         $vals[$key] = $value;
      }
   }
   Dropdown::showFromArray('kanban-board-switcher', $vals, [
      'value' => isset($_REQUEST['items_id']) ? $_REQUEST['items_id'] : ''
   ]);

} else if ($_REQUEST['action'] == 'get_url') {

   $checkParams(['items_id']);

   $kb = new PluginTasklistsKanban();
   echo $kb->getSearchURL() . '?context_id=' . $_REQUEST['items_id'];
   return;

} else if ($_REQUEST['action'] == 'load_column_state') {

   $checkParams(['items_id', 'last_load']);
   header("Content-Type: application/json; charset=UTF-8", true);
   $response = [
      'state'     => [],
      'timestamp' => $_SESSION['glpi_currenttime']
   ];
   echo json_encode($response, JSON_FORCE_OBJECT);

} else if ($_REQUEST['action'] == 'list_columns') {

   $checkParams(['column_field']);
   header("Content-Type: application/json; charset=UTF-8", true);
   echo json_encode(PluginTasklistsTaskState::getAllKanbanColumns());

} else if ($_REQUEST['action'] == 'get_column') {

   $checkParams(['column_id', 'column_field', 'items_id']);
   header("Content-Type: application/json; charset=UTF-8", true);
   $column = $itemtype::getKanbanColumns($_REQUEST['items_id'], $_REQUEST['column_field'], [$_REQUEST['column_id']]);
   echo json_encode($column, JSON_FORCE_OBJECT);

} else if ($_REQUEST['action'] == 'add_status_context') {

   header("Content-Type: application/json; charset=UTF-8", true);
   $taskState = new PluginTasklistsTaskState();
   $taskState->getFromDB($_REQUEST['column']);
   $contexts           = json_decode($taskState->getField('tasktypes'));
   $contexts[]         = $_REQUEST['context_id'];
   $input              = [];
   $input["tasktypes"] = $contexts;
   $input["id"]        = $_REQUEST['column'];
   $taskState->update($input);
   PluginTasklistsStateOrder::addStateContext($_REQUEST['context_id'], $_REQUEST['column']);
   echo json_encode(true, JSON_FORCE_OBJECT);

} else if ($_REQUEST['action'] == 'remove_status_context') {

   header("Content-Type: application/json; charset=UTF-8", true);
   $taskState = new PluginTasklistsTaskState();
   $taskState->getFromDB($_REQUEST['column']);
   $contexts = json_decode($taskState->getField('tasktypes'));
   if (($key = array_search($_REQUEST['context_id'], $contexts)) !== false) {
      unset($contexts[$key]);
   }
   $input              = [];
   $input["tasktypes"] = $contexts;
   $input["id"]        = $_REQUEST['column'];
   $taskState->update($input);
   PluginTasklistsStateOrder::removeStateContext($_REQUEST['context_id'], $_REQUEST['column']);
   echo json_encode(true, JSON_FORCE_OBJECT);
}
