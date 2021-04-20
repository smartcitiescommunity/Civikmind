<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2011 by the mreporting Development Team.

 https://forge.indepnet.net/projects/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMreportingConfig extends CommonDBTM {
   static $rightname = 'config';

   static function getTypeName($nb = 0) {
      return __("Configuration", 'mreporting');
   }

   /**
    * Définition des onglets
   **/
   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginMreportingProfile', $ong, $options);
      return $ong;
   }


   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => self::getTypeName(),
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'itemlink_type'       => $this->getType(),
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'is_active',
         'name'               => __('Active'),
         'datatype'           => 'bool',
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'show_area',
         'name'               => __('See area', 'mreporting'),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'spline',
         'name'               => __('Curve lines (SVG)', 'mreporting'),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'show_label',
         'name'               => __('See values', 'mreporting'),
         'datatype'           => 'specific',
         'searchtype'         => 'equals',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'flip_data',
         'name'               => __('Reverse data array', 'mreporting'),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'unit',
         'name'               => __('Unit', 'mreporting'),
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'default_delay',
         'name'               => __('Default delay', 'mreporting'),
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this->getTable(),
         'field'              => 'condition',
         'name'               => __('Additional condition for MySQL', 'mreporting'),
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => $this->getTable(),
         'field'              => 'show_graph',
         'name'               => __('See graphic', 'mreporting'),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'classname',
         'name'               => __('Class', 'mreporting'),
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '12',
         'table'              => $this->getTable(),
         'field'              => 'graphtype',
         'name'               => __('Default chart format', 'mreporting'),
         'searchtype'         => 'equals',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '13',
         'table'              => $this->getTable(),
         'field'              => 'is_notified',
         'name'               => __('Send this report with the notification', 'mreporting'),
         'datatype'           => 'bool',
         'massiveaction'      => true,
      ];

      return $tab;
   }

   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'graphtype':
            return $values[$field];
            break;
         case 'show_label':
            $labels = self::getLabelTypes();
            return $labels[$values[$field]];
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name            (default '')
    * @param $values          (default '')
    * @param $options   array
    **/
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;
      $options['value']   = $values[$field];
      switch ($field) {
         case 'graphtype':
            return Dropdown::showFromArray($name,
                                           ['PNG'=>'PNG', 'SVG'=>'SVG'],
                                           $options);
            break;
         case 'show_label':
            return self::dropdownLabel($name, $options);
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   function getFromDBByFunctionAndClassname($function, $classname) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `name` = '".addslashes($function)."'
                AND `classname` = '".addslashes($classname)."'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }

   /**
    * add First config Link
    *@return nothing
    **/
   static function addFirstconfigLink() {
      global $CFG_GLPI;

      $buttons = [];
      $title = "";

      if (Session::haveRight('config', READ)) {
         $buttons["config.php?new=1"] = __("Initialize graphics configuration", 'mreporting');
      }
      Html::displayTitle(Plugin::getWebDir('mreporting') . "/pics/config2.png",
                        $title, $title, $buttons);

   }

   /**
    * create First Config for all graphs
    *@return nothing
    **/
   function createFirstConfig() {
      //$reports = array();
      $classConfig = false;

      $inc_dir = Plugin::getPhpDir('mreporting') . "/inc";
      //parse inc dir to search report classes
      $classes = PluginMreportingCommon::parseAllClasses($inc_dir);

      foreach ($classes as $classname) {

         if (!class_exists($classname)) {
            $class_filedir = $inc_dir.
                             strtolower(str_replace('PluginMreporting', '', $classname)).".class.php";
            if (file_exists($class_filedir)) {
               require_once $class_filedir;
            } else {
               continue;
            }
         }

         $functions = get_class_methods($classname);

         // We check if a config function exists in class
         foreach ($functions as $funct_name) {
            if ($funct_name == 'preconfig') { // If a preconfig exists we construct the class
               $classConfig = true;
               $classObject = new $classname([]);
            }
         }

         foreach ($functions as $funct_name) {

            $ex_func = preg_split('/(?<=\\w)(?=[A-Z])/', $funct_name);
            if ($ex_func[0] != 'report') {
               continue;
            }

            $input = [];

            if ($classConfig) { // If a preconfig exists in class we do it
               $input = $classObject->preconfig($funct_name, $classname, $this);
            } else {// Else we get the default preconfig
               $input = $this->preconfig($funct_name, $classname);
            }

            $input["firstconfig"] = 1;
            unset($input["id"]);
            $newid = $this->add($input);
         }
      }

   }

   /**
    * Preconfig datas for standard system
    * @graphname internal name of graph
    *@return nothing
    **/
   function preconfig($funct_name, $classname) {

      if ($funct_name != -1 && $classname) {

         $ex_func = preg_split('/(?<=\\w)(?=[A-Z])/', $funct_name);
         if ($ex_func[0] != 'report') {
            return false;
         }
         $gtype = strtolower($ex_func[1]);

         switch ($gtype) {
            case 'area':
            case 'garea':
               $this->fields["name"]=$funct_name;
               $this->fields["classname"]=$classname;
               $this->fields["is_active"]="1";
               $this->fields["show_area"]="1";
               $this->fields["show_graph"]="1";
               $this->fields["spline"]="1";
               $this->fields["default_delay"]="365";
               $this->fields["graphtype"]="SVG";
               break;
            case 'line':
            case 'gline':
               $this->fields["name"]=$funct_name;
               $this->fields["classname"]=$classname;
               $this->fields["is_active"]="1";
               $this->fields["spline"]="1";
               $this->fields["show_area"]="0";
               $this->fields["show_graph"]="1";
               $this->fields["default_delay"]="365";
               $this->fields["graphtype"]="SVG";
               break;
            case 'vstackbar':
               $this->fields["name"]=$funct_name;
               $this->fields["classname"]=$classname;
               $this->fields["is_active"]="1";
               $this->fields["show_graph"]="1";
               $this->fields["default_delay"]="365";
               $this->fields["graphtype"]="SVG";
               break;
            case 'hgbar':
               $this->fields["name"]=$funct_name;
               $this->fields["classname"]=$classname;
               $this->fields["is_active"]="1";
               $this->fields["show_graph"]="1";
               $this->fields["show_label"]="hover";
               $this->fields["spline"]="0";
               $this->fields["show_area"]="0";
               $this->fields["default_delay"]="365";
               $this->fields["graphtype"]="SVG";
               break;
            default:
               $this->fields["name"]=$funct_name;
               $this->fields["classname"]=$classname;
               $this->fields["is_active"]="1";
               $this->fields["show_label"]="hover";
               $this->fields["spline"]="0";
               $this->fields["show_area"]="0";
               $this->fields["show_graph"]="1";
               $this->fields["default_delay"]="30";
               $this->fields["graphtype"]="SVG";
               break;

         }
      }
      return $this->fields;
   }

   /**
    * show not used Graphs dropdown
    * @name name of dropdown
    * @options array example $value
    *@return nothing
    **/
   static function dropdownGraph($name, $options = []) {
      $self = new self();
      $common = new PluginMreportingCommon();
      $rand = mt_rand();

      $select  = "<select name='$name' id='dropdown_".$name.$rand."'>";
      $select .= "<option value='-1' selected>".Dropdown::EMPTY_VALUE."</option>";

      $i = 0;
      $reports = $common->getAllReports();
      foreach ($reports as $classname => $report) {

         foreach ($report['functions'] as $function) {
            if (!$self->getFromDBByFunctionAndClassname($function["function"], $classname)) {
               $graphs[$classname][$function['category_func']][] = $function;
            }
         }

         if (isset($graphs[$classname])) {
            $count = count($graphs[$classname]);
            if ($count > 0) {

               $select.= "<optgroup label=\"". $report['title'] ."\">";

               $count = count($graphs[$classname]);
               if ($count > 0) {
                  foreach ($graphs[$classname] as $cat => $graph) {

                     $select.= "<optgroup label=\"". $cat ."\">";

                     foreach ($graph as $k => $v) {

                        $comment = "";
                        if (isset($v["desc"])) {
                           $comment = $v["desc"];
                           $desc = " (".$comment.")";
                        }

                        $select.= "<option  title=\"".
                                 Html::cleanInputText($comment)."\"
                                 value='".$classname.";".$v["function"].
                                 "'".($options['value']==$classname.";".
                                 $v["function"]?" selected ":"").">";
                        $select.= $v["title"].$desc;
                        $select.= "</option>";

                        $i++;
                     }
                     $select.= "</optgroup>";

                  }
               }
               $select.= "</optgroup>";
            }
         }
      }

      $select.= "</select>";

      echo $select;
      return $rand;
   }

   /**
    * show Label dropdown
    * @name name of dropdown
    * @options array example $value
    *@return nothing
    **/
   static function dropdownLabel($name, $options = [], $notall = false) {
      $params['value']       = 0;
      $params['toadd']       = [];
      $params['on_change']   = '';

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $items = $params['toadd'];
      $items += self::getLabelTypes($notall);

      return Dropdown::showFromArray($name, $items, $params);
   }

   /**
    * Get label types
    *
    * @return array of types
   **/
   static function getLabelTypes($notall = false) {
      $options['never']     = __("Never");
      $options['hover']     = __("On mouse over", 'mreporting');
      if (!$notall) {
         $options['always'] = __("Always");
      }
      return $options;
   }

   /**
    * Get label Name
    *
    * @param $value type ID
   **/
   static function getLabelTypeName($value) {
      switch ($value) {
         case 'hover' :
            return __("On mouse over", 'mreporting');
         case 'never' :
            return __("Never");
         case 'always' :
            return __("Always");
      }
   }

   /**
    * checkVisibility
    *
    * @param $show_label show_label value (hover - always - never)
    * @param $always
    * @param $hover
   **/
   static function checkVisibility($show_label, &$always, &$hover) {
      switch ($show_label) {

         case 'hover':
            $always = "false";
            $hover = "true";
            break;
         case 'always':
            $always = "true";
            $hover = "true";
            break;
         default :
            $always = "false";
            $hover = "false";
            break;
      }
   }

   static function getColors($index = 20) {
      if (isset($_SESSION['mreporting']['colors'])) {
         $colors = $_SESSION['mreporting']['colors'];
      } else {
         /* if ($index <= 10) {
            $colors = array(
               "#1f77b4", "#ff7f0e", "#2ca02c", "#d62728", "#9467bd",
               "#8c564b", "#e377c2", "#7f7f7f", "#bcbd22", "#17becf"
            );
         } else {*/
            $colors = [
               "#1f77b4", "#aec7e8", "#ff7f0e", "#ffbb78", "#2ca02c",
               "#98df8a", "#d62728", "#ff9896", "#9467bd", "#c5b0d5",
               "#8c564b", "#c49c94", "#e377c2", "#f7b6d2", "#7f7f7f",
               "#c7c7c7", "#bcbd22", "#dbdb8d", "#17becf", "#9edae5"
            ];
            // }
      }

      //fill colors on size index
      $nb = count($colors);
      $tmp = $colors;
      while (count($colors) < $index) {
         $colors = array_merge($tmp, $colors);
      }

      return $colors;
   }


   function prepareInputForAdd($input) {
      if (isset ($input["name"])) {

         if ($this->getFromDBByFunctionAndClassname($input["name"], $input["classname"])) {
            if (!isset ($input["firstconfig"])) {
               Session::addMessageAfterRedirect(__("Object already exists", 'mreporting'),
                  false, ERROR);
            }
            return [];
         }
      }

      return $input;
   }

   function prepareInputForUpdate($input) {

      if (isset($input["classname"]) && method_exists(new $input["classname"]([]), 'checkConfig')) {
         $object = new $input["classname"]([]);
         $checkConfig = $object->checkConfig($input);
         if (!$checkConfig['result']) {
            Session::addMessageAfterRedirect($checkConfig['message'], ERROR, true);

            return [];
         }
      }

      return $input;
   }

   function showForm($ID, $options = []) {
      global $LANG;

      $this->initForm($ID, $options);

      if ($ID <= 0) {
         if (isset($_GET['name']) && isset($_GET['classname'])) {
            $this->preconfig($_GET['name'], $_GET['classname']);
            $_GET['preconfig'] = 1;
         } else {
            $_GET['name'] = -1;
            $_GET['classname'] = -1;
            $_GET['preconfig'] = -1;
         }
      }

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<td class='tab_bg_2 center' colspan='2'>";
      echo __("Preconfiguration")."&nbsp;";
      $opt = ['value' => $_GET['preconfig']];
      $rand = self::dropdownGraph('graphname', $opt);
      $params = ['graphname' => '__VALUE__'];
      Ajax::updateItemOnSelectEvent("dropdown_graphname$rand", "show_preconfig",
                                          "../ajax/dropdownGraphs.php",
                                          $params);
      echo "<span id='show_preconfig'></span>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      $style = ($_GET['preconfig']==-1 && $ID <= 0) ? "display:none;" : "'display:block;'";
      echo "<div id='show_form' style='$style'>";

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Name")."</td>";
      echo "<td>";
      echo $this->fields["name"];
      echo "<input type='hidden' name='name' value=\"".$this->fields["name"]."\">";
      echo "</td>";

      echo "<td colspan='2'>";
      $gtype = '';

      $f_name = $this->fields["name"];

      $ex_func = preg_split('/(?<=\\w)(?=[A-Z])/', $f_name);
      if (isset($ex_func[1])) {
         $gtype = strtolower($ex_func[1]);
      }

      $short_classname = str_replace('PluginMreporting', '', $this->fields["classname"]);

      if (!empty($short_classname)
        && !empty($f_name)
        && isset($LANG['plugin_mreporting'][$short_classname][$f_name]['title'])) {
            echo "&nbsp;";
            echo "<a href='graph.php?short_classname=".$short_classname."&f_name=".$f_name."&gtype=".$gtype."'>";
            echo $LANG['plugin_mreporting'][$short_classname][$f_name]['title'];
            echo "</a>";
      } else {
         echo __("No report is available !", 'mreporting');
      }

      echo "<input type='hidden' name='classname' value=\"".$this->fields["classname"]."\">";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("See graphic", 'mreporting')."</td>";
      echo "<td>";
      Dropdown::showYesNo("show_graph", $this->fields["show_graph"]);
      echo "</td>";

      echo "<td>".__("Default chart format")."</td>";
      echo "<td>";
      Dropdown::showFromArray("graphtype",
         ['PNG'=>'PNG', 'SVG'=>'SVG'],
         ['value' => $this->fields["graphtype"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Active")."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo "</td>";

      echo "<td>";
      echo __("See area", 'mreporting');
      echo "</td>";
      echo "<td>";
      if ($gtype == 'area' || $gtype == 'garea') {
         Dropdown::showYesNo("show_area", $this->fields["show_area"]);
      } else {
         echo Dropdown::getYesNo($this->fields["show_area"]);
         echo "<input type='hidden' name='show_area' value='0'>";
      }

      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __("Curve lines (SVG)", 'mreporting');
      echo "</td>";
      echo "<td>";
      if ($gtype == 'area' || $gtype == 'garea' || $gtype == 'line' || $gtype == 'gline') {
         Dropdown::showYesNo("spline", $this->fields["spline"]);
      } else {
         echo Dropdown::getYesNo($this->fields["spline"]);
         echo "<input type='hidden' name='spline' value='0'>";
      }
      echo "</td>";

      echo "<td>";
      echo __("Curve lines (SVG)", 'mreporting');
      echo "</td>";

      echo "<td>";
      $opt = ['value' => $this->fields["show_label"]];
      if ($gtype != 'area' && $gtype != 'garea' && $gtype != 'line' && $gtype != 'gline') {
         self::dropdownLabel('show_label', $opt);
      } else {
         self::dropdownLabel('show_label', $opt, true);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __("Reverse data array", 'mreporting');
      echo "</td>";
      echo "<td>";
      if ($gtype != 'hbar' && $gtype != 'pie' && $gtype != 'area' && $gtype != 'line') {
         Dropdown::showYesNo("flip_data", $this->fields["flip_data"]);
      } else {
         echo Dropdown::getYesNo($this->fields["flip_data"]);
         echo "<input type='hidden' name='flip_data' value='0'>";
      }
      echo "</td>";

      echo "<td>";
      echo __("Unit", 'mreporting');
      echo "</td>";
      echo "<td>";
      $opt = ['size' => 10];
      Html::autocompletionTextField($this, 'unit', $opt);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __("Default delay", 'mreporting');
      echo "</td>";
      echo "<td>";
      $opt = ['size' => 10];
      Html::autocompletionTextField($this, 'default_delay', $opt);
      echo "</td>";

      echo "<td>";
      echo __("Additional condition for MySQL", 'mreporting');
      echo "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, 'condition');
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __("Send this report with the notification", 'mreporting');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("is_notified", $this->fields["is_notified"]);
      echo "</td>";
      echo "<td>&nbsp;</td>";
      echo "<td>&nbsp;</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      echo "</div>";

      return true;
   }

   /**
    * initialize config for graph display options
    *
    * @param $name of graph
    * @param $classname of graph
   **/
   static function initConfigParams($name, $classname) {

      $crit = ['area'          => false,
               'spline'       => false,
               'flip_data'    => false,
               'unit'         => '',
               'show_label'   => 'never',
               'delay'        => '30',
               'condition'    => '',
               'show_graph'   => false,
               'randname'     => mt_rand(),
               'graphtype'    => 'SVG'];

      $self = new self();
      if ($self->getFromDBByFunctionAndClassname($name, $classname)) {
         $crit['area']        = $self->fields['show_area'];
         $crit['spline']      = $self->fields['spline'];
         $crit['show_label']  = $self->fields['show_label'];
         $crit['flip_data']   = $self->fields['flip_data'];
         $crit['unit']        = $self->fields['unit'];
         $crit['delay']       = $self->fields['default_delay'];
         $crit['condition']   = $self->fields['condition'];
         $crit['show_graph']  = $self->fields['show_graph'];
         $crit['graphtype']   = $self->fields['graphtype'];
         $crit['randname']    = $classname.$name;
      }

      if (DEBUG_MREPORTING) {
         $crit['show_graph']  = 1;
      }

      return $crit;
   }

   /**
    * test for value of show_graph field
    *
    * @param $name of graph
    * @param $classname of graph
   **/

   static function showGraphConfigValue($name, $classname) {
      if (DEBUG_MREPORTING) {
         return true;
      }

      $self = new self();
      if ($self->getFromDBByFunctionAndClassname($name, $classname)) {
         return $self->fields['show_graph'];
      }
      return false;
   }
}
