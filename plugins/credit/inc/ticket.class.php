<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of credit.
 *
 * credit is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * credit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2017-2018 by Teclib'.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/credit
 * @link      https://pluginsglpi.github.io/credit/
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginCreditTicket extends CommonDBTM {

   public static $rightname = 'ticket';

   static function getTypeName($nb = 0) {
      return _n('Credit voucher', 'Credit vouchers', $nb, 'credit');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $nb = self::countForItem($item);
      switch ($item->getType()) {
         case 'Ticket' :
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(self::getTypeName($nb), $nb);
            } else {
               return self::getTypeName($nb);
            }
         default :
            return self::getTypeName($nb);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case 'Ticket' :
            self::showForTicket($item);
            break;
      }
      return true;
   }

   /**
    * @param $item    CommonDBTM object
   **/
   public static function countForItem(CommonDBTM $item) {
      return countElementsInTable(self::getTable(), ['tickets_id' => $item->getID()]);
   }

   /**
    * Get all credit vouchers for a ticket.
    *
    * @param $ID           integer     tickets ID
    * @param $start        integer     first line to retrieve (default 0)
    * @param $limit        integer     max number of line to retrieve (0 for all) (default 0)
    * @param $sqlfilter    string      to add a SQL filter (default '')
    * @return array of vouchers
   **/
   static function getAllForTicket($ID, $start = 0, $limit = 0, $sqlfilter = '') {
      global $DB;

      $query = "SELECT *
                FROM `".self::getTable()."`
                WHERE `tickets_id` = '$ID'";
      if ($sqlfilter) {
         $query .= "AND ($sqlfilter) ";
      }
      $query .= "ORDER BY `id` DESC";

      if ($limit) {
         $query .= " LIMIT ".(int) $start.",".(int) $limit;
      }

      $vouchers = [];
      foreach ($DB->request($query) as $data) {
         $vouchers[$data['id']] = $data;
      }

      return $vouchers;
   }


   /**
    * Get all tickets for a credit vouchers.
    *
    * @param $ID           integer     plugin_credit_entities_id ID
    * @param $start        integer     first line to retrieve (default 0)
    * @param $limit        integer     max number of line to retrive (0 for all) (default 0)
    * @param $sqlfilter    string      to add a SQL filter (default '')
    * @return array of vouchers
   **/
   static function getAllForCreditEntity($ID, $start = 0, $limit = 0, $sqlfilter = '') {
      global $DB;

      $query = "SELECT *
                FROM `".self::getTable()."`
                WHERE `plugin_credit_entities_id` = '$ID'";
      if ($sqlfilter) {
         $query .= "AND ($sqlfilter) ";
      }
      $query .= " ORDER BY `id` DESC";

      if ($limit) {
         $query .= " LIMIT ".(int) $start."," .(int) $limit;
      }

      $tickets = [];
      foreach ($DB->request($query) as $data) {
         $tickets[$data['id']] = $data;
      }

      return $tickets;
   }

   /**
    * Get consumed tickets for credit entity entry
    *
    * @param $ID integer PluginCreditEntity id
   **/
   static function getConsumedForCreditEntity($ID) {
      global $DB;

      $tot   = 0;
      $query = "SELECT SUM(`consumed`)
                FROM `".self::getTable()."`
                WHERE `plugin_credit_entities_id` = '".$ID."'";

      if ($result = $DB->query($query)) {
         $sum = $DB->result($result, 0, 0);
         if (!is_null($sum)) {
            $tot += $sum;
         }
      }

      return $tot;
   }

   /**
    * Show credit vouchers consumed for a ticket
    *
    * @param $ticket Ticket object
   **/
   static function showForTicket(Ticket $ticket) {
      global $DB, $CFG_GLPI;

      $ID = $ticket->getField('id');
      if (!$ticket->can($ID, READ)) {
         return false;
      }

      $canedit = $ticket->canEdit($ID);
      if (in_array($ticket->fields['status'], Ticket::getSolvedStatusArray())
          || in_array($ticket->fields['status'], Ticket::getClosedStatusArray())) {
         $canedit = false;
      }

      $out = "";
      $out .= "<div class='spaced'>";
      $out .= "<table class='tab_cadre_fixe'>";
      $out .= "<tr class='tab_bg_1'><th colspan='2'>";
      $out .= __('Consumed credits for this ticket', 'credit');
      $out .= "</th></tr></table></div>";

      $number = self::countForItem($ticket);
      $rand   = mt_rand();

      if ($number) {
         $out .= "<div class='spaced'>";

         if ($canedit) {
            $out .= Html::getOpenMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams =  [
               'num_displayed'    => $number,
               'container'        => 'mass'.__CLASS__.$rand,
               'rand'             => $rand,
               'display'          => false,
               'specific_actions' => [
                  'update' => _x('button', 'Update'),
                  'purge'  => _x('button', 'Delete permanently')
               ]
            ];
            $out .= Html::showMassiveActions($massiveactionparams);
         }

         $out .= "<table class='tab_cadre_fixehov'>";
         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';
         if ($canedit) {
            $header_begin  .= "<th width='10'>";
            $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_bottom .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_end    .= "</th>";
         }
         $header_end .= "<th>".__('Voucher name', 'credit')."</th>";
         $header_end .= "<th>".__('Voucher type', 'credit')."</th>";
         $header_end .= "<th>".__('Date consumed', 'credit')."</th>";
         $header_end .= "<th>".__('User consumed', 'credit')."</th>";
         $header_end .= "<th>".__('Quantity consumed', 'credit')."</th>";
         $header_end .= "</tr>";
         $out.= $header_begin.$header_top.$header_end;

         foreach (self::getAllForTicket($ID) as $data) {

            $out .= "<tr class='tab_bg_2'>";
            if ($canedit) {
               $out .= "<td width='10'>";
               $out .= Html::getMassiveActionCheckBox(__CLASS__, $data["id"]);
               $out .= "</td>";
            }

            $credit_entity = new PluginCreditEntity();
            $credit_entity->getFromDB($data['plugin_credit_entities_id']);

            $out .= "<td width='40%' class='center'>";
            $out .= $credit_entity->getName();
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= Dropdown::getDropdownName(PluginCreditType::getTable(),
                                              $credit_entity->getField('plugin_credit_types_id'));
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= Html::convDate($data["date_creation"]);
            $out .= "</td>";

            $showuserlink = 0;
            if (Session::haveRight('user', READ)) {
               $showuserlink = 1;
            }

            $out .= "<td class='center'>";
            $out .= getUserName($data["users_id"], $showuserlink);
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= $data['consumed'];
            $out .= "</td></tr>";
         }

         $out .= $header_begin.$header_bottom.$header_end;
         $out .= "</table>";

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            $out .= Html::showMassiveActions($massiveactionparams);
            $out .= Html::closeForm(false);
         }

      } else {
         $out .= "<p class='center b'>".__('No credit was recorded', 'credit')."</p>";
      }
      $out .= "</div>";

      $out .= "<div class='spaced'>";
      $out .= "<table class='tab_cadre_fixe'>";
      $out .= "<tr class='tab_bg_1'><th colspan='2'>";
      $out .= __('Active credit vouchers for ticket entity', 'credit');
      $out .= "</th></tr></table>";
      $out .= "</div>";
      echo $out;

      $Entity = new Entity();
      $Entity->getFromDB($ticket->fields['entities_id']);
      PluginCreditEntity::showForItemtype($Entity, 'Ticket');
   }

   /**
    * Display voucher consumption fields at the end of a ticket processing form.
    *
    * @param array $params Array with "item" and "options" keys
    *
    * @return void
    */
   static public function displayVoucherInTicketProcessingForm($params) {

      global $CFG_GLPI;

      $item = $params['item'];

      if (!($item instanceof ITILSolution)
          && !($item instanceof TicketTask)
          && !($item instanceof ITILFollowup)) {
         return;
      }

      if (!$item->isNewItem()) {
         // Do not display fields in item update form.
         return;
      }

      $ticket = null;
      if (array_key_exists('parent', $params['options'])
          && $params['options']['parent'] instanceof Ticket) {
         // Ticket can be found in `parent` option for TicketTask.
         $ticket = $params['options']['parent'];
      } else if (array_key_exists('item', $params['options'])
         && $params['options']['item'] instanceof Ticket) {
         // Ticket can be found in `'item'` option for ITILFollowup and ITILSolution.
         $ticket = $params['options']['item'];
      }

      // No parent of type Ticket found, parent might we might be an another
      // type of CommonITILObject so we should exit here
      if ($ticket === null) {
         return;
      }

      $out = "";

      $canedit = $ticket->canEdit($ticket->getID());
      if (in_array($ticket->fields['status'], Ticket::getSolvedStatusArray())
          || in_array($ticket->fields['status'], Ticket::getClosedStatusArray())) {
         $canedit = false;
      }

      $rand = mt_rand();
      if ($canedit) {
         $out .= "<tr><th colspan='2'>";
         $out .= self::getTypeName(2);
         $out .= "</th><th colspan='2'></th></tr>";
         $out .= "<tr><td>";
         $out .= "<label for='plugin_credit_consumed_voucher'>";
         $out .= __('Consume a voucher ?', 'credit');
         $out .= "</label>";
         $out .= "</td><td>";
         $out .= Dropdown::showYesNo('plugin_credit_consumed_voucher', 0, -1, ['display' => false]);
         $out .= "</td><td colspan='2'></td>";
         $out .= "</tr><tr><td>";
         $out .= "<label for='voucher'>";
         $out .= __('Voucher name', 'credit');
         $out .= "</label>";
         $out .= "</td><td>";
         $out .= PluginCreditEntity::dropdown(['name'      => 'plugin_credit_entities_id',
                                               'entity'    => $ticket->getEntityID(),
                                               'display'   => false,
                                               'condition' => ['is_active' => 1],
                                               'rand'      => $rand]);
         $out .= "</td><td colspan='2'></td>";
         $out .= "</tr><tr><td>";
         $out .= "<label for='plugin_credit_quantity'>";
         $out .= __('Quantity consumed', 'credit');
         $out .= "</label>";
         $out .= "</td><td>";
         $out .= "<div id='plugin_credit_quantity_container$rand'></div>";
         $out .= Ajax::updateItemOnSelectEvent(
            "dropdown_plugin_credit_entities_id$rand",
            "plugin_credit_quantity_container$rand",
            Plugin::getWebDir('credit') . "/ajax/dropdownQuantity.php",
            ['entity' => '__VALUE__'],
            false
         );
         $out .= "</td><td colspan='2'></td></tr>";
      }

      echo $out;
   }

   /**
    * Display the detailled list of tickets on which consumption is declared.
    *
    * @param $ID plugin_credit_entities_id
   **/
   static function displayConsumed($ID) {

      $out = "";
      $out .= "<div class='spaced'>";
      $out .= "<table class='tab_cadre_fixe'>";
      $out .= "<tr class='tab_bg_1'><th colspan='2'>";
      $out .= __('Detail of tickets on which consumption is declared', 'credit');
      $out .= "</th></tr></table>";
      $out .= "</div>";

      if (self::getConsumedForCreditEntity($ID) == 0) {
         $out .= "<p class='center b'>";
         $out .= __('No credit was recorded', 'credit');
         $out .= "</p>";
      } else {
         $out .= "<table class='tab_cadre_fixehov'>";
         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';
         $header_end .= "<th>".__('Title')."</th>";
         $header_end .= "<th>".__('Status')."</th>";
         $header_end .= "<th>".__('Type')."</th>";
         $header_end .= "<th>".__('Ticket category')."</th>";
         $header_end .= "<th>".__('Date consumed', 'credit')."</th>";
         $header_end .= "<th>".__('User consumed', 'credit')."</th>";
         $header_end .= "<th>".__('Quantity consumed', 'credit')."</th>";
         $header_end .= "</tr>";
         $out .= $header_begin.$header_top.$header_end;

         foreach (self::getAllForCreditEntity($ID) as $data) {

            $Ticket = new Ticket();
            $Ticket->getFromDB($data['tickets_id']);

            $out .= "<tr class='tab_bg_2'>";
            $out .= "<td class='center'>";
            $out .= $Ticket->getLink(['linkoption' => 'target="_blank"']);
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= Ticket::getStatus($Ticket->fields['status']);
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= Ticket::getTicketTypeName($Ticket->fields['type']);
            $out .= "</td>";

            $itilcat = new ITILCategory();
            if ($itilcat->getFromDB($Ticket->fields['itilcategories_id'])) {
               $out .= "<td class='center'>";
               $out .= $itilcat->getName(['comments' => true]);
               $out .= "</td>";
            } else {
               $out .= "<td class='center'>";
               $out .= __('None');
               $out .= "</td>";
            }

            $out .= "<td class='center'>";
            $out .= Html::convDate($data["date_creation"]);
            $out .= "</td>";

            $showuserlink = 0;
            if (Session::haveRight('user', READ)) {
               $showuserlink = 1;
            }

            $out .= "<td class='center'>";
            $out .= getUserName($data["users_id"], $showuserlink);
            $out .= "</td>";
            $out .= "<td class='center'>";
            $out .= $data['consumed'];
            $out .= "</td></tr>";
         }

         $out .= $header_begin.$header_bottom.$header_end;
         $out .= "</table>";
      }

      echo $out;
   }

   /**
    * Test if consumed voucher is selected and add them.
    *
    * @param CommonDBTM $item Created item
    *
    * @return boolean
    */
   static function consumeVoucher(CommonDBTM $item) {

      if (!is_array($item->input) || !count($item->input)) {
         return;
      }

      $ticketId = null;
      if (array_key_exists('tickets_id', $item->fields)) {
         // Ticket ID can be found in `tickets_id` field for TicketTask.
         $ticketId = $item->fields['tickets_id'];
      } else if (array_key_exists('itemtype', $item->fields)
                 && array_key_exists('items_id', $item->fields)
                 && 'Ticket' == $item->fields['itemtype']) {
         // Ticket ID can be found in `items_id` field for ITILFollowup and ITILSolution.
         $ticketId = $item->fields['items_id'];
      }

      $ticket = new Ticket();
      if (null === $ticketId || !$ticket->getFromDB($ticketId)) {
         return;
      }

      if (!is_numeric(Session::getLoginUserID(false))
          || !Session::haveRightsOr('ticket', [Ticket::STEAL, Ticket::OWN])) {
         return;
      }

      if (!isset($item->input['plugin_credit_consumed_voucher'])
          || $item->input['plugin_credit_consumed_voucher'] != 1) {
         return;
      }

      if (!isset($item->input['plugin_credit_entities_id'])
          || $item->input['plugin_credit_entities_id'] == 0) {
         Session::addMessageAfterRedirect(
            __('You must provide a credit voucher', 'credit'),
            true,
            ERROR
         );
         return;
      }

      $credit_ticket = new self();

      $credit_entity = new PluginCreditEntity();
      $credit_entity->getFromDB($item->input['plugin_credit_entities_id']);

      $quantity_sold      = (int)$credit_entity->fields['quantity'];
      $quantity_consumed  = $credit_ticket->getConsumedForCreditEntity($item->input['plugin_credit_entities_id']);
      $quantity_remaining = max(0, $quantity_sold - $quantity_consumed);

      if (0 !== $quantity_sold && $quantity_remaining < $item->input['plugin_credit_quantity']) {
         if ($credit_entity->getField('overconsumption_allowed')) {
            Session::addMessageAfterRedirect(
               sprintf(
                  __('Quantity consumed exceeds remaining credits: %d', 'credit'),
                  $quantity_remaining
               ),
               true,
               WARNING
            );
         } else {
            Session::addMessageAfterRedirect(
               sprintf(
                  __('Quantity consumed exceeds remaining credits: %d', 'credit'),
                  $quantity_remaining
               ),
               true,
               ERROR
            );
            return;
         }
      }

      $input = [
         'tickets_id'                => $ticket->getID(),
         'plugin_credit_entities_id' => $item->input['plugin_credit_entities_id'],
         'consumed'                  => $item->input['plugin_credit_quantity'],
         'users_id'                  => Session::getLoginUserID(),
      ];
      if ($credit_ticket->add($input)) {
         Session::addMessageAfterRedirect(
            __('Credit voucher successfully added.', 'credit'),
            true,
            INFO
         );
      }
   }


   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => 881,
         'table'    => self::getTable(),
         'field'    => 'date_creation',
         'name'     => __('Date consumed', 'credit'),
         'datatype' => 'date',
      ];

      $tab[] = [
         'id'       => 882,
         'table'    => self::getTable(),
         'field'    => 'consumed',
         'name'     => __('Quantity consumed', 'credit'),
         'datatype' => 'number',
         'min'      => 1,
         'max'      => 1000000,
         'step'     => 1,
         'toadd'    => [0 => __('Unlimited')],
      ];

      $tab[] = [
         'id'       => 883,
         'table'    => PluginCreditEntity::getTable(),
         'field'    => 'name',
         'name'     => __('Credit vouchers', 'credit'),
         'datatype' => 'dropdown',
      ];

      return $tab;
   }

   /**
    * Install all necessary table for the plugin
    *
    * @return boolean True if success
    */
   static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();

      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL auto_increment,
                     `tickets_id` int(11) NOT NULL DEFAULT '0',
                     `plugin_credit_entities_id` int(11) NOT NULL DEFAULT '0',
                     `date_creation` timestamp NULL DEFAULT NULL,
                     `consumed` int(11) NOT NULL DEFAULT '0',
                     `users_id` int(11) NOT NULL DEFAULT '0',
                     PRIMARY KEY (`id`),
                     KEY `tickets_id` (`tickets_id`),
                     KEY `plugin_credit_entities_id` (`plugin_credit_entities_id`),
                     KEY `date_creation` (`date_creation`),
                     KEY `consumed` (`consumed`),
                     KEY `users_id` (`users_id`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      } else {

         // Fix #1 in 1.0.1 : change tinyint(1) to int(11) for tickets_id
         $migration->changeField($table, 'tickets_id', 'tickets_id', 'integer');

         // Change tinyint to int
         $migration->changeField(
            $table,
            'plugin_credit_entities_id',
            'plugin_credit_entities_id',
            'integer'
         );
         $migration->changeField($table, 'users_id', 'users_id', 'integer');

         //execute the whole migration
         $migration->executeMigration();
      }
   }

   /**
    * Uninstall previously installed table of the plugin
    *
    * @return boolean True if success
    */
   static function uninstall(Migration $migration) {

      $table = self::getTable();
      $migration->displayMessage("Uninstalling $table");
      $migration->dropTable($table);
   }
}
