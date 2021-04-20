<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginActivityNotificationTargetHoliday extends NotificationTarget {

   const HOLIDAY_VALIDATOR = 4498;
   const HOLIDAY_REQUESTER = 4499;

   function getEvents() {
      return ['newvalidation'    => __('New personal holiday request', 'activity'),
              'answervalidation' => __('Answer to a personal holiday request', 'activity')];
   }

   /**
    * Get additionnals targets for holiday
    */
   function addAdditionalTargets($event = '') {
      $this->addTarget(PluginActivityNotificationTargetHoliday::HOLIDAY_REQUESTER, _n('Requester', 'Requesters', 1));
      $this->addTarget(PluginActivityNotificationTargetHoliday::HOLIDAY_VALIDATOR, __('Approvers', 'activity'));
   }

   function addSpecificTargets($data, $options) {
      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case PluginActivityNotificationTargetHoliday::HOLIDAY_REQUESTER:
            return $this->getUserAddress();
            break;
         case PluginActivityNotificationTargetHoliday::HOLIDAY_VALIDATOR:
            return $this->getValidatorAddress();
            break;
      }
   }

   //Get recipient
   function getUserAddress() {
      global $DB;

      $query = " SELECT DISTINCT `glpi_useremails`.`email`
                  FROM `glpi_plugin_activity_holidays`,`glpi_useremails`
                  WHERE `glpi_useremails`.`users_id` = `glpi_plugin_activity_holidays`.`users_id` 
                  AND `glpi_useremails`.`is_default` = 1
                  AND  `glpi_plugin_activity_holidays`.`id` ='" . $this->obj->fields["id"] . "'";

      $result       = $DB->query($query);
      $res['email'] = $DB->result($result, 0, 'email');

      $this->addToRecipientsList($res);
   }

   function getValidatorAddress() {
      global $DB;

      $query = "SELECT DISTINCT `glpi_users`.`id` AS id
                FROM `glpi_plugin_activity_holidayvalidations`
                LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_plugin_activity_holidayvalidations`.`users_id_validate`)
                WHERE `glpi_plugin_activity_holidayvalidations`.`plugin_activity_holidays_id` = '" . $this->obj->fields["id"] . "'";

      foreach ($DB->request($query) as $data) {
         $data['email'] = UserEmail::getDefaultForUser($data['id']);
         $this->addToRecipientsList($data);
      }
   }

   function addDataForTemplate($event, $options = []) {
      global $CFG_GLPI, $DB;

      $dbu    = new DbUtils();
      $AllDay = PluginActivityReport::getAllDay();

      $holidayValidation = new PluginActivityHolidayValidation();
      $holidayValidation->getFromDB($options['plugin_activity_holidayvaldiations_id']);

      $holidayType = new PluginActivityHolidayType();
      $holidayType->getFromDB($this->obj->getField('plugin_activity_holidaytypes_id'));

      switch ($this->obj->getField('global_validation')) {
         case PluginActivityCommonValidation::ACCEPTED:
            $this->data['##lang.activity.title##'] = __('Validation of your holiday request', 'activity');
            break;
         case PluginActivityCommonValidation::REFUSED:
            $this->data['##lang.activity.title##'] = __('Refusal of your holiday request', 'activity');
            break;
         case PluginActivityCommonValidation::WAITING:
         default:
            $this->data['##lang.activity.title##'] = __('A new holiday request has been submitted', 'activity');
            break;
      }

      $this->data['##lang.holiday.status##'] = __('Approval');
      $this->data['##holiday.status##']      = PluginActivityCommonValidation::getStatus($this->obj->getField('global_validation'));

      $this->data['##lang.holiday.name##'] = __('Title');
      $this->data['##holiday.name##']      = $this->obj->getField("name");

      $this->data['##lang.holiday.applicant.name##'] = __('Complete name');
      $this->data['##holiday.applicant.name##']      = $dbu->getUserName($this->obj->getField('users_id'));

      $this->data['##lang.holiday.begin.date##'] = __('Begin date');
      $this->data['##holiday.begin.date##']      = Html::convDateTime($this->obj->getField("begin"));

      $this->data['##lang.holiday.end.date##'] = __('End date');
      $this->data['##holiday.end.date##']      = Html::convDateTime($this->obj->getField("end"));

      $actionTime                            = $this->obj->getField('actiontime');
      $nbDays                                = $actionTime / $AllDay;
      $this->data['##lang.holiday.nbdays##'] = __('Number of days', 'activity');
      $this->data['##holiday.nbdays##']      = $nbDays;

      $this->data['##lang.holiday.date.submission##'] = sprintf(__('%1$s %2$s'), __('Date'), __('Request'));
      $this->data['##holiday.date.submission##']      = Html::convDateTime($holidayValidation->fields["submission_date"]);

      $this->data['##lang.holiday.date.validation##'] = sprintf(__('%1$s %2$s'), __('Date'), __('Validation'));
      $this->data['##holiday.date.validation##']      = Html::convDateTime($holidayValidation->fields["validation_date"]);

      $this->data['##lang.holiday.commentvalidation##'] = sprintf(__('%1$s: %2$s'), _n('Approval', 'Approvals', 1), __('Comments'));
      $this->data['##holiday.commentvalidation##']      = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $holidayValidation->fields["comment_validation"]));

      if (isset($this->obj->fields['comment'])) {
         $this->data['##lang.holiday.commentrequest##'] = sprintf(__('%1$s: %2$s'), __('Request'), __('Comments'));
         $comment                                       = stripslashes(str_replace(['\r\n', '\n', '\r'], "<br/>", $this->obj->fields['comment']));
         $this->data['##holiday.commentrequest##']      = $comment;
      }

      $this->data['##lang.holiday.holidaytype##'] = _n('Holiday type', 'Holiday types', 1, 'activity');
      $this->data['##holiday.holidaytype##']      = $holidayType->getField("name");

      $this->data['##lang.activity.url##'] = "URL";
      $this->data['##activity.url##']      = urldecode($CFG_GLPI["url_base"] . "/index.php?redirect=PluginActivityHoliday_" .
                                                       $this->obj->getField("id"));

      //      $this->data['##activity.url##'] = urldecode($CFG_GLPI["url_base"]."/plugins/activity/ajax/generateTXTFile.php?holidays_id=".
      //                                 $holiday->getField("id"));

      $this->data['##holidayvalidation.id##'] = sprintf("%07d", $this->obj->getField("id"));
      $this->data['##holiday.id##']           = sprintf("%07d", $this->obj->getField("id"));

      //comment infos
      $restrict = ["plugin_activity_holidays_id" => $this->obj->getField('id')];

//      $order    = " ORDER BY `validation_date` DESC";
      $dbu      = new DbUtils();
      $comments = $dbu->getAllDataFromTable('glpi_plugin_activity_holidayvalidations', $restrict, false);

      $this->data['##lang.comment.name##']        = __('Name');
      $this->data['##lang.comment.author##']      = __('Writer');
      $this->data['##lang.comment.datecomment##'] = __('Date');
      $this->data['##lang.comment.comment##']     = __('Content');

      foreach ($comments as $comment) {
         $tmp = [];

         $tmp['##comment.name##']        = $comment['comment_validation'];
         $tmp['##comment.author##']      = Html::clean($dbu->getUserName($comment['users_id_validate']));
         $tmp['##comment.datecomment##'] = Html::convDateTime($comment['validation_date']);
         $tmp['##comment.comment##']     = nl2br($comment['comment_validation']);

         $this->data['comments'][] = $tmp;
      }
   }

   function getTags() {

      $tags = ['activity.title'            => __('Title'),
               'holiday.name'              => __('Name'),
               'holiday.applicant.name'    => __('Writer'),
               'holiday.end.date'          => __('End date'),
               'holiday.begin.date'        => __('Begin date'),
               'holiday.nbdays'            => __('Number of days', 'activity'),
               'holiday.date.validation'   => sprintf(__('%1$s %2$s'), __('Date'), __('Validation')),
               'holiday.date.submission'   => sprintf(__('%1$s %2$s'), __('Date'), __('Request')),
               'holiday.commentvalidation' => sprintf(__('%1$s: %2$s'), _n('Approval', 'Approvals', 1), __('Comments')),
               'holiday.commentrequest'    => sprintf(__('%1$s: %2$s'), __('Request'), __('Comments'))];

      foreach ($tags as $tag => $label) {
         $this->addTagToList(['tag'   => $tag,
                              'label' => $label,
                              'value' => true]);
      }

      $this->addTagToList(['tag'     => 'activity',
                           'label'   => __('New personal holiday request', 'activity'),
                           'value'   => false,
                           'foreach' => true,
                           'events'  => ['newvalidation']]);

      $this->addTagToList(['tag'     => 'activity',
                           'label'   => __('Answer to a personal holiday request', 'activity'),
                           'value'   => false,
                           'foreach' => true,
                           'events'  => ['answervalidation']]);

      asort($this->tag_descriptions);
   }
}