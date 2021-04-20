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
$DBCONNECTION_REQUIRED = 0;

include("../../../../inc/includes.php");

//"Rapport listant les ressources sans utilisateurs";
//"Report listing resource without user";
// Instantiate Report with Name
$titre  = $LANG['plugin_resources']['checkhabilitation'];
$report = new PluginReportsAutoReport($titre);

//colname with sort allowed
$columns = ['entity'              => ['sorton' => 'entity'],
                 'name'                => ['sorton' => 'name'],
                 'firstname'           => ['sorton' => 'firstname'],
                 'registration_number' => ['sorton' => 'registration_number'],
                 'rank'                => ['sorton' => 'rank'],
                 'date_begin'          => ['sorton' => 'date_begin'],
                 'date_end'            => ['sorton' => 'date_end'],
                 'begin_date'          => ['sorton' => 'begin_date'],
                 'end_date'            => ['sorton' => 'end_date'],];

$output_type = Search::HTML_OUTPUT;

if (isset ($_POST['list_limit'])) {
   $_SESSION['glpilist_limit'] = $_POST['list_limit'];
   unset ($_POST['list_limit']);
}
if (!isset ($_REQUEST['sort'])) {
   $_REQUEST['sort']  = "entity";
   $_REQUEST['order'] = "ASC";
}

$limit = $_SESSION['glpilist_limit'];

if (isset ($_POST["display_type"])) {
   $output_type = $_POST["display_type"];
   if ($output_type < 0) {
      $output_type = -$output_type;
      $limit       = 0;
   }
} else {
   $output_type = Search::HTML_OUTPUT;
}

$title = $report->getFullTitle();
$dbu   = new DbUtils();

$query_resource_user = "SELECT glpi_plugin_resources_resources.*, glpi_users.id as glpi_users_id
                        FROM `glpi_plugin_resources_resources` 
                        LEFT JOIN glpi_plugin_resources_resources_items ON glpi_plugin_resources_resources_items.plugin_resources_resources_id = glpi_plugin_resources_resources.id
                        AND glpi_plugin_resources_resources_items.itemtype = 'User'
                        LEFT JOIN glpi_users ON glpi_plugin_resources_resources_items.items_id = glpi_users.id
                        AND glpi_plugin_resources_resources_items.itemtype = 'User'
                        WHERE `glpi_plugin_resources_resources`.`is_deleted` = 0
                        AND `glpi_plugin_resources_resources`.`is_template` = 0 
                        AND `glpi_plugin_resources_resources`.`is_leaving` = 0 ";

$query_resource_user .= $dbu->getEntitiesRestrictRequest('AND', 'glpi_plugin_resources_resources', '', '', true);
$query_resource_user .= " ORDER BY glpi_plugin_resources_resources.id ASC";



$result_resource_user = $DB->query($query_resource_user);

$dataAll = [];
while ($data = $DB->fetchAssoc($result_resource_user)) {
   $habilitations = [];
   $groups        = [];
   if (!empty($data['glpi_users_id'])) {
      $users_id     = $data['glpi_users_id'];
      $resources_id = $data['id'];

      $query_resources  = "SELECT `glpi_plugin_resources_resources`.`date_end`
                              FROM `glpi_plugin_resources_resources`
                              WHERE `id` = $resources_id";
      $result_resources = $DB->query($query_resources);
      $date_end = $DB->result($result_resources, 0, 'date_end');

      $query_habilitations  = "SELECT `glpi_plugin_resources_habilitations` .*
                              FROM `glpi_plugin_resources_resourcehabilitations`
                              LEFT JOIN `glpi_plugin_resources_habilitations` 
                              ON `glpi_plugin_resources_habilitations`.id = `glpi_plugin_resources_resourcehabilitations`.`plugin_resources_habilitations_id`
                              WHERE `plugin_resources_resources_id` = $resources_id";
      $result_habilitations = $DB->query($query_habilitations);

      while ($data_habilitation = $DB->fetchAssoc($result_habilitations)) {
         $habilitations[$data_habilitation['id']] = $data_habilitation['name'];
      }

      $query_groups  = "SELECT `glpi_groups`.* 
                        FROM `glpi_groups_users` 
                        LEFT JOIN `glpi_groups` ON `glpi_groups`.`id` = `glpi_groups_users`.`groups_id`
                        WHERE `glpi_groups_users`.`users_id` = $users_id";
      $result_groups = $DB->query($query_groups);
      while ($data_group = $DB->fetchAssoc($result_groups)) {
         $groups[$data_group['id']] = $data_group['name'];
      }

      $array_diff = array_diff($habilitations, $groups);

      if (count($array_diff) > 0) {
         $dataAll[] = [
            'resources_id'       => $resources_id,
            'resources_date_end' => $date_end,
            'users_id'           => $users_id,
            'habilitations'      => $habilitations,
            'diff'               => $array_diff
         ];
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
   echo "<div class='center'><span style='color : red;font-weight:bold;'>" . __('No item found') . "</span></div>";
   Html::footer();
} else if ($output_type == Search::PDF_OUTPUT_PORTRAIT || $output_type == Search::PDF_OUTPUT_LANDSCAPE) {
   include(GLPI_ROOT . "/vendor/tecnickcom/tcpdf/examples/tcpdf_include.php");
} else if ($output_type == Search::HTML_OUTPUT) {
   if (!$HEADER_LOADED) {
      Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
      Report::title();
   }
   echo "<div class='center'><table class='tab_cadre_fixe'>";
   echo "<tr><th>$title</th></tr>\n";
   echo "<tr class='tab_bg_2 center'><td class='center'>";
   echo "<form method='POST' action='" . $_SERVER["PHP_SELF"] . "?start=$start'>\n";

   $param = "";
   foreach ($_POST as $key => $val) {
      if (is_array($val)) {
         foreach ($val as $k => $v) {
            echo "<input type='hidden' name='" . $key . "[$k]' value='$v' >";
            if (!empty ($param)) {
               $param .= "&";
            }
            $param .= $key . "[" . $k . "]=" . urlencode($v);
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

if ($nbtot > 0) {
   $nbcols = 4;
   $nbrows = count($dataAll);
   $num    = 1;
   $link   = $_SERVER['PHP_SELF'];
   $order  = 'ASC';
   $issort = false;

   echo Search::showHeader($output_type, $nbrows, $nbcols, true);

   echo Search::showNewLine($output_type);

   echo Search::showHeaderItem($output_type, PluginResourcesResource::getTypeName(1), $num);
   echo Search::showHeaderItem($output_type, Location::getTypeName(1), $num);
   echo Search::showHeaderItem($output_type, __('Departure date', 'resources'), $num);
   echo Search::showHeaderItem($output_type, PluginResourcesHabilitation::getTypeName(2), $num);
   echo Search::showHeaderItem($output_type, User::getTypeName(1), $num);
   echo Search::showHeaderItem($output_type, __('Login'), $num);
   echo Search::showHeaderItem($output_type, $LANG['plugin_resources']['missinggroup'], $num);

   echo Search::showEndLine($output_type);

   if ($limit) {
      $dataAll = array_slice($dataAll, $start, $limit);
   }

   foreach ($dataAll as $key => $data) {
      echo Search::showNewLine($output_type);
      $resource = new PluginResourcesResource();
      $resource->getFromDB($data['resources_id']);

      echo Search::showItem($output_type, $resource->getLink(), $num, $key);
      echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_locations',
                                                                    $resource->getField('locations_id')), $num, $key);
      echo Search::showItem($output_type, Html::convDate($data["resources_date_end"]), $num, $key);
      echo Search::showItem($output_type, implode('<br>', $data['habilitations']), $num, $key);
      $user = new User();
      $user->getFromDB($data['users_id']);
      echo Search::showItem($output_type, $user->getLink(), $num, $key);
      echo Search::showItem($output_type, $user->getField('name'), $num, $key);
      echo Search::showItem($output_type, implode('<br>', $data['diff']), $num, $key);

      echo Search::showEndLine($output_type);
   }

   echo Search::showFooter($output_type, $title);
}

if ($output_type == Search::HTML_OUTPUT) {
   Html::footer();
}
