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

include('../../../inc/includes.php');

Html::header('Jamf Plugin', '', 'plugins', 'PluginJamfMenu', 'import');

global $CFG_GLPI;

$plugin_dir = Plugin::getWebDir('jamf');
$links = [];
if (Session::haveRight('plugin_jamf_mobiledevice', CREATE)) {
   $links[] = Html::link(_x('menu', 'Import devices', 'jamf'), PluginJamfImport::getSearchURL());
   $links[] = Html::link(_x('menu', 'Merge existing devices', 'jamf'), "{$plugin_dir}/front/merge.php");
}
if (Session::haveRight('config', UPDATE)) {
   $links[] = Html::link(_x('action', 'Configure plugin', 'jamf'), Config::getFormURL()."?forcetab=PluginJamfConfig$1");
}

if (count($links)) {
   echo "<div class='center'><table class='tab_cadre'>";
   echo "<thead><th>"._x('plugin_info', 'Jamf plugin', 'jamf')."</th></thead>";
   echo "<tbody>";
   foreach ($links as $link) {
      echo "<tr><td>{$link}</td></tr>";
   }
   echo "</tbody></table></div>";
} else {
   echo "<div class='center warning' style='width: 40%; margin: auto;'>";
   echo "<i class='fa fa-exclamation-triangle fa-3x'></i>";
   echo "<p>"._x('error', 'You do not have access to any Jamf plugin items', 'jamf')."</p>";
   echo "</div>";
}
Html::footer();