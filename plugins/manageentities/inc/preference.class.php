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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * class plugin_manageentities_preference
 * Load and store the preference configuration from the database
 */
class PluginManageentitiesPreference extends CommonDBTM {

   static function checkIfPreferenceExists($users_id) {
      global $DB;

      $result = $DB->query("SELECT `id`
                FROM `glpi_plugin_manageentities_preferences`
                WHERE `users_id` = '" . $users_id . "' ");
      if ($DB->numrows($result) > 0)
         return $DB->result($result, 0, "id");
      else
         return 0;
   }

   static function addDefaultPreference($users_id) {

      $self                  = new self();
      $input["users_id"]     = $users_id;
      $input["show_on_load"] = 0;

      return $self->add($input);
   }

   static function checkPreferenceValue($users_id) {
      global $DB;

      $result = $DB->query("SELECT *
                FROM `glpi_plugin_manageentities_preferences`
                WHERE `users_id` = '" . $users_id . "' ");
      if ($DB->numrows($result) > 0)
         return $DB->result($result, 0, "show_on_load");
      else
         return 0;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Preference') {
         return __('Entities portal', 'manageentities');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if (get_class($item) == 'Preference') {
         $pref_ID = self::checkIfPreferenceExists(Session::getLoginUserID());
         if (!$pref_ID)
            $pref_ID = self::addDefaultPreference(Session::getLoginUserID());

         self::showForm($CFG_GLPI['root_doc'] . "/plugins/manageentities/front/preference.form.php", $pref_ID, Session::getLoginUserID());
      }
      return true;
   }

   static function showForm($target, $ID, $user_id) {
      global $DB;

      $data = plugin_version_manageentities();
      $self = new self();
      $self->getFromDB($ID);
      echo "<form action='" . $target . "' method='post'>";
      echo "<div align='center'>";

      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='2'>" . $data['name'] . " - " . $data['version'] . "</th></tr>";

      echo "<tr class='tab_bg_1 center'><td>" . __('Launch the plugin Entities portal with GLPI launching', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("show_on_load", $self->fields["show_on_load"]);
      echo "</td></tr>";

      $contractstate  = new PluginManageentitiesContractState();
      $contractstates = $contractstate->find();
      $states         = [];
      foreach ($contractstates as $key => $val) {
         $states[$key] = $val['name'];
      }
      echo "<tr class='tab_bg_1 center'><td>" . __('Status list contract for the general monitoring', 'manageentities') . "</td>";
      echo "<td>";
      if ($self->fields["contract_states"] == NULL) {
         Dropdown::showFromArray("contract_states", $states, ['multiple' => true,
                                                              'width'    => 200,
                                                              'value'    => $self->fields["contract_states"]]);
      } else {
         Dropdown::showFromArray("contract_states", $states, ['multiple' => true,
                                                              'width'    => 200,
                                                              'values'   => json_decode($self->fields["contract_states"], true)]);
      }
      echo "</td></tr>";


      $query = "SELECT  `glpi_users`.*, `glpi_plugin_manageentities_businesscontacts`.`id` as users_id
               FROM `glpi_plugin_manageentities_businesscontacts`, `glpi_users`
               WHERE `glpi_plugin_manageentities_businesscontacts`.`users_id`=`glpi_users`.`id`
               GROUP BY `glpi_plugin_manageentities_businesscontacts`.`users_id`";

      $result = $DB->query($query);
      $users  = [];
      while ($data = $DB->fetchAssoc($result)) {
         $users[$data['id']] = $data['realname'] . " " . $data['firstname'];
      }
      echo "<tr class='tab_bg_1 center'><td>" . __('Default list of Business for the general monitoring', 'manageentities') . "</td>";
      echo "<td>";
      if ($self->fields["business_id"] == NULL) {
         Dropdown::showFromArray("business_id", $users, ['multiple' => true,
                                                         'width'    => 200,
                                                         'value'    => $self->fields["business_id"]]);
      } else {
         Dropdown::showFromArray("business_id", $users, ['multiple' => true,
                                                         'width'    => 200,
                                                         'values'   => json_decode($self->fields["business_id"], true)]);
      }
      echo "</td></tr>";
      echo "<tr class='tab_bg_1 center'><td>" . __('Default list of companies for the general monitoring', 'manageentities') . "</td>";
      echo "<td>";
      $plugin_company = new PluginManageentitiesCompany();
      $result         = $plugin_company->find();

      $company = [];
      foreach ($result as $data) {
         $company[$data['id']] = $data['name'];
      }
      if ($self->fields['companies_id'] == NULL) {
         Dropdown::showFromArray("companies_id", $company, ['multiple' => true,
                                                            'width'    => 200,
                                                            'value'    => $self->fields["companies_id"]]);
      } else {
         Dropdown::showFromArray("companies_id", $company, ['multiple' => true,
                                                            'width'    => 200,
                                                            'values'   => json_decode($self->fields["companies_id"], true)]);
      }
      echo "</td></tr>";


      echo "<tr class='tab_bg_1 center'><td colspan='2'>";
      echo "<input type='submit' name='update_user_preferences_manageentities' value='" . _sx('button', 'Post') . "' class='submit'>";
      echo "<input type='hidden' name='id' value='" . $ID . "'>";
      echo "</td></tr>";
      echo "<tr class='tab_bg_1 center'><td colspan='2'>";
      echo __('Warning : If there are more than one plugin which be loaded at startup, then only the first will be used', 'manageentities');
      echo "</td></tr>";
      echo "</table>";

      echo "</div>";
      Html::closeForm();

   }

   function prepareInputForUpdate($input) {
      if (isset($input['contract_states'])) {
         $input['contract_states'] = json_encode($input['contract_states']);
      } else {
         $input['contract_states'] = 'NULL';
      }
      if (isset($input['business_id'])) {
         $input['business_id'] = json_encode($input['business_id']);
      } else {
         $input['business_id'] = 'NULL';
      }
      if (isset($input['companies_id'])) {
         $input['companies_id'] = json_encode($input['companies_id']);
      } else {
         $input['companies_id'] = 'NULL';
      }
      return $input;
   }
}