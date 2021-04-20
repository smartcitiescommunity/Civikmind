<?php
/*
 *
 -------------------------------------------------------------------------
 Plugin GLPI News
 Copyright (C) 2015 by teclib.
 http://www.teclib.com
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Plugin GLPI News.
 Plugin GLPI News is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Plugin GLPI News is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Plugin GLPI News. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginNewsAlert_Target extends CommonDBTM {
   static $rightname = 'reminder_public';

   static function getTypeName($nb = 0) {
      return _n('Target', 'Targets', $nb);
   }

   static function canDelete() {
      return self::canUpdate();
   }

   static function canPurge() {
      return self::canUpdate();
   }

   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'items_id':
            if (isset($values['itemtype'])
                && is_subclass_of($values['itemtype'], 'CommonDBTM')) {
               $item = new $values['itemtype'];
               if ($values['itemtype'] == "Profile"
                   && $values['items_id'] == -1) {
                  return $item->getTypeName()." - ".__('All');
               }
               $item->getFromDB($values['items_id']);
               return $item->getTypeName()." - ".$item->getName();
            }
            break;

      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item instanceof PluginNewsAlert) {
         $nb = countElementsInTable(
            self::getTable(),
            ['plugin_news_alerts_id' => $item->getID()]
         );
         return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item instanceof PluginNewsAlert) {
         self::showForAlert($item);
      }
   }

   static function showForAlert(PluginNewsAlert $alert) {
      global $CFG_GLPI;

      $rand = mt_rand();

      echo "<form method='post' action='".Toolbox::getItemTypeFormURL('PluginNewsAlert')."'>";
      echo "<input type='hidden' name='plugin_news_alerts_id' value='".$alert->getID()."'>";

      $types = ['Group', 'Profile', 'User'];
      echo "<table class='plugin_news_alert-visibility'>";
      echo "<tr>";
      echo "<td>";
      echo __('Add a target').":&nbsp;";
      $addrand = Dropdown::showItemTypes('itemtype', $types, ['width' => '']);
      echo "</td>";
      $params  = ['type'         => '__VALUE__',
                  'entities_id'  => $alert->fields['entities_id'],
                  'is_recursive' => $alert->fields['is_recursive']
                  ];
      Ajax::updateItemOnSelectEvent("dropdown_itemtype".$addrand, "visibility$rand",
                                    Plugin::getWebDir('news')."/ajax/targets.php",
                                    $params);
      echo "<td>";
      echo "<span id='visibility$rand'></span>";
      echo "</td>";
      echo "<tr>";
      echo "</table>";
      Html::closeForm();

      echo "<div class='spaced'>";
      $target       = new self();
      $found_target = $target->find(['plugin_news_alerts_id' => $alert->getID()]);
      if ($nb = count($found_target) > 0) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams
            = ['num_displayed'    => $nb,
               'container'        => 'mass'.__CLASS__.$rand,
               'specific_actions' => ['delete' => _x('button', 'Delete permanently')]
               ];
         Html::showMassiveActions($massiveactionparams);

         echo "<table class='tab_cadre_fixehov'>";

         echo "<tr>";
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
         echo "<th>".__('Type')."</th>";
         echo "<th>".__('Recipient')."</th>";
         echo "</tr>";

         foreach ($found_target as $current_target) {
            if (class_exists($current_target['itemtype'])) {
               $item = new $current_target['itemtype'];
               $item->getFromDB($current_target['items_id']);
               $name = ($current_target['items_id'] == -1
                        && $current_target['itemtype'] == "Profile")
                           ?__('All')
                           :$item->getName(['complete' => true]);

               echo "<tr class='tab_bg_2'>";
               echo "<td>";
                     Html::showMassiveActionCheckBox(__CLASS__, $current_target["id"]);
                     echo "</td>";
               echo "<td>".$item->getTypeName()."</td>";
               echo "<td>$name</td>";
               echo "</tr>";
            }
         }

         echo "</table>";

         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";

      return true;
   }
}
