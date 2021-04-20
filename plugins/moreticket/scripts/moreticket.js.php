<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 moreticket plugin for GLPI
 Copyright (C) 2013-2016 by the moreticket Development Team.

 https://github.com/InfotelGLPI/moreticket
 -------------------------------------------------------------------------

 LICENSE

 This file is part of moreticket.

 moreticket is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 moreticket is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with moreticket. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

//change mimetype
header("Content-type: application/javascript");

//not executed in self-service interface & right verification
if (Session::getCurrentInterface() == "central") {

   $config = new PluginMoreticketConfig();
   $use_waiting = $config->useWaiting();
   $use_solution = $config->useSolution();
   $use_question = $config->useQuestion();
   $solution_status = $config->solutionStatus();

   $params = ['root_doc' => $CFG_GLPI["root_doc"].PLUGIN_MORETICKET_DIR_NOFULL,
      'waiting' => CommonITILObject::WAITING,
      'closed' => CommonITILObject::CLOSED,
      'use_waiting' => $use_waiting,
      'use_solution' => $use_solution,
      'use_question' => $use_question,
      'solution_status' => $solution_status];

   echo "moreticket(" . json_encode($params) . ");";
}
