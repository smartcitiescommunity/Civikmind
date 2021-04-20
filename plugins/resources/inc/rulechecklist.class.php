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
* Rule class store all informations about a GLPI rule :
*   - description
*   - criterias
*   - actions
*
**/
class PluginResourcesRuleChecklist extends Rule {

   static $rightname = 'plugin_resources';

   // From Rule
   public $can_sort=true;

   /**
    * Get title used in rule
    *
    * @return Title of the rule
    **/
   function getTitle() {

      return PluginResourcesResource::getTypeName(2)." ".PluginResourcesChecklist::getTypeName(1);
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
    * @return bool
    */
   function maybeRecursive() {
      return true;
   }


   /**
    * @return bool
    */
   function isEntityAssign() {
      return true;
   }

   /**
    * Can I change recursive flag to false
    * check if there is "linked" object in another entity
    *
    * May be overloaded if needed
    *
    * @return booleen
    **/
   function canUnrecurs() {
      return true;
   }

   /**
    * Get maximum number of Actions of the Rule (0 = unlimited)
    *
    * @return the maximum number of actions
    **/
   function maxActionsCount() {
      return count($this->getActions());
   }

   /**
    * Function used to add specific params before rule processing
    *
    * @param $params parameters
    **/
   function addSpecificParamsForPreview($params) {

      if (!isset($params["entities_id"])) {
         $params["entities_id"] = $_SESSION["glpiactive_entity"];
      }
      return $params;
   }

   /**
    * @return array
    */
   function getCriterias() {

      $criterias = [];

      $criterias['plugin_resources_contracttypes_id']['name']  = PluginResourcesContractType::getTypeName(1);
      $criterias['plugin_resources_contracttypes_id']['type']  = 'dropdownContractType';

      $criterias['plugin_resources_contracttypes_id']['allow_condition'] = [Rule::PATTERN_IS, Rule::PATTERN_IS_NOT];

      $criterias['checklist_type']['name']  = __('Checklist type', 'resources');
      $criterias['checklist_type']['type']  = 'dropdownChecklistType';

      $criterias['checklist_type']['allow_condition'] = [Rule::PATTERN_IS, Rule::PATTERN_IS_NOT];

      return $criterias;
   }

   /**
    * Display item used to select a pattern for a criteria
    *
    * @param $name      criteria name
    * @param $ID        the given criteria
    * @param $condition condition used
    * @param $value     the pattern (default '')
    * @param $test      Is to test rule ? (false by default)
    **/
   function displayCriteriaSelectPattern($name, $ID, $condition, $value = "", $test = false) {

      $PluginResourcesChecklist = new PluginResourcesChecklist();
      $PluginResourcesContractType = new PluginResourcesContractType();

      $crit    = $this->getCriteria($ID);
      $display = false;
      if (isset($crit['type'])
          && ($test||$condition==Rule::PATTERN_IS || $condition==Rule::PATTERN_IS_NOT)) {

         switch ($crit['type']) {
            case "dropdownChecklistType" :
               $PluginResourcesChecklist->dropdownChecklistType($name);
               $display = true;
               break;
            case "dropdownContractType" :
               $PluginResourcesContractType->dropdownContractType($name);
               $display = true;
               break;
         }
      }

      if ($condition == Rule::PATTERN_EXISTS || $condition == Rule::PATTERN_DOES_NOT_EXISTS) {
         echo "<input type='hidden' name='$name' value='1'>";
         $display=true;
      }

      if (!$display) {
         $rc = new $this->rulecriteriaclass();
         Html::autocompletionTextField($rc, "pattern", ['name'  => $name,
                                                       'value' => $value,
                                                       'size'  => 70]);
      }
   }

   /**
    * Return a value associated with a pattern associated to a criteria to display it
    *
    * @param $ID the given criteria
    * @param $condition condition used
    * @param $pattern the pattern
   **/
   function getCriteriaDisplayPattern($ID, $condition, $pattern) {

      if (($condition==Rule::PATTERN_IS || $condition==Rule::PATTERN_IS_NOT)) {
         $crit = $this->getCriteria($ID);
         if (isset($crit['type'])) {

            switch ($crit['type']) {
               case "dropdownChecklistType" :
                  $PluginResourcesChecklist = new PluginResourcesChecklist();
                  return $PluginResourcesChecklist->getChecklistType($pattern);
               case "dropdownContractType" :
                  $PluginResourcesContractType = new PluginResourcesContractType();
                  return $PluginResourcesContractType->getContractTypeName($pattern);
            }
         }
      }
      return $pattern;
   }

   /**
    * @return array
    */
   function getActions() {

      $actions = [];

      $actions['checklists_id']['name']  = __('Checklist action', 'resources');
      $actions['checklists_id']['table'] = 'glpi_plugin_resources_checklistconfigs';
      $actions['checklists_id']['type'] = 'dropdown';
      $actions['checklists_id']['force_actions'] = ['assign'];

      return $actions;
   }
}

