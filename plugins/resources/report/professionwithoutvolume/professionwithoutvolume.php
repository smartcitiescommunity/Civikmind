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

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 1;

include ("../../../../inc/includes.php");

// Instantiate Report with Name
$titre = $LANG['plugin_resources']['professionwithoutvolume'];
$report = new PluginReportsAutoReport($titre);

//Report's search criterias
$professioncategory = New PluginReportsDropdownCriteria($report, 'plugin_resources_professioncategories_id',
   'glpi_plugin_resources_professioncategories', PluginResourcesProfessionCategory::getTypeName(1));
$professionline = New PluginReportsDropdownCriteria($report, 'plugin_resources_professionlines_id',
   'glpi_plugin_resources_professionlines', PluginResourcesProfessionLine::getTypeName(1));

//Display criterias form is needed
$report->displayCriteriasForm();

//colname with sort allowed
$columns = ['professioncategory' => ['sorton' => 'professioncategory'],
                 'professionline' => ['sorton' => 'professionline'],
                 'profession' => ['sorton' => 'profession'],
                 'profession_code' => ['sorton' => 'profession_code'],
                 'rank_name' => ['sorton' => 'rank'],
                 'rank_code' => ['sorton' => 'rank_code'],
                 'begin_date' => ['sorton' => 'begin_date'],
                 'end_date' => ['sorton' => 'end_date'],];


$output_type = Search::HTML_OUTPUT;
$dbu = new DbUtils();

// Form validate
if ($report->criteriasValidated()) {

   if (isset ($_POST['list_limit'])) {
      $_SESSION['glpilist_limit'] = $_POST['list_limit'];
      unset ($_POST['list_limit']);
   }
   if (!isset ($_REQUEST['sort'])) {
      $_REQUEST['sort'] = "profession";
      $_REQUEST['order'] = "ASC";
   }

   $limit = $_SESSION['glpilist_limit'];

   if (isset ($_POST["display_type"])) {
      $output_type = $_POST["display_type"];
      if ($output_type < 0) {
         $output_type = - $output_type;
         $limit = 0;
      }
   } else {
      $output_type = Search::HTML_OUTPUT;
   }

   $title = $report->getFullTitle();

   //to verify if professions exist
   // SQL statement
   $condition = $dbu->getEntitiesRestrictRequest('AND', 'glpi_plugin_resources_resources');
   $date=date("Y-m-d");
   $sqlprofessioncategory = $professioncategory->getSqlCriteriasRestriction('AND');
   $sqlprofessionline = $professionline->getSqlCriteriasRestriction('AND');
   $dataAll=[];

   //recover all professions_id present in resources
   $requestResource = "SELECT DISTINCT(`glpi_plugin_resources_ranks`.`plugin_resources_professions_id`)
               FROM `glpi_plugin_resources_resources`
               LEFT JOIN `glpi_plugin_resources_ranks`
                     ON (`glpi_plugin_resources_ranks`.`id` = `glpi_plugin_resources_resources`.`plugin_resources_ranks_id`)
               WHERE `glpi_plugin_resources_resources`.`date_begin` <= '".$date."'
                  AND (`glpi_plugin_resources_resources`.`date_end` IS NULL
                        OR `glpi_plugin_resources_resources`.`date_end` >= '".$date."')".
                  $condition.
               "ORDER BY `plugin_resources_professions_id`";

   $professionsResourceList = '0';
   foreach ($DB->request($requestResource) as $data) {
      if ($data['plugin_resources_professions_id'] != null) {
         $professionsResourceList.=',';
         $professionsResourceList.=$data['plugin_resources_professions_id'];
      }
   }

   //case global management
   $query = "SELECT `glpi_plugin_resources_professions`.`plugin_resources_professionlines_id` AS professionline,
                    `glpi_plugin_resources_professions`.`plugin_resources_professioncategories_id` AS professioncategory,
                    `glpi_plugin_resources_professions`.`name` AS profession,
                    `glpi_plugin_resources_professions`.`id` AS profession_id,
                    `glpi_plugin_resources_professions`.`code` AS profession_code,
                    0 AS rank_name, 0 AS rank_code,
                    `glpi_plugin_resources_professions`.`begin_date`,
                    `glpi_plugin_resources_professions`.`end_date`
             FROM `glpi_plugin_resources_professions`
             LEFT JOIN `glpi_plugin_resources_budgets`
                  ON (`glpi_plugin_resources_budgets`.`plugin_resources_professions_id` = `glpi_plugin_resources_professions`.`id`
                  AND ((`glpi_plugin_resources_budgets`.`begin_date` IS NULL)
                        OR (`glpi_plugin_resources_budgets`.`begin_date` < '".$date."')
                       AND (`glpi_plugin_resources_budgets`.`end_date` IS NULL)
                        OR (`glpi_plugin_resources_budgets`.`end_date` > '".$date."')))
             WHERE (`glpi_plugin_resources_professions`.`id` IN (".$professionsResourceList.")
                  AND `glpi_plugin_resources_professions`.`is_active` = 1
                  AND ((`glpi_plugin_resources_professions`.`end_date` IS NULL )
                        OR (`glpi_plugin_resources_professions`.`end_date` > '".$date."' ))
                     AND ((`glpi_plugin_resources_professions`.`begin_date` IS NULL)
                        OR ( `glpi_plugin_resources_professions`.`begin_date` < '".$date."')))
                  AND ((`glpi_plugin_resources_budgets`.`id` IS NULL)
                  OR (`glpi_plugin_resources_budgets`.`begin_date` IS NOT NULL
                     AND `glpi_plugin_resources_budgets`.`begin_date` > '".$date."')
                  OR (`glpi_plugin_resources_budgets`.`end_date` IS NOT NULL
                        AND `glpi_plugin_resources_budgets`.`end_date` < '".$date."')) ".
      $sqlprofessioncategory.$sqlprofessionline;

   $conditionAll = $dbu->getEntitiesRestrictRequest('AND', 'glpi_plugin_resources_professions', '', '', true);

   $query.=$conditionAll." ".getOrderBy('profession', $columns);

   $result = $DB->query($query);
   for ($row_num = 0; $data=$DB->fetchAssoc($result); $row_num++) {
      if ($row_num == 0) {
         $dataAll[$row_num]['professionline']= PluginResourcesProfession::getTypeName(1);
         $row_num++;
      }
      $dataAll[$row_num]=$data;
   }

   //Case of specific management for each grade of a profession
   $rankList = "SELECT DISTINCT(`glpi_plugin_resources_budgets`.`plugin_resources_ranks_id`)
               FROM `glpi_plugin_resources_budgets`
               WHERE ((`glpi_plugin_resources_budgets`.`begin_date` < '".$date."')
                  AND (`glpi_plugin_resources_budgets`.`end_date` IS NOT NULL
                  OR `glpi_plugin_resources_budgets`.`end_date` > '".$date."'))
               ORDER BY `glpi_plugin_resources_budgets`.`plugin_resources_ranks_id`";

   foreach ($DB->request($rankList) as $d) {
      if ($d['plugin_resources_ranks_id']!=0) {
         $rank=new PluginResourcesRank();
         $rank->getFromDB($d['plugin_resources_ranks_id']);

         $qRank = "SELECT  `glpi_plugin_resources_ranks`.`name` AS rank_name,
                              `glpi_plugin_resources_ranks`.`code` AS rank_code,
                              `glpi_plugin_resources_professions`.`plugin_resources_professionlines_id` AS professionline,
                              `glpi_plugin_resources_professions`.`plugin_resources_professioncategories_id` AS professioncategory,
                              `glpi_plugin_resources_professions`.`name` AS profession,
                              `glpi_plugin_resources_professions`.`id` AS profession_id,
                              `glpi_plugin_resources_professions`.`code` AS profession_code,
                              `glpi_plugin_resources_professions`.`begin_date`,
                              `glpi_plugin_resources_professions`.`end_date`
                      FROM `glpi_plugin_resources_ranks`
                      LEFT JOIN `glpi_plugin_resources_professions`
                           ON (`glpi_plugin_resources_ranks`.`plugin_resources_professions_id` = `glpi_plugin_resources_professions`.`id`)
                      LEFT JOIN `glpi_plugin_resources_budgets`
                           ON (`glpi_plugin_resources_budgets`.`plugin_resources_ranks_id` = `glpi_plugin_resources_ranks`.`id`
                                 AND ((`glpi_plugin_resources_budgets`.`begin_date` IS NULL)
                                    OR (`glpi_plugin_resources_budgets`.`begin_date` < '".$date."')
                                 AND (`glpi_plugin_resources_budgets`.`end_date` IS NULL)
                                    OR (`glpi_plugin_resources_budgets`.`end_date` > '".$date."')))
                      WHERE `glpi_plugin_resources_ranks`.`plugin_resources_professions_id`='".$rank->getField('plugin_resources_professions_id')."'
                           AND `glpi_plugin_resources_ranks`.`is_active` = 1
                           AND ((`glpi_plugin_resources_ranks`.`end_date` IS NULL )
                                 OR (`glpi_plugin_resources_ranks`.`end_date` > '".$date."' ))
                           AND ((`glpi_plugin_resources_ranks`.`begin_date` IS NULL)
                                 OR ( `glpi_plugin_resources_ranks`.`begin_date` < '".$date."'))
                           AND ((`glpi_plugin_resources_budgets`.`id` IS NULL)
                              OR (`glpi_plugin_resources_budgets`.`begin_date` IS NOT NULL
                                 AND `glpi_plugin_resources_budgets`.`begin_date` > '".$date."')
                              OR (`glpi_plugin_resources_budgets`.`end_date` IS NOT NULL
                                 AND `glpi_plugin_resources_budgets`.`end_date` < '".$date."')) ".
            $sqlprofessioncategory.$sqlprofessionline;

         $qRank.=$conditionAll." ".getOrderBy('profession', $columns);

         $first=0;
         foreach ($DB->request($qRank) as $dataRank) {
            if ($first == 0) {
               $dataAll[$row_num]['professionline']= PluginResourcesRank::getTypeName(1);
               $first++;
               $row_num++;
            }
            $dataAll[$row_num]=$dataRank;
            $row_num++;
         }
      }
   }

   $nbtot = count($dataAll);
   if ($limit) {
      $start = (isset ($_GET["start"]) ? $_GET["start"] : 0);
      if ($start >= $nbtot) {
         $start = 0;
      }
   } else {
      $start = 0;
   }

   if ($nbtot == 0) {
      if (!$HEADER_LOADED) {
         Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
         Report::title();
      }
      echo "<div class='center'><span style='color : red;font-weight:bold;'>".__('No item found')."</span></div>";
      Html::footer();
   } else if ($output_type == Search::PDF_OUTPUT_PORTRAIT || $output_type == Search::PDF_OUTPUT_LANDSCAPE) {
      include (GLPI_ROOT . "/vendor/tecnickcom/tcpdf/examples/tcpdf_include.php");
   } else if ($output_type == Search::HTML_OUTPUT) {
      if (!$HEADER_LOADED) {
         Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
         Report::title();
      }
      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th>$title</th></tr>\n";
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

   if ($nbtot >0) {
      $nbcols = $DB->num_fields($result);
      $nbrows = $DB->numrows($result);
      $num = 1;
      $link  = $_SERVER['PHP_SELF'];
      $order = 'ASC';
      $issort = false;

      echo Search::showHeader($output_type, $nbrows, $nbcols, true);

      echo Search::showNewLine($output_type);

      showTitle($output_type, $num, PluginResourcesProfessionLine::getTypeName(1), 'professionline', true);
      showTitle($output_type, $num, PluginResourcesProfessionCategory::getTypeName(1), 'professioncategory', true);
      showTitle($output_type, $num, PluginResourcesProfession::getTypeName(1), 'profession', true);
      showTitle($output_type, $num, PluginResourcesProfession::getTypeName(1)." - ".__('Code', 'resources'), 'profession_code', true);
      showTitle($output_type, $num, PluginResourcesRank::getTypeName(1), 'rank_name', true);
      showTitle($output_type, $num, PluginResourcesRank::getTypeName(1)." - ".__('Code', 'resources'), 'rank_code', true);
      showTitle($output_type, $num, __('Begin date'), 'begin_date', true);
      showTitle($output_type, $num, __('End date'), 'end_date', true);

      echo Search::showEndLine($output_type);

      if ($limit) {
         $dataAll= array_slice($dataAll, $start, $limit);
      }

      foreach ($dataAll as $key=>$data) {
         $num = 1;
         if (!is_numeric($data['professionline'])) {
            echo Search::showNewLine($output_type);
            echo Search::showItem($output_type, $data['professionline'], $num, $key);
            echo Search::showItem($output_type, '', $num, $key);
            echo Search::showItem($output_type, '', $num, $key);
            echo Search::showItem($output_type, '', $num, $key);
            echo Search::showItem($output_type, '', $num, $key);
            echo Search::showItem($output_type, '', $num, $key);
            echo Search::showItem($output_type, '', $num, $key);
            echo Search::showItem($output_type, '', $num, $key);

            echo Search::showEndLine($output_type);
         } else {
            echo Search::showNewLine($output_type);
            echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_plugin_resources_professionlines', $data['professionline']), $num, $key);
            echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_plugin_resources_professioncategories', $data['professioncategory']), $num, $key);
            echo Search::showItem($output_type, $data['profession'], $num, $key);
            echo Search::showItem($output_type, $data['profession_code'], $num, $key);
            if ($data['rank_name'] == '0') {
               $data['rank_name']='';
            }
            echo Search::showItem($output_type, $data['rank_name'], $num, $key);
            if ($data['rank_code'] == '0') {
               $data['rank_code']='';
            }
            echo Search::showItem($output_type, $data['rank_code'], $num, $key);
            echo Search::showItem($output_type, Html::convDate($data['begin_date']), $num, $key);
            echo Search::showItem($output_type, Html::convDate($data['end_date']), $num, $key);
            echo Search::showEndLine($output_type);
         }
      }

      echo Search::showFooter($output_type, $title);
   }
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

   $tabs[] = getOrderByFields($default, $columns);
   if (count($tabs) > 0) {
      foreach ($tabs as $tab) {
         return " ORDER BY " . $tab . " " . $order;
      }
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


