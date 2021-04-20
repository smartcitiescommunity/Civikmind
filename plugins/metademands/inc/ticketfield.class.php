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
 * Class PluginMetademandsTicketField
 */
class PluginMetademandsTicketField extends CommonDBChild {

   static public $itemtype = 'PluginMetademandsMetademand';
   static public $items_id = 'plugin_metademands_metademands_id';

   //4 => requester
   //71 => requester group
   static $used_fields = [
      'content', 'itilcategories_id', 'type', 'status',
      'time_to_resolve', 'itemtype',
                          'items_id', '_groups_id_requester', '_users_id_requester', 'slas_id', 4, 71
   ];

   static $types = ['PluginMetademandsMetademand'];

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
      return _n('Ticket field', 'Ticket fields', $nb, 'metademands');
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

      if (!$withtemplate) {
         if ($item->getType() == 'PluginMetademandsMetademand') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               $dbu = new DbUtils();
               return self::createTabEntry(self::getTypeName(2),
                                           $dbu->countElementsInTable($this->getTable(),
                                                                      ["plugin_metademands_metademands_id" => $item->getID()]));
            }
            return self::getTypeName(2);
         }
      }
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

      if (in_array($item->getType(), self::getTypes(true))) {
         $field->showPluginFromItems($item);
      }
      return true;
   }

   /**
    * Print the field form
    *
    * @param $item
    *
    * @return bool (display)
    * @throws \GlpitestSQLError
    */
   function showPluginFromItems($item) {

      if (!$this->canview()) {
         return false;
      }
      if (!$this->cancreate()) {
         return false;
      }

      $meta    = new PluginMetademandsMetademand();
      $canedit = $meta->can($item->fields['id'], UPDATE);

      $tt               = new TicketTemplate();
      $ticketfield_data = $this->find(['plugin_metademands_metademands_id' => $item->fields['id']]);
      $searchOption     = Search::getOptions('Ticket');

      $used_fields = $this->getPredefinedFields($item->fields['id'], true);
      $fields      = $tt->getAllowedFieldsNames(true, isset($used_fields['itemtype']));

      if ($canedit) {
         echo "<div class='center first-bloc'>";
         echo "<form name='ticketfield_form' method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='6'>";
         echo __('Synchronise with ticket template', 'metademands') . " ";
         $ticket = new Ticket();
         $tt     = $ticket->getITILTemplateToUse(0, $meta->fields["type"], $item->fields['itilcategories_id'], $item->fields['entities_id']);
         echo $tt->getLink();
         echo "</th>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
         echo "<td class='tab_bg_2 center'>";
         echo "<input type='submit' class='submit' name='template_sync' value='" . __('Synchronise with ticket template', 'metademands') . "'>";
         foreach ($item->fields as $name => $value) {
            echo "<input type='hidden' name='$name' value='$value'>";
         }
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      $this->listFields($ticketfield_data, $fields, $searchOption, $canedit, $tt);
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
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      if (!$this->canview()) {
         return false;
      }
      if (!$this->cancreate()) {
         return false;
      }

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $this->check(-1, UPDATE);
         $this->getEmpty();
      }

      $this->showFormHeader(['colspan' => 2]);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      $searchOption = Search::getOptions('Ticket');
      echo $searchOption[$this->fields['num']]['name'];

      echo "<input type='hidden' name='entities_id' value='" . $this->fields["entities_id"] . "'>";
      echo "<input type='hidden' name='is_recursive' value='" . $this->fields["is_recursive"] . "'>";
      echo "</td>";
      echo "<td>" . __('Value') . "</td>";
      echo "<td>";
      $used_fields   = $this->getPredefinedFields($ID, true);
      $itemtype_used = '';
      if (isset($used_fields['itemtype'])) {
         $itemtype_used = $used_fields['itemtype'];
      }
      echo "<span id='show_massiveaction_field'>&nbsp;</span>\n";
      $paramsmassaction = ['id_field'       => $this->fields["num"],
                           'value'          => $this->fields["value"],
                           'name'           => 'value',
                           'itemtype'       => 'Ticket',
                           'itemtype_used'  => $itemtype_used,
                           'relative_dates' => 1];

      Ajax::updateItem("show_massiveaction_field",
                       $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/dropdownMassiveActionField.php",
                       $paramsmassaction);
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons(['colspan' => 2, 'candel' => $this->fields["is_deletable"]]);

      return true;
   }

   /**
    * @param $ticketfield_data
    * @param $fields
    * @param $searchOption
    * @param $canedit
    * @param $tt
    */
   private function listFields($ticketfield_data, $fields, $searchOption, $canedit, $tt) {

      $ticket = new Ticket();

      $display_options = ['relative_dates' => true,
                          'comments'       => true,
                          'html'           => true];

      $rand = mt_rand();

      if (count($ticketfield_data) && count($fields)) {
         echo "<div class='center first-bloc'>";

         if ($canedit) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'>";
         echo "<th class='center b' colspan='6'>" . self::getTypeName(2) . "</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_2'>";
         echo "<th width='10'>";
         if ($canedit) {
            echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
         }
         echo "</th>";
         echo "<th>" . __('Name') . "</th>";
         echo "<th>" . __('Value') . "</th>";
         echo "</tr>";
         // Init navigation list for field items
         Session::initNavigateListItems($this->getType(), self::getTypeName(2));

         $fieldnames = $tt->getAllowedFields(true);
         foreach ($ticketfield_data as $id => $value) {
            if (!in_array($searchOption[$value['num']]['linkfield'], self::$used_fields)
                && !in_array($value['num'], self::$used_fields)) {
               Session::addToNavigateListItems($this->getType(), $id);
               echo "<tr class='tab_bg_1'>";
               echo "<td width='10'>";
//               $predefined = false;
//               if (isset($tt->predefined[$fieldnames[$value['num']]])) {
//                  $predefined = true;
//               }

               if ($canedit) {
                  Html::showMassiveActionCheckBox(__CLASS__, $id);
               }
               echo "</td>";
               echo "<td>";
//               if (!$predefined) {
                  echo "<a href='" . Toolbox::getItemTypeFormURL('PluginMetademandsTicketField') . "?id=" . $id . "'>" . $fields[$value['num']] . "</a> ";
//               } else {
//                  echo $fields[$value['num']] . " (" . __('Predefined value in template', 'metademands') . " " . $tt->getLink() . ") ";
//               }
               echo $tt->getMandatoryMark($fieldnames[$value['num']]);
               echo "</td>";
               echo "<td>";
               $display_datas = [];
               $display_datas[$searchOption[$value['num']]['field']] = $value['value'];

               echo $ticket->getValueToDisplay($searchOption[$value['num']], $display_datas, $display_options);
               echo "</td>";
               echo "</tr>";
            }
         }

         echo "</table>";

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         echo "</div>";
      } else {
         echo "<div class='center first-bloc'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><td class='center'>" . __('No item to display') . "</td></tr>";
         echo "</table></div>";
      }
   }

   /**
    * Get predefined fields for a template
    *
    * @param $ID the template ID
    * @param $withtypeandcategory bool with type and category
    *
    * @return an array of predefined fields
    **@throws \GlpitestSQLError
    * @throws \GlpitestSQLError
    * @since version 0.83
    *
    */
   function getPredefinedFields($ID, $withtypeandcategory = false) {
      global $DB;

      $sql    = "SELECT *
              FROM `" . $this->getTable() . "`
              WHERE `" . self::$items_id . "` = '$ID'
              ORDER BY `id`";
      $result = $DB->query($sql);

      $tt             = new TicketTemplate();
      $allowed_fields = $tt->getAllowedFields($withtypeandcategory, true);
      $fields         = [];

      while ($rule = $DB->fetchAssoc($result)) {
         if (isset($allowed_fields[$rule['num']])) {
            $fields[$allowed_fields[$rule['num']]] = $rule['value'];
         }
      }

      return $fields;
   }


   /**
    * Type that could be linked to a metademand
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      $dbu = new DbUtils();
      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!($item = $dbu->getItemForItemtype($type))) {
            continue;
         }

         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * @param $field_id
    * @param $name
    * @param $value
    */
   static function getSpecificTicketFields($field_id, $name, $value) {

      $ticket = new Ticket();

      switch ($name) {
         case '_users_id_requester':
            $params = ['name'  => 'ticketfield[' . $field_id . ']',
                       'value' => $value,
                       'right' => $ticket->getDefaultActorRightSearch(CommonITILActor::REQUESTER)];

            User::dropdown($params);
            break;
         case '_groups_id_requester':
            Dropdown::show('Group', ['name'      => 'ticketfield[' . $field_id . ']',
                                     'value'     => $value,
                                     'entity'    => $_SESSION['glpiactive_entity'],
                                     'condition' => ['is_watcher' => 1]]);
            break;
         case '_users_id_observer':
            $params = ['name'  => 'ticketfield[' . $field_id . ']',
                       'value' => $value,
                       'right' => $ticket->getDefaultActorRightSearch(CommonITILActor::OBSERVER)];

            User::dropdown($params);
            break;
         case '_groups_id_observer':
            Dropdown::show('Group', ['name'      => 'ticketfield[' . $field_id . ']',
                                     'value'     => $value,
                                     'entity'    => $_SESSION['glpiactive_entity'],
                                     'condition' => ['is_requester' => 1]]);
            break;
         case '_users_id_assign':
            $params = ['name'  => 'ticketfield[' . $field_id . ']',
                       'value' => $value,
                       'right' => $ticket->getDefaultActorRightSearch(CommonITILActor::ASSIGN)];

            User::dropdown($params);
            break;
         case '_groups_id_assign':
            Dropdown::show('Group', ['name'      => 'ticketfield[' . $field_id . ']',
                                     'value'     => $value,
                                     'entity'    => $_SESSION['glpiactive_entity'],
                                     'condition' => ['is_assign' => 1]]);
            break;
         case 'status':
            $opt = ['name' => 'ticketfield[' . $field_id . ']',
                    'value' => $value];
            Ticket::dropdownStatus($opt);
            break;
         case 'itemtype':
            $dev_user_id  = 0;
            $dev_itemtype = 0;
            $dev_items_id = $value;
            Ticket::dropdownAllDevices('ticketfield[' . $field_id . ']', $dev_itemtype, $dev_items_id,
                                       1, $dev_user_id, $_SESSION['glpiactive_entity']);
            break;
         case 'actiontime':
            Dropdown::showTimeStamp('ticketfield[' . $field_id . ']', ['addfirstminutes' => true,
                                                                       'value'           => $value]);
            break;
         case 'requesttypes_id';
            Dropdown::show('RequestType', ['name' => 'ticketfield[' . $field_id . ']', 'value' => $value]);
            break;
      }

   }

   /**
    * @param $input
    *
    * @return bool
    */
   static function updateMandatoryTicketFields($input) {
      if (isset($input['itilcategories_id']) && isset($input['entities_id']) && isset($input['id'])) {
         $meta = new PluginMetademandsMetademand();
         $meta->getFromDB($input['id']);
         $type = $meta->getField('type');
         // Add mandatory ticket fields
         self::addTemplateFields($input['id'], $input['itilcategories_id'], $type, $input['entities_id']);
         // Add predefined ticket fields
         self::addTemplateFields($input['id'], $input['itilcategories_id'], $type, $input['entities_id'], 'predefined');
      }

      return true;
   }


   /**
    * @param \ITILCategory $itilcategory
    */
   static function update_category_mandatoryFields(ITILCategory $itilcategory) {

      $categid = 0;
      if (isset($itilcategory->fields['id'])) {
         $categid = $itilcategory->fields['id'];
      }

      $metademands      = new PluginMetademandsMetademand();
      $metademands_data = $metademands->find(['entities_id'       => $_SESSION['glpiactive_entity'],
                                              'itilcategories_id' => $categid]);
      foreach ($metademands_data as $id => $value) {
         self::addTemplateFields($id, $categid, $value['type'], $value['entities_id']);
      }
   }


   /**
    * @param \ITILCategory $itilcategory
    */
   static function update_category_predefinedFields(ITILCategory $itilcategory) {

      $categid = 0;
      if (isset($itilcategory->fields['id'])) {
         $categid = $itilcategory->fields['id'];
      }
      $metademands      = new PluginMetademandsMetademand();
      $metademands_data = $metademands->find(['entities_id'       => $_SESSION['glpiactive_entity'],
                                              'itilcategories_id' => $categid]);
      foreach ($metademands_data as $id => $value) {
         self::addTemplateFields($id, $categid, $value['type'], $value['entities_id'], 'predefined');
      }
   }

   /**
    * @param \TicketTemplateMandatoryField $ttp
    */
   static function post_add_mandatoryField(TicketTemplateMandatoryField $ttp) {
      self::addFieldsFromTemplate($ttp);
   }

   /**
    * @param \TicketTemplatePredefinedField $ttp
    */
   static function post_add_predefinedField(TicketTemplatePredefinedField $ttp) {
      self::addFieldsFromTemplate($ttp);
   }

   /**
    * @param \TicketTemplateMandatoryField $ttp
    */
   static function post_delete_mandatoryField(TicketTemplateMandatoryField $ttp) {
      self::deleteFieldsFromTemplate($ttp);
   }

   /**
    * @param \TicketTemplatePredefinedField $ttp
    */
   static function post_delete_predefinedField(TicketTemplatePredefinedField $ttp) {
      self::deleteFieldsFromTemplate($ttp, 'predefined');
   }

   /**
    * @param $ttp
    */
   static function addFieldsFromTemplate($ttp) {
      $ticketField = new PluginMetademandsTicketField();
      $metademands = new PluginMetademandsMetademand();

      $metademands_data = $metademands->find();
      foreach ($metademands_data as $id => $value) {
         // Search for the metademand template
         $ticket     = new Ticket();
         $meta_tt    = $ticket->getITILTemplateToUse(0, $value['type'], $value['itilcategories_id'], $value['entities_id']);
         $fieldsname = $meta_tt->getAllowedFields(true);

         // Template of metademand found
         if ($meta_tt->fields['id'] == $ttp->fields['tickettemplates_id']) {
            if (!in_array($fieldsname[$ttp->fields['num']], self::$used_fields)
                && $ttp->fields['num'] != -2) {
               $used        = false;
               $fields_data = $ticketField->find(['plugin_metademands_metademands_id' => $id]);
               foreach ($fields_data as $fields_value) {
                  if ($fields_value['num'] == $ttp->fields['num']) {
                     $used = $fields_value['id'];
                     break;
                  }
               }

               switch ($fieldsname[$ttp->fields['num']]) {
                  case 'status':
                     $default_value = Ticket::INCOMING;
                     break;
                  case 'priority':
                  case 'urgency':
                  case 'impact':
                     $default_value = 3;
                     break;
                  default:
                     $default_value = 0;
                     break;
               }

               if (isset($meta_tt->predefined[$fieldsname[$ttp->fields['num']]])) {
                  $default_value = $meta_tt->predefined[$fieldsname[$ttp->fields['num']]];
               }

               if (!$used) {
                  $ticketField->add(['num'                               => $ttp->fields['num'],
                                     'value'                             => $default_value,
                                     'is_deletable'                      => 0,
                                     'type'                              => $value['type'],
                                     'is_mandatory'                      => 1,
                                     'entities_id'                       => $value['entities_id'],
                                     'plugin_metademands_metademands_id' => $id]);
               } else {
                  $ticketField->update(['id' => $used, 'value' => $default_value]);
               }
            }
         }
      }
   }

   /**
    * @param        $ttp
    * @param string $templatetype
    */
   static function deleteFieldsFromTemplate($ttp, $templatetype = 'mandatory') {
      $ticketField = new PluginMetademandsTicketField();
      $metademands = new PluginMetademandsMetademand();

      $metademands_data = $metademands->find();
      foreach ($metademands_data as $id => $value) {
         $ticket = new Ticket();
         $tt     = $ticket->getITILTemplateToUse(0, $value['type'], $value['itilcategories_id'], $value['entities_id']);

         if ($tt->fields['id'] == $ttp->fields['tickettemplates_id']) {
            $fieldsname = $tt->getAllowedFields(true);

            $used = false;
            if ($templatetype == 'mandatory' && isset($tt->predefined[$fieldsname[$ttp->fields['num']]])) {
               $used = true;
            }
            if ($templatetype == 'predefined' && isset($tt->mandatory[$fieldsname[$ttp->fields['num']]])) {
               $used = true;
            }

            if (!$used) {
               $ticketField->deleteByCriteria(['num' => $ttp->fields['num'], 'plugin_metademands_metademands_id' => $id]);
            }
         }
      }
   }

   /**
    * @param        $metademands_id
    * @param        $categid
    * @param        $type
    * @param        $entity
    * @param string $templatetype
    */
   static function addTemplateFields($metademands_id, $categid, $type, $entity, $templatetype = 'mandatory') {
      $ticketField = new self();
      $fields_data = $ticketField->find(['plugin_metademands_metademands_id' => $metademands_id]);

      $ticket = new Ticket();
      $tt     = $ticket->getITILTemplateToUse(0, $type, $categid, $entity);

      $fieldnames = $tt->getAllowedFields(true);
      $fieldnames = array_flip($fieldnames);

      // Get template type to add
      $templateToAdd = $tt->mandatory;
      switch ($templatetype) {
         case 'predefined':
            $templateToAdd = $tt->predefined;
            break;
      }

      if (count($templateToAdd)) {
         foreach ($templateToAdd as $key => $val) {
            $num = $fieldnames[$key];
            if (!in_array($key, self::$used_fields) && $num != -2) {
               $used = false;
               foreach ($fields_data as $fields_value) {
                  if ($fields_value['num'] == $num) {
                     $used = $fields_value['id'];
                     break;
                  }
               }
               switch ($key) {
                  case 'status':
                     $default_value = Ticket::INCOMING;
                     break;
                  case 'priority':
                     $default_value = 3;
                     break;
                  default:
                     $default_value = 0;
                     break;
               }

               if (isset($tt->predefined[$key])) {
                  $default_value = $tt->predefined[$key];
               }
//               $default_value = json_encode($default_value);
               if (!$used) {
                  $ticketField->add(['value'                             => $default_value,
                                     'num'                               => $num,
                                     'is_deletable'                      => 0,
                                     'is_mandatory'                      => 1,
                                     'entities_id'                       => $entity,
                                     'plugin_metademands_metademands_id' => $metademands_id]);
               } else {
                  if (!empty($default_value)) {
                     $ticketField->update(['id' => $used, 'value' => $default_value]);
                  }
               }
            }
         }
      }
   }

   /**
    * @return array
    */
   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();

      if (!self::canCreate()) {
         $forbidden[] = 'delete';
         $forbidden[] = 'purge';
         $forbidden[] = 'restore';
      }

      $forbidden[] = 'update';
      $forbidden[] = 'clone';
      $forbidden[] = 'add_transfer_list';
      return $forbidden;
   }

}
