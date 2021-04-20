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
 * Class PluginResourcesTask_Item
 */
class PluginResourcesTask_Item extends CommonDBTM {

   static $rightname = 'plugin_resources_task';

   /**
    * @return bool|\booleen
    */
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return bool|\booleen
    */
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $withtemplate
    *
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'PluginResourcesTask') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(_n('Associated item', 'Associated items', 2), self::countForResourceTask($item));
            }
            return _n('Associated item', 'Associated items', 2);
         }
      }
      return '';
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $tabnum
    * @param int         $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      $self = new self();
      if ($item->getType() == 'PluginResourcesTask') {
         $self->showItemFromPlugin($item->getID(), $withtemplate);
      }
      return true;
   }

   /**
    * @param \PluginResourcesTask $item
    *
    * @return int
    */
   static function countForResourceTask(PluginResourcesTask $item) {

      $types = PluginResourcesResource::getTypes();
      if (count($types) == 0) {
         return 0;
      }
      $dbu   = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_resources_tasks_items',
                                        ["plugin_resources_tasks_id" => $item->getID(),
                                         "itemtype"                      => $types
                                        ]);
   }

   /**
    * @param $plugin_resources_tasks_id
    * @param $items_id
    * @param $itemtype
    *
    * @return bool
    */
   function getFromDBbyTaskAndItem($plugin_resources_tasks_id, $items_id, $itemtype) {
      global $DB;

      $query = "SELECT * FROM `".$this->getTable()."` ".
              "WHERE `plugin_resources_tasks_id` = '".$plugin_resources_tasks_id."' 
			AND `itemtype` = '".$itemtype."'
			AND `items_id` = '".$items_id."'";
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

   /**
    * @param $values
    */
   function addTaskItem($values) {

      $args = explode(",", $values['item_item']);
      if (isset($args[0]) && isset($args[1])) {
         $this->add(['plugin_resources_tasks_id' => $values["plugin_resources_tasks_id"],
             'items_id' => $args[0],
             'itemtype' => $args[1]]);
      }
   }

   /**
    * @param $plugin_resources_tasks_id
    * @param $items_id
    * @param $itemtype
    *
    * @return bool
    */
   function deleteItemByTaskAndItem($plugin_resources_tasks_id, $items_id, $itemtype) {

      if ($this->getFromDBbyTaskAndItem($plugin_resources_tasks_id, $items_id, $itemtype)) {
         return $this->delete(['id' => $this->fields["id"]]);
      }

      return false;
   }

   /**
    * @param        $instID
    * @param string $withtemplate
    */
   function showItemFromPlugin($instID, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $PluginResourcesTask = new PluginResourcesTask();
      if ($PluginResourcesTask->getFromDB($instID)) {

         $plugin_resources_resources_id = $PluginResourcesTask->fields["plugin_resources_resources_id"];
         $PluginResourcesResource = new PluginResourcesResource();
         $PluginResourcesResource->getFromDB($plugin_resources_resources_id);

         $canedit = $PluginResourcesResource->can($plugin_resources_resources_id, UPDATE);

         $query = "SELECT `items_id`, `itemtype` 
               FROM `".$this->getTable()."` 
               WHERE `plugin_resources_tasks_id` = '$instID' 
               ORDER BY `itemtype` ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);

         echo "<form method='post' name='addtaskitem' action=\"./task.form.php\">";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='".($canedit ? 3 : 2)."'>"._n('Associated item', 'Associated items', 2);
         echo "</th></tr>";
         echo "<tr><th>"._n('Type', 'Types', 2)."</th>";
         echo "<th>".__('Name')."</th>";
         if ($canedit && $this->canCreate() && $withtemplate < 2) {
            echo "<th>&nbsp;</th>";
         }
         echo "</tr>";
         $used = [];
         $dbu = new DbUtils();
         if ($number != "0") {

            for ($i = 0; $i < $number; $i++) {
               $type = $DB->result($result, $i, "itemtype");
               $items_id = $DB->result($result, $i, "items_id");
               if (!class_exists($type)) {
                  continue;
               }
               $item = new $type();
               if ($item->canView()) {
                  $table = $dbu->getTableForItemType($type);
                  $query = "SELECT `".$table."`.*, `".$this->getTable()."`.`id` as items_id 
                        FROM `".$this->getTable()."` 
                        INNER JOIN `".$table."` ON (`".$table."`.`id` = `".$this->getTable()."`.`items_id`) 
                        WHERE `".$this->getTable()."`.`itemtype` = '".$type."' 
                        AND `".$this->getTable()."`.`items_id` = '".$items_id."' 
                        AND `".$this->getTable()."`.`plugin_resources_tasks_id` = '$instID' ";
                  $query.= "ORDER BY `".$table."`.`name` ";
                  $result_linked = $DB->query($query);

                  if ($DB->numrows($result_linked)) {

                     while ($data = $DB->fetchAssoc($result_linked)) {
                        $ID = "";
                        $itemID = $data["id"];
                        $used[] = $itemID;
                        if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                           $ID = " (".$data["id"].")";
                        }
                        $itemname = $data["name"];
                        if ($type == 'User') {
                           $itemname = $dbu->getUserName($itemID);
                        }

                        $link = Toolbox::getItemTypeFormURL($type);
                        $name = "<a href=\"".$link."\">".$itemname."$ID</a>";
                        echo "<tr class='tab_bg_1'>";
                        echo "<td class='center'>".$item->getTypeName()."</td>";

                        echo "<td class='center' ".(isset($data['is_deleted']) && $data['is_deleted'] == '1' ? "class='tab_bg_2_2'" : "").">".$name."</td>";
                        if ($canedit && $this->canCreate() && $withtemplate < 2) {
                           echo "<td class='center' class='tab_bg_2'>";
                           Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/resources/front/task.form.php', 'deletetaskitem', _x('button', 'Delete permanently'), ['id' => $data["items_id"]]);
                           echo "</td>";
                        }
                        echo "</tr>";
                     }
                  }
               }
            }
         }
         if ($canedit && $this->canCreate() && $withtemplate < 2) {
            echo "<tr class='tab_bg_1'><td colspan='2' class='right'>";
            echo "<input type='hidden' name='plugin_resources_tasks_id' value='$instID'>";
            $PluginResourcesResource_Item = new PluginResourcesResource_Item();
            $PluginResourcesResource_Item->dropdownItems($plugin_resources_resources_id, $used);
            echo "</td>";
            echo "<td class='center' colspan='2' class='tab_bg_2'>";
            echo "<input type='submit' name='addtaskitem' value=\""._sx('button', 'Add')."\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>";
         } else {

            echo "</table></div>";
         }
         Html::closeForm();
         echo "<br>";
      }
   }

}

