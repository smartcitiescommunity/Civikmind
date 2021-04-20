<?php
/*
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
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (strpos($_SERVER['PHP_SELF'], "dropdownContact.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkCentralAccess();

// Make a select box with all glpi users
$where = " WHERE `glpi_contacts_suppliers`.`contacts_id` = `glpi_contacts`.`id`
           AND (`glpi_contacts_suppliers`.`suppliers_id` = '".$_POST['suppliers_id']."'
           AND `glpi_contacts`.`is_deleted` = '0' ) ";


if (isset($_POST["entity_restrict"])) {
   if (!is_numeric($_POST["entity_restrict"]) && !is_array($_POST["entity_restrict"])) {
      $_POST["entity_restrict"] = unserialize(Toolbox::stripslashes_deep($_POST["entity_restrict"]));
   }
   $where .= getEntitiesRestrictRequest("AND", "glpi_contacts", '', $_POST["entity_restrict"], true);
} else {
   $where .= getEntitiesRestrictRequest("AND", "glpi_contacts", '', '', true);
}

if ($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"]) {
   $where .= " AND `glpi_contacts`.`name` ".makeTextSearch($_POST['searchText']);
}

$LIMIT = "LIMIT 0, ".$CFG_GLPI["dropdown_max"];
if ($_POST['searchText'] == $CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}


$query = "SELECT `glpi_contacts`.*
          FROM `glpi_contacts`,`glpi_contacts_suppliers`
          $where
          ORDER BY `entities_id`, `name` $LIMIT";
//error_log($query);
$result = $DB->query($query);

echo "<select name='contacts_id'>";

echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";

if ($DB->numrows($result)) {
   $prev = -1;
   while ($data = $DB->fetchArray($result)) {
      if ($data["entities_id"] != $prev) {
         if ($prev > 0) {
            echo "</optgroup>";
         }
         $prev = $data["entities_id"];
         echo "<optgroup label=\"".Dropdown::getDropdownName("glpi_entities", $prev)."\">";
      }
      $output = formatUserName($data["id"], "", $data["name"], $data["firstname"]);
      if ($_SESSION["glpiis_ids_visible"] || empty($output)) {
         $output .= " (".$data["id"].")";
      }
      echo "<option value=\"".$data["id"]."\" title=\"$output\">"
        .substr($output, 0, $CFG_GLPI["dropdown_chars_limit"])."</option>";
   }
   if ($prev >= 0) {
      echo "</optgroup>";
   }
}

echo "</select>";
