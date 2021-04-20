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
 * Class PluginResourcesNotificationTargetResource
 */
class PluginResourcesNotificationTargetResource extends NotificationTarget {

   const RESOURCE_MANAGER                     = 4300;
   const RESOURCE_AUTHOR                      = 4301;
   const RESOURCE_AUTHOR_LEAVING              = 4302;
   const RESOURCE_TASK_TECHNICIAN             = 4303;
   const RESOURCE_TASK_GROUP                  = 4304;
   const RESOURCE_USER                        = 4305;
   const RESOURCE_TARGET_ENTITY_GROUP         = 4306;
   const RESOURCE_SOURCE_ENTITY_GROUP         = 4307;
   const RESOURCE_SOURCE_ENTITY_GROUP_MANAGER = 4308;
   const RESOURCE_TARGET_ENTITY_GROUP_MANAGER = 4309;
   const RESOURCE_SALES_MANAGER               = 4310;

   /**
    * Return main notification events for the object type
    * Internal use only => should use getAllEvents
    *
    * @return an array which contains : event => event label
    **/
   function getEvents() {

      return ['new'                    => __('A resource has been added by', 'resources'),
              'update'                 => __('A resource has been updated by', 'resources'),
              'delete'                 => __('A resource has been removed by', 'resources'),
              'newtask'                => __('A task has been added by', 'resources'),
              'updatetask'             => __('A task has been updated by', 'resources'),
              'deletetask'             => __('A task has been removed by', 'resources'),
              'AlertExpiredTasks'      => __('List of not finished tasks', 'resources'),
              'AlertLeavingResources'  => __('These resources have normally left the company', 'resources'),
              'AlertArrivalChecklists' => __('Actions to do on these new resources', 'resources'),
              'AlertLeavingChecklists' => __('Actions to do on these leaving resources', 'resources'),
              'LeavingResource'        => __('A resource has been declared leaving', 'resources'),
              'report'                 => __('Creation report of the human resource', 'resources'),
              'newresting'             => __('A non contract period has been added', 'resources'),
              'updateresting'          => __('A non contract period has been updated', 'resources'),
              'deleteresting'          => __('A non contract period has been removed', 'resources'),
              'newholiday'             => __('A forced holiday has been added', 'resources'),
              'updateholiday'          => __('A forced holiday has been updated', 'resources'),
              'deleteholiday'          => __('A forced holiday has been removed', 'resources'),
              'other'                  => __('Other resource notification', 'resources'),
              'transfer'               => __('Transfer resource notification', 'resources'),
              'AlertCommercialManager' => __('Resources list of commercial manager', 'resources')
      ];
   }

   /**
    * Get additionnals targets for Tickets
    */
   function addAdditionalTargets($event = '') {

      if ($event != 'AlertExpiredTasks'
          && $event != 'AlertLeavingResources'
          && $event != 'AlertLeavingChecklists'
          && $event != 'AlertLeavingChecklists'
          && $event != 'AlertCommercialManager') {
         $this->addTarget(self::RESOURCE_MANAGER, __('Resource manager', 'resources'));
         $this->addTarget(self::RESOURCE_SALES_MANAGER, __('Sales manager', 'resources'));
         $this->addTarget(self::RESOURCE_AUTHOR, __('Requester'));
         $this->addTarget(self::RESOURCE_AUTHOR_LEAVING, __('Informant of leaving', 'resources'));
         $this->addTarget(self::RESOURCE_USER, __('Resource user', 'resources'));
         if ($event == 'newtask'
             || $event == 'updatetask'
             || $event == 'deletetask') {
            $this->addTarget(self::RESOURCE_TASK_TECHNICIAN, __("Task's responsible technician", "resources"));
            $this->addTarget(self::RESOURCE_TASK_GROUP, __("Task's responsible group", "resources"));
         }
      }

      if ($event == 'transfer') {
         // Value used for sort
         $this->notification_targets = [];
         // Displayed value
         $this->notification_targets_labels = [];
         $this->addTarget(self::RESOURCE_TARGET_ENTITY_GROUP, __('Target entity group', 'resources'));
         $this->addTarget(self::RESOURCE_SOURCE_ENTITY_GROUP, __('Source entity group', 'resources'));
         $this->addTarget(self::RESOURCE_SOURCE_ENTITY_GROUP_MANAGER, __('Source entity group manager', 'resources'));
         $this->addTarget(self::RESOURCE_TARGET_ENTITY_GROUP_MANAGER, __('Target entity group manager', 'resources'));
      }

      if ($event == 'AlertCommercialManager') {
         $this->addTarget(self::RESOURCE_SALES_MANAGER, __('Sales manager', 'resources'));
      }
   }

   /**
    * Add targets by a method not defined in NotificationTarget (specific to an itemtype)
    *
    * @param array $data    Data
    * @param array $options Options
    *
    * @return void
    **/
   function addSpecificTargets($data, $options) {

      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case self::RESOURCE_MANAGER :
            $this->getManagerAddress();
            break;
         case self::RESOURCE_SALES_MANAGER :
            $this->getSalesManagerAddress();
            break;
         case self::RESOURCE_AUTHOR :
            $this->getAuthorAddress();
            break;
         case self::RESOURCE_AUTHOR_LEAVING :
            $this->getAuthorLeavingAddress();
            break;
         case self::RESOURCE_TASK_TECHNICIAN :
            $this->getTaskTechAddress($options);
            break;
         case self::RESOURCE_TASK_GROUP :
            $this->getTaskGroupAddress($options);
            break;
         case self::RESOURCE_USER :
            $this->getRessourceAddress($options);
            break;
         case self::RESOURCE_TARGET_ENTITY_GROUP :
            $this->getEntityGroup($options, 'target');
            break;
         case self::RESOURCE_SOURCE_ENTITY_GROUP :
            $this->getEntityGroup($options, 'source');
            break;
         case self::RESOURCE_SOURCE_ENTITY_GROUP_MANAGER :
            $this->getEntityGroup($options, 'source', true);
            break;
         case self::RESOURCE_TARGET_ENTITY_GROUP_MANAGER  :
            $this->getEntityGroup($options, 'target', true);
            break;
      }
   }

   /**
    * @param        $options
    * @param string $type
    * @param bool   $supervisor
    */
   function getEntityGroup($options, $type = 'source', $supervisor = false) {
      global $DB;

      switch ($type) {
         case 'target':
            $entity = $options['target_entity'];
            break;
         case 'source':
            $entity = $options['source_entity'];
            break;
      }
      $criteria = $this->getDistinctUserCriteria();
      $criteria['FROM'] = User::getTable();
      $criteria['LEFT JOIN'] = [
            Group_User::getTable()                    => [
               'ON' => [
                  Group_User::getTable() => 'users_id',
                  User::getTable()       => 'id'
               ]
            ],
            PluginResourcesTransferEntity::getTable() => [
               'ON' => [
                  PluginResourcesTransferEntity::getTable() => 'groups_id',
                  Group_User::getTable()                    => 'groups_id'
               ]
            ]
         ];
      $criteria['WHERE'] = ['glpi_plugin_resources_transferentities.entities_id' => $entity];

      if ($supervisor) {
         $criteria['WHERE']['glpi_groups_users.is_manager'] = 1;
      }

      $iterator = $DB->request($criteria);
      while ($data = $iterator->next()) {
         $this->addToRecipientsList($data);
      }
   }

   //Get recipient
   function getManagerAddress() {
      return $this->addUserByField("users_id");
   }

   function getSalesManagerAddress() {
      return $this->addUserByField("users_id_sales");
   }

   function getAuthorAddress() {
      return $this->addUserByField("users_id_recipient");
   }

   function getAuthorLeavingAddress() {
      return $this->addUserByField("users_id_recipient_leaving");
   }

   /**
    * @param array $options
    */
   function getRessourceAddress($options = []) {
      global $DB;

      if (isset($options['reports_id'])) {
         $query = "SELECT DISTINCT `glpi_users`.`id` AS id,
                          `glpi_users`.`language` AS language
                   FROM `glpi_plugin_resources_resources_items`
                   LEFT JOIN `glpi_users` 
                     ON (`glpi_users`.`id` = `glpi_plugin_resources_resources_items`.`items_id` 
                           AND `glpi_plugin_resources_resources_items`.`itemtype` = 'USER')
                   LEFT JOIN `glpi_plugin_resources_reportconfigs` 
                     ON (`glpi_plugin_resources_resources_items`.`plugin_resources_resources_id` = `glpi_plugin_resources_reportconfigs`.`plugin_resources_resources_id`)
                   WHERE `glpi_plugin_resources_reportconfigs`.`id` = '" . $options['reports_id'] . "'";

         foreach ($DB->request($query) as $data) {
            $data['email'] = UserEmail::getDefaultForUser($data['id']);
            $this->addToRecipientsList($data);
         }
      }
   }

   /**
    * @param array $options
    */
   function getTaskTechAddress($options = []) {
      global $DB;

      if (isset($options['tasks_id'])) {
         if (is_array($options['tasks_id'])) {
            $options['tasks_id'] = reset($options['tasks_id']);
         }
         $query = "SELECT DISTINCT `glpi_users`.`id` AS id,
                          `glpi_users`.`language` AS language
                   FROM `glpi_plugin_resources_tasks`
                   LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_plugin_resources_tasks`.`users_id`)
                   WHERE `glpi_plugin_resources_tasks`.`id` = '" . $options['tasks_id'] . "'";

         foreach ($DB->request($query) as $data) {
            $data['email'] = UserEmail::getDefaultForUser($data['id']);
            $this->addToRecipientsList($data);
         }
      }
   }

   /**
    * @param array $options
    */
   function getTaskGroupAddress($options = []) {
      global $DB;

      if (isset($options['groups_id'])
          && $options['groups_id'] > 0
          && isset($options['tasks_id'])) {

         if (is_array($options['tasks_id'])) {
            $options['tasks_id'] = reset($options['tasks_id']);
         }

         $criteria = $this->getDistinctUserCriteria();
         $criteria['FROM'] = User::getTable();
         $criteria['LEFT JOIN'] = [
            Group_User::getTable()          => [
               'ON' => [
                  Group_User::getTable() => 'users_id',
                  User::getTable()       => 'id'
               ]
            ],
            PluginResourcesTask::getTable() => [
               'ON' => [
                  Group_User::getTable()          => 'groups_id',
                  PluginResourcesTask::getTable() => 'groups_id'
               ]
            ]
         ];
         $criteria['WHERE'] = ['glpi_plugin_resources_tasks.id' => $options['tasks_id']];

         $iterator = $DB->request($criteria);
         while ($data = $iterator->next()) {
            $this->addToRecipientsList($data);
         }
      }
   }

   /**
    * Get all data needed for template processing
    * Provides minimum information for alerts
    * Can be overridden by each NotificationTartget class if needed
    *
    * @param string $event   Event name
    * @param array  $options Options
    *
    * @return void
    **/
   function addDataForTemplate($event, $options = []) {
      global $CFG_GLPI, $DB;

      $dbu = new DbUtils();
      if ($event == 'AlertExpiredTasks') {

         $this->data['##resource.entity##']      =
            Dropdown::getDropdownName('glpi_entities',
                                      $options['entities_id']);
         $this->data['##lang.resource.entity##'] = __('Entity');
         $this->data['##resource.action##']      = __('List of not finished tasks', 'resources');

         $this->data['##lang.task.name##']      = __('Name');
         $this->data['##lang.task.type##']      = __('Type');
         $this->data['##lang.task.users##']     = __('Technician');
         $this->data['##lang.task.groups##']    = __('Group');
         $this->data['##lang.task.datebegin##'] = __('Begin date');
         $this->data['##lang.task.dateend##']   = __('End date');
         $this->data['##lang.task.planned##']   = __('Used for planning', 'resources');
         $this->data['##lang.task.realtime##']  = __('Effective duration', 'resources');
         $this->data['##lang.task.finished##']  = __('Carried out task', 'resources');
         $this->data['##lang.task.comment##']   = __('Comments');
         $this->data['##lang.task.resource##']  = PluginResourcesResource::getTypeName(1);

         foreach ($options['tasks'] as $id => $task) {
            $tmp = [];

            $tmp['##task.name##']   = $task['name'];
            $tmp['##task.type##']   = Dropdown::getDropdownName('glpi_plugin_resources_tasktypes',
                                                                $task['plugin_resources_tasktypes_id']);
            $tmp['##task.users##']  = Html::clean($dbu->getUserName($task['users_id']));
            $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                                $task['groups_id']);
            $restrict               = ["plugin_resources_tasks_id" => $task['id']];
            $plans                  = $dbu->getAllDataFromTable("glpi_plugin_resources_taskplannings", $restrict);

            if (!empty($plans)) {
               foreach ($plans as $plan) {
                  $tmp['##task.datebegin##'] = Html::convDateTime($plan["begin"]);
                  $tmp['##task.dateend##']   = Html::convDateTime($plan["end"]);
               }
            } else {
               $tmp['##task.datebegin##'] = '';
               $tmp['##task.dateend##']   = '';
            }

            $tmp['##task.planned##']  = '';
            $tmp['##task.finished##'] = Dropdown::getYesNo($task['is_finished']);
            $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
            $comment                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $task['comment']));
            $tmp['##task.comment##']  = Html::clean($comment);
            $tmp['##task.resource##'] = Dropdown::getDropdownName('glpi_plugin_resources_resources',
                                                                  $task['plugin_resources_resources_id']);

            $this->data['tasks'][] = $tmp;
         }
      } else if ($event == 'AlertLeavingResources') {

         $this->data['##resource.entity##']      =
            Dropdown::getDropdownName('glpi_entities',
                                      $options['entities_id']);
         $this->data['##lang.resource.entity##'] = __('Entity');
         $this->data['##resource.action##']      = __('These resources have normally left the company', 'resources');

         $this->data['##lang.resource.id##']              = "ID";
         $this->data['##lang.resource.name##']            = __('Surname');
         $this->data['##lang.resource.firstname##']       = __('First name');
         $this->data['##lang.resource.type##']            = PluginResourcesContractType::getTypeName(1);
         $this->data['##lang.resource.users##']           = __('Resource manager', 'resources');
         $this->data['##lang.resource.userssale##']       = __('Sales manager', 'resources');
         $this->data['##lang.resource.usersrecipient##']  = __('Requester');
         $this->data['##lang.resource.datedeclaration##'] = __('Request date');
         $this->data['##lang.resource.datebegin##']       = __('Arrival date', 'resources');
         $this->data['##lang.resource.dateend##']         = __('Departure date', 'resources');
         $this->data['##lang.resource.department##']      = PluginResourcesDepartment::getTypeName(1);
         $this->data['##lang.resource.habilitation##']    = PluginResourcesHabilitation::getTypeName(1);
         $this->data['##lang.resource.status##']          = PluginResourcesResourceState::getTypeName(1);
         $this->data['##lang.resource.location##']        = __('Location');
         $this->data['##lang.resource.comment##']         = __('Description');
         $this->data['##lang.resource.usersleaving##']    = __('Informant of leaving', 'resources');
         $this->data['##lang.resource.leaving##']         = __('Declared as leaving', 'resources');
         $this->data['##lang.resource.leavingreason##']   = PluginResourcesLeavingReason::getTypeName(1);
         $this->data['##lang.resource.helpdesk##']        = __('Associable to a ticket');
         $this->data['##lang.resource.url##']             = __('URL');

         foreach ($options['resources'] as $id => $resource) {
            $tmp = [];

            $tmp['##resource.name##']            = $resource['name'];
            $tmp['##resource.firstname##']       = $resource['firstname'];
            $tmp['##resource.type##']            = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                                             $resource['plugin_resources_contracttypes_id']);
            $tmp['##resource.users##']           = Html::clean($dbu->getUserName($resource['users_id']));
            $tmp['##resource.userssale##']       = Html::clean($dbu->getUserName($resource['users_id_sales']));
            $tmp['##resource.usersrecipient##']  = Html::clean($dbu->getUserName($resource['users_id_recipient']));
            $tmp['##resource.datedeclaration##'] = Html::convDateTime($resource['date_declaration']);
            $tmp['##resource.datebegin##']       = Html::convDateTime($resource['date_begin']);
            $tmp['##resource.dateend##']         = Html::convDateTime($resource['date_end']);
            $tmp['##resource.department##']      = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                                             $resource['plugin_resources_departments_id']);
            $resourcehabilitation                = new PluginResourcesResourceHabilitation();
            $habilitations                       = $resourcehabilitation->find(['plugin_resources_resources_id' => $resource['id']]);
            $tab                                 = [];
            foreach ($habilitations as $habilitation) {
               $tab[] = Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                  $habilitation['plugin_resources_habilitations_id']) . "\n";
            }
            $tmp['##resource.habilitation##'] = implode(', ', $tab);

            $tmp['##resource.status##']                 = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                                                    $resource['plugin_resources_resourcestates_id']);
            $tmp['##resource.location##']               = Dropdown::getDropdownName('glpi_locations',
                                                                                    $resource['locations_id']);
            $comment                                    = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $resource['comment']));
            $tmp['##resource.comment##']                = Html::clean($comment);
            $tmp['##resource.usersleaving##']           = Html::clean($dbu->getUserName($resource['users_id_recipient_leaving']));
            $tmp['##resource.leaving##']                = Dropdown::getYesNo($resource['is_leaving']);
            $tmp['##resource.datedeclarationleaving##'] = Html::convDateTime($resource['date_declaration_leaving']);
            $tmp['##resource.leavingreason##']          = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
                                                                                    $resource['plugin_resources_leavingreasons_id']);
            $tmp['##resource.helpdesk##']               = Dropdown::getYesNo($resource['is_helpdesk_visible']);
            $tmp['##resource.url##']                    = urldecode($CFG_GLPI["url_base"] . "/index.php?redirect=PluginResourcesResource_" . $resource['id']);

            $this->data['resources'][] = $tmp;
         }
      } else if ($event == 'AlertArrivalChecklists' || $event == 'AlertLeavingChecklists') {

         $this->data['##checklist.entity##']      =
            Dropdown::getDropdownName('glpi_entities',
                                      $options['entities_id']);
         $this->data['##lang.checklist.entity##'] = __('Entity');

         if ($event == 'AlertArrivalChecklists') {
            $checklist_type                         = PluginResourcesChecklist::RESOURCES_CHECKLIST_IN;
            $this->data['##checklist.action##']     = __('Actions to do on these new resources', 'resources');
            $this->data['##lang.checklist.title##'] = __('New resource - checklist needs to verificated', 'resources');
         } else {
            $checklist_type                         = PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT;
            $this->data['##checklist.action##']     = __('Actions to do on these leaving resources', 'resources');
            $this->data['##lang.checklist.title##'] = __('Leaving resource - checklist needs to verificated', 'resources');
         }
         $this->data['##lang.checklist.title2##'] = __('Checklist needs to verificated', 'resources');

         $this->data['##lang.checklist.id##']              = "ID";
         $this->data['##lang.checklist.name##']            = __('Surname');
         $this->data['##lang.checklist.firstname##']       = __('First name');
         $this->data['##lang.checklist.type##']            = PluginResourcesContractType::getTypeName(1);
         $this->data['##lang.checklist.users##']           = __('Resource manager', 'resources');
         $this->data['##lang.checklist.userssale##']       = __('Sales manager', 'resources');
         $this->data['##lang.checklist.usersrecipient##']  = __('Requester');
         $this->data['##lang.checklist.datedeclaration##'] = __('Request date');
         $this->data['##lang.checklist.datebegin##']       = __('Arrival date', 'resources');
         $this->data['##lang.checklist.dateend##']         = __('Departure date', 'resources');
         $this->data['##lang.checklist.department##']      = PluginResourcesDepartment::getTypeName(1);
         $this->data['##lang.checklist.habilitation##']    = PluginResourcesHabilitation::getTypeName(1);
         $this->data['##lang.checklist.status##']          = PluginResourcesResourceState::getTypeName(1);
         $this->data['##lang.checklist.location##']        = __('Location');
         $this->data['##lang.checklist.comment##']         = __('Description');
         $this->data['##lang.checklist.usersleaving##']    = __('Informant of leaving', 'resources');
         $this->data['##lang.checklist.leaving##']         = __('Declared as leaving', 'resources');
         //         $this->data['##lang.checklist.leavingreason##'] = PluginResourcesLeavingReason::getTypeName(1);
         $this->data['##lang.checklist.helpdesk##'] = __('Associable to a ticket');
         $this->data['##lang.checklist.url##']      = "URL";

         foreach ($options['checklists'] as $id => $checklist) {
            $tmp = [];

            $tmp['##checklist.id##']              = $checklist['plugin_resources_resources_id'];
            $tmp['##checklist.name##']            = $checklist['resource_name'];
            $tmp['##checklist.firstname##']       = $checklist['resource_firstname'];
            $tmp['##checklist.type##']            = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                                              $checklist['plugin_resources_contracttypes_id']);
            $tmp['##checklist.users##']           = Html::clean($dbu->getUserName($checklist['users_id']));
            $tmp['##checklist.userssale##']       = Html::clean($dbu->getUserName($checklist['users_id_sales']));
            $tmp['##checklist.usersrecipient##']  = Html::clean($dbu->getUserName($checklist['users_id_recipient']));
            $tmp['##checklist.datedeclaration##'] = Html::convDateTime($checklist['date_declaration']);
            $tmp['##checklist.datebegin##']       = Html::convDateTime($checklist['date_begin']);
            $tmp['##checklist.dateend##']         = Html::convDateTime($checklist['date_end']);
            $tmp['##checklist.department##']      = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                                              $checklist['plugin_resources_departments_id']);
            $resourcehabilitation                 = new PluginResourcesResourceHabilitation();
            $habilitations                        = $resourcehabilitation->find(['plugin_resources_resources_id' => $checklist['plugin_resources_resources_id']]);
            $tab                                  = [];
            foreach ($habilitations as $habilitation) {
               $tab[] = Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                  $habilitation['plugin_resources_habilitations_id']) . "\n";
            }
            $tmp['##checklist.habilitation##']           = implode(', ', $tab);
            $tmp['##checklist.status##']                 = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                                                     $checklist['plugin_resources_resourcestates_id']);
            $tmp['##checklist.location##']               = Dropdown::getDropdownName('glpi_locations',
                                                                                     $checklist['locations_id']);
            $comment                                     = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $checklist['comment']));
            $tmp['##checklist.comment##']                = Html::clean($comment);
            $tmp['##checklist.usersleaving##']           = Html::clean($dbu->getUserName($checklist['users_id_recipient_leaving']));
            $tmp['##checklist.datedeclarationleaving##'] = Html::convDateTime($checklist['date_declaration_leaving']);
            $tmp['##checklist.leaving##']                = Dropdown::getYesNo($checklist['is_leaving']);
            //            $tmp['##checklist.leavingreason##'] = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
            //                                                   $checklist['plugin_resources_leavingreasons_id']);
            $tmp['##checklist.helpdesk##'] = Dropdown::getYesNo($checklist['is_helpdesk_visible']);
            $tmp['##checklist.url##']      = urldecode($CFG_GLPI["url_base"] . "/index.php?redirect=PluginResourcesResource_" .
                                                       $checklist['plugin_resources_resources_id']);

            $query = PluginResourcesChecklist::queryListChecklists($checklist['plugin_resources_resources_id'], $checklist_type);

            $tmp['##tasklist.name##'] = '';
            foreach ($DB->request($query) as $data) {

               $tmp['##tasklist.name##'] .= $data["name"];
               if ($_SESSION["glpiis_ids_visible"] == 1) {
                  $tmp['##tasklist.name##'] .= " (" . $data["id"] . ")";
               }
               $tmp['##tasklist.name##'] .= "\n";
            }

            $this->data['checklists'][] = $tmp;

         }
      } else if ($event == 'LeavingResource') {

         $this->data['##resource.entity##']      =
            Dropdown::getDropdownName('glpi_entities',
                                      $this->obj->getField('entities_id'));
         $this->data['##lang.resource.entity##'] = __('Entity');
         $this->data['##lang.resource.title##']  = __('A resource has been declared leaving', 'resources');

         $this->data['##lang.resource.title2##'] = __('Please check the leaving checklist of the resource', 'resources');

         $this->data['##lang.resource.id##']   = "ID";
         $this->data['##resource.id##']        = $this->obj->getField("id");
         $this->data['##lang.resource.name##'] = __('Surname');
         $this->data['##resource.name##']      = $this->obj->getField("name");

         $this->data['##lang.resource.firstname##'] = __('First name');
         $this->data['##resource.firstname##']      = $this->obj->getField("firstname");

         $this->data['##lang.resource.type##'] = PluginResourcesContractType::getTypeName(1);
         $this->data['##resource.type##']      = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                                           $this->obj->getField('plugin_resources_contracttypes_id'));

         $this->data['##lang.resource.situation##'] = PluginResourcesResourceSituation::getTypeName(1);
         $this->data['##resource.situation##']      = Dropdown::getDropdownName('glpi_plugin_resources_resourcesituations',
                                                                                $this->obj->getField('plugin_resources_resourcesituations_id'));

         $this->data['##lang.resource.contractnature##'] = PluginResourcesContractNature::getTypeName(1);
         $this->data['##resource.contractnature##']      = Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
                                                                                     $this->obj->getField('plugin_resources_contractnatures_id'));

         $this->data['##lang.resource.quota##'] = __('Quota', 'resources');
         $this->data['##resource.quota##']      = $this->obj->getField('quota');

         $this->data['##lang.resource.department##'] = PluginResourcesDepartment::getTypeName(1);
         $this->data['##resource.department##']      = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                                                 $this->obj->getField('plugin_resources_departments_id'));

         $resourcehabilitation                         = new PluginResourcesResourceHabilitation();
         $habilitations                                = $resourcehabilitation->find(['plugin_resources_resources_id' => $this->obj->getField('id')]);
         $tab                                          = [];
         $this->data['##lang.resource.habilitation##'] = PluginResourcesHabilitation::getTypeName(count($habilitations));
         foreach ($habilitations as $habilitation) {
            $tab[] = Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                               $habilitation['plugin_resources_habilitations_id']) . "\n";
         }
         $this->data['##resource.habilitation##'] = implode(', ', $tab);

         $this->data['##lang.resource.rank##'] = PluginResourcesRank::getTypeName(1);
         $this->data['##resource.rank##']      = Dropdown::getDropdownName('glpi_plugin_resources_ranks',
                                                                           $this->obj->getField('plugin_resources_ranks_id'));

         $this->data['##lang.resource.speciality##'] = PluginResourcesResourceSpeciality::getTypeName(1);
         $this->data['##resource.speciality##']      = Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
                                                                                 $this->obj->getField('plugin_resources_resourcespecialities_id'));

         $this->data['##lang.resource.status##'] = PluginResourcesResourceState::getTypeName(1);
         $this->data['##resource.status##']      = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                                             $this->obj->getField('plugin_resources_resourcestates_id'));

         $this->data['##lang.resource.users##'] = __('Resource manager', 'resources');
         $this->data['##resource.users##']      = Html::clean($dbu->getUserName($this->obj->getField("users_id")));

         $this->data['##lang.resource.userssale##'] = __('Sales manager', 'resources');
         $this->data['##resource.userssale##']      = Html::clean($dbu->getUserName($this->obj->getField("users_id_sales")));

         $this->data['##lang.resource.usersrecipient##'] = __('Requester');
         $this->data['##resource.usersrecipient##']      = Html::clean($dbu->getUserName($this->obj->getField("users_id_recipient")));

         $this->data['##lang.resource.datedeclaration##'] = __('Request date');
         $this->data['##resource.datedeclaration##']      = Html::convDate($this->obj->getField('date_declaration'));

         $this->data['##lang.resource.datebegin##'] = __('Arrival date', 'resources');
         $this->data['##resource.datebegin##']      = Html::convDate($this->obj->getField('date_begin'));

         $this->data['##lang.resource.dateend##'] = __('Departure date', 'resources');
         $this->data['##resource.dateend##']      = Html::convDate($this->obj->getField('date_end'));

         $this->data['##lang.resource.location##'] = __('Location');
         $this->data['##resource.location##']      = Dropdown::getDropdownName('glpi_locations',
                                                                               $this->obj->getField('locations_id'));

         $this->data['##lang.resource.helpdesk##'] = __('Associable to a ticket');
         $this->data['##resource.helpdesk##']      = Dropdown::getYesNo($this->obj->getField('is_helpdesk_visible'));

         $this->data['##lang.resource.leaving##'] = __('Declared as leaving', 'resources');
         $this->data['##resource.leaving##']      = Dropdown::getYesNo($this->obj->getField('is_leaving'));

         $this->data['##lang.resource.leavingreason##'] = PluginResourcesLeavingReason::getTypeName(1);
         $this->data['##resource.leavingreason##']      = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
                                                                                    $this->obj->getField('plugin_resources_leavingreasons_id'));

         $this->data['##lang.resource.usersleaving##'] = __('Informant of leaving', 'resources');
         $this->data['##resource.usersleaving##']      = Html::clean($dbu->getUserName($this->obj->getField('users_id_recipient_leaving')));

         $this->data['##lang.resource.datedeclarationleaving##'] = __('Declaration of departure date', 'resources');
         $this->data['##resource.datedeclarationleaving##']      = Html::convDateTime($this->obj->getField('date_declaration_leaving'));

         $this->data['##lang.resource.comment##'] = __('Description');
         $comment                                 = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $this->obj->getField("comment")));
         $this->data['##resource.comment##']      = Html::clean($comment);

         $this->data['##lang.resource.url##'] = "URL";
         $this->data['##resource.url##']      = urldecode($CFG_GLPI["url_base"] . "/index.php?redirect=PluginResourcesResource_" .
                                                          $this->obj->getField("id"));

         $this->data['##lang.resource.badge##'] = " ";
         if (isset($this->target_object->input['checkbadge'])) {
            if (!empty($this->target_object->input['checkbadge'])) {
               $this->data['##lang.resource.badge##'] = __('Thanks to recover his badges', 'resources');
            } else {
               $this->data['##lang.resource.badge##'] = " ";
            }
         }

      } else if ($event == 'AlertCommercialManager') {

         $this->data['##lang.commercial.title##'] = __('List of your associated resources', 'resources');

         $this->data['##lang.resource.id##']              = "ID";
         $this->data['##lang.resource.name##']            = __('Surname');
         $this->data['##lang.resource.firstname##']       = __('First name');
         $this->data['##lang.resource.type##']            = PluginResourcesContractType::getTypeName(1);
         $this->data['##lang.resource.situation##']       = PluginResourcesResourceSituation::getTypeName(1);
         $this->data['##lang.resource.contractnature##']  = PluginResourcesContractNature::getTypeName(1);
         $this->data['##lang.resource.quota##']           = __('Quota', 'resources');
         $this->data['##lang.resource.department##']      = PluginResourcesDepartment::getTypeName(1);
         $this->data['##lang.resource.habilitation##']    = PluginResourcesHabilitation::getTypeName(1);
         $this->data['##lang.resource.rank##']            = PluginResourcesRank::getTypeName(1);
         $this->data['##lang.resource.speciality##']      = PluginResourcesResourceSpeciality::getTypeName(1);
         $this->data['##lang.resource.status##']          = PluginResourcesResourceState::getTypeName(1);
         $this->data['##lang.resource.users##']           = __('Resource manager', 'resources');
         $this->data['##lang.resource.userssale##']       = __('Sales manager', 'resources');
         $this->data['##lang.resource.usersrecipient##']  = __('Requester');
         $this->data['##lang.resource.datedeclaration##'] = __('Request date');
         $this->data['##lang.resource.datebegin##']       = __('Arrival date', 'resources');
         $this->data['##lang.resource.dateend##']         = __('Departure date', 'resources');
         $this->data['##lang.resource.location##']        = __('Location');
         $this->data['##lang.resource.helpdesk##']        = __('Associable to a ticket');
         $this->data['##lang.resource.leaving##']         = __('Declared as leaving', 'resources');
         $this->data['##lang.resource.leavingreason##']   = PluginResourcesLeavingReason::getTypeName(1);
         $this->data['##lang.resource.usersleaving##']    = __('Informant of leaving', 'resources');
         $this->data['##lang.resource.comment##']         = __('Description');
         $this->data['##lang.resource.url##']             = "URL";

         foreach ($options['resources'] as $resource) {
            $tmp = [];

            $tmp['##resource.id##'] = $resource["id"];

            $tmp['##resource.name##'] = $resource["name"];

            $tmp['##resource.firstname##'] = $resource["firstname"];

            $tmp['##resource.type##'] = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                                  $resource['plugin_resources_contracttypes_id']);

            $tmp['##resource.situation##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcesituations',
                                                                       $resource['plugin_resources_resourcesituations_id']);

            $tmp['##resource.contractnature##'] = Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
                                                                            $resource['plugin_resources_contractnatures_id']);

            $tmp['##resource.quota##'] = $resource['quota'];

            $tmp['##resource.department##'] = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                                        $resource['plugin_resources_departments_id']);

            $resourcehabilitation = new PluginResourcesResourceHabilitation();
            $habilitations        = $resourcehabilitation->find(['plugin_resources_resources_id' => $resource['id']]);
            $tab                  = [];
            foreach ($habilitations as $habilitation) {
               $tab[] = Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                  $habilitation['plugin_resources_habilitations_id']) . "\n";
            }
            $tmp['##resource.habilitation##'] = implode(', ', $tab);

            $tmp['##resource.rank##'] = Dropdown::getDropdownName('glpi_plugin_resources_ranks',
                                                                  $resource['plugin_resources_ranks_id']);

            $tmp['##resource.speciality##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
                                                                        $resource['plugin_resources_resourcespecialities_id']);

            $tmp['##resource.status##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                                    $resource['plugin_resources_resourcestates_id']);

            $tmp['##resource.users##'] = Html::clean($dbu->getUserName($resource["users_id"]));

            $tmp['##resource.userssale##'] = Html::clean($dbu->getUserName($resource["users_id_sales"]));

            $tmp['##resource.usersrecipient##'] = Html::clean($dbu->getUserName($resource["users_id_recipient"]));

            $tmp['##resource.datedeclaration##'] = Html::convDate($resource['date_declaration']);

            $tmp['##resource.datebegin##'] = Html::convDate($resource['date_begin']);

            $tmp['##resource.dateend##'] = Html::convDate($resource['date_end']);

            $tmp['##resource.location##'] = Dropdown::getDropdownName('glpi_locations',
                                                                      $resource['locations_id']);

            $tmp['##resource.helpdesk##'] = Dropdown::getYesNo($resource['is_helpdesk_visible']);

            $tmp['##resource.leaving##'] = Dropdown::getYesNo($resource['is_leaving']);

            $tmp['##resource.leavingreason##'] = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
                                                                           $resource['plugin_resources_leavingreasons_id']);

            $tmp['##resource.usersleaving##'] = Html::clean($dbu->getUserName($resource['users_id_recipient_leaving']));

            $tmp['##resource.datedeclarationleaving##'] = Html::convDateTime($resource['date_declaration_leaving']);

            $comment                     = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $resource["comment"]));
            $tmp['##resource.comment##'] = Html::clean($comment);

            $tmp['##resource.url##'] = urldecode($CFG_GLPI["url_base"] . "/index.php?redirect=PluginResourcesResource_" .
                                                 $resource["id"]);

            $this->data['commercials'][] = $tmp;

         }
      } else {

         $events = $this->getAllEvents();

         $this->data['##lang.resource.title##']  = $events[$event];
         $this->data['##resource.action_user##'] = $dbu->getUserName(Session::getLoginUserID());
         $this->data['##lang.resource.entity##'] = __('Entity');
         $this->data['##resource.entity##']      =
            Dropdown::getDropdownName('glpi_entities',
                                      $this->obj->getField('entities_id'));
         $this->data['##resource.id##']          = $this->obj->getField("id");

         $this->data['##lang.resource.name##'] = __('Surname');
         $this->data['##resource.name##']      = $this->obj->getField("name");

         $this->data['##lang.resource.firstname##'] = __('First name');
         $this->data['##resource.firstname##']      = $this->obj->getField("firstname");

         $this->data['##lang.resource.type##'] = PluginResourcesContractType::getTypeName(1);
         $this->data['##resource.type##']      = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                                           $this->obj->getField('plugin_resources_contracttypes_id'));

         $this->data['##lang.resource.situation##'] = PluginResourcesResourceSituation::getTypeName(1);
         $this->data['##resource.situation##']      = Dropdown::getDropdownName('glpi_plugin_resources_resourcesituations',
                                                                                $this->obj->getField('plugin_resources_resourcesituations_id'));

         $this->data['##lang.resource.contractnature##'] = PluginResourcesContractNature::getTypeName(1);
         $this->data['##resource.contractnature##']      = Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
                                                                                     $this->obj->getField('plugin_resources_contractnatures_id'));

         $this->data['##lang.resource.quota##'] = __('Quota', 'resources');
         $this->data['##resource.quota##']      = $this->obj->getField('quota');

         $this->data['##lang.resource.users##'] = __('Resource manager', 'resources');
         $this->data['##resource.users##']      = Html::clean($dbu->getUserName($this->obj->getField("users_id")));

         $this->data['##lang.resource.userssale##'] = __('Sales manager', 'resources');
         $this->data['##resource.userssale##']      = Html::clean($dbu->getUserName($this->obj->getField("users_id_sales")));

         $this->data['##lang.resource.usersrecipient##'] = __('Requester');
         $this->data['##resource.usersrecipient##']      = Html::clean($dbu->getUserName($this->obj->getField("users_id_recipient")));

         $this->data['##lang.resource.datedeclaration##'] = __('Request date');
         $this->data['##resource.datedeclaration##']      = Html::convDate($this->obj->getField('date_declaration'));

         $this->data['##lang.resource.datebegin##'] = __('Arrival date', 'resources');
         $this->data['##resource.datebegin##']      = Html::convDate($this->obj->getField('date_begin'));

         $this->data['##lang.resource.dateend##'] = __('Departure date', 'resources');
         $this->data['##resource.dateend##']      = Html::convDate($this->obj->getField('date_end'));

         $this->data['##lang.resource.department##'] = PluginResourcesDepartment::getTypeName(1);
         $this->data['##resource.department##']      = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                                                 $this->obj->getField('plugin_resources_departments_id'));

         $this->data['##lang.resource.rank##'] = PluginResourcesRank::getTypeName(1);
         $this->data['##resource.rank##']      = Dropdown::getDropdownName('glpi_plugin_resources_ranks',
                                                                           $this->obj->getField('plugin_resources_ranks_id'));

         $this->data['##lang.resource.speciality##'] = PluginResourcesResourceSpeciality::getTypeName(1);
         $this->data['##resource.speciality##']      = Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
                                                                                 $this->obj->getField('plugin_resources_resourcespecialities_id'));

         $this->data['##lang.resource.status##'] = PluginResourcesResourceState::getTypeName(1);
         $this->data['##resource.status##']      = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                                             $this->obj->getField('plugin_resources_resourcestates_id'));

         $this->data['##lang.resource.location##'] = __('Location');
         $this->data['##resource.location##']      = Dropdown::getDropdownName('glpi_locations',
                                                                               $this->obj->getField('locations_id'));

         $this->data['##lang.resource.comment##'] = __('Description');
         $comment                                 = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $this->obj->getField("comment")));
         $this->data['##resource.comment##']      = Html::clean($comment);

         $this->data['##lang.resource.usersleaving##'] = __('Informant of leaving', 'resources');
         $this->data['##resource.usersleaving##']      = Html::clean($dbu->getUserName($this->obj->getField("users_id_recipient_leaving")));

         $this->data['##lang.resource.datedeclarationleaving##'] = __('Declaration of departure date', 'resources');
         $this->data['##resource.datedeclarationleaving##']      = Html::convDateTime($this->obj->getField('date_declaration_leaving'));

         $this->data['##lang.resource.leaving##'] = __('Declared as leaving', 'resources');
         $this->data['##resource.leaving##']      = Dropdown::getYesNo($this->obj->getField('is_leaving'));

         $this->data['##lang.resource.leavingreason##'] = PluginResourcesLeavingReason::getTypeName(1);
         $this->data['##resource.leavingreason##']      = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
                                                                                    $this->obj->getField('plugin_resources_leavingreasons_id'));

         $resourcehabilitation                         = new PluginResourcesResourceHabilitation();
         $habilitations                                = $resourcehabilitation->find(['plugin_resources_resources_id' => $this->obj->getField('id')]);
         $this->data['##lang.resource.habilitation##'] = PluginResourcesHabilitation::getTypeName(count($habilitations));
         $tab                                          = [];
         foreach ($habilitations as $habilitation) {
            $tab[] = Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                               $habilitation['plugin_resources_habilitations_id']);
         }
         $this->data['##resource.habilitation##'] = implode(', ', $tab);

         $this->data['##lang.resource.sensitizesecurity##'] = __('Sensitized to security', 'resources');
         $this->data['##resource.sensitizesecurity##']      = Dropdown::getYesNo($this->obj->getField('sensitize_security'));

         $this->data['##lang.resource.readchart##'] = __('Reading the security charter', 'resources');
         $this->data['##resource.readchart##']      = Dropdown::getYesNo($this->obj->getField('read_chart'));

         $this->data['##lang.resource.helpdesk##'] = __('Associable to a ticket');
         $this->data['##resource.helpdesk##']      = Dropdown::getYesNo($this->obj->getField('is_helpdesk_visible'));

         $this->data['##lang.resource.url##'] = "URL";
         $this->data['##resource.url##']      = urldecode($CFG_GLPI["url_base"] . "/index.php?redirect=PluginResourcesResource_" .
                                                          $this->obj->getField("id"));

         if ($event == 'report') {

            $this->data['##lang.resource.creationtitle##'] = __('Creation report of the human resource', 'resources');

            $this->data['##resource.login##'] = "";
            $this->data['##resource.email##'] = "";

            $restrict = ["itemtype" => 'User',
                        "plugin_resources_resources_id" => $this->obj->getField("id")];
            $items    = $dbu->getAllDataFromTable("glpi_plugin_resources_resources_items", $restrict);
            if (!empty($items)) {
               foreach ($items as $item) {
                  $user = new User();
                  $user->getFromDB($item["items_id"]);
                  $this->data['##resource.login##'] = $user->fields["name"];
                  $this->data['##resource.email##'] = $user->getDefaultEmail();
               }
            }

            $this->data['##lang.resource.login##'] = __('Login');

            $this->data['##lang.resource.creation##']     = __('Informations of the created user', 'resources');
            $this->data['##lang.resource.datecreation##'] = __('Creation date');
            $this->data['##resource.datecreation##']      = Html::convDate(date("Y-m-d"));

            $this->data['##lang.resource.email##'] = __('Email');

            $this->data['##lang.resource.informationtitle##'] = __('Additional informations', 'resources');

            $PluginResourcesReportConfig = new PluginResourcesReportConfig();
            $PluginResourcesReportConfig->getFromDB($options['reports_id']);

            $this->data['##lang.resource.informations##'] = _n('Information', 'Informations', 2);
            $information                                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br>", $PluginResourcesReportConfig->fields['information']));
            $this->data['##resource.informations##']      = Html::clean(nl2br($information));

            $this->data['##lang.resource.commentaires##'] = __('Comments');
            $commentaire                                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br>", $PluginResourcesReportConfig->fields['comment']));
            $this->data['##resource.commentaires##']      = Html::clean(nl2br($commentaire));
         }

         if ($event == 'transfer') {
            $this->data['##lang.resource.transfertitle##'] = __('Transfer report of the human resource', 'resources');

            $this->data['##resource.login##'] = "";
            $this->data['##resource.email##'] = "";

            $restrict = ["itemtype"                      => 'User',
                         "plugin_resources_resources_id" => $this->obj->getField("id")];
            $items    = $dbu->getAllDataFromTable("glpi_plugin_resources_resources_items", $restrict);
            if (!empty($items)) {
               foreach ($items as $item) {
                  $user = new User();
                  $user->getFromDB($item["items_id"]);
                  $this->data['##resource.login##'] = $user->fields["name"];
                  $this->data['##resource.email##'] = $user->getDefaultEmail();
               }
            }

            $this->data['##lang.resource.login##'] = __('Login');

            $this->data['##lang.resource.transfer##']     = __('Informations of the created user', 'resources');
            $this->data['##lang.resource.datetransfer##'] = __('Transfer Date');
            $this->data['##resource.datetransfer##']      = Html::convDate(date("Y-m-d"));

            $this->data['##lang.resource.email##'] = __('Email');

            $this->data['##lang.resource.informationtitle##'] = __('Additional informations', 'resources');

            $PluginResourcesReportConfig = new PluginResourcesReportConfig();
            $PluginResourcesReportConfig->getFromDB($options['reports_id']);

            $this->data['##lang.resource.informations##'] = _n('Information', 'Informations', 2);
            $information                                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br>", $PluginResourcesReportConfig->fields['information']));
            $this->data['##resource.informations##']      = Html::clean(nl2br($information));

            $this->data['##lang.resource.commentaires##'] = __('Comments');
            $commentaire                                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br>", $PluginResourcesReportConfig->fields['comment']));
            $this->data['##resource.commentaires##']      = Html::clean(nl2br($commentaire));

            $this->data['##lang.resource.targetentity##'] = __('Target entity', 'resources');
            $this->data['##lang.resource.sourceentity##'] = __('Source entity', 'resources');

            $entity = new Entity();
            if ($entity->getFromDB($options['target_entity'])) {
               $this->data['##resource.targetentity##'] = $entity->fields['name'];
            }
            if ($entity->getFromDB($options['source_entity'])) {
               $this->data['##resource.sourceentity##'] = $entity->fields['name'];
            }
         }

         if ($event == 'newresting'
             || $event == 'updateresting'
             || $event == 'deleteresting'
         ) {

            $this->data['##lang.resource.restingtitle##'] = _n('Non contract period management', 'Non contract periods management', 1, 'resources');

            $this->data['##lang.resource.resting##']      = __('Detail of non contract period', 'resources');
            $this->data['##lang.resource.datecreation##'] = __('Creation date');
            $this->data['##resource.datecreation##']      = Html::convDate(date("Y-m-d"));

            $PluginResourcesResourceResting = new PluginResourcesResourceResting();
            $PluginResourcesResourceResting->getFromDB($options['resting_id']);

            $this->data['##lang.resource.location##'] = __('Agency concerned', 'resources');
            $this->data['##resource.location##']      = Dropdown::getDropdownName('glpi_locations',
                                                                                  $PluginResourcesResourceResting->fields['locations_id']);

            $this->data['##lang.resource.home##'] = __('At home', 'resources');
            $this->data['##resource.home##']      = Dropdown::getYesNo($PluginResourcesResourceResting->fields['at_home']);

            $this->data['##lang.resource.datebegin##'] = __('Begin date');
            $this->data['##resource.datebegin##']      = Html::convDate($PluginResourcesResourceResting->fields['date_begin']);

            $this->data['##lang.resource.dateend##'] = __('End date');
            $this->data['##resource.dateend##']      = Html::convDate($PluginResourcesResourceResting->fields['date_end']);

            $this->data['##lang.resource.informationtitle##'] = __('Additional informations', 'resources');

            $this->data['##lang.resource.commentaires##'] = __('Comments');
            $commentaire                                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br>", $PluginResourcesResourceResting->fields['comment']));
            $this->data['##resource.commentaires##']      = Html::clean(nl2br($commentaire));

            $this->data['##lang.resource.openby##'] = __('Reported by', 'resources');
            $this->data['##resource.openby##']      = Html::clean($dbu->getUserName(Session::getLoginUserID()));

            if (isset($options['oldvalues']) && !empty($options['oldvalues'])) {
               $this->target_object->oldvalues = $options['oldvalues'];
            }
         }

         if ($event == 'newholiday'
             || $event == 'updateholiday'
             || $event == 'deleteholiday'
         ) {

            $this->data['##lang.resource.holidaytitle##'] = __('Forced holiday management', 'resources');

            $this->data['##lang.resource.holiday##']      = __('Detail of the forced holiday', 'resources');
            $this->data['##lang.resource.datecreation##'] = __('Creation date');
            $this->data['##resource.datecreation##']      = Html::convDate(date("Y-m-d"));

            $PluginResourcesResourceHoliday = new PluginResourcesResourceHoliday();
            $PluginResourcesResourceHoliday->getFromDB($options['holiday_id']);

            $this->data['##lang.resource.datebegin##'] = __('Begin date');
            $this->data['##resource.datebegin##']      = Html::convDate($PluginResourcesResourceHoliday->fields['date_begin']);

            $this->data['##lang.resource.dateend##'] = __('End date');
            $this->data['##resource.dateend##']      = Html::convDate($PluginResourcesResourceHoliday->fields['date_end']);

            $this->data['##lang.resource.informationtitle##'] = __('Additional informations', 'resources');

            $this->data['##lang.resource.commentaires##'] = __('Comments');
            $commentaire                                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br>", $PluginResourcesResourceHoliday->fields['comment']));
            $this->data['##resource.commentaires##']      = Html::clean(nl2br($commentaire));

            $this->data['##lang.resource.openby##'] = __('Reported by', 'resources');
            $this->data['##resource.openby##']      = Html::clean($dbu->getUserName(Session::getLoginUserID()));

            if (isset($options['oldvalues']) && !empty($options['oldvalues'])) {
               $this->target_object->oldvalues = $options['oldvalues'];
            }
         }

         //old values infos
         if (isset($this->target_object->oldvalues)
             && !empty($this->target_object->oldvalues)
             && ($event == 'update'
                 || $event == 'updateresting'
                 || $event == 'updateholiday')
         ) {

            $this->data['##lang.update.title##'] = __('Modified fields', 'resources');

            $tmp = [];

            if (isset($this->target_object->oldvalues['name'])) {
               if (empty($this->target_object->oldvalues['name'])) {
                  $tmp['##update.name##'] = "---";
               } else {
                  $tmp['##update.name##'] = $this->target_object->oldvalues['name'];
               }
            }
            if (isset($this->target_object->oldvalues['firstname'])) {
               if (empty($this->target_object->oldvalues['firstname'])) {
                  $tmp['##update.firstname##'] = "---";
               } else {
                  $tmp['##update.firstname##'] = $this->target_object->oldvalues['firstname'];
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_contracttypes_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_contracttypes_id'])) {
                  $tmp['##update.type##'] = "---";
               } else {
                  $tmp['##update.type##'] = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                                      $this->target_object->oldvalues['plugin_resources_contracttypes_id']);
               }
            }

            if (isset($this->target_object->oldvalues['users_id'])) {
               if (empty($this->target_object->oldvalues['users_id'])) {
                  $tmp['##update.users##'] = "---";
               } else {
                  $tmp['##update.users##'] = Html::clean($dbu->getUserName($this->target_object->oldvalues['users_id']));
               }
            }

            if (isset($this->target_object->oldvalues['users_id_sales'])) {
               if (empty($this->target_object->oldvalues['users_id_sales'])) {
                  $tmp['##update.userssale##'] = "---";
               } else {
                  $tmp['##update.userssale##'] = Html::clean($dbu->getUserName($this->target_object->oldvalues['users_id_sales']));
               }
            }

            if (isset($this->target_object->oldvalues['users_id_recipient'])) {
               if (empty($this->target_object->oldvalues['users_id_recipient'])) {
                  $tmp['##update.usersrecipient##'] = "---";
               } else {
                  $tmp['##update.usersrecipient##'] = Html::clean($dbu->getUserName($this->target_object->oldvalues['users_id_recipient']));
               }
            }

            if (isset($this->target_object->oldvalues['date_declaration'])) {
               if (empty($this->target_object->oldvalues['date_declaration'])) {
                  $tmp['##update.datedeclaration##'] = "---";
               } else {
                  $tmp['##update.datedeclaration##'] = Html::convDate($this->target_object->oldvalues['date_declaration']);
               }
            }

            if (isset($this->target_object->oldvalues['date_begin'])) {
               if (empty($this->target_object->oldvalues['date_begin'])) {
                  $tmp['##update.datebegin##'] = "---";
               } else {
                  $tmp['##update.datebegin##'] = Html::convDate($this->target_object->oldvalues['date_begin']);
               }
            }

            if (isset($this->target_object->oldvalues['date_end'])) {
               if (empty($this->target_object->oldvalues['date_end'])) {
                  $tmp['##update.dateend##'] = "---";
               } else {
                  $tmp['##update.dateend##'] = Html::convDate($this->target_object->oldvalues['date_end']);
               }
            }

            if (isset($this->target_object->oldvalues['quota'])) {
               if (empty($this->target_object->oldvalues['quota'])) {
                  $tmp['##update.quota##'] = "---";
               } else {
                  $tmp['##update.quota##'] = $this->target_object->oldvalues['quota'];
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_departments_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_departments_id'])) {
                  $tmp['##update.department##'] = "---";
               } else {
                  $tmp['##update.department##'] = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                                            $this->target_object->oldvalues['plugin_resources_departments_id']);
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_resourcestates_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_resourcestates_id'])) {
                  $tmp['##update.status##'] = "---";
               } else {
                  $tmp['##update.status##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                                        $this->target_object->oldvalues['plugin_resources_resourcestates_id']);
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_resourcesituations_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_resourcesituations_id'])) {
                  $tmp['##update.situation##'] = "---";
               } else {
                  $tmp['##update.situation##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcesituations',
                                                                           $this->target_object->oldvalues['plugin_resources_resourcesituations_id']);
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_contractnatures_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_contractnatures_id'])) {
                  $tmp['##update.contractnature##'] = "---";
               } else {
                  $tmp['##update.contractnature##'] = Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
                                                                                $this->target_object->oldvalues['plugin_resources_contractnatures_id']);
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_ranks_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_ranks_id'])) {
                  $tmp['##update.rank##'] = "---";
               } else {
                  $tmp['##update.rank##'] = Dropdown::getDropdownName('glpi_plugin_resources_ranks',
                                                                      $this->target_object->oldvalues['plugin_resources_ranks_id']);
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_resourcespecialities_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_resourcespecialities_id'])) {
                  $tmp['##update.speciality##'] = "---";
               } else {
                  $tmp['##update.speciality##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
                                                                            $this->target_object->oldvalues['plugin_resources_resourcespecialities_id']);
               }
            }

            if (isset($this->target_object->oldvalues['locations_id'])) {
               if (empty($this->target_object->oldvalues['locations_id'])) {
                  $tmp['##update.location##'] = "---";
               } else {
                  $tmp['##update.location##'] = Dropdown::getDropdownName('glpi_locations',
                                                                          $this->target_object->oldvalues['locations_id']);
               }
            }

            if (isset($this->target_object->oldvalues['comment'])) {
               if (empty($this->target_object->oldvalues['comment'])) {
                  $tmp['##update.comment##'] = "---";
               } else {
                  $comment                   = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $this->target_object->oldvalues['comment']));
                  $tmp['##update.comment##'] = Html::clean($comment);
               }
            }

            if (isset($this->target_object->oldvalues['users_id_recipient_leaving'])) {
               if (empty($this->target_object->oldvalues['users_id_recipient_leaving'])) {
                  $tmp['##update.usersleaving##'] = "---";
               } else {
                  $tmp['##update.usersleaving##'] = Html::clean($dbu->getUserName($this->target_object->oldvalues['users_id_recipient_leaving']));
               }
            }

            if (isset($this->target_object->oldvalues['date_declaration_leaving'])) {
               if (empty($this->target_object->oldvalues['date_declaration_leaving'])) {
                  $tmp['##update.datedeclarationleaving##'] = "---";
               } else {
                  $tmp['##update.datedeclarationleaving##'] = Html::convDateTime($this->obj->getField('date_declaration_leaving'));
               }
            }

            if (isset($this->target_object->oldvalues['is_leaving'])) {
               if (empty($this->target_object->oldvalues['is_leaving'])) {
                  $tmp['##update.leaving##'] = "---";
               } else {
                  $tmp['##update.leaving##'] = Dropdown::getYesNo($this->target_object->oldvalues['is_leaving']);
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_leavingreasons_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_leavingreasons_id'])) {
                  $tmp['##update.leavingreason##'] = "---";
               } else {
                  $tmp['##update.leavingreason##'] = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
                                                                               $this->target_object->oldvalues['plugin_resources_leavingreasons_id']);
               }
            }

            if (isset($this->target_object->oldvalues['plugin_resources_leavingreasons_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_leavingreasons_id'])) {
                  $tmp['##update.leavingreason##'] = "---";
               } else {
                  $tmp['##update.leavingreason##'] = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
                                                                               $this->target_object->oldvalues['plugin_resources_leavingreasons_id']);
               }
            }

            if (isset($this->target_object->oldvalues['is_helpdesk_visible'])) {
               if (empty($this->target_object->oldvalues['is_helpdesk_visible'])) {
                  $tmp['##update.helpdesk##'] = "---";
               } else {
                  $tmp['##update.helpdesk##'] = Dropdown::getYesNo($this->target_object->oldvalues['is_helpdesk_visible']);
               }
            }

            if (isset($this->target_object->oldvalues['at_home'])) {
               if (empty($this->target_object->oldvalues['at_home'])) {
                  $tmp['##update.home##'] = "---";
               } else {
                  $tmp['##update.home##'] = Dropdown::getYesNo($this->target_object->oldvalues['at_home']);
               }
            }

            $this->data['updates'][] = $tmp;
         }

         //task infos
         $restrict = ["plugin_resources_resources_id" => $this->obj->getField('id'),
                      "is_deleted"                    => 0];

         if (isset($options['tasks_id']) && is_array($options['tasks_id'])) {
            $restrict += ["id" =>  $options['tasks_id']];
         } else if (isset($options['tasks_id']) && $options['tasks_id']) {
            $restrict += ["id" => $options['tasks_id']];
         }
         $restrict += ["ORDER" => "name DESC"];
         $tasks    = $dbu->getAllDataFromTable('glpi_plugin_resources_tasks', $restrict);

         $this->data['##lang.task.title##'] = __('Associated tasks', 'resources');

         $this->data['##lang.task.name##']      = __('Name');
         $this->data['##lang.task.type##']      = __('Type');
         $this->data['##lang.task.users##']     = __('Technician');
         $this->data['##lang.task.groups##']    = __('Group');
         $this->data['##lang.task.datebegin##'] = __('Begin date');
         $this->data['##lang.task.dateend##']   = __('End date');
         $this->data['##lang.task.planned##']   = __('Used for planning', 'resources');
         $this->data['##lang.task.realtime##']  = __('Effective duration', 'resources');
         $this->data['##lang.task.finished##']  = __('Carried out task', 'resources');
         $this->data['##lang.task.comment##']   = __('Description');

         foreach ($tasks as $task) {
            $tmp = [];

            $tmp['##task.name##']   = $task['name'];
            $tmp['##task.type##']   = Dropdown::getDropdownName('glpi_plugin_resources_tasktypes',
                                                                $task['plugin_resources_tasktypes_id']);
            $tmp['##task.users##']  = Html::clean($dbu->getUserName($task['users_id']));
            $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                                $task['groups_id']);
            $restrict               = ["plugin_resources_tasks_id" => $task['id']];
            $plans                  = $dbu->getAllDataFromTable("glpi_plugin_resources_taskplannings", $restrict);

            if (!empty($plans)) {
               foreach ($plans as $plan) {
                  $tmp['##task.datebegin##'] = Html::convDateTime($plan["begin"]);
                  $tmp['##task.dateend##']   = Html::convDateTime($plan["end"]);
               }
            } else {
               $tmp['##task.datebegin##'] = '';
               $tmp['##task.dateend##']   = '';
            }
            $tmp['##task.planned##']  = '';
            $tmp['##task.finished##'] = Dropdown::getYesNo($task['is_finished']);
            $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
            $comment                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $task['comment']));
            $tmp['##task.comment##']  = Html::clean($comment);

            $this->data['tasks'][] = $tmp;
         }
      }
   }

   /**
    * @return array|void
    */
   function getTags() {

      $tags = ['resource.id'                => 'ID',
               'resource.name'              => __('Surname'),
               'resource.entity'            => __('Entity'),
               'resource.action'            => __('List of not finished tasks', 'resources'),
               'resource.firstname'         => __('First name'),
               'resource.type'              => PluginResourcesContractType::getTypeName(1),
               'resource.quota'             => __('Quota', 'resources'),
               'resource.situation'         => PluginResourcesResourceSituation::getTypeName(1),
               'resource.contractnature'    => PluginResourcesContractNature::getTypeName(1),
               'resource.rank'              => PluginResourcesRank::getTypeName(1),
               'resource.speciality'        => PluginResourcesResourceSpeciality::getTypeName(1),
               'resource.users'             => __('Resource manager', 'resources'),
               'resource.usersrecipient'    => __('Requester'),
               'resource.datedeclaration'   => __('Request date'),
               'resource.datebegin'         => __('Arrival date', 'resources'),
               'resource.dateend'           => __('Departure date', 'resources'),
               'resource.department'        => PluginResourcesDepartment::getTypeName(1),
               'resource.status'            => PluginResourcesResourceState::getTypeName(1),
               'resource.location'          => __('Location'),
               'resource.restingtitle'      => __('Non contract period management', 'resources'),
               'resource.resting'           => __('Detail of non contract period', 'resources'),
               'resource.comment'           => __('Description'),
               'resource.usersleaving'      => __('Informant of leaving', 'resources'),
               'resource.leaving'           => __('Declared as leaving', 'resources'),
               'resource.leavingreason'     => PluginResourcesLeavingReason::getTypeName(1),
               'resource.sensitizesecurity' => __('Sensitized to security', 'resources'),
               'resource.readchart'         => __('Reading the security charter', 'resources'),
               'resource.helpdesk'          => __('Associable to a ticket'),
               'resource.action_user'       => __('Last updater'),
               'resource.holidaytitle'      => __('Forced holiday management', 'resources'),
               'resource.holiday'           => __('Detail of the forced holiday', 'resources'),
               'update.name'                => __('Surname'),
               'update.firstname'           => __('First name'),
               'update.type'                => PluginResourcesContractType::getTypeName(1),
               'update.quota'               => __('Quota', 'resources'),
               'update.situation'           => PluginResourcesResourceSituation::getTypeName(1),
               'update.contractnature'      => PluginResourcesContractNature::getTypeName(1),
               'update.rank'                => PluginResourcesRank::getTypeName(1),
               'update.speciality'          => PluginResourcesResourceSpeciality::getTypeName(1),
               'update.users'               => __('Resource manager', 'resources'),
               'update.usersrecipient'      => __('Requester'),
               'update.datedeclaration'     => __('Request date'),
               'update.datebegin'           => __('Arrival date', 'resources'),
               'update.dateend'             => __('Departure date', 'resources'),
               'update.department'          => PluginResourcesDepartment::getTypeName(1),
               'update.status'              => PluginResourcesResourceState::getTypeName(1),
               'update.location'            => __('Location'),
               'update.comment'             => __('Description'),
               'update.usersleaving'        => __('Informant of leaving', 'resources'),
               'update.leaving'             => __('Declared as leaving', 'resources'),
               'update.leavingreason'       => PluginResourcesLeavingReason::getTypeName(1),
               'update.helpdesk'            => __('Associable to a ticket'),
               'task.name'                  => __('Name'),
               'task.type'                  => __('Type'),
               'task.users'                 => __('Technician'),
               'task.groups'                => __('Group'),
               'task.datebegin'             => __('Begin date'),
               'task.dateend'               => __('End date'),
               'task.planned'               => __('Used for planning', 'resources'),
               'task.realtime'              => __('Effective duration', 'resources'),
               'task.finished'              => __('Carried out task', 'resources'),
               'task.comment'               => __('Description'),
               'task.resource'              => PluginResourcesResource::getTypeName(1),
               'resouce.sourceentity'       => __('Source entity', 'resources'),
               'resouce.targetentity'       => __('Target entity', 'resources')];
      foreach ($tags as $tag => $label) {
         $this->addTagToList(['tag'   => $tag, 'label' => $label,
                              'value' => true]);
      }

      $this->addTagToList(['tag'     => 'resource',
                           'label'   => __('At creation, update, removal of a resource', 'resources'),
                           'value'   => false,
                           'foreach' => true,
                           'events'  => ['new', 'update', 'delete', 'report', 'newresting', 'updateresting', 'deleteresting', 'newholiday', 'updateholiday', 'deleteholiday']]);
      $this->addTagToList(['tag'     => 'updates',
                           'label'   => __('Modified fields', 'resources'),
                           'value'   => false,
                           'foreach' => true,
                           'events'  => ['update', 'updateresting', 'updateholiday']]);
      $this->addTagToList(['tag'     => 'tasks',
                           'label'   => __('At creation, update, removal of a task', 'resources'),
                           'value'   => false,
                           'foreach' => true,
                           'events'  => ['newtask', 'updatetask', 'deletetask']]);

      $this->addTagToList(['tag'     => 'commercials',
                           'label'   => __('Resources list of commercial manager', 'resources'),
                           'value'   => false,
                           'foreach' => true,
                           'events'  => ['AlertCommercialManager']]);

      asort($this->tag_descriptions);
   }

   public static function update78() {
      global $DB;

      $template = new NotificationTemplate();
      $dbu      = new DbUtils();

      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` 
                  WHERE `itemtype`='PluginResourcesResource' AND `name` = 'Resources'";
      $result = $DB->query($query_id) or die($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Resources',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }
      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.resource.title## -  ##resource.firstname## ##resource.name##';
            $tmp['content_text']             = self::getContentTextResource();
            $tmp['content_html']             = self::getContentHtmlResource();

            $translation->add($tmp);
         }

         $notifs = [
            'New Resource'         => 'new',
            'Update Resource'      => 'update',
            'Delete Resource'      => 'delete',
            'New Resource Task'    => 'newtask',
            'Update Resource Task' => 'updatetask',
            'Delete Resource Task' => 'deletetask',
         ];

         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }
      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` 
                  WHERE `itemtype`='PluginResourcesResource' AND `name` = 'Alert Resources Tasks'";
      $result = $DB->query($query_id) or die($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Alert Resources Tasks',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##resource.action## : ##resource.entity##';
            $tmp['content_text']             = '##FOREACHtasks##
   ##lang.task.name## : ##task.name##
   ##lang.task.type## : ##task.type##
   ##lang.task.users## : ##task.users##
   ##lang.task.groups## : ##task.groups##
   ##lang.task.datebegin## : ##task.datebegin##
   ##lang.task.dateend## : ##task.dateend##
   ##lang.task.comment## : ##task.comment##
   ##lang.task.resource## : ##task.resource##
   ##ENDFOREACHtasks##';
            $tmp['content_html']             = '&lt;table class="tab_cadre" border="1" cellspacing="2" cellpadding="3"&gt;
   &lt;tbody&gt;
   &lt;tr&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.name##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.type##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.users##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.groups##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.datebegin##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.dateend##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.comment##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.resource##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##FOREACHtasks##
   &lt;tr&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.name##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.type##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.users##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.groups##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.datebegin##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.dateend##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.comment##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.resource##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##ENDFOREACHtasks##
   &lt;/tbody&gt;
   &lt;/table&gt;';

            $translation->add($tmp);
         }

         $notifs               = [
            'Alert Expired Resources Tasks' => 'AlertExpiredTasks',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event" => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }

      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` 
                   WHERE `itemtype`='PluginResourcesResource' AND `name` = 'Alert Leaving Resources'";
      $result = $DB->query($query_id) or die($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Alert Leaving Resources',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##resource.action## : ##resource.entity##';
            $tmp['content_text']             = '##FOREACHresources##
   ##lang.resource.name## : ##resource.name##
   ##lang.resource.firstname## : ##resource.firstname##
   ##lang.resource.type## : ##resource.type##
   ##lang.resource.location## : ##resource.location##
   ##lang.resource.users## : ##resource.users##
   ##lang.resource.dateend## : ##resource.dateend##
   ##ENDFOREACHresources##';
            $tmp['content_html']             = '&lt;table class="tab_cadre" border="1" cellspacing="2" cellpadding="3"&gt;
   &lt;tbody&gt;
   &lt;tr&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.name##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.firstname##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.type##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.location##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.users##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.dateend##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##FOREACHresources##
   &lt;tr&gt;
   &lt;td&gt;&lt;a href="##resource.url##"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.name##&lt;/span&gt;&lt;/a&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.firstname##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.type##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.location##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.users##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.dateend##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##ENDFOREACHresources##
   &lt;/tbody&gt;
   &lt;/table&gt;';

            $translation->add($tmp);
         }

         $notifs               = [
            'Alert Leaving Resources' => 'AlertLeavingResources',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }

      $query_id = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginResourcesResource'
                       AND `name` = 'Alert Resources Checklists'";
      $result = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Alert Resources Checklists',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##checklist.action## : ##checklist.entity##';
            $tmp['content_text']             = '##lang.checklist.title##

   ##FOREACHchecklists##
   ##lang.checklist.name## ##lang.checklist.firstname## : ##checklist.name## ##checklist.firstname##
   ##lang.checklist.datebegin## : ##checklist.datebegin##
   ##lang.checklist.dateend## : ##checklist.dateend##
   ##lang.checklist.entity## : ##checklist.entity##
   ##lang.checklist.location## : ##checklist.location##
   ##lang.checklist.type## : ##checklist.type##

   ##lang.checklist.title2## :
   ##tasklist.name##
   ##ENDFOREACHchecklists##';
            $tmp['content_html']             = '&lt;table class="tab_cadre" border="1" cellspacing="2" cellpadding="3"&gt;
   &lt;tbody&gt;
   &lt;tr bgcolor="#d9c4b8"&gt;
   &lt;th colspan="7"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: center;"&gt;##lang.checklist.title##&lt;/span&gt;&lt;/th&gt;
   &lt;/tr&gt;
   &lt;tr&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.checklist.name## ##lang.checklist.firstname##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.checklist.datebegin##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.checklist.dateend##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.checklist.entity##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.checklist.location##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.checklist.type##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.checklist.title2##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##FOREACHchecklists##
   &lt;tr&gt;
   &lt;td&gt;&lt;a href="##checklist.url##"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##checklist.name## ##checklist.firstname##&lt;/span&gt;&lt;/a&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##checklist.datebegin##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##checklist.dateend##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##checklist.entity##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##checklist.location##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##checklist.type##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
   &lt;table width="100%"&gt;
   &lt;tbody&gt;
   &lt;tr&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt; ##tasklist.name## &lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   &lt;/tbody&gt;
   &lt;/table&gt;
   &lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##ENDFOREACHchecklists##
   &lt;/tbody&gt;
   &lt;/table&gt;';
            $translation->add($tmp);
         }
         $notifs               = [
            'Alert Arrival Checklists' => 'AlertArrivalChecklists',
            'Alert Leaving Checklists' => 'AlertLeavingChecklists',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }

      $query_id = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginResourcesResource'
                       AND `name` = 'Leaving Resource'";
      $result = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Leaving Resource',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.resource.title## -  ##resource.firstname## ##resource.name##';
            $tmp['content_text']             = '##lang.resource.title2##

   ##lang.resource.url## : ##resource.url##

   ##lang.resource.entity## : ##resource.entity##
   ##IFresource.name## ##lang.resource.name## : ##resource.name##
   ##ENDIFresource.name##
   ##IFresource.firstname## ##lang.resource.firstname## : ##resource.firstname##
   ##ENDIFresource.firstname##

   ##lang.resource.badge##';
            $tmp['content_html']             = '&lt;p&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;&lt;strong&gt;##lang.resource.title2##&lt;/strong&gt;
   &lt;p&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
   &lt;strong&gt;##lang.resource.url##&lt;/strong&gt; :
   &lt;a href="##resource.url##"&gt;##resource.url##&lt;/a&gt;
   &lt;/span&gt; &lt;br /&gt;&lt;br /&gt;
   &lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
   &lt;strong&gt;##lang.resource.entity##&lt;/strong&gt; : ##resource.entity##&lt;/span&gt;
   &lt;br /&gt; ##IFresource.name##
   &lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
   &lt;strong&gt;##lang.resource.name##&lt;/strong&gt; : ##resource.name##&lt;br /&gt;
   &lt;/span&gt;##ENDIFresource.name## ##IFresource.firstname##
   &lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
   &lt;strong&gt;##lang.resource.firstname##&lt;/strong&gt; : ##resource.firstname##
   &lt;br /&gt;&lt;/span&gt;##ENDIFresource.firstname##&lt;/p&gt;
   &lt;p&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;&lt;strong&gt;##lang.resource.badge##&lt;/strong&gt;&lt;/span&gt;&lt;/p&gt;
   &lt;/span&gt;&lt;/p&gt;';

            $translation->add($tmp);
         }
         $notifs               = [
            'Leaving Resource' => 'LeavingResource',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                            "event" => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }
   }

   static function update80() {
      global $DB;

      $template = new NotificationTemplate();
      $dbu      = new DbUtils();

      $query_id = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginResourcesResource'
                       AND `name` = 'Resource Resting'";

      $result = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Resource Resting',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.resource.title## -  ##resource.firstname## ##resource.name##';
            $tmp['content_text']             = '##lang.resource.restingtitle##
##lang.resource.openby## : ##resource.openby##
##lang.resource.entity## : ##resource.entity##

##lang.resource.name## : ##resource.name##
##lang.resource.firstname## : ##resource.firstname##

##lang.resource.department## : ##resource.department##
##lang.resource.users## : ##resource.users##

##lang.resource.resting##

##lang.resource.location## : ##resource.location##
##lang.resource.home## : ##resource.home##
##lang.resource.datebegin## : ##resource.datebegin##
##lang.resource.dateend## : ##resource.dateend##

##lang.resource.commentaires## : ##resource.commentaires##

##FOREACHupdates##
##lang.update.title##

##IFupdate.datebegin####lang.resource.datebegin## : ##update.datebegin####ENDIFupdate.datebegin##
##IFupdate.dateend####lang.resource.dateend## : ##update.dateend####ENDIFupdate.dateend##
##IFupdate.location####lang.resource.location## : ##update.location###ENDIFupdate.location##
##IFupdate.home####lang.resource.home## : ##update.home####ENDIFupdate.home##
##IFupdate.comment####lang.resource.comment## : ##update.comment####ENDIFupdate.comment##
##ENDFOREACHupdates##';
            $tmp['content_html']             = self::getContentHtmlResourceResting();
            $translation->add($tmp);
         }
         $notifs               = [
            'New Resource Resting'    => 'newresting',
            'Update Resource Resting' => 'updateresting',
            'Delete Resource Resting' => 'deleteresting',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }

      $query_id = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginResourcesResource'
                             AND `name` = 'Resource Holiday'";
      $result = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Resource Holiday',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.resource.title## -  ##resource.firstname## ##resource.name##';
            $tmp['content_text']             = '##lang.resource.holidaytitle##
##lang.resource.openby## : ##resource.openby##
##lang.resource.entity## : ##resource.entity##

##lang.resource.name## : ##resource.name##
##lang.resource.firstname## : ##resource.firstname##

##lang.resource.department## : ##resource.department##
##lang.resource.users## : ##resource.users##

##lang.resource.holiday##

##lang.resource.datebegin## : ##resource.datebegin##
##lang.resource.dateend## : ##resource.dateend##

##lang.resource.commentaires## : ##resource.commentaires##

##FOREACHupdates##
##lang.update.title##

##IFupdate.datebegin####lang.resource.datebegin## : ##update.datebegin####ENDIFupdate.datebegin##
##IFupdate.dateend####lang.resource.dateend## : ##update.dateend####ENDIFupdate.dateend##
##IFupdate.comment####lang.resource.comment## : ##update.comment####ENDIFupdate.comment##
##ENDFOREACHupdates##';
            $tmp['content_html']             = self::getContentHtmlResourceHoliday();
            $translation->add($tmp);
         }

         $notifs               = [
            'New Resource Holiday'    => 'newholiday',
            'Update Resource Holiday' => 'updateholiday',
            'Delete Resource Holiday' => 'deleteholiday',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }
   }

   static function update203() {
      global $DB;

      $template = new NotificationTemplate();
      $dbu      = new DbUtils();

      $query_id = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginResourcesResource'
                       AND `name` = 'Send other resource notification'";
      $result = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Send other resource notification',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }
      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.resource.title## -  ##resource.firstname## ##resource.name##';
            $tmp['content_text']             = '
##lang.resource.openby## : ##resource.openby##
##lang.resource.entity## : ##resource.entity##

##lang.resource.name## : ##resource.name##
##lang.resource.firstname## : ##resource.firstname##

##lang.resource.department## : ##resource.department##
##lang.resource.users## : ##resource.users##

##lang.resource.commentaires## : ##resource.commentaires##';
            $tmp['content_html']             = '
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.entity##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.entity##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.openby##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.openby##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/tbody&gt;
&lt;/table&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.commentaires##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.commentaires##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/tbody&gt;
&lt;/table&gt;';
            $translation->add($tmp);
         }
         $notifs               = [
            'Other resource notification' => 'other',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }

   }

   static function update204() {
      global $DB;

      $template = new NotificationTemplate();
      $dbu      = new DbUtils();

      $query_id = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginResourcesResource'
                       AND `name` = 'Resource Transfer'";
      $result = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Resource Transfer',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.resource.transfertitle## -  ##resource.firstname## ##resource.name##';
            $tmp['content_text']             = "##lang.resource.transfertitle##
La ressource ##resource.firstname## ##resource.name## a été transférée de l\'entité ##resource.sourceentity## vers l\'entité ##resource.sourceentity##.";
            $tmp['content_html']             = "&lt;p&gt;##lang.resource.transfertitle##&lt;/p&gt;
&lt;p&gt;La ressource ##resource.firstname## ##resource.name## a été transférée de l\'entité ##resource.sourceentity## vers l\'entité ##resource.targetentity##.&lt;/p&gt;";

            $translation->add($tmp);
         }
         $notifs               = [
            'Resource Report Transfer' => 'transfer',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }
   }

   static function update231() {
      global $DB;

      $template = new NotificationTemplate();
      $dbu      = new DbUtils();

      $query_id = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginResourcesResource'
                       AND `name` = 'Alert for sales people'";
      $result = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Alert for sales people',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.commercial.title##';
            $tmp['content_text']             = '##lang.commercial.title##

##FOREACHcommercials##
##lang.resource.name## : ##resource.name##
##lang.resource.firstname## : ##resource.firstname##
##lang.resource.type## : ##resource.type##
##lang.resource.department## : ##resource.department##
##lang.resource.location## : ##resource.location##
##lang.resource.datebegin## : ##resource.datebegin##
##lang.resource.dateend## : ##resource.dateend##
##lang.resource.leaving## : ##resource.leaving##
##lang.resource.userssale## : ##resource.userssale##
##lang.resource.users## : ##resource.users##
##lang.resource.accessprofile## : ##resource.accessprofile##
##ENDFOREACHcommercials##';
            $tmp['content_html']             = '&lt;table class="tab_cadre" border="1" cellspacing="2" cellpadding="3"&gt;
&lt;tbody&gt;
&lt;tr bgcolor="#d9c4b8"&gt;
&lt;th colspan="11"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: center;"&gt;##lang.commercial.title##&lt;/span&gt;&lt;/th&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.type##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.location##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.datebegin##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.dateend##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.leaving##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.userssale##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.accessprofile##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##FOREACHcommercials##
&lt;tr&gt;
&lt;td&gt;&lt;a href="##resource.url##"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.name##&lt;/span&gt;&lt;/a&gt;&lt;/td&gt;
&lt;td&gt;&lt;a href="##resource.url##"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.firstname##&lt;/span&gt;&lt;/a&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.type##&lt;/span&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.location##&lt;/span&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.datebegin##&lt;/span&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.dateend##&lt;/span&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.leaving##&lt;/span&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.userssale##&lt;/span&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.accessprofile##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDFOREACHcommercials##
&lt;/tbody&gt;
&lt;/table&gt;';
            $translation->add($tmp);
         }

         $notifs               = [
            'Alert Commercial Manager' => 'AlertCommercialManager',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }
   }

   static function update_notif() {
      global $DB;

      $template = new NotificationTemplate();
      $dbu      = new DbUtils();

      $query_id = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginResourcesResource'
                       AND `name` = 'Resource Report Creation'";
      $result = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Resource Report Creation',
            'itemtype' => 'PluginResourcesResource',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.resource.title## -  ##resource.firstname## ##resource.name##';
            $tmp['content_text']             = '##lang.resource.creationtitle##

##lang.resource.entity## : ##resource.entity##

##lang.resource.name## : ##resource.name##
##lang.resource.firstname## : ##resource.firstname##
##lang.resource.department## : ##resource.department##
##lang.resource.location## : ##resource.location##
##lang.resource.users## : ##resource.users##
##lang.resource.usersrecipient## : ##resource.usersrecipient##
##lang.resource.datedeclaration## : ##resource.datedeclaration##
##lang.resource.datebegin## : ##resource.datebegin##

##lang.resource.creation##

##lang.resource.datecreation## : ##resource.datecreation##
##lang.resource.login## : ##resource.login##
##lang.resource.email## : ##resource.email##

##lang.resource.informationtitle##

##IFresource.commentaires####lang.resource.commentaires## : ##resource.commentaires####ENDIFresource.commentaires##

##IFresource.informations####lang.resource.informations## : ##resource.informations####ENDIFresource.informations##';
            $tmp['content_html']             = self::getContentHtmlResourceReport();
            $translation->add($tmp);
         }

         $notifs               = [
            'Resource Report Creation' => 'report',
         ];
         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginResourcesResource',
                                             "event" => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginResourcesResource',
                  'event'        => $name,
                  'comment'      => '',
                  'is_recursive' => 1,
                  'is_active'    => 1,
                  'date_mod'     => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }
   }

   public static function install() {

      self::update_notif();
      self::update78();
      self::update80();
      self::update203();
      self::update204();
      self::update231();

   }


   /**
    * @return string
    */
   static function getContentTextResource() {
      return '##lang.resource.url##  : ##resource.url##

   ##lang.resource.entity## : ##resource.entity##
   ##IFresource.name####lang.resource.name## : ##resource.name##
   ##ENDIFresource.name## ##IFresource.firstname####lang.resource.firstname## : ##resource.firstname##
   ##ENDIFresource.firstname## ##IFresource.type####lang.resource.type## : ##resource.type##
   ##ENDIFresource.type## ##IFresource.users####lang.resource.users## : ##resource.users##
   ##ENDIFresource.users## ##IFresource.usersrecipient####lang.resource.usersrecipient## : ##resource.usersrecipient##
   ##ENDIFresource.usersrecipient## ##IFresource.datedeclaration####lang.resource.datedeclaration## : ##resource.datedeclaration##
   ##ENDIFresource.datedeclaration## ##IFresource.datebegin####lang.resource.datebegin## : ##resource.datebegin##
   ##ENDIFresource.datebegin## ##IFresource.dateend####lang.resource.dateend## : ##resource.dateend##
   ##ENDIFresource.dateend## ##IFresource.department####lang.resource.department## : ##resource.department##
   ##ENDIFresource.department## ##IFresource.status####lang.resource.status## : ##resource.status##
   ##ENDIFresource.status## ##IFresource.location####lang.resource.location## : ##resource.location##
   ##ENDIFresource.location## ##IFresource.comment####lang.resource.comment## : ##resource.comment##
   ##ENDIFresource.comment## ##IFresource.usersleaving####lang.resource.usersleaving## : ##resource.usersleaving##
   ##ENDIFresource.usersleaving## ##IFresource.leaving####lang.resource.leaving## : ##resource.leaving##
   ##ENDIFresource.leaving## ##IFresource.helpdesk####lang.resource.helpdesk## : ##resource.helpdesk##
   ##ENDIFresource.helpdesk## ##FOREACHupdates##----------
   ##lang.update.title## :
   ##IFupdate.name####lang.resource.name## : ##update.name##
   ##ENDIFupdate.name## ##IFupdate.firstname####lang.resource.firstname## : ##update.firstname##
   ##ENDIFupdate.firstname## ##IFupdate.type####lang.resource.type## : ##update.type##
   ##ENDIFupdate.type## ##IFupdate.users####lang.resource.users## : ##update.users##
   ##ENDIFupdate.users## ##IFupdate.usersrecipient####lang.resource.usersrecipient## : ##update.usersrecipient##
   ##ENDIFupdate.usersrecipient## ##IFupdate.datedeclaration####lang.resource.datedeclaration## : ##update.datedeclaration##
   ##ENDIFupdate.datedeclaration## ##IFupdate.datebegin####lang.resource.datebegin## : ##update.datebegin##
   ##ENDIFupdate.datebegin## ##IFupdate.dateend####lang.resource.dateend## : ##update.dateend##
   ##ENDIFupdate.dateend## ##IFupdate.department####lang.resource.department## : ##update.department##
   ##ENDIFupdate.department## ##IFupdate.status####lang.resource.status## : ##update.status##
   ##ENDIFupdate.status## ##IFupdate.location####lang.resource.location## : ##update.location##
   ##ENDIFupdate.location## ##IFupdate.comment####lang.resource.comment## : ##update.comment##
   ##ENDIFupdate.comment## ##IFupdate.usersleaving####lang.resource.usersleaving## : ##update.usersleaving##
   ##ENDIFupdate.usersleaving## ##IFupdate.leaving####lang.resource.leaving## : ##update.leaving##
   ##ENDIFupdate.leaving## ##IFupdate.helpdesk####lang.resource.helpdesk## : ##update.helpdesk##
   ##ENDIFupdate.helpdesk## ----------##ENDFOREACHupdates##
   ##FOREACHtasks####lang.task.title## :
   ##IFtask.name####lang.task.name## : ##task.name##
   ##ENDIFtask.name## ##IFtask.type####lang.task.type## : ##task.type##
   ##ENDIFtask.type## ##IFtask.users####lang.task.users## : ##task.users##
   ##ENDIFtask.users## ##IFtask.groups####lang.task.groups## : ##task.groups##
   ##ENDIFtask.groups## ##IFtask.datebegin####lang.task.datebegin## : ##task.datebegin##
   ##ENDIFtask.datebegin## ##IFtask.dateend####lang.task.dateend## : ##task.dateend##
   ##ENDIFtask.dateend## ##IFtask.comment####lang.task.comment## : ##task.comment##
   ##ENDIFtask.comment## ##IFtask.finished####lang.task.finished## : ##task.finished##
   ##ENDIFtask.finished## ##IFtask.realtime####lang.task.realtime## : ##task.realtime##
   ##ENDIFtask.realtime## ----------##ENDFOREACHtasks## ';
   }

   /**
    * @return string
    */
   static function getContentHtmlResource() {
      return "&lt;p&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.url##
                        &lt;/strong&gt; :
                        &lt;a href=\"##resource.url##\"&gt;##resource.url##
                        &lt;/a&gt;&lt;/span&gt; &lt;br /&gt;&lt;br /&gt;
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.entity##&lt;/strong&gt; : ##resource.entity##
                        &lt;/span&gt; &lt;br /&gt; ##IFresource.name##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.name##&lt;/strong&gt; : ##resource.name##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.name## ##IFresource.firstname##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.firstname##&lt;/strong&gt; : ##resource.firstname##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.firstname## ##IFresource.type##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.type##&lt;/strong&gt; :  ##resource.type##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFresource.type## ##IFresource.status##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.status##&lt;/strong&gt; :  ##resource.status##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFresource.status## ##IFresource.users##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.users##&lt;/strong&gt; :  ##resource.users##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFresource.users## ##IFresource.usersrecipient##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.usersrecipient##
                        &lt;/strong&gt; :  ##resource.usersrecipient##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFresource.usersrecipient## ##IFresource.datedeclaration##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.datedeclaration##
                        &lt;/strong&gt; :  ##resource.datedeclaration##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFresource.datedeclaration## ##IFresource.datebegin##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.datebegin##&lt;/strong&gt; :  ##resource.datebegin##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.datebegin## ##IFresource.dateend##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.dateend##&lt;/strong&gt; :  ##resource.dateend##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.dateend## ##IFresource.department##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.department##&lt;/strong&gt; :  ##resource.department##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.department## ##IFresource.location##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.location##&lt;/strong&gt; :  ##resource.location##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.location## ##IFresource.comment##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.comment##&lt;/strong&gt; :  ##resource.comment##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.comment## ##IFresource.usersleaving##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.usersleaving##&lt;/strong&gt; :  ##resource.usersleaving##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.usersleaving## ##IFresource.leaving##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.leaving##&lt;/strong&gt; :  ##resource.leaving##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.leaving## ##IFresource.helpdesk##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.helpdesk##&lt;/strong&gt; :  ##resource.helpdesk##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFresource.helpdesk##   ##FOREACHupdates##----------
                        &lt;br /&gt;
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.update.title## :&lt;/strong&gt;&lt;/span&gt;
                        &lt;br /&gt; ##IFupdate.name##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.name##&lt;/strong&gt; : ##update.name##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFupdate.name## ##IFupdate.firstname##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.firstname##&lt;/strong&gt; : ##update.firstname##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.firstname## ##IFupdate.type##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.type##&lt;/strong&gt; : ##update.type##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFupdate.type## ##IFupdate.status##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.status##&lt;/strong&gt; : ##update.status##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFupdate.status## ##IFupdate.users##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.users##&lt;/strong&gt; : ##update.users##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFupdate.users## ##IFupdate.usersrecipient##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.usersrecipient##&lt;/strong&gt; : ##update.usersrecipient##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.usersrecipient## ##IFupdate.datedeclaration##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.datedeclaration##
                        &lt;/strong&gt; : ##update.datedeclaration##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFupdate.datedeclaration## ##IFupdate.datebegin##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.datebegin##&lt;/strong&gt; : ##update.datebegin##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.datebegin## ##IFupdate.dateend##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.dateend##&lt;/strong&gt; : ##update.dateend##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.dateend## ##IFupdate.department##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.department##&lt;/strong&gt; : ##update.department##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.department## ##IFupdate.location##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.location##&lt;/strong&gt; : ##update.location##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.location## ##IFupdate.comment##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.comment##&lt;/strong&gt; : ##update.comment##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.comment## ##IFupdate.usersleaving##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.usersleaving##
                        &lt;/strong&gt; : ##update.usersleaving##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFupdate.usersleaving## ##IFupdate.leaving##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.leaving##&lt;/strong&gt; : ##update.leaving##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.leaving## ##IFupdate.helpdesk##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.resource.helpdesk##&lt;/strong&gt; : ##update.helpdesk##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFupdate.helpdesk####ENDFOREACHupdates##   ##FOREACHtasks##----------
                        &lt;br /&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.title## :&lt;/strong&gt;&lt;/span&gt; &lt;br /&gt; ##IFtask.name##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.name##&lt;/strong&gt; : ##task.name##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.name## ##IFtask.type##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.type##&lt;/strong&gt; : ##task.type##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.type## ##IFtask.users##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.users##&lt;/strong&gt; : ##task.users##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.users## ##IFtask.groups##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.groups##&lt;/strong&gt; : ##task.groups##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.groups## ##IFtask.datebegin##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.datebegin##&lt;/strong&gt; : ##task.datebegin##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.datebegin## ##IFtask.dateend##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.dateend##&lt;/strong&gt; : ##task.dateend##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.dateend## ##IFtask.comment##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.comment##&lt;/strong&gt; : ##task.comment##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.comment## ##IFtask.finished##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.finished##&lt;/strong&gt; : ##task.finished##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.finished## ##IFtask.realtime##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.realtime##&lt;/strong&gt; : ##task.realtime##
                        &lt;/span&gt;##ENDIFtask.realtime##&lt;br /&gt;----------##ENDFOREACHtasks##&lt;/p&gt;";
   }

   /**
    * @return string
    */
   static function getContentHtmlResourceReport() {
      return '&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.resource.creationtitle##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="2" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.entity##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" colspan="2" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.entity##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.location##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.location##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.usersrecipient##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.usersrecipient##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.datedeclaration##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.datedeclaration##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.datebegin##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.datebegin##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/tbody&gt;
&lt;/table&gt;
&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.resource.creation##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.datecreation##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.datecreation##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.login##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.login##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.email##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.email##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/tbody&gt;
&lt;/table&gt;
&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.resource.informationtitle##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
##IFresource.commentaires##
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.commentaires##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.commentaires##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFresource.commentaires## ##IFresource.informations##
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.informations##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.informations##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFresource.informations##
&lt;/tbody&gt;
&lt;/table&gt;';

   }

   /**
    * @return string
    */
   static function getContentHtmlResourceResting() {
      return '&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.resource.restingtitle##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.entity##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.entity##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.openby##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.openby##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/tbody&gt;
&lt;/table&gt;
&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.resource.resting##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.location##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.location##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.home##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.home##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.datebegin##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.datebegin##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.dateend##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.dateend##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.commentaires##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.commentaires##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/tbody&gt;
&lt;/table&gt;
&lt;p&gt;##FOREACHupdates##&lt;/p&gt;
&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.update.title##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
##IFupdate.datebegin##
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.datebegin## : ##update.datebegin##
&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFupdate.datebegin## ##IFupdate.dateend##
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.dateend## : ##update.dateend##
&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFupdate.dateend## ##IFupdate.location##
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.location## : ##update.location##
&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFupdate.location## ##IFupdate.home##
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.home## : ##update.home##
&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFupdate.home## ##IFupdate.comment##
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.comment## : ##update.comment##
&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFupdate.comment##
&lt;/tbody&gt;
&lt;/table&gt;
&lt;p&gt;##ENDFOREACHupdates##&lt;/p&gt;';
   }

   /**
    * @return string
    */
   static function getContentHtmlResourceHoliday() {
      return '&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.resource.holidaytitle##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.entity##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.entity##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.openby##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.openby##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.name##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.firstname##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.department##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.users##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/tbody&gt;
&lt;/table&gt;
&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.resource.holiday##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.datebegin##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.datebegin##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.dateend##&lt;/span&gt;&lt;/td&gt;
&lt;td style="text-align: left;" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.dateend##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.commentaires##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##resource.commentaires##&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/tbody&gt;
&lt;/table&gt;
&lt;p&gt;##FOREACHupdates##&lt;/p&gt;
&lt;p style="text-align: center;"&gt;&lt;span style="font-size: 11px; font-family: verdana;"&gt;##lang.update.title##&lt;/span&gt;&lt;/p&gt;
&lt;table border="1" cellspacing="2" cellpadding="3" width="590px" align="center"&gt;
&lt;tbody&gt;
##IFupdate.datebegin##
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.datebegin## : ##update.datebegin##
&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFupdate.datebegin## ##IFupdate.dateend##
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.dateend## : ##update.dateend##
&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFupdate.dateend## ##IFupdate.comment##
&lt;tr&gt;
&lt;td style="text-align: left;" colspan="4" width="auto"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;
&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.resource.comment## : ##update.comment##
&lt;/span&gt;&lt;/span&gt;&lt;/td&gt;
&lt;/tr&gt;
##ENDIFupdate.comment##
&lt;/tbody&gt;
&lt;/table&gt;
&lt;p&gt;##ENDFOREACHupdates##&lt;/p&gt;';
   }
}

