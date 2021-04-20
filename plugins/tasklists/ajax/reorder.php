<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Tasklists plugin for GLPI
 Copyright (C) 2003-2016 by the Tasklists Development Team.

 https://github.com/InfotelGLPI/tasklists
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Tasklists.

 Tasklists is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Tasklists is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Tasklists. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include("../../../inc/includes.php");

Session::checkLoginUser();

$dbu   = new DbUtils();
$table = $dbu->getTableForItemType('PluginTasklistsStateOrder');

// Récupération de l'ID du champ à modifier
$query = "SELECT id FROM $table
            WHERE `plugin_tasklists_tasktypes_id` = {$_POST['plugin_tasklists_tasktypes_id']}
               AND `ranking` = {$_POST['old_order']}";

$result = $DB->queryOrDie($query, 'Error');
//$result  = $DB->query($query);
$first   = $result->fetchAssoc();
$id_item = $first['id'];

// Réorganisation de tout les champs
if ($_POST['old_order'] < $_POST['new_order']) {

   $DB->query("UPDATE $table SET
               `ranking` = `ranking`-1
               WHERE `plugin_tasklists_tasktypes_id` = {$_POST['plugin_tasklists_tasktypes_id']}
               AND `ranking` > {$_POST['old_order']}
               AND `ranking` <= {$_POST['new_order']}");
} else {

   $DB->query("UPDATE $table SET
               `ranking` = `ranking`+1
               WHERE `plugin_tasklists_tasktypes_id` = {$_POST['plugin_tasklists_tasktypes_id']}
               AND `ranking` < {$_POST['old_order']}
               AND `ranking` >= {$_POST['new_order']}");
}

if (isset($id_item) && $id_item > 0) {
   $DB->query("UPDATE $table SET
               `ranking` = {$_POST['new_order']}
               WHERE id = $id_item");
}