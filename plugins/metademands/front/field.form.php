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
Session::checkLoginUser();

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$field = new PluginMetademandsField();

if (!isset($_POST["check_value"])) {
   $_POST["check_value"] = PluginMetademandsField::_serialize([]);
} else {
   $field->getFromDB($_POST["id"]);
   $hidden_kinks = PluginMetademandsField::_unserialize($field->fields["hidden_link"]);
   if (is_array($hidden_kinks)) {
      foreach ($hidden_kinks as $hidden_link) {
         $update["id"]      = $hidden_link;
         $update["to_hide"] = 0;
         $field->update($update);
      }
   }

   foreach ($_POST["hidden_link"] as $idField) {
      $update            = [];
      $update["id"]      = $idField;
      $update["to_hide"] = 1;
      $field->update($update);
   }
}

if (isset($_POST['type']) && $_POST['type'] == 'dropdown_object'
    && isset($_POST['item']) && $_POST['item'] == 'Group') {
   if (isset($_POST['is_assign']) && $_POST['is_assign'] > 0) {
      $custom_values['is_assign'] = $_POST['is_assign'];
   }
   if (isset($_POST['is_watcher']) && $_POST['is_watcher'] > 0) {
      $custom_values['is_watcher'] = $_POST['is_watcher'];
   }
   if (isset($_POST['is_requester']) && $_POST['is_requester'] > 0) {
      $custom_values['is_requester'] = $_POST['is_requester'];
   }
   $_POST['custom_values'] = $custom_values;
}

if (isset($_POST['item']) && isset($_POST['type']) && (empty($_POST['item']) || $_POST['item'] === 0)) {
   $_POST['item'] = $_POST['type'];
}

if (isset($_POST['type']) && $_POST['type'] == 'number') {
   $custom_values['min']   = $_POST['min'];
   $custom_values['max']   = $_POST['max'];
   $custom_values['step']  = $_POST['step'];
   $_POST['custom_values'] = $custom_values;
}

if (isset($_POST["add"])) {
   if (isset($_POST["custom_values"]) && is_array($_POST["custom_values"])) {
      if (isset($_POST['type']) && $_POST['type'] == 'dropdown_multiple') {
         $_POST['item'] = 'other';
      }
      $_POST["custom_values"] = PluginMetademandsField::_serialize($_POST["custom_values"]);
      if (isset($_POST["comment_values"])) {
         $_POST["comment_values"] = PluginMetademandsField::_serialize($_POST["comment_values"]);
      }
      if (isset($_POST["default_values"])) {
         $_POST["default_values"] = PluginMetademandsField::_serialize($_POST["default_values"]);
      }
   }
   // Check update rights for fields
   $field->check(-1, UPDATE, $_POST);
   if ($_POST['id'] = $field->add($_POST)) {
      $field->recalculateOrder($_POST);
      PluginMetademandsMetademand::addLog($_POST, PluginMetademandsMetademand::LOG_ADD);
      unset($_SESSION['glpi_plugin_metademands_fields']);
   }

   Html::back();

} else if (isset($_POST["update"])) {

   if ($_POST["type"] == 'checkbox'
       || $_POST["type"] == 'radio') {
      $_POST["item"] = 0;
   }
   if (isset($_POST["custom_values"]) && is_array($_POST["custom_values"])
       && ($_POST["item"] == 'other'
           || $_POST["type"] == 'checkbox'
           || $_POST["type"] == 'radio'
           || $_POST["type"] == 'dropdown_multiple'
           || $_POST['item'] == 'Group'
           || $_POST['type'] == 'number')) {
      $comment_values = "";
      $custom_values  = [];
      $default_values = [];
      if (isset($_POST['custom_values'])) {
         $custom_values = $_POST['custom_values'];
      }
      if (isset($_POST['comment_values'])) {
         $comment_values = $_POST['comment_values'];
      }
      if (isset($_POST['default_values'])) {
         $default_values = $_POST['default_values'];
      }
      $_POST["custom_values"]  = PluginMetademandsField::_serialize($custom_values);
      $_POST["comment_values"] = PluginMetademandsField::_serialize($comment_values);
      $_POST["default_values"] = PluginMetademandsField::_serialize($default_values);
   } else if ($_POST["type"] == 'link') {
      $_POST["custom_values"]  = PluginMetademandsField::_serialize($_POST['custom_values']);
      $_POST["comment_values"] = '';
   } else if ($_POST["type"] != 'yesno') {
      $_POST["custom_values"]  = '';
      $_POST["comment_values"] = '';
   }
   if (isset($_POST["value"]) && is_array($_POST["value"])) {
      $_POST["value"] = PluginMetademandsField::_serialize($_POST["value"]);
   }

   if (isset($_POST["check_value"]) && is_array($_POST["check_value"])) {
      $_POST["check_value"] = PluginMetademandsField::_serialize($_POST["check_value"]);
   }
   if (isset($_POST["plugin_metademands_tasks_id"])) {
      $_POST["plugin_metademands_tasks_id"] = PluginMetademandsField::_serialize($_POST["plugin_metademands_tasks_id"]);
   }
   if (isset($_POST["fields_link"])) {
      $_POST["fields_link"] = PluginMetademandsField::_serialize($_POST["fields_link"]);
   }
   if (isset($_POST["hidden_link"])) {
      $_POST["hidden_link"] = PluginMetademandsField::_serialize($_POST["hidden_link"]);
   }
   if (isset($_POST["hidden_block"])) {
      $_POST["hidden_block"] = PluginMetademandsField::_serialize($_POST["hidden_block"]);
   }

   if(!isset($_POST['item'])){
      $_POST['item'] ="";
   }
   // Check update rights for fields
   $field->check(-1, UPDATE, $_POST);
   if ($field->update($_POST)) {
      $field->recalculateOrder($_POST);
      PluginMetademandsMetademand::addLog($_POST, PluginMetademandsMetademand::LOG_UPDATE);

      //Hook to add and update values add from plugins
      if (isset($PLUGIN_HOOKS['metademands'])) {
         $plugin = new Plugin();
         foreach ($PLUGIN_HOOKS['metademands'] as $plug => $method) {
            $p = $_POST;
            $new_res = PluginMetademandsField::getPluginSaveOptions($plug,$p);
         }
      }
   }

   Html::redirect($field->getFormURL() . "?id=" . $_POST["id"]);

}  else if (isset($_POST["clear_option"])) {
   // Check update rights for fields
   $ids = $_POST['option'];

   foreach ($ids as $k => $v) {
      unset($_POST["check_value"][$k]);
      unset($_POST["plugin_metademands_tasks_id"][$k]);
      unset($_POST["fields_link"][$k]);
      unset($_POST["hidden_link"][$k]);
      unset($_POST["hidden_block"][$k]);
   }
   $input["id"] = $_POST["id"];
   if (isset($_POST["check_value"])) {
      $input["check_value"] = PluginMetademandsField::_serialize($_POST["check_value"]);
   }
   if (isset($_POST["plugin_metademands_tasks_id"])) {
      $input["plugin_metademands_tasks_id"] = PluginMetademandsField::_serialize($_POST["plugin_metademands_tasks_id"]);
   }
   if (isset($_POST["fields_link"])) {
      $input["fields_link"] = PluginMetademandsField::_serialize($_POST["fields_link"]);
   }
   if (isset($_POST["hidden_link"])) {
      $input["hidden_link"] = PluginMetademandsField::_serialize($_POST["hidden_link"]);
   }
   if (isset($_POST["hidden_block"])) {
      $input["hidden_block"] = PluginMetademandsField::_serialize($_POST["hidden_block"]);
   }

   $field->check(-1, UPDATE, $_POST);
   $field->update($input);
   Html::redirect($field->getFormURL() . "?id=" . $input["id"]);

} else if (isset($_POST["purge"])) {
   // Check update rights for fields
   $field->check(-1, UPDATE, $_POST);
   $field->delete($_POST, 1);

   PluginMetademandsMetademand::addLog($_POST, PluginMetademandsMetademand::LOG_DELETE);
   $field->redirectToList();

} else if (isset($_POST["delete_custom_value"])) {
   if (isset($_POST["custom_values"]) && is_array($_POST["custom_values"])) {
      foreach ($_POST["custom_values"] as $key => $value) {
         if ($key == key($_POST["delete_custom_value"])) {
            unset($_POST["custom_values"][$key]);
            unset($_POST["comment_values"][$key]);
            unset($_POST["default_values"][$key]);
         }
      }
      $_POST["custom_values"]  = PluginMetademandsField::_serialize($_POST["custom_values"]);
      $_POST["comment_values"] = PluginMetademandsField::_serialize($_POST["comment_values"]);
      $_POST["default_values"] = PluginMetademandsField::_serialize($_POST["default_values"]);
      // Check update rights for fields
      $field->check(-1, UPDATE, $_POST);
      $field->update($_POST);
   }

   Html::back();

} else {
   $field->checkGlobal(READ);
   Html::header(PluginMetademandsField::getTypeName(2), '', "helpdesk", "pluginmetademandsmetademand");
   Html::requireJs('tinymce');
   $field->display(['id' => $_GET["id"]]);
   Html::footer();
}
