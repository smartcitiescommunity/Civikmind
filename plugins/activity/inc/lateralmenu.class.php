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

class PluginActivityLateralmenu extends CommonDBTM {

   static function showMenu() {
      global $CFG_GLPI;

      $listActions = array_merge(PluginActivityPlanningExternalEvent::getActionsOn(), PluginActivityHoliday::getActionsOn());

      $types = [
         PluginActivityActions::ADD_ACTIVITY,
         PluginActivityActions::HOLIDAY_REQUEST,
         PluginActivityActions::CRA,
         PluginActivityActions::APPROVE_HOLIDAYS
      ];

      foreach ($listActions as $key => $action) {
         if (in_array($key, $types) && $action['rights']) {
            echo "<tr class='tab_bg_1'>";
            echo "<td  colspan='2' class='small no-wrap'>";
            echo "<a href=\"" . $action['link'] . "\"  class='plugin_activity_button'";
            if (isset($action['onclick']) && !empty($action['onclick'])) {
               echo "onclick=\"" . $action['onclick'] . "\"";
            }
            echo ">";
            echo $action['label'];
            echo "</a>";
            echo "</td>";
            echo "</tr>";
         }
      }

      Ajax::createIframeModalWindow('holiday',
         $CFG_GLPI["root_doc"] . "/plugins/activity/front/holiday.form.php",
         ['title'         => __('Create a holiday request', 'activity'),
            'reloadonclose' => false,
            'width'         => 1180,
            'height'        => 500,
         ]);

      if (Session::haveRight("plugin_activity_can_requestholiday", 1)) {
         $holiday = new PluginActivityHoliday();
         $hcount  = new PluginActivityHolidayCount();

         $periods = $hcount->getCurrentPeriods();

         if (count($periods) > 0) {
            $nbHolidays = $holiday->countNbHoliday(Session::getLoginUserID(true), $periods, PluginActivityCommonValidation::ACCEPTED);

            if (!empty($nbHolidays[PluginActivityReport::$HOLIDAY]['total'])
               || !empty($nbHolidays[PluginActivityReport::$SICKNESS]['total'])
               || !empty($nbHolidays[PluginActivityReport::$PART_TIME]['total'])) {
               echo "<tr class='tab_bg_1'><td colspan='2'><b>" . __('Holidays detail', 'activity') . " : </b></td></tr>";
               $holiday->showHolidayDetailsByType($nbHolidays);
            }

            $nbHolidays = $holiday->countNbHoliday(Session::getLoginUserID(true), $periods, PluginActivityCommonValidation::WAITING);

            if (!empty($nbHolidays[PluginActivityReport::$HOLIDAY]['total'])
               || !empty($nbHolidays[PluginActivityReport::$SICKNESS]['total'])
               || !empty($nbHolidays[PluginActivityReport::$PART_TIME]['total'])) {
               echo "<tr class='tab_bg_1'><td colspan='2'><b>" . __('Holidays detail in progress', 'activity') . " : </b></td></tr>";
               $holiday->showHolidayDetailsByType($nbHolidays);
            }
            $nbHolidays = $holiday->countNbHolidayByPeriod(Session::getLoginUserID(true), $periods);

            $hcount->showHolidayDetailsByPeriod($nbHolidays);
         }
      }
   }
}