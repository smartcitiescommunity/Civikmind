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
 * JSS Extension Attribute Item Link class
 *
 * @since 1.1.0
 */
class PluginJamfItem_ExtensionAttribute extends CommonDBChild
{

   static public $itemtype = 'itemtype';
   static public $items_id = 'items_id';

   public static function getTypeName($nb = 1)
   {
      return _nx('itemtype', 'Extension attribute', 'Extension attributes', $nb, 'jamf');
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      /** @var PluginJamfAbstractDevice $jamf_class */
      $jamf_class = PluginJamfAbstractDevice::getJamfItemClassForGLPIItem($item::getType(), $item->getID());
      if ($jamf_class === null) {
         return false;
      }
      $jamf_item = $jamf_class::getJamfItemForGLPIItem($item);
      if ($jamf_class === null || !$jamf_class::canView()) {
         return false;
      }
      return self::createTabEntry(self::getTypeName(2), self::countForJamfItem($jamf_item));
   }

   public static function countForJamfItem($jamf_item)
   {
      return countElementsInTable(self::getTable(), [
         'itemtype' => $jamf_item::getType(),
         'items_id' => $jamf_item->getID()
      ]);
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      return self::showForItem($item);
   }

   public static function showForItem(CommonDBTM $item)
   {
      /** @var PluginJamfAbstractDevice $jamf_class */
      $jamf_class = PluginJamfAbstractDevice::getJamfItemClassForGLPIItem($item::getType(), $item->getID());
      if ($jamf_class === null || !$jamf_class::canView()) {
         return false;
      }

      $mobiledevice = $jamf_class::getJamfItemForGLPIItem($item);
      if ($mobiledevice === null) {
         return false;
      }

      $attributes = $mobiledevice->getExtensionAttributes();

      echo "<table class='tab_cadre_fixe'>";
      echo "<thead>";
      echo "<th>" . _x('field', 'Name', 'jamf') . "</th>";
      echo "<th>" . _x('field', 'Type', 'jamf') . "</th>";
      echo "<th>" . _x('field', 'Value', 'jamf') . "</th>";
      echo "</thead>";
      echo "<tbody>";
      foreach ($attributes as $attribute) {
         echo "<tr><td>{$attribute['name']}</td><td>{$attribute['data_type']}</td><td>{$attribute['value']}</td></tr>";
      }
      echo "</tbody>";
      echo "</table>";
      return true;
   }
}
