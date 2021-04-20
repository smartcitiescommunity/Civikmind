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
 * Abstraction for all Jamf device types (Mobile device and Computer for now)
 * @since 2.0.0
 */
abstract class PluginJamfAbstractDevice extends CommonDBChild
{
   static public $itemtype = 'itemtype';
   static public $items_id = 'items_id';
   static public $jamftype_name = null;
   public static $mustBeAttached = false;

   /**
    * Display the extra information for Jamf devices on the main Computer or Phone tab.
    * @param array $params
    * @return void|bool Displays HTML only if a supported item is in the params parameter. If there is any issue, false is returned.
    * @since 1.0.0
    * @since 2.0.0 Renamed from showForComputerOrPhoneMain to showForItem
    */
   abstract public static function showForItem(array $params);

   /**
    * Get a direct link to the device on the Jamf server.
    * @param int $jamf_id The Jamf ID of the device.
    * @return string Jamf URL for the mobile device.
    */
   abstract public static function getJamfDeviceUrl(int $jamf_id): string;

   /**
    * Cleanup relations when an item is purged.
    * @global DBmysql $DB
    * @param CommonDBTM $item
    */
   private static function purgeItemCommon(CommonDBTM $item)
   {
      global $DB;

      $jamf_class = static::getJamfItemClassForGLPIItem($item::getType(), $item->getID());
      if (!is_string($jamf_class)) {
         return;
      }
      $jamf_item = $jamf_class::getJamfItemForGLPIItem($item);
      if ($jamf_item === null) {
         return;
      }
      $device = $jamf_item->getJamfDeviceData();
      if (!empty($device)) {
         $DB->delete('glpi_plugin_jamf_devices', [
            'id' => $device['id']
         ]);
         $DB->delete($jamf_item::getTable(), [
            'glpi_plugin_jamf_devices_id' => $device['id']
         ]);
      }
   }

   /**
    * Cleanup relations when a Computer is purged.
    * @param Computer $item
    * @global DBmysql $DB
    */
   public static function plugin_jamf_purgeComputer(Computer $item)
   {
      static::purgeItemCommon($item);
   }

   /**
    * Cleanup relations when a Phone is purged.
    * @param Phone $item
    * @global DBmysql $DB
    */
   public static function plugin_jamf_purgePhone(Phone $item)
   {
      global $DB;

      static::purgeItemCommon($item);
      $DB->delete(Item_OperatingSystem::getTable(), [
         'itemtype' => $item::getType(),
         'items_id' => $item->getID()
      ]);
   }

   static function preUpdatePhone($item) {
      if (isset($item->input['_plugin_jamf_uuid'])) {
         PluginJamfExtField::setValue($item::getType(), $item->getID(), 'uuid', $item->input['_plugin_jamf_uuid']);
      }
   }

   public static function getJamfItemClassForGLPIItem(string $itemtype, $items_id): ?string
   {
      global $DB;

      $iterator = $DB->request([
         'SELECT'    => [
            'jamf_type'
         ],
         'FROM'      => 'glpi_plugin_jamf_devices',
         'WHERE'     => [
            'itemtype'  => $itemtype,
            'items_id'  => $items_id
         ]
      ]);
      if (count($iterator)) {
         $jamf_type = $iterator->next()['jamf_type'];
         if ($jamf_type === 'Computer') {
            return PluginJamfComputer::class;
         }

         if ($jamf_type === 'MobileDevice') {
            return PluginJamfMobileDevice::class;
         }
      }

      return null;
   }

   public static function getJamfItemForGLPIItem(CommonDBTM $item, $limit_to_type = false): ?PluginJamfAbstractDevice
   {
      global $DB;

      $found_type = static::class;
      $found_id = null;

      if (!$limit_to_type) {
         $iterator = $DB->request([
            'SELECT'    => [
               'jamf_type',
               static::getTable().'.id',
               'itemtype',
               'items_id',
               'jamf_items_id'
            ],
            'FROM'      => 'glpi_plugin_jamf_devices',
            'LEFT JOIN' => [
               static::getTable() => [
                  'ON'  => [
                     static::getTable()         => 'glpi_plugin_jamf_devices_id',
                     'glpi_plugin_jamf_devices' => 'id'
                  ]
               ]
            ],
            'WHERE'     => [
               'itemtype'  => $item::getType(),
               'items_id'  => $item->getID()
            ]
         ]);
         if (count($iterator)) {
            $jamf_data = $iterator->next();
            if ($jamf_data['jamf_type'] === 'Computer') {
               $found_type = PluginJamfComputer::class;
               $found_id = $jamf_data['id'];
            } else if ($jamf_data['jamf_type'] === 'MobileDevice') {
               $found_type = PluginJamfMobileDevice::class;
               $found_id = $jamf_data['id'];
            }
         }
      }

      if ($found_id !== null) {
         $device = new $found_type();
         $device->getFromDB($found_id);
         return $device;
      }
      return null;
   }

   public function getGLPIItem() {
      $itemtype = $this->fields['itemtype'];
      $item = new $itemtype();
      $item->getFromDB($this->fields['items_id']);
      return $item;
   }

   public function getJamfDeviceData() {
      global $DB;

      $iterator = $DB->request([
         'FROM'   => 'glpi_plugin_jamf_devices',
         'WHERE'  => ['id' => $this->fields['glpi_plugin_jamf_devices_id']]
      ]);
      if (count($iterator)) {
         return $iterator->next();
      }
      return [];
   }

   abstract public function getMDMCommands();

   public function getExtensionAttributes()
   {
      global $DB;

      $ext_table = PluginJamfExtensionAttribute::getTable();
      $item_ext_table = PluginJamfItem_ExtensionAttribute::getTable();

      $iterator = $DB->request([
         'SELECT' => [
            'name', 'data_type', 'value'
         ],
         'FROM'   => $ext_table,
         'LEFT JOIN'  => [
            $item_ext_table => [
               'FKEY'   => [
                  $ext_table       => 'id',
                  $item_ext_table  => 'glpi_plugin_jamf_extensionattributes_id'
               ]
            ]
         ],
         'WHERE'  => [
            $item_ext_table.'.itemtype'   => static::getType(),
            'items_id'   => $this->getID()
         ]
      ]);

      $attributes = [];
      while ($data = $iterator->next()) {
         $attributes[] = $data;
      }
      return $attributes;
   }
}