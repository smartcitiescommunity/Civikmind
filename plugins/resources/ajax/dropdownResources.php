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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'], "dropdownResources.php")) {
   $AJAX_INCLUDE = 1;
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

if (empty($_GET)) {
   $_GET = $_POST;
}

Session::checkLoginUser();
// Default view : Nobody
if (!isset($_GET['all'])) {
   $_GET['all'] = 0;
}

$used  = [];

if (isset($_GET['used'])) {
   if (is_array($_GET['used'])) {
      $used = $_GET['used'];
   } else {
      $used = Toolbox::decodeArrayFromInput($_GET['used']);
   }
}

if (!isset($_GET['searchText'])) {
  $_GET['searchText'] = '';
}

$plugin_resources_contracttypes_id=0;
if (isset($_GET["plugin_resources_contracttypes_id"])&&
   $_GET["plugin_resources_contracttypes_id"]>0) {

   $plugin_resources_contracttypes_id = $_GET["plugin_resources_contracttypes_id"];
}

$result = PluginResourcesResource::getSqlSearchResult(false, $_GET["entity"],
                                   $_GET['value2'], $used, $_GET['searchText']);

$users       = [];
$logins      = [];
$linkedUsers = [];

$dbu = new DbUtils();

// Add linked resource users
if ($DB->numrows($result)) {
   while ($data = $DB->fetchArray($result)) {
      array_push($users, ['id'   => $data["id"],
                          'text' => $dbu->formatUserName($data["id"], $data["username"],
                                                   $data["name"], $data["firstname"], 0)]);
      //      $logins[$data["id"]] = $data["name"];
      $linkedUsers[] = $data["userid"];
   }
}

// Add unlinked users
if ($_GET['addUnlinkedUsers']) {
   //   ksort($logins);
   $query = "SELECT `glpi_users`.*
             FROM `glpi_users`
             WHERE `glpi_users`.`id` NOT IN ('".implode("','", $linkedUsers)."') 
             AND `glpi_users`.`entities_id` IN ('".implode("','", $_GET["entity"])."')
             AND `is_deleted` = 0
             AND (`glpi_users`.`name` ".Search::makeTextSearch($_GET['searchText'])."
                  OR `glpi_users`.`firstname` ".Search::makeTextSearch($_GET['searchText'])."
                  OR `glpi_users`.`realname` ".Search::makeTextSearch($_GET['searchText'])."
                  OR `glpi_users`.`registration_number` ".Search::makeTextSearch($_GET['searchText'])."
                  OR `glpi_users`.`name` ".Search::makeTextSearch($_GET['searchText'])."
                  OR CONCAT(`glpi_users`.`name`,' ',`glpi_users`.`firstname`,' ',`glpi_users`.`registration_number`,' ',`glpi_users`.`name`) ".
                  Search::makeTextSearch($_GET['searchText']).");";
   $result = $DB->query($query);
   while ($data = $DB->fetchArray($result)) {
      array_push($users, ['id'   => 'users-' . $data["id"],
                          'text' => $dbu->formatUserName($data["id"], $data["name"],
                                                         $data["realname"], $data["firstname"], 0)]);
      //      $logins['users-'.$data["id"]] = $data["name"];
   }
}

if (!function_exists('dpuser_cmp')) {
   /**
    * @param $a
    * @param $b
    *
    * @return int
    */
   function dpuser_cmp($a, $b) {
      return strcasecmp($a['text'], $b['text']);
   }
}

// Sort non case sensitive
usort($users, 'dpuser_cmp');
//
//echo "<select id='dropdown_".$_GET["name"].$_GET["rand"]."' name='".$_GET['name']."'";
//
//if (isset($_GET["on_change"]) && !empty($_GET["on_change"])) {
//   echo " onChange='".$_GET["on_change"]."'";
//}
//
//echo ">";
//
//if ($_GET['searchText']!=$CFG_GLPI["ajax_wildcard"]
//    && $DB->numrows($result)==$CFG_GLPI["dropdown_max"]) {
//
//   echo "<option value='0'>--".__('Limited view')."--</option>";
//}
//
//if ($_GET['all']==0) {
//   echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
//} else if ($_GET['all']==1) {
//   echo "<option value='0'>[".__('All')."]</option>";
//}
//
//if (isset($_GET['value2'])) {
//   $output = PluginResourcesResource::getResourceName($_GET['value2']);
//
//   if (!empty($output) && $output!="&nbsp;") {
//      echo "<option selected value='".$_GET['value2']."'>".$output."</option>";
//   }
//}
//
//if (count($users)) {
//   foreach ($users as $ID => $output) {
//      echo "<option value='$ID' title=\"".Html::cleanInputText($output." - ".$logins[$ID])."\">".
//             Toolbox::substr($output, 0, $_SESSION["glpidropdown_chars_limit"])."</option>";
//   }
//}
//echo "</select>";
//
//if (isset($_GET["comment"]) && $_GET["comment"]) {
//   $paramscomment = array('value' => '__VALUE__',
//                          'table' => "glpi_plugin_resources_resources");
//
//   if (isset($_GET['update_link'])) {
//      $paramscomment['withlink'] = "comment_link_".$_GET["name"].$_GET["rand"];
//   }
//   Ajax::updateItemOnSelectEvent("dropdown_".$_GET["name"].$_GET["rand"],
//                                 "comment_".$_GET["name"].$_GET["rand"],
//                                 $CFG_GLPI["root_doc"]."/plugins/resources/ajax/comments.php", $paramscomment);
//}
//
//Ajax::commonDropdownUpdateItem($_GET);

$ret['results'] = $users;
$ret['count']   = count($users);

echo json_encode($ret);

