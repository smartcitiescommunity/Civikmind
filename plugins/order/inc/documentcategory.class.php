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

class PluginOrderDocumentCategory extends CommonDBTM {


   static function getTypeName($nb = 0) {
      return __("Document category", "order");
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $config = PluginOrderConfig::getConfig();

      if ($item->getType() == "DocumentCategory" && $config->canRenameDocuments()) {
         return __("Orders", "order");
      }

      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $config = PluginOrderConfig::getConfig();

      if ($item->getType() == "DocumentCategory" && $config->canRenameDocuments()) {
         self::showForDocumentCategory($item);
      }
      return true;
   }


   static function purgeItem($item) {
      $temp = new self();
      $temp->deleteByCriteria([
         'documentcategories_id' => $item->getField("id")
      ]);
   }


   static function showForDocumentCategory($item) {
      $documentCategory = new self();
      if (!$documentCategory->getFromDBByCrit(['documentcategories_id' => $item->fields['id']])) {
         $documentCategory->getEmpty();
      }

      echo "<form name='form' method='post' action='".
      Toolbox::getItemTypeFormURL($documentCategory->getType())."'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Document category prefix', 'order')."</th></tr>";

      echo "<tr class='tab_bg_1'>";
      // Dropdown group
      echo "<td>";
      echo __('Document category prefix', 'order');
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='documentcategories_prefix' value='".$documentCategory->fields['documentcategories_prefix']."'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td class='tab_bg_2 center' colspan='6'>";
      echo "<input type='submit' name='update' class='submit' value='"._sx('button', 'Save')."' >";
      echo Html::hidden('documentcategories_id', ['value' => $item->getID()]);
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      Html::closeForm();
   }


   //------------------------------------------------------------
   //--------------------Install / uninstall --------------------
   //------------------------------------------------------------

   static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();
      //Installation
      if (!$DB->tableExists($table)
          && !$DB->tableExists("glpi_plugin_order_documentcategories")) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_documentcategories` (
                     `id` int(11) NOT NULL auto_increment,
                     `documentcategories_id` int(11) NOT NULL default '0',
                     `documentcategories_prefix` varchar(255) collate utf8_unicode_ci default NULL,
                     PRIMARY KEY  (`id`),
                     KEY `documentcategories_id` (`documentcategories_id`),
                     UNIQUE KEY `unicity` (`documentcategories_id`, `documentcategories_prefix`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }
   }


   static function uninstall() {
      global $DB;
      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".self::getTable()."`") or die ($DB->error());
   }


}
