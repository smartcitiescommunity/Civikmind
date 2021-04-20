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

include ('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

if (isset($_POST["action"])) {
   switch ($_POST["action"]) {
      case "update_checklist":
          echo "&nbsp;<input type='submit' name='update_checklist' class='submit' value='"._sx('button', 'Post')."'></td>";
         break;
      case "delete_checklist":
         echo "&nbsp;<input type='submit' name='delete_checklist' class='submit' value='"._sx('button', 'Post')."'></td>";
         break;
      case "open_checklist":
         echo "<input type='hidden' name='checklist_type' value='".$_POST['checklist_type']."'>";
         echo Html::hidden('plugin_resources_resources_id', ['value' => $_POST['plugin_resources_resources_id']]);
         echo "&nbsp;<input type='submit' name='open_checklist' class='submit' value='"._sx('button', 'Post')."'></td>";
         break;
      case "close_checklist":
         echo "<input type='hidden' name='checklist_type' value='".$_POST['checklist_type']."'>";
         echo Html::hidden('plugin_resources_resources_id', ['value' => $_POST['plugin_resources_resources_id']]);
         echo "<input type='hidden' name='entities_id' value='".$_POST['entities_id']."'>";
         echo "&nbsp;";
         echo __('Templates');
         echo "&nbsp;";
         Dropdown::show('TicketTemplate', ['name'  => 'tickettemplates_id',
                                       'entities_id' => $_POST["entities_id"]]);
         echo "&nbsp;";
         echo __('Total duration');
         echo "&nbsp;";
         Dropdown::showTimeStamp('actiontime', ['addfirstminutes' => true]);
               echo "&nbsp;<input type='submit' name='close_checklist' class='submit' value='"._sx('button', 'Post')."'></td>";
         break;
      case "add_task":
         echo "&nbsp;".__('Assigned to')."&nbsp;";
         User::dropdown(['name' => "users_id",'right' => 'interface']);
         echo "&nbsp;<input type='submit' name='add_task' class='submit' value='"._sx('button', 'Post')."'></td>";
         break;
      case "add_ticket":
         echo "&nbsp;<input type='submit' name='add_ticket' class='submit' value='"._sx('button', 'Post')."'></td>";
         break;
   }
}

