<?php

/*
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2019 by the Metademands Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

/**
 * Update from 2.6.4 to 2.7.1
 * Glpi upgrade to 9.5
 * @return bool for success (will die for most error)
 * */

ini_set("memory_limit", "-1");
ini_set("max_execution_time", 0);
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', realpath('../../..'));
}

include_once(GLPI_ROOT . "/inc/autoload.function.php");
include_once(GLPI_ROOT . "/inc/db.function.php");
include_once(GLPI_ROOT . "/inc/based_config.php");
include_once(GLPI_CONFIG_DIR . "/config_db.php");
include_once(GLPI_ROOT . "/inc/define.php");

$GLPI = new GLPI();
$GLPI->initLogger();
Config::detectRootDoc();

if (is_writable(GLPI_SESSION_DIR)) {
   Session::setPath();
} else {
   die("Can't write in " . GLPI_SESSION_DIR . "\r\n");
}
Session::start();
$_SESSION['glpi_use_mode'] = 0;
Session::loadLanguage();

global $DB;
if (!$DB->connected) {
   die("No DB connection\r\n");
}
$CFG_GLPI['notifications_ajax']    = 0;
$CFG_GLPI['notifications_mailing'] = 0;
$CFG_GLPI['use_notifications']     = 0;

function migrateAllExistingMetademandsWithNewStatus() {
   global $DB;
   $dbu = new DbUtils();

   $ticket_metademand = new PluginMetademandsTicket_Metademand();

   //Migrate existing metademands status for mini-dashboards
   migrateAllRunningAndToBeClosedMetademands($DB, $dbu, $ticket_metademand);
   migrateAllClosedMetademands($DB, $dbu, $ticket_metademand);
}


function migrateAllRunningAndToBeClosedMetademands($DB, $dbu, $ticket_metademand) {
   $get_running_parents_tickets_meta = "SELECT `glpi_plugin_metademands_tickets_metademands`.`parent_tickets_id` as 'ticket_id' FROM `glpi_plugin_metademands_tickets_metademands`
                        LEFT JOIN `glpi_tickets` ON `glpi_tickets`.`id` =  `glpi_plugin_metademands_tickets_metademands`.`tickets_id` WHERE
                            `glpi_tickets`.`status` NOT IN ('" . Ticket::CLOSED . "', '" . Ticket::SOLVED . "') 
                                 AND `glpi_tickets`.`is_deleted` = 0 ";

   $results_running_parents = $DB->query($get_running_parents_tickets_meta);

   $running_parents_meta = [];
   while ($row = $DB->fetchArray($results_running_parents)) {
      $running_parents_meta[$row['ticket_id']] = $row['ticket_id'];
   }


   if (count($running_parents_meta) > 0) {

      foreach ($running_parents_meta as $running_parent) {

         $ticket_metademand->getFromDBByCrit(['parent_tickets_id' => $running_parent]);

         $get_running_sons_ticket = getSonsQuery($running_parent);

         $results_sons_ticket = $DB->query($get_running_sons_ticket);

         $counterClosed = 0;

         if ($results_sons_ticket->num_rows != 0) {

            while ($row = $DB->fetchArray($results_sons_ticket)) {
               if ($row['status'] == Ticket::CLOSED || $row['status'] == Ticket::SOLVED) {
                  $counterClosed++;
               }
            }

            if ($counterClosed == $results_sons_ticket->num_rows) {
               $ticket_metademand->update(['id' => $ticket_metademand->getID(), 'status' => PluginMetademandsTicket_Metademand::TO_CLOSED]);
            } elseif ($counterClosed < $results_sons_ticket->num_rows) {
               $ticket_metademand->update(['id' => $ticket_metademand->getID(), 'status' => PluginMetademandsTicket_Metademand::RUNNING]);
            }
         } else {
            $ticket_metademand->update(['id' => $ticket_metademand->getID(), 'status' => PluginMetademandsTicket_Metademand::RUNNING]);
         }
      }
   }
}

function migrateAllClosedMetademands($DB, $dbu, $ticket_metademand) {
   $get_closed_meta = "SELECT `glpi_plugin_metademands_tickets_metademands`.`parent_tickets_id` as 'ticket_id' FROM `glpi_plugin_metademands_tickets_metademands`
                        LEFT JOIN `glpi_tickets` ON `glpi_tickets`.`id` =  `glpi_plugin_metademands_tickets_metademands`.`tickets_id` WHERE
                            `glpi_tickets`.`status` IN ('" . Ticket::CLOSED . "', '" . Ticket::SOLVED . "') 
                                 AND `glpi_tickets`.`is_deleted` = 0 ";

   $results_closed_parents = $DB->query($get_closed_meta);

   $closed_parents_meta = [];
   while ($row = $DB->fetchArray($results_closed_parents)) {
      $closed_parents_meta[$row['ticket_id']] = $row['ticket_id'];
   }


   if (count($closed_parents_meta) > 0) {

      foreach ($closed_parents_meta as $closed_parent) {

         $ticket_metademand->getFromDBByCrit(['parent_tickets_id' => $closed_parent]);

         $get_closed_sons_ticket = getSonsQuery($closed_parent);

         $results_sons_ticket = $DB->query($get_closed_sons_ticket);

         $counterClosed = 0;

         if ($results_sons_ticket->num_rows != 0) {

            while ($row = $DB->fetchArray($results_sons_ticket)) {
               if ($row['status'] == Ticket::CLOSED || $row['status'] == Ticket::SOLVED) {
                  $counterClosed++;
               }
            }


            if ($counterClosed == $results_sons_ticket->num_rows) {
               $ticket_metademand->update(['id' => $ticket_metademand->getID(), 'status' => PluginMetademandsTicket_Metademand::TO_CLOSED]);
            } elseif ($counterClosed < $results_sons_ticket->num_rows) {
               $ticket_metademand->update(['id' => $ticket_metademand->getID(), 'status' => PluginMetademandsTicket_Metademand::RUNNING]);
            }

         } else {
            $ticket_metademand->update(['id' => $ticket_metademand->getID(), 'status' => PluginMetademandsTicket_Metademand::CLOSED]);
         }
      }
   }
}

function getSonsQuery($parent_id) {
   return " SELECT `glpi_plugin_metademands_tickets_tasks`.`tickets_id` as 'sons_ticket', `glpi_tickets`.`status` FROM `glpi_plugin_metademands_tickets_tasks`
                        LEFT JOIN `glpi_tickets` ON `glpi_tickets`.`id` = 
                        `glpi_plugin_metademands_tickets_tasks`.`tickets_id` WHERE `glpi_plugin_metademands_tickets_tasks`.`parent_tickets_id` = " . $parent_id;
}

