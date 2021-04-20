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
 * Class PluginResourcesImport
 */
class PluginResourcesImport extends CommonDBTM {

   static $rightname = 'plugin_resources_import';
   public $dohistory = true;

   static $keyInOtherTables = 'plugin_resources_imports_id';

   static function getFormUrl($full = true){
      global $CFG_GLPI;
      return $CFG_GLPI["root_doc"] . "/plugins/resources/front/import.form.php";
   }

   static function getIndexUrl(){
      global $CFG_GLPI;
      return $CFG_GLPI["root_doc"] . "/plugins/resources/front/import.php";
   }

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Import', 'Imports', $nb, 'resources');
   }

   /**
    * Define tabs to display
    *
    * NB : Only called for existing object
    *
    * @param $options array
    *     - withtemplate is a template view ?
    *
    * @return array containing the onglets
    **/
   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(PluginResourcesImportColumn::class, $ong, $options);
      return $ong;
   }

   /***
    *
    *
    * @param $identifier
    * @return array
    */
   function getChildColumns($importID, $identifier = null){

      $column = new PluginResourcesImportColumn();

      $input = [
         PluginResourcesImportColumn::$items_id => $importID
      ];

      if(!is_null($identifier)){
         $input['is_identifier'] = $identifier;
      }

      return $column->find($input);
   }

   function showTitle($links = true, $display = true){
      $html = '<div class="center">';
      $title = '<h1>'.$this->getTypeName()."</h1>";

      if($links){

         $html.= '<a href="'.self::getIndexUrl().'" class="pointer" title="'.__("List of Imports").'">';
         $html.= $title.'</a>';

         if(Session::haveright(self::$rightname, CREATE)){
            $html.= '<a href="'.self::getFormUrl().'" class="pointer" title="'.__("Add an Import").'"><i class="fas fa-plus fa-2x"></i>';
            $html .='</a>';
         }

      }else{
         $html.= $title;
      }

      $html.= '<br></div>';

      if($display){
         echo $html;
      }
   }

   /**
    * Print survey
    *
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {
      if (!$this->canView()) {
         return false;
      }
      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td>" . __('Comments') . "</td>";
      echo "<td>";
      echo "<textarea cols='60' rows='6' name='comment' >" . $this->fields["comment"] . "</textarea>";
      echo "</td></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Active') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo "</td><td colspan='2'></td></tr>";
      $this->showFormButtons($options);
      Html::closeForm();
      return true;
   }

}