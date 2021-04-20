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

if (strpos($_SERVER['PHP_SELF'], "dropdownValue.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

$table = $_POST['table'];

$displaywith = false;

if (isset($_POST['displaywith'])
    && is_array($_POST['displaywith'])
    && count($_POST['displaywith'])) {

   $displaywith = true;
}

// No define value
if (!isset($_POST['value'])) {
   $_POST['value'] = '';
}

// No define rand
if (!isset($_POST['rand'])) {
   $_POST['rand'] = mt_rand();
}

if (isset($_POST['condition']) && !empty($_POST['condition'])) {
   $_POST['condition'] = rawurldecode(stripslashes($_POST['condition']));
}

if (!isset($_POST['emptylabel']) || $_POST['emptylabel'] == '') {
   $_POST['emptylabel'] = Dropdown::EMPTY_VALUE;
}

if (!isset($_POST['display_rootentity'])) {
   $_POST['display_rootentity'] = false;
}

if (isset($_POST["entity_restrict"])
    && !is_numeric($_POST["entity_restrict"])
    && !is_array($_POST["entity_restrict"])) {

   $_POST["entity_restrict"] = unserialize(stripslashes($_POST["entity_restrict"]));
}

// Make a select box with preselected values
if (!isset($_POST["limit"])) {
   $_POST["limit"] = $_SESSION["glpidropdown_chars_limit"];
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";


if ($_POST['searchText'] == $CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

echo "<select id='dropdown_".$_POST["myname"].$_POST["rand"]."' name='".$_POST['myname']."'
          size='1'";

if (isset($_POST["on_change"]) && !empty($_POST["on_change"])) {
   echo " onChange='".$_POST["on_change"]."'";
}

echo ">";

$query = "SELECT `gr`.`name`, `gr`.`id`, `grm`.`reference_code`
                FROM `".$table."` AS gr, `glpi_plugin_order_references_suppliers` AS grm
                WHERE `gr`.`itemtype` = '".$_POST["itemtype"]."'
                   AND `grm`.`suppliers_id` = '".$_POST["suppliers_id"]."'
                   AND `grm`.`plugin_order_references_id` = `gr`.`id`
                   AND `gr`.`is_active`='1' AND `gr`.`is_deleted`='0'";

if ($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"]) {
   $search = Search::makeTextSearch($_POST['searchText']);
   $query .= " AND  `gr`.`name` ".$search;
}

$query .= "ORDER BY `gr`.`name` ASC";

if ($result = $DB->query($query)) {

   if ($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"] && $DB->numrows($result) == $NBMAX) {
      echo "<option value='0'>--" . __("Limited view") . "--</option>";

   } else if (!isset($_POST['display_emptychoice']) || $_POST['display_emptychoice']) {
      echo "<option value='0'>" . $_POST["emptylabel"] . "</option>";
   }

   foreach ($DB->request($query) as $data) {
      echo "<option value='" . $data["id"] . "'>" . $data['name'];
      if ($data['reference_code']) {
         echo " (" . $data['reference_code'] . ")";
      }
      echo "</option>";
   }
}
echo "</select>";

if (isset($_POST["comment"]) && $_POST["comment"]) {
   Ajax::updateItemOnSelectEvent("dropdown_" . $_POST["myname"] . $_POST["rand"],
                                 "comment_" . $_POST["myname"] . $_POST["rand"],
                                 $CFG_GLPI["root_doc"] . "/ajax/comments.php",
                                 [
                                    'value' => '__VALUE__',
                                    'table' => $table
                                 ]);
}

if (isset($_POST["action"]) && $_POST["action"]) {
   $params = [
      $_POST['myname']   => '__VALUE__',
      'entity_restrict'  => $_POST['entity_restrict'],
      'suppliers_id'     => $_POST["suppliers_id"],
      'rand'             => $_POST['rand'],
      'itemtype'         => $_POST['itemtype'],
   ];

   Ajax::updateItemOnSelectEvent("dropdown_" . $_POST["myname"] . $_POST["rand"], $_POST['span'],
                                 $_POST['action'],
                                 $params);
}

Ajax::commonDropdownUpdateItem($_POST);
