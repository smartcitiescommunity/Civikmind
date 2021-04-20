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

include ('../../../inc/includes.php');
Html::header_nocache();

Session::checkLoginUser();

global $DB;

// Get AJAX input and load it into $_REQUEST
$input = file_get_contents('php://input');
parse_str($input, $_REQUEST);

// An action must be specified
if (!isset($_REQUEST['crontask'])) {
   throw new \RuntimeException('Required argument missing!');
}

$accepted_tasks = ['importJamf', 'syncJamf'];
if (!in_array($_REQUEST['crontask'], $accepted_tasks)) {
   throw new \RuntimeException('Unacceptable cron task!');
}
CronTask::launch(-CronTask::MODE_EXTERNAL, 1, $_REQUEST['crontask']);