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
 * JamfMobileDevice class. This represents a mobile device from Jamf.
 * This is mainly used to store extra fields that are not already in Computer or Phone classes.
 */
class PluginJamfMobileDevice extends PluginJamfAbstractDevice
{

   static public $itemtype = 'itemtype';
   static public $items_id = 'items_id';
   public static $rightname = 'plugin_jamf_mobiledevice';

   public static function getTypeName($nb = 1)
   {
      return _nx('itemtype', 'Jamf mobile device', 'Jamf mobile devices', $nb, 'jamf');
   }

   /**
    * Display the extra information for mobile devices on the main Computer or Phone tab.
    * @param array $params
    * @return void|bool
    */
   public static function showForComputerOrPhoneMain($params)
   {

   }

   /**
    * Get a direct link to the mobile device on the Jamf server.
    * @param int $jamf_id The Jamf ID of the device.
    * @return string Jamf URL for the mobile device.
    */
   public static function getJamfDeviceURL(int $jamf_id): string
   {
      $config = PluginJamfConfig::getConfig();
      return "{$config['jssserver']}/mobileDevices.html?id={$jamf_id}";
   }

   /**
    * Cleanup relations when an item is purged.
    * @global CommonDBTM $DB
    * @param CommonDBTM $item
    */
   private static function purgeItemCommon(CommonDBTM $item)
   {
      global $DB;

      $DB->delete(self::getTable(), [
         'itemtype' => $item::getType(),
         'items_id' => $item->getID()
      ]);
   }

   /**
    * Cleanup relations when a Computer is purged.
    * @param Computer $item
    */
   public static function plugin_jamf_purgeComputer(Computer $item)
   {
      self::purgeItemCommon($item);
   }

   /**
    * Cleanup relations when a Phone is purged.
    * @global DBmysql $DB
    * @param Phone $item
    */
   public static function plugin_jamf_purgePhone(Phone $item)
   {
      global $DB;

      self::purgeItemCommon($item);
      $DB->delete(Item_OperatingSystem::getTable(), [
         'itemtype' => $item::getType(),
         'items_id' => $item->getID()
      ]);
   }


//   /**
//    * @param CommonDBTM $item
//    * @return PluginJamfMobileDevice
//    */
//   public static function getJamfItemForGLPIItem(CommonDBTM $item)
//   {
//       $mobiledevice = new self();
//       $matches = $mobiledevice->find([
//           'itemtype'   => $item::getType(),
//           'items_id'   => $item->getID()
//       ], [], 1);
//       if (count($matches)) {
//           $id = reset($matches)['id'];
//           $mobiledevice->getFromDB($id);
//           return $mobiledevice;
//       }
//       return null;
//   }

   public static function preUpdatePhone($item) {
      if (isset($item->input['_plugin_jamf_uuid'])) {
         PluginJamfExtField::setValue($item::getType(), $item->getID(), 'uuid', $item->input['_plugin_jamf_uuid']);
      }
   }

   public function getGLPIItem() {
      $device_data = $this->getJamfDeviceData();
      $itemtype = $device_data['itemtype'];
      $item = new $itemtype();
      $item->getFromDB($device_data['items_id']);
      return $item;
   }

   public function getMDMCommands()
   {
      $device_data = $this->getJamfDeviceData();
      $commandhistory = PluginJamfAPIClassic::getItems('mobiledevicehistory', [
         'id' => $device_data['jamf_items_id'],
         'subset' => 'ManagementCommands'
      ]);
      return $commandhistory['management_commands'] ?? [
         'completed' => [],
         'pending'   => [],
         'failed'    => []
      ];
   }

   public function getSpecificType()
   {
      $item = $this->getGLPIItem();
      $modelclass = $this->getJamfDeviceData()['itemtype'].'Model';
      if ($item->fields[getForeignKeyFieldForItemType($modelclass)] > 0) {
         /** @var CommonDropdown $model */
         $model = new $modelclass();
         $model->getFromDB($item->fields[getForeignKeyFieldForItemType($modelclass)]);
         $modelname = $model->fields['name'];
         switch ($modelname) {
            case strpos($modelname, 'iPad') !== false:
               return 'ipad';
            case strpos($modelname, 'iPhone') !== false:
               return 'iphone';
            case strpos($modelname, 'Apple TV') !== false:
               return 'appletv';
            default:
               return null;
         }
      }
      return null;
   }

   public static function dashboardCards()
   {
      $cards = [];

      $cards['plugin_jamf_mobile_lost'] = [
         'widgettype'  => ['bigNumber'],
         'label'       => _x('dashboard', 'Jamf Lost Mobile Device Count', 'jamf'),
         'provider'    => 'PluginJamfMobileDevice::cardLostModeProvider'
      ];
      $cards['plugin_jamf_mobile_managed'] = [
         'widgettype'  => ['bigNumber'],
         'label'       => _x('dashboard', 'Jamf Managed Mobile Device Count', 'jamf'),
         'provider'    => 'PluginJamfMobileDevice::cardManagedProvider'
      ];
      $cards['plugin_jamf_mobile_supervised'] = [
         'widgettype'  => ['bigNumber'],
         'label'       => _x('dashboard', 'Jamf Supervised Mobile Device Count', 'jamf'),
         'provider'    => 'PluginJamfMobileDevice::cardSupervisedProvider'
      ];

      return $cards;
   }

   public static function cardLostModeProvider($params = [])
   {
      global $DB;

      $table = self::getTable();
      $iterator = $DB->request([
         'SELECT'   => [
            'COUNT' => 'lost_mode_enabled as cpt'
         ],
         'FROM'  => $table,
         'WHERE' => ['lost_mode_enabled' => 'Enabled'],
      ]);

      return [
         'label' => _x('dashboard', 'Jamf Lost Mobile Device Count', 'jamf'),
         'number' => $iterator->next()['cpt']
      ];
   }

   public static function cardManagedProvider($params = [])
   {
      global $DB;

      $table = self::getTable();
      $iterator = $DB->request([
         'SELECT'   => [
            'COUNT' => 'managed as cpt'
         ],
         'FROM'  => $table,
         'WHERE' => ['managed' => 1],
      ]);

      return [
         'label' => _x('dashboard', 'Jamf Managed Mobile Device Count', 'jamf'),
         'number' => $iterator->next()['cpt']
      ];
   }

   public static function cardSupervisedProvider($params = [])
   {
      global $DB;

      $table = self::getTable();
      $iterator = $DB->request([
         'SELECT'   => [
            'COUNT' => 'supervised as cpt'
         ],
         'FROM'  => $table,
         'WHERE' => ['supervised' => 1],
      ]);
      return [
         'label'  => _x('dashboard', 'Jamf Supervised Mobile Device Count', 'jamf'),
         'number' => $iterator->next()['cpt']
      ];
   }

   public static function showForItem(array $params)
   {
      global $CFG_GLPI, $DB;

      /** @var CommonDBTM $item */
      $item = $params['item'];

      if (!self::canView() || (!($item::getType() === 'Computer') && !($item::getType() === 'Phone'))) {
         return false;
      }

      $getYesNo = static function($value) {
         return $value ? __('Yes') : __('No');
      };

      $out = '';
      if ($item::getType() === 'Phone') {
         $uuid = PluginJamfExtField::getValue('Phone', $item->getID(), 'uuid');
         $out .= '<tr><td>' . _x('field', 'UUID', 'jamf') . '</td><td>';
         $out .= Html::input('_plugin_jamf_uuid', [
            'value' => $uuid
         ]);
         $out .= "</td></tr>";
      }
      $jamf_item = static::getJamfItemForGLPIItem($item);

      if ($jamf_item === null) {
         echo $out;
         return false;
      }
      $match = $jamf_item->fields;
      $match = array_merge($match, $jamf_item->getJamfDeviceData());

      $out .= "<tr><th colspan='4'>"._x('form_section', 'Jamf General Information', 'jamf')."</th></tr>";
      $out .= "<tr><td>"._x('field', 'Import date', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['import_date'])."</td>";
      $out .= "<td>"._x('field', 'Last sync', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['sync_date'])."</td></tr>";

      $out .= "<tr><td>"._x('field', 'Jamf last inventory', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['last_inventory'])."</td>";
      $out .= "<td>"._x('field', 'Jamf import date', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['entry_date'])."</td></tr>";

      $out .= "<tr><td>"._x('field', 'Enrollment date', 'jamf')."</td>";
      $out .= "<td>".Html::convDateTime($match['enroll_date'])."</td>";
      $out .= "<td>"._x('field', 'Shared device', 'jamf')."</td>";
      $out .= "<td>".$match['shared']."</td></tr>";

      $out .= "<tr><td>"._x('field', 'Supervised', 'jamf')."</td>";
      $out .= "<td>".$getYesNo($match['supervised'])."</td>";
      $out .= "<td>"._x('field', 'Managed', 'jamf')."</td>";
      $out .= "<td>".$getYesNo($match['managed'])."</td></tr>";

      $out .= "<td>"._x('field', 'Cloud backup enabled', 'jamf')."</td>";
      $out .= "<td>".$getYesNo($match['cloud_backup_enabled'])."</td>";
      $out .= "<td>"._x('field', 'Activation locked', 'jamf')."</td>";
      $out .= "<td>".$getYesNo($match['activation_lock_enabled'])."</td></tr>";

      $link = self::getJamfDeviceURL($match['jamf_items_id']);
      $view_msg = _x('field', 'View in Jamf', 'jamf');
      $out .= "<tr><td colspan='4' class='center'>";
      $out .= "<a class='vsubmit' href='{$link}' target='_blank'>{$view_msg}</a>";

      if ($item->canUpdate()) {
         $out .= "&nbsp;&nbsp;<a class='vsubmit' onclick='syncDevice(&quot;{$item::getType()}&quot;, {$item->getID()}); return false;'>"._x('action', 'Sync now', 'jamf')."</a>";
         $ajax_url = Plugin::getWebDir('jamf') . '/ajax/sync.php';
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
      $out .= "</td></tr>";

      $out .= "<tr><th colspan='4'>"._x('form_section', 'Jamf Lost Mode Information', 'jamf')."</th></tr>";
      $enabled = $match['lost_mode_enabled'];
      if (!$enabled || ($enabled != 'true')) {
         $out .= "<tr class='center'><td colspan='4'>"._x('field', 'Lost mode is not enabled')."</td></tr>";
      } else {
         $out .= "<tr><td>"._x('field', 'Enabled', 'jamf')."</td>";
         $out .= "<td>".$enabled."</td>";
         $out .= "<td>"._x('field', 'Enforced', 'jamf')."</td>";
         $out .= "<td>".$getYesNo($match['lost_mode_enforced'])."</td></tr>";

         $out .= "<tr><td>"._x('field', 'Enable date', 'jamf')."</td>";
         $out .= "<td>".Html::convDateTime($match['lost_mode_enable_date'])."</td></tr>";

         $out .= "<tr><td>"._x('field', 'Message', 'jamf')."</td>";
         $out .= "<td>".$match['lost_mode_message']."</td>";
         $out .= "<td>"._x('field', 'Phone', 'jamf')."</td>";
         $out .= "<td>".$match['lost_mode_phone']."</td></tr>";

         $lat = $match['lost_location_latitude'];
         $long = $match['lost_location_longitude'];
         $out .= "<td>"._x('field', 'GPS')."</td><td>";
         //TODO Use leaflet
         $out .= Html::link("$lat, $long", "https://www.google.com/maps/place/$lat,$long", [
            'display'   => false
         ]);
         $out .= "</td><td>"._x('field', 'Altitude')."</td>";
         $out .= "<td>".$match['lost_location_altitude']."</td>";
         $out .= "<tr><td>"._x('field', 'Speed', 'jamf')."</td>";
         $out .= "<td>".$match['lost_location_speed']."</td>";
         $out .= "<td>"._x('field', 'Lost location date')."</td>";
         $out .= "<td>".Html::convDateTime($match['lost_location_date'])."</td></tr>";
      }

      echo $out;
   }
}
