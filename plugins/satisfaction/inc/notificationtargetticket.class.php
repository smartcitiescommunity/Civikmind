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
 * Class PluginSatisfactionNotificationTargetTicket
 */
class PluginSatisfactionNotificationTargetTicket extends NotificationTarget {

   function getEvents(){
      return ["survey_reminder" => __('Survey Reminder', 'satisfaction')];
   }

   function getDatasForTemplate($event, $options = []) {
   }

   function getSpecificTargets($data, $options) {

   }

   public static function sendReminder($tickets_id){

      $ticketDBTM = new Ticket();
      if($ticketDBTM->getFromDB($tickets_id)){
         NotificationEvent::raiseEvent("survey_reminder", $ticketDBTM);
      }
   }

   function getTags() {
      $notification_target_ticket = new NotificationTargetTicket();
      $notification_target_ticket->getTags();
      $this->tag_descriptions = $notification_target_ticket->tag_descriptions;
   }

   function getDatasForObject(CommonDBTM $item, array $options, $simple=false) {
      $notification_target_ticket = new NotificationTargetTicket();
      $data = $notification_target_ticket->getDataForObject($item, $options, $simple);
      return $data;
   }

   static function install(){

      $notificationTemplateDBTM = new NotificationTemplate();
      if(!$notificationTemplateDBTM->getFromDBByCrit(['name' => 'Ticket Satisfaction Reminder'])) {
         $notificationTemplateId = $notificationTemplateDBTM->add([
            'name'     => "Ticket Satisfaction Reminder",
            'itemtype' => 'Ticket',
            'comment'  => "Created by the plugin satisfaction"
         ]);
      }

      $notificationDBTM = new Notification();
      if(!$notificationDBTM->getFromDBByCrit(['name' => 'Ticket Satisfaction Reminder'])){
         $notifications_id   = $notificationDBTM->add([
            'name'                     => "Ticket Satisfaction Reminder",
            'entities_id'              => 0,
            'is_recursive'             => 1,
            'is_active'                => 1,
            'itemtype'                 => 'Ticket',
            'event'                    => "survey_reminder",
            'comment'                  => "Created by the plugin Satisfaction"
         ]);
      }
   }

   static function uninstall(){
      global $DB;

      $notificationDBTM = new Notification();
      $notificationDBTM->getFromDBByCrit(['event'=>'survey-reminder']);

      $notification_notificationTemplate = new Notification_NotificationTemplate();

      if($notification_notificationTemplate->find(['notifications_id' => $notificationDBTM->getID()])){
         $DB->query("DELETE FROM glpi_notificationtemplatetranslations
                     WHERE notificationtemplates_id = " . $notification_notificationTemplate->getField('notificationtemplates_id'));

         $DB->query("DELETE FROM glpi_notificationtargets
                     WHERE notifications_id = " . $notificationDBTM->getID());

         $DB->query("DELETE FROM glpi_notifications_notificationtemplates
                     WHERE id = " . $notification_notificationTemplate->getField('notificationtemplates_id'));

         $DB->query("DELETE FROM glpi_notificationtemplates
                     WHERE id = " . $notification_notificationTemplate->getField('notificationtemplates_id'));

         $DB->query("DELETE FROM glpi_notifications
                     WHERE id = " . $notificationDBTM->getID());
      }
   }
}