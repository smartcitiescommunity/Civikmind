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

class PluginActivityHolidayPeriod extends CommonDropdown {

   var $can_be_translated  = true;

   static function getTypeName($nb = 0) {
      return _n('Holiday period', 'Holiday periods', $nb, 'activity');
   }


   function getAdditionalFields() {

      return [
         [
            'name'  => 'short_name',
            'label' => __('Short name', 'activity'),
            'type'  => 'text',
            'list'  => true],
         [
            'name'  => 'begin',
            'label' => __('Begin date'),
            'type'  => 'date',
            'list'  => true],
         [
            'name'  => 'end',
            'label' => __('End date'),
            'type'  => 'date',
            'list'  => true],
         [
            'name'  => 'archived',
            'label' => __('Archived', 'activity'),
            'type'  => 'bool',
            'list'  => true],
      ];
   }

   function prepareInputForAdd($input) {

      if (!isset($input['begin']) || $input['begin'] == "") {
         Session::addMessageAfterRedirect(__('Please fill a begin date', 'activity'), false, ERROR);
         return false;
      }
      if (!isset($input['end']) || $input['end'] == "") {
         Session::addMessageAfterRedirect(__('Please fill an end date', 'activity'), false, ERROR);
         return false;
      }

      return $input;
   }

}