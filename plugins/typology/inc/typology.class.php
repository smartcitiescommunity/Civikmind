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
 * Class PluginTypologyTypology
 */
class PluginTypologyTypology extends CommonDBTM {

   // From CommonDBTM
   var              $dohistory         = true;
   static           $rightname         = "plugin_typology";
   protected        $usenotepad        = true;
   protected        $usenotepadrights  = true;
   protected static $forward_entity_to = ['PluginTypologyTypologyCriteria'];

   static $types = ['Computer'];

   static $types_criteria = [
      'Computer',
      'Monitor',
      'Software',
      'Peripheral',
      'Printer',
      'IPAddress'
      //      'NetworkPort'
      /*'Phone'*/];

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   public static function getTypeName($nb = 0) {

      return _n('Typology', 'Typologies', $nb, 'typology');
   }

   /**
    * Display tab for each typology
    * */
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginTypologyTypologyCriteria', $ong, $options);
      $this->addStandardTab('PluginTypologyTypology_Item', $ong, $options);
      $this->addStandardTab('Document', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }

   /**
    * Actions done when a typo is deleted from the database
    *
    * @return nothing
    **/
   function cleanDBonPurge() {

      //Clean typology_item
      $temp1 = new PluginTypologyTypology_Item();
      $temp1->deleteByCriteria(['plugin_typology_typologies_id' => $this->fields['id']]);

      //Clean typologycriteria
      $temp2 = new PluginTypologyTypologyCriteria();
      $temp2->deleteByCriteria(['plugin_typology_typologies_id' => $this->fields['id']]);

      //Clean rule
      Rule::cleanForItemAction($this);
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
    **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }

   /**
    * Type than could be linked to a typo
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    **/
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;
      $dbu = new DbUtils();
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
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
    **/
   static function registerTypeCriteria($typeCriteria) {
      if (!in_array($typeCriteria, self::getTypesCriteria())) {
         self::$types_criteria[] = $typeCriteria;
      }
   }

   /**
    * Type than could be used as a criteria for a typo
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    **/
   static function getTypesCriteria() {

      // Only allowed types
      $types_criteria = self::$types_criteria;
      $devtypes       = self::getComputerDeviceTypes();
      $dbu = new DbUtils();
      foreach ($types_criteria as $key => $type_criteria) {
         if (!($item = $dbu->getItemForItemtype($type_criteria))) {
            continue;
         }

         //         if (!$item->canView()) {
         //            unset($types_criteria[$key]);
         //         }
      }

      foreach ($devtypes as $itemtype) {
         $device = new $itemtype();
         if ($device->can(-1, 'r')) {
            $types_criteria[] = $itemtype;
         }
      }

      return $types_criteria;
   }

   /**
    * Display the typology form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return boolean item found
    **/
   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name", ['value' => $this->fields["name"]]);
      echo "</td>";
      echo "<td rowspan=2>" . __('Comments') . "</td>";
      echo "<td rowspan=2>";
      echo "<textarea cols='45' rows='8' name='comment' >" . $this->fields["comment"] . "</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";

      if (!$ID) {
         echo "<td>" . __('Last update') . "</td>";
         echo "<td>";
         echo Html::convDateTime($_SESSION["glpi_currenttime"]);

      } else {
         echo "<td>" . __('Last update') . "</td>";
         echo "<td>" . ($this->fields["date_mod"] ? Html::convDateTime($this->fields["date_mod"])
               : __('Never'));
      }

      echo "</td></tr>";
      echo "<input type='hidden' name='entities_id' value='" . $_SESSION['glpiactive_entity'] . "'>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * Provides search options configuration. Do not rely directly
    * on this, @see CommonDBTM::searchOptions instead.
    *
    * @since 9.3
    *
    * This should be overloaded in Class
    *
    * @return array a *not indexed* array of search options
    *
    * @see https://glpi-developer-documentation.rtfd.io/en/master/devapi/search.html
    **/
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(1)
      ];
      $tab[] = [
         'id'            => 1,
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType(),
         'massiveaction' => false
      ];

      $tab[] = [
         'id'            => 2,
         'table'         => $this->getTable(),
         'field'         => 'id',
         'name'          => __('ID'),
         'massiveaction' => false,
         'datatype'      => 'number'
      ];
      $tab[] = [
         'id'            => 14,
         'table'         => $this->getTable(),
         'field'         => 'date_mod',
         'name'          => __('Last update'),
         'massiveaction' => false,
         'datatype'      => 'datetime'
      ];
      $tab[] = [
         'id'            => 16,
         'table'         => $this->getTable(),
         'field'         => 'comment',
         'name'          => __('Comments'),
         'datatype'      => 'text',
         'massiveaction' => true
      ];
      $tab[] = [
         'id'            => 80,
         'table'         => 'glpi_entities',
         'field'         => 'completename',
         'name'          => __('Entity'),
         'massiveaction' => false,
         'datatype'      => 'dropdown'
      ];
      $tab[] = [
         'id'            => 86,
         'table'         => $this->getTable(),
         'field'         => 'is_recursive',
         'name'          => __('Child entities'),
         'datatype'      => 'bool',
         'massiveaction' => true
      ];

      return $tab;
   }


   /**
    * @return array
    */
   static function getComputerDeviceTypes() {
      return [/*1 => 'DeviceMotherboard', */
              2 => 'DeviceProcessor', 3 => 'DeviceMemory',
              4 => 'DeviceHardDrive'/*,   5 => 'DeviceNetworkCard', 6 => 'DeviceDrive',
                   7 => 'DeviceControl',     8 => 'DeviceGraphicCard', 9 => 'DeviceSoundCard',
                   10 => 'DevicePci',        11 => 'DeviceCase',       12 => 'DevicePowerSupply'*/];
   }

   ////// CRON FUNCTIONS ///////
   //Cron action
   /**
    * @param $name
    *
    * @return array
    */
   static function cronInfo($name) {

      switch ($name) {
         case 'UpdateTypology':
            return [
               'description' => __('Recalculate typology for the elements', 'typology')];   // Optional
            break;
         case 'NotValidated':
            return [
               'description' => __('Elements not match with the typology', 'typology')];   // Optional
            break;
      }
      return [];
   }

   /**
    * @return string
    */
   function queryUpdateTypology() {

      $query = "SELECT *
            FROM `glpi_plugin_typology_typologies_items`";

      return $query;

   }

   /**
    * @return string
    */
   function queryNotValidated() {

      $query = "SELECT `glpi_plugin_typology_typologies_items`.*,
                        `glpi_plugin_typology_typologies`.`name`,
                        `glpi_plugin_typology_typologies`.`entities_id`
            FROM `glpi_plugin_typology_typologies_items`
            LEFT JOIN `glpi_plugin_typology_typologies`
            ON (`glpi_plugin_typology_typologies_items`.`plugin_typology_typologies_id` = `glpi_plugin_typology_typologies`.`id`)
            WHERE `glpi_plugin_typology_typologies_items`.`is_validated` = 0
            ORDER BY `glpi_plugin_typology_typologies`.`name`";

      return $query;

   }


   /**
    * Cron action on tasks : UpdateTypology
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronUpdateTypology($task = null) {
      global $DB;

      $cron_status = 0;
      $message     = [];

      $typo        = new self();
      $query_items = $typo->queryUpdateTypology();

      $querys = [Alert::END => $query_items];

      $task_infos    = [];
      $task_messages = [];

      foreach ($querys as $type => $query) {
         $task_infos[$type] = [];
         foreach ($DB->request($query) as $data) {

            //update all linked item to a typology
            if (isset($data['id'])) {
               $input = PluginTypologyTypology_Item::checkValidated($data);
            }

            if ($data['error'] != $input['error']) {
               $typo_item = new PluginTypologyTypology_Item();
               $typo_item->getFromDB($data['id']);

               $typo_item->update($input);
               $typo->getFromDB($data['plugin_typology_typologies_id']);
               $entity = $typo->fields['entities_id'];
               if (!isset($message[$entity])) {
                  $message = [$entity => ''];
               }
               $task_infos[$type][$entity][] = $data;
               if (!isset($task_messages[$type][$entity])) {
                  $task_messages[$type][$entity] = __('Typology of the linked elements is updated.', 'typology') . "<br />";
               }
               $task_messages[$type][$entity] .= $message[$entity];
            }
         }
      }

      foreach ($querys as $type => $query) {

         foreach ($task_infos[$type] as $entity => $items) {
            Plugin::loadLang('typology');

            $message     = $task_messages[$type][$entity];
            $cron_status = 1;
            if ($task) {
               $task->log(Dropdown::getDropdownName("glpi_entities",
                                                    $entity) . ":  $message\n");
               $task->addVolume(count($items));
            } else {
               Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                          $entity) . ":  $message");
            }
         }
      }

      return $cron_status;
   }

   /**
    * Cron action on tasks : UpdateTypology
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronNotValidated($task = null) {
      global $DB, $CFG_GLPI;

      if (!$CFG_GLPI["notifications_mailing"]) {
         return 0;
      }

      $cron_status = 0;
      $message     = [];

      $typo        = new self();
      $query_items = $typo->queryNotValidated();

      $querys = [Alert::END => $query_items];

      $task_infos    = [];
      $task_messages = [];
      $dbu = new DbUtils();

      foreach ($querys as $type => $query) {
         $task_infos[$type] = [];
         foreach ($DB->request($query) as $data) {

            // Get items entity
            $item = $dbu->getItemForItemtype($data['itemtype']);
            $item->getFromDB($data['items_id']);

            if (!isset($item->fields['is_deleted'])
                || !$item->fields['is_deleted']) {
               if (isset($item->fields['entities_id'])) {
                  $entity = $item->fields['entities_id'];

                  $message                      = $data["name"] . ": " .
                                                  $data["error"] . "<br>\n";
                  $task_infos[$type][$entity][] = $data;

                  if (!isset($tasks_infos[$type][$entity])) {
                     $task_messages[$type][$entity] = __('Elements not match with the typology', 'typology') . "<br />";
                  }
                  $task_messages[$type][$entity] .= $message;
               }
            }
         }
      }

      foreach ($querys as $type => $query) {

         foreach ($task_infos[$type] as $entity => $items) {
            Plugin::loadLang('typology');

            $message     = $task_messages[$type][$entity];
            $cron_status = 1;

            if (NotificationEvent::raiseEvent("AlertNotValidatedTypology",
                                              new PluginTypologyTypology(),
                                              ['entities_id' => $entity,
                                               'items'       => $items])) {
               $message     = $task_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                                                       $entity) . ":  $message\n");
                  $task->addVolume(1);
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                             $entity) . ":  $message");
               }

            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                                                       $entity) . ":  $message\n");
                  $task->addVolume(count($items));
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                             $entity) . ":  $message");
               }
            }
         }
      }

      return $cron_status;
   }

   /**
    * Get the specific massive actions
    *
    * @since version 0.84
    *
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    **/
   /**
    * Get the specific massive actions
    *
    * @since version 0.84
    *
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    **/
   public function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if (Session::getCurrentInterface() == 'central') {
         if ($isadmin) {
            $actions['PluginTypologyTypology' . MassiveAction::CLASS_ACTION_SEPARATOR . 'duplicate'] = _sx('button', 'Duplicate');

            if (Session::haveRight('transfer', READ)
                && Session::isMultiEntitiesMode()) {
               $actions['PluginTypologyTypology' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __('Transfer');
            }
         }
      }
      return $actions;
   }

   /**
    * Class-specific method used to show the fields to specify the massive action
    *
    * @since 0.85
    *
    * @param MassiveAction $ma the current massive action object
    *
    * @return boolean false if parameters displayed ?
    **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "duplicate" :
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $criteria   = new PluginTypologyTypologyCriteria();
      $definition = new PluginTypologyTypologyCriteriaDefinition();
      $dbu        = new DbUtils();

      switch ($ma->getAction()) {

         case "transfer" :
            $input = $ma->getInput();
            if ($item->getType() == 'PluginTypologyTypology') {
               foreach ($ids as $key) {
                  $item->getFromDB($key);

                  $restrict = ["plugin_typology_typologies_id" => $key];
                  $crits    = $dbu->getAllDataFromTable("glpi_plugin_typology_typologycriterias",
                                                        $restrict);
                  if (!empty($crits)) {
                     foreach ($crits as $crit) {

                        $criteria->getFromDB($crit["id"]);

                        $condition = ["plugin_typology_typologycriterias_id" => $crit["id"]];
                        $defs      = $dbu->getAllDataFromTable("glpi_plugin_typology_typologycriteriadefinitions",
                                                               $condition);
                        if (!empty($defs)) {
                           foreach ($defs as $def) {

                              $definition->getFromDB($def["id"]);

                              unset($values);
                              $values["id"]          = $def["id"];
                              $values["entities_id"] = $input['entities_id'];
                              $definition->update($values);
                           }
                        }
                        unset($values);
                        $values["id"]          = $crit["id"];
                        $values["entities_id"] = $input['entities_id'];
                        $criteria->update($values);
                     }
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
            break;

         case 'duplicate':
            if ($item->getType() == 'PluginTypologyTypology') {
               foreach ($ids as $key) {

                  $item->getFromDB($key);

                  $restrict = ["plugin_typology_typologies_id" => $key];
                  $crits    = $dbu->getAllDataFromTable("glpi_plugin_typology_typologycriterias",
                                                        $restrict);

                  unset($item->fields["id"]);
                  $item->fields["name"]    = addslashes($item->fields["name"] . " Copy");
                  $item->fields["comment"] = addslashes($item->fields["comment"]);
                  //TODO duplicate notes
                  //                  $item->fields["notepad"] = addslashes($item->fields["notepad"]);
                  if (!$newIDtypo = $item->add($item->fields)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  }
                  if (!empty($crits)) {
                     foreach ($crits as $crit) {
                        $criteria->getFromDB($crit["id"]);

                        $condition = ["plugin_typology_typologycriterias_id" => $crit["id"]];
                        $defs      = $dbu->getAllDataFromTable("glpi_plugin_typology_typologycriteriadefinitions",
                                                               $condition);

                        unset($criteria->fields["id"]);
                        $criteria->fields["name"]                          = addslashes($criteria->fields["name"]);
                        $criteria->fields["plugin_typology_typologies_id"] = $newIDtypo;
                        $criteria->fields["itemtype"]                      = addslashes($criteria->fields["itemtype"]);
                        $newIDcrit                                         = $criteria->add($criteria->fields);

                        if (!empty($defs)) {
                           foreach ($defs as $def) {

                              $definition->getFromDB($def["id"]);

                              unset($definition->fields["id"]);
                              $definition->fields["plugin_typology_typologycriterias_id"] = $newIDcrit;
                              $definition->fields["field"]                                = addslashes($definition->fields["field"]);
                              $definition->fields["action_type"   ]                          = addslashes($definition->fields["action_type"]);
                              $definition->fields["value"]                                = addslashes($definition->fields["value"]);
                              $definition->add($definition->fields);

                           }
                        }
                     }
                  }
               }
            }
            break;
      }
   }
}
