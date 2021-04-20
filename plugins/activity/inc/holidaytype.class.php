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

/// PluginActivityHolidayType class
class PluginActivityHolidayType extends CommonDropdown {

   var $can_be_translated  = true;
   static $rightname = "dropdown";

   const RTT = 'RT';
   const CP = 'CP';

   static function canCreate() {
      return Session::haveRight('plugin_activity', CREATE)
               && Session::haveRight("plugin_activity_all_users", 1);
   }

   //static function canView() {
   //   return Session::haveRight('plugin_activity', CREATE);
   //}

   static function getTypeName($nb = 0) {
      return _n('Holiday type', 'Holiday types', $nb, 'activity');
   }

   function getAdditionalFields() {

      return [
         [
            'name'  => 'short_name',
            'label' => __('Short name', 'activity'),
            'type'  => 'text',
            'list'  => true],
         [
            'name'  => 'mandatory_comment',
            'label' => __('Mandatory comment', 'activity'),
            'type'  => 'bool',
            'list'  => true],
         [
            'name'  => 'auto_validated',
            'label' => __('Auto-validated', 'activity'),
            'type'  => 'bool',
            'list'  => true],
         [
            'name'  => 'is_holiday',
            'label' => __('Holiday', 'activity'),
            'type'  => 'bool',
            'list'  => true],
         [
            'name'  => 'is_holiday_counter',
            'label' => __('Use this holiday in the counter', 'activity'),
            'type'  => 'bool',
            'list'  => true],
         [
            'name'  => 'is_sickness',
            'label' => __('Sickness', 'activity'),
            'type'  => 'bool',
            'list'  => true],
         [
            'name'  => 'is_part_time',
            'label' => __('Part time', 'activity'),
            'type'  => 'bool',
            'list'  => true],
         [
            'name'  => 'is_period',
            'label' => __('Linked to a period', 'activity'),
            'type'  => 'bool',
            'list'  => true],
      ];
   }

   function prepareInputForAdd($input) {
      parent::prepareInputForAdd($input);
      if (isset($input['is_holiday'])
              && isset($input['is_sickness'])
              && isset($input['is_part_time'])) {
         if (($input['is_holiday']+$input['is_sickness']+$input['is_part_time']) > 1) {
            Session::addMessageAfterRedirect(__("Can't be of more than one type at the same time", "activity"), false, ERROR);
            return false;
         }
      }

      return $input;
   }

   function prepareInputForUpdate($input) {
      parent::prepareInputForUpdate($input);
      if (isset($input['is_holiday'])
              && isset($input['is_sickness'])
              && isset($input['is_part_time'])) {
         if (($input['is_holiday']+$input['is_sickness']+$input['is_part_time']) > 1) {
            Session::addMessageAfterRedirect(__("Can't be of more than one type at the same time", "activity"), false, ERROR);
            return false;
         }
      }

      return $input;
   }

   static function isPeriod($holiday_type_id) {
      $holidaytype = new PluginActivityHolidayType();
      $holidaytype->getFromDB($holiday_type_id);
      if ($holidaytype->fields['is_period']) {
         return true;
      }
      return false;
   }
}