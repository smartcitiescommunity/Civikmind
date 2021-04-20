<?php
/*
* @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
LICENSE

This file is part of the purgelogs plugin.

Purgelogs plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Purgelogs plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with datainjection. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
 @package   purgelogs
 @author    TECLIB
 @copyright Copyright (c) 2009-2017 purgelogs plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/purgelogs
 @link      http://www.glpi-project.org/
 @link      http://www.teclib-edition.com/
 @since     2009
 ---------------------------------------------------------------------- */

function plugin_purgelogs_install() {
   include (GLPI_ROOT."/plugins/purgelogs/inc/config.class.php");
   include (GLPI_ROOT."/plugins/purgelogs/inc/purge.class.php");
   $migration = new Migration("0.85");
   PluginPurgelogsConfig::install($migration);
   PluginPurgelogsPurge::install($migration);
   return true;
}

function plugin_purgelogs_uninstall() {
   include (GLPI_ROOT."/plugins/purgelogs/inc/config.class.php");
   include (GLPI_ROOT."/plugins/purgelogs/inc/purge.class.php");
   PluginPurgelogsConfig::uninstall();
   PluginPurgelogsPurge::uninstall();
   return true;
}
