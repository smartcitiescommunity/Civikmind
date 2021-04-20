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
 * Class PluginMetademandsTicketTask
 */
class PluginMetademandsTicketTask extends CommonDBTM {

   static $rightname = 'plugin_metademands';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    *
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {
      return __('Task creation', 'metademands');
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
    * @param       $metademands_id
    * @param       $canchangeorder
    * @param array $input
    *
    * @throws \GlpitestSQLError
    * @throws \GlpitestSQLError
    */
   static function showTicketTaskForm($metademands_id, $canchangeorder, $input = []) {
      global $CFG_GLPI;

      $metademands = new PluginMetademandsMetademand();
      $metademands->getFromDB($metademands_id);

      // Default values
      $values = ['itilcategories_id'                      => 0,
                 'type'                                   => Ticket::DEMAND_TYPE,
                 'parent_tasks_id'                        => 0,
                 'plugin_metademands_tasks_id'            => 0,
                 'content'                                => '',
                 'name'                                   => '',
                 'block_use'                                   => '',
                 'entities_id'                            => 0];

      // Init values
      foreach ($input as $key => $val) {
         $values[$key] = $val;
      }

//      $values['block_use'] = json_decode($values['block_use']);
      $ticket = new Ticket();

      // Restore saved value or override with page parameter
      if (isset($_SESSION["metademandsHelpdeskSaved"])) {
         foreach ($_SESSION["metademandsHelpdeskSaved"] as $name => $value) {
            $values[$name] = $value;
         }
         unset($_SESSION["metademandsHelpdeskSaved"]);
      }
      $values['block_use'] = json_decode($values['block_use']);
      if($values['block_use'] == null){
         $values['block_use'] = [];
      }
      // Clean text fields
      $values['name']    = stripslashes($values['name']);
      $values['content'] = Html::cleanPostForTextArea($values['content']);
      $values['type']    = $metademands->getField("type");

      // Get Template
      $tt = $ticket->getITILTemplateToUse(false, $values['type'], $values['itilcategories_id'], $values['entities_id']);

      // In percent
      $colsize1 = '13';
      $colsize3 = '87';

      echo "<div>";
      echo "<table class='tab_cadre_fixe' id='mainformtable'>";

      echo "<tr class='tab_bg_1'>";

      echo "<th>" . sprintf(__('%1$s'), __('Block to use','metademands')) . "</th>";
      echo "<td>";
      $metademands;
      $field = new PluginMetademandsField();
      $fields = $field->find(["plugin_metademands_metademands_id"=>$metademands_id]);
      $blocks = [];
      foreach($fields as $f){
         if(!isset($blocks[$f['rank']])){
            $blocks[intval($f['rank'])] = sprintf(__("Block %s",'metademands'),$f["rank"]);
         }
      }
      ksort($blocks);

      Dropdown::showFromArray('block_use', $blocks,
                              ['values'   => $values['block_use'],
                               'width'    => '100%',
                               'multiple' => true,
                               'entity'   => $_SESSION['glpiactiveentities']]);

      echo "</td>";
      echo "<td colspan='4'></td>";

      echo "</tr>";

      $metademands_ticket = new PluginMetademandsTicket();
      echo "<tr class='tab_bg_1'>";

      echo "<th>" . sprintf(__('%1$s%2$s'), __('Category'),
                            $tt->getMandatoryMark('itilcategories_id')) . "</th>";
      echo "<td>";

      $condition = [];
      switch ($values['type']) {
         case Ticket::DEMAND_TYPE :
            $condition = ['is_request' => 1];
            break;

         default: // Ticket::INCIDENT_TYPE :
            $condition = ['is_incident' => 1];
      }
      $opt = ['value'     => $values['itilcategories_id'],
              'condition' => $condition,
              'entity'    => $metademands->fields["entities_id"]];

      if ($values['itilcategories_id'] && $tt->isMandatoryField("itilcategories_id")) {
         $opt['display_emptychoice'] = false;
      }

      ITILCategory::dropdown($opt);
      echo "</td>";
      echo "<td colspan='4'></td>";

      echo "</tr>";

      if ($canchangeorder) {
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('Create after the task', 'metademands') . "</th>";
         echo "<td colspan='2'>";
         Dropdown::show('PluginMetademandsTask',
                        ['name'      => 'parent_tasks_id',
                         'value'     => $values['parent_tasks_id'],
                         'entity'    => $metademands->fields["entities_id"],
                         'condition' => ['type'                              => PluginMetademandsTask::TICKET_TYPE,
                                         'plugin_metademands_metademands_id' => $metademands->fields["id"],
                                         'id'                                => ['<>', $values['plugin_metademands_tasks_id']]]]);
         echo "<td>";
         echo "</tr>";
      }
      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th rowspan='3' width='$colsize1%'>" . __('Actors', 'metademands') . "</th>";
      if ($tt->isMandatoryField('_users_id_requester') || $tt->isMandatoryField('_groups_id_requester')) {
         echo "<th>" . __('Requester') . "</th>";
      } else {
         echo "<th>";
         echo "</th>";
      }
      if ($tt->isMandatoryField('_users_id_observer') || $tt->isMandatoryField('_groups_id_observer')) {
         echo "<th>" . __('Observer') . "</th>";
      } else {
         echo "<th>";
         echo "</th>";
      }
      echo "<th>" . __('Assigned to') . "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      if ($tt->isMandatoryField('_users_id_requester')) {
         echo "<td>";
         $ticket = new Ticket();
         // Requester user
         echo CommonITILObject::getActorIcon('user', CommonITILActor::REQUESTER) . '&nbsp;';
         echo $tt->getMandatoryMark('_users_id_requester');
         User::dropdown(['name'   => 'users_id_requester',
                         'value'  => isset($values['users_id_requester']) ? $values['users_id_requester'] : 0,
                         'entity' => $metademands->fields["entities_id"],
                         'right'  => $ticket->getDefaultActorRightSearch(CommonITILActor::REQUESTER)]);
         echo "</td>";
      } else {
         echo "<td>";
         echo "</td>";
      }
      if ($tt->isMandatoryField('_users_id_observer')) {
         echo "<td>";
         $ticket = new Ticket();
         // Observer user
         echo CommonITILObject::getActorIcon('user', CommonITILActor::OBSERVER) . '&nbsp;';
         echo $tt->getMandatoryMark('_users_id_observer');
         User::dropdown(['name'   => 'users_id_observer',
                         'value'  => isset($values['users_id_observer']) ? $values['users_id_observer'] : 0,
                         'entity' => $metademands->fields["entities_id"],
                         'right'  => $ticket->getDefaultActorRightSearch(CommonITILActor::OBSERVER)]);
         echo "</td>";
      } else {
         echo "<td>";
         echo "</td>";
      }
      echo "<td>";
      // Assign user
      echo CommonITILObject::getActorIcon('user', CommonITILActor::ASSIGN) . '&nbsp;';
      echo $tt->getMandatoryMark('_users_id_assign');
      User::dropdown(['name'   => 'users_id_assign',
                      'value'  => isset($values['users_id_assign']) ? $values['users_id_assign'] : 0,
                      'entity' => $metademands->fields["entities_id"],
                      'right'  => $ticket->getDefaultActorRightSearch(CommonITILActor::ASSIGN)]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      if ($tt->isMandatoryField('_groups_id_requester')) {
         echo "<td>";
         // Requester Group
         echo CommonITILObject::getActorIcon('group', CommonITILActor::REQUESTER) . '&nbsp;';
         echo $tt->getMandatoryMark('_groups_id_requester');
         Dropdown::show('Group', ['name'      => 'groups_id_requester',
                                  'value'     => isset($values['groups_id_requester']) ? $values['groups_id_requester'] : 0,
                                  'entity'    => $metademands->fields["entities_id"],
                                  'condition' => ['is_requester' => 1]]);
         echo "</td>";
      } else {
         echo "<td>";
         echo "</td>";
      }

      if ($tt->isMandatoryField('_groups_id_observer')) {
         echo "<td>";
         // Observer Group
         echo CommonITILObject::getActorIcon('group', CommonITILActor::OBSERVER) . '&nbsp;';
         echo $tt->getMandatoryMark('_groups_id_observer');
         Dropdown::show('Group', ['name'      => 'groups_id_observer',
                                  'value'     => isset($values['groups_id_observer']) ? $values['groups_id_observer'] : 0,
                                  'entity'    => $metademands->fields["entities_id"],
                                  'condition' => ['is_watcher' => 1]]);
         echo "</td>";
      } else {
         echo "<td>";
         echo "</td>";
      }
      echo "<td>";
      // Assign Group
      echo CommonITILObject::getActorIcon('group', CommonITILActor::ASSIGN) . '&nbsp;';
      echo $tt->getMandatoryMark('_groups_id_assign');
      Dropdown::show('Group', ['name'      => 'groups_id_assign',
                               'value'     => isset($values['groups_id_assign']) ? $values['groups_id_assign'] : 0,
                               'entity'    => $metademands->fields["entities_id"],
                               'condition' => ['is_assign' => 1]]);
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";
      // Status
      if ($tt->isMandatoryField('status') || $tt->isMandatoryField('requesttypes_id')) {
         echo "<tr class='tab_bg_1'>";
         if ($tt->isMandatoryField('status')) {
            echo "<th width='$colsize1%'>" . __('Status') . '&nbsp;:' . $tt->getMandatoryMark('status') . "</th>";
            echo "<td>";

            Ticket::dropdownStatus(['value' => isset($values['status']) ? $values['status'] : Ticket::INCOMING]);
            echo "</td>";
         } else {
            echo "<td colspan = '2'>";
            echo "</td>";
         }
         echo "</tr>";

         // Request type
         if ($tt->isMandatoryField('requesttypes_id')) {
            echo "<th width='$colsize1%'>" . __('Request source') . '&nbsp;:' . $tt->getMandatoryMark('requesttypes_id') . "</th>";
            echo "<td>";
            Dropdown::show('RequestType', ['value' => isset($values['requesttypes_id']) ? $values['requesttypes_id'] : '']);
            echo "</td>";
         } else {
            echo "<td colspan = '2'>";
            echo "</td>";
         }
         echo "</tr>";
      }

      if ($tt->isMandatoryField('actiontime') || $tt->isMandatoryField('itemtype')) {
         // Actiontime
         echo "<tr class='tab_bg_1'>";
         if ($tt->isMandatoryField('actiontime')) {
            echo "<th width='$colsize1%'>" . __('Total duration') . '&nbsp;:' . $tt->getMandatoryMark('actiontime') . "</th>";
            echo "<td>";
            Dropdown::showTimeStamp('actiontime', ['addfirstminutes' => true,
                                                   'value'           => isset($values['actiontime']) ? $values['actiontime'] : '']);
            echo "</td>";
         } else {
            echo "<td colspan = '2'>";
            echo "</td>";
         }

         // Itemtype
         if ($tt->isMandatoryField('itemtype')) {
            echo "<th width='$colsize1%'>" . __('Associated element') . '&nbsp;:' . $tt->getMandatoryMark('itemtype') . "</th>";
            echo "<td>";
            $dev_user_id  = 0;
            $dev_itemtype = 0;
            $dev_items_id = isset($values['itemtype']) ? $values['itemtype'] : '';
            Ticket::dropdownAllDevices('itemtype', $dev_itemtype, $dev_items_id,
                                       1, $dev_user_id, $metademands->fields["entities_id"]);
            echo "</td>";
         } else {
            echo "<td colspan = '2'>";
            echo "</td>";
         }
         echo "</tr>";
      }
      echo "</table>";

      echo "<table class='tab_cadre_fixe'>";
      // Title
      echo "<tr class='tab_bg_1'>";
      echo "<th width='$colsize1%'>" . __('Title') . '&nbsp;' . $tt->getMandatoryMark('name') . "</th>";
      echo "<td width='$colsize3%'>";
      $name = isset($values['name']) ? $values['name'] : '';
      echo "<input type='text' size='90' maxlength='250' name='name' value=\"$name\">";
      echo "</td>";
      echo "</tr>";

      // Description
      echo "<tr class='tab_bg_1'>";
      echo "<th width='$colsize1%'>" . __('Description') . "</th>";
      echo "<td width='$colsize3%'>";

      $rand      = mt_rand();
      $rand_text = mt_rand();
      Html::initEditorSystem("content" . $rand, $rand, true);
      if (!isset($values['content'])) {
         $content = '';
      } else {
         $content = $values['content'];
      }
      echo "<div id='content$rand_text'>";
      echo "<textarea id='content$rand' name='content' rows='3'>" . stripslashes($content) . "</textarea>";
      echo "</div>";

      echo "<input type='hidden' name='_tickettemplates_id' value='" . $tt->fields['id'] . "'>";
      echo "<input type='hidden' name='showForMetademands' value='1'>";
      echo "<input type='hidden' name='displayType' value='0'>";
//      $config          = PluginMetademandsConfig::getInstance();
//      $options         = ['config' => $config, 'root_doc' => $CFG_GLPI['root_doc']];
//      $options['lang'] = ['category' => __('Category')];
//      echo Html::scriptBlock(" if ($(\"input[name='itilcategories_id']\") != undefined) {
//                                    var object = $(document).metademandAdditionalFields(" . json_encode($options) . ");
//
//                                    if (object.params['config'].enable_families) {
//                                        object.metademands_loadFamilies(0, 0, \"form_ticket\");
//                                    }
//                                }");
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      echo "</div>";
   }

   /**
    * Print the field form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return bool (display)
    * @throws \GlpitestSQLError
    */
   function showForm($ID, $options = [""]) {

      if (!$this->canview() || !$this->cancreate()) {
         return false;
      }

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $this->check(-1, UPDATE);
         $this->getEmpty();
      }

      // Get associated meatdemands values
      $metademands = new PluginMetademandsMetademand();
      $this->getMetademandForTicketTask($ID, $metademands);

      $canedit = $metademands->can($metademands->getID(), UPDATE);

      // Check if metademand tasks has been already created
      $ticket_metademand      = new PluginMetademandsTicket_Metademand();
      $ticket_metademand_data = $ticket_metademand->find(['plugin_metademands_metademands_id' => $metademands->fields['id']]);
      $solved                 = PluginMetademandsTicket::isTicketSolved($ticket_metademand_data);
      if (!$solved && $canedit) {
         $metademands->showDuplication($metademands->fields['id']);
      }

      // Get associated tasks values
      $tasks = new PluginMetademandsTask();
      $tasks->getFromDB($this->fields['plugin_metademands_tasks_id']);

      $input                                = array_merge($tasks->fields, $this->fields);
      $input['plugin_metademands_tasks_id'] = $tasks->fields['id'];
      $input['parent_tasks_id']             = $tasks->fields['plugin_metademands_tasks_id'];

      // Get Template
      $ticket = new Ticket();
      $tt     = $ticket->getITILTemplateToUse(false, $input['type'], $input['itilcategories_id'], $input['entities_id']);

      echo "<form name='form_ticket' method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "?_in_modal=1&id=$ID' enctype=\"multipart/form-data\">";
      PluginMetademandsTicketTask::showTicketTaskForm($metademands->fields['id'], $solved, $input);
      echo "<input type='hidden' name='plugin_metademands_tasks_id' value='" . $this->fields['plugin_metademands_tasks_id'] . "'>";
      echo "<input type='hidden' name='_tickettemplates_id' value='" . $tt->fields['id'] . "'>";
      echo "<input type='hidden' name='type' value='" . $metademands->fields['type'] . "'>";
      echo "<input type='hidden' name='entities_id' value='" . $metademands->fields['entities_id'] . "'>";
      echo "<div><table class='tab_cadre_fixe'>";

      $options['canedit'] = $canedit;
      $options['candel']  = $solved;
      $this->showFormButtons($options);

      return true;
   }

   /**
    * @param        $input
    * @param bool   $showMessage
    * @param bool   $webserviceMode
    * @param string $customMessage
    *
    * @return array|bool
    */
   function isMandatoryField($input, $showMessage = true, $webserviceMode = false, $customMessage = '') {
      if (!$webserviceMode) {
         $_SESSION["metademandsHelpdeskSaved"] = $input;
      }

      $meta = new PluginMetademandsMetademand();
      if (isset($input["plugin_metademands_metademands_id"])) {
         $meta->getFromDB($input["plugin_metademands_metademands_id"]);

         $type    = $meta->getField("type");
         $categid = 0;
         if (isset($input['itilcategories_id'])) {
            $categid = $input['itilcategories_id'];
         }

         // Get Template
         $ticket = new Ticket();
         $tt     = $ticket->getITILTemplateToUse(false, $type, $categid, $input['entities_id']);

         $message           = '';
         $mandatory_missing = [];

         if (count($tt->mandatory)) {
            $fieldsname = $tt->getAllowedFieldsNames(true);
            foreach ($tt->mandatory as $key => $val) {
               if (isset($input[$key]) &&
                   (empty($input[$key]) || $input[$key] == 'NULL')
                   && (!in_array($key, PluginMetademandsTicketField::$used_fields))) {
                  $mandatory_missing[$key] = $fieldsname[$val];
               }
            }

            if (count($mandatory_missing)) {
               if (empty($customMessage)) {
                  $message = __('Mandatory field') . "&nbsp;" . implode(", ", $mandatory_missing);
               } else {
                  $message = $customMessage . "&nbsp;:&nbsp;" . implode(", ", $mandatory_missing);
               }
               if ($showMessage) {
                  Session::addMessageAfterRedirect($message, false, ERROR);
               }
               if (!$webserviceMode) {
                  return false;
               }
            }
         }

         unset($_SESSION["metademandsHelpdeskSaved"]);
      }
      if (!$webserviceMode) {
         return true;
      } else {
         return ['ticket_template' => $tt->fields['id'],
                 'mandatory_fields' => $mandatory_missing,
                 'message' => $message];
      }
   }

   /**
    * @param array $input
    *
    * @return array|bool
    */
   /**
    * @param array $input
    *
    * @return array|bool
    */
   function prepareInputForUpdate($input) {

      $this->getFromDB($input['id']);

      // Cannot update a used metademand category
      if (isset($input['itilcategories_id'])) {
         $type = $input["type"];
         if (isset($input['type'])) {
            $type = $input["type"];
         }
         if (!empty($input["itilcategories_id"])) {
            $dbu   = new DbUtils();
            $metas = $dbu->getAllDataFromTable('glpi_plugin_metademands_metademands', ["`itilcategories_id`" => $input["itilcategories_id"],
                                                                                       "`type`"              => $type]);

            if (!empty($metas)) {
               $input = [];
               Session::addMessageAfterRedirect(__('The category is related to a demand. Thank you to select another', 'metademands'), false, ERROR);
               return false;
            }
         }
      }

      return $input;
   }

   /**
    * @param                              $tasks_id
    * @param \PluginMetademandsMetademand $metademands
    *
    * @throws \GlpitestSQLError
    */
   function getMetademandForTicketTask($tasks_id, PluginMetademandsMetademand $metademands) {
      global $DB;

      if ($tasks_id > 0) {
         $query  = "SELECT `glpi_plugin_metademands_metademands`.*
                  FROM `glpi_plugin_metademands_tickettasks`
                  LEFT JOIN `glpi_plugin_metademands_tasks`
                    ON (`glpi_plugin_metademands_tickettasks`.`plugin_metademands_tasks_id` = `glpi_plugin_metademands_tasks`.`id`)
                  LEFT JOIN `glpi_plugin_metademands_metademands`
                    ON (`glpi_plugin_metademands_tasks`.`plugin_metademands_metademands_id` = `glpi_plugin_metademands_metademands`.`id`)
                  WHERE `glpi_plugin_metademands_tickettasks`.`id` = " . $tasks_id;
         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            $metademands->fields = $DB->fetchAssoc($result);
         } else {
            $metademands->getEmpty();
         }
      } else {
         $metademands->getEmpty();
      }
   }

}
