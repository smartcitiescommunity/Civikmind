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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMetademandsMetademandValidation
 */
class PluginMetademandsMetademandValidation extends CommonDBTM {


   static $rightname = 'plugin_metademands';

   const VALIDATE_WITHOUT_TASK   = 3; // meta validate without task
   const TASK_CREATION           = 2; // task_created
   const TICKET_CREATION         = 1; // tickets_created
   const TO_VALIDATE             = 0; // waiting
   const TO_VALIDATE_WITHOUTTASK = -1; // waiting without ticket

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    *
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {
      return __('Metademand validation', 'metademands');
   }

   /**
    * @return bool|int
    */
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return bool
    */
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * Display tab for each users
    *
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      //      if (!$withtemplate) {
      //         if ($item->getType() == 'PluginMetademandsMetademand') {
      //            if ($_SESSION['glpishow_count_on_tabs']) {
      //               $dbu = new DbUtils();
      //               return self::createTabEntry(self::getTypeName(),
      //                                           $dbu->countElementsInTable($this->getTable(),
      //                                                                      ["plugin_metademands_metademands_id" => $item->getID()]));
      //            }
      //            return self::getTypeName();
      //         }
      //      }
      return '';
   }

   /**
    * Display content for each users
    *
    * @static
    *
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $field = new self();

      //      if (in_array($item->getType(), self::getTypes(true))) {
      //         $field->showForm(0, ["item" => $item]);
      //      }
      return true;
   }

   /**
    * @param array $options
    *
    * @return array
    * @see CommonGLPI::defineTabs()
    */
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      //      $this->addStandardTab('PluginMetademandsFieldTranslation', $ong, $options);

      return $ong;
   }


   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    * @throws \GlpitestSQLError
    */
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      if (!$this->canview()) {
         return false;
      }
      if (!$this->cancreate()) {
         return false;
      }
      Html::requireJs('tinymce');

      $metademand = new PluginMetademandsMetademand();

      if ($ID > 0) {
         $this->check($ID, READ);
         $metademand->getFromDB($this->fields['plugin_metademands_metademands_id']);
      } else {
         // Create item
         $item    = $options['item'];
         $canedit = $metademand->can($item->fields['id'], UPDATE);
         $this->getEmpty();
         $this->fields["plugin_metademands_metademands_id"] = $item->fields['id'];
         $this->fields['color']                             = '#000';
      }


      if ($ID > 0) {
         $this->showFormHeader(['colspan' => 2]);
      } else {
         echo "<div class='center first-bloc'>";
         echo "<form name='field_form' method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='6'>" . __('Add a field', 'metademands') . "</th>";
         echo "</tr>";
      }


      if ($ID > 0) {
         $this->showFormButtons(['colspan' => 2]);

      } else {
         if ($canedit) {
            echo "<tr class='tab_bg_1'>";
            echo "<td class='tab_bg_2 center' colspan='6'>";
            echo "<input type='hidden' class='submit' name='plugin_metademands_metademands_id' value='" . $item->fields['id'] . "'>";
            echo "<input type='submit' class='submit' name='add' value='" . _sx('button', 'Add') . "'>";
            echo "</td>";
            echo "</tr>";
         }

         echo "</table>";
         Html::closeForm();
         echo "</div>";

      }
      return true;
   }

   function validateMeta($params) {
      $ticket_id = $params["tickets_id"];
      $inputVal  = [];

      $this->getFromDBByCrit(['tickets_id' => $ticket_id]);
      $meta_tasks = json_decode($this->fields["tickets_to_create"], true);
      if (is_array($meta_tasks)) {
         foreach ($meta_tasks as $key => $val) {
            $meta_tasks[$key]['tickettasks_name']   = urldecode($val['tickettasks_name']);
            $meta_tasks[$key]['tasks_completename'] = urldecode($val['tasks_completename']);
            $meta_tasks[$key]['content']            = urldecode($val['content']);
         }
      }

      $ticket = new Ticket();
      $ticket->getFromDB($ticket_id);
      $ticket->fields["_users_id_requester"] = Session::getLoginUserID();
      $users                                 = $ticket->getUsers(CommonITILActor::REQUESTER);
      foreach ($users as $user) {
         $ticket->fields["_users_id_requester"] = $user['users_id'];
      }
      $meta = new PluginMetademandsMetademand();
      $meta->getFromDB($this->getField("plugin_metademands_metademands_id"));
      if ($params["create_subticket"] == 1) {
         if (!$meta->createSonsTickets($ticket_id,
                                       $ticket->fields,
                                       $ticket_id, $meta_tasks, 1)) {
            $KO[] = 1;

         }
         $inputVal['validate'] = self::TICKET_CREATION;
      } else if ($params["create_subticket"] == 0) {
         if (is_array($meta_tasks)) {
            foreach ($meta_tasks as $meta_task) {
               if (PluginMetademandsTicket_Field::checkTicketCreation($meta_task['tasks_id'], $ticket_id)) {
                  $ticket_task             = new TicketTask();
                  $input                   = [];
                  $input['content']        = Toolbox::addslashes_deep($meta_task['tickettasks_name']) . " " . Toolbox::addslashes_deep($meta_task['content']);
                  $input['tickets_id']     = $ticket_id;
                  $input['groups_id_tech'] = $params["group_to_assign"];
                  $ticket_task->add($input);
               }
            }
         }
         $input                              = [];
         $input['id']                        = $ticket_id;
         $input['_itil_assign']["_type"]     = "group";
         $input['_itil_assign']["groups_id"] = $params["group_to_assign"];

         $ticket->update($input);
         $inputVal['validate'] = self::TASK_CREATION;
      } else {
         $input                              = [];
         $input['id']                        = $ticket_id;
         $input['_itil_assign']["_type"]     = "group";
         $input['_itil_assign']["groups_id"] = $params["group_to_assign"];

         $ticket->update($input);
         $inputVal['validate'] = self::VALIDATE_WITHOUT_TASK;
      }

      $inputVal['id']       = $this->getID();
      $inputVal['users_id'] = Session::getLoginUserID();
      $inputVal['date']     = $_SESSION["glpi_currenttime"];;
      $this->update($inputVal);
   }

   function viewValidation($params) {
      global $CFG_GLPI;

      $ticket_id = $params["tickets_id"];
      $this->getFromDBByCrit(['tickets_id' => $ticket_id]);
      $ticket = new Ticket();
      $ticket->getFromDB($ticket_id);
      echo "<form name='form_raz' id='form_raz' method='post' action='" . $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/timeline.php" . "' >";
      echo "<input type='hidden' name='action' id='action_validationMeta' value='validationMeta' />";
      echo "<input type='hidden' name='tickets_id' id='action_validationMeta' value='$ticket_id' />";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __("Metademand validation", 'metademands');
      echo "</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1 center'>";
      if ($this->fields["users_id"] == 0
          && $this->fields["validate"] == self::TO_VALIDATE) {
         echo "<td>" . __('Create sub-tickets', 'metademands') . " &nbsp;";
         echo "<input  type='radio' name='create_subticket' id='create_subticket' value='1' checked>";
         echo "</td>";
         echo "<td>" . __('Create tasks', 'metademands') . "&nbsp;";
         echo "<input  type='radio' name='create_subticket' id='create_subticket2' value='0'>";
         echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_1 center' id='to_update_group'>";

         Ajax::updateItemOnEvent('create_subticket',
                                 'to_update_group',
                                 $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/displayGroupField.php",
                                 ["create_subticket" => '__VALUE__',
                                  'tickets_id' => $ticket_id]);
         Ajax::updateItemOnEvent('create_subticket2',
                                 'to_update_group',
                                 $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/displayGroupField.php",
                                 ["create_subticket" => '__VALUE__',
                                  'tickets_id' => $ticket_id]);

      } else if ($this->fields["users_id"] == 0
                 && $this->fields["validate"] == self::TO_VALIDATE_WITHOUTTASK) {
         echo "<td colspan='2'>" . __('Attribute ticket to ', 'metademands') . " &nbsp;";
         echo Html::hidden("create_subticket", ["value" => 2]);
         $group = 0;
         foreach ($ticket->getGroups(CommonITILActor::ASSIGN) as $d) {
            $group = $d['groups_id'];
         }
         Group::dropdown(['condition' => ['is_assign' => 1],
                          'name' => 'group_to_assign',
                          'value' => $group]);
         echo "</td>";

      } else if ($this->fields["users_id"] != 0
                 && $this->fields["validate"] == self::TASK_CREATION) {
         echo "<td colspan='2'>" . __('Tasks are created', 'metademands') . "</td>";
         //         echo "<td>" . __('Create sub-tickets', 'metademands') . "&nbsp;";
         //         echo "<input class='custom-control-input' type='radio' name='create_subticket' id='create_subticket[" . 1 . "]' value='1' checked>";
         //         echo "</td>";
         //         echo "<td>" . __('Create tasks', 'metademands') . "&nbsp;";
         //         echo "<input class='custom-control-input' type='radio' name='create_subticket' id='create_subticket[" . 0 . "]' value='0' disabled>";
         //         echo "</td>";
      } else if ($this->fields["users_id"] != 0
                 && $this->fields["validate"] == self::VALIDATE_WITHOUT_TASK) {

      } else {
         echo "<td colspan='2'>" . __('Sub-tickets are created', 'metademands') . "</td>";

      }
      echo "</tr>";
      if ($this->fields["users_id"] != 0) {
         echo "<tr class='tab_bg_1 center'>";
         echo "<td colspan='4'>";
         echo sprintf(__('Validated by %s on %s', 'metademands'), User::getFriendlyNameById($this->fields["users_id"]), Html::convDateTime($this->fields["date"]));
         echo "</td>";
         echo "</tr>";
      }

      if ($this->fields["users_id"] == 0
      ) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2' class='center'>";
         echo "<input type='submit' class='submit' name='btnAddAll' id='btnAddAll' ";

         echo "value='" . __("Validate metademands", 'metademands') . "' />";
         echo "</td>";
         echo "</tr>";
      }
      //      foreach ($data['custom_values'] as $key => $label) {
      //         $field .= "<div class='custom-control custom-radio $inline'>";
      //
      //         $checked = "";
      //         if ($value != NULL && $value == $key) {
      //            $checked = $value == $key ? 'checked' : '';
      //         } elseif ($value == NULL && isset($defaults[$key]) && $on_basket == false) {
      //            $checked = ($defaults[$key] == 1) ? 'checked' : '';
      //         }
      //         $field .= "<input class='custom-control-input' type='radio' name='" . $namefield . "[" . $data['id'] . "]' id='" . $namefield . "[" . $data['id'] . "][" . $key . "]' value='$key' $checked>";
      //         $nbr++;
      //         $field .= "&nbsp;<label class='custom-control-label' for='" . $namefield . "[" . $data['id'] . "][" . $key . "]'>$label</label>";
      //         if (isset($data['comment_values'][$key]) && !empty($data['comment_values'][$key])) {
      //            $field .= "&nbsp;<span style='vertical-align: bottom;'>";
      //            $field .= Html::showToolTip($data['comment_values'][$key],
      //                                        ['awesome-class' => 'fa-info-circle',
      //                                         'display'       => false]);
      //            $field .= "</span>";
      //         }
      //         $field .= "</div>";
      //      }
      Html::closeForm();
   }

   /**
    * @param $field
    * @param $name (default '')
    * @param $values (default '')
    * @param $options   array
    *
    * @return string
    * *@since version 0.84
    *
    */
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'validate' :
            $options['name']  = $name;
            $options['value'] = $values[$field];
            //            $options['withmajor'] = 1;
            return self::dropdownStatus($options);
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
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
         case 'validate':
            return self::getStatusName($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * @param array $options
    *
    * @return int|string
    */
   static function dropdownStatus(array $options = []) {

      $p['name']                = 'validate';
      $p['value']               = 0;
      $p['showtype']            = 'normal';
      $p['display']             = true;
      $p['display_emptychoice'] = false;
      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $values = [];
      //      $values[0]               = static::getStatusName(0);
      $values[self::TO_VALIDATE_WITHOUTTASK] = static::getStatusName(self::TO_VALIDATE_WITHOUTTASK);
      $values[self::TO_VALIDATE]             = static::getStatusName(self::TO_VALIDATE);
      $values[self::TICKET_CREATION]         = static::getStatusName(self::TICKET_CREATION);
      $values[self::TASK_CREATION]           = static::getStatusName(self::TASK_CREATION);
      $values[self::VALIDATE_WITHOUT_TASK]   = static::getStatusName(self::VALIDATE_WITHOUT_TASK);

      return Dropdown::showFromArray($p['name'], $values, $p);
   }


   /**
    * @param $value
    *
    * @return string
    */
   static function getStatusName($value) {

      switch ($value) {

         case self::TO_VALIDATE :
            return __('To validate', 'metademands');
         case self::TICKET_CREATION :
            return __('Child tickets created', 'metademands');
         case self::TASK_CREATION :
            return __('Tasks created', 'metademands');
         case self::TO_VALIDATE_WITHOUTTASK :
            return __('To validate without child', 'metademands');
         case self::VALIDATE_WITHOUT_TASK :
            return __('Validate without child', 'metademands');
         default :
            // Return $value if not define
            return Dropdown::EMPTY_VALUE;
      }
   }
}
