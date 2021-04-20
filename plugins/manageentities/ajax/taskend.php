<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

$AJAX_INCLUDE = 1;
include ('../../../inc/includes.php');

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_POST['duration']) && ($_POST['duration'] == 0)
   && isset($_POST['name'])) {
   if (!isset($_POST['global_begin'])) {
      $_POST['global_begin'] = '';
   }
   if (!isset($_POST['global_end'])) {
      $_POST['global_end'] = '';
   }
   Html::showDateTimeField($_POST['name'], [
      'timestep'   => -1,
      'maybeempty' => false,
      'canedit'    => true,
      'mindate'    => '',
      'maxdate'    => '',
      'mintime'    => $_POST['global_begin'],
      'maxtime'    => $_POST['global_end']]);
}
