<?php
/*
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
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Class for a Dropdown
class PluginOrderOrderTax extends CommonDropdown {

   public static $rightname = 'plugin_order_order';


   public static function getTypeName($nb = 0) {
      return __("VAT", "order");
   }


   public static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();

      if (!$DB->tableExists($table) && !$DB->tableExists("glpi_dropdown_plugin_order_taxes")) {
         $migration->displayMessage("Installing $table");

         //Install
         $query = "CREATE TABLE `glpi_plugin_order_ordertaxes` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  `comment` text collate utf8_unicode_ci,
                  PRIMARY KEY  (`id`),
                  KEY `name` (`name`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());

         $taxes = new self();
         foreach (['20', '5.5', '19.6'] as $tax) {
            $taxes->add(['name' => $tax]);
         }
      } else {
         //Update

         $migration->displayMessage("Migrating $table");

         //1.2.0
         $migration->renameTable("glpi_dropdown_plugin_order_taxes", $table);
         $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");
         $migration->changeField($table, "name", "name", "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->changeField($table, "comments", "comment", "text collate utf8_unicode_ci");
         $migration->migrationOneTable($table);

         //Remplace , by . in taxes
         foreach ($DB->request("SELECT `name` FROM `$table`") as $data) {
            if (strpos($data["name"], ',')) {
               $name  = str_replace(',', '.', $data["name"]);
               $query = "UPDATE `$table`
                         SET `name` = '" . $name . "'
                         WHERE `name`= '" . $data["name"] . "'";
               $DB->query($query) or die($DB->error());
            }
         }
      }
   }


   public static function uninstall() {
      global $DB;

      //Old table
      $DB->query("DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_taxes`") or die ($DB->error());

      //New table
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
   }

   /**
    * Get the tax rate of loaded item.
    *
    * @return number
    */
   public function getRate() {

      if (array_key_exists('name', $this->fields) && !empty($this->fields['name'])) {
         return floatval($this->fields['name']);
      }

      return 0;
   }
}
