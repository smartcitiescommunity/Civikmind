<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderOther extends CommonDBTM {

   public static $rightname = 'plugin_order_order';


   public static function getTypeName($nb = 0) {
      return __("Other kind of items");
   }


   public static function install(Migration $migration) {
      global $DB;

      //Only avaiable since 1.2.0
      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_others` (
                  `id` int(11) NOT NULL auto_increment,
                  `entities_id` int(11) NOT NULL default '0',
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  `othertypes_id` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`ID`),
                  KEY `name` (`name`),
                  KEY `entities_id` (`entities_id`),
                  KEY `othertypes_id` (`othertypes_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }
   }


   public static function uninstall() {
      global $DB;

      //Current table name
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
   }


}
