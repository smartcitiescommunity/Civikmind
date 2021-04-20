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
include ("../../../inc/includes.php");

Session::checkRight("config", UPDATE);

$config = new PluginPurgelogsConfig();
if (isset($_POST["update"])) {
   $config->update($_POST);
   Html::back();
}

Html::header(__("Purge history", "purgelogs"), $_SERVER['PHP_SELF'], "plugins", "config");
$config->showForm();
Html::footer();