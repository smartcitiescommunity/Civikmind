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

/**
 * @return bool
 */
function plugin_moreticket_install() {
   global $DB,$CFG_GLPI;

   include_once(PLUGIN_MORETICKET_DIR . "/inc/profile.class.php");

   if (!$DB->tableExists("glpi_plugin_moreticket_configs")) {
      // table sql creation
      $DB->runFile(PLUGIN_MORETICKET_DIR. "/sql/empty-1.5.1.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_moreticket_configs", "solution_status")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.1.1.sql");
   }

   if ($DB->fieldExists("glpi_plugin_moreticket_waitingtypes", "is_helpdeskvisible")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.1.2.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_moreticket_closetickets", "documents_id")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.1.3.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_moreticket_configs", "date_report_mandatory")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.2.0.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_moreticket_configs", "close_followup")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.2.2.sql");
   }

   //version 1.2.3
   if (!$DB->fieldExists("glpi_plugin_moreticket_configs", "waitingreason_mandatory")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.2.3.sql");
   }

   //version 1.2.4
   if (!$DB->fieldExists("glpi_plugin_moreticket_configs", "urgency_justification")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.2.4.sql");
   }

   //version 1.2.5
   if (!$DB->fieldExists("glpi_plugin_moreticket_waitingtickets", "status")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.2.5.sql");
   }

   //version 1.3.2
   if (!$DB->fieldExists("glpi_plugin_moreticket_configs", "use_duration_solution")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.3.2.sql");
   }

   //version 1.3.4
   if (!$DB->fieldExists("glpi_plugin_moreticket_configs", "is_mandatory_solution")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.3.4.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_moreticket_configs", "use_question")) {
      $DB->runFile(PLUGIN_MORETICKET_DIR . "/sql/update-1.5.1.sql");
   }

   CronTask::Register('PluginMoreticketWaitingTicket', 'MoreticketWaitingTicket', DAY_TIMESTAMP, ['state' => 0]);

   PluginMoreticketProfile::initProfile();
   PluginMoreticketProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("2.0.0");
   $migration->dropTable('glpi_plugin_moreticket_profiles');
   return true;
}

// Uninstall process for plugin : need to return true if succeeded
/**
 * @return bool
 */
function plugin_moreticket_uninstall() {
   global $DB;

   include_once(PLUGIN_MORETICKET_DIR . "/inc/profile.class.php");

   // Plugin tables deletion
   $tables = ["glpi_plugin_moreticket_configs",
                   "glpi_plugin_moreticket_waitingtickets",
                   "glpi_plugin_moreticket_waitingtypes",
                   "glpi_plugin_moreticket_closetickets",
                   "glpi_plugin_moreticket_urgencytickets"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginMoreticketProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }

   CronTask::Unregister('moreticket');

   return true;
}

function plugin_moreticket_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['moreticket'] = [];
   $PLUGIN_HOOKS['item_add']['moreticket']   = [];
}

// Define dropdown relations
/**
 * @return array
 */
function plugin_moreticket_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("moreticket")) {
      return ["glpi_tickets"                        => ["glpi_plugin_moreticket_waitingtickets" => "tickets_id"],
                   "glpi_plugin_moreticket_waitingtypes" => ["glpi_plugin_moreticket_waitingtickets" => "plugin_moreticket_waitingtypes_id"],
                   "glpi_tickets"                        => ["glpi_plugin_moreticket_closetickets" => "tickets_id"]];
   } else {
      return [];
   }
}

// Define Dropdown tables to be manage in GLPI :
/**
 * @return array
 */
function plugin_moreticket_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("moreticket")) {
      return ['PluginMoreticketWaitingType' => PluginMoreticketWaitingType::getTypeName(2)];
   } else {
      return [];
   }
}

// Hook done on purge item case
/**
 * @param $item
 */
function plugin_pre_item_purge_moreticket($item) {

   switch (get_class($item)) {
      case 'Ticket' :
         $temp = new PluginMoreticketWaitingTicket();
         $temp->deleteByCriteria(['tickets_id' => $item->getField('id')]);
         break;
   }
}


////// SEARCH FUNCTIONS ///////() {

// Define search option for types of the plugins
/**
 * @param $itemtype
 *
 * @return array
 */
function plugin_moreticket_getAddSearchOptions($itemtype) {

   $sopt = [];

   if ($itemtype == "Ticket") {
      if (Session::haveRight("plugin_moreticket", READ)) {

         $config = new PluginMoreticketConfig();

         $sopt[3450]['table']         = 'glpi_plugin_moreticket_waitingtickets';
         $sopt[3450]['field']         = 'reason';
         $sopt[3450]['name']          = __('Reason', 'moreticket');
         $sopt[3450]['datatype']      = "text";
         $sopt[3450]['joinparams']    = ['jointype' => 'child',
                                              'condition' => "AND `NEWTABLE`.`date_end_suspension` IS NULL"];
         $sopt[3450]['massiveaction'] = false;

         $sopt[3451]['table']         = 'glpi_plugin_moreticket_waitingtickets';
         $sopt[3451]['field']         = 'date_report';
         $sopt[3451]['name']          = __('Postponement date', 'moreticket');
         $sopt[3451]['datatype']      = "datetime";
         $sopt[3451]['joinparams']    = ['jointype' => 'child',
                                              'condition' => "AND `NEWTABLE`.`date_end_suspension` IS NULL"];
         $sopt[3451]['massiveaction'] = false;

         $sopt[3452]['table']         = 'glpi_plugin_moreticket_waitingtypes';
         $sopt[3452]['field']         = 'name';
         $sopt[3452]['name']          = PluginMoreticketWaitingType::getTypeName(1);
         $sopt[3452]['datatype']      = "dropdown";
         $condition                   = "AND (`NEWTABLE`.`date_end_suspension` IS NULL)";
         $sopt[3452]['joinparams']    = ['beforejoin'
                                              => ['table'      => 'glpi_plugin_moreticket_waitingtickets',
                                                       'joinparams' => ['jointype'  => 'child',
                                                                             'condition' => $condition]]];
         $sopt[3452]['massiveaction'] = false;

         if ($config->closeInformations()) {
            $sopt[3453]['table']         = 'glpi_plugin_moreticket_closetickets';
            $sopt[3453]['field']         = 'date';
            $sopt[3453]['name']          = __('Close ticket informations', 'moreticket') . " : " . __('Date');
            $sopt[3453]['datatype']      = "datetime";
            $sopt[3453]['joinparams']    = ['jointype' => 'child'];
            $sopt[3453]['massiveaction'] = false;

            $sopt[3454]['table']         = 'glpi_plugin_moreticket_closetickets';
            $sopt[3454]['field']         = 'comment';
            $sopt[3454]['name']          = __('Close ticket informations', 'moreticket') . " : " . __('Comments');
            $sopt[3454]['datatype']      = "text";
            $sopt[3454]['joinparams']    = ['jointype' => 'child'];
            $sopt[3454]['massiveaction'] = false;

            $sopt[3455]['table']         = 'glpi_plugin_moreticket_closetickets';
            $sopt[3455]['field']         = 'requesters_id';
            $sopt[3455]['name']          = __('Close ticket informations', 'moreticket') . " : " . __('Writer');
            $sopt[3455]['datatype']      = "dropdown";
            $sopt[3455]['joinparams']    = ['jointype' => 'child'];
            $sopt[3455]['massiveaction'] = false;

            $sopt[3486]['table']         = 'glpi_documents';
            $sopt[3486]['field']         = 'name';
            $sopt[3486]['name']          = __('Close ticket informations', 'moreticket') . " : " . _n('Document', 'Documents', Session::getPluralNumber());
            $sopt[3486]['forcegroupby']  = true;
            $sopt[3486]['usehaving']     = true;
            $sopt[3486]['datatype']      = 'dropdown';
            $sopt[3486]['massiveaction'] = false;
            $sopt[3486]['joinparams']    = ['beforejoin' => ['table'      => 'glpi_documents_items',
                                                                       'joinparams' => ['jointype'          => 'itemtype_item',
                                                                                             'specific_itemtype' => 'PluginMoreticketCloseTicket',
                                                                                             'beforejoin'        => ['table'      => 'glpi_plugin_moreticket_closetickets',
                                                                                                                          'joinparams' => []]]]];
         }
      }
   }
   return $sopt;
}

/**
 * @param $link
 * @param $nott
 * @param $type
 * @param $ID
 * @param $val
 * @param $searchtype
 *
 * @return string
 */
function plugin_moreticket_addWhere($link, $nott, $type, $ID, $val, $searchtype) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table . "." . $field) {
      case "glpi_plugin_moreticket_waitingtickets.date_report" :
         $query = "";
         if (isset($_GET['criteria'])) {
            foreach ($_GET['criteria'] as $key => $search_item) {
               if (in_array($search_item['field'], array_keys($searchopt)) && $search_item['field'] == $ID) {
                  $NOT = $nott ? "NOT" : "";

                  $SEARCH = "";
                  switch ($search_item['searchtype']) {
                     case 'morethan':
                        $SEARCH = "> '" . $val . "'";
                        break;
                     case 'lessthan':
                        $SEARCH = "< '" . $val . "'";
                        break;
                     case 'equals':
                        $SEARCH = "= '" . $val . "'";
                        break;
                     case 'notequals':
                        $SEARCH = "!= '" . $val . "'";
                        break;
                     case 'contains':
                        $SEARCH = "LIKE '%" . $val . "%'";
                        if ($val == 'NULL') {
                           $SEARCH = "IS NULL";
                        }
                        break;
                  }

                  $query = " " . $link . " " . $NOT . " ((SELECT max(`" . $table . "`.`" . $field . "`) FROM `" . $table . "` WHERE `tickets_id` = `glpi_tickets`.`id`) " . $SEARCH;
                  //               if ($search_item['searchtype'] != 'contains') {
                  //                  $query .= " OR `".$table."`.`".$field."` IS NULL";
                  //               }
                  $query .= ")";
               }
            }
         }

         return $query;
   }

   return "";
}
