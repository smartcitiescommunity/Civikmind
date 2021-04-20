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

class PluginJamfMobileSync extends PluginJamfDeviceSync {

   protected static $jamfplugin_itemtype = 'PluginJamfMobileDevice';
   protected static $jamf_itemtype = 'MobileDevice';

   protected function syncGeneral(): PluginJamfDeviceSync
   {
      if (!$this->config['sync_general'] || $this->item === null || !isset($this->data['general'])) {
         $this->status['syncGeneral'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $general = $this->data['general'];
         $itemtype = $this->item::getType();

         if (($general['name'] !== $this->item->fields['name'])) {
            $this->item_changes['name'] = $general['name'];
         }
         $other_general_items = [
            'asset_tag' => 'otherserial',
            'serial_number' => 'serial'
         ];
         if ($itemtype === 'Computer') {
            $other_general_items['udid'] = 'uuid';
         } else {
            $this->extitem_changes['uuid'] = $general['udid'];
         }
         foreach ($other_general_items as $jamf_field => $item_field) {
            if ($general[$jamf_field] !== $this->item->fields[$item_field]) {
               $this->item_changes[$item_field] = $this->db->escape($general[$jamf_field]);
            }
         }

         // Create or match model
         if ($itemtype === 'Phone') {
            $model_type = 'PhoneModel';
         } else {
            $model_type = 'ComputerModel';
         }

         $model = $this->applyDesiredState($model_type, [
            'name'            => $general['model'],
            'product_number'  => $general['model_number']
         ], [
            'name' => $general['model'],
            'product_number' => $general['model_number'],
            'comment' => 'Created by Jamf Plugin for GLPI'
         ]);

         // Set model
         if ($itemtype === 'Computer') {
            $this->item_changes['computermodels_id'] = $model->getID();
         } else {
            $this->item_changes['phonemodels_id'] = $model->getID();
         }

         // Set default type
         if ($itemtype === 'Phone') {
            $preferred_type = $this->config['iphone_type'];
            if ($preferred_type) {
               $this->item_changes['phonetypes_id'] = $preferred_type;
            }
         } else {
            if (strpos($general['model'], 'Apple TV') === false) {
               $preferred_type = $this->config['ipad_type'];
            } else {
               $preferred_type = $this->config['appletv_type'];
            }
            if ($preferred_type) {
               $this->item_changes['computertypes_id'] = $preferred_type;
            }
         }

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
      if (!$this->config['sync_os'] || $this->item === null || !isset($this->data['general'])) {
         $this->status['syncOS'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $general = $this->data['general'];
         $os = $this->applyDesiredState('OperatingSystem', [
            'name'      => $general['os_type'],
         ], [
            'name'      => $general['os_type'],
            'comment'   => 'Created by Jamf Plugin for GLPI'
         ]);

         $os_version = $this->applyDesiredState('OperatingSystemVersion', [
            'name'      => $general['os_version'],
         ], [
            'name'      => $general['os_version'],
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

      if (!$this->config['sync_software'] || $this->item === null || !isset($this->data['applications'])) {
         $this->status['syncSoftware'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $applications = $this->data['applications'];
         $software = new Software();
         $softwareversion = new SoftwareVersion();
         $jamf_software = new PluginJamfMobileDeviceSoftware();

         foreach ($applications as $application) {
            $jamfsoftware_matches = $jamf_software->find(['bundle_id' => $application['identifier']]);

            if (!count($jamfsoftware_matches)) {
               $software_data = static::$api_classic::getItems('mobiledeviceapplications', [
                  'bundleid' => $application['identifier'],
                  'version' => $application['application_version']
               ]);
               if (is_null($software_data) || !isset($software_data['general'])) {
                  continue;
               }
               $software_matches = $software->find(['name' => $software_data['general']['name']]);
               if (!count($software_matches)) {
                  $software_id = $software->add([
                     'name' => $this->db->escape($software_data['general']['name']),
                     'comment' => $this->db->escape($software_data['general']['description']),
                     'entities_id' => $this->item->fields['entities_id'],
                     'is_recursive' => $this->item->fields['is_recursive']
                  ]);
               } else {
                  $software_id = reset($software_matches)['id'];
               }
               $jamf_software->add([
                  'softwares_id' => $software_id,
                  'bundle_id' => $application['identifier'],
                  'itunes_store_url' => $this->db->escape($software_data['general']['itunes_store_url'])
               ]);
            } else {
               $software_id = array_values($jamfsoftware_matches)[0]['softwares_id'];
            }
            $softwareversion_matches = $softwareversion->find([
               'softwares_id' => $software_id,
               'name' => $application['application_version']
            ]);
            if (!count($softwareversion_matches)) {
               $version_input = [
                  'softwares_id' => $software_id,
                  'name' => $application['application_version'],
                  'entities_id' => $this->item->fields['entities_id'],
                  'is_recursive' => $this->item->fields['is_recursive']
               ];
               $softwareversion_id = $softwareversion->add($version_input);
            } else {
               $softwareversion_id = array_keys($softwareversion_matches)[0];
            }
            if (!$softwareversion_id) {
               continue;
            }
            $item_softwareversion = new Item_SoftwareVersion();
            $item_softwareversion_matches = $item_softwareversion->find([
               'itemtype'              => $this->item::getType(),
               'items_id'              => $this->item->getID(),
               'softwareversions_id'   => $softwareversion_id
            ]);
            if (!count($item_softwareversion_matches)) {
               $item_softwareversion->add([
                  'itemtype'              => $this->item::getType(),
                  'items_id'              => $this->item->getID(),
                  'softwareversions_id'   => $softwareversion_id,
                  'entities_id'           => $this->item->fields['entities_id'],
                  'is_recursive'          => $this->item->fields['is_recursive']
               ]);
            }
         }

         // Unlink software that isn't installed anymore
         $installed_bundles = array_column($applications, 'identifier');
         $inventoried_software = PluginJamfMobileDeviceSoftware::getForGlpiItem($this->item);
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
         $user_match = $user->find(['name' => $location['username']]);
         if (count($user_match)) {
            $user_match = reset($user_match);
            $this->item_changes['users_id'] = $user_match['id'];
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
      } else if ($this->config['sync_general'] && ($this->jamfplugin_device === null || empty($this->jamfplugin_device->fields))) {
         $this->status['syncExtensionAttributes'] = self::STATUS_DEFERRED;
         return $this;
      }
      try {
         $extension_attributes = $this->data['extension_attributes'];
         $ext_attribute = new PluginJamfExtensionAttribute();

         foreach ($extension_attributes as $attr) {
            $attr_match = $ext_attribute->find([
               'jamf_id' => $attr['id'],
               'jamf_type' => 'MobileDevice'
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
         if (!empty($security['lost_mode_enable_issued_utc'])) {
            $lost_mode_enable_date = $security['lost_mode_enable_issued_utc'];
            $this->jamfplugin_item_changes['lost_mode_enable_date'] = $lost_mode_enable_date;
         }
         if (!empty($security['lost_location_utc'])) {
            $lost_location_date = $security['lost_location_utc'];
            $this->jamfplugin_item_changes['lost_location_date'] = $lost_location_date;
         }
         $this->commondevice_changes['activation_lock_enabled'] = $security['activation_lock_enabled'];
         $this->jamfplugin_item_changes['lost_mode_enabled'] = $security['lost_mode_enabled'];
         $this->jamfplugin_item_changes['lost_mode_enforced'] = $security['lost_mode_enforced'];
         $this->jamfplugin_item_changes['lost_mode_message'] = $security['lost_mode_message'];
         $this->jamfplugin_item_changes['lost_mode_phone'] = $security['lost_mode_phone'];
         $this->jamfplugin_item_changes['lost_location_latitude'] = $security['lost_location_latitude'];
         $this->jamfplugin_item_changes['lost_location_longitude'] = $security['lost_location_longitude'];
         $this->jamfplugin_item_changes['lost_location_altitude'] = $security['lost_location_altitude'];
         $this->jamfplugin_item_changes['lost_location_speed'] = $security['lost_location_speed'];
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
         $expected_netcard_name = "Generic {$general['model']} Network Card";

         if (isset($general['wifi_mac_address']) && !empty($general['wifi_mac_address'])) {
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
               'mac'                           => $general['wifi_mac_address']
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
               if (isset($ip_matches['id'])) {
                  $ipaddress->getFromDB($ip_matches['id']);
                  $ipaddress->update([
                     'id'     => $ip_matches['id'],
                     'name'   => $general['ip_address']
                  ]);
               }
            }
         }

         if (isset($general['bluetooth_mac_address']) && !empty($general['bluetooth_mac_address'])) {
            $bt_model = $this->createOrGetItem('DeviceNetworkCardModel', ['name' => $expected_netcard_name], [
               'name'       => $expected_netcard_name,
               'comment'    => 'Created by Jamf Plugin for GLPI'
            ]);
            $bt = $this->createOrGetItem('DeviceNetworkCard', ['designation' => $expected_netcard_name], [
               'designation'                   => $expected_netcard_name,
               'devicenetworkcardmodels_id'    => $bt_model->getID(),
               'comment'                       => 'Created by Jamf Plugin for GLPI'
            ]);
            $item_bt = $this->createOrGetItem('Item_DeviceNetworkCard', [
               'itemtype'              => $this->item->getType(),
               'items_id'              => $this->item->getID(),
               'devicenetworkcards_id' => $bt->getID()
            ], [
               'itemtype'              => $this->item->getType(),
               'items_id'              => $this->item->getID(),
               'devicenetworkcards_id' => $bt->getID(),
               'is_dynamic'            => 1,
               'entities_id'           => 0,
               'is_recursive'          => 1
            ]);

            $this->createOrGetItem('NetworkPort', [
               'itemtype'              => $this->item->getType(),
               'items_id'              => $this->item->getID(),
               'instantiation_type'    => 'NetworkPortWifi',
               'logical_number'        => 1
            ], [
               'itemtype'                      => $this->item->getType(),
               'items_id'                      => $this->item->getID(),
               'instantiation_type'            => 'NetworkPortWifi',
               'logical_number'                => 1,
               'name'                          => 'Bluetooth',
               'comment'                       => 'Created by Jamf Plugin for GLPI',
               'items_devicenetworkcards_id'   => $item_bt->getID(),
               'mac'                           => $general['bluetooth_mac_address']
            ]);
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
            $last_inventory = $general['last_inventory_update_utc'];
            $this->commondevice_changes['last_inventory'] = $last_inventory;
         }
         if (!empty($general['initial_entry_date_utc'])) {
            $entry_date = $general['initial_entry_date_utc'];
            $this->commondevice_changes['entry_date'] = $entry_date;
         }
         if (!empty($general['last_enrollment_utc'])) {
            $enroll_date = $general['last_enrollment_utc'];
            $this->commondevice_changes['enroll_date'] = $enroll_date;
         }

         $this->commondevice_changes['itemtype'] = $this->item::getType();
         $this->commondevice_changes['items_id'] = $this->item->getID();
         $this->commondevice_changes['jamf_items_id'] = $general['id'];
         $this->commondevice_changes['udid'] = $general['udid'];
         $this->commondevice_changes['managed'] = $general['managed'];
         $this->commondevice_changes['supervised'] = $general['supervised'];
         $this->jamfplugin_item_changes['shared'] = $general['shared'];
         $this->jamfplugin_item_changes['cloud_backup_enabled'] = $general['cloud_backup_enabled'];
      } catch (Exception $e) {
         $this->status['syncGeneralJamfPluginItem'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncGeneralJamfPluginItem'] = self::STATUS_OK;
      return $this;
   }

   protected function syncComponents(): PluginJamfDeviceSync
   {
      if (!$this->config['sync_components'] || $this->item === null || !isset($this->data['general'])) {
         $this->status['syncComponents'] = self::STATUS_SKIPPED;
         return $this;
      }
      try {
         $config = PluginJamfConfig::getConfig();
         $general = $this->data['general'];

         // Volume
         $this->createOrGetItem(Item_Disk::class, [
            'itemtype'              => $this->item::getType(),
            'items_id'              => $this->item->getID(),
            'mountpoint'            => '/',
         ], [
            'itemtype'              => $this->item::getType(),
            'items_id'              => $this->item->getID(),
            'name'                  => 'OS',
            'mountpoint'            => '/',
            'totalsize'             => $general['capacity'],
            'freesize'              => $general['available'],
            'entities_id'           => $this->item->fields['entities_id'],
            'is_dynamic'            => 1,
         ]);

         if (!empty($general['phone_number'])) {
            // Simcard/Line
            $simcard = $this->createOrGetItem(DeviceSimcard::class, [
               'designation'  => 'Generic Apple Simcard',
            ], [
               'designation'  => 'Generic Apple Simcard',
               'comment'      => 'Created by Jamf Plugin for GLPI',
               'is_recursive' => 1,
               'manufacturer' => $config['default_manufacturer'],
            ]);
            $line = $this->createOrGetItem(Line::class, [
               'caller_num'   => $general['phone_number'],
            ], [
               'name'         => $general['phone_number'],
               'caller_num'   => $general['phone_number'],
            ]);
            $this->createOrGetItem(Item_DeviceSimcard::class, [
               'itemtype'              => $this->item::getType(),
               'items_id'              => $this->item->getID(),
               'devicesimcards_id'     => $simcard->getID()
            ], [
               'itemtype'              => $this->item::getType(),
               'items_id'              => $this->item->getID(),
               'devicesimcards_id'     => $simcard->getID(),
               'is_dynamic'            => 1,
               'entities_id'           => $this->item->fields['entities_id'],
               'is_recursive'          => 1,
               'lines_id'              => $line->getID(),
            ]);
         }
      } catch (Exception $e) {
         $this->status['syncComponents'] = self::STATUS_ERROR;
         return $this;
      }
      $this->status['syncComponents'] = self::STATUS_OK;
      return $this;
   }

   public static function discover(): bool
   {
      global $DB;

      $volume = 0;
      $jamf_devices = static::$api_classic::getItems('mobiledevices');
      if ($jamf_devices === null || !count($jamf_devices)) {
         // API error or device no longer exists in Jamf
         return -1;
      }
      $imported = [];
      $iterator = $DB->request([
         'SELECT' => ['udid'],
         'FROM'   => 'glpi_plugin_jamf_devices',
         'WHERE'  => ['jamf_type' => static::$jamf_itemtype]
      ]);
      while ($data = $iterator->next()) {
         $imported[] = $data['udid'];
      }
      $pending_iterator = $DB->request([
         'FROM'   => 'glpi_plugin_jamf_imports',
         'WHERE'  => [
            'jamf_type' => 'MobileDevice'
         ]
      ]);
      $pending_import = [];
      while ($data = $pending_iterator->next()) {
         $pending_import[$data['udid']] = $data;
      }

      $config = Config::getConfigurationValues('plugin:Jamf');
      foreach ($jamf_devices as $jamf_device) {
         if (!in_array($jamf_device['udid'], $imported, true)) {
            $itemtype = strpos($jamf_device['model_identifier'], 'iPhone') !== false ? 'Phone' :  'Computer';
            if (isset($config['autoimport']) && $config['autoimport']) {
               try {
                  $result = self::import($itemtype, $jamf_device['id']);
                  if ($result) {
                     $volume++;
                  }
               } catch (Exception $e2) {
                  // Some other error
               }
            } else {
               if (!array_key_exists($jamf_device['udid'], $pending_import)) {
                  $DB->insert('glpi_plugin_jamf_imports', [
                     'jamf_type'       => 'MobileDevice',
                     'jamf_items_id'   => $jamf_device['id'],
                     'name'            => $DB->escape($jamf_device['name']),
                     'type'            => $itemtype,
                     'udid'            => $jamf_device['udid'],
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

      $jamf_item = static::$api_classic::getItems('mobiledevices', ['id' => $jamf_items_id]);
      if ($jamf_item === null) {
         // API error or device no longer exists in Jamf
         return false;
      }

      $rules = new PluginJamfRuleImportCollection();
      $ruleinput = [
         '_jamf_type'      => 'MobileDevice',
         'name'            => $jamf_item['general']['name'],
         'itemtype'        => $itemtype,
         'last_inventory'  => $jamf_item['general']['last_inventory_update_utc'],
         'managed'         => $jamf_item['general']['managed'],
         'supervised'      => $jamf_item['general']['supervised'],
      ];
      $ruleinput = $rules->processAllRules($ruleinput, $ruleinput, ['recursive' => true]);

      if (isset($ruleinput['_import']) && !$ruleinput['_import']) {
         // Dropped by rules
         return false;
      }

      if ($DB->fieldExists($itemtype::getTable(), 'uuid')) {
         $iterator = $DB->request([
            'SELECT' => [$itemtype::getTable().'.id'],
            'FROM' => $itemtype::getTable(),
            'WHERE' => [
               'uuid' => $jamf_item['general']['udid']
            ],
            'LIMIT' => 1
         ]);
      } else {
         $iterator = $DB->request([
            'SELECT' => [$itemtype::getTable().'.id'],
            'FROM' => $itemtype::getTable(),
            'LEFT JOIN' => [
               'glpi_plugin_jamf_extfields' => [
                  'FKEY' => [
                     'glpi_plugin_jamf_extfields' => 'items_id',
                     $itemtype::getTable() => 'id', [
                        'AND' => [
                           'glpi_plugin_jamf_extfields.itemtype' => $itemtype
                        ]
                     ]
                  ]
               ]
            ],
            'WHERE' => [
               'glpi_plugin_jamf_extfields.name' => 'uuid',
               'glpi_plugin_jamf_extfields.value' => $jamf_item['general']['udid']
            ],
            'LIMIT' => 1
         ]);
      }
      if ($iterator->count()) {
         // Already imported
         Event::log(-1, $itemtype, 4, 'Jamf plugin', "Jamf mobile device $jamf_items_id not imported. A {$itemtype::getTypeName(1)} exists with the same uuid.");
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
         $jamf_mobiledevice = new PluginJamfMobileDevice();
         $r2 = $jamf_mobiledevice->add([
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

      return static::$api_classic::getItems('mobiledevices', ['id' => $jamf_item['jamf_items_id']]) ?? [];
   }

   public static function getSupportedGlpiItemtypes(): array
   {
      return ['Computer', 'Phone'];
   }
}