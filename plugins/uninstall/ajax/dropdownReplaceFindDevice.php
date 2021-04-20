<?php
/*
 * @version $Id: dropdownReplaceFindDevice.php 149 2013-07-10 09:54:40Z tsmr $
 LICENSE

 This file is part of the uninstall plugin.

 Uninstall plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Uninstall plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with uninstall. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   uninstall
 @author    the uninstall plugin team
 @copyright Copyright (c) 2010-2013 Uninstall plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/uninstall
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$itemtypeisplugin = isPluginItemType($_REQUEST['itemtype']);
$item             = new $_REQUEST['itemtype']();
$table            = getTableForItemType($_REQUEST['itemtype']);
$options          = [];
$count            = 0;
$datastoadd       = [];

$displaywith = false;
if (isset($_REQUEST['displaywith'])) {
   if (is_array($_REQUEST['displaywith']) && count($_REQUEST['displaywith'])) {
      $displaywith = true;
   }
}

if ($item->isEntityAssign()) {
   // allow opening ticket on recursive object (printer, software, ...)
   $where = getEntitiesRestrictRequest("WHERE", $table, '',
                                         $_SESSION['glpiactiveentities'], $item->maybeRecursive());

} else {
   $where = "WHERE 1";
}

if ($item->maybeDeleted()) {
   $where .= " AND `is_deleted` = '0' ";
}
if ($item->maybeTemplate()) {
   $where .= " AND `is_template` = '0' ";
}

if (isset($_REQUEST['searchText'])
    && strlen($_REQUEST['searchText']) > 0
    && $_REQUEST['searchText'] != $CFG_GLPI["ajax_wildcard"]) {
   $search = Search::makeTextSearch($_REQUEST['searchText']);

   $where .= " AND (`name` ".$search."
                    OR `id` = '".$_REQUEST['searchText']."'
                    OR `serial` ".$search."
                    OR `otherserial` ".$search.")";
}

//If software or plugins : filter to display only the objects that are allowed to be visible in Helpdesk
if (in_array($_REQUEST['itemtype'], $CFG_GLPI["helpdesk_visible_types"])) {
   $where .= " AND `is_helpdesk_visible` = '1' ";
}

if (isset($_REQUEST['used'])) {
   $used = $_REQUEST['used'];

   if (count($used)) {
      $where .=" AND `$table`.`id` NOT IN ('".implode("','", $used)."' ) ";
   }
}

if (isset($_REQUEST['current_item']) && ($_REQUEST['current_item'] > 0)) {
   $where .= " AND `id` != " . $_REQUEST['current_item'];
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";

if (isset($_REQUEST['searchText'])
    && $_REQUEST['searchText'] == $CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

$query = "SELECT *
          FROM $table
          $where
          ORDER BY `name`
          $LIMIT";
$result = $DB->query($query);
while ($data = $DB->fetchAssoc($result)) {
   $outputval = Toolbox::unclean_cross_side_scripting_deep($data["name"]);

   if ($displaywith) {
      foreach ($_REQUEST['displaywith'] as $key) {
         if (isset($data[$key])) {
            $withoutput = $data[$key];
            if (isForeignKeyField($key)) {
               $withoutput = Dropdown::getDropdownName(getTableNameForForeignKeyField($key),
                                                       $data[$key]);
            }
            if ((strlen($withoutput) > 0) && ($withoutput != '&nbsp;')) {
               $outputval = sprintf(__('%1$s - %2$s'), $outputval, $withoutput);
            }
         }
      }
   }
   $ID         = $data['id'];
   $addcomment = "";
   $title      = $outputval;
   if (isset($data["comment"])) {
      $addcomment .= $data["comment"];
      $title = sprintf(__('%1$s - %2$s'), $title, $addcomment);
   }
   if ($_SESSION["glpiis_ids_visible"]
       || (strlen($outputval) == 0)) {
      $outputval = sprintf(__('%1$s (%2$s)'), $outputval, $ID);
   }
   array_push($options, ['id'     => $ID,
                         'text'  => $outputval,
                         'title' => $title]);
   $count++;
}


echo json_encode(['results' => $options,
                  'count'    => $count]);
