<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include ('../../../inc/includes.php');

if (!isset($_GET['holidays_id'])) {
   exit();
}

if (isset($_GET['holidays_id'])) {
   $hId = $_GET['holidays_id'];
}

Session::checkLoginUser();

$holiday = new PluginActivityHoliday();
$holiday->getFromDB($hId);

if (isset($holiday->fields['id']) &&
   PluginActivityHolidayValidation::canValidate($hId)) {

   $user = new User();
   $user->getFromDB($holiday->fields['users_id']);

   $userName = (isset($user->fields['realname'])?strtoupper($user->fields['realname']):'')." ".(isset($user->fields['firstname'])?$user->fields['firstname']:'');

   $periods = $holiday->getPeriodForTemplate($holiday->fields['actiontime']);

   $dateBegin = date('d-m-y', strtotime($holiday->fields['begin'])).$periods['txt'];

   $strTxtFile = $holiday->generateTXTfile($hId);

   $filename = "DC ".$userName." ".date('Y')." ".$dateBegin.".txt";

   $f = fopen("php://output", 'w');
   fwrite($f, $strTxtFile);
   fclose($f);

   header('Pragma: public');
   header('Expires: 0');
   header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
   header('Cache-Control: private', false); // required for certain browsers
   header("Content-Type: application/octet-stream");

   header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
   header('Content-Transfer-Encoding: binary');

} else {
   exit();
}

exit;
