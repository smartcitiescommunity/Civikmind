<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Tasklists plugin for GLPI
 Copyright (C) 2003-2016 by the Tasklists Development Team.

 https://github.com/InfotelGLPI/tasklists
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Tasklists.

 Tasklists is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Tasklists is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Tasklists. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginTasklistsNotificationTargetTask
 */
class PluginTasklistsNotificationTargetTask extends NotificationTarget {

   const TASK_USER  = 6303;
   const TASK_GROUP = 6304;
   const TASK_REQUESTER = 6305;

   /**
    * Return main notification events for the object type
    * Internal use only => should use getAllEvents
    *
    * @return array array which contains : event => event label
    */
   function getEvents() {

      return ['newtask'    => __('A task has been added', 'tasklists'),
              'updatetask' => __('A task has been updated', 'tasklists'),
              'deletetask' => __('A task has been removed', 'tasklists'),
              //              'alerttasks'      => __('Reminder of unfinished tasks', 'tasklists')
      ];
   }

   /**
    * Get additionnals targets for Tickets
    *
    * @param string $event
    */
   function addAdditionalTargets($event = '') {

      if ($event == 'newtask'
          || $event == 'updatetask'
          || $event == 'deletetask') {
         $this->addTarget(self::TASK_USER, _n('User', 'Users', 1));
         $this->addTarget(self::TASK_GROUP, _n('Group', 'Groups', 1));
         $this->addTarget(self::TASK_REQUESTER, _n('Requester', 'Requesters', 1));
      }
   }

   /**
    * Add targets by a method not defined in NotificationTarget (specific to an itemtype)
    *
    * @param array $data Data
    * @param array $options Options
    *
    * @return void
    **/
   function addSpecificTargets($data, $options) {

      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case self::TASK_USER :
            $this->getUserAddress();
            break;
         case self::TASK_GROUP :
            $this->getGroupAddress();
            break;
         case self::TASK_REQUESTER :
            $this->getRequesterAddress();
            break;
      }
   }


   //Get recipient
   function getUserAddress() {
      return $this->addUserByField("users_id");
   }
   function getRequesterAddress() {
      return $this->addUserByField("users_id_requester");
   }


   function getGroupAddress() {
      global $DB;

      $group_field = "groups_id";

      if (isset($this->obj->fields[$group_field])
          && $this->obj->fields[$group_field] > 0) {

         $criteria                                         = $this->getDistinctUserCriteria() + $this->getProfileJoinCriteria();
         $criteria['FROM']                                 = User::getTable();
         $criteria['LEFT JOIN']                            = ['glpi_groups_users' => ['ON' => ['glpi_groups_users' => 'users_id',
                                                                                               'glpi_users'        => 'id']]];
         $criteria['WHERE']['glpi_groups_users.groups_id'] = $this->obj->fields[$group_field];
         $iterator                                         = $DB->request($criteria);

         while ($data = $iterator->next()) {
            //Add the user email and language in the notified users list
            $this->addToRecipientsList($data);
         }
      }
   }


   /**
    * Get all data needed for template processing
    * Provides minimum information for alerts
    * Can be overridden by each NotificationTartget class if needed
    *
    * @param string $event Event name
    * @param array  $options Options
    *
    * @return void
    **/
   function addDataForTemplate($event, $options = []) {
      global $CFG_GLPI;

      $dbu = new DbUtils();
      //      if ($event == 'alerttasks') {
      //
      //         $this->data['##resource.entity##']      =
      //            Dropdown::getDropdownName('glpi_entities',
      //                                      $options['entities_id']);
      //         $this->data['##lang.resource.entity##'] = __('Entity');
      //         $this->data['##resource.action##']      = __('List of not finished tasks', 'resources');
      //
      //         $this->data['##lang.task.name##']      = __('Name');
      //         $this->data['##lang.task.type##']      = __('Type');
      //         $this->data['##lang.task.users##']     = __('Technician');
      //         $this->data['##lang.task.groups##']    = __('Group');
      //         $this->data['##lang.task.datebegin##'] = __('Begin date');
      //         $this->data['##lang.task.dateend##']   = __('End date');
      //         $this->data['##lang.task.planned##']   = __('Used for planning', 'resources');
      //         $this->data['##lang.task.realtime##']  = __('Effective duration', 'resources');
      //         $this->data['##lang.task.finished##']  = __('Carried out task', 'resources');
      //         $this->data['##lang.task.comment##']   = __('Comments');
      //         $this->data['##lang.task.resource##']  = PluginResourcesResource::getTypeName(1);
      //
      //         foreach ($options['tasks'] as $id => $task) {
      //            $tmp = [];
      //
      //            $tmp['##task.name##']   = $task['name'];
      //            $tmp['##task.type##']   = Dropdown::getDropdownName('glpi_plugin_resources_tasktypes',
      //                                                                $task['plugin_resources_tasktypes_id']);
      //            $tmp['##task.users##']  = Html::clean($dbu->getUserName($task['users_id']));
      //            $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
      //                                                                $task['groups_id']);
      //            $restrict               = ["plugin_resources_tasks_id" => $task['id']];
      //            $plans                  = $dbu->getAllDataFromTable("glpi_plugin_resources_taskplannings", $restrict);
      //
      //            if (!empty($plans)) {
      //               foreach ($plans as $plan) {
      //                  $tmp['##task.datebegin##'] = Html::convDateTime($plan["begin"]);
      //                  $tmp['##task.dateend##']   = Html::convDateTime($plan["end"]);
      //               }
      //            } else {
      //               $tmp['##task.datebegin##'] = '';
      //               $tmp['##task.dateend##']   = '';
      //            }
      //
      //            $tmp['##task.planned##']  = '';
      //            $tmp['##task.finished##'] = Dropdown::getYesNo($task['is_finished']);
      //            $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
      //            $comment                  = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $task['comment']));
      //            $tmp['##task.comment##']  = Html::clean($comment);
      //            $tmp['##task.resource##'] = Dropdown::getDropdownName('glpi_plugin_resources_resources',
      //                                                                  $task['plugin_resources_resources_id']);
      //
      //            $this->data['tasks'][] = $tmp;
      //         }
      //      } else {

      $events = $this->getAllEvents();

      $this->data['##lang.task.title##'] = $events[$event];

      $this->data['##lang.task.name##']        = __('Name');
      $this->data['##lang.task.client##']      = __('Entity');
      $this->data['##lang.task.type##']        = __('Type');
      $this->data['##lang.task.users##']       = _n('User', 'Users', 1);
      $this->data['##lang.task.requester##']       = _n('Requester', 'Requesters', 1);
      $this->data['##lang.task.groups##']      = _n('Group', 'Groups', 1);
      $this->data['##lang.task.actiontime##']  = __('Planned duration');
      $this->data['##lang.task.percentdone##'] = __('Percent done');
      $this->data['##lang.task.duedate##']     = __('Due date', 'tasklists');
      $this->data['##lang.task.comment##']     = __('Description');
      $this->data['##lang.task.priority##']    = __('Priority');
      $this->data['##lang.task.status##']      = __('Status');
      $this->data['##lang.task.otherclient##'] = __('Other client', 'tasklists');

      $this->data['##task.name##'] = $this->obj->getField("name");
      $entity_name                 = __('None');
      $entity                      = new Entity();
      if ($entity->getFromDB($this->obj->getField('entities_id'))) {
         $entity_name = $entity->fields['name'];
      }
      $this->data['##task.client##']      = $entity_name;
      $this->data['##task.type##']        = Dropdown::getDropdownName('glpi_plugin_tasklists_tasktypes',
                                                                      $this->obj->getField('plugin_tasklists_tasktypes_id'));
      $this->data['##task.users##']       = Html::clean($dbu->getUserName($this->obj->getField("users_id")));
      $this->data['##task.requester##']       = Html::clean($dbu->getUserName($this->obj->getField("users_id_requester")));
      $this->data['##task.groups##']      = Dropdown::getDropdownName('glpi_groups',
                                                                      $this->obj->getField("groups_id"));
      $this->data['##task.actiontime##']  = Html::timestampToString($this->obj->getField('actiontime'), false, true);
      $this->data['##task.percentdone##'] = Dropdown::getValueWithUnit($this->obj->getField('percent_done'), "%");
      $this->data['##task.duedate##']     = Html::convDate($this->obj->getField('due_date'));
      $comment                            = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $this->obj->getField("comment")));
      $this->data['##task.comment##']     = Html::clean($comment);
      $this->data['##task.priority##']    = CommonITILObject::getPriorityName($this->obj->getField("priority"));
      $this->data['##task.status##']      = PluginTasklistsTask::getStateName($this->obj->getField('plugin_tasklists_taskstates_id'));
      $this->data['##task.otherclient##'] = $this->obj->getField("client");

      $this->data['##lang.task.url##'] = __('URL');
      $this->data['##task.url##']      = urldecode($CFG_GLPI["url_base"] . "/index.php?redirect=PluginTasklistsTask_" . $this->obj->getField("id"));

   }

   /**
    * @return array|void
    */
   function getTags() {

      $tags = ['task.id'          => 'ID',
               'task.name'        => __('Name'),
               'task.type'        => __('Type'),
               'task.users'       => _n('User', 'Users', 1),
               'task.requester'       => _n('Requester', 'Requesters', 1),
               'task.groups'      => _n('Group', 'Groups', 1),
               'task.actiontime'  => __('Planned duration'),
               'task.percentdone' => __('Percent done'),
               'task.duedate'     => __('Due date', 'tasklists'),
               'task.comment'     => __('Description'),
               'task.priority'    => __('Priority'),
               'task.state'       => __('Status'),
               'task.otherclient' => __('Other client', 'tasklists'),
      ];
      foreach ($tags as $tag => $label) {
         $this->addTagToList(['tag'   => $tag, 'label' => $label,
                              'value' => true]);
      }

      $this->addTagToList(['tag'     => 'tasks',
                           'label'   => __('At creation, update, removal of a task', 'tasklists'),
                           'value'   => false,
                           'foreach' => true,
                           'events'  => ['newtask', 'updatetask', 'deletetask']]);

      asort($this->tag_descriptions);
   }

   public static function install140() {
      global $DB;

      $template = new NotificationTemplate();
      $dbu      = new DbUtils();

      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` 
                  WHERE `itemtype`='PluginTasklistsTask' AND `name` = 'Tasks'";
      $result = $DB->query($query_id) or die($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp          = [
            'name'     => 'Tasks',
            'itemtype' => 'PluginTasklistsTask',
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
            $tmp['subject']                  = '##lang.task.title## - ##task.name##';
            $tmp['content_text']             = self::getContentText();
            $tmp['content_html']             = self::getContentHtml();

            $translation->add($tmp);
         }

         $notifs = [
            'New Task'    => 'newtask',
            'Update Task' => 'updatetask',
            'Delete Task' => 'deletetask',
         ];

         $notification         = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginTasklistsTask',
                                             "event"    => $name])) {
               $tmp             = [
                  'name'         => $label,
                  'entities_id'  => 0,
                  'itemtype'     => 'PluginTasklistsTask',
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
      //      $query_id = "SELECT `id` FROM `glpi_notificationtemplates`
      //                  WHERE `itemtype`='PluginTasklistsTask' AND `name` = 'Alert not finished Tasks'";
      //      $result = $DB->query($query_id) or die($DB->error());
      //
      //      if ($DB->numrows($result) > 0) {
      //         $templates_id = $DB->result($result, 0, 'id');
      //      } else {
      //         $tmp          = [
      //            'name'     => 'Alert not finished Tasks',
      //            'itemtype' => 'PluginTasklistsTask',
      //            'date_mod' => $_SESSION['glpi_currenttime'],
      //            'comment'  => '',
      //            'css'      => '',
      //         ];
      //         $templates_id = $template->add($tmp);
      //      }
      //
      //      if ($templates_id) {
      //         $translation = new NotificationTemplateTranslation();
      //         if (!$dbu->countElementsInTable($translation->getTable(),
      //                                         ["notificationtemplates_id" => $templates_id])) {
      //            $tmp['notificationtemplates_id'] = $templates_id;
      //            $tmp['language']                 = '';
      //            $tmp['subject']                  = '##resource.action## : ##resource.entity##';
      //            $tmp['content_text']             = '##FOREACHtasks##
      //   ##lang.task.name## : ##task.name##
      //   ##lang.task.type## : ##task.type##
      //   ##lang.task.users## : ##task.users##
      //   ##lang.task.groups## : ##task.groups##
      //   ##lang.task.datebegin## : ##task.datebegin##
      //   ##lang.task.dateend## : ##task.dateend##
      //   ##lang.task.comment## : ##task.comment##
      //   ##lang.task.resource## : ##task.resource##
      //   ##ENDFOREACHtasks##';
      //            $tmp['content_html']             = '&lt;table class="tab_cadre" border="1" cellspacing="2" cellpadding="3"&gt;
      //   &lt;tbody&gt;
      //   &lt;tr&gt;
      //   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.name##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.type##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.users##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.groups##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.datebegin##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.dateend##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.comment##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.task.resource##&lt;/span&gt;&lt;/td&gt;
      //   &lt;/tr&gt;
      //   ##FOREACHtasks##
      //   &lt;tr&gt;
      //   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.name##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.type##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.users##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.groups##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.datebegin##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.dateend##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.comment##&lt;/span&gt;&lt;/td&gt;
      //   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##task.resource##&lt;/span&gt;&lt;/td&gt;
      //   &lt;/tr&gt;
      //   ##ENDFOREACHtasks##
      //   &lt;/tbody&gt;
      //   &lt;/table&gt;';
      //
      //            $translation->add($tmp);
      //         }
      //      }
   }


   public static function install() {

      self::install140();

   }


   /**
    * @return string
    */

   static function getContentText() {
      return '##lang.task.url##  : ##task.url##

   ##lang.task.client## : ##task.client##
   ##IFtask.name####lang.task.name## : ##task.name##
   ##ENDIFtask.name## ##IFtask.type####lang.task.type## : ##task.type##
   ##ENDIFtask.type## ##IFtask.users####lang.task.users## : ##task.users##
   ##ENDIFtask.users## ##lang.task.status## : ##task.status##
   ##IFtask.comment####lang.task.comment## : ##task.comment##
   ##ENDIFtask.comment## ';
   }

   /**
    * @return string
    */

   static function getContentHtml() {
      return "&lt;p&gt;&lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.url##
                        &lt;/strong&gt; :
                        &lt;a href=\"##task.url##\"&gt;##task.url##
                        &lt;/a&gt;&lt;/span&gt; &lt;br /&gt;&lt;br /&gt;
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.client##&lt;/strong&gt; : ##task.client##
                        &lt;/span&gt; &lt;br /&gt; ##IFtask.name##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.name##&lt;/strong&gt; : ##task.name##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFtask.name## ##IFtask.type##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.type##&lt;/strong&gt; :  ##task.type##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.type## 
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.status##&lt;/strong&gt; :  ##task.status##&lt;br /&gt;
                        &lt;/span&gt; ##IFtask.users##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.users##&lt;/strong&gt; :  ##task.users##&lt;br /&gt;
                        &lt;/span&gt;##ENDIFtask.users## ##IFtask.comment##
                        &lt;span style=\"font-family: Verdana; font-size: 11px; text-align: left;\"&gt;
                        &lt;strong&gt;##lang.task.comment##&lt;/strong&gt; :  ##task.comment##
                        &lt;br /&gt;&lt;/span&gt;##ENDIFtask.comment##";
   }
}

