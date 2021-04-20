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
if (strpos($_SERVER['PHP_SELF'], "dropdownValue.php")) {
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

$dbu = new DbUtils();

// Security
if (!($item = $dbu->getItemForItemtype($_GET['itemtype']))) {
   exit();
}

$table = $item->getTable();

$displaywith = false;

if (isset($_GET['displaywith'])
    && is_array($_GET['displaywith'])
    && count($_GET['displaywith'])) {

   $displaywith = true;
}

// No define value
if (!isset($_GET['value'])) {
   $_GET['value'] = '';
}

// No define rand
if (!isset($_GET['rand'])) {
   $_GET['rand'] = mt_rand();
}

if (isset($_GET['condition']) && !empty($_GET['condition'])) {
   $_GET['condition'] = rawurldecode(stripslashes($_GET['condition']));
}

if (!isset($_GET['emptylabel']) || $_GET['emptylabel'] == '') {
   $_GET['emptylabel'] = Dropdown::EMPTY_VALUE;
}

if (!isset($_GET['display_rootentity'])) {
   $_GET['display_rootentity'] = false;
}

if (isset($_GET["entity_restrict"])
    && !is_numeric($_GET["entity_restrict"])
    && !is_array($_GET["entity_restrict"])) {

   $_GET["entity_restrict"] = Toolbox::decodeArrayFromInput($_GET["entity_restrict"]);
}

// Make a select box with preselected values
if (!isset($_GET["limit"])) {
   $_GET["limit"] = $_SESSION["glpidropdown_chars_limit"];
}

$where = "WHERE 1 ";

if ($item->maybeDeleted()) {
   $where .= " AND `is_deleted` = 0 ";
}
if ($item->maybeTemplate()) {
   $where .= " AND `is_template` = 0 ";
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";

if ($_GET['searchText']==$CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

$where .=" AND `$table`.`id` NOT IN ('".$_GET['value']."'";

if (isset($_GET['used'])) {

   if (is_array($_GET['used'])) {
      $used = $_GET['used'];
   } else {
      $used = Toolbox::decodeArrayFromInput($_GET['used']);
   }

   if (count($used)) {
      $where .= ",'".implode("','", $used)."'";
   }
}

if (isset($_GET['toadd'])) {
   if (is_array($_GET['toadd'])) {
      $toadd = $_GET['toadd'];
   } else {
      $toadd = Toolbox::decodeArrayFromInput($_GET['toadd']);
   }
} else {
   $toadd = [];
}

$where .= ") ";

if (isset($_GET['condition']) && $_GET['condition'] != '') {
   $where .= " AND ".$_GET['condition']." ";
}

if ($item instanceof CommonTreeDropdown) {

   if ($_GET['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
      $where .= " AND `completename` ".Search::makeTextSearch($_GET['searchText']);
   }
   $multi = false;

   // Manage multiple Entities dropdowns
   $add_order = "";

   if ($item->isEntityAssign()) {
      $recur = $item->maybeRecursive();

       // Entities are not really recursive : do not display parents
      if ($_GET['itemtype'] == 'Entity') {
         $recur = false;
      }

      if (isset($_GET["entity_restrict"]) && !($_GET["entity_restrict"]<0)) {
         $where .= $dbu->getEntitiesRestrictRequest(" AND ", $table, '', $_GET["entity_restrict"],
                                              $recur);

         if (is_array($_GET["entity_restrict"]) && count($_GET["entity_restrict"])>1) {
            $multi = true;
         }

      } else {
         $where .= $dbu->getEntitiesRestrictRequest(" AND ", $table, '', '', $recur);

         if (count($_SESSION['glpiactiveentities'])>1) {
            $multi = true;
         }
      }

      // Force recursive items to multi entity view
      if ($recur) {
         $multi = true;
      }

      // no multi view for entitites
      if ($_GET['itemtype']=="Entity") {
         $multi = false;
      }

      if ($multi) {
         $add_order = '`entities_id`, ';
      }

   }

   $query = "SELECT *
             FROM `$table`
             $where
             ORDER BY $add_order `completename`
             $LIMIT";

   if ($result = $DB->query($query)) {
      echo "<select id='dropdown_".$_GET["myname"].$_GET["rand"]."' name='".$_GET['myname']."'
             size='1'";

      if (isset($_GET["on_change"]) && !empty($_GET["on_change"])) {
         echo " onChange='".$_GET["on_change"]."'";
      }
      echo ">";

      if ($_GET['searchText']!=$CFG_GLPI["ajax_wildcard"] && $DB->numrows($result)==$NBMAX) {
         echo "<option class='tree' value='0'>--".__('Limited view')."--</option>";
      }

      if (count($toadd)) {
         foreach ($toadd as $key => $val) {
            echo "<option class='tree' ".($_GET['value']==$key?'selected':'').
                 " value='$key' title=\"".Html::cleanInputText($val)."\">".
                  Toolbox::substr($val, 0, $_GET["limit"])."</option>";
         }
      }

      $display_selected = true;

      switch ($table) {
         case "glpi_entities" :
            // If entity=0 allowed
            if (isset($_GET["entity_restrict"])
                && (($_GET["entity_restrict"]<=0 && in_array(0, $_SESSION['glpiactiveentities']))
                    || (is_array($_GET["entity_restrict"])
                        && in_array(0, $_GET["entity_restrict"])))) {

               echo "<option class='tree' value='0'>".__('Root entity')."</option>";

               // Entity=0 already add above
               if ($_GET['value']==0 && !$_GET['display_rootentity']) {
                  $display_selected = false;
               }
            }
            break;

         default :
            if ($_GET['display_emptychoice']) {
               echo "<option class='tree' value='0'>".$_GET['emptylabel']."</option>";
            }
      }

      if ($display_selected) {
         $outputval = Dropdown::getDropdownName($table, $_GET['value']);

         if (Toolbox::strlen($outputval)!=0 && $outputval!="&nbsp;") {

            if (Toolbox::strlen($outputval)>$_GET["limit"]) {
               // Completename for tree dropdown : keep right
               $outputval = "&hellip;".Toolbox::substr($outputval, -$_GET["limit"]);
            }
            if ($_SESSION["glpiis_ids_visible"] || Toolbox::strlen($outputval)==0) {
               $outputval .= " (".$_GET['value'].")";
            }
            echo "<option class='tree' selected value='".$_GET['value']."'>".$outputval."</option>";
         }
      }

      $last_level_displayed = [];

      if ($DB->numrows($result)) {
         $prev = -1;

         while ($data =$DB->fetchArray($result)) {
            $ID     = $data['id'];
            $level  = $data['level'];
            $output = $data['name'];

            if ($displaywith) {
               foreach ($_GET['displaywith'] as $key) {
                  if (isset($data[$key]) && strlen($data[$key])!=0) {
                     $output .= " - ".$data[$key];
                  }
               }
            }

            if ($multi && $data["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev = $data["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
               // Reset last level displayed :
               $last_level_displayed = [];
            }

            $class = " class='tree' ";
            $raquo = "&raquo;";

            if ($level==1) {
               $class = " class='treeroot'";
               $raquo = "";
            }

            if ($_SESSION['glpiuse_flat_dropdowntree']) {
               $output = $data['completename'];
               if ($level>1) {
                  $class = "";
                  $raquo = "";
                  $level = 0;
               }

            } else { // Need to check if parent is the good one
               if ($level>1) {
                  // Last parent is not the good one need to display arbo
                  if (!isset($last_level_displayed[$level-1])
                      || $last_level_displayed[$level-1] != $data[$item->getForeignKeyField()]) {

                     $work_level    = $level-1;
                     $work_parentID = $data[$item->getForeignKeyField()];
                     $to_display    = '';

                     do {
                        // Get parent
                        if ($item->getFromDB($work_parentID)) {
                           $addcomment = "";

                           if (isset($item->fields["comment"])) {
                              $addcomment = " - ".$item->fields["comment"];
                           }
                           $output2 = $item->getName();
                           if (Toolbox::strlen($output2)>$_GET["limit"]) {
                              $output2 = Toolbox::substr($output2, 0, $_GET["limit"])."&hellip;";
                           }

                           $class2 = " class='tree' ";
                           $raquo2 = "&raquo;";

                           if ($work_level==1) {
                              $class2 = " class='treeroot'";
                              $raquo2 = "";
                           }

                           $to_display = "<option disabled value='$work_parentID' $class2
                                           title=\"".Html::cleanInputText($item->fields['completename'].
                                             $addcomment)."\">".
                                         str_repeat("&nbsp;&nbsp;&nbsp;", $work_level).
                                         $raquo2.$output2."</option>".$to_display;

                           $last_level_displayed[$work_level] = $item->fields['id'];
                           $work_level--;
                           $work_parentID = $item->fields[$item->getForeignKeyField()];

                        } else { // Error getting item : stop
                           $work_level = -1;
                        }

                     } while ($work_level > 1
                              && (!isset($last_level_displayed[$work_level])
                                  || $last_level_displayed[$work_level] != $work_parentID));

                     echo $to_display;
                  }
               }
               $last_level_displayed[$level] = $data['id'];
            }

            if (Toolbox::strlen($output)>$_GET["limit"]) {

               if ($_SESSION['glpiuse_flat_dropdowntree']) {
                  $output = "&hellip;".Toolbox::substr($output, -$_GET["limit"]);
               } else {
                  $output = Toolbox::substr($output, 0, $_GET["limit"])."&hellip;";
               }
            }

            if ($_SESSION["glpiis_ids_visible"] || Toolbox::strlen($output)==0) {
               $output .= " ($ID)";
            }
            $addcomment = "";

            if (isset($data["comment"])) {
               $addcomment = " - ".$data["comment"];
            }
            echo "<option value='$ID' $class title=\"".Html::cleanInputText($data['completename'].
                   $addcomment)."\">".str_repeat("&nbsp;&nbsp;&nbsp;", $level).$raquo.$output.
                 "</option>";
         }
         if ($multi) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
   }

} else { // Not a dropdowntree
   $multi = false;

   if ($item->isEntityAssign()) {
      $multi = $item->maybeRecursive();

      if (isset($_GET["entity_restrict"]) && !($_GET["entity_restrict"]<0)) {
         $where .= $dbu->getEntitiesRestrictRequest("AND", $table, "entities_id",
                                              $_GET["entity_restrict"], $multi);

         if (is_array($_GET["entity_restrict"]) && count($_GET["entity_restrict"])>1) {
            $multi = true;
         }

      } else {
         $where .= $dbu->getEntitiesRestrictRequest("AND", $table, '', '', $multi);

         if (count($_SESSION['glpiactiveentities'])>1) {
            $multi = true;
         }
      }
   }

   $field = "name";

   if ($_GET['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
      $search = Search::makeTextSearch($_GET['searchText']);
      $where .=" AND  (`$table`.`$field` ".$search;
      $where .= ')';
   }

   switch ($_GET['itemtype']) {

      default :
         $query = "SELECT *
                   FROM `$table`
                   $where";
   }

   if ($multi) {
      $query .= " ORDER BY `entities_id`, $field
                 $LIMIT";
   } else {
      $query .= " ORDER BY $field
                 $LIMIT";
   }

   if ($result = $DB->query($query)) {
      echo "<select id='dropdown_".$_GET["myname"].$_GET["rand"]."' name='".$_GET['myname']."'
             size='1'";

      if (isset($_GET["on_change"]) && !empty($_GET["on_change"])) {
         echo " onChange='".$_GET["on_change"]."'";
      }

      echo ">";

      if ($_GET['searchText']!=$CFG_GLPI["ajax_wildcard"] && $DB->numrows($result)==$NBMAX) {
         echo "<option value='0'>--".__('Limited view')."--</option>";

      } else if (!isset($_GET['display_emptychoice']) || $_GET['display_emptychoice']) {
         echo "<option value='0'>".$_GET["emptylabel"]."</option>";
      }

      if (count($toadd)) {
         foreach ($toadd as $key => $val) {
            echo "<option title=\"".Html::cleanInputText($val)."\" value='$key' ".
                  ($_GET['value']==$key?'selected':'').">".
                  Toolbox::substr($val, 0, $_GET["limit"])."</option>";
         }
      }

      $output = Dropdown::getDropdownName($table, $_GET['value']);

      if (strlen($output)!=0 && $output!="&nbsp;") {
         if ($_SESSION["glpiis_ids_visible"]) {
            $output .= " (".$_GET['value'].")";
         }
         echo "<option selected value='".$_GET['value']."'>".$output."</option>";
      }

      if ($DB->numrows($result)) {
         $prev = -1;

         while ($data =$DB->fetchArray($result)) {
            $output = $data[$field];

            if ($displaywith) {
               foreach ($_GET['displaywith'] as $key) {
                  if (isset($data[$key]) && strlen($data[$key])!=0) {
                     $output .= " - ".$data[$key];
                  }
               }
            }
            $ID = $data['id'];
            $addcomment = "";

            if (isset($data["comment"])) {
               $addcomment = " - ".$data["comment"];
            }
            if ($_SESSION["glpiis_ids_visible"] || strlen($output)==0) {
               $output .= " ($ID)";
            }

            if ($multi && $data["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev = $data["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
            }

            echo "<option value='$ID' title=\"".Html::cleanInputText($output.$addcomment)."\">".
                  Toolbox::substr($output, 0, $_GET["limit"])."</option>";
         }

         if ($multi) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
   }
}

if (isset($_GET["comment"]) && $_GET["comment"]) {
   $paramscomment = ['value' => '__VALUE__',
                          'table' => $table];

   Ajax::updateItemOnSelectEvent("dropdown_".$_GET["myname"].$_GET["rand"],
                                 "comment_".$_GET["myname"].$_GET["rand"],
                                 $CFG_GLPI["root_doc"]."/ajax/comments.php", $paramscomment);
}

if (isset($_GET["action"]) && $_GET["action"]) {


   $sort = false;
   if (isset($_GET['sort']) && !empty($_GET['sort'])) {
      $sort = $_GET['sort'];
   }

   $params=[$_GET['myname'] => '__VALUE__',
                       'entity_restrict' => $_GET['entity_restrict'],
                       'rand' => $_GET['rand'],
                       'sort' => $sort];

   Ajax::updateItemOnSelectEvent("dropdown_".$_GET["myname"].$_GET["rand"], $_GET['span'],
                                     $_GET['action'],
                                     $params);

}

Ajax::commonDropdownUpdateItem($_GET);
