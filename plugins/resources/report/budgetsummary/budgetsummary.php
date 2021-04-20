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
$titre = $LANG['plugin_resources']['budgetsummary'];
$report = new PluginReportsAutoReport($titre);

//Report's search criterias
$datecrit = new PluginResourcesDateCriteria($report, 'date_budget', '', PluginResourcesBudget::getTypeName(1)." - ".__('Date'));
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
                 'rank' => ['sorton' => 'rank'],
                 'begin_date' => ['sorton' => 'begin_date'],
                 'end_date' => ['sorton' => 'end_date'],
                 'budget_type' => ['sorton' => 'budget_type'],
                 'qt_vol_budg_vot' => ['sorton' => 'qt_vol_budg_vot'],];

$output_type = Search::HTML_OUTPUT;

//If criterias have been validated
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
   $dbu   = new DbUtils();

   //to verify if budget exist
   // SQL statement
   $condition = $dbu->getEntitiesRestrictRequest('', "glpi_plugin_resources_budgets", '', '', false);
   $date = $datecrit->getDate();
   $sqlprofessioncategory = $professioncategory->getSqlCriteriasRestriction('AND');
   $sqlprofessionline = $professionline->getSqlCriteriasRestriction('AND');

   //recover all budgets
   $query = "SELECT `glpi_plugin_resources_professions`.`plugin_resources_professioncategories_id` AS professioncategory,
                    `glpi_plugin_resources_professions`.`plugin_resources_professionlines_id` AS professionline,
                    `glpi_plugin_resources_budgets`.`plugin_resources_professions_id` AS profession,
                    `glpi_plugin_resources_budgets`.`plugin_resources_ranks_id` AS rank,
                    `glpi_plugin_resources_budgets`.`begin_date` AS begin_date,
                    `glpi_plugin_resources_budgets`.`end_date` AS end_date,
                    `glpi_plugin_resources_budgets`.`plugin_resources_budgettypes_id` AS budget_type,
                    `glpi_plugin_resources_budgets`.`volume` AS qt_vol_budg_vot
             FROM `glpi_plugin_resources_budgets`
                  LEFT JOIN `glpi_plugin_resources_professions`
                     ON (`glpi_plugin_resources_budgets`.`plugin_resources_professions_id`
                           = `glpi_plugin_resources_professions`.`id`)
             WHERE ".$condition."
                  AND (`glpi_plugin_resources_budgets`.`begin_date` <= '".$date."'
                     AND (`glpi_plugin_resources_budgets`.`end_date` IS NULL
                        OR `glpi_plugin_resources_budgets`.`end_date` >= '".$date."'))
                  AND `glpi_plugin_resources_professions`.`is_active` = 1
                  AND ((`glpi_plugin_resources_professions`.`begin_date` <= '".$date."')
                     AND (`glpi_plugin_resources_professions`.`end_date` IS NULL
                        OR `glpi_plugin_resources_professions`.`end_date` >= '".$date."')) ".
      $sqlprofessioncategory.$sqlprofessionline."
             GROUP BY profession,
                      rank,
                      budget_type ".getOrderBy('profession', $columns);

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

   if ($res && $nbtot >0) {
      $nbcols = $DB->num_fields($res);
      $nbrows = $DB->numrows($res);
      $num = 1;
      $link  = $_SERVER['PHP_SELF'];
      $order = 'ASC';
      $issort = false;

      echo Search::showHeader($output_type, $nbrows, $nbcols, true);

      echo Search::showNewLine($output_type);

      showTitle($output_type, $num, PluginResourcesProfessionCategory::getTypeName(1), 'professioncategory', true);
      showTitle($output_type, $num, PluginResourcesProfessionLine::getTypeName(1), 'professionline', true);
      showTitle($output_type, $num, PluginResourcesProfession::getTypeName(1), 'profession', true);
      showTitle($output_type, $num, PluginResourcesRank::getTypeName(1), 'rank', true);
      showTitle($output_type, $num, __('Begin date'), 'begin_date', true);
      showTitle($output_type, $num, __('End date'), 'end_date', true);
      showTitle($output_type, $num, PluginResourcesBudgetType::getTypeName(1), 'budget_type', true);
      showTitle($output_type, $num, __('Budget volume(qty)', 'resources'), 'qt_vol_budg_vot', true);
      showTitle($output_type, $num, __('Employment volume (qty)', 'resources'), 'qt_vol_budg_use');
      showTitle($output_type, $num, __('Resource volume (qty)', 'resources'), 'qt_vol_real');
      showTitle($output_type, $num, __('Remaining budget - employment (qty)', 'resources'), 'solde_qt');
      showTitle($output_type, $num, __('Budget volume (€)', 'resources'), 'vol_budg_vot');
      showTitle($output_type, $num, __('Employment volume (€)', 'resources'), 'vol_budg_use');
      showTitle($output_type, $num, __('Resource volume (€)', 'resources'), 'vol_real');
      showTitle($output_type, $num, __('Remaining budget - employment (€)', 'resources'), 'solde');

      echo Search::showEndLine($output_type);

      $totalvolbudget = 0;
      $totalvolemployment=0;
      $totalvolresource=0;
      $totalamountbudget=0;
      $totalamountemployment=0;
      $totalamountresource=0;
      $totalbudgetemployment = 0;

      //For each budget
      for ($row_num = 2; $data=$DB->fetchAssoc($res); $row_num++) {

         $num = 1;
         echo Search::showNewLine($output_type);
         echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_plugin_resources_professioncategories', $data['professioncategory']), $num, $row_num);
         echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_plugin_resources_professionlines', $data['professionline']), $num, $row_num);
         echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_plugin_resources_professions', $data['profession']), $num, $row_num);
         echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_plugin_resources_ranks', $data['rank']), $num, $row_num);
         echo Search::showItem($output_type, Html::convDate($data['begin_date']), $num, $row_num);
         echo Search::showItem($output_type, Html::convDate($data['end_date']), $num, $row_num);
         echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_plugin_resources_budgettypes', $data['budget_type']), $num, $row_num);
         echo Search::showItem($output_type, Html::formatNumber($data['qt_vol_budg_vot'], '', 0), $num, $row_num);

         $totalvolbudget = $totalvolbudget + $data['qt_vol_budg_vot'];

         //recover ratio employment sum for each budget depending on rank, profession and year
         $calqtvolbudguse = "SELECT SUM(`glpi_plugin_resources_employments`.`ratio_employment_budget`) AS sum
                          FROM `glpi_plugin_resources_employments`
                              LEFT JOIN `glpi_plugin_resources_employmentstates`
                                 ON (`glpi_plugin_resources_employments`.`plugin_resources_employmentstates_id`
                                       = `glpi_plugin_resources_employmentstates`.`id`
                                       AND `glpi_plugin_resources_employmentstates`.`is_leaving_state` = 0) ";
         if ($data['rank']!=0) {
            $calqtvolbudguse.=" LEFT JOIN `glpi_plugin_resources_ranks`
                                 ON (`glpi_plugin_resources_employments`.`plugin_resources_ranks_id`
                        = `glpi_plugin_resources_ranks`.`id`)";
         }
         $calqtvolbudguse.=" WHERE (`glpi_plugin_resources_employments`.`begin_date`
                                  >= '".$data['begin_date']."')
                            AND (`glpi_plugin_resources_employments`.`end_date`
                                  <= '".$data['end_date']."'
                                  OR `glpi_plugin_resources_employments`.`end_date` IS NULL)
                            AND `glpi_plugin_resources_employments`.`begin_date`
                                  <= '".$data['end_date']."'
                            AND `glpi_plugin_resources_employments`.`plugin_resources_professions_id` = '".$data['profession']."' ";
         if ($data['rank']!=0) {
            $calqtvolbudguse.= " AND `glpi_plugin_resources_employments`.`plugin_resources_ranks_id` = '".$data['rank']."'
                                 AND `glpi_plugin_resources_ranks`.`is_active` = 1
                                 AND ((`glpi_plugin_resources_ranks`.`begin_date` <= '".$date."')
                                    AND (`glpi_plugin_resources_ranks`.`end_date` IS NULL
                                       OR `glpi_plugin_resources_ranks`.`end_date` >= '".$date."'))";
         }

         $result1 = $DB->query($calqtvolbudguse);
         $data1=$DB->fetchArray($result1);

         //link to recap.php displaying only employments with same rank and profession
         $ratio ="";
         if (!empty($data1['sum'])) {
            $ratio = "<a href='".$CFG_GLPI['root_doc']."/plugins/resources/front/recap.php?employment_professions_id=".
               $data['profession'];
            $ratio.="&amp;date=".$date;
            if ($data['rank']!=0) {
               $ratio.="&amp;employment_ranks_id=".$data['rank'].
                  "&amp;glpisearchcount=5&amp;glpisearchcount5=0&amp;reset=reset";
            } else {
               $ratio.="&amp;glpisearchcount=4&amp;glpisearchcount4=0&amp;reset=reset";
            }
            $ratio.= "' target='_blank'>".
               Html::formatNumber($data1['sum'], '', 2)."</a>";
         }

         echo Search::showItem($output_type, $ratio, $num, $row_num);
         $totalvolemployment = $totalvolemployment + $data1['sum'];

         //recover quota sum of resource for each budget depending on rank, profession and year
         $calqtvolreal = "SELECT SUM(`glpi_plugin_resources_resources`.`quota`) AS sum
                           FROM `glpi_plugin_resources_resources`
                           LEFT JOIN `glpi_plugin_resources_ranks`
                              ON (`glpi_plugin_resources_resources`.`plugin_resources_ranks_id`
                                    = `glpi_plugin_resources_ranks`.`id`
                                    AND `glpi_plugin_resources_resources`.`is_leaving` = 0)
                           LEFT JOIN `glpi_plugin_resources_employments`
                              ON (`glpi_plugin_resources_employments`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id`)
                          LEFT JOIN `glpi_plugin_resources_employmentstates`
                              ON (`glpi_plugin_resources_employments`.`plugin_resources_employmentstates_id`
                                  = `glpi_plugin_resources_employmentstates`.`id` AND
                                  `glpi_plugin_resources_employmentstates`.`is_active` = 1)
                        WHERE (`glpi_plugin_resources_employments`.`begin_date`
                                  >= '".$data['begin_date']."')
                            AND (`glpi_plugin_resources_employments`.`end_date`
                                  <= '".$data['end_date']."'
                                  OR `glpi_plugin_resources_employments`.`end_date` IS NULL)
                            AND `glpi_plugin_resources_employments`.`begin_date`
                                  <= '".$data['end_date']."'
                       AND `glpi_plugin_resources_ranks`.`plugin_resources_professions_id` = '".$data['profession']."'";
         if ($data['rank']!=0) {
            $calqtvolreal.=" AND `glpi_plugin_resources_resources`.`plugin_resources_ranks_id` = '".$data['rank']."'
                             AND `glpi_plugin_resources_ranks`.`is_active` = 1
                             AND ((`glpi_plugin_resources_ranks`.`begin_date` <= '".$date."')
                                AND (`glpi_plugin_resources_ranks`.`end_date` IS NULL
                                    OR `glpi_plugin_resources_ranks`.`end_date` >= '".$date."'))";
         }

         $result2 = $DB->query($calqtvolreal);
         $data2=$DB->fetchArray($result2);

         //link to recap.php displaying only resource with same rank and profession
         $quota ="";
         if (!empty($data2['sum'])) {
            $quota = "<a href='".$CFG_GLPI['root_doc']."/plugins/resources/front/recap.php?resource_professions_id=".
               $data['profession'];
            $quota.="&amp;date=".$date;
            if ($data['rank']!=0) {
               $quota.="&amp;resource_ranks_id=".$data['rank'].
               "&amp;glpisearchcount=5&amp;glpisearchcount5=0&amp;reset=reset";
            } else {
               $quota.="&amp;glpisearchcount=4&amp;glpisearchcount4=0&amp;reset=reset";
            }
            $quota.="' target='_blank'>".
               Html::formatNumber($data2['sum'], '', 4)."</a>";
         }
         echo Search::showItem($output_type, $quota, $num, $row_num);
         $totalvolresource = $totalvolresource+$data2['sum'];

         //difference between quantity of budget voting and sum of resource quota using it
         $solde = $data['qt_vol_budg_vot'] - $data2['sum'];

         echo Search::showItem($output_type, $solde, $num, $row_num);

         //recover cost allocated for each couple rank/profession/year
         $query3 = "SELECT `glpi_plugin_resources_costs`.`cost` AS cost
                 FROM `glpi_plugin_resources_costs`
                 WHERE `glpi_plugin_resources_costs`.`plugin_resources_ranks_id` = '".$data['rank']."'
                 AND `glpi_plugin_resources_costs`.`plugin_resources_professions_id` = '".$data['profession']."'
                 AND (`begin_date` <= '".$date."'
                 AND (`end_date` IS NULL
                        OR `end_date` >= '".$date."'))";

         $result3 = $DB->query($query3);
         $data3=$DB->fetchArray($result3);

         //ammount of budget voting
         $calvolbudgvot = $data3['cost'] * $data['qt_vol_budg_vot'];
         $totalamountbudget = $totalamountbudget + $calvolbudgvot;

         echo Search::showItem($output_type, Html::formatNumber($calvolbudgvot, '', 2), $num, $row_num);

         //amount of budget used
         $calvolbudguse = $data3['cost'] * $data1['sum'];
         $totalamountemployment = $totalamountemployment + $calvolbudguse;

         echo Search::showItem($output_type, Html::formatNumber($calvolbudguse, '', 2), $num, $row_num);

         //amount of volume real
         $volreal = $data3['cost'] * $data2['sum'];
         $totalamountresource = $totalamountresource + $volreal;

         echo Search::showItem($output_type, Html::formatNumber($volreal, '', 2), $num, $row_num);

         //difference between amount of budget voting and and sum of resource quota using it
         $soldeamount = $calvolbudgvot - $volreal;

         $totalbudgetemployment = $totalbudgetemployment + $soldeamount;

         echo Search::showItem($output_type, Html::formatNumber($soldeamount, '', 2), $num, $row_num);

         echo Search::showEndLine($output_type);
      }

      $num = 1;
      $row_num++;
      echo Search::showNewLine($output_type);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, __('Total', 'resources')." - ".__('Budget volume(qty)', 'resources'), $num, $row_num);
      echo Search::showItem($output_type, __('Total', 'resources')." - ".__('Employment volume (qty)', 'resources'), $num, $row_num);
      echo Search::showItem($output_type, __('Total', 'resources')." - ".__('Resource volume (qty)', 'resources'), $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, __('Total', 'resources')." - ".__('Budget volume (€)', 'resources'), $num, $row_num);
      echo Search::showItem($output_type, __('Total', 'resources')." - ".__('Employment volume (€)', 'resources'), $num, $row_num);
      echo Search::showItem($output_type, __('Total', 'resources')." - ".__('Resource volume (€)', 'resources'), $num, $row_num);
      echo Search::showItem($output_type, __('Total', 'resources')." - ".__('Remaining budget - employment (€)', 'resources'), $num, $row_num);
      echo Search::showEndLine($output_type);

      $num = 1;
      $row_num++;
      echo Search::showNewLine($output_type);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, Html::formatNumber($totalvolbudget, '', 0), $num, $row_num);
      echo Search::showItem($output_type, Html::formatNumber($totalvolemployment, '', 2), $num, $row_num);
      echo Search::showItem($output_type, Html::formatNumber($totalvolresource, '', 4), $num, $row_num);
      echo Search::showItem($output_type, '', $num, $row_num);
      echo Search::showItem($output_type, Html::formatNumber($totalamountbudget, '', 2), $num, $row_num);
      echo Search::showItem($output_type, Html::formatNumber($totalamountemployment, '', 2), $num, $row_num);
      echo Search::showItem($output_type, Html::formatNumber($totalamountresource, '', 2), $num, $row_num);
      echo Search::showItem($output_type, Html::formatNumber($totalbudgetemployment, '', 2), $num, $row_num);
      echo Search::showEndLine($output_type);

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
 * @param $columns
 *
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

