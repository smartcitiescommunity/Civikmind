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

class PluginOrderReferenceFree extends CommonDBTM {
   public static $rightname         = 'plugin_order_order';
   public $dohistory                = true;

   public static function getTypeName($nb = 0) {
      return __("Reference free", "order");
   }

   public function addDetails($ref_id, $itemtype, $orders_id, $quantity, $price, $discounted_price, $taxes_id) {
      $order = new PluginOrderOrder();
      if ($quantity > 0 && $order->getFromDB($orders_id)) {
         for ($i = 0; $i < $quantity; $i++) {
            $input["plugin_order_orders_id"]     = $orders_id;
            $input["plugin_order_ordertaxes_id"] = $taxes_id;
            $input["itemtype"]                   = $itemtype;
            $input["entities_id"]                = $order->getEntityID();
            $input["is_recursive"]               = $order->isRecursive();
            $input["price_taxfree"]              = $price;
            $input["price_discounted"]           = $price - ($price * ($discounted_price / 100));
            $input["states_id"]                  = PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED;

            $input["price_ati"] = $this->getPricesATI($input["price_discounted"], Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", $taxes_id));
            $input["discount"]  = $discounted_price;

            $this->add($input);
         }
      }
   }

   public static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         //Install
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_referencefrees` (
               `id` int(11) NOT NULL auto_increment,
               `entities_id` int(11) NOT NULL default '0',
               `is_recursive` tinyint(1) NOT NULL default '0',
               `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
               `name` varchar(255) collate utf8_unicode_ci default NULL,
               `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
               `manufacturers_reference` varchar(255) collate utf8_unicode_ci NOT NULL DEFAULT '',
               `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
               `templates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
               `comment` text collate utf8_unicode_ci,
               `price_taxfree` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_discounted` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `discount` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_ati` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `states_id` int(11) NOT NULL default 1,
               `delivery_date` date default NULL,
               `plugin_order_ordertaxes_id` float NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)',
               `is_deleted` tinyint(1) NOT NULL default '0',
               `is_active` tinyint(1) NOT NULL default '1',
               `notepad` longtext collate utf8_unicode_ci,
               `date_mod` timestamp NULL default NULL,
               PRIMARY KEY  (`id`),
               KEY `name` (`name`),
               KEY `entities_id` (`entities_id`),
               KEY `manufacturers_id` (`manufacturers_id`),
               KEY `templates_id` (`templates_id`),
               KEY `plugin_order_ordertaxes_id` (`plugin_order_ordertaxes_id`),
               KEY `states_id` (`states_id`),
               KEY `is_active` (`is_active`),
               KEY `is_deleted` (`is_deleted`),
               KEY date_mod (date_mod)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());

      }
   }

   public static function uninstall() {
      global $DB;

      $table  = getTableForItemType(__CLASS__);
      foreach (["glpi_displaypreferences", "glpi_documents_items", "glpi_savedsearches",
                      "glpi_logs"] as $t) {
         $query = "DELETE FROM `$t` WHERE `itemtype`='" . __CLASS__ . "'";
         $DB->query($query);
      }

      $DB->query("DROP TABLE IF EXISTS `$table`") or die ($DB->error());
   }
}