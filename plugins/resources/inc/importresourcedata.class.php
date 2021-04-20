<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesImportResourceData
 */
class PluginResourcesImportResourceData extends CommonDBChild {

   static $rightname = 'plugin_resources_importresourcedatas';

   // From CommonDBChild
   public static $itemtype = 'PluginResourcesImportResource';
   public static $items_id = 'plugin_resources_importresources_id';


   public function prepareInput($name, $value, $parent_id, $column_id){

      return [
         'name' => $name,
         'value' => $value,
         self::$items_id => $parent_id,
         'plugin_resources_importcolumns_id' => $column_id
      ];
   }

   public function purgeDatabase(){
      global $DB;

      $query = "DELETE FROM `".self::getTable()."`";
      return $DB->query($query);
   }

   public function purgeDataByImportResource($importResourceId){
      global $DB;

      $query =
         "DELETE FROM `".self::getTable()."`".
         " WHERE `plugin_resources_importresources_id` = ".$importResourceId;

      return $DB->query($query);
   }

   public function getFromParentAndIdentifierLevel($importResourceId, $identifierLevel = null, $order = []){

      global $DB;

      $query =
         "SELECT data.id, data.name, data.value, ic.resource_column, ic.type".
         " FROM `".self::getTable()."` as data".
         " INNER JOIN `".PluginResourcesImportColumn::getTable()."` as ic".
         " ON ic.`id` = data.`plugin_resources_importcolumns_id`".
         " WHERE data.`plugin_resources_importresources_id` = ".$importResourceId;

      if($identifierLevel){
         $query.=" AND ic.`is_identifier` = ".$identifierLevel;
      }

      if(count($order)){
         $query.= " ORDER BY ";

         foreach($order as $o){
            $query.= "`$o` ";
         }
      }

      $results = $DB->query($query);
      $temp = [];

      while ($data = $DB->fetchAssoc($results)) {
         $temp[] = $data;
      }
      return $temp;
   }
}