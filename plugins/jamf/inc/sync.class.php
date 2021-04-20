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
 * PluginJamfSync class.
 * This is the base class for all classes which sync data from Jamf to GLPI..
 */
abstract class PluginJamfSync
{

   /**
    * The sync task completed successfully.
    */
   public const STATUS_OK = 0;

   /**
    * The sync task was skipped because the required data was not supplied (rights error on JSS), the config denies the sync, or another reason.
    */
   public const STATUS_SKIPPED = 1;

   /**
    * An error occurred during the sync task.
    */
   public const STATUS_ERROR = 2;

   /**
    * An attempt was made to run the sync task without the necessary resources being ready.
    * For example, adding an extension attribute to a mobile device on the first sync before it is created.
    * In this case, the task will get deferred until the sync is finalized. At that stage, the task is retired a final time.
    */
   public const STATUS_DEFERRED = 3;

   /**
    * The sync task does not apply to the itemtype synced by this sync engine.
    * For example, a mobile device may not sync scripts because no such relation exists in Jamf/Apple MDM.
    */
   public const STATUS_NOT_APPLICABLE = 4;

   /**
    * @var bool If true, it indicates an instance of the sync engine was created without the intention of using it for syncing.
    *              Any task that attempts to run, will be set to an error state.
    */
   protected $dummySync = false;

   protected $config = [];

   protected $item_changes = [];

   protected $extitem_changes = [];

   protected $jamfplugin_item_changes = [];

   protected $data = [];

   /** @var CommonDBTM */
   protected $item = null;

   /** @var CommonDBTM */
   protected $jamfplugin_device = null;

   /**
    * @var null
    * @since 1.0.0
    * @since 2.0.0 Renamed jamf_itemtype to jamfplugin_itemtype
    */
   protected static $jamfplugin_itemtype = null;

   /**
    * Textual identifier of the itemtype in Jamf that this sync engine works with.
    *
    * The identifier should typically match up with the Jamf API endpoint path.
    * For example 'MobileDevice' matches with the classic endpoint "/mobiledevices".
    * There shouldn't be any spaces, but may contain uppercase characters to make it easier to read.
    * The identifier may be treated as case-sensitive, but it is not guaranteed.
    *
    * @var string
    * @since 2.0.0
    */
   protected static $jamf_itemtype = null;

   protected $status = [];

   /**
    * @var DBmysql
    */
   protected $db;

   /**
    * @var PluginJamfAPIClassic
    */
   protected static $api_classic = PluginJamfAPIClassic::class;

   /**
    * @var PluginJamfAPIPro
    */
   protected static $api_pro = PluginJamfAPIPro::class;

   /**
    * PluginJamfSync constructor.
    * @param CommonDBTM|null $item
    * @param array $data
    */
   public function __construct(CommonDBTM $item = null, array $data = [])
   {
      /** @global DBmysql */
      global $DB;

      $this->db = $DB;
      if ($item === null) {
         $this->dummySync = true;
         return;
      }
      $this->config = PluginJamfConfig::getConfig();
      $this->item = $item;
      $this->data = $data;
      $jamfitem = static::$jamfplugin_itemtype::getJamfItemForGLPIItem($item);
      //$jamfitem = new static::$jamfplugin_itemtype();
      $this->jamfplugin_device = $jamfitem;
//      $jamf_match = $jamfitem->find([
//         'itemtype' => $item::getType(),
//         'items_id' => $item->getID()], [], 1);
//      if (count($jamf_match)) {
//         $jamf_id = reset($jamf_match)['id'];
//         $jamfitem->getFromDB($jamf_id);
//         $this->jamfplugin_device = $jamfitem;
//      }
   }

   /**
    * Apply all pending changes and retry deferred tasks.
    * @since 1.1.0
    * @return array STATUS_OK if the sync was successful, STATUS_ERROR otherwise.
    */
   protected function finalizeSync()
   {
      if ($this->dummySync) {
         return $this->status;
      }
      $this->jamfplugin_item_changes['sync_date'] = $_SESSION['glpi_currenttime'];
      $this->item->update([
            'id' => $this->item->getID()
         ] + $this->item_changes);
      foreach ($this->extitem_changes as $key => $value) {
         PluginJamfExtField::setValue($this->item::getType(), $this->item->getID(), $key, $value);
      }
      $this->db->updateOrInsert(static::$jamfplugin_itemtype::getTable(), $this->jamfplugin_item_changes, [
         'itemtype' => $this->item::getType(),
         'items_id' => $this->item->getID()
      ]);

      if ($this->jamfplugin_device === null) {
         $jamf_item = new static::$jamfplugin_itemtype();
         $jamf_match = $jamf_item->find([
            'itemtype' => $this->item::getType(),
            'items_id' => $this->item->getID()], [], 1);
         if (count($jamf_match)) {
            $jamf_item->getFromDB(reset($jamf_match)['id']);
            $this->jamfplugin_device = $jamf_item;
         }
      }

      // Re-run all deferred tasks
      $deferred = array_keys($this->status, self::STATUS_DEFERRED);
      foreach ($deferred as $task) {
         if (method_exists($this, $task)) {
            $this->$task();
         } else {
            $this->status[$task] = self::STATUS_ERROR;
         }
      }
      return $this->status;
   }

   protected function createOrGetItem($itemtype, $criteria, $params)
   {
       $item = new $itemtype();
       $item_matches = $item->find($criteria);
       if (!count($item_matches)) {
           $items_id = $item->add($params);
           $item->getFromDB($items_id);
       } else {
           $item->getFromDB(reset($item_matches)['id']);
       }
       return $item;
   }

   protected function applyDesiredState($itemtype, $match_criteria, $state, $options = []): CommonDBTM
   {
      $opts = [];
      $opts = array_replace($opts, $options);

      /** @var CommonDBTM $item */
      $item = new $itemtype();
      $item_matches = $item->find($match_criteria);
      if (!count($item_matches)) {
         $items_id = $item->add($state);
         $item->getFromDB($items_id);
         return $item;
      }

      $match = reset($item_matches);
      $item->getFromDB($match['id']);
      $item->update(['id' => $match['id']] + $state);
      return $item;
   }

   abstract public static function discover(): bool;

   abstract public static function import(string $itemtype, int $jamf_items_id, $use_transaction = true): bool;

   abstract public static function syncAll(): int;

   /**
    * Get the data needed to sync the given GLPI item with a Jamf item from the Jamf API.
    *
    * It is assumed that the GLPI item's existence was already verified. This function should verify that the GLPI item is linked to a Jamf item.
    * @param string $itemtype GLPI item type
    * @param int $items_id GLPI item ID
    * @return array
    * @since 1.0.0
    */
   abstract protected static function getJamfDataForSyncingByGlpiItem(string $itemtype, int $items_id): array;

   abstract public static function sync(string $itemtype, int $items_id, bool $use_transaction = true): bool;

   abstract public static function getSupportedGlpiItemtypes(): array;

   public static function isSupportedGlpiItemtype(string $itemtype): bool
   {
      return in_array($itemtype, static::getSupportedGlpiItemtypes(), true);
   }

   /**
    * @return PluginJamfSync[]
    * @since 1.0.0
    */
   final public static function getDeviceSyncEngines(): array
   {
      return [
         PluginJamfMobileDevice::class => PluginJamfMobileSync::class,
         PluginJamfComputer::class => PluginJamfComputerSync::class
      ];
   }
}
