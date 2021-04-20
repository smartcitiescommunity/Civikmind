<?php
/**
 * @version $Id: document_item.class.php 338 2021-03-30 12:36:31Z yllen $
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
 @author    David Durieux, Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2021 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2014

 --------------------------------------------------------------------------
*/

class PluginBehaviorsDocument_Item {


   static function addEvents(NotificationTargetTicket $target) {

      $config = PluginBehaviorsConfig::getInstance();

      if ($config->getField('add_notif')) {
         Plugin::loadLang('behaviors');

         $target->events['plugin_behaviors_document_itemnew']
            = sprintf(__('%1$s - %2$s'), __('Behaviours', 'behaviors'),
                      __('Add document to ticket', 'behaviors'));

         $target->events['plugin_behaviors_document_itemdel']
            = sprintf(__('%1$s - %2$s'), __('Behaviours', 'behaviors'),
                      __('Delete document to ticket', 'behaviors'));
      }
   }


   static function afterAdd(Document_Item $document_item) {

      $config = PluginBehaviorsConfig::getInstance();
      if ($config->getField('add_notif')
          && (isset($_POST['itemtype']))
          && (isset($document_item->input['itemtype']))
          && ($document_item->input['itemtype'] == 'Ticket')
          && ($_POST['itemtype'] == 'Ticket')) {// prevent not in case of create ticket
         $ticket = new Ticket();
         $ticket->getFromDB($document_item->input['items_id']);

         NotificationEvent::raiseEvent('plugin_behaviors_document_itemnew', $ticket);
      }
   }


   static function afterPurge(Document_Item $document_item) {

      $config = PluginBehaviorsConfig::getInstance();
      if ($config->getField('add_notif')
          && (isset($document_item->input['itemtype']))
          && ($document_item->fields['itemtype'] == 'Ticket')
          && isset($_POST['item'])) { // prevent not use in case of purge ticket

         $ticket = new Ticket();
         $ticket->getFromDB($document_item->fields['items_id']);

         NotificationEvent::raiseEvent('plugin_behaviors_document_itemdel', $ticket);
      }
   }

}
