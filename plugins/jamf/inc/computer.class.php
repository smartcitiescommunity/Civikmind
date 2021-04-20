<?php
/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * PluginJamfComputer class. This represents a computer from Jamf.
 * This is mainly used to store extra fields that are not already in the GLPI Computer class.
 */
class PluginJamfComputer extends PluginJamfAbstractDevice
{
   static $rightname = 'plugin_jamf_computer';

   public static $jamftype_name = 'Computer';

   public static function getTypeName($nb = 1)
   {
      return _nx('itemtype', 'Jamf computer', 'Jamf computers', $nb, 'jamf');
   }

   /**
    * Display the extra information for Jamf computers on the main Computer tab.
    * @param array $params
    * @return void|bool Displays HTML only if a supported item is in the params parameter. If there is any issue, false is returned.
    * @throws Exception
    * @since 2.0.0 Renamed from showForComputerOrPhoneMain to showForItem
    * @since 1.0.0
    */
   public static function showForItem(array $params)
   {
      global $CFG_GLPI;

      $item = $params['item'];

      if (!self::canView() || $item::getType() !== 'Computer') {
         return false;
      }

      $getYesNo = static function($value) {
         return $value ? __('Yes') : __('No');
      };

      $out = '';
      $jamf_item = static::getJamfItemForGLPIItem($item);

      if ($jamf_item === null) {
         echo $out;
         return false;
      }
      $match = $jamf_item->fields;
      $match = array_merge($match, $jamf_item->getJamfDeviceData());

      $out .= "<tr><th colspan='4'>"._x('form_section', 'Jamf General Information', 'jamf'). '</th></tr>';
      $out .= '<tr><td>' ._x('field','Import date', 'jamf'). '</td>';
      $out .= '<td>' .Html::convDateTime($match['import_date']). '</td>';
      $out .= '<td>' ._x('field','Last sync', 'jamf'). '</td>';
      $out .= '<td>' .Html::convDateTime($match['sync_date']). '</td></tr>';

      $out .= '<tr><td>' ._x('field','Jamf last inventory', 'jamf'). '</td>';
      $out .= '<td>'.PluginJamfToolbox::utcToLocal($match['last_inventory']). '</td>';
      $out .= '<td>'._x('field','Jamf import date', 'jamf'). '</td>';
      $out .= '<td>' .PluginJamfToolbox::utcToLocal($match['entry_date']). '</td></tr>';

      $out .= '<tr><td>'._x('field','Enrollment date', 'jamf').'</td>';
      $out .= '<td>'.PluginJamfToolbox::utcToLocal($match['enroll_date']).'</td>';
      $out .= '<td>'._x('field','Supervised', 'jamf').'</td>';
      $out .= '<td>'.$getYesNo($match['supervised']).'</td></tr>';

      $out .= '<tr><td>'._x('field','Managed', 'jamf').'</td>';
      $out .= '<td>'.$getYesNo($match['managed']).'</td>';
      $out .= '<td>'._x('field','Activation locked', 'jamf').'</td>';
      $out .= '<td>'.$getYesNo($match['activation_lock_enabled']).'</td></tr>';

      $link = self::getJamfDeviceURL($match['jamf_items_id']);
      $view_msg = _x('action', 'View in Jamf', 'jamf');
      $out .= "<tr><td colspan='4' class='center'>";
      $out .= "<a class='vsubmit' href='{$link}' target='_blank'>{$view_msg}</a>";

      if ($item->canUpdate()) {
         $onclick = "syncDevice(\"{$item::getType()}\", {$item->getID()}); return false;";
         $out .= "&nbsp;&nbsp;<a class='vsubmit' onclick='{$onclick}'>"._x('action', 'Sync now', 'jamf'). '</a>';
         $ajax_url = $CFG_GLPI['root_doc']. '/plugins/jamf/ajax/sync.php';
         $js = <<<JAVASCRIPT
               function syncDevice(itemtype, items_id) {
                  $.ajax({
                     type: "POST",
                     url: "{$ajax_url}",
                     data: {"itemtype": itemtype, "items_id": items_id},
                     contentType: 'application/json',
                     success: function() {
                        location.reload();
                     }
                  });
               }
JAVASCRIPT;
         $out .= Html::scriptBlock($js);
      }
      $out .= '</td></tr>';
      echo $out;
   }

   /**
    * Get a direct link to the device on the Jamf server.
    * @param int $jamf_id The Jamf ID of the device.
    * @return string Jamf URL for the mobile device.
    */
   public static function getJamfDeviceUrl(int $jamf_id): string
   {
      $config = PluginJamfConfig::getConfig();
      return "{$config['jssserver']}/computers.html?id={$jamf_id}";
   }

   public function getMDMCommands()
   {
      return [
         'completed' => [],
         'pending'   => [],
         'failed'    => []
      ];
   }
}