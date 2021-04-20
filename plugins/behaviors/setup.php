<?php
/**
 * @version $Id: setup.php 338 2021-03-30 12:36:31Z yllen $
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

// Init the hooks of the plugins -Needed
function plugin_init_behaviors() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   Plugin::registerClass('PluginBehaviorsConfig', ['addtabon' => 'Config']);
   $PLUGIN_HOOKS['config_page']['behaviors'] = 'front/config.form.php';

   $PLUGIN_HOOKS['item_add']['behaviors'] =
      ['Ticket_User'        => ['PluginBehaviorsTicket_User',       'afterAdd'],
       'Group_Ticket'       => ['PluginBehaviorsGroup_Ticket',      'afterAdd'],
       'Supplier_Ticket'    => ['PluginBehaviorsSupplier_Ticket',   'afterAdd'],
       'Document_Item'      => ['PluginBehaviorsDocument_Item',     'afterAdd'],
       'ITILSolution'       => ['PluginBehaviorsITILSolution',      'afterAdd']];

   $PLUGIN_HOOKS['item_update']['behaviors'] =
      ['Ticket'             => ['PluginBehaviorsTicket',            'afterUpdate']];

   $PLUGIN_HOOKS['pre_item_add']['behaviors'] =
      ['Ticket'             => ['PluginBehaviorsTicket',            'beforeAdd'],
       'ITILSolution'       => ['PluginBehaviorsITILSolution',      'beforeAdd'],
       'TicketTask'         => ['PluginBehaviorsTickettask',        'beforeAdd'],
       'Change'             => ['PluginBehaviorsChange',            'beforeAdd']];

   $PLUGIN_HOOKS['post_prepareadd']['behaviors'] =
      ['Ticket'             => ['PluginBehaviorsTicket',            'afterPrepareAdd']];

   $PLUGIN_HOOKS['pre_item_update']['behaviors'] =
      ['Problem'            => ['PluginBehaviorsProblem',           'beforeUpdate'],
       'Ticket'             => ['PluginBehaviorsTicket',            'beforeUpdate'],
       'ITILSolution'       => ['PluginBehaviorsITILSolution',      'beforeUpdate'],
       'TicketTask'         => ['PluginBehaviorsTickettask',        'beforeUpdate']];

   $PLUGIN_HOOKS['pre_item_purge']['behaviors'] =
      ['Computer'           => ['PluginBehaviorsComputer',          'beforePurge']];

   $PLUGIN_HOOKS['item_purge']['behaviors'] =
      ['Document_Item'      => ['PluginBehaviorsDocument_Item',     'afterPurge']];

   // Notifications
   $PLUGIN_HOOKS['item_get_events']['behaviors'] =
      ['NotificationTargetTicket' => ['PluginBehaviorsTicket',      'addEvents']];

   $PLUGIN_HOOKS['item_add_targets']['behaviors'] =
      ['NotificationTargetTicket' => ['PluginBehaviorsTicket',      'addTargets']];

   $PLUGIN_HOOKS['item_action_targets']['behaviors'] =
      ['NotificationTargetTicket' => ['PluginBehaviorsTicket',      'addActionTargets']];

   $PLUGIN_HOOKS['pre_item_form']['behaviors'] = [PluginBehaviorsITILSolution::class, 'messageWarningSolution'];
   $PLUGIN_HOOKS['post_item_form']['behaviors'] = [PluginBehaviorsITILSolution::class, 'deleteAddSolutionButtton'];

   // End init, when all types are registered
   $PLUGIN_HOOKS['post_init']['behaviors'] = ['PluginBehaviorsCommon', 'postInit'];

   $PLUGIN_HOOKS['csrf_compliant']['behaviors'] = true;

   foreach ($CFG_GLPI["asset_types"] as $type) {
      $PLUGIN_HOOKS['item_can']['behaviors'][$type] = [$type => ['PluginBehaviorsConfig', 'item_can']];
   }

   $PLUGIN_HOOKS['add_default_where']['behaviors'] = ['PluginBehaviorsConfig', 'add_default_where'];

}


function plugin_version_behaviors() {

   return ['name'           => __('Behaviours', 'behaviors'),
           'version'        => '2.5.0',
           'license'        => 'AGPLv3+',
           'author'         => 'Remi Collet, Nelly Mahu-Lasson',
           'homepage'       => 'https://forge.glpi-project.org/projects/behaviors',
           'minGlpiVersion' => '9.5.4',
           'requirements'   => ['glpi' => ['min' => '9.5.4',
                                           'max' => '9.6']]];
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_behaviors_check_config($verbose=false) {
   return true;
}
