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
 * Class PluginTasklistsTask
 */
class PluginTasklistsTask extends CommonDBTM {
   use Glpi\Features\Clonable;

   public    $dohistory  = true;
   static    $rightname  = 'plugin_tasklists';
   protected $usenotepad = true;
   static    $types      = [];

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {

      return _n('Task', 'Tasks', $nb);
   }


   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType()
      ];

      $tab[] = [
         'id'       => '2',
         'table'    => 'glpi_plugin_tasklists_tasktypes',
         'field'    => 'name',
         'name'     => _n('Context', 'Contexts', 1, 'tasklists'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'        => '3',
         'table'     => 'glpi_users',
         'field'     => 'name',
         'linkfield' => 'users_id',
         'name'      => __('User'),
         'datatype'  => 'dropdown'
      ];

      $tab[] = [
         'id'            => '4',
         'table'         => $this->getTable(),
         'field'         => 'actiontime',
         'name'          => __('Planned duration'),
         'datatype'      => 'timestamp',
         'massiveaction' => false
      ];

      $tab[] = [
         'id'       => '5',
         'table'    => $this->getTable(),
         'field'    => 'percent_done',
         'name'     => __('Percent done'),
         'datatype' => 'number',
         'unit'     => '%',
         'min'      => 0,
         'max'      => 100,
         'step'     => 5
      ];

      $tab[] = [
         'id'       => '6',
         'table'    => $this->getTable(),
         'field'    => 'due_date',
         'name'     => __('Due date', 'tasklists'),
         'datatype' => 'date'
      ];

      $tab[] = [
         'id'       => '7',
         'table'    => $this->getTable(),
         'field'    => 'comment',
         'name'     => __('Description'),
         'datatype' => 'text'
      ];

      $tab[] = [
         'id'         => '8',
         'table'      => $this->getTable(),
         'field'      => 'priority',
         'name'       => __('Priority'),
         'searchtype' => 'equals',
         'datatype'   => 'specific'
      ];

      $tab[] = [
         'id'            => '9',
         'table'         => $this->getTable(),
         'field'         => 'visibility',
         'name'          => __('Visibility'),
         'searchtype'    => 'equals',
         'datatype'      => 'specific',
         'massiveaction' => false
      ];

      $tab[] = [
         'id'        => '10',
         'table'     => 'glpi_groups',
         'field'     => 'name',
         'linkfield' => 'groups_id',
         'name'      => __('Group'),
         'condition' => '`is_usergroup`',
         'datatype'  => 'dropdown'
      ];

      $tab[] = [
         'id'         => '11',
         'table'      => $this->getTable(),
         'field'      => 'plugin_tasklists_taskstates_id',
         'name'       => __('Status'),
         'searchtype' => ['equals', 'notequals'],
         'datatype'   => 'specific'
      ];

      $tab[] = [
         'id'            => '12',
         'table'         => $this->getTable(),
         'field'         => 'date_mod',
         'massiveaction' => false,
         'name'          => __('Last update'),
         'datatype'      => 'datetime'
      ];

      $tab[] = [
         'id'       => '13',
         'table'    => $this->getTable(),
         'field'    => 'is_archived',
         'name'     => __('Archived', 'tasklists'),
         'datatype' => 'bool'
      ];

      $tab[] = [
         'id'       => '14',
         'table'    => $this->getTable(),
         'field'    => 'client',
         'name'     => __('Other client', 'tasklists'),
         'datatype' => 'text'
      ];

      $tab[] = [
         'id'            => '121',
         'table'         => $this->getTable(),
         'field'         => 'date_creation',
         'name'          => __('Creation date'),
         'datatype'      => 'datetime',
         'massiveaction' => false
      ];

      $tab[] = [
         'id'       => '18',
         'table'    => $this->getTable(),
         'field'    => 'is_recursive',
         'name'     => __('Child entities'),
         'datatype' => 'bool'
      ];

      $tab[] = [
         'id'       => '30',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'number'
      ];

      $tab[] = [
         'id'       => '80',
         'table'    => 'glpi_entities',
         'field'    => 'completename',
         'name'     => __('Entity'),
         'datatype' => 'dropdown'
      ];
      $tab[] = [
         'id'        => '81',
         'table'     => 'glpi_users',
         'field'     => 'name',
         'linkfield' => 'users_id_requester',
         'name'      => _n('Requester', 'Requesters', 1),
         'datatype'  => 'dropdown'
      ];
      return $tab;
   }

   /**
    * @param array $options
    *
    * @return array
    */
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('Document_Item', $ong, $options);
      if (!isset($options['withtemplate']) || empty($options['withtemplate'])) {
         $this->addStandardTab('PluginTasklistsTask_Comment', $ong, $options);
         $this->addStandardTab('PluginTasklistsTicket', $ong, $options);
      }
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   /**
    *
    */
   function post_getEmpty() {

      $this->fields['priority']     = 3;
      $this->fields['percent_done'] = 0;
      $this->fields['visibility']   = 2;
   }


   public function getCloneRelations() :array {
      return [
         Document_Item::class,
         Notepad::class
      ];
   }

   /**
    * @see CommonDBTM::cleanDBonPurge()
    *
    * @since 0.83.1
    **/
   function cleanDBonPurge() {

      /// PluginTasklistsTask_Comment does not extends CommonDBConnexity
      $kbic = new PluginTasklistsTask_Comment();
      $kbic->deleteByCriteria(['plugin_tasklists_tasks_id' => $this->fields['id']]);
   }

   /**
    * @param datas $input
    *
    * @return datas
    */
   function prepareInputForAdd($input) {

      if (isset($input['due_date']) && empty($input['due_date'])) {
         $input['due_date'] = 'NULL';
      }
      if (isset($input["id"]) && ($input["id"] > 0)) {
         $input["_oldID"] = $input["id"];
      }
      unset($input['id']);

      return $input;
   }

   function post_addItem() {
      global $CFG_GLPI;

      if (isset($this->input['withtemplate'])
          && $this->input["withtemplate"] != 1
      ) {
         if ($CFG_GLPI["notifications_mailing"]) {
            NotificationEvent::raiseEvent("newtask", $this);
         }
      }
   }

   /**
    * @param datas $input
    *
    * @return datas
    */
   function prepareInputForUpdate($input) {

      if (isset($input['due_date']) && empty($input['due_date'])) {
         $input['due_date'] = 'NULL';
      }
      if (isset($input['plugin_tasklists_taskstates_id'])) {
         $state = new PluginTasklistsTaskState();
         if ($state->getFromDB($input['plugin_tasklists_taskstates_id'])) {
            if ($state->getFinishedState()) {
               $input['percent_done'] = 100;
            }
         }
      }
      if (isset($input['is_archived'])
          && $input['is_archived'] == 1) {
         $state = new PluginTasklistsTaskState();
         if ($state->getFromDB($this->fields['plugin_tasklists_taskstates_id'])) {
            if (!$state->getFinishedState()) {
               Session::addMessageAfterRedirect(__('You cannot archive a task with this state', 'tasklists'), false, ERROR);
               return false;
            }
         }
      }
      return $input;
   }

   /**
    * Actions done after the UPDATE of the item in the database
    *
    * @param int $history store changes history ? (default 1)
    *
    * @return void
    */
   function post_updateItem($history = 1) {
      global $CFG_GLPI;

      if (count($this->updates)
          && isset($this->input["withtemplate"])
          && $this->input["withtemplate"] != 1
      ) {

         if ($CFG_GLPI["notifications_mailing"]) {
            NotificationEvent::raiseEvent("updatetask", $this);
         }
      }
   }


   /**
    * Actions done before the DELETE of the item in the database /
    * Maybe used to add another check for deletion
    *
    * @return bool : true if item need to be deleted else false
    **/
   function pre_deleteItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["notifications_mailing"]
          && $this->fields["is_template"] != 1
          && isset($this->input['_delete'])
      ) {

         NotificationEvent::raiseEvent("deletetask", $this);
      }

      return true;
   }


   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      Html::initEditorSystem('comment');

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo Html::hidden('id',['value'=>$ID]);
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name", ['option' => "size='40'"]);
      if (isset($options['from_edit_ajax'])
          && $options['from_edit_ajax']) {
         echo Html::hidden('from_edit_ajax', ['value' => $options['from_edit_ajax']]);
      }
      if (isset($options['withtemplate']) && empty($options['withtemplate'])) {
         $options['withtemplate'] = 0;
      }
      echo Html::hidden('withtemplate', ['value' => $options['withtemplate']]);
      echo "</td>";

      $plugin_tasklists_tasktypes_id = $this->fields["plugin_tasklists_tasktypes_id"];
      if (isset($options['plugin_tasklists_tasktypes_id'])
          && $options['plugin_tasklists_tasktypes_id']) {
         $plugin_tasklists_tasktypes_id = $options['plugin_tasklists_tasktypes_id'];
      }
      echo "<td>" . _n('Context', 'Contexts', 1, 'tasklists') . "</td><td>";
      $types     = PluginTasklistsTypeVisibility::seeAllowedTypes();
      $rand_type = Dropdown::show('PluginTasklistsTaskType', ['name'      => "plugin_tasklists_tasktypes_id",
                                                              'value'     => $plugin_tasklists_tasktypes_id,
                                                              'entity'    => $this->fields["entities_id"],
                                                              'condition' => ['id' => $types],
                                                              'on_change' => "plugin_tasklists_load_states();",]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Priority') . "</td>";
      echo "<td>";
      $priority = $this->fields['priority'];
      if (isset($options['priority'])
          && $options['priority']) {
         $priority = $options['priority'];
      }
      CommonITILObject::dropdownPriority(['value'     => $priority,
                                          'withmajor' => 1]);
      echo "</td>";

      echo "<td>" . __('Planned duration') . "</td>";
      echo "<td>";
      Dropdown::showTimeStamp("actiontime", ['min'   => HOUR_TIMESTAMP * 2,
                                             'max'   => MONTH_TIMESTAMP * 2,
                                             'step'  => HOUR_TIMESTAMP * 2,
                                             'value' => $this->fields["actiontime"]]);
      echo "</td>";

      echo "</tr>";

      if (isset($_SESSION["glpiactiveentities"])
          && count($_SESSION["glpiactiveentities"]) > 1
          && ($ID == 0 || (isset($options['withtemplate']) && ($options['withtemplate'] == 2)))) {

         echo "<tr class='tab_bg_1'>";

         echo "<td>" . __('Existing client', 'tasklists') . "</td>";
         echo "<td>";
         $entities_id = $this->fields['entities_id'];
         if (isset($options['entities_id'])
             && $options['entities_id']) {
            $entities_id = $options['entities_id'];
         }
         $rand_entity = Dropdown::show('Entity', ['name'         => "entities_id",
                                                  'value'        => $entities_id,
                                                  'entity'       => $_SESSION["glpiactiveentities"],
                                                  'is_recursive' => true,
                                                  'on_change'    => "plugin_tasklists_load_entities();",]);
         echo "</td>";

         echo "<td colspan='2' id='plugin_tasklists_entity'>";
         $JS     = "function plugin_tasklists_load_entities(){";
         $params = ['entities_id' => '__VALUE__',
                    'entity'      => $this->fields["entities_id"]];
         $JS     .= Ajax::updateItemJsCode("plugin_tasklists_entity",
                                           $CFG_GLPI["root_doc"] . "/plugins/tasklists/ajax/inputEntity.php",
                                           $params, 'dropdown_entities_id' . $rand_entity, false);
         $JS     .= "}";
         echo Html::scriptBlock($JS);
         echo "</td>";
         echo "</tr>";
      }

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Other client', 'tasklists') . "</td>";
      echo "<td>";
      $client = $this->fields['client'];
      if (isset($options['client'])
          && $options['client']) {
         $client = $options['client'];
      }
      Html::autocompletionTextField($this, "client", ['option' => "size='40'",
                                                      'value'  => $client]);
      echo "</td>";
      echo "<td>" . __("Due date", "tasklists") . "</td>";
      echo "<td>";
      Html::showDateField("due_date", ['value' => $this->fields["due_date"]]);
      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>" . _n('Requester', 'Requesters', 1) . "</td><td>";
      $users_id_requester = $this->fields['users_id_requester'];
      if (isset($options['users_id_requester'])
          && $options['users_id_requester']) {
         $users_id_requester = $options['users_id_requester'];
      }

      User::dropdown(['name'   => "users_id_requester",
                      'value'  => $users_id_requester,
                      'entity' => $this->fields["entities_id"],
                      'right'  => 'all']);
      echo "</td>";

      echo "<td></td>";
      echo "<td>";
      echo "</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Technician') . "</td><td>";
      $users_id = $this->fields['users_id'];
      if (isset($options['users_id'])
          && $options['users_id']) {
         $users_id = $options['users_id'];
      }

      User::dropdown(['name'   => "users_id",
                      'value'  => $users_id,
                      'entity' => $this->fields["entities_id"],
                      'right'  => 'all']);
      echo "</td>";

      echo "<td>" . __('Percent done') . "</td>";
      echo "<td>";
      Dropdown::showNumber("percent_done", ['value' => $this->fields['percent_done'],
                                            'min'   => 0,
                                            'max'   => 100,
                                            'step'  => 10,
                                            'unit'  => '%']);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Group') . "</td>";
      echo "<td>";
      $groups_id = $this->fields['groups_id'];
      if (isset($options['groups_id'])
          && $options['groups_id']) {
         $groups_id = $options['groups_id'];
      }
      Dropdown::show('Group', ['name'      => "groups_id",
                               'value'     => $groups_id,
                               'entity'    => $this->fields["entities_id"],
                               'condition' => ['is_usergroup' => 1]
      ]);
      echo "</td>";

      echo "<td>" . __('Status') . "</td><td id='plugin_tasklists_state'>";

      $plugin_tasklists_taskstates_id = $this->fields["plugin_tasklists_taskstates_id"];
      if (isset($options['plugin_tasklists_taskstates_id'])
          && $options['plugin_tasklists_taskstates_id']) {
         $plugin_tasklists_taskstates_id = $options['plugin_tasklists_taskstates_id'];
      }

      if ($plugin_tasklists_tasktypes_id) {
         self::displayState($plugin_tasklists_tasktypes_id, $plugin_tasklists_taskstates_id);
      }
      $JS     = "function plugin_tasklists_load_states(){";
      $params = ['plugin_tasklists_tasktypes_id' => '__VALUE__',
                 'entity'                        => $this->fields["entities_id"]];
      $JS     .= Ajax::updateItemJsCode("plugin_tasklists_state",
                                        $CFG_GLPI["root_doc"] . "/plugins/tasklists/ajax/dropdownState.php",
                                        $params, 'dropdown_plugin_tasklists_tasktypes_id' . $rand_type, false);
      $JS     .= "}";
      echo Html::scriptBlock($JS);

      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>";
      echo __('Description') . "</td>";
      echo "<td colspan = '3' class='center'>";
      $rand_text  = mt_rand();
      $content_id = "comment$rand_text";
      $cols       = 100;
      $rows       = 15;
      Html::textarea(['name'            => 'comment',
                      'value'           => $this->fields["comment"],
                      'rand'            => $rand_text,
                      'editor_id'       => $content_id,
                      'enable_richtext' => true,
                      'cols'            => $cols,
                      'rows'            => $rows]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Visibility') . "</td>";
      echo "<td>";
      $visibility = $this->fields['visibility'];
      if (isset($options['visibility'])
          && $options['visibility']) {
         $visibility = $options['visibility'];
      }
      self::dropdownVisibility(['value' => $visibility]);
      echo "</td>";

      echo "<td>" . __('Archived', 'tasklists') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("is_archived", $this->fields["is_archived"]);
      echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }


   /**
    * States by type dropdown list
    *
    * @param     $plugin_tasklists_tasktypes_id
    * @param int $plugin_tasklists_taskstates_id
    */
   static function displayState($plugin_tasklists_tasktypes_id, $plugin_tasklists_taskstates_id = 0) {


      $states[]      = ['id'   => 0,
                        'name' => __('Backlog', 'tasklists'),
                        'rank' => 0];
      $ranked        = [];
      $states_ranked = [];
      $dbu           = new DbUtils();
      $datastates    = $dbu->getAllDataFromTable($dbu->getTableForItemType('PluginTasklistsTaskState'));
      if (!empty($datastates)) {
         foreach ($datastates as $datastate) {
            $tasktypes = json_decode($datastate['tasktypes']);
            if (is_array($tasktypes)) {
               if (in_array($plugin_tasklists_tasktypes_id, $tasktypes)) {

                  $condition = ['plugin_tasklists_taskstates_id' => $datastate['id'],
                                'plugin_tasklists_tasktypes_id'  => $plugin_tasklists_tasktypes_id];
                  $order     = new PluginTasklistsStateOrder();
                  $ranks     = $order->find($condition);
                  $ranking   = 0;
                  if (count($ranks) > 0) {
                     foreach ($ranks as $rank) {
                        $ranking = $rank['ranking'];
                     }
                  }
                  //                  $states[$datastate['id']] = $datastate['name'];
                  if (empty($name = DropdownTranslation::getTranslatedValue($datastate['id'], 'PluginTasklistsTaskState', 'name', $_SESSION['glpilanguage']))) {
                     $name = $datastate['name'];
                  }
                  $states[] = ['id'   => $datastate['id'],
                               'name' => $name,
                               'rank' => $ranking];


                  foreach ($states as $key => $row) {
                     $ranked[$key] = $row['rank'];
                  }
                  array_multisort($ranked, SORT_ASC, $states);
               }
            }
         }
      }
      foreach ($states as $k => $v) {
         $states_ranked[$v['id']] = $v['name'];
      }
      $rand = mt_rand();
      Dropdown::showFromArray('plugin_tasklists_taskstates_id', $states_ranked, ['rand'    => $rand,
                                                                                 'value'   => $plugin_tasklists_taskstates_id,
                                                                                 'display' => true]);

   }


   /**
    * Closed States for a task
    *
    * @param     $plugin_tasklists_tasks_id
    */
   static function getClosedStateForTask($plugin_tasklists_tasks_id) {

      $task = new PluginTasklistsTask();
      if ($task->getFromDB($plugin_tasklists_tasks_id)) {
         $state      = $task->fields["plugin_tasklists_taskstates_id"];
         $dbu        = new DbUtils();
         $condition  = ["is_finished" => 1];
         $datastates = $dbu->getAllDataFromTable($dbu->getTableForItemType('PluginTasklistsTaskState'), $condition);
         if (!empty($datastates)) {
            foreach ($datastates as $datastate) {
               $tasktypes = json_decode($datastate['tasktypes']);
               if (is_array($tasktypes)) {
                  if (in_array($task->fields["plugin_tasklists_tasktypes_id"], $tasktypes)) {
                     $state = $datastate['id'];
                  }
               }
            }
         }
         return $state;
      }
   }

   /**
    * @param $value
    *
    * @return string
    */
   static function getStateName($value) {

      switch ($value) {

         case 0 :
            return __('Backlog', 'tasklists');

         default :
            // Return $value if not define
            return Dropdown::getDropdownName("glpi_plugin_tasklists_taskstates", $value);

      }
   }

   /**
    * Make a select box for link tasklists
    *
    * Parameters which could be used in options array :
    *    - name : string / name of the select (default is documents_id)
    *    - entity : integer or array / restrict to a defined entity or array of entities
    *                   (default -1 : no restriction)
    *    - used : array / Already used items ID: not to display in dropdown (default empty)
    *
    * @param $options array of possible options
    *
    * @return nothing (print out an HTML select box)
    *
    * @throws \GlpitestSQLError
    */
   static function dropdownTasklists($options = []) {

      global $DB, $CFG_GLPI;

      $p['name']    = 'plugin_tasklists_tasklists_id';
      $p['entity']  = '';
      $p['used']    = [];
      $p['display'] = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $rand  = mt_rand();
      $dbu   = new DbUtils();
      $where = " WHERE `glpi_plugin_tasklists_tasklists`.`is_deleted` = '0'  AND `glpi_plugin_tasklists_tasks`.`is_template` = 0";
      $where .= $dbu->getEntitiesRestrictRequest("AND", 'glpi_plugin_tasklists_tasklists', '', $p['entity'], true);

      if (count($p['used'])) {
         $where .= " AND `id` NOT IN (0, " . implode(",", $p['used']) . ")";
      }

      $query  = "SELECT *
        FROM `glpi_plugin_tasklists_tasktypes`
        WHERE `id` IN (SELECT DISTINCT `plugin_tasklists_tasktypes_id`
                       FROM `glpi_plugin_tasklists_tasks`
                       $where)
        ORDER BY `name`";
      $result = $DB->query($query);

      $values = [0 => Dropdown::EMPTY_VALUE];

      while ($data = $DB->fetchAssoc($result)) {
         $values[$data['id']] = $data['name'];
      }

      $out      = Dropdown::showFromArray('_tasktype', $values, ['width'   => '30%',
                                                                 'rand'    => $rand,
                                                                 'display' => false]);
      $field_id = Html::cleanId("dropdown__tasktype$rand");

      $params = ['tasktypes' => '__VALUE__',
                 'entity'    => $p['entity'],
                 'rand'      => $rand,
                 'myname'    => $p['name'],
                 'used'      => $p['used']
      ];

      $out .= Ajax::updateItemOnSelectEvent($field_id, "show_" . $p['name'] . $rand, $CFG_GLPI["root_doc"] . "/plugins/tasklists/ajax/dropdownTypeTasks.php", $params, false);

      $out .= "<span id='show_" . $p['name'] . "$rand'>";
      $out .= "</span>\n";

      $params['tasktype'] = 0;
      $out                .= Ajax::updateItem("show_" . $p['name'] . $rand, $CFG_GLPI["root_doc"] . "/plugins/tasklists/ajax/dropdownTypeTasks.php", $params, false);
      if ($p['display']) {
         echo $out;
         return $rand;
      }
      return $out;
   }

   //Massive action

   /**
    * @param null $checkitem
    *
    * @return array
    */
   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         if ($isadmin) {

            if (Session::haveRight('transfer', READ) && Session::isMultiEntitiesMode()
            ) {
               $actions['PluginTasklistsTask' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __('Transfer');
            }
         }
      }
      return $actions;
   }

   /**
    * @param MassiveAction $ma
    *
    * @return bool|false
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    *
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @param MassiveAction $ma
    * @param CommonDBTM    $item
    * @param array         $ids
    *
    * @return nothing|void
    * @throws \GlpitestSQLError
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    *
    * @since version 0.85
    *
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {

      switch ($ma->getAction()) {
         case "transfer" :
            $input = $ma->getInput();
            if ($item->getType() == 'PluginTasklistsTask') {
               foreach ($ids as $key) {
                  $item->getFromDB($key);
                  $type = PluginTasklistsTaskType::transfer($item->fields["plugin_tasklists_tasktypes_id"], $input['entities_id']);
                  if ($type > 0) {
                     $values["id"]                            = $key;
                     $values["plugin_tasklists_tasktypes_id"] = $type;
                     $item->update($values);
                  }
                  unset($values);
                  $values["id"]          = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;

      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @param $type string class name
    * *@since version 1.3.0
    *
    */
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }

   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * display a value according to a field
    *
    * @param $field     String         name of the field
    * @param $values    String / Array with the value to display
    * @param $options   Array          of option
    *
    * @return a string
    **@since version 0.83
    *
    */
   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'priority':
            return CommonITILObject::getPriorityName($values[$field]);
         case 'visibility':
            return self::getVisibilityName($values[$field]);
         case 'plugin_tasklists_taskstates_id':
            return self::getStateName($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * @param $field
    * @param $name (default '')
    * @param $values (default '')
    * @param $options   array
    *
    * @return string
    **@since version 0.84
    *
    */
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'priority':
            $options['name']      = $name;
            $options['value']     = $values[$field];
            $options['withmajor'] = 1;
            return CommonITILObject::dropdownPriority($options);

         case 'visibility':
            $options['name']  = $name;
            $options['value'] = $values[$field];
            return self::dropdownVisibility($options);

         case 'plugin_tasklists_taskstates_id':
            return Dropdown::show('PluginTasklistsTaskState', ['name'       => $name,
                                                               'value'      => $values[$field],
                                                               'emptylabel' => __('Backlog', 'tasklists'),
                                                               'display'    => false,
                                                               'width'      => '200px'
            ]);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   /*
    * @since  version 0.84 new proto
    *
    * @param $options array of options
    *       - name     : select name (default is urgency)
    *       - value    : default value (default 0)
    *       - showtype : list proposed : normal, search (default normal)
    *       - display  : boolean if false get string
    *
    * @return string id of the select
   **/
   /**
    * @param array $options
    *
    * @return int|string
    */
   static function dropdownVisibility(array $options = []) {

      $p['name']      = 'visibility';
      $p['value']     = 0;
      $p['showtype']  = 'normal';
      $p['display']   = true;
      $p['withmajor'] = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $values = [];

      $values[1] = static::getVisibilityName(1);
      $values[2] = static::getVisibilityName(2);
      $values[3] = static::getVisibilityName(3);

      return Dropdown::showFromArray($p['name'], $values, $p);

   }

   /**
    * Get ITIL object priority Name
    *
    * @param $value priority ID
    *
    * @return priority|string
    */
   static function getVisibilityName($value) {

      switch ($value) {

         case 1 :
            return _x('visibility', 'This user', 'tasklists');

         case 2 :
            return _x('visibility', 'This user and this group', 'tasklists');

         case 3 :
            return _x('visibility', 'All', 'tasklists');

         default :
            // Return $value if not define
            return $value;

      }
   }

   /**
    * @param $id
    *
    * @return bool
    */
   function checkVisibility($id) {

      if (Session::haveRight("plugin_tasklists_see_all", 1)) {
         return true;
      }
      if ($this->getFromDB(($id))) {
         $groupusers = Group_User::getGroupUsers($this->fields['groups_id']);
         $groups     = [];
         foreach ($groupusers as $groupuser) {
            $groups[] = $groupuser["id"];
         }
         if (($this->fields['visibility'] == 1 && ($this->fields['users_id'] == Session::getLoginUserID() || $this->fields['users_id_requester'] == Session::getLoginUserID()))
             || ($this->fields['visibility'] == 2 && ($this->fields['users_id'] == Session::getLoginUserID() || $this->fields['users_id_requester'] == Session::getLoginUserID()
                                                      || in_array(Session::getLoginUserID(), $groups)))
             || ($this->fields['visibility'] == 3)) {
            return true;
         }
      }
      return false;
   }

   /**
    * @see Rule::getActions()
    * */
   function getActions() {

      $actions = [];

      $actions['tasklists']['name']          = __('Affect entity for create task', 'tasklists');
      $actions['tasklists']['type']          = 'dropdown';
      $actions['tasklists']['table']         = 'glpi_entities';
      $actions['tasklists']['force_actions'] = ['send'];

      return $actions;
   }

   /**
    * Execute the actions as defined in the rule
    *
    * @param $action
    * @param $output the fields to manipulate
    * @param $params parameters
    *
    * @return the $output array modified
    */
   function executeActions($action, $output, $params) {

      switch ($params['rule_itemtype']) {
         case 'RuleMailCollector':
            switch ($action->fields["field"]) {
               case "tasklists" :

                  if (isset($params['headers']['subject'])) {
                     $input['name'] = $params['headers']['subject'];
                  }
                  if (isset($params['ticket'])) {
                     $input['comment'] = addslashes(strip_tags($params['ticket']['content']));
                  }
                  if (isset($params['headers']['from'])) {
                     $input['users_id'] = User::getOrImportByEmail($params['headers']['from']);
                  }

                  if (isset($action->fields["value"])) {
                     $input['entities_id'] = $action->fields["value"];
                  }
                  $input['state'] = 1;

                  if (isset($input['name'])
                      && $input['name'] !== false
                      && isset($input['entities_id'])
                  ) {
                     $this->add($input);
                  }
                  $output['_refuse_email_no_response'] = true;
                  break;
            }
      }
      return $output;
   }

   /**
    * @param $options
    *
    * @return bool
    */
   function hasTemplate($options) {

      $templates = [];
      $dbu       = new DbUtils();
      $restrict  = ["is_template" => 1] +
                   ["is_deleted" => 0] +
                   ["is_archived" => 0] +
                   ["plugin_tasklists_tasktypes_id" => $options['plugin_tasklists_tasktypes_id']] +
                   //                  ["users_id" => Session::getLoginUserID()] +
                   $dbu->getEntitiesRestrictCriteria($this->getTable(), '', '', $this->maybeRecursive());

      $templates = $dbu->getAllDataFromTable($this->getTable(), $restrict);
      reset($templates);
      foreach ($templates as $template) {
         return $template['id'];
      }
      return false;
   }


   /**
    * @param       $target
    * @param int   $add
    * @param array $options
    */
   function listOfTemplates($target, $add = 0) {
      $dbu = new DbUtils();

      $restrict = ["is_template" => 1] +
                  $dbu->getEntitiesRestrictCriteria($this->getTable(), '', '', $this->maybeRecursive()) +
                  ["ORDER" => "name"];

      $templates = $dbu->getAllDataFromTable($this->getTable(), $restrict);

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      if ($add) {
         echo "<tr><th colspan='" . (2 + $colsup) . "'>" . __('Choose a template') . " - " . self::getTypeName(2) . "</th>";
      } else {
         echo "<tr><th colspan='" . (2 + $colsup) . "'>" . __('Templates') . " - " . self::getTypeName(2) . "</th>";
      }

      echo "</tr>";
      if ($add) {

         echo "<tr>";
         echo "<td colspan='" . (2 + $colsup) . "' class='center tab_bg_1'>";
         echo "<a href=\"$target?id=-1&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;" . __('Blank Template') . "&nbsp;&nbsp;&nbsp;</a></td>";
         echo "</tr>";
      }

      foreach ($templates as $template) {

         $templname = $template["template_name"];
         if ($_SESSION["glpiis_ids_visible"] || empty($template["template_name"])) {
            $templname .= "(" . $template["id"] . ")";
         }

         echo "<tr>";
         echo "<td class='center tab_bg_1'>";
         if (!$add) {
            echo "<a href=\"$target?id=" . $template["id"] . "&amp;withtemplate=1\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";

            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center tab_bg_2'>";
               echo Dropdown::getDropdownName("glpi_entities", $template['entities_id']);
               echo "</td>";
            }
            echo "<td class='center tab_bg_2'>";
            Html::showSimpleForm($target,
                                 'purge',
                                 _x('button', 'Delete permanently'),
                                 ['id' => $template["id"], 'withtemplate' => 1]);
            echo "</td>";

         } else {
            echo "<a href=\"$target?id=" . $template["id"] . "&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";

            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center tab_bg_2'>";
               echo Dropdown::getDropdownName("glpi_entities", $template['entities_id']);
               echo "</td>";
            }
         }
         echo "</tr>";
      }
      if (!$add) {
         echo "<tr>";
         echo "<td colspan='" . (2 + $colsup) . "' class='tab_bg_2 center'>";
         echo "<b><a href=\"$target?withtemplate=1\">" . __('Add a template...') . "</a></b>";
         echo "</td>";
         echo "</tr>";
      }
      echo "</table></div>";
   }

}
