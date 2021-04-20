<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 badges plugin for GLPI
 Copyright (C) 2009-2016 by the badges Development Team.

 https://github.com/InfotelGLPI/badges
 -------------------------------------------------------------------------

 LICENSE

 This file is part of badges.

 badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginBadgesConfig
 */
class PluginBadgesConfig extends CommonDBTM {

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return string|translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'CronTask' && $item->getField('name') == "BadgesAlert") {
         return __('Plugin Setup', 'badges');
      }
      return '';
   }


   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType() == 'CronTask') {

         $target = PLUGINBADGES_DIR . "/front/notification.state.php";
         PluginBadgesBadge::configCron($target);
      }
      return true;
   }

   /**
    * @param $target
    * @param $ID
    */
   function showForm($target, $ID) {

      if (!$this->getFromDB($ID)) {
         $this->getEmpty();
      }

      $delay_expired     = $this->fields["delay_expired"];
      $delay_whichexpire = $this->fields["delay_whichexpire"];

      $date_expired     = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $delay_expired, date("y")));
      $date_whichexpire = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $delay_whichexpire, date("y")));

      echo "<div align='center'>";
      echo "<form method='post' action=\"$target\">";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>";
      echo __('Time of checking of validity of the badges', 'badges');
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Badges expired for more than ', 'badges');
      echo "</td>";
      echo "<td>";
      echo "&nbsp;<input type='text' size='15' name='delay_expired' value=\"$delay_expired\">";
      echo "&nbsp;" . _n('Day', 'Days', 2) . " ( > " . Html::convDate($date_expired) . ")<br>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Badges expiring in less than ', 'badges');
      echo "</td>";
      echo "<td>";
      echo "&nbsp;<input type='text' size='15' name='delay_whichexpire' value=\"$delay_whichexpire\">";
      echo "&nbsp;" . _n('Day', 'Days', 2) . " ( < " . Html::convDate($date_whichexpire) . ")<br>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='center' colspan='4'>";
      echo Html::hidden('id', ['value' => $ID]);
      echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }

   /**
    * @param $target
    * @param $ID
    */
   public function showFormBadgeReturn($target, $ID) {

      $this->getFromDB($ID);

      echo "<div align='center'>";
      echo "<form method='post' action=\"$target\">";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>";
      echo __('Time of checking of validity of the badges', 'badges');
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Badge return delay', 'badges') . "&nbsp;";
      echo "</td>";
      echo "<td>";
      Dropdown::showTimeStamp("delay_returnexpire", ['min'             => DAY_TIMESTAMP,
                                                          'max'             => 52 * WEEK_TIMESTAMP,
                                                          'step'            => DAY_TIMESTAMP,
                                                          'value'           => $this->fields["delay_returnexpire"],
                                                          'addfirstminutes' => true,
                                                          'inhours'         => false]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='center' colspan='4'>";
      echo Html::hidden('id', ['value' => $ID]);
      echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
}
