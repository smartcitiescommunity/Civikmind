<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");

$tickettask = new TicketTask();

if (isset($_POST['tickets_id']) && isset($_POST['tickettasks_id']) && $tickettask->getFromDB($_POST['tickettasks_id'])) {
   switch ($_POST ['action']) {
      case "showCloneTicketTask" :
         $rand = mt_rand();
         echo '<tr class="tab_bg_1"><td colspan="3"></td>';
         echo '<td style="padding-left:0px">';
         echo "<div class='fa-label'>";
         $value = $tickettask->fields['date'];
         if (!empty($tickettask->fields['begin'])) {
            $value = date('Y-m-d H:i:s', strtotime($tickettask->fields['begin'] . ' + 1 DAY'));
         }
         $randDate      = Html::showDateTimeField('new_date', ['value'   => $value,
                                                               'rand'    => $rand,
                                                               'mintime' => $CFG_GLPI["planning_begin"],
                                                               'maxtime' => $CFG_GLPI["planning_end"]]);
         $params        = json_encode(['root_doc'       => $CFG_GLPI['root_doc'],
                                       'new_date_id'    => 'showdate' . $randDate,
                                       'tickets_id'     => $_POST['tickets_id'],
                                       'tickettasks_id' => $_POST['tickettasks_id']]);
         $tickettask_id = $_POST['tickettasks_id'];
         echo "<span name=\"duplicate_$tickettask_id\" onclick='cloneTicketTask($params);'>";
         echo "<i class='far fa-clone fa-fw pointer'
            title='" . _sx('button', 'Duplicate') . "'></i>";

         echo "</span>";
         echo '</div>';
         echo '</td></tr>';
         break;

      case "cloneTicketTask":
         header('Content-Type: application/json; charset=UTF-8"');

         if (isset($_POST['new_date_value'])) {
            $tickettask->fields['begin'] = $_POST['new_date_value'];
         }

         unset($tickettask->fields['end']);
         unset($tickettask->fields['id']);
         $tickettask->fields['date']    = date("Y-m-d H:i:s ", time());
         $tickettask->fields['content'] = addslashes($tickettask->fields['content']);
         $tickettask->fields['plan']    = ['begin'     => date("Y-m-d H:i:s ", strtotime($tickettask->fields['begin'])),
                                           '_duration' => $tickettask->fields['actiontime'],
                                           'users_id'  => $tickettask->fields['users_id_tech']];

         if ($id = $tickettask->add($tickettask->fields)) {
            echo json_encode(['tickettasks_id' => $id]);
         }
         break;
   }
}
