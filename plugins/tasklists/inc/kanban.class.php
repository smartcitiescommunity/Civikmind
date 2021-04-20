<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Tasklists plugin for GLPI
 Copyright (C) 2003-2016 by the Tasklists Development Team.

 https://github.com/InfotelGLPI/tasklists
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Tasklists.

 Tasklists is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Tasklists is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Tasklists. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginTasklistsKanban
 */
class PluginTasklistsKanban extends CommonGLPI {

   static $rightname = 'plugin_tasklists';

   /**
    * @return bool
    */
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return bool
    */
   static function canCreate() {
      return Session::haveRight(self::$rightname, CREATE);
   }

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {
      return __('Kanban', 'tasklists');
   }


   /**
    * @param array $options
    *
    * @return array
    */
   function defineTabs($options = []) {

      $ong = [];
      $this->addStandardTab(__CLASS__, $ong, $options);
      $ong['no_all_tab'] = true;

      return $ong;
   }

   /**
    * @param $id
    *
    * @return int
    */
   static function countTasksForKanban($id) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_tasklists_tasks',
                                        ["plugin_tasklists_tasktypes_id" => $id,
                                         "is_template"                   => 0]);
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $withtemplate
    *
    * @return array|bool|string
    * @throws \GlpitestSQLError
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      global $DB, $CFG_GLPI;

      $dbu   = new DbUtils();
      $query = "SELECT `glpi_plugin_tasklists_tasktypes`.*
                FROM `glpi_plugin_tasklists_tasktypes` ";
      $query .= $dbu->getEntitiesRestrictRequest('WHERE', 'glpi_plugin_tasklists_tasktypes', '', $_SESSION["glpiactiveentities"], true);
      $query .= "ORDER BY `name`";
      $tabs  = [];
      if ($item->getType() == __CLASS__) {
         if ($result = $DB->query($query)) {
            if ($DB->numrows($result)) {
               while ($data = $DB->fetchArray($result)) {
                  //                  if (self::countTasksForKanban($data["id"]) > 0) {
                  if (PluginTasklistsTypeVisibility::isUserHaveRight($data["id"])) {
                     $tabs[$data["id"]] = $data["completename"];
                  }
                  //                  }
               }
            }
         }
         if (count($tabs) == 0) {
            echo "<div align='center'><br><br><i class='fas fa-exclamation-triangle fa-4x' style='color:orange'></i><br><br>";
            echo "<b>" . __("You don't have the right to see any context", 'tasklists') . "</b></div>";
            return false;
         }

         return $tabs;
      }

      return '';
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $tabnum
    * @param int         $withtemplate
    *
    * @return bool
    * @throws \GlpitestSQLError
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == __CLASS__) {
         self::showKanban($tabnum);
      }
      return true;
   }


   static function showKanban($ID) {
      $project = new Project();

      /*   if (($ID <= 0 && !Project::canView()) ||
            ($ID > 0 && (!$project->getFromDB($ID) || !$project->canView()))) {
            return false;
         }
   */
      $supported_itemtypes = [];
      if (PluginTasklistsTask::canCreate()) {
         $supported_itemtypes['PluginTasklistsTask'] = [
            'name'   => PluginTasklistsTask::getTypeName(1),
            'fields' => [
               'name'    => [
                  'placeholder' => __('Name')
               ],
               'content' => [
                  'placeholder' => __('Content'),
                  'type'        => 'textarea'
               ]
            ]
         ];
      }

      $column_field = [
         'id'           => 'plugin_tasklists_taskstates_id',
         'extra_fields' => [
            'color' => [
               'type' => 'color'
            ]
         ]
      ];

      if ($ID > 0) {
         $item_id = $ID;
      } else {
         $item_id = PluginTasklistsPreference::checkPreferenceValue("default_type", Session::getLoginUserID());
      }
      if ($item_id == 0) {
         echo "<div align='center'><br><br>";
         echo "<i class='fas fa-exclamation-triangle fa-4x' style='color:orange'></i><br><br>";
         echo "<b>" . __("There is no accessible context", "tasklists") . "</b></div>";
      } else {
         $supported_itemtypes = json_encode($supported_itemtypes, JSON_FORCE_OBJECT);
         $column_field        = json_encode($column_field, JSON_FORCE_OBJECT);

         echo "<div id='kanban' class='kanban'></div>";
         $refresh = 0;
         if (PluginTasklistsPreference::checkPreferenceValue("automatic_refresh", Session::getLoginUserID()) != 0) {
            $refresh = PluginTasklistsPreference::checkPreferenceValue("automatic_refresh_delay", Session::getLoginUserID());
         }
         $darkmode       = ($_SESSION['glpipalette'] === 'darker') ? 'true' : 'false';
         $canadd_item    = json_encode(self::canCreate());
         $canmodify_view = json_encode(Session::haveRight("plugin_tasklists_config", 1));
         //      $canmodify_view = json_encode(($ID == 0 || $project->canModifyGlobalState()));
         $cancreate_column      = json_encode((bool)Session::haveRight("plugin_tasklists_config", 1));
         $limit_addcard_columns = $canmodify_view !== 'false' ? '[]' : json_encode([0]);
         $can_order_item        = json_encode((bool)PluginTasklistsTypeVisibility::isUserHaveRight($item_id));


         $js = <<<JAVASCRIPT
         $(function(){
            // Create Kanban
            var kanban = new GLPIKanban({
               element: "#kanban",
               allow_add_item: $canadd_item,
               allow_modify_view: $canmodify_view,
               allow_create_column: $cancreate_column,
               limit_addcard_columns: $limit_addcard_columns,
               allow_order_card: $can_order_item,
               supported_itemtypes: $supported_itemtypes,
               dark_theme: {$darkmode},
               max_team_images: 3,
               column_field: $column_field,
               background_refresh_interval:  $refresh,
               item: {
                  itemtype: 'PluginTasklistsTaskType',
                  items_id: $item_id
               }
            });
            // Create kanban elements and add data
            kanban.init();
         });
JAVASCRIPT;
         echo Html::scriptBlock($js);
      }
   }

   public function canOrderKanbanCard($ID) {
      if ($ID > 0) {
         $this->getFromDB($ID);
      }
      return ($ID <= 0 || $this->canModifyGlobalState());
   }

   public static function getLocalizedKanbanStrings() {
      $strings = [
         'Add'                               => __('Add'),
         'Delete'                            => __('Delete'),
         'Close'                             => __('Close'),
         'Toggle collapse'                   => __('Toggle collapse', 'tasklists'),
         'Search'                            => __('Search', 'tasklists'),
         'Search or filter results'          => __('Search or filter results', 'tasklists'),
         'Add column'                        => __('Add status', 'tasklists'),
         'Create status'                     => __('Create status', 'tasklists'),
         '%d other team members'             => __('%d other team members'),
         'Add a column from existing status' => __('Add a column from existing status', 'tasklists'),
         'Or add a new status'               => __('Or add a new status', 'tasklists'),
         'users'                             => _n('User', 'Users', 2),
         'status'                            => __('Status'),
         'add_tasks'                         => __('Add task', 'tasklists'),

         'archive_all_tasks'       => __('Archive all tasks of this state', 'tasklists'),
         'see_archived_tasks'      => __('See archived tasks', 'tasklists'),
         'hide_archived_tasks'     => __('Hide archived tasks', 'tasklists'),
         'clone_task'              => __('Clone task', 'tasklists'),
         'see_progress_tasks'      => __('See tasks in progress', 'tasklists'),
         'see_my_tasks'            => __('See tasks of', 'tasklists'),
         'see_all_tasks'           => __('See all tasks', 'tasklists'),
         'alert_archive_task'      => __('Are you sure you want to archive this task ?', 'tasklists'),
         'alert_archive_all_tasks' => __('Are you sure you want to archive all tasks ?', 'tasklists'),
         'archive_task'            => __('Archive this task', 'tasklists'),
         'update_priority'         => __('Update priority of task', 'tasklists'),
         'see_details'             => __('Details of task', 'tasklists'),
      ];
      return $strings;
   }
}
