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
 * Class PluginResourcesResource_Change
 */
class PluginResourcesResource_Change extends CommonDBTM {

   static $rightname = 'plugin_resources';

   //List of possible actions
   CONST CHANGE_RESOURCEMANAGER = 1;
   CONST CHANGE_ACCESSPROFIL    = 2;
   CONST CHANGE_CONTRACTYPE     = 3;
   CONST CHANGE_AGENCY          = 4;
   CONST CHANGE_TRANSFER        = 5;
   CONST BADGE_RESTITUTION      = 6;
   CONST CHANGE_RESOURCESALE    = 7;

   /**
    * Returns all actions
    */
   static function getAllActions() {
      $actions                               = [];
      $actions[0]                            = self::getNameActions(0);
      $actions[self::CHANGE_RESOURCEMANAGER] = self::getNameActions(self::CHANGE_RESOURCEMANAGER);
      $actions[self::CHANGE_RESOURCESALE]    = self::getNameActions(self::CHANGE_RESOURCESALE);
      $actions[self::CHANGE_ACCESSPROFIL]    = self::getNameActions(self::CHANGE_ACCESSPROFIL);
      $actions[self::CHANGE_CONTRACTYPE]     = self::getNameActions(self::CHANGE_CONTRACTYPE);
      $actions[self::CHANGE_AGENCY]          = self::getNameActions(self::CHANGE_AGENCY);
      $transfer = new PluginResourcesTransferEntity();
      $dataEntity = $transfer->find();
      if (is_array($dataEntity) && count($dataEntity) > 0) {
         $actions[self::CHANGE_TRANSFER]        = self::getNameActions(self::CHANGE_TRANSFER);
      }

      return $actions;
   }

   /**
    * Returns the label of the action
    *
    * @param $actions_id
    *
    * @return \translated
    */
   static function getNameActions($actions_id) {
      switch ($actions_id) {
         case self::CHANGE_RESOURCEMANAGER :
            return __("Change manager", 'resources');
         case self::CHANGE_RESOURCESALE :
            return __("Change the sales manager", 'resources');
         case self::CHANGE_ACCESSPROFIL :
            return __("Change the access profil", 'resources');
         case self::CHANGE_CONTRACTYPE :
            return __("Change contract type", 'resources');
         case self::CHANGE_AGENCY :
            return __("Change of agency", 'resources');
         case self::CHANGE_TRANSFER :
            return __("Change direction (mutation)", 'resources');
         case self::BADGE_RESTITUTION :
            return __('Badge restitution', 'resources');
         default :
            return Dropdown::EMPTY_VALUE;
      }
   }

   /**
    * Form for each change
    *
    * @param $action_id
    * @param $plugin_resources_resources_id
    */
   static function setFieldByAction($action_id, $plugin_resources_resources_id) {
      global $CFG_GLPI, $DB;

      if ($plugin_resources_resources_id == 0) {
         echo "<span class='red'>" . __('Please select a resource', 'resources') . "</span>";
         return;
      }
      $resource = new PluginResourcesResource();
      $resource->getFromDB($plugin_resources_resources_id);

      $dbu = new DbUtils();

      //Display for each action
      switch ($action_id) {
         case self::CHANGE_RESOURCEMANAGER :

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __("Manager for the current resource", "resources");
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo "&nbsp;" . $dbu->getUserName($resource->getField('users_id'));
            echo "</div>";
            echo "</div>";

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __('New resource manager', 'resources');
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-4 \">";
            $rand = User::dropdown(['name'      => "users_id",
                                    'entity'    => $resource->fields["entities_id"],
                                    'right'     => 'all',
                                    'used'      => [$resource->getField('users_id')],
                                    'on_change' => 'plugin_resources_load_button_changeresources_manager()']);

            echo "<script type='text/javascript'>";
            echo "function plugin_resources_load_button_changeresources_manager(){";
            $params = ['load_button_changeresources' => true, 'action' => self::CHANGE_RESOURCEMANAGER, 'users_id' => '__VALUE__'];
            Ajax::updateItemJsCode('plugin_resources_buttonchangeresources', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php', $params, 'dropdown_users_id' . $rand);
            echo "}";
            echo "</script>";
            echo "</div>";
            echo "</div>";

            break;

         case self::CHANGE_RESOURCESALE :

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __("Sales manager for the current resource", "resources");
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo "&nbsp;" . $dbu->getUserName($resource->getField('users_id_sales'));
            echo "</div>";
            echo "</div>";

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __('New resource sales manager', 'resources');
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-4 \">";
            $rand = User::dropdown(['name'      => "users_id_sales",
                                    'entity'    => $resource->fields["entities_id"],
                                    'right'     => 'all',
                                    'used'      => [$resource->getField('users_id_sales')],
                                    'on_change' => 'plugin_resources_load_button_changeresources_sale()']);

            echo "<script type='text/javascript'>";
            echo "function plugin_resources_load_button_changeresources_sale(){";
            $params = ['load_button_changeresources' => true, 'action' => self::CHANGE_RESOURCESALE, 'users_id_sales' => '__VALUE__'];
            Ajax::updateItemJsCode('plugin_resources_buttonchangeresources', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php', $params, 'dropdown_users_id_sales' . $rand);
            echo "}";
            echo "</script>";
            echo "</div>";
            echo "</div>";

            break;

         case self::CHANGE_ACCESSPROFIL :

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __("Current access profile of the resource", "resources");
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            $query = "SELECT `glpi_plugin_resources_habilitations`.`id` 
                      FROM `glpi_plugin_resources_resourcehabilitations` 
                      LEFT JOIN `glpi_plugin_resources_habilitations` 
                      ON `glpi_plugin_resources_habilitations`.`id` = `glpi_plugin_resources_resourcehabilitations`.`plugin_resources_habilitations_id`
                      LEFT JOIN `glpi_plugin_resources_habilitationlevels` 
                      ON `glpi_plugin_resources_habilitationlevels`.`id` = `glpi_plugin_resources_habilitations`.`plugin_resources_habilitationlevels_id`
                      WHERE `plugin_resources_resources_id` = $plugin_resources_resources_id
                      AND `glpi_plugin_resources_habilitationlevels`.`is_mandatory_creating_resource` = 1";
            $used = [];
            foreach ($DB->request($query) as $data) {
               echo "&nbsp;" . Dropdown::getDropdownName('glpi_plugin_resources_habilitations', $data['id']) . "<br>";
               $used[] = $data['id'];
            }
            echo "</div>";
            echo "</div>";

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __('New access profile of the resource', 'resources');
            echo "</div>";

            //level
            $habilitationlevel = new PluginResourcesHabilitationLevel();
            $levels = $habilitationlevel->find(['is_mandatory_creating_resource' => 1]);
            $condition = [];
            foreach ($levels as $level) {
               $condition["plugin_resources_habilitationlevels_id"] = $level['id'];
            }

            echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-4 \">";
            $rand = PluginResourcesHabilitation::dropdown(['name'      => "plugin_resources_habilitations_id",
                                                           'entity'    => $resource->fields["entities_id"],
                                                           'right'     => 'all',
                                                           'condition' => $condition,
                                                           'used'      => $used,
                                                           'on_change' => 'plugin_resources_load_button_changeresources_profil()']);

            echo "<script type='text/javascript'>";
            echo "function plugin_resources_load_button_changeresources_profil(){";
            $params = ['load_button_changeresources'       => true,
                       'action'                            => self::CHANGE_ACCESSPROFIL,
                       'plugin_resources_habilitations_id' => '__VALUE__'];
            Ajax::updateItemJsCode('plugin_resources_buttonchangeresources',
                                   $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php',
                                   $params,
                                   'dropdown_plugin_resources_habilitations_id' . $rand);
            echo "}";
            echo "</script>";
            echo "</div>";
            echo "</div>";

            break;
         case self::CHANGE_CONTRACTYPE :

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __("Current contract type of the resource", "resources");
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo "&nbsp;" . Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                      $resource->getField('plugin_resources_contracttypes_id'));

            echo "</div>";
            echo "</div>";

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __('New type of contract', 'resources');
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-4 \">";
            $rand = PluginResourcesContractType::dropdown(['name'      => "plugin_resources_contracttypes_id",
                                                           'entity'    => $resource->fields["entities_id"],
                                                           'right'     => 'all',
                                                           'used'      => [$resource->getField('plugin_resources_contracttypes_id')],
                                                           'on_change' => 'plugin_resources_load_button_changeresources_contract()']);

            echo "<script type='text/javascript'>";
            echo "function plugin_resources_load_button_changeresources_contract(){";
            $params = ['load_button_changeresources'       => true,
                       'action'                            => self::CHANGE_CONTRACTYPE,
                       'plugin_resources_contracttypes_id' => '__VALUE__'];
            Ajax::updateItemJsCode('plugin_resources_buttonchangeresources',
                                   $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php',
                                   $params,
                                   'dropdown_plugin_resources_contracttypes_id' . $rand);
            echo "}";
            echo "</script>";
            echo "</div>";
            echo "</div>";

            break;
         case self::CHANGE_AGENCY :

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __("Current agency of the resource", "resources");
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo "&nbsp;" . Dropdown::getDropdownName('glpi_locations', $resource->getField('locations_id'));
            echo "</div>";
            echo "</div>";

            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
            echo __('New resource agency', 'resources');
            echo "</div>";
            echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-4 \">";
            $rand = Location::dropdown(['name'      => "locations_id",
                                        'entity'    => $resource->fields["entities_id"],
                                        'right'     => 'all',
                                        'used'      => [$resource->getField('locations_id')],
                                        'on_change' => 'plugin_resources_load_button_changeresources_agency();']);

            echo "<script type='text/javascript'>";
            echo "function plugin_resources_load_button_changeresources_agency(){";
            $params = ['load_button_changeresources' => true, 'action' => self::CHANGE_AGENCY, 'locations_id' => '__VALUE__'];
            Ajax::updateItemJsCode('plugin_resources_buttonchangeresources', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php', $params, 'dropdown_locations_id' . $rand);
            echo "}";
            echo "</script>";
            echo "</div>";
            echo "</div>";

            break;

         case self::CHANGE_TRANSFER :
            echo "<script type='text/javascript'>";
            echo "function plugin_resources_load_button_changeresources_transfer(){";
            $params = ['load_button_changeresources' => true, 'action' => self::CHANGE_TRANSFER];
            Ajax::updateItemJsCode('plugin_resources_buttonchangeresources', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php', $params, "");
            echo "}";
            echo "plugin_resources_load_button_changeresources_transfer();";
            echo "</script>";
            break;
      }
   }

   /**
    * @param $action_id
    * @param $options
    */
   function loadButtonChangeResources($action_id, $options) {
      $display = false;

      //Display for each action
      switch ($action_id) {
         case self::CHANGE_RESOURCEMANAGER :

            if (isset($options['users_id'])
                && !empty($options['users_id'])
                && $options['users_id'] != 0) {
               $display = true;
            }
            break;
         case self::CHANGE_RESOURCESALE :

            if (isset($options['users_id_sales'])
                && !empty($options['users_id_sales'])
                && $options['users_id_sales'] != 0) {
               $display = true;
            }
            break;

         case self::CHANGE_ACCESSPROFIL :

            if (isset($options['plugin_resources_habilitations_id'])
                && !empty($options['plugin_resources_habilitations_id'])
                && $options['plugin_resources_habilitations_id'] != 0) {
               $display = true;
            }
            break;
         case self::CHANGE_CONTRACTYPE :
            if (isset($options['plugin_resources_contracttypes_id'])
                && !empty($options['plugin_resources_contracttypes_id'])
                && $options['plugin_resources_contracttypes_id'] != 0) {
               $display = true;
            }
            break;
         case self::CHANGE_AGENCY :
            if (isset($options['locations_id'])
                && !empty($options['locations_id'])
                && $options['locations_id'] != 0) {
               $display = true;
            }
            break;

         case self::CHANGE_TRANSFER :
            $display = true;
            break;

      }

      if ($display) {
         echo "<div class='next'>";
         echo "<input type='submit' name='changeresources' value=\"" . __s('Starting change', 'resources') . "\" class='submit'>";
         echo "</div>";

      }

   }

   /**
    * Launch of change for ticket creation
    *
    * @param       $plugin_resources_resources_id
    * @param       $action_id
    * @param array $options
    */
   static function startingChange($plugin_resources_resources_id, $action_id, $options = []) {
      global $DB;

      $resource = new PluginResourcesResource();
      $resource->getFromDB($plugin_resources_resources_id);

      $dbu = new DbUtils();

      //Preparation of ticket data
      $data                                  = [];
      $data['itilcategories_id']             = 0;
      $data['tickettemplates_id']            = 0;
      $data['entities_id']                   = $resource->fields['entities_id'];
      $data['plugin_resources_resources_id'] = $plugin_resources_resources_id;

      //Search for the entity-related category for that action
      $resource_change = new PluginResourcesResource_Change();
      if ($resource_change->getFromDBByCrit(['actions_id'  => $action_id,
                                             'entities_id' => $resource->fields['entities_id']])) {
         $data['itilcategories_id'] = $resource_change->fields['itilcategories_id'];

         //Search of the ticket template
         $itil_category = new ITILCategory();
         if ($itil_category->getFromDB($data['itilcategories_id'])) {
            $data['tickettemplates_id'] = $itil_category->fields['tickettemplates_id_demand'];
         }
      }

      // name and content of ticket
      switch ($action_id) {
         case self::CHANGE_RESOURCEMANAGER :
            $data['name']    = __("Change manager for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id);
            $data['content'] = __("Change manager for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id) . "\n";
            $data['content'] .= __("Manager for the current resource", 'resources') . "&nbsp;:&nbsp;" .
                                $dbu->getUserName($resource->getField('users_id')) . "\n";
            $data['content'] .= __("New resource manager", 'resources') . "&nbsp;:&nbsp;" .
                                $dbu->getUserName($options['users_id']) . "\n";

            $input['users_id'] = $options['users_id'];
            break;

         case self::CHANGE_RESOURCESALE :
            $data['name']    = __("Change of sales manager for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id);
            $data['content'] = __("Change of sales manager for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id) . "\n";
            $data['content'] .= __("Sales manager for the current resource", 'resources') . "&nbsp;:&nbsp;" .
                                $dbu->getUserName($resource->getField('users_id_sales')) . "\n";
            $data['content'] .= __("New sales manager for the resource", 'resources') . "&nbsp;:&nbsp;" .
                                $dbu->getUserName($options['users_id_sales']) . "\n";

            $input['users_id_sales'] = $options['users_id_sales'];
            break;
         case self::CHANGE_ACCESSPROFIL :

            $data['name']    = __("Change the access profile for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id);
            $data['content'] = __("Change the access profile for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id) . "\n";

            $data['content'] .= __("Current access profile of the resource", 'resources') . "&nbsp;:&nbsp;";
            $query = "SELECT `glpi_plugin_resources_habilitations`.`id` 
                      FROM `glpi_plugin_resources_resourcehabilitations` 
                      LEFT JOIN `glpi_plugin_resources_habilitations` 
                       ON `glpi_plugin_resources_habilitations`.`id` = `glpi_plugin_resources_resourcehabilitations`.`plugin_resources_habilitations_id`
                      LEFT JOIN `glpi_plugin_resources_habilitationlevels` 
                      ON `glpi_plugin_resources_habilitationlevels`.`id` = `glpi_plugin_resources_habilitations`.`plugin_resources_habilitationlevels_id`
                      WHERE `plugin_resources_resources_id` = $plugin_resources_resources_id
                      AND `glpi_plugin_resources_habilitationlevels`.`is_mandatory_creating_resource` = 1";
            foreach ($DB->request($query) as $habilitation) {
               $data['content'] .= Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                             $habilitation['id'])."\n";
            }

            $data['content'] .= __("New access profile of the resource", 'resources') . "&nbsp;:&nbsp;" .
                                Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                          $options['plugin_resources_habilitations_id']) . "\n";

            $input['plugin_resources_habilitations_id'] = $options['plugin_resources_habilitations_id'];
            break;
         case self::CHANGE_CONTRACTYPE :

            $data['name']    = __("Change the type of contract for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id);
            $data['content'] = __("Change the type of contract for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id) . "\n";
            $data['content'] .= __("Current contract type of the resource", 'resources') . " " . "&nbsp;:&nbsp;" .
                                Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                          $resource->getField('plugin_resources_contracttypes_id')) . "\n";
            $data['content'] .= __("New type of contract", 'resources') . "&nbsp;:&nbsp;" .
                                Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                          $options['plugin_resources_contracttypes_id']) . "\n";

            $input['plugin_resources_contracttypes_id'] = $options['plugin_resources_contracttypes_id'];
            break;
         case self::CHANGE_AGENCY :

            $data['name']    = __("Change of agency for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id);
            $data['content'] = __("Change of agency for", 'resources') . " " .
                               PluginResourcesResource::getResourceName($plugin_resources_resources_id) . "\n";
            $data['content'] .= __("Current agency of the resource", 'resources') . "&nbsp;:&nbsp;" .
                                Dropdown::getDropdownName('glpi_locations', $resource->getField('locations_id')) . "\n";
            $data['content'] .= __("New resource agency", 'resources') . "&nbsp;:&nbsp;" .
                                Dropdown::getDropdownName('glpi_locations', $options['locations_id']) . "\n";

            $input['locations_id'] = $options['locations_id'];
            break;
      }

      $input['id']                = $plugin_resources_resources_id;
      $input['send_notification'] = 0;
      //update resource
      $resource->update($input);

      self::createTicket($data);
   }

   /**
    * Setup form
    */
   function showForm() {

      echo "<form name='form' method='post' action='" . self::getFormURL() . "'>";
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th>" . __("Managing change actions", 'resources') . "</th></tr>";
      echo "<tr class='tab_bg_1'><td class='center'>";
      echo "<a href=\"./resource_change.form.php\">" . __('Setup') . "</a>";
      echo "</td></tr></table></div>";
      Html::closeForm();

   }

   /**
    * Setup form for each action
    *
    * @return bool
    */
   function showFormActions() {
      global $CFG_GLPI;

      if (!$this->canView()) {
         return false;
      }
      if (!$this->canCreate()) {
         return false;
      }

      echo "<form name='form' method='post' action='" . self::getFormURL() . "'>";
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='3'>" . __("Managing change actions", 'resources') . "</th></tr>";

      $actions                          = self::getAllActions();
      $actions[self::BADGE_RESTITUTION] = self::getNameActions(self::BADGE_RESTITUTION);
      //delete mutation
      unset($actions[self::CHANGE_TRANSFER]);

      $canedit = true;

      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>";
      echo __('Action') . '&nbsp;';
      $rand = Dropdown::showFromArray('actions_id', $actions, ['on_change' => 'plugin_resources_load_entity();']);
      // Dropdown list according to the entity
      echo "<script type='text/javascript'>";
      echo "function plugin_resources_load_entity(){";
      $params = ['action' => 'loadEntity', 'actions_id' => '__VALUE__'];
      Ajax::updateItemJsCode('plugin_resources_entity_itil_categories', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php', $params, 'dropdown_actions_id' . $rand);
      $params = ['action' => 'clean', 'actions_id' => '__VALUE__'];
      Ajax::updateItemJsCode('plugin_resources_button_add', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php', $params, 'dropdown_actions_id' . $rand);
      echo "}";
      echo "</script>";
      echo "</td>";

      // Dropdown entity
      echo "<td class='center' id='plugin_resources_entity_itil_categories'>";

      echo "</td>";

      echo "<td class='center' id='plugin_resources_button_add'>";

      echo "</td>";

      echo "</tr>";

      echo "</table></div>";
      Html::closeForm();

      self::listItems($canedit);
   }


   /**
    * List of entities and categories already added
    *
    * @param $canedit
    */
   private function listItems($canedit) {
      // Entity already added for this action
      $datas = $this->find([], "actions_id");

      $rand = mt_rand();

      echo "<div class='center'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='4'>" . __('List') . "</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
      }
      echo "</th>";
      echo "<th>" . __('Action') . "</th>";
      echo "<th>" . __('Entity') . "</th>";
      echo "<th>" . __('Category') . "</th>";
      echo "</tr>";
      foreach ($datas as $action) {
         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $action['id']);
         }
         echo "</td>";
         //DATA LINE
         echo "<td>" . self::getNameActions($action['actions_id']) . "</td>";
         echo "<td>" . Dropdown::getDropdownName('glpi_entities', $action['entities_id']) . "</td>";
         echo "<td>" . Dropdown::getDropdownName('glpi_itilcategories', $action['itilcategories_id']) . "</td>";
         echo "</tr>";
      }

      if ($canedit) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</table>";
      echo "</div>";
   }

   /**
    * @param $actions_id
    */
   function loadEntity($actions_id) {
      global $CFG_GLPI;

      // Entity already added for this action
      $datas = $this->find(['actions_id' => $actions_id]);

      $used_entities = [];
      if ($datas) {
         foreach ($datas as $field) {
            $used_entities[] = $field['entities_id'];
         }
      }

      echo __('Entity') . '&nbsp;';
      $mrand = Dropdown::show("Entity", ['name'      => 'entities_id',
                                         'used'      => $used_entities,
                                         'on_change' => 'plugin_resources_load_category();']);

      //Dropdown list according to the entity
      echo "<script type='text/javascript'>";
      echo "function plugin_resources_load_category(){";
      $params = ['action' => 'loadCategory', 'entities_id' => '__VALUE__'];
      Ajax::updateItemJsCode('plugin_resource_itil_categories', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php', $params, 'dropdown_entities_id' . $mrand);
      echo "};";
      echo "</script>";

      echo "<span id='plugin_resource_itil_categories'>";
      self::displayCategory($_SESSION['glpiactive_entity']);
      echo "</span>";

   }

   /**
    * Display dropdown list of the category
    *
    * @param $entities_id
    */
   static function displayCategory($entities_id) {
      global $CFG_GLPI;

      echo __('Category') . "&nbsp;";
      $rand = Dropdown::show('ITILCategory', ['name'      => 'itilcategories_id',
                                              'entity'    => $entities_id,
                                              'condition' => 'is_request',
                                              'on_change' => 'plugin_resources_load_buttonadd();']);

      echo "<script type='text/javascript'>";
      echo "function plugin_resources_load_buttonadd(){";
      $params = ['action' => 'loadButtonAdd', 'itilcategories_id' => '__VALUE__'];
      Ajax::updateItemJsCode('plugin_resources_button_add', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcechange.php', $params, 'dropdown_itilcategories_id' . $rand);
      echo "};";
      echo "</script>";
   }

   /**
    * @param $itilcategories_id
    */
   static function displayButtonAdd($itilcategories_id) {
      if ($itilcategories_id != 0) {
         echo "<input type='submit' name='add_entity_category' class='submit' value='" . _sx('button', 'Add') . "' >";
      }
   }

   /**
    * Creation of ticket for change
    *
    * @param $data
    *
    * @return bool
    */
   static function createTicket($data) {

      $result = false;
      $tt     = new TicketTemplate();

      // Create ticket based on ticket template and entity informations of ticketrecurrent
      if ($tt->getFromDB($data['tickettemplates_id'])) {
         // Get default values for ticket
         $input = Ticket::getDefaultValues($data['entities_id']);
         // Apply tickettemplates predefined values
         $ttp        = new TicketTemplatePredefinedField();
         $predefined = $ttp->getPredefinedFields($data['tickettemplates_id'], true);

         if (count($predefined)) {
            foreach ($predefined as $predeffield => $predefvalue) {
               $input[$predeffield] = $predefvalue;
            }
         }
      }

      // Set date to creation date
      $createtime                 = date('Y-m-d H:i:s');
      $input['date']              = $createtime;
      $input['type']              = Ticket::DEMAND_TYPE;
      $input['itilcategories_id'] = $data['itilcategories_id'];
      // Compute time_to_resolve if predefined based on create date
      if (isset($predefined['time_to_resolve'])) {
         $input['time_to_resolve'] = Html::computeGenericDateTimeSearch($predefined['time_to_resolve'], false,
                                                                        strtotime($createtime));
      }
      // Set entity
      $input['entities_id'] = $data['entities_id'];
      $res                  = new PluginResourcesResource();
      if ($res->getFromDB($data['plugin_resources_resources_id'])) {

         $default_use_notif                                      = Entity::getUsedConfig('is_notif_enable_default', $input['entities_id'], '', 1);
         $input['users_id_recipient']                            = Session::getLoginUserID();
         $input['_users_id_requester']                           = [Session::getLoginUserID()];
         $input['_users_id_requester_notif']['use_notification'] = [$default_use_notif];

         $alternativeEmail = '';
         if (filter_var(Session::getLoginUserID(), FILTER_VALIDATE_EMAIL) !== false) {
            $alternativeEmail = Session::getLoginUserID();
         }
         $input['_users_id_requester_notif']['alternative_email'] = [$alternativeEmail];

         $input["items_id"] = ['PluginResourcesResource' => [$data['plugin_resources_resources_id']]];
      }
      $input["name"]    = $data['name'];
      $input["content"] = $data['content'];
      $input["content"] .= addslashes("\n\n");
      $input['id']      = 0;
      $ticket           = new Ticket();
      $input            = Toolbox::addslashes_deep($input);

      if ($tid = $ticket->add($input)) {
         $msg    = __('Create a end treatment ticket', 'resources') . " OK - ($tid)"; // Success
         $result = true;
      } else {
         $msg = __('Failed operation'); // Failure
      }
      if ($tid) {
         $changes[0] = 0;
         $changes[1] = '';
         $changes[2] = addslashes($msg);
         Log::history($data['plugin_resources_resources_id'], "PluginResourcesResource", $changes, '', Log::HISTORY_LOG_SIMPLE_MESSAGE);
      }
      return $result;
   }

}
