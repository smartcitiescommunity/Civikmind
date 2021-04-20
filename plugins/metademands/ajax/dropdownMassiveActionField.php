<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();
$dbu = new DbUtils();
if (!isset($_POST["itemtype"]) || !($item = $dbu->getItemForItemtype($_POST['itemtype']))) {
   exit();
}

if (in_array($_POST["itemtype"], $CFG_GLPI["infocom_types"])) {
   Session::checkSeveralRightsOr([$_POST["itemtype"] => UPDATE,
                                  "infocom"          => UPDATE]);
} else {
   $item->checkGlobal(UPDATE);
}

if (isset($_POST["itemtype"]) && isset($_POST["id_field"]) && $_POST["id_field"]) {
   $search = Search::getOptions($_POST["itemtype"]);
   if (!isset($search[$_POST["id_field"]])) {
      exit();
   }
   $search            = $search[$_POST["id_field"]];
   $FIELDNAME_PRINTED = false;
   $USE_TABLE         = false;

   if ($search["table"] == $dbu->getTableForItemType($_POST["itemtype"])) { // field type
      switch ($search["table"] . "." . $search["linkfield"]) {
         case "glpi_tickets.status" :
            Ticket::dropdownStatus(['name'  => $search["linkfield"],
                                    'value' => $_POST["value"]]);
            break;

         case "glpi_tickets.items_id" :
            if (isset($_POST['itemtype_used']) && !empty($_POST['itemtype_used'])) {
               Dropdown::show($_POST['itemtype_used'], ['name' => $search["linkfield"], 'value' => $_POST["value"]]);
            }
            break;

         case "glpi_tickets.type" :
            Ticket::dropdownType($search["linkfield"], ['value' => $_POST["value"]]);
            break;

         case "glpi_tickets.priority" :
            Ticket::dropdownPriority(['name' => $search["linkfield"], 'value' => $_POST["value"]]);
            break;

         case "glpi_tickets.impact" :
            Ticket::dropdownImpact(['name' => $search["linkfield"], 'value' => $_POST["value"]]);
            break;

         case "glpi_tickets.urgency" :
            Ticket::dropdownUrgency(['name' => $search["linkfield"], 'value' => $_POST["value"]]);
            break;

         case "glpi_tickets.global_validation" :
            TicketValidation::dropdownStatus($search["linkfield"], ['value' => $_POST["value"]]);
            break;
         default :
            // Specific plugin Type case
            $plugdisplay = false;
            if ($plug = isPluginItemType($_POST["itemtype"])) {
               $plugdisplay = Plugin::doOneHook($plug['plugin'], 'MassiveActionsFieldsDisplay',
                                                ['itemtype' => $_POST["itemtype"],
                                                 'options'  => $search]);
            }
            $already_display = false;

            if (isset($search['datatype'])) {

               switch ($search['datatype']) {
                  case "date" :
                     echo "<table><tr><td>";
                     Html::showDateField($search["linkfield"], ['value' => $_POST['value']]);
                     echo "</td>";
                     $USE_TABLE       = true;
                     $already_display = true;
                     break;

                  case "datetime" :
                     if (!isset($_POST['relative_dates']) || !$_POST['relative_dates']) {
                        echo "<table><tr><td>";
                        Html::showDateTimeField($search["linkfield"], ['value' => $_POST['value']]);
                        echo "</td>";
                        $already_display = true;
                        $USE_TABLE       = true;
                     } else { // For ticket template
                        Html::showGenericDateTimeSearch($search["linkfield"], $_POST['value'],
                                                        ['with_time'          => true,
                                                         'with_future'
                                                                              => (isset($search['maybefuture'])
                                                                                  && $search['maybefuture']),
                                                         'with_days'          => false,
                                                         'with_specific_date' => false]);

                        $already_display = true;
                     }
                     break;

                  case "itemtypename" :
                     if (isset($search['itemtype_list'])) {
                        Dropdown::dropdownTypes($search["linkfield"], $_POST['value'], $CFG_GLPI[$search['itemtype_list']]);
                        $already_display = true;
                     }
                     break;

                  case "bool" :
                     Dropdown::showYesNo($search["linkfield"], $_POST['value']);
                     $already_display = true;
                     break;

                  case "timestamp" :
                     Dropdown::showTimeStamp($search["linkfield"], ['value' => $_POST['value']]);
                     $already_display = true;
                     break;

                  case "text" :
                     Html::textarea(['name'              => $search["linkfield"],
                                     'cols'              => '45',
                                     'rows'              => '5',
                                     'value'             => $_POST['value'],
                                     'enable_richtext'   => false,
                                     'enable_fileupload' => false]);
                     $already_display = true;
                     break;
               }
            }

            if (!$plugdisplay && !$already_display) {
               $newtype = $dbu->getItemTypeForTable($search["table"]);
               if ($newtype != $_POST["itemtype"]) {
                  $item = new $newtype();
               }
               Html::autocompletionTextField($item, $search["linkfield"],
                                             ['name'   => $search["linkfield"],
                                              'value'  => stripslashes($_POST['value']),
                                              'entity' => $_SESSION["glpiactive_entity"]]);
            }
      }

   } else {
      switch ($search["table"]) {
         case "glpi_users" : // users
            switch ($search["linkfield"]) {
               //                case "users_id_assign" :
               //                   User::dropdown(array('name'   => $search["linkfield"],
               //                                        'right'  => 'own_ticket',
               //                                        'entity' => $_SESSION["glpiactive_entity"]));
               //                   break;

               case "users_id_tech" :
                  User::dropdown(['name'   => $search["linkfield"],
                                  'value'  => $_POST["value"],
                                  'right'  => 'own_ticket',
                                  'entity' => $_SESSION["glpiactive_entity"]]);
                  break;

               default :
                  User::dropdown(['name'   => $search["linkfield"],
                                  'value'  => $_POST["value"],
                                  'entity' => $_SESSION["glpiactive_entity"],
                                  'right'  => 'all']);
            }
            break;

            break;

         case "glpi_softwareversions":
            switch ($search["linkfield"]) {
               case "softwareversions_id_use" :
               case "softwareversions_id_buy" :
                  $_POST['softwares_id'] = $_POST['extra_softwares_id'];
                  $_POST['myname']       = $search['linkfield'];
                  include("dropdownInstallVersion.php");
                  break;
            }
            break;

         default : // dropdown case
            $plugdisplay = false;
            // Specific plugin Type case
            if (($plug = isPluginItemType($_POST["itemtype"]))
                // Specific for plugin which add link to core object
                || ($plug = isPluginItemType($dbu->getItemTypeForTable($search['table'])))) {
               $plugdisplay = Plugin::doOneHook($plug['plugin'], 'MassiveActionsFieldsDisplay',
                                                ['itemtype' => $_POST["itemtype"],
                                                 'options'  => $search]);
            }
            $already_display = false;

            if (isset($search['datatype'])) {
               switch ($search['datatype']) {
                  case "date" :
                     echo "<table><tr><td>";
                     Html::showDateField($search["linkfield"], $_POST["value"]);
                     echo "</td>";
                     $USE_TABLE       = true;
                     $already_display = true;
                     break;

                  case "datetime" :
                     echo "<table><tr><td>";
                     Html::showDateTimeField($search["linkfield"], ['value' => $_POST["value"]]);
                     echo "</td>";
                     $already_display = true;
                     $USE_TABLE       = true;
                     break;

                  case "bool" :
                     Dropdown::showYesNo($search["linkfield"], $_POST["value"]);
                     $already_display = true;
                     break;

                  case "text" :
                     echo "<textarea cols='45' rows='5' name='" . $search["linkfield"] . "' >" . $_POST["value"] . "</textarea>";
                     $already_display = true;
                     break;
               }
            }

            if (!$plugdisplay && !$already_display) {
               $cond = (isset($search['condition']) ? $search['condition'] : []);
               Dropdown::show($dbu->getItemTypeForTable($search["table"]),
                              ['name'      => $search["linkfield"],
                               'value'     => $_POST["value"],
                               'entity'    => $_SESSION['glpiactiveentities'],
                               'condition' => $cond]);
            }
      }
   }

   if ($USE_TABLE) {
      echo "<td>";
   }

   if (!$FIELDNAME_PRINTED) {
      if (empty($search["linkfield"])) {
         echo "<input type='hidden' name='field' value='" . $search["field"] . "'>";
      } else {
         echo "<input type='hidden' name='field' value='" . $search["linkfield"] . "'>";
      }
   }

   if ($USE_TABLE) {
      echo "</td></tr></table>";
   }

}
