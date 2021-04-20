<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesChecklistconfig
 */
class PluginResourcesChecklistconfig extends CommonDBTM {

   static $rightname = 'plugin_resources_checklist';

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {

      return _n('Checklist setup', 'Checklists setup', $nb, 'resources');
   }

   /**
    * Have I the global right to "view" the Object
    *
    * Default is true and check entity if the objet is entity assign
    *
    * May be overloaded if needed
    *
    * @return booleen
    **/
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * Clean object veryfing criteria (when a relation is deleted)
    *
    * @param $crit array of criteria (should be an index)
    */
   public function clean($crit) {
      global $DB;

      foreach ($DB->request($this->getTable(), $crit) as $data) {
         $this->delete($data);
      }
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

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '3',
         'table'    => $this->getTable(),
         'field'    => 'comment',
         'name'     => __('Description'),
         'datatype' => 'text'
      ];

      $tab[] = [
         'id'       => '4',
         'table'    => $this->getTable(),
         'field'    => 'tag',
         'name'     => __('Important', 'resources'),
         'datatype' => 'bool'
      ];

      $tab[] = [
         'id'            => '30',
         'table'         => $this->getTable(),
         'field'         => 'id',
         'name'          => __('ID'),
         'datatype'      => 'number',
         'massiveaction' => false
      ];

      return $tab;
   }

   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td >".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name", ['size' => "40"]);
      echo "</td>";

      echo "<td>";
      echo __('Important', 'resources')."</td><td>";
      Dropdown::showYesNo("tag", $this->fields["tag"]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td >".__('Link', 'resources')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "address", ['size' => "75"]);
      echo "</td>";

      echo "<td></td>";
      echo "<td></td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo __('Description')."</td></tr>";
      echo "<tr><td class='center'>";
      echo "<textarea cols='125' rows='6' name='comment'>".$this->fields["comment"];
      echo "</textarea>";
      echo "</td></tr></table>";
      echo "</td>";

      echo "</tr>";

//      $options['candel'] = false;
      $this->showFormButtons($options);
      return true;
   }

   /**
    * @param $resource
    * @param $checklists_id
    * @param $checklist_type
    */
   function addResourceChecklist($resource, $checklists_id, $checklist_type) {

      $restrict = ["id" => $checklists_id];
      $dbu = new DbUtils();
      $checklists = $dbu->getAllDataFromTable("glpi_plugin_resources_checklistconfigs", $restrict);

      if (!empty($checklists)) {

         foreach ($checklists as $checklist) {

            if (isset($resource->fields["plugin_resources_contracttypes_id"])) {
               unset($checklist["id"]);
               $checklist["plugin_resources_resources_id"]     = $resource->fields["id"];
               $checklist["plugin_resources_contracttypes_id"] = $resource->fields["plugin_resources_contracttypes_id"];
               $checklist["checklist_type"]                    = $checklist_type;
               $checklist["name"]                              = addslashes($checklist["name"]);
               $checklist["address"]                           = addslashes($checklist["address"]);
               $checklist["comment"]                           = addslashes($checklist["comment"]);
               $checklist["entities_id"]                       = $resource->fields["entities_id"];
               $resource_checklist                             = new PluginResourcesChecklist();
               $resource_checklist->add($checklist);
            }
         }
      }
   }

   /**
    * @param $resource
    * @param $checklist_type
    */
   function addChecklistsFromRules($resource, $checklist_type) {

      $rulecollection = new PluginResourcesRuleChecklistCollection($resource->fields["entities_id"]);

      if (isset($resource->fields["plugin_resources_contracttypes_id"]) &&
              $resource->fields["plugin_resources_contracttypes_id"] > 0) {
         $contract = $resource->fields["plugin_resources_contracttypes_id"];
      } else {
         $contract = 0;
      }

      $checklists = [];
      $checklists = $rulecollection->processAllRules(["plugin_resources_contracttypes_id" => $contract,
          "checklist_type"                    => $checklist_type], $checklists, []);

      if (!empty($checklists)) {

         foreach ($checklists as $key => $checklist) {
            $this->addResourceChecklist($resource, $checklist, $checklist_type);
         }
      }
   }

   /**
    * Create a rule for the checklist
    * @param type $data
    * @param type $ma
    * @param type $item
    */
   function addRulesFromChecklists($data, $ma, $item) {

      $rulecollection = new PluginResourcesRuleChecklistCollection();
      $rulecollection->checkGlobal(UPDATE);

      foreach ($ma->items["PluginResourcesChecklistconfig"] as $key => $val) {

         $this->getFromDB($key);
         $rule                   = new PluginResourcesRuleChecklist();
         $values["name"]         = addslashes($this->fields["name"]);
         $values["match"]        = "AND";
         $values["is_active"]    = 1;
         $values["is_recursive"] = 1;
         $values["entities_id"]  = $this->fields["entities_id"];
         $values["sub_type"]     = "PluginResourcesRuleChecklist";
         $newID                  = $rule->add($values);

         if (isset($data["checklist_type"]) && $data["checklist_type"] > 0) {
            $criteria            = new RuleCriteria();
            $values["rules_id"]  = $newID;
            $values["criteria"]  = "checklist_type";
            $values["condition"] = 0;
            $values["pattern"]   = $data["checklist_type"];
            $criteria->add($values);
         }

         if (isset($data["plugin_resources_contracttypes_id"])) {
            $criteria            = new RuleCriteria();
            $values["rules_id"]  = $newID;
            $values["criteria"]  = "plugin_resources_contracttypes_id";
            $values["condition"] = $data["condition"];
            $values["pattern"]   = $data["plugin_resources_contracttypes_id"];
            $criteria->add($values);
         }

         $action                = new RuleAction();
         $values["rules_id"]    = $newID;
         $values["action_type"] = "assign";
         $values["field"]       = "checklists_id";
         $values["value"]       = $key;
         $action->add($values);
         if ($newID) {
            $ma->itemDone($item->getType(), $newID, MassiveAction::ACTION_OK);
         } else {
            $ma->itemDone($item->getType(), $newID, MassiveAction::ACTION_KO);
         }
      }
   }

   /**
    * Get the specific massive actions
    *
    * @since version 0.84
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    * */
   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         $actions['PluginResourcesChecklistconfig'.MassiveAction::CLASS_ACTION_SEPARATOR.'Generate_Rule'] = __('Generate a rule', 'resources');

         if (Session::haveRight('transfer', READ)
                 && Session::isMultiEntitiesMode()) {
            $actions['PluginResourcesChecklistconfig'.MassiveAction::CLASS_ACTION_SEPARATOR.'Transfert'] = __('Transfer');
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

      $PluginResourcesChecklist    = new PluginResourcesChecklist();
      $PluginResourcesContractType = new PluginResourcesContractType();

      switch ($ma->getAction()) {
         case "Generate_Rule" :
            $PluginResourcesChecklist->dropdownChecklistType("checklist_type", $_SESSION["glpiactive_entity"]);
            echo "&nbsp;";
            RuleCriteria::dropdownConditions("PluginResourcesRuleChecklist", ['criterion'        => 'plugin_resources_contracttypes_id',
                                                                                   'allow_conditions' => [Rule::PATTERN_IS, Rule::PATTERN_IS_NOT]]);
            echo "&nbsp;";
            $PluginResourcesContractType->dropdownContractType("plugin_resources_contracttypes_id");
            echo "&nbsp;";
            break;

         case "Transfert" :
            Dropdown::show('Entity');
            break;
      }

      return parent::showMassiveActionsSubForm($ma);
   }
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    * */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {

      $input    = $ma->getInput();
      $itemtype = $ma->getItemtype(false);

      switch ($ma->getAction()) {
         case "Transfert" :
            if ($itemtype == 'PluginResourcesEmployment') {
               foreach ($ids as $key => $val) {
                  $values["id"] = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            break;
         case "Generate_Rule" :
            if ($itemtype == 'PluginResourcesChecklistconfig') {
               $item->addRulesFromChecklists($input, $ma, $item);
            }
            break;

         default :
            return parent::doSpecificMassiveActions($input);
      }
   }

}

