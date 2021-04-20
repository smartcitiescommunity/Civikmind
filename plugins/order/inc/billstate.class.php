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

class PluginOrderBillState extends CommonDropdown {
   const NOTPAID = 0;
   const PAID    = 1;

   public static $rightname = 'plugin_order_bill';


   public static function getTypeName($nb = 0) {
      return __("Bill status", "order");
   }


   public function pre_deleteItem() {
      if ($this->getID() <= self::PAID) {
         Session::addMessageAfterRedirect(__("You cannot remove this status", "order").
                                          ": ".$this->fields['name'],
                                          false, ERROR);
         return false;
      } else {
         return true;
      }
   }


   public static function getStates() {
      return [self::NOTPAID => __("Being paid", "order"),
              self::PAID    => __("Paid", "order")];
   }


   public static function getState($states_id) {
      $states = self::getStates();
      if (isset($states[$states_id])) {
         return $states[$states_id];
      } else {
         return '';
      }
   }


   public static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_billstates` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `comment` text COLLATE utf8_unicode_ci,
                    PRIMARY KEY (`id`),
                    KEY `name` (`name`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }
      if (countElementsInTable($table) < 2) {
         $state = new self();
         foreach ([self::PAID     => __("Paid", "order"),
                   self::NOTPAID  => __("Not paid", "order")] as $id => $label) {
            $state->add(['id'   => $id,
                         'name' => Toolbox::addslashes_deep($label)]);
         }
      }
   }


   public static function uninstall() {
      global $DB;

      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
   }


}
