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
 * Class PluginResourcesImportColumn
 */
class PluginResourcesImportColumn extends CommonDBChild {

   static $rightname = 'plugin_resources_import';
   public $dohistory = true;

   static public $itemtype = PluginResourcesImport::class;
   static public $items_id = 'plugin_resources_imports_id';

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Column', 'Columns', $nb, 'resources');
   }

   static function getColumnsTypes(){
      return [
         __("Integer", "resources"),
         __("Decimal", "resources"),
         __("String", "resources"),
         __("Date", "resources"),
      ];
   }

   /**
    * Alternative to find to order array by resource_column
    */
   function getColumnsByImport($importID, $distinctResourceColumns = false){

      global $DB;

      $query = 'SELECT * from '.self::getTable();
      $query.= " WHERE ".self::$items_id." = ".$importID;

      if($distinctResourceColumns){
         $query.= " GROUP BY resource_column";
      }

      $query.= " ORDER BY resource_column";

      $results = $DB->query($query);

      $temp = [];

      $it = 0;
      while ($data = $DB->fetchAssoc($results)) {
         $temp[$it] = $data;
         $it++;
      }
      return $temp;
   }

   /**
    * Get Tab Name used for itemtype
    *
    * NB : Only called for existing object
    *      Must check right on what will be displayed + template
    *
    * @since version 0.83
    *
    * @param CommonDBTM|CommonGLPI $item CommonDBTM object for which the tab need to be displayed
    * @param bool|int              $withtemplate boolean  is a template object ? (default 0)
    *
    * @return string tab name
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      // can exists for template
      if ($item->getType() == self::$itemtype) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            $dbu = new DbUtils();
            $table = $dbu->getTableForItemType(__CLASS__);
            return self::createTabEntry(self::getTypeName(),
               $dbu->countElementsInTable($table,
                  [PluginResourcesImport::$keyInOtherTables => $item->getID()]));
         }
         return self::getTypeName();
      }
      return '';
   }

   /**
    * show Tab content
    *
    * @since version 0.83
    *
    * @param          $item                  CommonGLPI object for which the tab need to be displayed
    * @param          $tabnum       integer  tab number (default 1)
    * @param bool|int $withtemplate boolean  is a template object ? (default 0)
    *
    * @return true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == self::$itemtype) {
         self::showForImport($item, $withtemplate);
      }
      return true;
   }

   static function getIdentifierNames(){
      return [
         __("No Identifier", "resources"),
         __("Level 1 Identifier", "resources"),
         __("Level 2 Identifier", "resources")
      ];
   }

   function getIsIdentifierDropdown($value, $disabled = false){

      $names = self::getIdentifierNames();

      $param = [
         'value' => $value,
         'disabled' => $disabled
      ];

      return Dropdown::showFromArray("is_identifier", $names, $param);
   }

   public static function showForImport(PluginResourcesImport $import, $withtemplate = ''){
      $importInstance = new self();
      $sID            = $import->fields['id'];
      $rand    = mt_rand();

      $jsFunctionName = "viewAddColumn$sID$rand";
      $viewDomElementName = "viewcolumn$sID$rand";

      $canadd   = Session::haveRight(self::$rightname, CREATE);
      $canedit  = Session::haveRight(self::$rightname, UPDATE);
      $canpurge = Session::haveRight(self::$rightname, PURGE);

      echo "<div id='$viewDomElementName'></div>\n";
      if ($canadd) {

         $importInstance->addEvent($sID, $jsFunctionName, $viewDomElementName);

         echo "<div class='center'>";
         echo "<a href='javascript:$jsFunctionName();'>";
         echo __('Add a column', 'resources') ;
         echo "</a></div><br>\n";
      }
      // Display existing columns
      $columns = $importInstance->find([self::$items_id => $sID], 'id');
      if (count($columns) == 0) {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
         echo "<th class='b'>" . __('No columns for this import', 'resources') . "</th>";
         echo "</tr></table>";
      } else {
         $rand = mt_rand();
         if ($canpurge) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
         }
         echo "<table class='tab_cadre_fixehov'>";
         // Title
         echo "<tr>";
         echo "<th colspan='5'>" . self::getTypeName(2) . "</th>";
         echo "</tr>";

         // Columns
         echo "<tr>";
         if ($canpurge) {
            echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
         }

         // Columns
         echo "<th>" . __('Name') . "</th>";
         echo "<th>" . __('Type') . "</th>";
         echo "<th>" . __('Resource Attribute', 'resources') . "</th>";
         echo "<th>" . __('Identifiers', 'resources') . "</th>";
         echo "</tr>";
         foreach ($columns as $column) {
            if ($importInstance->getFromDB($column['id'])) {
               $importInstance->showOne($viewDomElementName, $canedit, $canpurge, $rand);
            }
         }
         echo "</table>";
         if ($canpurge) {
            $paramsma['ontop'] = false;
            Html::showMassiveActions($paramsma);
            Html::closeForm();
         }
      }
   }

   public function showOne($viewDomElementName, $canedit, $canpurge, $rand){
      $jsFunctionName = "viewEditColumn".$this->fields[self::$items_id].$this->fields['id'].$rand;

      if ($canedit) {
         $style = "style='cursor:pointer'";
         $event = "onclick='$jsFunctionName()'";
      }else{
         $style = '';
         $event = '';
      }

      echo "<tr class='tab_bg_2' $style $event>";

      if ($canpurge) {
         echo "<td width='10'>";
         Html::showMassiveActionCheckBox(__CLASS__, $this->fields["id"]);
         echo "</td>";
      }
      if ($canedit) {
         $this->editEvent($jsFunctionName, $viewDomElementName);
      }

      // NAME
      echo "<td class='left' style='text-align:center'>" . nl2br($this->fields["name"]) . "</td>";

      // Type
      $array = PluginResourcesImportColumn::getColumnsTypes();
      echo "<td style='text-align:center'>";
      Dropdown::showFromArray('type', $array,
         [
            'value'     => $this->fields['type'],
            'disabled' => true
         ]);

      echo "</td>";

      // Resource attribute
      $array = PluginResourcesResource::getDataNames();
      echo "<td style='text-align:center'>";
      Dropdown::showFromArray('resource_column', $array,
         [
            'value'     => $this->fields['resource_column'],
            'disabled' => true
         ]);

      echo "</td>";

      echo "<td style='text-align:center'>";
      self::getIsIdentifierDropdown($this->getField('is_identifier'), true);
      echo "</td>";

      echo "</tr>";
   }

   public function showForm($ID, $options = []){
      if (isset($options['parent']) && !empty($options['parent'])) {
         $import = $options['parent'];
      }

      $importColumn = new self();
      if ($ID <= 0) {
         $importColumn->getEmpty();
         $title = "<tr><th colspan='4'>" . __('Add a column', 'resources') . "</th></tr>";
         $name = "";
         $submitButton = "<input type='submit' name='add' class='submit' value='" . _sx('button', 'Add') . "' >";
      } else {
         $importColumn->getFromDB($ID);
         $title = "<tr><th colspan='4'>" . __('Edit a column', 'resources') . "</th></tr>";
         $name = $importColumn->getField('name');
         $submitButton = "<input type='submit' name='update' class='submit' value='" . _sx('button', 'Save') . "' >";
      }
      if (!$importColumn->canView()) {
         return false;
      }

      echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL(self::getType()) . "'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";

      echo Html::hidden('id', ['value' => $importColumn->getID()]);
      echo Html::hidden(self::$items_id, ['value' => $import->getID()]);

      echo $title;

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."</td>";
      echo "<td><input type='text' name='name' value=\"$name\"></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Type')."</td>";
      echo "<td>";
      Dropdown::showFromArray(
         'type',
         PluginResourcesImportColumn::getColumnsTypes(),
         ['value'     => $importColumn->fields['type']]
      );

      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Resource Attribute', 'resources')."</td>";
      echo "<td>";
      Dropdown::showFromArray(
         'resource_column',
         PluginResourcesResource::getDataNames(),
         ['value'     => $importColumn->fields['resource_column']]
      );

      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>".__('Identifiers', 'resources')."</td>";
      echo "<td>";
      $this->getIsIdentifierDropdown($importColumn->getField('is_identifier'), false);
      echo "</td>";

      echo "</tr>";

      echo "<tr>";
      echo "<td class='tab_bg_2 center' colspan='4'>";

      echo $submitButton;

      echo "</td>";
      echo "</tr>";

      echo "</table></div>";
      Html::closeForm();
      return true;
   }


   private function addEvent($ID, $jsFunctionName, $viewDomElementName){
      global $CFG_GLPI;
      echo "<script type='text/javascript' >\n";
      echo "function $jsFunctionName() {\n";
      $params = [
         'type'          => __CLASS__,
         'parenttype'    => self::$itemtype,
         self::$items_id => $ID,
         'id'            => -1];
      $url = $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php";
      Ajax::updateItemJsCode($viewDomElementName, $url, $params);
      echo "};";
      echo "</script>\n";
   }

   private function editEvent($jsFunctionName, $viewDomElementName){
      global $CFG_GLPI;
      $url = $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php";

      $params = [
         'type'          => __CLASS__,
         'parenttype'    => self::$itemtype,
         self::$items_id => $this->fields[self::$items_id],
         'id'            => $this->fields["id"]
      ];

      echo "\n<script type='text/javascript' >\n";
      echo "function $jsFunctionName(){\n";
      echo "console.log('EDIT CALLED');\n";
      Ajax::updateItemJsCode($viewDomElementName, $url, $params);
      echo "};";
      echo "</script>\n";
   }

}