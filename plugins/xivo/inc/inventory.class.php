<?php
/*
 -------------------------------------------------------------------------
 xivo plugin for GLPI
 Copyright (C) 2017 by the xivo Development Team.

 https://github.com/pluginsGLPI/xivo
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivo.

 xivo is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 xivo is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with xivo. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginXivoInventory extends CommonGLPI {
   const NOT_CONFIGURED = 'not_configured';

   static function cronInfo($name) {
      switch ($name) {
         case 'xivoimport' :
            return ['description' => __('Import Xivo assets', 'xivo')];
      }

      return [];
   }

   /**
    * Execute a full inventory synchronisation
    * Import from xivo api:
    *    - Phones
    *    - Lines
    *    - Association with users
    *
    * @param  CronTask $crontask
    * @return boolean
    */
   static function cronXivoimport(CronTask $crontask) {
      $xivoconfig    = PluginXivoConfig::getConfig();
      $apiclient     = new PluginXivoAPIClient;
      $totaldevices  = 0;
      $totallines    = 0;
      $phone_lines   = [];

      // check if api config is valid
      if (!PluginXivoConfig::isValid(true)) {
         return false;
      }

      // check if import asset is enabled
      if (!$xivoconfig['import_assets']) {
         return true;
      }

      // track execution time
      $time_start = microtime(true);

      // retrieve phones
      $phones = [];
      if ($xivoconfig['import_phones']) {
         $phones = $apiclient->paginate('Devices');
      }

      // retrieve lines
      $lines = [];
      if ($xivoconfig['import_lines']) {
         $lines = $apiclient->paginate('Lines');
      }

      // retrieve users
      $users = $apiclient->paginate('Users');

      // build an association between call_id (present in lines) and username (ldap)
      $caller_id_list = [];
      foreach ($users as $user) {
         if (!empty($user['username'])) {
            $caller_id_name = trim($user['caller_id'], '"');
            $caller_id_list[$caller_id_name] = $user['username'];
         }
      }

      // import lines
      foreach ($lines as &$line) {
         //check if we can retrive the ldap username
         $line['glpi_users_id'] = 0;
         if (isset($caller_id_list[$line['caller_id_name']])) {
            $line['glpi_users_id'] = User::getIdByName($caller_id_list[$line['caller_id_name']]);
         }

         // add or update assets
         $lines_id         = PluginXivoLine::importSingle($line);
         $line['lines_id'] = $lines_id;
         $totallines       += (int) (bool) $lines_id;
      }

      if ($totallines) {
         $crontask->log(sprintf(_n('%1$d line imported',
                                   '%1$d lines imported',
                                    $totallines, 'xivo')."\n",
                                $totallines));
      }

      foreach ($phones as $index => &$phone) {
         // remove phones with missing mandatory informations
         if (!$xivoconfig['import_empty_sn']
             && empty($phone['sn'])) {
            unset($phones[$index]);
            continue;
         }
         if (!$xivoconfig['import_empty_mac']
             && empty($phone['mac'])) {
            unset($phones[$index]);
            continue;
         }
         if (!$xivoconfig['import_notconfig']
             && $phone['status'] == self::NOT_CONFIGURED) {
            unset($phones[$index]);
            continue;
         }

         // find possible lines for this device
         $phone['lines'] = [];
         if ($xivoconfig['import_phonelines']) {
            foreach ($lines as $line) {
               if ($line['device_id'] == $phone['id']) {
                  $phone['lines'][] = $line;
               }
            }
         }

         // add or update assets
         $phones_id     = PluginXivoPhone::importSingle($phone);
         $totaldevices += (int) (bool) $phones_id;
      }

      if ($totaldevices) {
         $crontask->log(sprintf(_n('%1$d phone imported',
                                   '%1$d phones imported',
                                    $totaldevices, 'xivo')."\n",
                                $totaldevices));
      }
      $totalimported = $totaldevices + $totallines;

      // end track of execution time
      $time_end = microtime(true);
      $totaltime = $time_end - $time_start;
      if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
         Toolbox::logDebug("XIVO import (time + number)", round($totaltime, 2), $totalimported);
      }

      $crontask->setVolume($totalimported);
      if ($totalimported) {
         return true;
      } else {
         return false;
      }
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   static function install(Migration $migration) {
      CronTask::register(__CLASS__,
                         'xivoimport',
                         12 * HOUR_TIMESTAMP,
                         [
                           'comment'   => 'Import assets from xivo-confd api',
                           'mode'      => CronTask::MODE_EXTERNAL
                         ]);

      return true;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   static function uninstall() {
      return true;
   }
}