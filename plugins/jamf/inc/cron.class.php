<?php

/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Contains all cron functions for Jamf plugin
 * @since 1.0.0
 */
final class PluginJamfCron extends CommonGLPI
{

   public static function getTypeName($nb = 0)
   {
      return _x('plugin_info', 'Jamf plugin', 'jamf');
   }

   public static function cronSyncJamf(CronTask $task)
   {
      $volume = 0;
      $engines = PluginJamfSync::getDeviceSyncEngines();

      foreach ($engines as $jamf_class => $engine) {
         $v = $engine::syncAll();
         $volume += $v >= 0 ? $v : 0;
      }
      $task->addVolume($volume);

      return 1;
   }

   public static function cronImportJamf(CronTask $task)
   {
      $volume = 0;
      $engines = PluginJamfSync::getDeviceSyncEngines();

      foreach ($engines as $jamf_class => $engine) {
         $v = $engine::discover();
         $volume += $v >= 0 ? $v : 0;
      }
      $task->addVolume($volume);

      return 1;
   }
}