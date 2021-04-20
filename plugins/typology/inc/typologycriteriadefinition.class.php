<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 typology plugin for GLPI
 Copyright (C) 2009-2016 by the typology Development Team.

 https://github.com/InfotelGLPI/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of typology.

 typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginTypologyTypologyCriteriaDefinition
 */
class PluginTypologyTypologyCriteriaDefinition extends CommonDBChild {

   public static $itemtype = 'PluginTypologyTypologyCriteria';
   public static $items_id = 'plugin_typology_typologycriterias_id';
   public $dohistory = true;
   static $rightname                = "plugin_typology";

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   public static function getTypeName($nb = 0) {

      return _n('Definition', 'Definitions', $nb, 'typology');
   }

   /**
    * display typologycriteriaDefinition's tab for each typologycriteria
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'PluginTypologyTypologyCriteria' :
               $nb = self::countForItem($item->fields['id']);
               return [self::createTabEntry(self::getTypeName(), $nb)];
         }
      }
      return '';
   }

   /**
    * Count of definitions
    * @param type $item
    * @return type
    */
   static function countForItem($id) {
      $typoCritDef = new PluginTypologyTypologyCriteriaDefinition();
      $datas = $typoCritDef->find(['plugin_typology_typologycriterias_id' =>$id]);
      return count($datas);
   }

   /**
    * display tab's content for each typologycriteria
    *
    * @static
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'PluginTypologyTypologyCriteria') {
         if (Session::haveRight("plugin_typology", READ)) {
            self::showForCriteria($item);
         } else {
            echo __('You don\'t have right to create a definition for this criteria. Thank to contact a person having this right.', 'typology');
         }
      }
      return true;
   }

   /**
    * Display the add Definition form
    *
    * @param $typocrit_id typocrit ID
    **/
   static function showForCriteria(PluginTypologyTypologyCriteria $typocrit) {
      global $DB;

      $typocrit_id = $typocrit->getField('id');

      $canedit = Session::haveRight("plugin_typology", UPDATE);
      $rand    = mt_rand();

      $query = "SELECT `glpi_plugin_typology_typologycriteriadefinitions`.`id`,
                        `glpi_plugin_typology_typologycriterias`.`itemtype`,
                        `glpi_plugin_typology_typologycriterias`.`link`,
                       `glpi_plugin_typology_typologycriteriadefinitions`.`field`,
                       `glpi_plugin_typology_typologycriteriadefinitions`.`action_type`,
                       `glpi_plugin_typology_typologycriteriadefinitions`.`value`,
                       `glpi_plugin_typology_typologycriteriadefinitions`.`plugin_typology_typologycriterias_id`
                FROM `glpi_plugin_typology_typologycriteriadefinitions`
                  LEFT JOIN `glpi_plugin_typology_typologycriterias`
                     ON (`glpi_plugin_typology_typologycriterias`.`id`
                           = `glpi_plugin_typology_typologycriteriadefinitions`.`plugin_typology_typologycriterias_id`)
                WHERE `plugin_typology_typologycriterias_id` = '" . $typocrit_id . "'
                ORDER BY `glpi_plugin_typology_typologycriteriadefinitions`.`id`";

      echo "<div class='firstbloc'>";

      if ($result = $DB->query($query)) {

         if (Session::haveRight("plugin_typology", UPDATE)) {
            echo "<form method='post' action='./typologycriteria.form.php'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='6'>" . PluginTypologyTypologyCriteriaDefinition::getTypeName(1) . "</tr>";
            echo "<input type='hidden' name='plugin_typology_typologycriterias_id' value='$typocrit_id'>";
            echo "<input type='hidden' name='entities_id' value='".$typocrit->getField('entities_id')."'>";
            echo "<input type='hidden' name='is_recursive' value='".$typocrit->getField('is_recursive')."'>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td>" . _n('Field', 'Fields', 2) . "</td><td>";
            PluginTypologyTypologyCriteriaDefinition::dropdownFields($typocrit_id);
            echo "</td>";

            echo "<td>";
            echo "<span id='span_actions' name='span_actions'></span></td>";

            echo "<td>";
            echo "<span id='span_values' name='span_values'></span></td>";

            echo"<td class='tab_bg_2 left' width='80px'>";
            echo "<input type='submit' name='add_action' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>\n";
            echo "</table>";
            Html::closeForm();
            echo "</div>";
         }
         if ($DB->num_fields($result)>0) {

            if ($canedit) {
               Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
               $massiveactionparams = ['item' => $typocrit, 'container' => 'mass'.__CLASS__.$rand];
               Html::showMassiveActions($massiveactionparams);
            }

            echo "<div class='center'><table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='4'>".PluginTypologyTypologyCriteriaDefinition::getTypeName(2)."</th></tr>";

            echo "<tr class='tab_bg_1 center'>";
            if ($canedit) {
               echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
            }
            echo "<th>"._n('Field', 'Fields', 2)."</th>";
            echo "<th class='center b'>" . __('Logical operator') . "</th>";
            echo "<th class='center b'>" . __('Value') . "</th>";
            echo "</tr>";

            while ($ligne = $DB->fetchArray($result)) {
               echo "<tr class='tab_bg_2'>";

               if ($canedit) {
                  echo "<td width='10'>";
                  Html::showMassiveActionCheckBox(__CLASS__, $ligne["id"]);
                  echo "</td>";
               }

               self::showMinimalDefinitionForm($ligne);

               echo "</tr>";
            }

            if ($canedit) {
               $massiveactionparams['ontop'] = false;
               Html::showMassiveActions($massiveactionparams);
               Html::closeForm();
            }
            echo "</table></div>";
         }
      }
   }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @since version 0.84
    *
    * @return an array of massive actions
    **/
   public function getForbiddenStandardMassiveAction() {
      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';

      return $forbidden;
   }

   /**
    * Display all the fields available depending on itemtype selected
    *
    * @static
    * @param $typocrit_id
    * @param int $value
    * @return mixed
    */
   static function dropdownFields($typocrit_id, $value = 0) {
      global $DB, $CFG_GLPI;
      $typoCritDef = new PluginTypologyTypologyCriteriaDefinition();
      $typoCritDef->fields['plugin_typology_typologycriterias_id'] = $typocrit_id;
      $typoCrit = new PluginTypologyTypologyCriteria();
      $typoCrit->getFromDB($typocrit_id);
      $itemtype = $typoCrit->fields['itemtype'];

      $dbu = new DbUtils();

      if (!isset($typoCritDef->fields['entities_id'])) {
         $typoCritDef->fields['entities_id'] = $_SESSION['glpiactive_entity'];
      }

      //Search option for this type
      $target = new $itemtype();

      echo "<select name='field' id='field'>";
      echo "<option value='0'>" . Dropdown::EMPTY_VALUE . "</option>";

      foreach ($DB->list_fields($dbu->getTableForItemType($itemtype)) as $field) {
         $searchOption = $target->getSearchOptionByField('field', $field['Field']);
         if (empty($searchOption)) {
            if ($table = $dbu->getTableNameForForeignKeyField($field['Field'])) {
               $searchOption = $target->getSearchOptionByField('field', 'name', $table);
            }
         }

         if (empty($searchOption)) {
            if ($table = $dbu->getTableNameForForeignKeyField($field['Field'])) {
               $crit = $dbu->getItemForItemtype($dbu->getItemTypeForTable($table));
               if ($crit instanceof CommonTreeDropdown) {
                  $searchOption = $target->getSearchOptionByField('field', 'completename', $table);
               } else {
                  $searchOption = $target->getSearchOptionByField('field', 'name', $table);
               }
            }
         }

         if (!empty($searchOption)
            && !in_array($field['Field'], self::getUnallowedFields($itemtype))
         ) {
            if (!empty($searchOption['datatype'])) {
               echo "<option value='" . $field['Field'] . ";" . $searchOption['table'] . ";" . $searchOption['datatype'] . "'";
            } else {
               echo "<option value='" . $field['Field'] . ";" . $searchOption['table'] . ";'";
            }

            echo  ">" . $searchOption['name'] . "</option>";

         }
      }
      if ($itemtype == 'DeviceMemory') {
         echo "<option value='count;glpi_items_devicememories;number'>" .
            _x('Quantity', 'Number') . "</option>";
      } else if ($itemtype == 'DeviceProcessor') {
         echo "<option value='count;glpi_items_deviceprocessors;number'>" .
            _x('Quantity', 'Number') . "</option>";
      } else if ($itemtype == 'Software') {
         echo "<option value='softwareversions_id;glpi_softwareversions;'>" .
            __('Name')." - "._n('Version', 'Versions', 2) . "</option>";
      }
      echo "</select>";

      $params = ['field' => '__VALUE__',
         'value' => $value,
         'itemtype' => $itemtype,
         'typocrit_id' => $typocrit_id];

      Ajax::updateItemOnSelectEvent("field", "span_actions",
         $CFG_GLPI["root_doc"]. PLUGIN_TYPOLOGY_DIR_NOFULL . "/ajax/dropdownAction.php",
         $params);
   }

   /**
    * List field not allowed
    *
    * @static
    * @param $itemclass
    * @return array
    */
   static function getUnallowedFields($itemclass) {
      switch ($itemclass) {
         //         case "DevicePowerSupply":
         //            return array('comment',
         //               'designation');
         //            break;
         //         case "DevicePci":
         //            return array('comment',
         //               'designation');
         //            break;
         //         case "DeviceCase":
         //            return array('comment',
         //               'designation');
         //            break;
         //         case "DeviceGraphicCard":
         //            return array('comment',
         //               'designation');
         //            break;
         //         case "DeviceMotherboard":
         //            return array('comment',
         //               'designation');
         //            break;
         //         case "DeviceNetworkCard":
         //            return array('comment',
         //               'designation');
         //            break;
         //         case "DeviceSoundCard":
         //            return array('comment',
         //               'designation');
         //            break;
         //         case "DeviceControl":
         //            return array('comment',
         //               'designation');
         //            break;
         case "DeviceHardDrive":
            return ['comment',
               'designation',
               'rpm',
               'interfacetypes_id',
               'cache',
               'manufacturers_id'];
            break;
         case "Printer" :
            return ['id',
               'name',
               'entities_id',
               'is_recursive',
               'date_mod',
               'contact',
               'contact_num',
               'users_id_tech',
               'groups_id_tech',
               'serial',
               'otherserial',
               'comment',
               'locations_id',
               'domains_id',
               'printermodels_id',
               'manufacturers_id',
               'is_global',
               'init_pages_counter',
               'last_pages_counter',
               'notepad',
               'groups_id',
               'users_id',
               'states_id'];
            break;
         //         case "DeviceDrive":
         //            return array('comment',
         //               'designation');
         //            break;
         case "Software" :
            return ['id',
         //               'name',
               'entities_id',
               'is_recursive',
               'comment',
               'locations_id',
               'users_id_tech',
               'groups_id_tech',
               'softwares_id',
               'manufacturers_id',
               'date_mod',
               'notepad',
               'groups_id',
               'users_id',
               'is_helpdesk_visible'];
            break;
         case "Monitor" :
            return ['id',
               'name',
               'entities_id',
               'date_mod',
               'contact',
               'contact_num',
               'users_id_tech',
               'groups_id_tech',
               'comment',
               'serial',
               'otherserial',
               'have_bnc',
               'locations_id',
               'monitormodels_id',
               'manufacturers_id',
               'is_global',
               'notepad',
               'users_id',
               'groups_id',
               'states_id'];
            break;
         case "DeviceMemory":
            return ['comment',
               'designation',
               'manufacturers_id',
               'devicememorytypes_id'];
            break;
         case "Computer" :
            return ['id',
               'name',
               'entities_id',
               'serial',
               'otherserial',
               'contact',
               'contact_num',
               'users_id_tech',
               'groups_id_tech',
               'comment',
               'date_mod',
         //               'os_license_number',
               'os_licenseid',
               'autoupdatesystems_id',
         //               'locations_id',
               'manufacturers_id',
               'computermodels_id',
               'notepad',
               'is_ocs_import',
               'users_id',
               'groups_id',
               //'states_id',
               'uuid'];
            break;
         case "NetworkPort":
            return ['id',
               'itemtype',
               'items_id',
               'logical_number',
         //               'name',
               'mac',
               'networkinterfaces_id',
               'netpoints_id',
               'comment'];
            break;
         case "IPAddress":
            return ['id',
               'itemtype',
               'items_id',
               'logical_number',
         //               'name',
               'mac',
               'networkinterfaces_id',
               'netpoints_id',
               'comment'];
            break;
         case "DeviceProcessor":
            return ['comment',
               'manufacturers_id',
         //               'specif_default',
               'frequence'];
            break;
         case "Peripheral" :
            return ['id',
               'name',
               'entities_id',
               'date_mod',
               'contact',
               'contact_num',
               'users_id_tech',
               'manufacturers_id',
               'peripheralmodels_id',
               'brand',
               'groups_id_tech',
               'comment',
               'serial',
               'otherserial',
               'locations_id',
               'is_global',
               'notepad',
               'users_id',
               'groups_id',
               'states_id'];
            break;
         //         case "Phone" :
         //            return array('id',
         //               'name',
         //               'entities_id',
         //               'date_mod',
         //               'contact',
         //               'contact_num',
         //               'users_id_tech',
         //               'groups_id_tech',
         //               'comment',
         //               'serial',
         //               'otherserial',
         //               'locations_id',
         //               'is_global',
         //               'notepad',
         //               'users_id',
         //               'groups_id',
         //               'states_id');
         //            break;
      }
   }

   /**
    * Display logical operator depending on field selected
    *
    * @static
    * @param $itemtype
    * @param $typocrit_id
    * @param $field
    * @param int $value
    * @return mixed
    */
   static function dropdownSelect($itemtype, $typocrit_id, $field, $value = 0) {
      global $CFG_GLPI;
      $test = explode(";", $field);
      $itemTable = $test[1];
      $itemDataType = $test[2];

      $dbu = new DbUtils();

      $typoCritDef = new PluginTypologyTypologyCriteriaDefinition();
      $typoCritDef->fields['plugin_typology_typologycriterias_id'] = $typocrit_id;
      $typoCritDef->fields['field'] = $field;

      if (!isset($typoCritDef->fields['field']) || !$typoCritDef->fields['field']) {
         echo  "</span>";
         return;
      }

      if (!isset($typoCritDef->fields['entities_id'])) {
         $typoCritDef->fields['entities_id'] = $_SESSION['glpiactive_entity'];
      }

      echo "<select name='action_type' id='action_type'>";
      echo "<option value='0'>" . Dropdown::EMPTY_VALUE . "</option>";

      switch ($itemDataType) {
         case "bool" :
            echo "<option value='equals'>" . __('is') . "</option>";
            echo "<option value='notequals'>" . __('is not') . "</option>";
            break;
         case "number" :
            echo "<option value='equals'>" . __('is') . "</option>";
            echo "<option value='notequals'>" . __('is not') . "</option>";
            echo "<option value='lessthan'>" . __('Less than', 'typology') . "</option>";
            echo "<option value='morethan'>" . __('More than', 'typology') . "</option>";
            break;
         case "text" :
            echo "<option value='equals'>" . __('is') . "</option>";
            echo "<option value='notequals'>" . __('is not') . "</option>";
            echo "<option value='lessthan'>" . __('Less than', 'typology') . "</option>";
            echo "<option value='morethan'>" . __('More than', 'typology') . "</option>";
            break;
         case "string" :
            echo "<option value='equals'>" . __('is') . "</option>";
            echo "<option value='notequals'>" . __('is not') . "</option>";
            echo "<option value='contains'>" . __('contains') . "</option>";
            echo "<option value='notcontains'>" . __('does not contain') . "</option>";
            break;
         //         case "ip" :
         //            echo "<option value='equals'>" . __('is') . "</option>";
         //            echo "<option value='notequals'>" . __('is not') . "</option>";
         //            echo "<option value='contains'>" . __('contains') . "</option>";
         //            echo "<option value='notcontains'>" . __('does not contain') . "</option>";
         //            echo "<option value='regex_match'>" . __('regular expression matches') . "</option>";
         //            echo "<option value='regex_not_match'>" . __('regular expression does not match') . "</option>";
         //            break;
         default :
            $item = $dbu->getItemForItemtype($dbu->getItemTypeForTable($itemTable));
            switch ($itemTable) {
               case "glpi_users":
                  echo "<option value='equals'>" . __('is') . "</option>";
                  echo "<option value='notequals'>" . __('is not') . "</option>";
                  echo "<option value='contains'>" . __('contains') . "</option>";
                  echo "<option value='notcontains'>" . __('does not contain') . "</option>";
                  break;
               case "glpi_softwareversions":
                  echo "<option value='equals'>" . __('is') . "</option>";
                  echo "<option value='notequals'>" . __('is not') . "</option>";
                  break;
               default :
                  echo "<option value='equals'>" . __('is') . "</option>";
                  echo "<option value='notequals'>" . __('is not') . "</option>";
                  echo "<option value='contains'>" . __('contains') . "</option>";
                  echo "<option value='notcontains'>" . __('does not contain') . "</option>";
                  if ($item instanceof CommonTreeDropdown) {
                     echo "<option value='under'>" . __('under') . "</option>";
                     echo "<option value='notunder'>" . __('not under') . "</option>";
                  }
                  if ($itemTable == 'glpi_ipaddresses') {
                     echo "<option value='regex_match'>" . __('regular expression matches') . "</option>";
                     echo "<option value='regex_not_match'>" . __('regular expression does not match') . "</option>";
                  }
                  break;
            }
            break;
      }
      echo "</select>";

      $params = ['action_type' => '__VALUE__',
         'value' => $value,
         'itemtype' => $itemtype,
         'field' => $field,
         'typocrit_id' => $typocrit_id];

      Ajax::updateItemOnSelectEvent("action_type", "span_values",
         $CFG_GLPI["root_doc"]. PLUGIN_TYPOLOGY_DIR_NOFULL . "/ajax/dropdownCaseValue.php",
         $params);

      if ($value > 0) {
         echo "<script type='text/javascript' >\n";
         echo "document.getElementById('action_type').value='" . $value . "';";
         echo "</script>\n";

         $params["typetable"] = $value;
         Ajax::UpdateItem("span_values",
            $CFG_GLPI["root_doc"]. PLUGIN_TYPOLOGY_DIR_NOFULL . "/ajax/dropdownCaseValue.php", $params);
      }
   }

   /**
    * Display all values available depending on field selected
    *
    * @static
    * @param $options
    * @param int $value
    */
   static function dropdownValues($options) {

      $itemtype = $options['itemtype'];
      $typocrit_id = $options['typocrit_id'];
      $field = $options['field'];
      $action = $options['action_type'];

      $test = explode(";", $field);
      $itemField = $test[0];
      $itemTable = $test[1];
      $itemDataType = $test[2];

      $typoCritDef = new PluginTypologyTypologyCriteriaDefinition();
      $typoCritDef->fields['plugin_typology_typologycriterias_id'] = $typocrit_id;
      $typoCritDef->fields['field'] = $field;
      $typoCrit = new PluginTypologyTypologyCriteria();
      $typoCrit->getFromDB($typocrit_id);

      if ($action == 'contains'
         || $action == 'notcontains'
            || $action == 'regex_match'
               || $action == 'regex_not_match') {
         Html::autocompletionTextField($typoCritDef, "value");
      } else {
         switch ($itemDataType) {
            case "bool" :
               Dropdown::showYesNo('value');
               break;
            case "number" :
               Html::autocompletionTextField($typoCritDef, "value");
               if ($itemField == 'size') {
                  echo "\"";
               }
               break;
            case "string" :
               Html::autocompletionTextField($typoCritDef, "value");
               break;
            case "text" :
               Html::autocompletionTextField($typoCritDef, "value");
               if ($itemtype=='DeviceHardDrive') {
                  echo " ".__('Mio');
               } else if ($itemField == 'frequence') {
                  echo " ".__('MHz');
               } else if ($itemtype=='DeviceMemory' && $itemField == 'specif_default') {
                  echo " ".__('Mio');
               } else if ($itemtype=='DeviceProcessor' && $itemField == 'specif_default') {
                  echo " ".__('MHz');
               }
               break;
            //            case "ip" :
            //               Html::autocompletionTextField($typoCritDef, "value");
            //               break;
            default :
               switch ($itemTable) {
                  case "glpi_users":
                     User::dropdown(['right' => 'all',
                                          'name' => 'value']);
                     break;
                  case "glpi_softwareversions":
                     Software::dropdownSoftwareToInstall("value", $typoCrit->fields['entities_id']);
                     break;
                  default :
                     $dbu = new DbUtils();
                     $itemclass = $dbu->getItemTypeForTable($itemTable);
                     Dropdown::show($itemclass, ['name' => 'value','entity' => $typoCrit->fields['entities_id']]);
                     break;
               }
               break;
         }
      }

      echo "</span></td>";
   }

   /**
    * Show the minimal form for the definition criteria
    *
    * @param $ligne datas used to display the definition
    **/
   static function showMinimalDefinitionForm($ligne, $options = []) {
      global $DB,$CFG_GLPI;

      $params['seeResult'] = 0;
      $params['seeItemtype'] = 0;
      $img_OK = "<i style='color:forestgreen' class='question fas fa-check-circle fa-2x'></i>";
      $img_NOT = "<i style='color:darkred' class='question fas fa-times-circle fa-2x'></i>";

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $item = new $ligne["itemtype"]();

      if ($params['seeItemtype']) {
         echo "<td class='center'>";

         echo $item->getTypeName(0);

         echo "</td>";

         echo "<td class='center'>";
         if ($ligne['link'] == 0) {
            echo __('and');
         } else if ($ligne['link'] == 1) {
            echo __('or');
         }
         echo "</td>";

      }

      echo "<td class='center'>";

      $test = explode(";", $ligne["field"]);
      $itemField = $test[0];
      $itemTable = $test[1];
      $itemDataType = $test[2];

      $dbu = new DbUtils();
      if ($itemField == 'count') {
         echo _x('Quantity', 'Number');
      } else {
         $searchOption = $item->getSearchOptionByField('field', $itemField);

         if (empty($searchOption)) {
            $crit = $dbu->getItemForItemtype($dbu->getItemTypeForTable($itemTable));
            if ($crit instanceof CommonTreeDropdown) {
               $searchOption = $item->getSearchOptionByField('field', 'completename', $itemTable);
               echo  $searchOption['name'];
            } else {
               $searchOption = $item->getSearchOptionByField('field', 'name', $itemTable);
               echo  $searchOption['name'];
            }
         } else {
            echo  $searchOption['name'];
         }
      }
      echo "</td>";

      if ($params['seeResult']) {
         echo "<td class='center'>";
         if ($ligne['result'] == 'ok') {
            echo $img_OK;
         } else if ($ligne['result'] == 'not_ok') {
            echo $img_NOT;
         }
         echo " </td>";
      }

      // logical operator
      echo "<td class='center'>";

      if ($ligne['action_type'] == 'contains') {
         echo __('contains');
      } else if ($ligne['action_type'] == 'notcontains') {
         echo __('does not contain');
      } else if ($ligne['action_type'] == 'lessthan') {
         echo __('Less than', 'typology');
      } else if ($ligne['action_type'] == 'morethan') {
         echo __('More than', 'typology');
      } else if ($ligne['action_type'] == 'regex_match') {
         echo __('regular expression matches');
      } else if ($ligne['action_type'] == 'regex_not_match') {
         echo __('regular expression does not match');
      } else if ($ligne['action_type'] == 'equals') {
         echo __('is');
      } else if ($ligne['action_type'] == 'notequals') {
         echo __('is not');
      } else if ($ligne['action_type'] == 'under') {
         echo __('under');
      } else if ($ligne['action_type'] == 'notunder') {
         echo __('not under');
      }

      echo "</td>";

      // criteria value
      echo "<td class='center'>";

      if ($ligne['action_type'] == 'contains'
         || $ligne['action_type'] == 'notcontains'
            || $ligne['action_type'] == 'regex_match'
               || $ligne['action_type'] == 'regex_not_match') {
         echo $ligne["value"];
      } else {
         switch ($itemDataType) {
            case "bool" :
               echo Dropdown::getYesNo($ligne["value"]);
               break;
            case "number" :
               echo $ligne["value"];
               if ($itemField == 'size') {
                  echo "\"";
               }
               break;
            case "string" :
               echo $ligne["value"];
               break;
            case "text" :
               echo $ligne["value"];
               if ($ligne["itemtype"]=='DeviceHardDrive') {
                  echo " ".__('Mio');
               } else if ($itemField == 'frequence') {
                  echo " ".__('MHz');
               } else if ($ligne["itemtype"]=='DeviceMemory' && $itemField == 'specif_default') {
                  echo " ".__('Mio');
               } else if ($ligne["itemtype"]=='DeviceProcessor' && $itemField == 'specif_default') {
                  echo " ".__('MHz');
               }
               break;
            //            case "ip" :
            //               echo $ligne["value"];
            //               break;
            default :
               switch ($itemTable) {
                  case "glpi_users":
                     echo $dbu->getUserName($ligne["value"]);
                     break;
                  case "glpi_softwareversions":
                     $query = "SELECT `glpi_softwares`.`name` as softname,
                                      `glpi_softwareversions`.`name` as vname,
                                      `glpi_softwareversions`.`id` as vid
                               FROM `glpi_softwareversions`
                               INNER JOIN `glpi_softwares` on (`glpi_softwareversions`.`softwares_id` = `glpi_softwares`.`id`)
                               WHERE `glpi_softwareversions`.`id`='".$ligne["value"]."'";
                     if ($result = $DB->query($query)) {
                        while ($data = $DB->fetchArray($result)) {
                           echo $data['softname']." - ";
                           if ($data['vname']=='') {
                              echo "(".$data['vid'].")";
                           } else {
                              echo $data['vname'];
                           }
                        }
                     }
                     break;
                  default :
                     if ($item instanceof CommonDevice) {
                        $item->getFromDB($ligne["value"]);
                        echo $item->getName();
                     } else {
                        echo Dropdown::getDropdownName($itemTable, $ligne["value"]);
                     }
                     break;
               }
               break;
         }
      }

      echo "</td>";

      // computer value
      if ($params['seeResult']) {

         echo "<td class='center'>";

         if (($ligne['realvalue'][0] > 1
               && $ligne['realvalue'][1] > 1)
                  || ($ligne['realvalue'][0] == 1
                        && $ligne['realvalue'][1] == 2)) {
            echo $ligne['realvalue'][0].' / '.$ligne['realvalue'][1];
            echo "<br>";
         } else if ($ligne['realvalue'][0] == 0
               && $ligne['realvalue'][1] != 1) {
            echo __('Item not found');
         }

         if ((isset($ligne['list_ok'])
            && $ligne['realvalue'][0] > 0 )
            || ($ligne['realvalue'][0] == 0
               && $ligne['realvalue'][1] == 1)) {

            $count = count($ligne['list_ok']);
            $i = 0;

            foreach ($ligne['list_ok'] as $key => $val) {
               $i++;
               if ($val == '') {
                  $val = __('Item not found');
               }
               if ($itemDataType != "bool") {
                  echo $val;
                  if ($itemField == 'size') {
                     echo "\"";
                  } else if ($ligne["itemtype"]=='DeviceHardDrive') {
                     echo " ".__('Mio');
                  } else if ($itemField == 'frequence') {
                     echo " ".__('MHz');
                  } else if ($ligne["itemtype"]=='DeviceMemory' && $itemField == 'specif_default') {
                     echo " ".__('Mio');
                  } else if ($ligne["itemtype"]=='DeviceProcessor' && $itemField == 'specif_default') {
                     echo " ".__('MHz');
                  }
               } else {
                  echo Dropdown::GetYesNo($val);
               }
               if ($i < $count) {
                  echo "<br>";
               }
            }
         }
         echo "</td>";
      }
   }

   /**
    * recover all value for console management display
    *
    * @static
    * @param $tabCritID
    * @param $pcID
    * @param $itemtype
    * @param $display
    * @return null
    */
   static function getConsoleData($tabCritID, $pcID, $itemtype, $display) {
      $valueFromDef = null;

      foreach ($tabCritID as $critID) {
         $valueFromDef[$itemtype][$critID] = self::getValueFromDef($critID);
      }

      $valueFromDef = self::getRealValue($pcID, $valueFromDef);

      if ($display) {
         $valueFromDef=self::getComputeResultByDef($valueFromDef, $pcID);
      } else {
         $valueFromDef=self::getComputeResultByCriteria($valueFromDef);
      }
      return $valueFromDef;
   }

   /**
    * get all definitions value for each criteria
    *
    * @static
    * @param $critID
    * @return array
    */
   static function getValueFromDef($critID) {
      global $DB;
      $resp = [];
      $query ="SELECT `glpi_plugin_typology_typologycriteriadefinitions`.`id` AS id,
                      `glpi_plugin_typology_typologycriteriadefinitions`.`plugin_typology_typologycriterias_id` AS plugin_typology_typologycriterias_id,
                      `glpi_plugin_typology_typologycriterias`.`link` AS link,
                      `glpi_plugin_typology_typologycriteriadefinitions`.`field` AS field,
                      `glpi_plugin_typology_typologycriteriadefinitions`.`action_type` AS action_type,
                      `glpi_plugin_typology_typologycriteriadefinitions`.`value` AS value
               FROM `glpi_plugin_typology_typologycriteriadefinitions`
               LEFT JOIN `glpi_plugin_typology_typologycriterias`
                  ON (`glpi_plugin_typology_typologycriterias`.`id` = `glpi_plugin_typology_typologycriteriadefinitions`.`plugin_typology_typologycriterias_id`)
               WHERE `glpi_plugin_typology_typologycriteriadefinitions`.`plugin_typology_typologycriterias_id` ".
         " = '".$critID."'
         ORDER BY `id`";
      foreach ($DB->request($query) as $data) {
         $resp[$data['id']] = $data;
      }
      return $resp;

   }

   /**
    * get real computer value
    *
    * @static
    * @param $pcID
    * @param $valueFromDef
    * @return mixed
    */
   static function getRealValue($pcID, $valueFromDef) {
      global $DB;

      $dbu = new DbUtils();

      foreach ($valueFromDef as $itemtype=>$allcrit) {
         if (!empty($allcrit)) {
            $item = new $itemtype();

            foreach ($allcrit as $key1=>$allDef) {
               if (!empty($allDef)) {

                  foreach ($allDef as $key2=>$def) {
                     if (!empty($def)) {
                        $test= explode(";", $def["field"]);
                        $itemField = $test[0];
                        $itemTable = $test[1];
                        $itemDataType = $test[2];

                        //1-SELECT
                        $queryReal = "SELECT ";
                        if ($itemField == 'count') {
                           $queryReal.= "COUNT(*) ";
                           $queryReal.= "as Field ";
                           $searchOption['table'] = $itemTable;
                        } else if ($itemField == 'softwareversions_id') {
                           $searchOption['table'] = $itemTable;
                           $searchOption['field'] = 'id';
                           $searchOption['name'] = __('Name')." - "._n('Version', 'Versions', 2);
                           $queryReal.= "`".$searchOption['table']."`.`".$searchOption['field']."` ";
                           $queryReal.= "as Field ";
                        } else if ($itemField == 'softwarecategories_id') {
                           $searchOption['table'] = $itemTable;
                           $queryReal.= " `glpi_softwares`.`name` as softwares_name, `glpi_softwarecategories`.`name` as softwarecategories_name, `glpi_softwarecategories`.`name` as Field ";
                        } else {
                           $searchOption = $item->getSearchOptionByField('field', $itemField);

                           if (empty($searchOption)) {
                              $crit = $dbu->getItemForItemtype($dbu->getItemTypeForTable($itemTable));
                              if ($crit instanceof CommonTreeDropdown) {
                                 $searchOption = $item->getSearchOptionByField('field', 'completename', $itemTable);
                              } else {
                                 $searchOption = $item->getSearchOptionByField('field', 'name', $itemTable);
                              }
                           }

                           $queryReal.= "`".$searchOption['table']."`.`".$searchOption['field']."` ";
                           $queryReal.= "as Field ";
                           $queryReal.= ",`".$searchOption['table']."`.`id` ";
                           $queryReal.= "as Field_id ";
                        }

                        // 2 - FROM
                        switch ($itemtype) {
                           case "Computer":
                              $queryReal .= " FROM `glpi_computers`";
                              if ($searchOption['table'] != 'glpi_computers') {
                                 $queryReal .= " INNER JOIN `" . $searchOption['table'] . "`";
                                 $fk = $dbu->getForeignKeyFieldForTable($searchOption['table']);
                                 $queryReal .= " ON (`glpi_computers`.`" . $fk . "`= `" . $searchOption['table'] . "`.`id`)";
                              }
                              $queryReal .= " WHERE `glpi_computers`.`id` = '$pcID'";
                              break;
                           case "Monitor":
                           case "Peripheral":
                           case "Printer":
                              $queryReal .= " FROM `glpi_computers_items`";
                              if (strstr($searchOption['table'], 'types')) {
                                 $table = str_replace('types', 's', $searchOption['table']);
                                 $fk        = $dbu->getForeignKeyFieldForTable($searchOption['table']);
                                 $queryReal.= " INNER JOIN `".$table."`";
                                 $queryReal.= " ON (`glpi_computers_items`.`items_id` = `".$dbu->getTableForItemType($itemtype)."`.`id`)";
                                 $queryReal.= " INNER JOIN `".$searchOption['table']."`";
                                 $queryReal.= " ON (`".$table."`.`".$fk."` = `".$searchOption['table']."`.`id`)";
                              } else if ($searchOption['table']=='glpi_networks') {
                                 $table = $dbu->getTableForItemType($itemtype);
                                 $fk        = $dbu->getForeignKeyFieldForTable($searchOption['table']);
                                 $queryReal.= " INNER JOIN `".$table."`";
                                 $queryReal.= " ON (`glpi_computers_items`.`items_id` = `".$dbu->getTableForItemType($itemtype)."`.`id`)";
                                 $queryReal.= " INNER JOIN `".$searchOption['table']."`";
                                 $queryReal.= " ON (`".$table."`.`".$fk."` = `".$searchOption['table']."`.`id`)";

                              } else {
                                 $queryReal .= " INNER JOIN `".$searchOption['table']."`";
                                 $queryReal .= " ON (`glpi_computers_items`.`items_id` = `".$dbu->getTableForItemType($itemtype)."`.`id`)";
                              }
                              $queryReal .= " WHERE `glpi_computers_items`.`itemtype` = '".$itemtype."'
                                                AND `glpi_computers_items`.`computers_id` = '$pcID'";
                              break;
                           case "Software":
                              $queryReal .= " FROM `glpi_items_softwareversions`";
                              $queryReal .= " LEFT JOIN `glpi_softwareversions` 
                                             ON (`glpi_items_softwareversions`.`softwareversions_id` = `glpi_softwareversions`.`id`)";
                              $queryReal .= " LEFT JOIN `glpi_softwares` 
                                             ON (`glpi_softwareversions`.`softwares_id` = `glpi_softwares`.`id`)";
                              $queryReal .= " LEFT JOIN `glpi_softwarecategories` 
                                             ON (`glpi_softwares`.`softwarecategories_id` = `glpi_softwarecategories`.`id`)";
                              $queryReal .= " WHERE `glpi_items_softwareversions`.`itemtype` ='Computer' 
                              AND `glpi_items_softwareversions`.`items_id` ='$pcID'";
                              break;
                           case "IPAddress":
                              $queryReal .= " FROM `glpi_networkports`";
                              $queryReal .= " LEFT JOIN `glpi_networknames`".
                                             " ON (`glpi_networkports`.`id` = `glpi_networknames`.`items_id`".
                                             " AND `glpi_networknames`.`itemtype`='NetworkPort')";
                              $queryReal .= " LEFT JOIN `glpi_ipaddresses`".
                                             " ON (`glpi_networknames`.`id` = `glpi_ipaddresses`.`items_id`".
                                             " AND `glpi_ipaddresses`.`itemtype`='NetworkName')";
                              $queryReal .= " WHERE `glpi_networkports`.`itemtype` = 'Computer'".
                                            " AND `glpi_networkports`.`items_id` = '$pcID'";
                              break;
                           //                           case "NetworkPort":
                           //                              $queryReal .= " FROM `glpi_networkports`";
                           //                              $queryReal .= " WHERE `glpi_networkports`.`itemtype` = 'Computer'".
                           //                                            " AND `glpi_networkports`.`items_id` = '$pcID'";
                           //                              break;
                           case "DeviceProcessor":
                           case "DeviceMemory":
                           case "DeviceHardDrive":
                              if ($itemField == 'count') {
                                 $queryReal .= " FROM `".$searchOption['table']."`";
                                 $queryReal .= " WHERE `items_id` = '$pcID' AND `itemtype` = 'Computer'";

                              } else {
                                 $linktable = $dbu->getTableForItemType('items_'.$itemtype);
                                 $fk        = $dbu->getForeignKeyFieldForTable($dbu->getTableForItemType($itemtype));

                                 $queryReal .= " FROM `".$linktable."`";
                                 $queryReal .= " INNER JOIN `".$searchOption['table']."`";
                                 $queryReal .= " ON (`".$linktable."`.`".$fk."` = `".$searchOption['table']."`.`id`)";
                                 $queryReal .= " WHERE `".$linktable."`.`items_id` = '$pcID'".
                                                " AND `".$linktable."`.`itemtype` = 'Computer'";
                              }
                              break;
                        }

                        $nbReal=0;
                        $real_value= null;
                        $nbok = 0;
                        $list = [];
                        foreach ($DB->request($queryReal) as $data) {

                           $nbReal++;

                           if ($def['action_type'] == 'equals') {
                              if ($itemDataType == "text"
                              //                                    || $itemDataType == "ip"
                                       || $itemField == 'softwareversions_id') {
                                 if ($data['Field'] == $def["value"]) {

                                    $nbok++;

                                    if ($itemField == 'softwareversions_id') {
                                       $query = "SELECT `glpi_softwares`.`name` AS softname,
                                                        `glpi_softwareversions`.`name` AS vname,
                                                        `glpi_softwareversions`.`id` AS vid
                                                 FROM `glpi_softwareversions`
                                                INNER JOIN `glpi_softwares`
                                                ON (`glpi_softwareversions`.`softwares_id` = `glpi_softwares`.`id`)
                                                WHERE `glpi_softwareversions`.`id`='".$def["value"]."'";
                                       if ($result = $DB->query($query)) {
                                          while ($data = $DB->fetchArray($result)) {
                                                $name= $data['softname']." - ";
                                             if ($data['vname']=='') {
                                                $name.= "(".$data['vid'].")";
                                             } else {
                                                $name.= $data['vname'];
                                             }
                                             $list[] = $name;
                                          }
                                       }
                                    } else {
                                       $list[]=$data['Field'];
                                    }
                                 }
                              } else if ($itemDataType== "bool" || $itemDataType == "number") {
                                 if ($data['Field'] == $def["value"]) {
                                    $nbok++;
                                    $list[]=$data['Field'];
                                 }

                              } else {
                                 if (stristr($searchOption['table'], 'device')) {// If device type
                                    $query = "SELECT `".$searchOption['table']."`.`designation`
                                              FROM `".$searchOption['table']."`
                                              WHERE `".$searchOption['table']."`.`designation` = '".$data['Field']."'";

                                    if ($resultQuery = $DB->query($query)) {
                                       $tabResult = $DB->fetchAssoc($resultQuery);
                                    }
                                    $dropdownResult = $tabResult['designation'];
                                 } else {
                                    $dropdownResult = Dropdown::getDropdownName($searchOption['table'], $def["value"]);
                                 }

                                 if ($data['Field'] == $dropdownResult) {
                                    $nbok++;

                                    if ($itemField == 'softwarecategories_id') {
                                        $name = $data['Field'];
                                       if (isset($data['softwares_name'])) {
                                           $name .= ' <span class="italic">('.
                                               $data['softwares_name'].')</span>';
                                       }
                                        $list[]=$name;
                                    } else {
                                        $list[]=$data['Field'];
                                    }
                                 }
                              }
                           } else if ($def['action_type'] == 'notequals') {
                              if ($itemDataType == "text"
                                 || $itemDataType == "ip"
                                 || $itemField == 'softwareversions_id') {
                                 if ($data['Field'] == $def["value"]) {
                                    $nbok++;
                                    if ($itemField == 'softwareversions_id') {
                                       $query = "SELECT `glpi_softwares`.`name` AS softname,
                                                        `glpi_softwareversions`.`name` AS vname,
                                                        `glpi_softwareversions`.`id` AS vid
                                                   FROM `glpi_softwareversions`
                                                   INNER JOIN `glpi_softwares`
                                                   ON (`glpi_softwareversions`.`softwares_id` = `glpi_softwares`.`id`)
                                                   WHERE `glpi_softwareversions`.`id` = '".$def["Field"]."'";
                                       if ($result = $DB->query($query)) {
                                          while ($data = $DB->fetchArray($result)) {
                                             $name= $data['softname']." - ";
                                             if ($data['vname']=='') {
                                                $name.= "(".$data['vid'].")";
                                             } else {
                                                $name.= $data['vname'];
                                             }
                                             $list[] = $name;
                                          }
                                       }
                                    } else {
                                       $list[]=$data['Field'];
                                    }
                                 }
                              } else if ($itemDataType == "bool" || $itemDataType == "number") {
                                 if ($data['Field'] == $def["value"]) {
                                    $nbok++;
                                    $list[]=$data['Field'];
                                 }

                              } else {
                                 if (stristr($searchOption['table'], 'device')) {// If device type
                                    $query = "SELECT `".$searchOption['table']."`.`designation`
                                                 FROM `".$searchOption['table']."`
                                                 WHERE `".$searchOption['table']."`.`designation` = '".$data['Field']."'";

                                    if ($resultQuery = $DB->query($query)) {
                                       $tabResult = $DB->fetchAssoc($resultQuery);
                                    }
                                       $dropdownResult = $tabResult['designation'];
                                 } else {
                                    $dropdownResult = Dropdown::getDropdownName($searchOption['table'], $def["value"]);
                                 }

                                 if ($data['Field'] == $dropdownResult) {
                                    $nbok++;

                                    if ($itemField == 'softwarecategories_id') {
                                        $name = $data['Field'];
                                       if (isset($data['softwares_name'])) {
                                           $name .= ' <span class="italic">('.
                                               $data['softwares_name'].')</span>';
                                       }
                                        $list[]=$name;
                                    } else {
                                        $list[]=$data['Field'];
                                    }
                                 }
                              }
                           } else if ($def['action_type'] == 'lessthan') {
                              if ($data['Field'] <= $def["value"]) {
                                 $nbok++;
                                 $list[]=$data['Field'];
                              }
                           } else if ($def['action_type'] == 'morethan') {
                              if ($data['Field'] >= $def["value"]) {
                                 $nbok++;
                                 $list[]=$data['Field'];

                              }
                           } else if ($def['action_type'] == 'contains') {
                              if (stristr($data['Field'], $def["value"])) {
                                 $nbok++;
                                 if ($itemField == 'softwarecategories_id') {
                                    $name = $data['Field'];
                                    if (isset($data['softwares_name'])) {
                                        $name .= ' <span class="italic">('.
                                            $data['softwares_name'].')</span>';
                                    }
                                    $list[]=$name;
                                 } else {
                                    $list[]=$data['Field'];
                                 }
                              }
                           } else if ($def['action_type'] == 'notcontains') {
                              if (stristr($data['Field'], $def["value"])) {
                                  $nbok++;
                                 if ($itemField == 'softwarecategories_id') {
                                     $name = $data['Field'];
                                    if (isset($data['softwares_name'])) {
                                        $name .= ' <span class="italic">('.
                                            $data['softwares_name'].')</span>';
                                    }
                                       $list[]=$name;
                                 } else {
                                    $list[]=$data['Field'];
                                 }
                              }
                           } else if ($def['action_type'] == 'regex_match') {
                              if (preg_match($def["value"], $data['Field'])) {
                                 $nbok++;
                                 $list[]=$data['Field'];

                              }
                           } else if ($def['action_type'] == 'regex_not_match') {
                              if (!preg_match($def["value"], $data['Field'])) {
                                 $nbok++;
                                 $list[]=$data['Field'];

                              }
                           } else if ($def['action_type'] == 'under') {

                              $sons = $dbu->getSonsOf($itemTable, $def["value"]);
                              if (in_array($data['Field_id'], $sons)) {
                                 $nbok++;
                                 $list[]=$data['Field'];
                              }
                           } else if ($def['action_type'] == 'notunder') {

                              $sons = $dbu->getSonsOf($itemTable, $def["value"]);

                              if (!in_array($data['Field_id'], $sons)) {
                                 $nbok++;
                                 $list[]=$data['Field'];
                              }
                           }
                        }
                        //display the real value even if $nbok==0
                        if ($nbReal == 1 && $nbok==0) {
                           foreach ($DB->request($queryReal) as $data) {
                              $list[]=$data['Field'];
                           }
                        }
                        $valueFromDef[$itemtype][$key1][$key2]["list_ok"]=$list;
                        $valueFromDef[$itemtype][$key1][$key2]["realvalue"]=[$nbok,$nbReal];
                     }
                  }
               }
            }
         }
      }
      return $valueFromDef;
   }

   /**
    * get result comparison def value and pc value
    *
    * @static
    * @param $valueFromDef
    * @param $pcID
    * @return mixed
    */
   static function getComputeResultByDef($valueFromDef, $pcID) {
      global $DB;

      $dbu = new DbUtils();

      foreach ($valueFromDef as $itemtype=>$allcrit) {
         if (!empty($allcrit)) {
            $item = new $itemtype();

            foreach ($allcrit as $key1=>$allDef) {
               if (!empty($allDef)) {

                  foreach ($allDef as $key2=>$def) {
                     if (!empty($def)) {

                        $test= explode(";", $def["field"]);
                        $itemField = $test[0];
                        $itemTable = $test[1];
                        $itemDataType = $test[2];

                        if ($itemField == 'count') {

                           $searchOption['table'] = $itemTable;

                        } else if ($itemField == 'softwareversions_id') {

                           $searchOption['table'] = $itemTable;
                           $searchOption['field'] = 'id';
                           $searchOption['name'] = __('Name')." - "._n('Version', 'Versions', 2);;

                        } else {
                           $searchOption = $item->getSearchOptionByField('field', $itemField);

                           if (empty($searchOption)) {
                              $crit = $dbu->getItemForItemtype($dbu->getItemTypeForTable($itemTable));
                              if ($crit instanceof CommonTreeDropdown) {
                                 $searchOption = $item->getSearchOptionByField('field', 'completename', $itemTable);
                              } else {
                                 $searchOption = $item->getSearchOptionByField('field', 'name', $itemTable);

                              }
                           }
                        }
                        //1-SELECT
                        $queryConsole = "SELECT COUNT(*) AS COUNT ";

                        // 2 - FROM
                        switch ($itemtype) {
                           case "Computer":
                              $queryConsole .= " FROM `glpi_computers`";
                              if ($searchOption['table'] != 'glpi_computers') {
                                 $queryConsole .= " INNER JOIN `" . $searchOption['table'] . "`";
                                 $fk = $dbu->getForeignKeyFieldForTable($searchOption['table']);
                                 $queryConsole .= " ON (`glpi_computers`.`" . $fk .
                                                  "`= `" . $searchOption['table'] . "`.`id`)";
                              }
                              $queryConsole .= " WHERE `glpi_computers`.`id` = '$pcID'";
                              break;
                           case "Monitor":
                           case "Peripheral":
                           case "Printer":
                              $queryConsole .= " FROM `glpi_computers_items`";
                              $fk        = $dbu->getForeignKeyFieldForTable($searchOption['table']);
                              if (strstr($searchOption['table'], 'types')) {
                                 $table = str_replace('types', 's', $searchOption['table']);
                                 $queryConsole.= " INNER JOIN `".$table."`";
                                 $queryConsole.= " ON (`glpi_computers_items`.`items_id` = `".$dbu->getTableForItemType($itemtype)."`.`id`)";
                                 $queryConsole.= " INNER JOIN `".$searchOption['table']."`";
                                 $queryConsole.= " ON (`".$table."`.`".$fk."` = `".$searchOption['table']."`.`id`)";
                              } else if ($searchOption['table']=='glpi_networks') {
                                 $table = $dbu->getTableForItemType($itemtype);
                                 $queryConsole.= " INNER JOIN `".$table."`";
                                 $queryConsole.= " ON (`glpi_computers_items`.`items_id` = `".$dbu->getTableForItemType($itemtype)."`.`id`)";
                                 $queryConsole.= " INNER JOIN `".$searchOption['table']."`";
                                 $queryConsole.= " ON (`".$table."`.`".$fk."` = `".$searchOption['table']."`.`id`)";

                              } else {
                                 $queryConsole .= " INNER JOIN `".$searchOption['table']."`";
                                 $queryConsole .= " ON (`glpi_computers_items`.`items_id` = `".$dbu->getTableForItemType($itemtype)."`.`id`)";
                              }
                              $queryConsole .= " WHERE `glpi_computers_items`.`itemtype` = '".$itemtype.
                                               "' AND `glpi_computers_items`.`computers_id` = '$pcID'";
                              break;
                           case "Software":
                              $queryConsole .= " FROM `glpi_items_softwareversions`";
                              $queryConsole .= " LEFT JOIN `glpi_softwareversions` 
                                                ON (`glpi_items_softwareversions`.`softwareversions_id` = `glpi_softwareversions`.`id`)";
                              $queryConsole .= " LEFT JOIN `glpi_softwares` 
                                                ON (`glpi_softwareversions`.`softwares_id` = `glpi_softwares`.`id`)";
                              $queryConsole .= " LEFT JOIN `glpi_softwarecategories` 
                                                ON (`glpi_softwares`.`softwarecategories_id` = `glpi_softwarecategories`.`id`)";
                              $queryConsole .= " WHERE `glpi_items_softwareversions`.`itemtype` ='Computer' 
                              AND `glpi_items_softwareversions`.`items_id` ='$pcID'";
                              break;
                           //                           case "NetworkPort":
                           //                              $queryConsole .= " FROM `glpi_networkports`";
                           //                              $queryConsole .= " WHERE `glpi_networkports`.`itemtype` = 'Computer'".
                           //                                               " AND `glpi_networkports`.`items_id` = '$pcID'";
                           //                              break;
                           case "IPAddress":
                              $queryConsole .= " FROM `glpi_networkports`";
                              $queryConsole .= " LEFT JOIN `glpi_networknames`".
                                 " ON (`glpi_networkports`.`id` = `glpi_networknames`.`items_id`".
                                 " AND `glpi_networknames`.`itemtype`='NetworkPort')";
                              $queryConsole .= " LEFT JOIN `glpi_ipaddresses`".
                                 " ON (`glpi_networknames`.`id` = `glpi_ipaddresses`.`items_id`".
                                 " AND `glpi_ipaddresses`.`itemtype`='NetworkName')";
                              $queryConsole .= " WHERE `glpi_networkports`.`itemtype` = 'Computer'".
                                 " AND `glpi_networkports`.`items_id` = '$pcID'";
                              break;

                           case "DeviceProcessor":
                           case "DeviceMemory":
                           case "DeviceHardDrive":
                              if ($itemField == 'count') {
                                 $queryConsole .= " FROM `".$searchOption['table']."`";
                                 $queryConsole .= " WHERE `items_id` = '$pcID' AND `itemtype` = 'Computer'";

                              } else {
                                 $linktable = $dbu->getTableForItemType('items_'.$itemtype);
                                 $fk        = $dbu->getForeignKeyFieldForTable($dbu->getTableForItemType($itemtype));

                                 $queryConsole .= " FROM `".$linktable."`";
                                 $queryConsole .= " INNER JOIN `".$searchOption['table']."`";
                                 $queryConsole .= " ON (`".$linktable."`.`".$fk."` = `".$searchOption['table']."`.`id`)";
                                 $queryConsole .= " WHERE `".$linktable."`.`items_id` = '$pcID'".
                                                  " AND `".$linktable."`.`itemtype` = 'Computer'";
                              }
                              break;
                        }

                        // 3 - WHERE comparison to typology criteria
                        switch ($itemDataType) {
                           case "bool" :
                              if ($def['action_type'] == 'equals') {
                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                    " = '".$def["value"]."'";
                              } else if ($def['action_type'] == 'notequals') {
                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                    " = '".$def["value"]."'";
                              }
                              break;
                           case "number" :
                              if ($itemField == 'count') {
                                 $queryConsole .=" Having COUNT ";
                                 if ($def['action_type'] == 'equals') {
                                    $queryConsole.= " = ";
                                 } else if ($def['action_type'] == 'notequals') {
                                    $queryConsole.= " = ";
                                 } else if ($def['action_type'] == 'lessthan') {
                                    $queryConsole.= " <= ";
                                 } else if ($def['action_type'] == 'morethan') {
                                    $queryConsole.= " >= ";
                                 }
                                 $queryConsole .= $def["value"];
                              } else {
                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`";
                                 if ($def['action_type'] == 'equals') {
                                    $queryConsole.= " = ";
                                 } else if ($def['action_type'] == 'notequals') {
                                    $queryConsole.= " = ";
                                 } else if ($def['action_type'] == 'lessthan') {
                                    $queryConsole.= " <= ";
                                 } else if ($def['action_type'] == 'morethan') {
                                    $queryConsole.= " >= ";
                                 }
                                 $queryConsole.= $def["value"];
                              }
                              break;
                           case "text" :
                              $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`";
                              if ($def['action_type'] == 'equals') {
                                 $queryConsole.= " = ";
                              } else if ($def['action_type'] == 'notequals') {
                                 $queryConsole.= " = ";
                              } else if ($def['action_type'] == 'lessthan') {
                                 $queryConsole.= " <= ";
                              } else if ($def['action_type'] == 'morethan') {
                                 $queryConsole.= " >= ";
                              }
                              $queryConsole.= $def["value"];
                              break;
                           //                           case "ip" :
                           //                              if ($def['action_type'] == 'contains') {
                           //                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                           //                                          " LIKE '%".$def["value"]."%'";
                           //                              } else if ($def['action_type'] == 'notcontains') {
                           //                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                           //                                    " NOT LIKE '%".$def["value"]."%'";
                           //                              } else if ($def['action_type'] == 'equals') {
                           //                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                           //                                          " = '".$def["value"]."'";
                           //                              } else if ($def['action_type'] == 'notequals') {
                           //                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                           //                                    " <> '".$def["value"]."'";
                           //                              } else if ($def['action_type'] == 'regex_match') {
                           //                                 $def["value"]=trim($def["value"],'/');
                           //                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                           //                                          " REGEXP '".$def["value"]."'";
                           //                              } else if ($def['action_type'] == 'regex_not_match') {
                           //                                 $def["value"]=trim($def["value"],'/');
                           //                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                           //                                          " NOT REGEXP '".$def["value"]."'";
                           //                              }
                           //                              break;
                           case "string" :
                              if ($def['action_type'] == 'contains') {
                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                    " LIKE '%".Toolbox::addslashes_deep($def["value"])."%'";
                              } else if ($def['action_type'] == 'notcontains') {
                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                    " LIKE '%".$def["value"]."%'";
                              } else if ($def['action_type'] == 'equals') {
                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                    " = '".$def["value"]."'";
                              } else if ($def['action_type'] == 'notequals') {
                                 $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                    " = '".$def["value"]."'";
                              }
                              break;
                           default :
                              $itemTest = $dbu->getItemForItemtype($dbu->getItemTypeForTable($itemTable));
                              switch ($itemTable) {
                                 case "glpi_users":
                                    if ($def['action_type'] == 'contains') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                          " LIKE '%".Toolbox::addslashes_deep($def["value"])."%'";
                                    } else if ($def['action_type'] == 'notcontains') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                          " LIKE '%".Toolbox::addslashes_deep($def["value"])."%'";
                                    } else if ($def['action_type'] == 'equals') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                          " = '".$dbu->getUserName($def["value"])."'";
                                    } else if ($def['action_type'] == 'notequals') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                          " = '".$dbu->getUserName($def["value"])."'";
                                    }
                                    break;
                                 case "glpi_softwareversions":
                                    if ($def['action_type'] == 'equals') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                          " = '".$def["value"]."'";
                                    } else if ($def['action_type'] == 'notequals') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                          " = '".$def["value"]."'";
                                    }
                                    break;
                                 default :
                                    if ($def['action_type'] == 'contains' || $def['action_type'] == 'notcontains') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                          " LIKE '%".Toolbox::addslashes_deep($def["value"])."%'";
                                    } else if ($def['action_type'] == 'equals') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`";
                                       if ($item instanceof CommonDevice) {
                                          $item->getFromDB($def["value"]);
                                          $queryConsole.= " = '".Toolbox::addslashes_deep($item->getName())."'";
                                       } else {
                                          $queryConsole.=" = '".Toolbox::addslashes_deep(Dropdown::getDropdownName($searchOption['table'], $def["value"]))."'";
                                       }
                                    } else if ($def['action_type'] == 'notequals') {
                                       $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`";
                                       if ($item instanceof CommonDevice) {
                                          $item->getFromDB($def["value"]);
                                          $queryConsole.= " = '".$item->getName()."'";
                                       } else {
                                          $queryConsole.=" = '".Dropdown::getDropdownName($searchOption['table'], $def["value"])."'";
                                       }
                                    } else if ($itemTest instanceof CommonTreeDropdown) {
                                       if ($def['action_type'] == 'under') {
                                          $sons = $dbu->getSonsOf($itemTable, $def["value"]);
                                          $queryConsole.= " AND `".$searchOption['table']."`.`id` IN ('".implode("','", $sons)."')";
                                       } else if ($def['action_type'] == 'notunder') {
                                          $sons = $dbu->getSonsOf($itemTable, $def["value"]);
                                          $queryConsole.= " AND `".$searchOption['table']."`.`id` NOT IN ('".implode("','", $sons)."')";
                                       }
                                    } else if ($itemTable == 'glpi_ipaddresses') {// REGEX matches of ip addresses
                                       if ($def['action_type'] == 'regex_match') {
                                          $def["value"]=trim($def["value"], '/');
                                          $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                             " REGEXP '".$def["value"]."'";
                                       } else if ($def['action_type'] == 'regex_not_match') {
                                          $def["value"]=trim($def["value"], '/');
                                          $queryConsole .= " AND `".$searchOption['table']."`.`".$searchOption['field']."`".
                                             " NOT REGEXP '".$def["value"]."'";
                                       }
                                    }
                                    break;
                              }
                              break;
                        }

                        $nbConsole = $DB->fetchArray($DB->query($queryConsole));
                        if ($nbConsole['COUNT'] > 0) {
                           if ($def['action_type'] == 'notequals' || $def['action_type'] == 'notcontains') {
                              $valueFromDef[$itemtype][$key1][$key2]["result"]='not_ok';
                           } else {
                              $valueFromDef[$itemtype][$key1][$key2]["result"]='ok';
                           }
                        } else {
                           if ($def['action_type'] == 'notequals' || $def['action_type'] == 'notcontains') {
                              $valueFromDef[$itemtype][$key1][$key2]["result"]='ok';
                           } else {
                              $valueFromDef[$itemtype][$key1][$key2]["result"]='not_ok';
                           }
                        }
                     }
                  }
               }
            }
         }
      }
      return $valueFromDef;
   }

    /**
    * get global result value vs computer by itemtype
    *
    * @static
    * @param $valueFromDef
    * @return mixed
    */
   static function getComputeResultByCriteria($valueFromDef) {

      foreach ($valueFromDef as $itemtype=>$allcrit) {
         if (!empty($allcrit)) {
            foreach ($allcrit as $key1=>$allDef) {// in all criteria
               if (!empty($allDef)) {
                  foreach ($allDef as $key2=>$def) {// checking all definitions
                     if ($def['action_type'] == 'notequals'
                           || $def['action_type'] == 'notcontains') {// EXEPTION
                        if ($def['link'] == 0) {// If link == 0, AND operator
                           if ($def['realvalue'][0] > 0) {
                              $ItemNotOkAND[$itemtype][$key1][] = 1;
                           } else {
                              $ItemNotOkAND[$itemtype][$key1][] = 0;
                           }
                        } else {// If link != 0, OR operator
                           if ($def['realvalue'][0] > 0) {
                              $ItemOkOR[$itemtype][$key1][] = 0;
                           } else {
                              $ItemOkOR[$itemtype][$key1][] = 1;
                           }
                        }
                     } else {// Normal action
                        if ($def['link'] == 0) {// If link == 0, AND operator
                           if ($def['realvalue'][0] == 0) {
                              $ItemNotOkAND[$itemtype][$key1][] = 1;
                           } else {
                              $ItemNotOkAND[$itemtype][$key1][] = 0;
                           }
                        } else {// If link != 0, OR operator
                           if ($def['realvalue'][0] == 0) {
                              $ItemOkOR[$itemtype][$key1][] = 0;
                           } else {
                              $ItemOkOR[$itemtype][$key1][] = 1;
                           }
                        }
                     }
                  }
                  // Results between the operator OR, AND
                  if (isset($ItemNotOkAND[$itemtype][$key1])) {// AND
                     if (in_array(1, $ItemNotOkAND[$itemtype][$key1])) {
                        $valueFromDef[$itemtype][$key1]["result"]='not_ok';
                     } else {
                        $valueFromDef[$itemtype][$key1]["result"]='ok';
                     }
                  } else if (isset($ItemOkOR[$itemtype][$key1])) {// OR
                     if (in_array(1, $ItemOkOR[$itemtype][$key1])) {
                        $valueFromDef[$itemtype][$key1]["result"]='ok';
                     } else {
                        $valueFromDef[$itemtype][$key1]["result"]='not_ok';
                     }
                  }
               }
            }
         }
      }
      return $valueFromDef;
   }
}
