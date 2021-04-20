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

/**
 * Class PluginResourcesRuleChecklistCollection
 */
class PluginResourcesRuleChecklistCollection extends RuleCollection {

   static $rightname = 'plugin_resources';

   // From RuleCollection
   //public $use_output_rule_process_as_next_input=true;
   public $menu_option='checklists';

   /**
    * Get title used in list of rules
    *
    * @return Title of the rule collection
    **/
   function getTitle() {
      return __('Assignment rules of a checklist to a contract type', 'resources');
   }

   /**
    * PluginResourcesRuleChecklistCollection constructor.
    *
    * @param int $entity
    */
   function __construct($entity = 0) {
      $this->entity = $entity;
   }

   /**
    * @return bool
    */
   function showInheritedTab() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]) && ($this->entity);
   }

   /**
    * @return bool
    */
   function showChildrensTab() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]) && (count($_SESSION['glpiactiveentities']) > 1);
   }

   /**
    * Process all the rules collection
    *
    * @param input the input data used to check criterias
    * @param output the initial ouput array used to be manipulate by actions
    * @param params parameters for all internal functions
    *
    * @return the output array updated by actions
   **/
   function processAllRules($input = [], $output = [], $params = [],
                            $force_no_cache = false) {

      // Get Collection datas
      $this->getCollectionDatas(1, 1);
      $input = $this->prepareInputDataForProcess($input, $params);
      $output["_no_rule_matches"] = true;
      $checklists = [];

      if (count($this->RuleList->list)) {
         foreach ($this->RuleList->list as $rule) {
            //If the rule is active, process it

            if ($rule->fields["is_active"]) {
               $output["_rule_process"] = false;
               $rule->process($input, $output, $params);

               if ($output["_rule_process"]==1) {
                  $checklists[]=$output["checklists_id"];
               }
            }

            if ($this->use_output_rule_process_as_next_input) {
               $input = $output;
            }
         }
      }

      return $checklists;
   }

   /**
    * Show test results for a rule
    *
    * @param $rule                     rule object
    * @param $output          array    output data array
    * @param $global_result   boolean  global result
    *
    * @return cleaned array
    **/
   function showTestResults($rule, array $output, $global_result) {

      $actions = $rule->getActions();

   }
}

