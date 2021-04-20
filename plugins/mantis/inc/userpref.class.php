<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMantisUserpref extends CommonDBTM {

   /**
    * Install this class in GLPI
    *
    *
    */
   static function install($migration) {
      global $DB;

      if (!$DB->tableExists("glpi_plugin_mantis_userprefs")) {
         $query = "CREATE TABLE `glpi_plugin_mantis_userprefs` (
               `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
               `users_id` int(11) NOT NULL ,
               `followTask` int(11) NOT NULL default '0',
               `followFollow` int(11) NOT NULL default '0',
               `followAttachment` int(11) NOT NULL default '0',
               `followTitle` int(11) NOT NULL default '0',
               `followDescription` int(11) NOT NULL default '0',
               `followCategorie` int(11) NOT NULL default '0',
               `followLinkedItem` int(11) NOT NULL default '0',
               UNIQUE KEY (`users_id`))";
         $DB->query($query) or die($DB->error());
      }
   }

   /**
    * Define tab name
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (in_array($item->getType(), [
            'User',
            'Preference'
      ])) {
         return __("MantisBT", "mantis");
      }
      return '';
   }

   static function getTypeName($nb = 0) {
      return __("MantisBT", "mantis");
   }

   static function canCreate() {
      return Session::haveRight('ticket', CREATE);
   }

   static function canView() {
      return Session::haveRight('ticket', READ);
   }

   /**
    * Define tab content
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'User') {
         $ID = $item->getField('id');
      } else if ($item->getType() == 'Preference') {
         $ID = Session::getLoginUserID();
      }

      $self = new self();
      $self->showForm($ID);

      return true;
   }

   /**
    * Function to show the form of plugin
    *
    * @param $item
    */
   public function showForm($ID, $options = []) {
      if (! $this->getFromDB($ID)) {
         $this->fields['users_id'] = $ID;
         $this->fields['id'] = $ID;
         $this->add($this->fields);
         $this->updateInDB($this->fields);
      }

      $target = $this->getFormURL();
      if (isset($options['target'])) {
         $target = $options['target'];
      }

      echo "<form method='post' action='" . $target . "' method='post'>";
      echo "<table id='table2' class='tab_cadre_fixe' cellpadding='2'>";
      echo "<tr class='headerRow'><th colspan='2'>" . __("Default checkbox status", "mantis") . "</th></tr>";

      $checked = ($this->fields['followAttachment']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Document') . "</th>";
      echo "<td><input type='checkbox' name='followAttachment' id='followAttachment' " . $checked . ">"
              . __("Forward document(s)", "mantis")
              . "<div id='attachmentforLinkToProject' ><div/></td></tr>";

      $checked = ($this->fields['followFollow']) ? "checked" : "";
      echo "<tr class='tab_bg_1' >";
      echo "<th>" . __('Ticket followup') . "</th>";
      echo "<td><input type='checkbox' name='followFollow' id='followFollow' " . $checked . ">"
              . __("Forward ticket followup", "mantis") . "</td></tr>";

      $checked = ($this->fields['followTask']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Ticket tasks') . "</th>";
      echo "<td><input type='checkbox' name='followTask' id='followTask' " . $checked . ">"
              . __("Forward ticket tasks", "mantis") . "</td></tr>";

      $checked = ($this->fields['followTitle']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Title') . "</th>";
      echo "<td><input type='checkbox' name='followTitle' id='followTitle' " . $checked . ">"
              . __("Forward title", "mantis") . "</td></tr>";

      $checked = ($this->fields['followDescription']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Description') . "</th>";
      echo "<td><input type='checkbox' name='followDescription' id='followDescription' " . $checked . ">"
              . __("Forward description", "mantis") . "</td></tr>";

      $checked = ($this->fields['followCategorie']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Category') . "</th>";
      echo "<td><input type='checkbox' name='followCategorie' id='followCategorie' " . $checked . ">"
              . __("Forward category", "mantis") . "</td></tr>";

      $checked = ($this->fields['followLinkedItem']) ? "checked" : "";
      echo "<tr class='tab_bg_1' >";
      echo "<th>" . _n('Linked ticket', 'Linked tickets', 2) . "</th>";
      echo "<td><input type='checkbox' name='followLinkedItem' id='followLinkedItem' " . $checked . ">"
              . __("Forward linked tickets", "mantis") . "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td><input id='update' type='submit' name='update' value='" . __('Update') . "' class='submit'></td><td></td></tr>";
      echo "<input type='hidden' name='id' value=" . $this->fields["id"] . ">";
      echo "<input type='hidden' name='users_id' value=" . $this->fields["users_id"] . ">";

      echo "</table>";
      Html::closeForm();
   }

   /**
    * Uninstall Cron Task from BDD
    */
   static function uninstall(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);

      if ($DB->tableExists($table)) {
         $migration->dropTable($table);
         $migration->executeMigration();
      }
   }
}
