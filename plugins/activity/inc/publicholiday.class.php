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

class PluginActivityPublicHoliday extends CommonDBTM {

   var $dohistory = false;
   static $rightname = "plugin_activity";

   // From CommonDBTM
   public $auto_message_on_action    = false;

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 1) {
      return _n('Public holiday', 'Public holidays', $nb, 'activity');
   }

    /**
    * Add items in the items fields of the parm array
    * Items need to have an unique index beginning by the begin date of the item to display
    * needed to be correcly displayed
    **/
   static function populatePlanning($options = []) {
      global $DB, $CFG_GLPI;

      $default_options = [
         'color'               => '',
         'event_type_color'    => '',
         'check_planned'       => false,
         'display_done_events' => true,
      ];
      $options = array_merge($default_options, $options);

      $interv   = [];
      $holiday = new Holiday();

      if (!isset($options['begin']) || ($options['begin'] == 'NULL')
          || !isset($options['end']) || ($options['end'] == 'NULL')) {
         return $interv;
      }

      if (!$options['display_done_events']) {
         return $interv;
      }

      $who        = $options['who'];
      $begin      = $options['begin'];
      $end        = $options['end'];

      $holiday = new PluginActivityHoliday();
      $holiday->setHolidays();
      $holidays = $holiday->getHolidays();

      if (!empty($holidays)) {
         foreach ($holidays as $k => $holiday) {
            if ($holiday['is_perpetual'] == 1) {
               for ($i = 0; $i <= 100; $i++) {
                  $annee_courante = strftime("%Y") - 10;
                  $year[$i]       = ($annee_courante + $i);
                  $hb             = date('m-d', strtotime($holiday['begin']));
                  $he             = date('m-d', strtotime($holiday['end']));
                  $begin          = $year[$i] . '-' . $hb . ' 00:00:00';
                  $end            = $year[$i] . '-' . $he . ' 00:00:00';

                  if(($options['begin'] <= $begin && $begin <= $options['end'])
                     || ($options['begin'] <= $end && $end <= $options['end'])){
                     $key                              = $begin . "$$" . "PluginActivityPublicHoliday" . $k;
                     $interv[$key]['color']            = $options['color'];
                     $interv[$key]['event_type_color'] = $options['event_type_color'];
                     $interv[$key]["itemtype"]         = 'PluginActivityPublicHoliday';
                     $interv[$key]["id"]               = $k;
                     $interv[$key]["users_id"]         = 0;
                     $interv[$key]["begin"]            = $begin;
                     $interv[$key]["end"]              = $end;
                     $interv[$key]["name"]             = self::getTypeName(1);
                     $interv[$key]["content"]          = " ";
                     $interv[$key]["editable"]         = false;
                  }
               }

            } else {

               $begin                            = $holiday['begin'] . ' 00:00:00';
               $end                              = $holiday['end'] . ' 00:00:00';
               if(($options['begin'] <= $begin && $begin <= $options['end'])
                  || ($options['begin'] <= $end && $end <= $options['end'])) {
                  $key                              = $begin . "$$" . "PluginActivityPublicHoliday" . $k;
                  $interv[$key]['color']            = $options['color'];
                  $interv[$key]['event_type_color'] = $options['event_type_color'];
                  $interv[$key]["itemtype"]         = 'PluginActivityPublicHoliday';
                  $interv[$key]["id"]               = $k;
                  $interv[$key]["users_id"]         = 0;
                  $interv[$key]["begin"]            = $begin;
                  $interv[$key]["end"]              = $end;
                  $interv[$key]["name"]             = self::getTypeName(1);
                  $interv[$key]["content"]          = " ";
                  $interv[$key]["editable"]         = false;
               }
            }
         }
      }

      return $interv;

   }

   /**
    * Display a Planning Item
    *
    * @param $parm Array of the item to display
    * @return Nothing (display function)
    **/
   static function displayPlanningItem(array $val, $who, $type = "", $complete = 0) {

      $html = "";
      $rand     = mt_rand();

      if ($val["name"]) {
         $html .= $val["name"]."<br>";
      }

      if ($val["end"]) {
         $html .= "<strong>".__('End date')."</strong> : ".Html::convdatetime($val["end"])."<br>";
      }

      if ($complete) {
         $html.= "<div class='event-description'>".$val["content"]."</div>";
      } else {
         $html.= Html::showToolTip($val["content"],
                                   ['applyto' => "cri_".$val["id"].$rand,
                                         'display' => false]);
      }

      return $html;
   }

}

