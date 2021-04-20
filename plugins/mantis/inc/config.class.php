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

class PluginMantisConfig extends CommonDBTM {

   static $rightname = 'config';

   /**
    * Display name of itemtype
    *
    * @return value name of this itemtype
    **/
   static function getTypeName($nb = 0) {

      return __('Setup - MantisBT', 'mantis');
   }

   /**
    * Prepare input data for updating the item
    *
    * @param $input data used to update the item
    *
    * @return the modified $input array
   **/
   function prepareInputForUpdate($input) {

      if (isset($input["pwd"]) AND !empty($input["pwd"])) {
         $input["pwd"] = Toolbox::sodiumEncrypt(stripslashes($input["pwd"]));
      }
      return $input;
   }

   /**
    * Print the config form
    *
    * @param $ID        Integer : ID of the item
    * @param $options   array
    *
    * @return Nothing (display)
   **/
   function showForm($ID, $options = []) {

      global $CFG_GLPI;

      $options['candel'] = false;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("MantisBT server base URL", "mantis") . "</td>";
      echo "<td><input id='host' name='host' type='text' size='70'
                     value='" . $this->fields["host"] . "'/></td>";
      echo "</tr><tr class='tab_bg_1'>";
      echo "<td></td><td>ex : http(s)://localhost/mantisbt</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Check SSL", "mantis") . "</td>";
      echo "<td>";
      Dropdown::showYesNo('check_ssl', $this->fields['check_ssl']);
      echo "</tr><tr class='tab_bg_1'>";
      echo "<td></td><td></td>";
      echo "</tr>";

      if (!empty($CFG_GLPI['proxy_name'])) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __("Use GLPi proxy configuration", "mantis") . "</td>";
         echo "<td>";
         Dropdown::showYesNo('use_proxy', $this->fields['use_proxy']);
         echo "</tr><tr class='tab_bg_1'>";
         echo "<td></td><td></td>";
         echo "</tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Wsdl file path", "mantis") . "</td>";
      echo "<td><input id='url' name='url' type='text' size='70'
                     value='" . $this->fields["url"] . "'/></td>";
      echo "</tr><tr class='tab_bg_1'>";
      echo "<td></td><td>ex : api/soap/mantisconnect.php?wsdl</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("MantisBT user login", "mantis") . "</td>";
      echo "<td><input  id='login' name='login' type='text' size='30'
                  value='" . $this->fields["login"] . "'/></td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("MantisBT user password", "mantis") . "</td>";
      echo "<td><input id='pwd' name='pwd' type='password' size='30'
                  value='" . Toolbox::sodiumDecrypt($this->fields["pwd"]) . "' /></td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Allow assignation", "mantis") . "</td>";
      echo "<td>";
      Dropdown::showYesNo("enable_assign", $this->fields["enable_assign"]);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Neutralize the escalation to MantisBT when the status of the GLPi object is", "mantis") . "</td>";
      echo "<td>";
      $p['name']        = 'neutralize_escalation';
      $p['showtype']    = 'normal';
      $p['value']       = $this->fields["neutralize_escalation"];
      Ticket::dropdownStatus($p);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Status of GLPi object after escalation to MantisBT", "mantis") . "</td>";
      echo "<td>";
      $p['name']        = 'status_after_escalation';
      $p['showtype']    = 'normal';
      $p['value']       = $this->fields["status_after_escalation"];
      Ticket::dropdownStatus($p);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Show option 'Delete the MantisBT issue' ", "mantis") . "</td>";
      echo "<td>";
      Dropdown::showYesNo('show_option_delete', $this->fields["show_option_delete"]);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Attachment type transfered to MantisBT", "mantis") . "</td>";
      echo "<td>";
      DocumentCategory::dropdown([
            'value'     => $this->fields["doc_categorie"],
            'name'      => 'doc_categorie'
      ]);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("MantisBT field for GLPI fields", "mantis") . "</td>";
      echo "<td>";
      DropDown::showFromArray('champsGlpi', PluginMantisIssue::$champsMantis,
                              ['value' => $this->fields["champsGlpi"]]
      );
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("MantisBT field for the link URL to the GLPi object", "mantis") . "</td>";
      echo "<td>";
      DropDown::showFromArray('champsUrlGlpi', PluginMantisIssue::$champsMantis,
                              ['value' => $this->fields["champsUrlGlpi"]]);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Close GLPi ticket when MantisBT issue status is", "mantis") . "</td>";
      echo "<td>";
      DropDown::showFromArray('etatMantis', PluginMantisIssue::$state_mantis,
                              ['value' => $this->fields["etatMantis"]]);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Solution type when MantisBT issue is resolved", "mantis") . "</td>";
      echo "<td>";
      SolutionType::dropdown(['value'  => $this->fields['solutiontypes_id'],
                                      'rand'   => mt_rand(),
                                      'entity' => -1]);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("GLPi user who solves ticket ?", "mantis") . "</td>";
      echo "<td>";
      User::dropdown(['name'   => 'users_id',
                                 'value'  => $this->fields["users_id"],
                                 'entity' => -1,
                                 'right'  => 'all']);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td><input id='test' onclick='testConnexionMantisWS();'
               value='" . __("Test the connection", "mantis") . "' class='submit'></td>";
      echo "<td><div id='infoAjax'></div></td>";
      echo "</tr>";

      $this->showFormButtons($options);
   }

   /**
    * Install all necessary tables for the plugin
    *
    * @return boolean True if success
    */
   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);

      if (!$DB->tableExists($table)) {
         $query = "CREATE TABLE `".$table."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `host` varchar(255) NOT NULL default '',
                     `url` varchar(255) NOT NULL default '',
                     `login` varchar(255) NOT NULL default '',
                     `pwd` varchar(255) NOT NULL default '',
                     `champsUrlGlpi` varchar(100) NOT NULL default '',
                     `champsGlpi` varchar(100) NOT NULL default '',
                     `enable_assign` int(3) NOT NULL default 0,
                     `neutralize_escalation` int(3) NOT NULL default 0,
                     `status_after_escalation` int(3) NOT NULL default 0,
                     `show_option_delete` int(3) NOT NULL default 0,
                     `doc_categorie` int(3) NOT NULL default 0,
                     `itemType` varchar(255) NOT NULL default '',
                     `etatMantis` varchar(100) NOT NULL default '',
                     `solutiontypes_id` int(11) NOT NULL DEFAULT 0,
                     `users_id` int(11) NOT NULL DEFAULT 0,
                     `check_ssl` int(1) NOT NULL DEFAULT 0,
                     `use_proxy` int(1) NOT NULL DEFAULT 0,
                     `is_password_sodium_encrypted` int(1) NOT NULL DEFAULT 1,
                     PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());

         $query = "INSERT INTO `$table` (id) VALUES (1)";
         $DB->query($query) or die ($DB->error());
      } else {

         if ($DB->fieldExists($table, 'version')) {
            $migration->dropField($table, 'version');
         }

         if (!$DB->fieldExists($table, 'solutiontypes_id')) {
            $migration->addField($table, "solutiontypes_id", "INT( 11 ) NOT NULL DEFAULT 0");
         }

         if (!$DB->fieldExists($table, 'users_id')) {
            $migration->addField($table, "users_id", "INT( 11 ) NOT NULL DEFAULT 0");
         }

         if (!$DB->fieldExists($table, 'check_ssl')) {
            $migration->addField($table, "check_ssl", "INT( 1 ) NOT NULL DEFAULT 0");
         }

         if (!$DB->fieldExists($table, 'use_proxy')) {
            $migration->addField($table, "use_proxy", "INT( 1 ) NOT NULL DEFAULT 0");
         }

         if (!$DB->fieldExists($table, 'is_password_sodium_encrypted')) {
            $config = new self();
            $config->getFromDB(1);
            if (!empty($config->fields['pwd'])) {
               $migration->addPostQuery(
                  $DB->buildUpdate(
                     'glpi_plugin_mantis_configs',
                     [
                        'pwd' => Toolbox::sodiumEncrypt(Toolbox::decrypt($config->fields['pwd']))
                     ],
                     [
                        'id' => 1,
                     ]
                  )
               );
            }
            $migration->addField($table, "is_password_sodium_encrypted", "INT(1) NOT NULL DEFAULT 1");
         }
      }

      $migration->executeMigration();
   }

   /**
    * Uninstall previously installed table of the plugin
    *
    * @return boolean True if success
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