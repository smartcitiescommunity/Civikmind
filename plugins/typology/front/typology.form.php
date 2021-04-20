<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 typology plugin for GLPI
 Copyright (C) 2009-2016 by the typology Development Team.

 https://github.com/InfotelGLPI/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of typology.

 typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$typo = new PluginTypologyTypology();
$typo_item = new PluginTypologyTypology_Item();

if (isset($_POST["add"])) {
   $typo->check(-1, CREATE, $_POST);
   $newID = $typo->add($_POST);

   Html::back();

} else if (isset($_POST["delete"])) {
   $typo->check($_POST['id'], DELETE);
   $typo->delete($_POST);

   $typo->redirectToList();

} else if (isset($_POST["update"])) {
   $typo->check($_POST['id'], UPDATE);
   $typo->update($_POST);

   Html::back();

} else if (isset($_POST["purge"])) {
   $typo->check($_POST['id'], PURGE);
   $typo->delete($_POST, 1);
   $typo->redirectToList();

} else if (isset($_POST["restore"])) {
   $typo->check($_POST['id'], PURGE);
   $typo->restore($_POST);
   $typo->redirectToList();

} else if (isset($_POST["add_item"])) {

   if (!empty($_POST['itemtype'])) {

      $input = ['plugin_typology_typologies_id' => $_POST['plugin_typology_typologies_id'],
                              'items_id'      => $_POST['items_id'],
                              'itemtype'      => $_POST['itemtype']];
      $item = new $_POST['itemtype']();
      if ($item->getFromDB($_POST['items_id'])) {
         $ruleCollection = new PluginTypologyRuleTypologyCollection($item->fields['entities_id']);
         $fields= [];
         $item->input = $_POST['plugin_typology_typologies_id'];
         $fields=$ruleCollection->processAllRules($item->fields, $fields, []);
         //Store rule that matched

         if (isset($fields['_ruleid'])) {
            if ($input['plugin_typology_typologies_id'] != $fields['plugin_typology_typologies_id']) {
               $message = __('Element not match with the rule for assigning the typology:', 'typology')." ".
                  Dropdown::getDropdownName('glpi_plugin_typology_typologies', $input['plugin_typology_typologies_id']);
               Session::addMessageAfterRedirect($message, ERROR, true);
            } else {
               $typo_item->check(-1, UPDATE, $input);
               $typo_item->add($input);

               $values = ['plugin_typology_typologies_id' => $input['plugin_typology_typologies_id'],
                            'items_id'      => $input['items_id'],
                            'itemtype'      => $input['itemtype']];

               PluginTypologyTypology_Item::addLog($values, PluginTypologyTypology_Item::LOG_ADD);

            }
         } else {
            $message = __('Element not match with rules for assigning a typology', 'typology');
            Session::addMessageAfterRedirect($message, ERROR, true);
         }
      }
   }
   Html::back();

} else if (isset($_POST["update_item"])) {

   if (!empty($_POST['itemtype'])) {

      $input=PluginTypologyTypology_Item::checkValidated($_POST);
      $typo_item->check($input['id'], UPDATE);
      $typo_item->update($input);

      $values = ['plugin_typology_typologies_id' => $input['plugin_typology_typologies_id'],
                            'items_id'      => $input['items_id'],
                            'itemtype'      => $input['itemtype']];

      PluginTypologyTypology_Item::addLog($values, PluginTypologyTypology_Item::LOG_UPDATE);

   }
   Html::back();

} else if (isset($_POST["delete_item"])) {

   if (!empty($_POST['itemtype'])) {

      $typo_item->delete($_POST);

      $values = ['plugin_typology_typologies_id' => $_POST['plugin_typology_typologies_id'],
                            'items_id'      => $_POST['items_id'],
                            'itemtype'      => $_POST['itemtype']];

      PluginTypologyTypology_Item::addLog($values, PluginTypologyTypology_Item::LOG_DELETE);

   } else {
      foreach ($_POST["item"] as $key => $val) {
         if ($val==1) {
            $typo_item->check($key, UPDATE);
            $typo_item->delete(['id'=>$key]);
         }
      }
   }
   Html::back();

} else {
   $typo->checkGlobal(READ);
   Html::header(PluginTypologyTypology::getTypeName(2), '', "tools", "plugintypologymenu");

   $typo->display($_GET);
   Html::footer();
}
