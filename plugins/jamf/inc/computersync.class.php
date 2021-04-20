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

use Glpi\Event;

class PluginJamfComputerSync extends PluginJamfDeviceSync {

   protected static $jamfplugin_itemtype = 'PluginJamfComputer';
   protected static $jamf_itemtype = 'Computer';

   protected function syncGeneral(): PluginJamfDeviceSync
   {
      if (!isset($this->data['general'], $this->data['hardware']) || !$this->config['sync_general'] || $this->item === null) {
         $this->status['syncGeneral'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $general = $this->data['general'];
         $hardware = $this->data['hardware'];

         if (($general['name'] !== $this->item->fields['name'])) {
            $this->item_changes['name'] = $general['name'];
         }
         $other_general_items = [
            'asset_tag' => 'otherserial',
            'serial_number' => 'serial'
         ];
         $other_general_items['udid'] = 'uuid';
         foreach ($other_general_items as $jamf_field => $item_field) {
            if ($general[$jamf_field] !== $this->item->fields[$item_field]) {
               $this->item_changes[$item_field] = $this->db->escape($general[$jamf_field]);
            }
         }

         // Create or match model
         /** @var ComputerModel $model */
         $model = $this->createOrGetItem(ComputerModel::class, [
            'name' => $hardware['model'],
            'product_number' => $hardware['model_identifier']
         ], [
            'name' => $hardware['model'],
            'product_number' => $hardware['model_identifier'],
            'comment' => 'Created by Jamf Plugin for GLPI',
         ]);

         // Set model
         $this->item_changes['computermodels_id'] = $model->getID();

         // Set default type
        //Not implemented yet

         // Set default manufacturer
         $preferred_manufacturer = $this->config['default_manufacturer'];
         if ($preferred_manufacturer) {
            $this->item_changes['manufacturers_id'] = $preferred_manufacturer;
         }

         if ($this->item === null || $this->item->fields['states_id'] === 0) {
            $this->item_changes['states_id'] = $this->config['default_status'];
         }
      } catch (Exception $e) {
         $this->status['syncGeneral'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncGeneral'] = self::STATUS_OK;
      return $this;
   }

   protected function syncOS(): PluginJamfDeviceSync
   {
      if (!$this->config['sync_os'] || $this->item === null || !isset($this->data['hardware'])) {
         $this->status['syncOS'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $hardware = $this->data['hardware'];
         $os = $this->applyDesiredState('OperatingSystem', [
            'name'      => $hardware['os_name'],
         ], [
            'name'      => $hardware['os_name'],
            'comment'   => 'Created by Jamf Plugin for GLPI'
         ]);

         $os_version = $this->applyDesiredState('OperatingSystemVersion', [
            'name'      => $hardware['os_version'],
         ], [
            'name'      => $hardware['os_version'],
            'comment'   => 'Created by Jamf Plugin for GLPI'
         ]);

         $this->db->updateOrInsert(Item_OperatingSystem::getTable(), [
            'operatingsystems_id' => $os->getID(),
            'operatingsystemversions_id' => $os_version->getID(),
            'date_creation' => $_SESSION['glpi_currenttime'],
         ], [
            'itemtype' => $this->item::getType(),
            'items_id' => $this->item->getID()
         ]);
      } catch (Exception $e) {
         $this->status['syncOS'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncOS'] = self::STATUS_OK;
      return $this;
   }

   protected function syncSoftware(): PluginJamfDeviceSync
   {
      global $DB;

      if (!$this->config['sync_software'] || $this->item === null || !isset($this->data['software']['applications'])) {
         $this->status['syncSoftware'] = self::STATUS_SKIPPED;
         return $this;
      }

      try {
         $applications = $this->data['software']['applications'];
         foreach ($applications as $application) {
            $software_data = static::$api_classic::getItems('mobiledeviceapplications', [
               'application'  => $application['name'],
               'version'      => $application['version']
            ]);
            if ($software_data === null) {
               continue;
            }
            $software = $this->applyDesiredState('Software', [
               'name'         => $this->db->escape($software_data['general']['name']),
            ], [
               'name'         => $this->db->escape($software_data['general']['name']),
               'entities_id'  => $this->item->fields['entities_id'],
               'is_recursive' => $this->item->fields['is_recursive']
            ]);
            $software_version = $this->applyDesiredState('SoftwareVersion', [
               'softwares_id' => $software->getID(),
               'name'         => $application['version'],
            ], [
               'softwares_id' => $software->getID(),
               'name'         => $application['version'],
               'entities_id'  => $this->item->fields['entities_id'],
               'is_recursive' => $this->item->fields['is_recursive']
            ]);
            $this->applyDesiredState('Item_SoftwareVersion', [
               'itemtype'              => $this->item::getType(),
               'items_id'              => $this->item->getID(),
               'softwareversions_id'   => $software_version->getID()
            ], [
               'itemtype'              => $this->item::getType(),
               'items_id'              => $this->item->getID(),
               'softwareversions_id'   => $software_version->getID(),
               'entities_id'           => $this->item->fields['entities_id'],
               'is_recursive'          => $this->item->fields['is_recursive']
            ]);
         }

         // Unlink software that isn't installed anymore
         $installed_bundles = array_column($applications, 'identifier');
         $inventoried_software = PluginJamfComputerSoftware::getForGlpiItem($this->item);
         // We don't need to worry about versions because Apple mobile devices cannot have multiple installs for the same bundle id, so no multiple version support
         $to_remove_software = array_filter($inventoried_software, static function($software) use ($installed_bundles) {
            return !in_array($software['bundle_id'], $installed_bundles, true);
         });
         foreach ($to_remove_software as $to_remove) {
            $DB->delete(Item_SoftwareVersion::getTable(), [
               SoftwareVersion::getTable().'.softwares_id' => $to_remove['softwares_id'],
               'itemtype'     => $this->item::getType(),
               'items_id'     => $this->item->getID()
            ], [
               'LEFT JOIN' => [
                  SoftwareVersion::getTable() => [
                     'ON'  => [
                        SoftwareVersion::getTable()      => 'id',
                        Item_SoftwareVersion::getTable() => 'softwareversions_id'
                     ]
                  ],
                  Software::getTable() => [
                     'ON'  => [
                        Software::getTable()             => 'id',
                        SoftwareVersion::getTable()      => 'softwares_id'
                     ]
                  ],
               ]
            ]);
         }
      } catch (Exception $e) {
         $this->status['syncSoftware'] = self::STATUS_ERROR;
         return $this;
      }

      $this->status['syncSoftware'] = self::STATUS_OK;
      return $this;
   }

   protected function syncUser(): PluginJamfDeviceSync
   {
      if (!$this->config['sync_user'] || $this->item === null || !isset($this->data['location'])) {
         $this->status['syncUser'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $location = $this->data['location'];
         $user = new User();
         $users_id = $user->find(['name' => $location['username']]);
         if ($users_id) {
            $this->item_changes['users_id'] = $users_id;
         }
      } catch (Exception $e) {
         $this->status['syncUser'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncUser'] = self::STATUS_OK;
      return $this;
   }

   protected function syncPurchasing(): PluginJamfDeviceSync
   {
      if (!$this->config['sync_financial'] || $this->item === null || !isset($this->data['purchasing'])) {
         $this->status['syncPurchasing'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $purchasing = $this->data['purchasing'];
         $infocom_changes = [];
         if (!empty($purchasing['po_date_utc'])) {
            $purchase_date = PluginJamfToolbox::utcToLocal(new DateTime($purchasing['po_date_utc']));
            $infocom_changes['buy_date'] = $purchase_date;
            if (!empty($purchasing['warranty_expires_utc'])) {
               $infocom_changes['warranty_date'] = $purchase_date;
               $warranty_expiration = PluginJamfToolbox::utcToLocal(new DateTime($purchasing['warranty_expires_utc']));
               $diff = date_diff(new DateTime($warranty_expiration), new DateTime($purchase_date));
               $warranty_length = $diff->m + ($diff->y * 12);
               $infocom_changes['warranty_duration'] = $warranty_length;
            }
         }
         if (!empty($purchasing['applecare_id'])) {
            $infocom_changes['warranty_info'] = "AppleCare ID: {$purchasing['applecare_id']}";
         }
         if (!empty($purchasing['po_number'])) {
            $infocom_changes['order_number'] = $purchasing['po_number'];
         }

         $this->db->updateOrInsert(Infocom::getTable(), $infocom_changes, [
            'itemtype' => $this->item::getType(),
            'items_id' => $this->item->getID()
         ]);
      } catch (Exception $e) {
         $this->status['syncPurchasing'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncPurchasing'] = self::STATUS_OK;
      return $this;
   }

   protected function syncExtensionAttributes(): PluginJamfDeviceSync
   {
      if (!$this->config['sync_general'] || !isset($this->data['extension_attributes'])) {
         $this->status['syncExtensionAttributes'] = self::STATUS_SKIPPED;
         return $this;
      } else if ($this->config['sync_general'] && $this->jamfplugin_device === null) {
         $this->status['syncExtensionAttributes'] = self::STATUS_DEFERRED;
         return $this;
      }
      try {
         $extension_attributes = $this->data['extension_attributes'];
         $ext_attribute = new PluginJamfExtensionAttribute();

         foreach ($extension_attributes as $attr) {
            $attr_match = $ext_attribute->find([
               'jamf_id'   => $attr['id'],
               'itemtype'  => $this->jamfplugin_device::getType(),
               'jamf_type' => 'Computer'
            ], [], 1);

            if ($attr_match !== null && count($attr_match)) {
               $attr_match = reset($attr_match);
               $this->db->updateOrInsert(PluginJamfItem_ExtensionAttribute::getTable(), ['value' => $attr['value']], [
                  'glpi_plugin_jamf_extensionattributes_id' => $attr_match['id'],
                  'items_id' => $this->jamfplugin_device->getID(),
                  'itemtype' => $this->jamfplugin_device::getType()
               ]);
            }
         }
      } catch (Exception $e) {
         $this->status['syncExtensionAttributes'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncExtensionAttributes'] = self::STATUS_OK;
      return $this;
   }

   protected function syncSecurity(): PluginJamfDeviceSync
   {
      if ($this->item === null || !isset($this->data['security'])) {
         $this->status['syncSecurity'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $security = $this->data['security'];
         $this->commondevice_changes['activation_lock_enabled'] = $security['activation_lock'];
      } catch (Exception $e) {
         $this->status['syncSecurity'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncSecurity'] = self::STATUS_OK;
      return $this;
   }

   protected function syncNetwork(): PluginJamfDeviceSync
   {
      if ($this->item === null || !isset($this->data['general'])) {
         $this->status['syncNetwork'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $general = $this->data['general'];
         $hardware = $this->data['hardware'];

         $expected_netcard_name = "Generic {$hardware['model']} Network Card";

         if (isset($general['alt_mac_address']) && !empty($general['alt_mac_address'])) {
            $wifi_model = $this->createOrGetItem('DeviceNetworkCardModel', ['name' => $expected_netcard_name], [
               'name'       => $expected_netcard_name,
               'comment'    => 'Created by Jamf Plugin for GLPI'
            ]);
            $wifi = $this->createOrGetItem('DeviceNetworkCard', ['designation' => $expected_netcard_name], [
               'designation'                   => $expected_netcard_name,
               'devicenetworkcardmodels_id'    => $wifi_model->getID(),
               'comment'                       => 'Created by Jamf Plugin for GLPI'
            ]);
            $item_wifi = $this->createOrGetItem('Item_DeviceNetworkCard', [
               'itemtype'              => $this->item->getType(),
               'items_id'              => $this->item->getID(),
               'devicenetworkcards_id' => $wifi->getID()
            ], [
               'itemtype'              => $this->item->getType(),
               'items_id'              => $this->item->getID(),
               'devicenetworkcards_id' => $wifi->getID(),
               'is_dynamic'            => 1,
               'entities_id'           => 0,
               'is_recursive'          => 1
            ]);

            $netport = $this->createOrGetItem('NetworkPort', [
               'itemtype'              => $this->item->getType(),
               'items_id'              => $this->item->getID(),
               'instantiation_type'    => 'NetworkPortWifi',
               'logical_number'        => 0
            ], [
               'itemtype'                      => $this->item->getType(),
               'items_id'                      => $this->item->getID(),
               'instantiation_type'            => 'NetworkPortWifi',
               'logical_number'                => 0,
               'name'                          => 'Wifi',
               'comment'                       => 'Created by Jamf Plugin for GLPI',
               'items_devicenetworkcards_id'   => $item_wifi->getID(),
               'is_dynamic'                    => 1,
               'mac'                           => $general['alt_mac_address']
            ]);

            $network_name = $this->createOrGetItem('NetworkName', [
               'itemtype'  => 'NetworkPort',
               'items_id'  => $netport->getID()
            ], [
               'itemtype'          => 'NetworkPort',
               'items_id'          => $netport->getID(),
               'entities_id'       => $this->item->fields['entities_id'],
               'is_dynamic'    => 1
            ]);

            $ipaddress = new IPAddress();
            $ip_matches = $ipaddress->find([
               'entities_id'   => $this->item->fields['entities_id'],
               'itemtype'      => 'NetworkName',
               'items_id'      => $network_name->getID(),
               'mainitemtype'  => $this->item->getType(),
               'mainitems_id'  => $this->item->getID(),
               'is_dynamic'    => 1
            ]);
            if (!count($ip_matches)) {
               $ipaddress->add([
                  'entities_id'   => $this->item->fields['entities_id'],
                  'itemtype'      => 'NetworkName',
                  'items_id'      => $network_name->getID(),
                  'mainitemtype'  => $this->item->getType(),
                  'mainitems_id'  => $this->item->getID(),
                  'is_dynamic'    => 1,
                  'name'          => $general['ip_address']
               ]);
            } else {
               $ip_matches = reset($ip_matches);
               $ipaddress->getFromDB($ip_matches['id']);
               $ipaddress->update([
                  'name'  => $general['ip_address']
               ]);
            }
         }

      } catch (Exception $e) {
         $this->status['syncNetwork'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncNetwork'] = self::STATUS_OK;
      return $this;
   }

   protected function syncGeneralJamfPluginItem(): PluginJamfDeviceSync
   {
      if (!$this->config['sync_general'] || $this->item === null || !isset($this->data['general'])) {
         $this->status['syncGeneralJamfPluginItem'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $general = $this->data['general'];
         if (!empty($general['last_inventory_update_utc'])) {
            $last_inventory = new DateTime($general['last_inventory_update_utc']);
            $this->commondevice_changes['last_inventory'] = $last_inventory;
         }
         if (!empty($general['initial_entry_date_utc'])) {
            $entry_date = $general['initial_entry_date_utc'];
            $this->commondevice_changes['entry_date'] = $entry_date;
         }
         if (!empty($general['last_enrollment_utc'])) {
            $enroll_date = new DateTime($general['last_enrollment_utc']);
            $this->commondevice_changes['enroll_date'] = $enroll_date;
         }

         $this->commondevice_changes['itemtype'] = $this->item::getType();
         $this->commondevice_changes['items_id'] = $this->item->getID();
         $this->commondevice_changes['jamf_items_id'] = $general['id'];
         $this->commondevice_changes['udid'] = $general['udid'];
         $this->commondevice_changes['managed'] = $general['remote_management']['managed'];
         $this->commondevice_changes['supervised'] = $general['supervised'];
      } catch (Exception $e) {
         $this->status['syncGeneralJamfPluginItem'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncGeneralJamfPluginItem'] = self::STATUS_OK;
      return $this;
   }

   protected function syncComponents(): PluginJamfDeviceSync
   {
      if (!isset($this->data['hardware']) || !$this->config['sync_components'] || $this->item === null) {
         $this->status['syncComponents'] = self::STATUS_SKIPPED;
         return $this;
      }
      $hardware = $this->data['hardware'];

      // Boot firmware
      if (isset($hardware['boot_rom']) && !empty($hardware['boot_rom'])) {
         $boot_rom = $this->applyDesiredState('DeviceFirmware', [
            'designation'   => $hardware['boot_rom']
         ], [
            'designation'   => $hardware['boot_rom'],
            'manufacturers_id'   => $this->config['default_manufacturer'],
            'entities_id'        => '0',
            'is_recursive'       => '1'
         ]);
         $this->applyDesiredState('Item_DeviceFirmware', [
            'itemtype'           => $this->item::getType(),
            'items_id'           => $this->item->getID(),
            'devicefirmwares_id' => $boot_rom->getID()
         ], [
            'itemtype'           => $this->item::getType(),
            'items_id'           => $this->item->getID(),
            'devicefirmwares_id' => $boot_rom->getID(),
            'is_deleted'         => 0,
            'is_dynamic'         => 1,
            'entities_id'        => 0,
            'is_recursive'       => 1,
         ]);
      }
      $storage = $hardware['storage'];
      if (!empty($storage)) {
         foreach ($storage as $disk) {
            foreach ($disk['partitions'] as $partition) {
               // Volume
               $this->applyDesiredState(Item_Disk::class, [
                  'itemtype'     => $this->item::getType(),
                  'items_id'     => $this->item->getID(),
                  'mountpoint'   => '',
                  'name'         => $partition['name']
               ], [
                  'itemtype'     => $this->item::getType(),
                  'items_id'     => $this->item->getID(),
                  'name'         => $partition['name'],
                  'mountpoint'   => '',
                  'totalsize'    => (($partition['partition_capacity_mb'] / 1000) / 1000),
                  'freesize'     => (($partition['available_mb'] / 1000) / 1000),
                  'entities_id'  => $this->item->fields['entities_id'],
                  'is_dynamic'   => 1,
               ]);
            }
         }
      }

      $this->status['syncComponents'] = self::STATUS_OK;
      return $this;
   }

   public static function discover(): bool
   {
      global $DB;

      $volume = 0;
      $jamf_devices = static::$api_classic::getItems('computers');
      if ($jamf_devices === null || !count($jamf_devices)) {
         // API error or device no longer exists in Jamf
         return -1;
      }
      $imported = [];
      $iterator = $DB->request([
         'SELECT' => ['jamf_items_id'],
         'FROM'   => 'glpi_plugin_jamf_devices',
         'WHERE'  => ['jamf_type' => static::$jamf_itemtype]
      ]);
      while ($data = $iterator->next()) {
         $imported[] = $data['jamf_items_id'];
      }
      $pending_iterator = $DB->request([
         'FROM'   => 'glpi_plugin_jamf_imports',
         'WHERE'  => [
            'jamf_type' => 'Computer'
         ]
      ]);
      $pending_import = [];
      while ($data = $pending_iterator->next()) {
         $pending_import[$data['jamf_items_id']] = $data;
      }

      $config = Config::getConfigurationValues('plugin:Jamf');
      foreach ($jamf_devices as $jamf_device) {
         if (!in_array($jamf_device['id'], $imported, true)) {
            // Not already imported
            if (isset($config['autoimport']) && $config['autoimport']) {
               try {
                  $result = self::import('Computer', $jamf_device['id']);
                  if ($result) {
                     $volume++;
                  }
               } catch (Exception $e2) {
                  // Some other error
               }
            } else {
               if (!array_key_exists($jamf_device['id'], $pending_import)) {
                  // Just discovered and cannot auto-import. Save to imports table instead.
                  $DB->insert('glpi_plugin_jamf_imports', [
                     'jamf_type'       => 'Computer',
                     'jamf_items_id'   => $jamf_device['id'],
                     'name'            => $DB->escape($jamf_device['name']),
                     'type'            => 'Computer',
                     'date_discover'   => $_SESSION['glpi_currenttime']
                  ]);
               }
            }
         }
      }
      return $volume;
   }

   public static function import(string $itemtype, int $jamf_items_id, $use_transaction = true): bool
   {
      global $DB;

      if (!self::isSupportedGlpiItemtype($itemtype)) {
         // Invalid itemtype for a mobile device
         return false;
      }
      $item = new $itemtype();

      $jamf_item = static::$api_classic::getItems('computers', ['id' => $jamf_items_id]);
      if ($jamf_item === null) {
         // API error or device no longer exists in Jamf
         return false;
      }

      $rules = new PluginJamfRuleImportCollection();
      $ruleinput = [
         'name'            => $jamf_item['general']['name'],
         'itemtype'        => $itemtype,
         'last_inventory'  => $jamf_item['general']['report_date_utc'],
         'managed'         => $jamf_item['general']['remote_management']['managed'],
         'supervised'      => $jamf_item['general']['supervised'],
      ];
      $ruleinput = $rules->processAllRules($ruleinput, $ruleinput, ['recursive' => true]);

      if (isset($ruleinput['_import']) && !$ruleinput['_import']) {
         // Dropped by rules
         return false;
      }

      $iterator = $DB->request([
         'SELECT' => [$itemtype::getTable().'.id'],
         'FROM' => $itemtype::getTable(),
         'WHERE' => [
            'uuid' => $jamf_item['general']['udid']
         ],
         'LIMIT' => 1
      ]);
      if ($iterator->count()) {
         // Already imported
         Event::log(-1, $itemtype, 4, 'Jamf plugin', "Jamf computer $jamf_items_id not imported. A {$itemtype::getTypeName(1)} exists with the same uuid.");
         return false;
      }

      if ($use_transaction) {
         $DB->beginTransaction();
      }
      // Import new device
      $items_id = $item->add([
         'name'         => $DB->escape($jamf_item['general']['name']),
         'entities_id'  => 0,
         'is_recursive' => 1,
         'is_dynamic'   => 1
      ]);
      if ($items_id) {
         // Link
         $r = $DB->insert('glpi_plugin_jamf_devices', [
            'itemtype'        => $item::getType(),
            'items_id'        => $items_id,
            'udid'            => $jamf_item['general']['udid'],
            'jamf_type'       => static::$jamf_itemtype,
            'jamf_items_id'   => $jamf_item['general']['id']
         ]);
         if ($r === false) {
            if ($use_transaction) {
               $DB->rollBack();
            }
            return false;
         }
         $device_id = $DB->insertId();
         $jamf_computer = new PluginJamfComputer();
         $r2 = $jamf_computer->add([
            'glpi_plugin_jamf_devices_id' => $device_id
         ]);
         if ($r2 === false) {
            if ($use_transaction) {
               $DB->rollBack();
            }
            return false;
         }
         if (self::sync($itemtype, $items_id, false)) {
            $DB->update('glpi_plugin_jamf_devices', [
               'import_date' => $_SESSION['glpi_currenttime']
            ], [
               'itemtype' => $itemtype,
               'items_id' => $items_id
            ]);
            $DB->delete(PluginJamfImport::getTable(), ['jamf_type' => static::$jamf_itemtype, 'jamf_items_id' => $jamf_items_id]);
            if ($use_transaction) {
               $DB->commit();
            }
         } else {
            if ($use_transaction) {
               $DB->rollBack();
            }
         }
      } else {
         if ($use_transaction) {
            $DB->rollBack();
         }
         return false;
      }
      return true;
   }

   protected static function getJamfDataForSyncingByGlpiItem(string $itemtype, int $items_id): array
   {
      global $DB;

      $iterator = $DB->request([
         'SELECT' => ['jamf_items_id'],
         'FROM'   => 'glpi_plugin_jamf_devices',
         'WHERE'  => [
            'itemtype'  => $itemtype,
            'items_id'  => $items_id
         ]
      ]);

      if (!count($iterator)) {
         return [];
      }
      $jamf_item = $iterator->next();

      return static::$api_classic::getItems('computers', ['id' => $jamf_item['jamf_items_id']]) ?? [];
   }

   public static function getSupportedGlpiItemtypes(): array
   {
      return ['Computer'];
   }
}