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

// Class for a Dropdown

/**
 * Class PluginTasklistsTaskType
 */
class PluginTasklistsTaskType extends CommonTreeDropdown {

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {

      return _n('Context', 'Contexts', $nb, 'tasklists');
   }

   static $rightname = 'plugin_tasklists_config';

   /**
    * @param array $options
    *
    * @return array
    * @see CommonGLPI::defineTabs()
    *
    */
   function defineTabs($options = []) {

      $ong = parent::defineTabs($options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('PluginTasklistsStateOrder', $ong, $options);
      $this->addStandardTab('PluginTasklistsTypeVisibility', $ong, $options);
      return $ong;
   }


   /**
    * @return array
    */
   static function getAllForKanban() {
      $self = new self();

      $list  = $self->find([], ["completename ASC"]);
      $items = [

      ];

      foreach ($list as $key => $value) {
         $self->getFromDB($value['id']);
         if (!$self->haveChildren()) {
            $items[$value['id']] = $value['completename'];
         }

      }
      return $items;
   }

   /**
    * @return bool
    */
   public function forceGlobalState() {
      // All users must be using the global state unless viewing the global Kanban
      return $this->getID() > 0;
   }

   /**
    * @param $ID
    * @param $entity
    *
    * @return ID|int|the
    * @throws \GlpitestSQLError
    */
   static function transfer($ID, $entity) {
      global $DB;

      if ($ID > 0) {
         // Not already transfer
         // Search init item
         $query = "SELECT *
                   FROM `glpi_plugin_tasklists_tasktypes`
                   WHERE `id` = '$ID'";

         if ($result = $DB->query($query)) {
            if ($DB->numrows($result)) {
               $data                                   = $DB->fetchAssoc($result);
               $data                                   = Toolbox::addslashes_deep($data);
               $input['name']                          = $data['name'];
               $input['entities_id']                   = $entity;
               $input['is_recursive']                  = $data['is_recursive'];
               $input['plugin_tasklists_tasktypes_id'] = $data['plugin_tasklists_tasktypes_id'];
               $temp                                   = new self();
               $newID                                  = $temp->getID();

               if ($newID < 0) {
                  $newID = $temp->import($input);
               }

               return $newID;
            }
         }
      }
      return 0;
   }

   /**
    * @param       $ID
    * @param       $column_field
    * @param array $column_ids
    * @param bool  $get_default
    *
    * @return array
    */
   static function getKanbanColumns($ID, $column_field, $column_ids = [], $get_default = false) {

      if (!PluginTasklistsTypeVisibility::isUserHaveRight($ID)) {
         return [];
      }
      $dbu        = new DbUtils();
      $cond       = ["plugin_tasklists_taskstates_id" => 0,
                     "plugin_tasklists_tasktypes_id"  => $ID,
                     "is_deleted"                     => 0,
                     "is_template"                    => 0,
                     "is_archived"                    => isset($_SESSION["archive"][Session::getLoginUserID()]) ? json_decode($_SESSION["archive"][Session::getLoginUserID()]) : 0]
                    + $dbu->getEntitiesRestrictCriteria('glpi_plugin_tasklists_tasks', '', $_SESSION["glpiactiveentities"], true);
      $countTasks = $dbu->countElementsInTable($dbu->getTableForItemType('PluginTasklistsTasks'),
                                               $cond);
      $states[]   = ['id'       => 0,
                     'name'     => __('Backlog', 'tasklists'),
                     'rank'     => 0,
                     'count'    => $countTasks,
                     'folded'   => PluginTasklistsItem_Kanban::loadStateForItem(PluginTasklistsTaskType::getType(), $ID, 0),
                     'finished' => 0];
      $nb         = 1;
      $datastates = $dbu->getAllDataFromTable($dbu->getTableForItemType('PluginTasklistsTaskState'));
      if (!empty($datastates)) {
         foreach ($datastates as $datastate) {
            $tasktypes = json_decode($datastate['tasktypes']);
            if (is_array($tasktypes)) {
               if (in_array($ID, $tasktypes)) {
                  $condition = ['plugin_tasklists_taskstates_id' => $datastate['id'],
                                'plugin_tasklists_tasktypes_id'  => $ID];
                  $order     = new PluginTasklistsStateOrder();
                  $ranks     = $order->find($condition);
                  $ranking   = 0;
                  if (count($ranks) > 0) {
                     foreach ($ranks as $rank) {
                        $ranking = $rank['ranking'];
                     }
                  }
                  $cond       = ["plugin_tasklists_taskstates_id" => $datastate['id'],
                                 "plugin_tasklists_tasktypes_id"  => $ID,
                                 "is_template"                    => 0,
                                 "is_deleted"                     => 0,
                                 "is_archived"                    => isset($_SESSION["archive"][Session::getLoginUserID()]) ? json_decode($_SESSION["archive"][Session::getLoginUserID()]) : 0]
                                + $dbu->getEntitiesRestrictCriteria('glpi_plugin_tasklists_tasks', '', $_SESSION["glpiactiveentities"], true);
                  $countTasks = $dbu->countElementsInTable($dbu->getTableForItemType('PluginTasklistsTasks'),
                                                           $cond);

                  if (empty($name = DropdownTranslation::getTranslatedValue($datastate['id'], 'PluginTasklistsTaskState', 'name', $_SESSION['glpilanguage']))) {
                     $name = $datastate['name'];
                  }

                  $states[] = ['id'       => $datastate['id'],
                               'color'    => $datastate['color'],
                               'name'     => $name,
                               'rank'     => $ranking,
                               'count'    => $countTasks,
                               'folded'   => PluginTasklistsItem_Kanban::loadStateForItem(PluginTasklistsTaskType::getType(), $ID, $datastate['id']),
                               'finished' => $datastate['is_finished']];

                  $states_ranked = [];
                  foreach ($states as $key => $row) {
                     $states_ranked[$key] = $row['rank'];
                  }
                  array_multisort($states_ranked, SORT_ASC, $states);

                  $colors[$datastate['id']] = $datastate['color'];

                  $nb++;

               }
            }
         }
      }
      $nstates = [];
      $task    = new PluginTasklistsTask();
      foreach ($states as $state) {

         $tasks = [];
         $datas = $task->find(["plugin_tasklists_tasktypes_id" => $ID, "plugin_tasklists_taskstates_id" => $state["id"], 'is_deleted' => 0, 'is_template' => 0], ['priority DESC,name']);

         foreach ($datas as $data) {
            $array = isset($_SESSION["archive"][Session::getLoginUserID()]) ? json_decode($_SESSION["archive"][Session::getLoginUserID()]) : [0];
            if (!in_array($data["is_archived"], $array)) {
               continue;
            }
            $usersallowed = isset($_SESSION["usersKanban"][Session::getLoginUserID()]) ? json_decode($_SESSION["usersKanban"][Session::getLoginUserID()]) : [-1];
            if (!in_array(-1, $usersallowed) && !in_array($data['users_id'], $usersallowed)) {
               continue;
            }
            $user = new User();
            $link = "";
            if ($user->getFromDB($data['users_id'])) {
               $link = "<div class='kanban_user_picture_border_verysmall'>";
               $link .= "<a target='_blank' href='" . Toolbox::getItemTypeFormURL('User') . "?id=" . $data['users_id'] . "'><img title=\"" . $dbu->getUserName($data['users_id']) . "\" class='kanban_user_picture_verysmall'  src='" .
                        User::getThumbnailURLForPicture($user->fields['picture']) . "'></a>";
               $link .= "</div>";
            }
            $plugin_tasklists_taskstates_id = $data['plugin_tasklists_taskstates_id'];
            $finished                       = 0;
            $finished_style                 = 'style="display: inline;"';
            $stateT                         = new PluginTasklistsTaskState();
            if ($stateT->getFromDB($plugin_tasklists_taskstates_id)) {
               if ($stateT->getFinishedState()) {
                  $finished_style = 'style="display: none;"';
                  $finished       = 1;
               }
            }
            $task = new PluginTasklistsTask();
            if ($task->checkVisibility($data['id']) == true) {
               $duedate = '';
               if (!empty($data['due_date'])) {
                  $duedate = __('Due date', 'tasklists') . " " . Html::convDate($data['due_date']);
               }
               $actiontime = '';
               if ($data['actiontime'] != 0) {
                  $actiontime = Html::timestampToString($data['actiontime'], false, true);
               }
               $archived = $data['is_archived'];

               if (isset($data['users_id'])
                   && $data['users_id'] != Session::getLoginUserID()) {
                  $finished_style = 'style="display: none;"';
               }

               $right = 0;
               if (($data['users_id'] == Session::getLoginUserID() && Session::haveRight("plugin_tasklists", UPDATE))
                   || Session::haveRight("plugin_tasklists_see_all", 1)) {
                  $right = 1;
               }

               if ($data['users_id'] == 0) {
                  $right          = 1;
                  $finished_style = 'style="display: inline;"';
               }

               $entity      = new Entity();
               $entity_name = __('None');
               if ($entity->getFromDB($data['entities_id'])) {
                  $entity_name = $entity->fields['name'];
               }
               $client = (empty($data['client'])) ? $entity_name : $data['client'];

               $comment = Toolbox::unclean_cross_side_scripting_deep(html_entity_decode($data["comment"],
                                                                                        ENT_QUOTES,
                                                                                        "UTF-8"));

               $nbcomments = "";
               $nb         = 0;
               $where      = [
                  'plugin_tasklists_tasks_id' => $data['id'],
                  'language'                  => null
               ];
               $nb         = countElementsInTable(
                  'glpi_plugin_tasklists_tasks_comments',
                  $where
               );
               if ($nb > 0) {
                  $nbcomments = " (" . $nb . ") ";
               }
               $linkname = $data["name"];
               if ($_SESSION["glpiis_ids_visible"]
                   || empty($data["name"])) {
                  $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
               }

               $tasks[] = ['id'             => $data['id'],
                           'title'          => $linkname . $nbcomments,
                           'block'          => ($ID > 0 ? $ID : 0),
                           'link'           => Toolbox::getItemTypeFormURL("PluginTasklistsTask") . "?id=" . $data['id'],
                           'description'    => Html::resume_text(Html::clean($comment), 80),
                           'descriptionfull' => $comment,
                           'priority'       => CommonITILObject::getPriorityName($data['priority']),
                           'priority_id'    => $data['priority'],
                           'bgcolor'        => $_SESSION["glpipriority_" . $data['priority']],
                           'percent'        => $data['percent_done'],
                           'actiontime'     => $actiontime,
                           'duedate'        => $duedate,
                           'user'           => $link,
                           'client'         => $client,
                           'finished'       => $finished,
                           'archived'       => $archived,
                           'finished_style' => $finished_style,
                           'right'          => $right,
                           'users_id'       => $data['users_id'],
                           '_readonly'      => false
               ];

               if ($archived != 1) {
                  $users_array[] = $data['users_id'];
               }
            }
         }
         $state["items"] = $tasks;
         $nstates[]      = $state;
      }
      return $nstates;

   }

   /**
    * @param $plugin_tasklists_tasktypes_id
    *
    * @return array
    */
   static function findUsers($plugin_tasklists_tasktypes_id) {
      $dbu   = new DbUtils();
      $users = [];
      $task  = new PluginTasklistsTask();
      $tasks = $task->find(["plugin_tasklists_tasktypes_id" => $plugin_tasklists_tasktypes_id, "is_archived" => 0, "is_deleted" => 0]);
      foreach ($tasks as $t) {
         $users[$t["users_id"]] = $dbu->getUserName($t["users_id"]);
      }
      $users     = array_unique($users);
      $users[-1] = __("All");


      return $users;
   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return boolean
    **/
   static function canCreate() {
      if (static::$rightname) {
         return Session::haveRight(static::$rightname, 1);
      }
      return false;
   }
   static function canUpdate() {
      if (static::$rightname) {
         return Session::haveRight(static::$rightname, 1);
      }
      return false;
   }

   static function canDelete() {
      if (static::$rightname) {
         return Session::haveRight(static::$rightname, 1);
      }
      return false;
   }



}
