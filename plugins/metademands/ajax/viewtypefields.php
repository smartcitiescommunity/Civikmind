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

if (!isset($_POST['step'])) {
   $_POST['step'] = 'default';
}

switch ($_POST['step']) {
   case 'order':
      $fields = new PluginMetademandsField();
      $fields->showOrderDropdown($_POST['rank'],
                                 $_POST['fields_id'],
                                 $_POST['previous_fields_id'],
                                 $_POST["metademands_id"]);
      break;
   case 'object':
      global $CFG_GLPI;
      if ($_POST["type"] == "dropdown"
          || $_POST["type"] == "dropdown_object"
          || $_POST["type"] == "dropdown_meta"
          || $_POST["type"] == "dropdown_multiple") {
         $randItem   = PluginMetademandsField::dropdownFieldItems("item", $_POST["type"], ['value' => $_POST['item'], 'rand' => $_POST["rand"]]);
         $paramsItem = ['value'          => '__VALUE__',
                        'item'           => '__VALUE__',
                        'type'           => $_POST['type'],
                        'task_link'      => $_POST['task_link'],
                        'fields_link'    => $_POST['fields_link'],
                        'max_upload'     => $_POST['max_upload'],
                        'regex'          => $_POST['regex'],
                        'display_type'   => $_POST['display_type'],
                        //                     'fields_display' => $this->fields['fields_display'],
                        'hidden_link'    => $_POST['hidden_link'],
                        'hidden_block'   => $_POST['hidden_block'],
                        'metademands_id' => $_POST["metademands_id"],
                        'custom_values'  => $_POST["custom_values"],
                        'comment_values' => $_POST["comment_values"],
                        'default_values' => $_POST["default_values"],
                        'check_value'    => $_POST['check_value']];
         Ajax::updateItemOnSelectEvent('dropdown_item' . $randItem, "show_values", $CFG_GLPI["root_doc"] .
                                                                                   "/plugins/metademands/ajax/viewtypefields.php?id=" . $_POST['metademands_id'], $paramsItem);
      }

      break;
   default:
      $fields = new PluginMetademandsField();
      $fields->getEditValue(PluginMetademandsField::_unserialize(stripslashes($_POST['custom_values'])),
                            PluginMetademandsField::_unserialize(stripslashes($_POST['comment_values'])),
                            PluginMetademandsField::_unserialize(stripslashes($_POST['default_values'])),
                            $_POST);
      $fields->viewTypeField($_POST);
      break;
}

Html::ajaxFooter();
