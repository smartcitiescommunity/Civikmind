<?php
/**
 * @version $Id: config.class.php 338 2021-03-30 12:36:31Z yllen $
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2021 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginBehaviorsConfig extends CommonDBTM {

   static private $_instance = NULL;
   static $rightname         = 'config';


   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }


   static function canView() {
      return Session::haveRight('config', READ);
   }


   static function getTypeName($nb=0) {
      return __('Setup');
   }


   function getName($with_comment=0) {
      return __('Behaviours', 'behaviors');
   }


   /**
    * Singleton for the unique config record
    */
   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }


   static function install(Migration $mig) {
      global $DB;

      $table = 'glpi_plugin_behaviors_configs';
      if (!$DB->tableExists($table)) { //not installed

         $query = "CREATE TABLE `". $table."`(
                     `id` int(11) NOT NULL,
                     `use_requester_item_group` tinyint(1) NOT NULL default '0',
                     `use_requester_user_group` tinyint(1) NOT NULL default '0',
                     `is_ticketsolutiontype_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketsolution_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketcategory_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketcategory_mandatory_on_assign` tinyint(1) NOT NULL default '0',
                     `is_tickettaskcategory_mandatory` tinyint(1) NOT NULL default '0',
                     `is_tickettech_mandatory` tinyint(1) NOT NULL default '0',
                     `is_tickettechgroup_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketrealtime_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketlocation_mandatory` tinyint(1) NOT NULL default '0',
                     `is_requester_mandatory` tinyint(1) NOT NULL default '0',
                     `is_ticketdate_locked` tinyint(1) NOT NULL default '0',
                     `use_assign_user_group` tinyint(1) NOT NULL default '0',
                     `use_assign_user_group_update` tinyint(1) NOT NULL default '0',
                     `ticketsolved_updatetech` tinyint(1) NOT NULL default '0',
                     `tickets_id_format` VARCHAR(15) NULL,
                     `changes_id_format` VARCHAR(15) NULL,
                     `is_problemsolutiontype_mandatory` tinyint(1) NOT NULL default '0',
                     `remove_from_ocs` tinyint(1) NOT NULL default '0',
                     `add_notif` tinyint(1) NOT NULL default '0',
                     `use_lock` tinyint(1) NOT NULL default '0',
                     `single_tech_mode` int(11) NOT NULL default '0',
                     `myasset` tinyint(1) NOT NULL default '0',
                     `groupasset` tinyint(1) NOT NULL default '0',
                     `clone` tinyint(1) NOT NULL default '0',
                     `is_tickettasktodo` tinyint(1) NOT NULL default '0',
                     `date_mod` datetime default NULL,
                     `comment` text,
                     PRIMARY KEY  (`id`)
                   ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->queryOrDie($query, __('Error in creating glpi_plugin_behaviors_configs', 'behaviors').
                                 "<br>".$DB->error());

         $query = "INSERT INTO `$table`
                         (id, date_mod)
                   VALUES (1, NOW())";
         $DB->queryOrDie($query, __('Error during update glpi_plugin_behaviors_configs', 'behaviors').
                                 "<br>" . $DB->error());

      } else {
         // Upgrade

         $mig->addField($table, 'tickets_id_format',        'string');
         $mig->addField($table, 'remove_from_ocs',          'bool');
         $mig->addField($table, 'is_requester_mandatory',   'bool');

         // version 0.78.0 - feature #2801 Forbid change of ticket's creation date
         $mig->addField($table, 'is_ticketdate_locked',     'bool');

         // Version 0.80.0 - set_use_date_on_state now handle in GLPI
         $mig->dropField($table, 'set_use_date_on_state');

         // Version 0.80.4 - feature #3171 additional notifications
         $mig->addField($table, 'add_notif',                'bool');

         // Version 0.83.0 - groups now have is_requester and is_assign attribute
         $mig->dropField($table, 'sql_user_group_filter');
         $mig->dropField($table, 'sql_tech_group_filter');

         // Version 0.83.1 - prevent update on ticket updated by another user
         $mig->addField($table, 'use_lock',                 'bool');

         // Version 0.83.4 - single tech/group #3857
         $mig->addField($table, 'single_tech_mode',         'integer');

         // Version 0.84.2 - solution description mandatory #2803
         $mig->addField($table, 'is_ticketsolution_mandatory', 'bool');
         //- ticket category mandatory #3738
         $mig->addField($table, 'is_ticketcategory_mandatory', 'bool');
         //- solution type mandatory for a problem  #5048
         $mig->addField($table, 'is_problemsolutiontype_mandatory', 'bool');

         // Version 0.90 - technician mandatory #5381
         $mig->addField($table, 'is_tickettech_mandatory', 'bool');

         // Version 1.3 - ticket location mandatory #5520
         $mig->addField($table, 'is_ticketlocation_mandatory', 'bool',
                        ['after' => 'is_ticketrealtime_mandatory']);

         // Version 1.5 - show my asset #5530
         $mig->addField($table, 'groupasset', 'bool', ['after' => 'single_tech_mode']);
         $mig->addField($table, 'myasset', 'bool', ['after' => 'single_tech_mode']);

         // Version 1.5.1 - config for clone #5531
         $mig->addField($table, 'clone', 'bool', ['after' => 'groupasset']);

         // Version 1.6.0 - delete newtech, newgroup dans newsupplier for notif. Now there are in the core
         $query = "UPDATE `glpi_notifications`
                   SET `event` = 'assign_user'
                   WHERE `event` = 'plugin_behaviors_ticketnewtech'";
         $DB->queryOrDie($query, "9.2 change notification assign user to core one");

         $query = "UPDATE `glpi_notifications`
                   SET `event` = 'assign_group'
                   WHERE `event` = 'plugin_behaviors_ticketnewgrp'";
         $DB->queryOrDie($query, "9.2 change notification assign group to core one");

         $query = "UPDATE `glpi_notifications`
                   SET `event` = 'assign_supplier'
                   WHERE `event` = 'plugin_behaviors_ticketnewsupp'";
         $DB->queryOrDie($query, "9.2 change notification assign supplier to core one");

         $query = "UPDATE `glpi_notifications`
                   SET `event` = 'observer_user'
                   WHERE `event` = 'plugin_behaviors_ticketnewwatch'";
         $DB->queryOrDie($query, "9.2 change notification add watcher to core one");

         $mig->addField($table, 'is_tickettasktodo', 'bool', ['after' => 'clone']);

         // version 2.1.0
         $mig->addField($table, 'is_tickettaskcategory_mandatory', 'bool',
                        ['after' => 'is_ticketcategory_mandatory']);
         $mig->addField($table, 'is_tickettechgroup_mandatory', 'bool',
                        ['after' => 'is_tickettech_mandatory']);

         // version 2.2.2
         $mig->addField($table, 'changes_id_format', 'VARCHAR(15) NULL',
                        ['after' => 'tickets_id_format']);

         // version 2.3.0
         $mig->addField($table, 'ticketsolved_updatetech', 'bool',
                        ['after' => 'use_assign_user_group']);
         $mig->addField($table, 'use_assign_user_group_update', 'bool',
                        ['after' => 'use_assign_user_group']);
         $mig->addField($table, 'is_ticketcategory_mandatory_on_assign', 'bool',
                        ['after' => 'is_ticketcategory_mandatory']);
      }

   }


   static function uninstall(Migration $mig) {
      $mig->dropTable('glpi_plugin_behaviors_configs');
   }


   static function showConfigForm($item) {

      $yesnoall = [0 => __('No'),
                   1 => __('First'),
                   2 => __('All')];

      $config = self::getInstance();

      $config->showFormHeader();

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' class='tab_bg_2 b center' width='60%'>".__('New ticket')."</td>";
      echo "<td colspan='2' class='tab_bg_2 b center'>".__('Inventory', 'behaviors')."</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Ticket's number format", "behaviors")."</td><td width='20%'>";
      $tab = ['NULL' => Dropdown::EMPTY_VALUE];
      foreach (['Y000001', 'Ym0001', 'Ymd01', 'ymd0001'] as $fmt) {
         $tab[$fmt] = date($fmt) . '  (' . $fmt . ')';
      }
      Dropdown::showFromArray("tickets_id_format", $tab,
                              ['value' => $config->fields['tickets_id_format']]);
      echo "<td>".__('Delete computer in OCSNG when purged from GLPI', 'behaviors')."</td><td>";
      $plugin = new Plugin();
      if ($plugin->isActivated('uninstall') && $plugin->isActivated('ocsinventoryng')) {
         Dropdown::showYesNo('remove_from_ocs', $config->fields['remove_from_ocs']);
      } else {
         if (!$plugin->isActivated('uninstall')) {
           echo __("Plugin \"Item's uninstallation\" not installed", "behaviors")."\n";
         }
         if (!$plugin->isActivated('ocsinventoryng')) {
            echo __("Plugin \"OCS Inventory NG\" not installed", "behaviors");
         }
      }
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the associated item's group", "behaviors")."</td><td>";
      Dropdown::showYesNo("use_requester_item_group", $config->fields['use_requester_item_group']);
      echo "<td>".__("Show my assets", "behaviors")."</td><td>";
      Dropdown::showYesNo('myasset', $config->fields['myasset']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the requester's group", "behaviors")."</td><td>";
      Dropdown::showFromArray('use_requester_user_group', $yesnoall,
                              ['value' => $config->fields['use_requester_user_group']]);
      echo "<td>".__("Show assets of my groups", "behaviors")."</td><td>";
      Dropdown::showYesNo('groupasset', $config->fields['groupasset']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the technician's group", "behaviors")."</td><td>";
      Dropdown::showFromArray('use_assign_user_group', $yesnoall,
                              ['value' => $config->fields['use_assign_user_group']]);
      echo "</td><td colspan='2' class='tab_bg_2 b center'>"._n('Notification', 'Notifications', 2,
            'behaviors');
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Requester is mandatory", "behaviors")."</td><td>";
      Dropdown::showYesNo("is_requester_mandatory", $config->fields['is_requester_mandatory']);
      echo "<td>".__('Additional notifications', 'behaviors')."</td><td>";
      Dropdown::showYesNo('add_notif', $config->fields['add_notif']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' class='tab_bg_2 b center'>".__('Update of a ticket')."</td>";
      echo "</td><td class='tab_bg_2 b center'>".__('Allow Clone', 'behaviors')."</td><td>";
      Dropdown::showYesNo('clone', $config->fields['clone']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Duration is mandatory before ticket is solved/closed', 'behaviors')."</td><td>";
      Dropdown::showYesNo("is_ticketrealtime_mandatory",
                          $config->fields['is_ticketrealtime_mandatory']);
      echo "<td colspan=2' class='tab_bg_2 b center'>".__('Update of a problem')."</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Category is mandatory before ticket is solved/closed', 'behaviors')."</td><td>";
      Dropdown::showYesNo("is_ticketcategory_mandatory",
                          $config->fields['is_ticketcategory_mandatory']);
      echo "</td><td>".__('Type of solution is mandatory before problem is solved/closed', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_problemsolutiontype_mandatory",
                          $config->fields['is_problemsolutiontype_mandatory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Type of solution is mandatory before ticket is solved/closed', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_ticketsolutiontype_mandatory",
                          $config->fields['is_ticketsolutiontype_mandatory']);
      echo "</td><td colspan=2' class='tab_bg_2 b center'>".__('New change')."</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Category is mandatory when you assign a ticket', 'behaviors')."</td><td>";
      Dropdown::showYesNo("is_ticketcategory_mandatory_on_assign",
                          $config->fields['is_ticketcategory_mandatory_on_assign']);
      echo "</td><td>".__("Change's number format", "behaviors")."</td><td width='20%'>";
      $tab = ['NULL' => Dropdown::EMPTY_VALUE];
      foreach (['Y000001', 'Ym0001', 'Ymd01', 'ymd0001'] as $fmt) {
         $tab[$fmt] = date($fmt) . '  (' . $fmt . ')';
      }
      Dropdown::showFromArray("changes_id_format", $tab,
                              ['value' => $config->fields['changes_id_format']]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "</td><td>".__('Description of solution is mandatory before ticket is solved/closed',
                          'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_ticketsolution_mandatory",
                          $config->fields['is_ticketsolution_mandatory']);
      echo "</td><td colspan='2' class='tab_bg_2 b center'>".__('Comments');
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Technician assigned is mandatory before ticket is solved/closed', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_tickettech_mandatory",
                          $config->fields['is_tickettech_mandatory']);
      echo "</td><td rowspan='7' colspan='2' class='center'>";
      echo "<textarea cols='60' rows='12' name='comment' >".$config->fields['comment']."</textarea>";
      echo "</td></tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group of technicians assigned is mandatory before ticket is solved/closed',
                     'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_tickettechgroup_mandatory",
                          $config->fields['is_tickettechgroup_mandatory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the technician's group", "behaviors")."</td><td>";
      Dropdown::showFromArray('use_assign_user_group_update', $yesnoall,
                              ['value' => $config->fields['use_assign_user_group_update']]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Location is mandatory before ticket is solved/closed', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_ticketlocation_mandatory",
      $config->fields['is_ticketlocation_mandatory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Task category is mandatory in a task', 'behaviors')."</td><td>";
      Dropdown::showYesNo("is_tickettaskcategory_mandatory",
      $config->fields['is_tickettaskcategory_mandatory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Deny change of ticket's creation date", "behaviors")."</td><td>";
      Dropdown::showYesNo("is_ticketdate_locked", $config->fields['is_ticketdate_locked']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Protect from simultaneous update', 'behaviors')."</td><td>";
      Dropdown::showYesNo("use_lock", $config->fields['use_lock']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Single technician and group', 'behaviors')."</td><td>";
      $tab = [0 => __('No'),
              1 => __('Single user and single group', 'behaviors'),
              2 => __('Single user or group', 'behaviors')];
      Dropdown::showFromArray('single_tech_mode', $tab,
                              ['value' => $config->fields['single_tech_mode']]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Block the solving/closing of a the ticket if task do to', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("is_tickettasktodo", $config->fields['is_tickettasktodo']);
      echo "</td><td colspan='2'></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Add the logged technician when solve ticket', 'behaviors');
      echo "</td><td>";
      Dropdown::showYesNo("ticketsolved_updatetech", $config->fields['ticketsolved_updatetech']);
      echo "</td><td colspan='2'></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'></th>";
      echo "<th colspan='2'>".sprintf(__('%1$s %2$s'), __('Last update'),
                                      Html::convDateTime($config->fields["date_mod"]));
      echo "</td></tr>";

      $config->showFormButtons(['formfooter' => true, 'candel'=>false]);

      return false;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Config') {
            return self::getName();
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Config') {
         self::showConfigForm($item);
      }
      return true;
   }


   /**
    * Restrict visibility rights
    *
    * @since 1.5.0
    *
    * @param  $item
   **/
   static function item_can($item) {
      global $DB, $CFG_GLPI;

      $itemtype = $item->getType();
      if (in_array($item->getType(), $CFG_GLPI["asset_types"])
          && !Session::haveRight($itemtype::$rightname, UPDATE)) {

         $config = PluginBehaviorsConfig::getInstance();
         if ($config->getField('myasset')
             && ($item->fields['users_id'] > 0)
             && ($item->fields['users_id'] <> Session::getLoginUserID())) {

            if ($config->getField('groupasset')
                && ($item->fields['groups_id'] > 0)
                && !in_array($item->fields['groups_id'], $_SESSION["glpigroups"])) {
               $item->right = '0';
            }
         }
         if ($config->getField('groupasset')
              && ($item->fields['groups_id'] > 0)
              && !in_array($item->fields['groups_id'], $_SESSION["glpigroups"])) {

            if ($config->getField('myasset')
                && ($item->fields['users_id'] > 0)
                && ($item->fields['users_id'] <> Session::getLoginUserID())) {
               $item->right = '0';
            }
         }
      }
   }


   /**
    * Restrict visibility rights
    *
    * @since 1.5.0
    *
    * @param  $item
   **/
   static function add_default_where($item) {
      global $DB, $CFG_GLPI;;

      $condition = "";
      list($itemtype, $condition) = $item;

      $dbu = new DbUtils();

      $config = PluginBehaviorsConfig::getInstance();
      if (in_array($itemtype, $CFG_GLPI["asset_types"])
          && !Session::haveRight($itemtype::$rightname, UPDATE)) {

         $dbu = new DbUtils();
         $table  = $dbu->getTableForItemType($itemtype);
         if ($config->getField('myasset')) {
            $condition .= "(`".$table."`.`users_id` = ".Session::getLoginUserID().")";
            if ($config->getField('groupasset')
                && count($_SESSION["glpigroups"])) {
               $condition .= " OR ";
            }
         }
         if ($config->getField('groupasset')
             && count($_SESSION["glpigroups"])) {
            $condition .= " (`".$table."`.`groups_id` IN ('".implode("','", $_SESSION["glpigroups"])."'))";
         }
      }

      $filtre = [];
      if ($itemtype == 'AllAssets') {
         foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$itemtype]] as $ctype) {
            if (($citem = $dbu->getItemForItemtype($ctype))
                && !$citem->canUpdate()) {
               $filtre[$ctype] = $ctype;
            }
         }

         if (count($filtre)) {
            if ($config->getField('myasset')) {
               $condition .= " (`asset_types`.`users_id` = ".Session::getLoginUserID().")";
               if ($config->getField('groupasset')
                   && count($_SESSION["glpigroups"])) {
                  $condition .= " OR ";
               }
            }
            if ($config->getField('groupasset')
                && count($_SESSION["glpigroups"])) {
               $condition .= " (`asset_types`.`groups_id` IN ('".implode("','", $_SESSION["glpigroups"])."'))";
            }
         }
      }
      return [$itemtype, $condition];
   }
}
