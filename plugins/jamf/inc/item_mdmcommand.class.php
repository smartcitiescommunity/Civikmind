<?php

/*
 * -------------------------------------------------------------------------
 * JAMF plugin for GLPI
 * Copyright (C) 2019-2020 by Curtis Conard
 * https://github.com/cconard96/jamf
 * -------------------------------------------------------------------------
 * LICENSE
 * This file is part of JAMF plugin for GLPI.
 * JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * JAMF plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

/**
 * JSS Item_MDMCommand class
 *
 * @since 1.1.0
 */
class PluginJamfItem_MDMCommand extends CommonDBTM {

   static public $rightname = 'plugin_jamf_mdmcommand';

   public static function getTypeName($nb = 0)
   {
      return _nx('itemtype', 'MDM command', 'MDM commands', $nb, 'jamf');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      $jamf_class = PluginJamfAbstractDevice::getJamfItemClassForGLPIItem($item::getType(), $item->getID());
      if ($jamf_class !== PluginJamfMobileDevice::class || !PluginJamfMobileDevice::canView()) {
         return false;
      }
      return self::getTypeName(2);
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      return self::showForItem($item);
   }

   /**
    * @param PluginJamfMobileDevice $mobiledevice
    * @return array
    * @since 1.0.0
    */
   public static function getApplicableCommands(PluginJamfMobileDevice $mobiledevice) {
      if (PluginJamfUser_JSSAccount::hasLink()) {
         $allcommands = PluginJamfMDMCommand::getAvailableCommands();
         $device_data = $mobiledevice->getJamfDeviceData();

         foreach ($allcommands as $command => &$params) {
            if (isset($params['requirements'])) {
               // Note: Costs are based on the number of DB or API calls. Checks should always be done least to most expensive.
               // DB call: 1 cost, API call: 2 cost
               // Check supervised - Cost: 0
               if (isset($params['requirements']['supervised']) &&
                  $params['requirements']['supervised'] != $device_data['supervised']) {
                  unset($allcommands[$command]);
                  continue;
               }

               // Check managed - Cost: 0
               if (isset($params['requirements']['managed']) &&
                  $params['requirements']['managed'] != $device_data['managed']) {
                  unset($allcommands[$command]);
                  continue;
               }

               // Check lost status - Cost: 0
               if (isset($params['requirements']['lostmode'])) {
                  $req_value = $params['requirements']['lostmode'];
                  $value = $mobiledevice->fields['lost_mode_enabled'];

                  if ($value !== 'true' && $value !== 'false') {
                     unset($allcommands[$command]);
                     continue;
                  }

                  if ($req_value && $value !== 'true') {
                     unset($allcommands[$command]);
                     continue;
                  }

                  if (!$req_value && $value !== 'false') {
                     unset($allcommands[$command]);
                     continue;
                  }
               }

               // Test device type requirements - Cost: 2
               if (isset($params['requirements']['devicetypes']) && !empty($params['requirements']['devicetypes']) &&
                  !array_key_exists('mobiledevice', $params['requirements']['devicetypes']) &&
                  !in_array('mobiledevice', $params['requirements']['devicetypes'], true)) {
                  $specifictype = $mobiledevice->getSpecificType();
                  if (!array_key_exists($specifictype, $params['requirements']['devicetypes']) &&
                     !in_array($specifictype, $params['requirements']['devicetypes'], true)) {
                     unset($allcommands[$command]);
                     continue;
                  }
               }

               // Test user JSS account rights - Cost: 2
               if (isset($params['jss_right']) && !empty($params['jss_right']) && !PluginJamfMDMCommand::canSend($command)) {
                  unset($allcommands[$command]);
                  continue;
               }
            }
         }
         return $allcommands;
      }
      return [];
   }

   public static function showForItem(CommonDBTM $item)
   {
      if (!PluginJamfMobileDevice::canView() || !static::canView()) {
         return false;
      }

      $mobiledevice = PluginJamfMobileDevice::getJamfItemForGLPIItem($item);
      if ($mobiledevice === null) {
         return false;
      }

      $commands = self::getApplicableCommands($mobiledevice);

      echo Html::hidden('itemtype', ['value' => $item->getType()]);
      echo Html::hidden('items_id', ['value' => $item->getID()]);
      echo "<div class='mdm-button-group'>";
      foreach ($commands as $command => $params) {
         $title = $params['name'];
         $icon = $params['icon'] ?? '';
         $icon_color = $params['icon_color'] ?? 'inherit';
         $onclick = "jamfPlugin.onMDMCommandButtonClick('$command', event)";
         echo "<div class='mdm-button' onclick=\"$onclick\"><i class='$icon' style='color: $icon_color'></i>$title</div>";
      }
      echo "</div>";

      $item_commands = $mobiledevice->getMDMCommands();

      echo "<h3>" . _x('form_section', 'Pending Commands', 'jamf') . "</h3>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<thead>";
      echo "<th>"._x('field', 'Command', 'jamf')."</th>";
      echo "<th>"._x('field', 'Status', 'jamf')."</th>";
      echo "<th>"._x('field', 'Date issued', 'jamf')."</th>";
      echo "<th>"._x('field', 'Date of last push', 'jamf')."</th>";
      echo "<th>"._x('field', 'Username', 'jamf')."</th>";
      echo "</thead>";
      echo "<tbody>";
      foreach ($item_commands['pending'] as $entry) {
         $last_push = $entry['date_time_failed'] ?? '';
         $username = $entry['username'] ?? '';
         $issued = $entry['date_time_issued'];
         echo "<tr><td>{$entry['name']}</td><td>{$entry['status']}</td><td>{$issued}</td><td>{$last_push}</td><td>{$username}</td></tr>";
      }
      echo "</tbody>";
      echo "</table>";

      echo "<h3>" ._x('form_section', 'Failed Commands', 'jamf') . "</h3>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<thead>";
      echo "<th>"._x('field', 'Command', 'jamf')."</th>";
      echo "<th>"._x('field', 'Error', 'jamf')."</th>";
      echo "<th>"._x('field', 'Date issued', 'jamf')."</th>";
      echo "<th>"._x('field', 'Date of last push', 'jamf')."</th>";
      echo "<th>"._x('field', 'Username', 'jamf')."</th>";
      echo "</thead>";
      echo "<tbody>";
      foreach ($item_commands['failed'] as $entry) {
         $last_push = $entry['date_time_failed'];
         $username = $entry['username'] ?? '';
         $issued = $entry['date_time_issued'];
         echo "<tr><td>{$entry['name']}</td><td>{$entry['error']}</td><td>{$issued}</td><td>{$last_push}</td><td>{$username}</td></tr>";
      }
      echo "</tbody>";
      echo "</table>";
      $commands_json = json_encode($commands, JSON_FORCE_OBJECT);
      $device_data = $mobiledevice->getJamfDeviceData();
      $jamf_id = $device_data['jamf_items_id'];
      $itemtype = 'MobileDevice';
      $items_id = $mobiledevice->getID();
      $js = <<<JAVASCRIPT
         $(function(){
            jamfPlugin = new JamfPlugin();
            jamfPlugin.init({
               commands: $commands_json,
               jamf_id: $jamf_id,
               itemtype: "$itemtype",
               items_id: $items_id,
            });
         });
JAVASCRIPT;

      echo Html::scriptBlock($js);
      return true;
   }

   /**
    * {@inheritDoc}
    */
   public function getRights($interface = 'central') {

      return [READ    => __('Read')];
   }
}
