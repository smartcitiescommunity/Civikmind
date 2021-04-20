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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_credit_install() {

   $migration = new Migration(PLUGIN_CREDIT_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginCredit' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'install')) {
            $classname::install($migration);
         }
      }
   }

   CronTask::register(
      'PluginCreditEntity',
      'creditexpired',
      DAY_TIMESTAMP,
      [
         'comment' => '',
         'mode' => CronTask::MODE_EXTERNAL,
      ]
   );

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_credit_uninstall() {

   $migration = new Migration(PLUGIN_CREDIT_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginCredit' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'uninstall')) {
            $classname::uninstall($migration);
         }
      }
   }
   return true;
}

/**
 * Define Dropdown tables to be manage in GLPI :
 */
function plugin_credit_getDropdown() {
   return ['PluginCreditType' => _n('Credit voucher type', 'Credit vouchers types',
                                    Session::getPluralNumber(),
                                    'credit')];
}

function plugin_credit_get_datas(NotificationTargetTicket $target) {

   global $DB;

   $target->data['##lang.credit.voucher##'] = __('Credit voucher', 'credit');
   $target->data['##lang.credit.used##']    = __('Quantity consumed', 'credit');
   $target->data['##lang.credit.left##']    = __('Quantity remaining', 'credit');

   $id = $target->data['##ticket.id##'];
   $ticket=new Ticket();
   $ticket->getFromDB($id);
   $entity_id=$ticket->fields['entities_id'];

   $query = "SELECT
         `glpi_plugin_credit_entities`.`name`,
         `glpi_plugin_credit_entities`.`quantity`,
         (SELECT SUM(`glpi_plugin_credit_tickets`.`consumed`) FROM `glpi_plugin_credit_tickets` WHERE `glpi_plugin_credit_tickets`.`plugin_credit_entities_id` = `glpi_plugin_credit_entities`.`id` AND `glpi_plugin_credit_tickets`.`tickets_id` = {$id}) AS `consumed_on_ticket`,
         (SELECT SUM(`glpi_plugin_credit_tickets`.`consumed`) FROM `glpi_plugin_credit_tickets` WHERE `glpi_plugin_credit_tickets`.`plugin_credit_entities_id` = `glpi_plugin_credit_entities`.`id`) AS  `consumed_total`
      FROM `glpi_plugin_credit_entities`
      WHERE `is_active`=1 and `entities_id`={$entity_id}";

   foreach ($DB->request($query) as $credit) {
      $target->data["credit.ticket"][] = [
         '##credit.voucher##' => $credit['name'],
         '##credit.used##'    => (int)$credit['consumed_on_ticket'],
         '##credit.left##'    => (int)$credit['quantity'] - (int)$credit['consumed_total'],
      ];
   }
}
