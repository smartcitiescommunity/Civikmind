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

include ('../../../inc/includes.php');

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");

if (isset($_POST['action'])) {

   switch ($_POST['action']) {
      case "load" :

         $config                = new PluginMoreticketConfig();
         $use_waiting           = $config->useWaiting();
         $use_solution          = $config->useSolution();
         $use_question          = $config->useQuestion();
         $use_urgency           = $config->useUrgency();
         $solution_status       = $config->solutionStatus();
         $urgency_ids           = $config->getUrgency_ids();
         $use_duration_solution = $config->useDurationSolution();

         $params = ['root_doc'        => $CFG_GLPI["root_doc"].PLUGIN_MORETICKET_DIR_NOFULL,
                         'waiting'         => CommonITILObject::WAITING,
                         'closed'          => CommonITILObject::CLOSED,
                         'use_waiting'     => $use_waiting,
                         'use_solution'    => $use_solution,
                         'use_question'    => $use_question,
                         'solution_status' => $solution_status,
                         'glpilayout'      => $_SESSION['glpilayout'],
                         'use_urgency'     => $use_urgency,
                         'urgency_ids'     => $urgency_ids,
                         'div_kb'          => Session::haveRight('knowbase', UPDATE)];

         echo "<script type='text/javascript'>";
         echo "var moreticket = $(document).moreticket(" . json_encode($params) . ");";

         if (Session::haveRight("plugin_moreticket", UPDATE)
            && ($config->useWaiting() == true || $config->useSolution() == true || $config->useQuestion() == true )) {
            if (Session::getCurrentInterface() == "central"
               && (strpos($_SERVER['HTTP_REFERER'], "ticket.form.php") !== false)) {

               echo "moreticket.moreticket_injectWaitingTicket();";
            }
         }

         if (Session::haveRight("plugin_moreticket_justification", READ)) {
            if ((strpos($_SERVER['HTTP_REFERER'], "ticket.form.php") !== false ||
                  strpos($_SERVER['HTTP_REFERER'], "helpdesk.public.php") !== false ||
                   strpos($_SERVER['HTTP_REFERER'], "tracking.injector.php") !== false)
               && ($config->useUrgency() == true)) {
               echo "moreticket.moreticket_urgency();";

            }
         }

         if ((Session::getCurrentInterface() == "central"
              && strpos($_SERVER['HTTP_REFERER'], "ticket.form.php") !== false)
             && $config->useDurationSolution()) {
            echo "moreticket.moreticket_solution();";
         }
         echo "</script>";

         break;
   }
}
