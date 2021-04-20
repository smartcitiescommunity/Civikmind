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

Session::checkLoginUser();

$treeview_url = Plugin::getWebDir('treeview');

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Frameset//EN'
       'http://www.w3.org/TR/html4/frameset.dtd'>";
echo "\n<html><head><title>".sprintf(__('%1$s - %2$s'), "GLPI", __('Tree view', 'treeview'))."</title>";
echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
// Send extra expires header if configured
echo "<meta http-equiv='Expires' content='Fri, Jun 12 1981 08:20:00 GMT'>\n";
echo "<meta http-equiv='Pragma' content='no-cache'>\n";
echo "<meta http-equiv='Cache-Control' content='no-cache'>\n";
echo "<link rel='stylesheet' type='text/css' href='$treeview_url/css/treeview.css' type='text/css'>\n";
echo "<script type='text/javascript' src='$treeview_url/js/treeview.js'>
      </script></head>\n";

echo '<div id="ie5menu" class="skin0" onMouseover="highlightie5(event)" ' .
      'onMouseout="lowlightie5(event)" onClick="jumptoie5(event)" display:none>';
echo '</div>';
echo "<body>";
// Title bar
echo '<div id=explorer_bar>';
echo '<div id=explorer_title>'.sprintf(__('%1$s - %2$s'), "GLPI", __('Tree view', 'treeview'));
echo '</div>';
echo "<div id=explorer_close>";
echo "<img border=0 src='pics/close.png' name='explorer_close'
       onclick='parent.location.href = parent.right.location.href;'></div>";
echo "</div>";

echo "<form method='get' name='get_level' action='" .$_SERVER["PHP_SELF"]. "'>";
// The IDs (primary key) of the requested nodes are stored in this field
echo "<input type='hidden' name='nodes' value=''>";
// Which item type should be opened?
echo "<input type='hidden' name='openedType' value=''>";
echo "</form>";

// Print the tree
$config = new PluginTreeviewConfig();
$config->buildTreeview();

echo "</body>";
echo "</html>";