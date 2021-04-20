<?php
/*
 * @version $Id: setup.php 313 2011-12-19 09:39:58Z remi $
 -------------------------------------------------------------------------
 treeview - TreeView browser plugin for GLPI
 Copyright (C) 2003-2012 by the treeview Development Team.

 https://forge.indepnet.net/projects/treeview
 -------------------------------------------------------------------------

 LICENSE

 This file is part of treeview.

 treeview is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 treeview is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with treeview. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../inc/includes.php');

Plugin::load('treeview', true);

$_SESSION["glpi_plugin_treeview_loaded"] = 1;
$config = new PluginTreeviewConfig();
$config->seeTreeview();