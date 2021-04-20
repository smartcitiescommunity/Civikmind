<?php
/*
 -------------------------------------------------------------------------
 Typology plugin for GLPI
 Copyright (C) 2006-2012 by the Typology Development Team.

 https://forge.indepnet.net/projects/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Typology.

 Typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 1;

include ("../../../../inc/includes.php");

$titre = __('Typologies list by service with materials list', 'typology');

// Instantiate Report with Name
$report = new PluginReportsAutoReport($titre);

//Report's search criterias
$typocrit = New PluginReportsDropdownCriteria($report, '`glpi_plugin_typology_typologies`.`id`',
   'glpi_plugin_typology_typologies', PluginTypologyTypology::getTypeName(1));

//Display criterias form is needed
$report->displayCriteriasForm();

//colname with sort allowed
$columns = ['entity' => ['sorton' => 'entity'],
   'groups_id' => ['sorton' => 'groups_id'],
   'typoID'=>['sorton'=>'typoID'],
   'COUNT'=> ['sorton'=>'COUNT']];

$output_type = Search::HTML_OUTPUT;

if (isset ($_POST['list_limit'])) {
   $_SESSION['glpilist_limit'] = $_POST['list_limit'];
   unset ($_POST['list_limit']);
}
if (!isset ($_REQUEST['sort'])) {
   $_REQUEST['sort'] = "entity";
   $_REQUEST['order'] = "ASC";
}

$limit = $_SESSION['glpilist_limit'];

if (isset ($_POST["display_type"])) {
   $output_type = $_POST["display_type"];
   if ($output_type < 0) {
      $output_type = - $output_type;
      $limit = 0;
   }
} //else {
//   $output_type = Search::HTML_OUTPUT;
//}

//Report title
$title = $LANG['plugin_typology']['typologyreport'];

$styleItemTitle = 'font-size: 12px;
   font-weight: bold;
   background-color: #e1cc7b;
   text-align: center;
   -moz-border-radius: 4px;
   -webkit-border-radius: 4px;
   -o-border-radius: 4px;
   padding: 2px;';

//to verify if typology exist in this entity
// SQL statement
$dbu = new DbUtils();
$condition = $dbu->getEntitiesRestrictRequest('', "glpi_plugin_typology_typologies", '', '', true);
$sqltypo = $typocrit->getSqlCriteriasRestriction('AND');

$query = "SELECT *
          FROM `glpi_plugin_typology_typologies`
          WHERE $condition $sqltypo ";

$res = $DB->query($query);
$nbtot = ($res ? $DB->numrows($res) : 0);
if ($limit) {
   $start = (isset ($_GET["start"]) ? $_GET["start"] : 0);
   if ($start >= $nbtot) {
      $start = 0;
   }
   if ($start > 0 || $start + $limit < $nbtot) {
      $res = $DB->query($query . " LIMIT $start,$limit");
   }
} else {
   $start = 0;
}

if ($nbtot == 0) {
   if (!$HEADER_LOADED) {
      Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
      Report::title();
   }
   echo "<div class='center'><span class='typology_font_red_bold'>".__('No item found')."</span></div>";
   Html::footer();
} else if ($output_type == Search::PDF_OUTPUT_PORTRAIT || $output_type == Search::PDF_OUTPUT_LANDSCAPE) {
   include (GLPI_ROOT . "/lib/ezpdf/class.ezpdf.php");
} else if ($output_type == Search::HTML_OUTPUT) {
   if (!$HEADER_LOADED) {
      Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
      Report::title();
   }
   echo "<div class='center'><table class='tab_cadre_fixe'>";
   echo "<tr><th>".$title."</th></tr>\n";

   // sub-Title
   echo "<tr><th>$titre</th></tr>\n";

   echo "<tr class='tab_bg_2 center'><td class='center'>";
   echo "<form method='POST' action='" .$_SERVER["PHP_SELF"] . "?start=$start'>\n";

   $param = "";
   foreach ($_POST as $key => $val) {
      if (is_array($val)) {
         foreach ($val as $k => $v) {
            echo "<input type='hidden' name='".$key."[$k]' value='$v' >";
            if (!empty ($param)) {
               $param .= "&";
            }
            $param .= $key."[".$k."]=".urlencode($v);
         }
      } else {
         echo "<input type='hidden' name='$key' value='$val' >";
         if (!empty ($param)) {
            $param .= "&";
         }
         $param .= "$key=" . urlencode($val);
      }
   }
   Dropdown::showOutputFormat();
   Html::closeForm();
   echo "</td></tr>";
   echo "</table></div>";

   Html::printPager($start, $nbtot, $_SERVER['PHP_SELF'], $param);
}

if ($res && $nbtot >0) {

   $nbCols=$DB->num_fields($res);
   $nbrows = $DB->numrows($res);
   $num = 1;
   $link  = $_SERVER['PHP_SELF'];
   $order = 'ASC';
   $issort = false;

   echo Search::showHeader($output_type, $nbrows, $nbCols, true);

   echo Search::showNewLine($output_type);
   showTitle($output_type, $num, __('Entity'), 'entity', true);
   showTitle($output_type, $num, __('Service'), 'groups_id', true);
   showTitle($output_type, $num, PluginTypologyTypology::getTypeName(1), 'typoID', true);
   showTitle($output_type, $num, __('Number', 'typology'), 'COUNT', true);
   echo Search::showEndLine($output_type);

   //By service and typology
   $queryService = "SELECT count(*) AS COUNT,
                           `glpi_plugin_typology_typologies`.`entities_id` AS entity,
                           `glpi_computers`.`groups_id`,
                           `glpi_plugin_typology_typologies`.`id` AS typoID
                     FROM `glpi_plugin_typology_typologies_items`
                     LEFT JOIN `glpi_plugin_typology_typologies`
                        ON(`glpi_plugin_typology_typologies_items`.`plugin_typology_typologies_id`
                            = `glpi_plugin_typology_typologies`.`id`)
                     LEFT JOIN `glpi_computers`
                        ON (`glpi_plugin_typology_typologies_items`.`items_id` = `glpi_computers`.`id`
                            AND `glpi_plugin_typology_typologies_items`.`itemtype` = 'Computer')
                     LEFT JOIN `glpi_entities`
                        ON (`glpi_plugin_typology_typologies`.`entities_id` = `glpi_entities`.`id`)
                     WHERE $condition $sqltypo
                     GROUP BY `glpi_entities`.`entities_id`,
                              `glpi_computers`.`groups_id`,
                              `glpi_plugin_typology_typologies`.`id`".
                      getOrderBy('entity', $columns);

   $resultService = $DB->query($queryService);
   $row_num = 1;
   while ($dataService=$DB->fetchAssoc($resultService)) {

      if ($dataService['groups_id'] == 0) {
         $serviceName = __('None');
      } else {
         $serviceName = Dropdown::getDropdownName("glpi_groups", $dataService['groups_id']);
      }

      if ($dataService['typoID'] == '0') {
         $typoName = __('None');
      } else {
         $typoName = Dropdown::getDropdownName("glpi_plugin_typology_typologies", $dataService["typoID"]);
      }

      $entityName = Dropdown::getDropdownName('glpi_entities', $dataService['entity']);

      $row_num++;
      $num = 1;
      echo Search::showNewLine($output_type);
      echo Search::showItem($output_type, $entityName, $num, $row_num);
      echo Search::showItem($output_type, $serviceName, $num, $row_num);
      echo Search::showItem($output_type, $typoName, $num, $row_num);
      echo Search::showItem($output_type, $dataService["COUNT"], $num, $row_num);
      echo Search::showEndLine($output_type);

      $row_num++;
      $num =1;
      echo Search::showNewLine($output_type);
      echo Search::showItem($output_type, '', $num, $row_num);
      if ($output_type == Search::HTML_OUTPUT) {
         echo Search::showItem($output_type, __('Detail (workstation concerned)', 'typology'), $num, $row_num, "colspan = '3' style='$styleItemTitle'");
      } else {
         echo Search::showItem($output_type, __('Detail (workstation concerned)', 'typology'), $num, $row_num);
         echo Search::showItem($output_type, '', $num, $row_num);
         echo Search::showItem($output_type, '', $num, $row_num);
      }
      echo Search::showEndLine($output_type);

      $row_num++;
      $num=1;
      echo Search::showNewLine($output_type);
      echo Search::showItem($output_type, '', $num, $row_num);
      if ($output_type == Search::HTML_OUTPUT) {
         echo Search::showItem($output_type, __('Name'), $num, $row_num, "style='$styleItemTitle'");
         echo Search::showItem($output_type, __('User'), $num, $row_num, "style='$styleItemTitle'");
         echo Search::showItem($output_type, __('Responding to typology\'s criteria', 'typology'), $num, $row_num, "style='$styleItemTitle'");
      } else {
         echo Search::showItem($output_type, __('Name'), $num, $row_num);
         echo Search::showItem($output_type, __('User'), $num, $row_num);
         echo Search::showItem($output_type, __('Responding to typology\'s criteria', 'typology'), $num, $row_num);
      }
      echo Search::showEndLine($output_type);

      //by computer
      $queryComputer = "SELECT `glpi_plugin_typology_typologies_items`.`itemtype` as itemtype,
                               `glpi_plugin_typology_typologies_items`.`items_id` as items_id,
                               `glpi_plugin_typology_typologies_items`.`is_validated`,
                               `glpi_plugin_typology_typologies_items`.`error`
                        FROM `glpi_plugin_typology_typologies_items`
                           LEFT JOIN `glpi_plugin_typology_typologies`
                              ON(`glpi_plugin_typology_typologies_items`.`plugin_typology_typologies_id`
                                 = `glpi_plugin_typology_typologies`.`id`)
                           LEFT JOIN `glpi_computers`
                              ON (`glpi_plugin_typology_typologies_items`.`items_id` = `glpi_computers`.`id`)
                        WHERE (`glpi_computers`.`groups_id` = '".$dataService['groups_id']."'
                               AND`plugin_typology_typologies_id` = '".$dataService['typoID']."'
                               AND `glpi_plugin_typology_typologies_items`.`itemtype`='Computer')
                               AND ".$condition."".$sqltypo."
                               ORDER BY `glpi_computers`.`name`";

      $resultComputer = $DB->query($queryComputer);
      $computeOK=0;
      $computeNOTOK=0;

      while ($dataComputer=$DB->fetchArray($resultComputer)) {

         $link=Toolbox::getItemTypeFormURL("Computer");

         $computerName = "<a href='".$link."?id=".$dataComputer["items_id"]."' target='_blank'>".
            Dropdown::getDropdownName("glpi_computers", $dataComputer["items_id"])."</a>";

         $computer = new Computer();
         $computer->getFromDB($dataComputer["items_id"]);

         $userName = $dbu->getUserName($computer->fields["users_id"]);

         if ($dataComputer["is_validated"] > 0) {
            $critTypOK = __('Yes');
            $computeOK++;
         } else {
            $critTypOK = "<span typology_font_red>".__('No')." ".
                           __('for the criteria', 'typology')." ";
            $i=0;

            $critTypOK.=PluginTypologyTypology_Item::displayErrors($dataComputer["error"]);

            $critTypOK.="</span>";
            $computeNOTOK++;
         }

         $row_num++;
         $num=1;
         echo Search::showNewLine($output_type);
         echo Search::showItem($output_type, '', $num, $row_num);
         echo Search::showItem($output_type, $computerName, $num, $row_num);
         echo Search::showItem($output_type, $userName, $num, $row_num);
         echo Search::showItem($output_type, $critTypOK, $num, $row_num);
         echo Search::showEndLine($output_type);
      }

      $row_num++;
      $num=1;

      $message = "<b><span typology_font_green>".__('Responding', 'typology')." ".
         $computeOK." / ".$dataService["COUNT"]."</span>".", "."<span class='typology_font_red'>".
         __('Not responding', 'typology')." ".$computeNOTOK." / ".$dataService["COUNT"]."</span></b>";
      echo Search::showNewLine($output_type);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, __('Total'), $num, $row_num);
      echo Search::showItem($output_type, $message, $num, $row_num);
      echo Search::showEndLine($output_type);


      $row_num++;
      $num=1;
      echo Search::showNewLine($output_type);
      if ($output_type == Search::HTML_OUTPUT) {
         echo Search::showItem($output_type, '', $num, $row_num, "colspan = '4' style='$styleItemTitle'");
      } else {
         echo Search::showItem($output_type, '', $num, $row_num);
         echo Search::showItem($output_type, '', $num, $row_num);
         echo Search::showItem($output_type, '', $num, $row_num);
         echo Search::showItem($output_type, '', $num, $row_num);
      }
      echo Search::showEndLine($output_type);

   }
   echo Search::showFooter($output_type, $title);
}

if ($output_type == Search::HTML_OUTPUT) {
   Html::footer();
}

/**
 * Display the column title and allow the sort
 *
 * @param $output_type
 * @param $num
 * @param $title
 * @param $columnname
 * @param bool $sort
 * @return mixed
 */
function showTitle($output_type, &$num, $title, $columnname, $sort = false) {

   if ($output_type != Search::HTML_OUTPUT ||$sort==false) {
      echo Search::showHeaderItem($output_type, $title, $num);
      return;
   }
   $order = 'ASC';
   $issort = false;
   if (isset($_REQUEST['sort']) && $_REQUEST['sort']==$columnname) {
      $issort = true;
      if (isset($_REQUEST['order']) && $_REQUEST['order']=='ASC') {
         $order = 'DESC';
      }
   }
   $link  = $_SERVER['PHP_SELF'];
   $first = true;
   foreach ($_REQUEST as $name => $value) {
      if (!in_array($name, ['sort','order','PHPSESSID'])) {
         $link .= ($first ? '?' : '&amp;');
         $link .= $name .'='.urlencode($value);
         $first = false;
      }
   }
   $link .= ($first ? '?' : '&amp;').'sort='.urlencode($columnname);
   $link .= '&amp;order='.$order;
   echo Search::showHeaderItem($output_type, $title, $num,
      $link, $issort, ($order=='ASC'?'DESC':'ASC'));
}

/**
 * Build the ORDER BY clause
 *
 * @param $default string, name of the column used by default
 * @return string
 */
function getOrderBy($default, $columns) {

   if (!isset($_REQUEST['order']) || $_REQUEST['order']!='DESC') {
      $_REQUEST['order'] = 'ASC';
   }
   $order   = $_REQUEST['order'];

   $tab = getOrderByFields($default, $columns);
   if (count($tab)>0) {
      return " ORDER BY ". $tab ." ". $order;
   }
   return '';
}

/**
 * Get the fields used for order
 *
 * @param $default string, name of the column used by default
 *
 * @return array of column names
 */
function getOrderByFields($default, $columns) {

   if (!isset($_REQUEST['sort'])) {
      $_REQUEST['sort'] = $default;
   }
   $colsort = $_REQUEST['sort'];

   foreach ($columns as $colname => $column) {
      if ($colname==$colsort) {
         return $column['sorton'];
      }
   }
   return [];
}

