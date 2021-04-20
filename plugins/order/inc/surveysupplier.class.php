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

class PluginOrderSurveySupplier extends CommonDBChild {

   public static $rightname = 'plugin_order_order';

   public static $itemtype  = 'PluginOrderOrder';

   public static $items_id  = 'plugin_order_orders_id';


   public static function getTypeName($nb = 0) {
      return __("Supplier quality", "order");
   }


   public function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_order_orders_id']) || $input['plugin_order_orders_id'] <= 0) {
         return false;
      }
      return $input;
   }


   public function getFromDBByOrder($plugin_order_orders_id) {
      global $DB;

      $table = self::getTable();
      $query = "SELECT *
                FROM `$table`
                WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }


   public function addNotation($field, $value) {

      $rand = mt_rand();

      echo "<table style='font-size:0.9em; width:50%' class='tab_format'>";
      echo "<tr>";
      echo "<td>";
      echo "<select id='$field$rand' name='$field'>";
      for ($i = 0; $i <= 5; $i++) {
         echo "<option value='$i' ".(($i == $value) ? 'selected' : '').
         ">$i</option>";
      }
      echo "</select>";
      echo "<div class='rateit' id='notation$rand'></div>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      echo  "<script type='text/javascript'>\n";
      echo "$('#notation$rand').rateit({value: '".$value."',
                                min : 0,
                                max : 5,
                                step: 1,
                                backingfld: '#$field$rand',
                                ispreset: true,
                                resetable: false});";
      echo "</script>";
   }


   public function getTotalNotation($plugin_order_orders_id) {
      global $DB;

      $table = self::getTable();
      $query  = "SELECT (`answer1` + `answer2` + `answer3` + `answer4` + `answer5`) AS total
                 FROM `$table`
                 WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         return $DB->result($result, 0, "total") / 5 * 2;
      } else {
         return 0;
      }
   }


   public function getNotation($suppliers_id, $field) {
      global $DB;

      $table = self::getTable();
      $query = "SELECT  SUM(survey.`$field`) AS total,
                        COUNT(survey.`id`) AS nb
                FROM `glpi_plugin_order_orders` orders, `$table` survey
                 WHERE survey.`suppliers_id` = orders.`suppliers_id`
                 AND survey.`plugin_order_orders_id` = orders.`id`
                 AND orders.`suppliers_id` = '$suppliers_id'"
              .getEntitiesRestrictRequest(" AND ", "orders", "entities_id", '', true);
      $result = $DB->query($query);
      $nb     = $DB->numrows($result);

      if ($nb) {
         return $DB->result($result, 0, "total") / $DB->result($result, 0, "nb");
      } else {
         return 0;
      }
   }


   public static function showGlobalNotation($suppliers_id) {
      global $DB;

      $config = PluginOrderConfig::getConfig();
      if (!$config->canUseSupplierSatisfaction()) {
         return;
      }

      $survey       = new self();
      $survey_table = $survey->getTable();

      $restrict = getEntitiesRestrictRequest(" AND ", "orders", "entities_id", '', true);

      $query  = "SELECT orders.`id`, orders.`entities_id`, orders.`name`, survey.`comment`
                 FROM `glpi_plugin_order_orders` orders, `$survey_table` survey
                 WHERE survey.`suppliers_id` = orders.`suppliers_id`
                  AND survey.`plugin_order_orders_id` = orders.`id`
                  AND orders.`suppliers_id` = '$suppliers_id'
                 $restrict";
      $query .= "GROUP BY `survey`.id";
      $result   = $DB->query($query);
      $nb       = $DB->numrows($result);
      $total    = 0;
      $nb_order = 0;

      echo "<br>";
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th colspan='4'>".__("Supplier quality", "order")."</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<th>".__("Entity")."</th>";
      echo "<th>".__("Order name", "order")."</th>";
      echo "<th>".__("Note", "order")."</th>";
      echo "<th>".__("Comment on survey", "order")."</th>";
      echo "</tr>";

      if ($nb) {
         for ($i = 0; $i < $nb; $i++) {
            $name        = $DB->result($result, $i, "name");
            $ID          = $DB->result($result, $i, "id");
            $comment     = $DB->result($result, $i, "comment");
            $entities_id = $DB->result($result, $i, "entities_id");
            $note        = $survey->getTotalNotation($ID);
            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo Dropdown::getDropdownName("glpi_entities", $entities_id);
            echo "</td>";
            $link = Toolbox::getItemTypeFormURL('PluginOrderOrder');
            echo "<td><a href=\"".$link."?id=".$ID."\">".$name."</a></td>";
            echo "<td>".$note." / 10</td>";
            echo "<td>".nl2br($comment)."</td>";
            echo "</tr>";
            $total += $survey->getTotalNotation($ID);
            $nb_order++;
         }
         echo "<tr>";
         echo "<th colspan='4'>&nbsp;</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>".
              __("Administrative followup quality (contracts, bills, mail, etc.)", "order").
              "</div></td>";
         echo "<td><div align='left'>".
              Html::formatNumber($survey->getNotation($suppliers_id, "answer1")).
              "&nbsp;/ 10</div></td>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>".
              __("Commercial followup quality, visits, responseness", "order"). "</div></td>";
         echo "<td><div align='left'>".
              Html::formatNumber($survey->getNotation($suppliers_id, "answer2")).
              "&nbsp;/ 10</div></td>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>".__("Contacts availability", "order"). "</div></td>";
         echo "<td><div align='left'>".
              Html::formatNumber($survey->getNotation($suppliers_id, "answer3")).
              "&nbsp;/ 10</div></td>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>".
              __("Quality of supplier intervention", "order"). "</div></td>";
         echo "<td><div align='left'>".
              Html::formatNumber($survey->getNotation($suppliers_id, "answer4")).
              "&nbsp;/ 10</div></td>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>".__("Reliability about annouced delays", "order").
              "</div></td>";
         echo "<td><div align='left'>".
              Html::formatNumber($survey->getNotation($suppliers_id, "answer5")).
              "&nbsp;/ 10</div></td>";

         echo "<tr>";
         echo "<th colspan='4'>&nbsp;</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1 b'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>".__("Final supplier note", "order")."</div></td>";
         echo "<td><div align='left'>".Html::formatNumber($total / $nb_order)."&nbsp;/ 10</div></td>";
         echo "</tr>";
      }
      echo "</table>";
      echo "</div>";
   }


   public function showForm ($ID, $options = []) {
      if (!self::canView()) {
         return false;
      }

      $plugin_order_orders_id = -1;
      if (isset($options['plugin_order_orders_id'])) {
         $plugin_order_orders_id = $options['plugin_order_orders_id'];
      }

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $input = [
            'plugin_order_orders_id' => $options['plugin_order_orders_id']
         ];
         $this->check(-1, UPDATE, $input);
      }

      $this->initForm($ID, $options);

      $options['colspan'] = 1;
      $this->showFormHeader($options);

      $order = new PluginOrderOrder();
      $order->getFromDB($plugin_order_orders_id);
      echo Html::hidden('plugin_order_orders_id', ['value' => $plugin_order_orders_id]);
      echo Html::hidden('entities_id', ['value' => $order->getEntityID()]);
      echo Html::hidden('is_recursive', ['value' => $order->isRecursive()]);

      echo "<tr class='tab_bg_1'><td>".__("Supplier").": </td><td>";
      $suppliers_id = $order->fields["suppliers_id"];
      if ($ID > 0) {
         $suppliers_id = $this->fields["suppliers_id"];
      }
      $link = Toolbox::getItemTypeFormURL('Supplier');
      echo "<a href=\"".$link."?id=".$suppliers_id."\">" .
         Dropdown::getDropdownName("glpi_suppliers", $suppliers_id)."</a>";
      echo Html::hidden('suppliers_id', ['value' => $suppliers_id]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>".
           __("Administrative followup quality (contracts, bills, mail, etc.)", "order").
           ": </td><td>";
      $this->addNotation("answer1", $this->fields["answer1"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>".
           __("Commercial followup quality, visits, responseness", "order").": </td><td>";
      $this->addNotation("answer2", $this->fields["answer2"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>".__("Contacts availability", "order").": </td><td>";
      $this->addNotation("answer3", $this->fields["answer3"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>".
           __("Quality of supplier intervention", "order").": </td><td>";
      $this->addNotation("answer4", $this->fields["answer4"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>".
           __("Reliability about annouced delays", "order").": </td><td>";
      $this->addNotation("answer5", $this->fields["answer5"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>";
      //comments of order
      echo __("Comments").": </td>";
      echo "<td>";
      echo "<textarea cols='80' rows='4' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td>";
      echo "</tr>";

      if ($ID > 0) {
         echo "<tr><th><div align='left'>".__("Average mark up to 10 (X points / 5)", "order")
           .": </div></th><th><div align='left'>";
         $total = $this->getTotalNotation($this->fields["plugin_order_orders_id"]);
         echo Html::formatNumber($total)." / 10";
         echo "</div></th>";
         echo "</tr>";
      }

      $this->showFormButtons($options);
      return true;
   }


   public static function showOrderSupplierSurvey($ID) {
      global $DB, $CFG_GLPI;

      $order = new PluginOrderOrder;
      $order->getFromDB($ID);

      $survey = new self();

      $table = self::getTable();
      Session::initNavigateListItems(__CLASS__, __("Order", "order") ." = ". $order->fields["name"]);

      $candelete = $order->can($ID, DELETE);
      $query     = "SELECT * FROM `$table` WHERE `plugin_order_orders_id` = '$ID' ";
      $result    = $DB->query($query);
      $rand      = mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_suppliersurvey$rand' id='show_suppliersurvey$rand' " .
            " action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\">";
      echo Html::hidden('plugin_order_orders_id', ['value' => $ID]);
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='5'>".__("Supplier quality", "order")."</th></tr>";
      echo "<tr><th>&nbsp;</th>";
      echo "<th>".__("Supplier")."</th>";
      echo "<th>".__("Note", "order")."</th>";
      echo "<th>".__("Comment on survey", "order")."</th>";
      echo "</tr>";

      if ($DB->numrows($result) > 0) {
         while ($data = $DB->fetchArray($result)) {
            Session::addToNavigateListItems(__CLASS__, $data['id']);
            echo Html::hidden("item[".$data["id"]."]", ['value' => $ID]);
            echo "<tr class='tab_bg_1 center'>";
            echo "<td>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[".$data["id"]."]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all') {
                  echo " checked ";
               }
               echo ">";
            }
            echo "</td>";
            $link = Toolbox::getItemTypeFormURL(__CLASS__);
            echo "<td><a href='".$link."?id=".$data["id"]."&plugin_order_orders_id=".$ID."'>"
              .Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"])."</a></td>";
            echo "<td>";
            $total = $survey->getTotalNotation($ID);
            echo $total." / 10";
            echo "</td>";
            echo "<td>";
            echo $data["comment"];
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($candelete) {
            echo "<div class='center'>";
            Html::openArrowMassives("show_suppliersurvey$rand", true);
            Html::closeArrowMassives(["delete" => __("Delete permanently")]);
            echo "</div>";
         }
      } else {
         echo "</table>";
      }

      Html::closeForm();
      echo "</div>";
   }


   public function checkIfSupplierSurveyExists($orders_id) {
      if ($orders_id) {
         return (countElementsInTable(self::getTable(), ['plugin_order_orders_id' => $orders_id]));
      } else {
         return false;
      }
   }


   public static function install(Migration $migration) {
      global $DB;
      //Only avaiable since 1.3.0

      $table = self::getTable();
      if (!$DB->tableExists("glpi_plugin_order_surveysuppliers")) {
         $migration->displayMessage("Installing $table");

         //Installation
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_surveysuppliers` (
                  `id` int(11) NOT NULL auto_increment,
                  `entities_id` int(11) NOT NULL default '0',
                  `is_recursive` tinyint(1) NOT NULL default '0',
                  `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
                  `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
                  `answer1` int(11) NOT NULL default 0,
                  `answer2` int(11) NOT NULL default 0,
                  `answer3` int(11) NOT NULL default 0,
                  `answer4` int(11) NOT NULL default 0,
                  `answer5` int(11) NOT NULL default 0,
                  `comment` text collate utf8_unicode_ci,
                  PRIMARY KEY  (`id`),
                  KEY `plugin_order_orders_id` (`plugin_order_orders_id`),
                  KEY `entities_id` (`entities_id`),
                  KEY `suppliers_id` (`suppliers_id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      } else {
         //upgrade
         $migration->displayMessage("Upgrading $table");

         //1.2.0
         $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");
         $migration->changeField($table, "FK_order", "plugin_order_orders_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)'");
         $migration->changeField($table, "FK_enterprise", "suppliers_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)'");
         $migration->changeField($table, "comment", "comment",
                                 "text collate utf8_unicode_ci");
         $migration->addField($table, "entities_id", "int(11) NOT NULL default '0'");
         $migration->addField($table, "is_recursive", "tinyint(1) NOT NULL default '0'");
         $migration->addKey($table, "plugin_order_orders_id");
         $migration->addKey($table, "suppliers_id");
         $migration->migrationOneTable($table);

         $query = "SELECT `suppliers_id`, `entities_id`,`is_recursive`,`id`
                   FROM `glpi_plugin_order_orders` ";
         foreach ($DB->request($query) as $data) {
            $query = "UPDATE `glpi_plugin_order_surveysuppliers` SET
                        `entities_id` = '{$data["entities_id"]}',
                        `is_recursive` = '{$data["is_recursive"]}'
                      WHERE `plugin_order_orders_id` = '{$data["id"]}' ";
            $DB->query($query) or die($DB->error());
         }
      }
   }


   public static function uninstall() {
      global $DB;

      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".self::getTable()."`") or die ($DB->error());
   }


   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item instanceof PluginOrderOrder) {
         $config = PluginOrderConfig::getConfig();
         if ($config->canUseSupplierSatisfaction()
             && $item->getState() == PluginOrderOrderState::DELIVERED) {
            return [1 => __("Supplier quality", "order")];
         }
      }
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item instanceof PluginOrderOrder) {
         $survey = new self();
         self::showOrderSupplierSurvey($item->getID());
         if (!$survey->checkIfSupplierSurveyExists($item->getID())
             && $item->can($item->getID(), UPDATE)) {
            $survey->showForm("", ['plugin_order_orders_id' => $item->getID()]);
         }
      }

      return true;
   }


}
