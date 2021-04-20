<?php
/**
 * ------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of SCCM plugin.
 *
 * SCCM plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * SCCM plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ------------------------------------------------------------------------
 * @author    François Legastelois <flegastelois@teclib.com>
 * @copyright Copyright (C) 2014-2018 by Teclib' and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * @link      https://pluginsglpi.github.io/sccm/
 * ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginSccmConfig extends CommonDBTM {

   static private $_instance = null;

   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }

   static function canUpdate() {
      return Session::haveRight('config', UPDATE);
   }

   static function canView() {
      return Session::haveRight('config', UPDATE);
   }

   static function getTypeName($nb = 0) {
      return __("Setup - SCCM", "sccm");
   }

   function getName($with_comment = 0) {
      return __("Interface - SCCM", "sccm");
   }

   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }


   function prepareInputForUpdate($input) {
      if (isset($input["sccmdb_password"]) AND !empty($input["sccmdb_password"])) {
         $input["sccmdb_password"] = Toolbox::sodiumEncrypt(stripslashes($input["sccmdb_password"]));
      }

      return $input;
   }

   static function install(Migration $migration) {
      global $DB;

      $table = 'glpi_plugin_sccm_configs';

      if (!$DB->tableExists($table)) {

         $query = "CREATE TABLE `". $table."`(
                     `id` int(11) NOT NULL,
                     `sccmdb_host` VARCHAR(255) NULL,
                     `sccmdb_dbname` VARCHAR(255) NULL,
                     `sccmdb_user` VARCHAR(255) NULL,
                     `sccmdb_password` VARCHAR(255) NULL,
                     `fusioninventory_url` VARCHAR(255) NULL,
                     `active_sync` tinyint(1) NOT NULL default '0',
                     `verify_ssl_cert` tinyint(1) NOT NULL,
                     `use_auth_ntlm` tinyint(1) NOT NULL,
                     `unrestricted_auth` tinyint(1) NOT NULL,
                     `use_auth_info` tinyint(1) NOT NULL,
                     `auth_info` VARCHAR(255) NULL,
                     `is_password_sodium_encrypted` tinyint(1) NOT NULL default '1',
                     `use_lasthwscan` tinyint(1) NOT NULL,
                     `date_mod` timestamp NULL default NULL,
                     `comment` text,
                     PRIMARY KEY  (`id`)
                   ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

         $DB->queryOrDie($query, __("Error when using glpi_plugin_sccm_configs table.", "sccm")
                              . "<br />".$DB->error());

         $sccmdb_password = Toolbox::sodiumEncrypt("");

         $query = "INSERT INTO `$table`
                         (id, date_mod, sccmdb_host, sccmdb_dbname,
                           sccmdb_user, sccmdb_password, fusioninventory_url)
                   VALUES (1, NOW(), 'srv_sccm','bdd_sccm','user_sccm','".$sccmdb_password."',
                           'http://glpi/plugins/fusioninventory/front/communication.php')";

         $DB->queryOrDie($query, __("Error when using glpi_plugin_sccm_configs table.", "sccm")
                                 . "<br />" . $DB->error());

      } else {

         if (!$DB->fieldExists($table, 'verify_ssl_cert')) {
            $migration->addField("glpi_plugin_sccm_configs", "verify_ssl_cert", "tinyint(1) NOT NULL");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'use_auth_ntlm')) {
            $migration->addField("glpi_plugin_sccm_configs", "use_auth_ntlm", "tinyint(1) NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'unrestricted_auth')) {
            $migration->addField("glpi_plugin_sccm_configs", "unrestricted_auth", "tinyint(1) NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'use_auth_info')) {
            $migration->addField("glpi_plugin_sccm_configs", "use_auth_info", "tinyint(1) NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'auth_info')) {
            $migration->addField("glpi_plugin_sccm_configs", "auth_info", "varchar(255)");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'is_password_sodium_encrypted')) {
            $config = self::getInstance();
            if (!empty($config->fields['sccmdb_password'])) {
               $migration->addPostQuery(
                  $DB->buildUpdate(
                     'glpi_plugin_sccm_configs',
                     [
                        'sccmdb_password' => Toolbox::sodiumEncrypt(Toolbox::decrypt($config->getField('sccmdb_password'), GLPIKEY))
                     ],
                     [
                        'id' => 1,
                     ]
                     )
                  );
            }
            $migration->addField("glpi_plugin_sccm_configs", "is_password_sodium_encrypted", "tinyint(1) NOT NULL default '1'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }

         if (!$DB->fieldExists($table, 'use_lasthwscan')) {
            $migration->addField("glpi_plugin_sccm_configs", "use_lasthwscan", "tinyint(1) NOT NULL default '0'");
            $migration->migrationOneTable('glpi_plugin_sccm_configs');
         }
      }

      return true;
   }


   static function uninstall() {
      global $DB;

      if ($DB->tableExists('glpi_plugin_sccm_configs')) {

         $query = "DROP TABLE `glpi_plugin_sccm_configs`";
         $DB->queryOrDie($query, $DB->error());
      }
      return true;
   }


   static function showConfigForm($item) {
      $config = self::getInstance();

      $config->showFormHeader();

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Enable SCCM synchronization", "sccm")."</td><td>";
      Dropdown::showYesNo("active_sync", $config->getField('active_sync'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Server hostname (MSSQL)", "sccm")."</td><td>";
      echo Html::input('sccmdb_host', ['value' => $config->getField('sccmdb_host')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Database name", "sccm")."</td><td>";
      echo Html::input('sccmdb_dbname', ['value' => $config->getField('sccmdb_dbname')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Username", "sccm")."</td><td>";
      echo Html::input('sccmdb_user', ['value' => $config->getField('sccmdb_user')]);
      echo "</td></tr>\n";

      $password = $config->getField('sccmdb_password');
      $password = Toolbox::sodiumDecrypt($password);
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Password", "sccm")."</td><td>";
      echo "<input type='password' name='sccmdb_password' value='$password' autocomplete='off'>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("URL FusionInventory for injection", "sccm")."</td><td>";
      echo Html::input('fusioninventory_url', ['value' => $config->getField('fusioninventory_url')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Verify SSL certificate", "sccm")."</td><td>";
      Dropdown::showYesNo("verify_ssl_cert", $config->getField('verify_ssl_cert'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use NLTM authentication", "sccm")."</td><td>";
      Dropdown::showYesNo("use_auth_ntlm", $config->getField('use_auth_ntlm'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Send credentials to other hosts too", "sccm")."</td><td>";
      Dropdown::showYesNo("unrestricted_auth", $config->getField('unrestricted_auth'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use specific authentication information", "sccm")."</td><td>";
      Dropdown::showYesNo("use_auth_info", $config->getField('use_auth_info'));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Value for spécific authentication", "sccm")."</td><td>";
      echo Html::input('auth_info', ['value' => $config->getField('auth_info')]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use LastHWScan as FusionInventory last inventory", "sccm")."</td><td>";
      Dropdown::showYesNo("use_lasthwscan", $config->getField('use_lasthwscan'));
      echo "</td></tr>\n";

      $config->showFormButtons(['candel'=>false]);

      return false;
   }

}
