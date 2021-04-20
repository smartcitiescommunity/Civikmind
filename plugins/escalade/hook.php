<?php
/*
 -------------------------------------------------------------------------
 {NAME} plugin for GLPI
 Copyright (C) 2016-2017 by the Escalade Development Team.

 https://github.com/pluginsGLPI/escalade
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Escalade.

 Escalade is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Escalade is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Escalade. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_escalade_install() {
   global $DB;

   //get version
   $plugin = new Plugin();
   $found = $plugin->find(['name' => 'escalade']);
   $plugin_escalade = array_shift($found);

   //init migration
   $migration = new Migration($plugin_escalade['version']);

   // == Tables creation (initial installation) ==
   if (!$DB->tableExists('glpi_plugin_escalade_histories')) {
      $query = "CREATE TABLE `glpi_plugin_escalade_histories` (
         `id`              INT(11) NOT NULL AUTO_INCREMENT,
         `tickets_id`      INT(11) NOT NULL,
         `groups_id`       INT(11) NOT NULL,
         `date_mod`        TIMESTAMP NOT NULL,
         PRIMARY KEY (`id`),
         KEY `tickets_id` (`tickets_id`),
         KEY `groups_id` (`groups_id`)
      ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->query($query);
   }

   if (!$DB->tableExists('glpi_plugin_escalade_configs')) {
      $query = "CREATE TABLE `glpi_plugin_escalade_configs` (
         `id`                                      INT(11) NOT NULL AUTO_INCREMENT,
         `remove_group`                            INT(11) NOT NULL,
         `show_history`                            INT(11) NOT NULL,
         `task_history`                            INT(11) NOT NULL,
         `remove_tech`                             INT(11) NOT NULL,
         `solve_return_group`                      INT(11) NOT NULL,
         `reassign_group_from_cat`                 INT(11) NOT NULL,
         `reassign_tech_from_cat`                  INT(11) NOT NULL,
         `cloneandlink_ticket`                     INT(11) NOT NULL,
         `close_linkedtickets`                     INT(11) NOT NULL,
         `use_assign_user_group`                   INT(11) NOT NULL,
         `use_assign_user_group_creation`          INT(11) NOT NULL,
         `use_assign_user_group_modification`      INT(11) NOT NULL,
         `remove_delete_requester_user_btn`        TINYINT(1) NOT NULL DEFAULT 1,
         `remove_delete_watcher_user_btn`          TINYINT(1) NOT NULL DEFAULT 1,
         `remove_delete_assign_user_btn`           TINYINT(1) NOT NULL DEFAULT 0,
         `remove_delete_requester_group_btn`       TINYINT(1) NOT NULL DEFAULT 1,
         `remove_delete_watcher_group_btn`         TINYINT(1) NOT NULL DEFAULT 1,
         `remove_delete_assign_group_btn`          TINYINT(1) NOT NULL DEFAULT 0,
         `remove_delete_assign_supplier_btn`       TINYINT(1) NOT NULL DEFAULT 1,
         `use_filter_assign_group`                 INT(11) NOT NULL,
         `ticket_last_status`                      INT(11) NOT NULL,
         PRIMARY KEY (`id`)
      ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->query($query);

      $query = "INSERT INTO glpi_plugin_escalade_configs
      VALUES (NULL, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 1, 1, 0, 1, 1, 0, 1, 0, '".Ticket::WAITING."')";
      $DB->query($query);
   }

   // == Update to 1.2 ==
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'cloneandlink_ticket')) {
      $migration->addField('glpi_plugin_escalade_configs', 'cloneandlink_ticket',
                           'integer', ['after' => 'reassign_tech_from_cat']);
      $migration->migrationOneTable('glpi_plugin_escalade_configs');
   }
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'close_linkedtickets')) {
      $migration->addField('glpi_plugin_escalade_configs', 'close_linkedtickets',
                           'integer', ['after' => 'cloneandlink_ticket']);
      $migration->migrationOneTable('glpi_plugin_escalade_configs');
   }

   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'use_assign_user_group')) {
      $migration->addField('glpi_plugin_escalade_configs', 'use_assign_user_group',
                           'integer', ['after' => 'close_linkedtickets']);
      $migration->migrationOneTable('glpi_plugin_escalade_configs');
   }
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'use_assign_user_group_creation')) {
      $migration->addField('glpi_plugin_escalade_configs', 'use_assign_user_group_creation',
                           'integer', ['after' => 'use_assign_user_group']);
      $migration->migrationOneTable('glpi_plugin_escalade_configs');
   }
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'use_assign_user_group_modification')) {
      $migration->addField('glpi_plugin_escalade_configs', 'use_assign_user_group_modification',
                           'integer', ['after' => 'use_assign_user_group_creation']);
      $migration->migrationOneTable('glpi_plugin_escalade_configs');
   }
   if (! isIndex("glpi_plugin_escalade_histories", 'tickets_id')
       || ! isIndex("glpi_plugin_escalade_histories", 'groups_id')) {
      $migration->addKey("glpi_plugin_escalade_histories", 'tickets_id', 'tickets_id');
      $migration->addKey("glpi_plugin_escalade_histories", 'groups_id', 'groups_id');
      $migration->migrationOneTable('glpi_plugin_escalade_histories');
   }

   // == Update to 1.3 ==
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'use_filter_assign_group')) {
      $migration->addField('glpi_plugin_escalade_configs', 'use_filter_assign_group',
                           'integer', ['after' => 'use_assign_user_group_modification']);
      $migration->migrationOneTable('glpi_plugin_escalade_configs');
   }
   if (!$DB->tableExists('glpi_plugin_escalade_groups_groups')) {
      $query = "CREATE TABLE `glpi_plugin_escalade_groups_groups` (
         `id` INT(11) NOT NULL AUTO_INCREMENT,
         `groups_id_source` int(11) NOT NULL DEFAULT '0',
         `groups_id_destination` int(11) NOT NULL DEFAULT '0',
         PRIMARY KEY (`id`)
      ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->query($query);
   }

   // Update for 0.84 status
   if ($DB->tableExists('glpi_plugin_escalade_configs')) {
      foreach ($DB->request("glpi_plugin_escalade_configs") as $data) {
         switch ($data['ticket_last_status']) {
            case 'solved':
               $status = Ticket::SOLVED;
               break;
            case 'waiting':
               $status = Ticket::WAITING;
               break;
            case 'closed':
               $status = Ticket::CLOSED;
               break;
            case 'assign':
               $status = Ticket::ASSIGNED;
               break;
            case 'new':
               $status = Ticket::INCOMING;
               break;
            case 'plan':
               $status = Ticket::PLANNED;
               break;
            default :
               $status = -1;
               break;
         }
         $query = "UPDATE `glpi_plugin_escalade_configs`
                   SET `ticket_last_status` = '".$status."'
                   WHERE `id` = '".$data['id']."'";
         $DB->query($query);
      }

      $query = "ALTER TABLE `glpi_plugin_escalade_configs` MODIFY `ticket_last_status` INT(11);";
      $DB->query($query);
   }

   // update to 0.85-1.0
   if ($DB->fieldExists("glpi_plugin_escalade_configs", "assign_me_ticket")) {
      // assign me ticket feature native in glpi 0.85
      $migration->dropField("glpi_plugin_escalade_configs", "assign_me_ticket");
      $migration->migrationOneTable('glpi_plugin_escalade_configs');
   }

   // update to 0.90-1.1
   if (!$DB->tableExists('glpi_plugin_escalade_users')) {
      $query = "CREATE TABLE `glpi_plugin_escalade_users` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `users_id` INT(11) NOT NULL,
                  `use_filter_assign_group` TINYINT(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  INDEX `users_id` (`users_id`)
               )
               ENGINE=InnoDB;";
      $DB->query($query);

      include_once(Plugin::getPhpDir('escalade')."/inc/config.class.php");

      $config = new PluginEscaladeConfig();
      $config->getFromDB(1);
      $default_value = $config->fields["use_filter_assign_group"];

      $user = new User();
      foreach ($user->find() as $data) {
         $query = "INSERT INTO glpi_plugin_escalade_users (`users_id`, `use_filter_assign_group`)
                     VALUES (".$data['id'].", $default_value)";
         $DB->query($query);
      }
   }

   // ## update to 2.2.0
   // add new fields
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'remove_delete_requester_user_btn')) {
      $migration->addField('glpi_plugin_escalade_configs', 'remove_delete_requester_user_btn',
                           'bool', ['value' => 1]);
   }
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'remove_delete_requester_group_btn')) {
      $migration->addField('glpi_plugin_escalade_configs', 'remove_delete_requester_group_btn',
                           'bool', ['value' => 1]);
   }
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'remove_delete_watcher_user_btn')) {
      $migration->addField('glpi_plugin_escalade_configs', 'remove_delete_watcher_user_btn',
                           'bool', ['value' => 1]);
   }
   if (!$DB->fieldExists('glpi_plugin_escalade_configs', 'remove_delete_watcher_group_btn')) {
      $migration->addField('glpi_plugin_escalade_configs', 'remove_delete_watcher_group_btn',
                           'bool', ['value' => 1]);
   }

   // migrate old fields
   if ($DB->fieldExists('glpi_plugin_escalade_configs', 'remove_delete_user_btn')) {
      $migration->changeField('glpi_plugin_escalade_configs',
                              'remove_delete_user_btn',
                              'remove_delete_assign_user_btn',
                              'bool',
                              ['value' => 0]);
   }
   if ($DB->fieldExists('glpi_plugin_escalade_configs', 'remove_delete_group_btn')) {
      $migration->changeField('glpi_plugin_escalade_configs',
                              'remove_delete_group_btn',
                              'remove_delete_assign_group_btn',
                              'bool',
                              ['value' => 0]);
   }
   if ($DB->fieldExists('glpi_plugin_escalade_configs', 'remove_delete_supplier_btn')) {
      $migration->changeField('glpi_plugin_escalade_configs',
                              'remove_delete_supplier_btn',
                              'remove_delete_assign_supplier_btn',
                              'bool',
                              ['value' => 1]);
   }

   $migration->migrationOneTable('glpi_plugin_escalade_configs');

   if (!$DB->fieldExists('glpi_plugin_escalade_histories', 'previous_groups_id')
      || !$DB->fieldExists("glpi_plugin_escalade_histories", 'counter')) {
      if (!$DB->fieldExists('glpi_plugin_escalade_histories', 'previous_groups_id')) {
         $migration->addField('glpi_plugin_escalade_histories', 'previous_groups_id', 'integer', ['before' => 'groups_id']);
      }
      if (!$DB->fieldExists('glpi_plugin_escalade_histories', 'counter')) {
         $migration->addField('glpi_plugin_escalade_histories', 'counter', 'integer', ['after' => 'groups_id']);
      }
      $migration->migrationOneTable('glpi_plugin_escalade_histories');

      $history = new PluginEscaladeHistory();
      $histories = [];
      foreach ($history->find() as $data) {
         $tickets_id = $data['tickets_id'];
         unset($data['tickets_id']);

         if (!isset($histories[$tickets_id])) {
            $histories[$tickets_id] = [];
         }

         $histories[$tickets_id][] = $data;
      }

      foreach ($histories as $tickets_id => $h) {
         $counters = [];

         foreach ($h as $k => $details) {
            if (isset($h[$k+1])) {
               $first  = $h[$k+1]['groups_id'] < $details['groups_id'] ? $h[$k+1]['groups_id'] : $details['groups_id'];
               $second = $h[$k+1]['groups_id'] < $details['groups_id'] ? $details['groups_id'] : $h[$k+1]['groups_id'];

               $counters[$first][$second] = isset($counters[$first][$second]) ? $counters[$first][$second] + 1 : 1;
               $h[$k+1]['previous_groups_id'] = $details['groups_id'];
               $h[$k+1]['counter'] = $counters[$first][$second];
            }
         }

         foreach ($h as $k => $details) {
            $DB->update(
               'glpi_plugin_escalade_histories',
               ['previous_groups_id' => $details['previous_groups_id'], 'counter' => $details['counter']],
               ['id' => $details['id']]
            );
         }
      }

   }

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_escalade_uninstall() {
   global $DB;

   //Delete plugin's table
   $tables = [
      'glpi_plugin_escalade_histories',
      'glpi_plugin_escalade_configs',
      'glpi_plugin_escalade_groups_groups',
      'glpi_plugin_escalade_users',
   ];
   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`");
   }

   return true;
}

function plugin_escalade_item_purge($item) {
   global $DB;

   if ($item instanceof User) {
      $DB->query("DELETE FROM glpi_plugin_escalade_users WHERE users_id = ".$item->getID());
   }

   if ($item instanceof Ticket) {
      $history = new PluginEscaladeHistory;
      $history->deleteByCriteria([
         'tickets_id' => $item->getID()
      ]);
   }
   return true;
}

function plugin_escalade_item_update($item) {
   if ($item instanceof Ticket) {
      return PluginEscaladeTicket::item_update($item);
   }
   return true;
}

function plugin_escalade_item_add_user($item) {
   global $DB;

   if ($item instanceof User) {
      $config = new PluginEscaladeConfig();
      $config->getFromDB(1);
      $default_value = $config->fields["use_filter_assign_group"];

      $query = "INSERT INTO glpi_plugin_escalade_users (`users_id`, `use_filter_assign_group`)
                  VALUES (".$item->getID().", $default_value)";
      $DB->query($query);
   }

   if ($item instanceof Ticket_User) {
      //prevent escalade hook to trigger on ticket creation
      if (isset($_SESSION['plugin_escalade']['skip_hook_add_user'])) {
         //unset($_SESSION['plugin_escalade']['skip_hook_add_user']);
         return true;
      }

      //this hook is only for assign
      if ($item->fields['type'] == CommonITILActor::ASSIGN) {
         return PluginEscaladeTicket::item_add_user($item);
      }
   }
   return true;
}

function plugin_escalade_pre_item_add_ticket($item) {
   if ($item instanceof Ticket) {
      $_SESSION['plugin_escalade']['skip_hook_add_user'] = true;
   }
}

function plugin_escalade_item_add_ticket($item) {
   //clean escalade session var after ticket creation
   if ($item instanceof Ticket) {
      unset($_SESSION['plugin_escalade']['skip_hook_add_user']);
      unset($_SESSION['plugin_escalade']['keep_users']);
   }
}

function plugin_escalade_pre_item_add_group_ticket($item) {
   if ($item instanceof Group_Ticket
       && $item->input['type'] == CommonITILActor::ASSIGN) {
         //disable notification to prevent notification for old AND new group
         $item->input['_disablenotif'] = true;
      return PluginEscaladeTicket::addHistoryOnAddGroup($item);
   }
   return $item;
}

function plugin_escalade_item_add_group_ticket($item) {
   if ($item instanceof Group_Ticket
       && $item->fields['type'] == CommonITILActor::ASSIGN) {
      return PluginEscaladeTicket::processAfterAddGroup($item);
   }
   return $item;
}


function plugin_escalade_post_prepareadd_ticket ($item) {
   if ($item instanceof Ticket) {
      return PluginEscaladeTicket::assignUserGroup($item);
   }
   return $item;
}

function plugin_escalade_getAddSearchOptions($itemtype) {
   $sopt = [];

   if ($itemtype == 'Ticket') {
         $sopt[1881]['table']         = 'glpi_groups';
         $sopt[1881]['field']         = 'completename';
         $sopt[1881]['datatype']      = 'dropdown';
         $sopt[1881]['name']          = __("Group concerned by the escalation", "escalade");
         $sopt[1881]['forcegroupby']  = true;
         $sopt[1881]['massiveaction'] = false;
         $sopt[1881]['condition']     = ['is_assign' => 1];
         $sopt[1881]['joinparams']    = [
            'beforejoin' => [
               'table'      => 'glpi_plugin_escalade_histories',
               'joinparams' => [
                  'jointype'  => 'child',
                  'condition' => ''
               ]
            ]
         ];

         $sopt[] = [
            'id'                 => '1991',
            'table'              => 'glpi_plugin_escalade_histories',
            'field'              => 'id',
            'name'               => __("Number of escalations", "escalade"),
            'forcegroupby'       => true,
            'usehaving'          => true,
            'datatype'           => 'count',
            'massiveaction'      => false,
            'joinparams'         => [
               'jointype'           => 'child'
            ]
         ];

         $sopt[] = [
            'id'                 => '1992',
            'table'              => 'glpi_plugin_escalade_histories',
            'field'              => 'counter',
            'name'               => __("Number of escalations between two groups", "escalade"),
            'datatype'           => 'integer',
            'joinparams'         => [
               'jointype'           => 'child'
            ]
         ];
   }
   if ($itemtype == 'User') {
      $sopt[2150]['table']         = 'glpi_plugin_escalade_users';
      $sopt[2150]['field']         = 'use_filter_assign_group';
      $sopt[2150]['linkfield']     = 'id';
      $sopt[2150]['datatype']      = 'bool';
      $sopt[2150]['searchtype']    = ['equals'];
      $sopt[2150]['name']          = __("Enable filtering on the groups assignment", 'escalade');
      $sopt[2150]['joinparams']    = [
         'beforejoin'  => [
            'table'      => 'glpi_plugin_escalade_users',
            'joinparams' => [
               'jointype' => 'child',
               'condition' => ''
            ]
         ]
      ];
   }
   return $sopt;
}

function plugin_escalade_MassiveActions($itemtype) {
   switch ($itemtype) {
      case 'User':
         return ['PluginEscaladeUser'.MassiveAction::CLASS_ACTION_SEPARATOR.'use_filter_assign_group'
                  => __("Enable filtering on the groups assignment", 'escalade')];
   }
   return [];
}
