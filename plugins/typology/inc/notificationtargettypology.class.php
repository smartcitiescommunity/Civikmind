<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 typology plugin for GLPI
 Copyright (C) 2009-2016 by the typology Development Team.

 https://github.com/InfotelGLPI/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of typology.

 typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget

/**
 * Class PluginTypologyNotificationTargetTypology
 */
class PluginTypologyNotificationTargetTypology extends NotificationTarget {

   /**
    * Return main notification events for the object type
    * Internal use only => should use getAllEvents
    *
    * @return an array which contains : event => event label
    **/
   function getEvents() {

      return  ['AlertNotValidatedTypology' => __('Elements not match with the typology', 'typology')];
   }

   /**
    * Get all data needed for template processing
    * Provides minimum information for alerts
    * Can be overridden by each NotificationTartget class if needed
    *
    * @param string $event   Event name
    * @param array  $options Options
    *
    * @return void
    **/
   function addDataForTemplate($event, $options = []) {
      global $CFG_GLPI;

      if ($event == 'AlertNotValidatedTypology') {

         $this->data['##typology.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $options['entities_id']);
         $this->data['##lang.typology.entity##'] =__('Entity');
         $this->data['##typology.action##'] = __('Elements not match with the typology', 'typology');

         $this->data['##lang.typology.name##'] = PluginTypologyTypology::getTypeName(1);
         $this->data['##lang.typology.itemtype##'] = __('Type');
         $this->data['##lang.typology.items_id##'] = __('Name');
         $this->data['##lang.typology.error##'] = __('Error');
         $this->data['##lang.typology.url##'] = __('Link to the typology', 'typology');
         $this->data['##lang.typology.itemurl##'] = __('Link to the element', 'typology');
         $this->data['##lang.typology.itemuser##'] = __('User');
         $this->data['##lang.typology.itemlocation##'] = __('Location');

         $dbu = new DbUtils();
         foreach ($options['items'] as $id => $item) {
            $tmp = [];

            $tmp['##typology.name##'] = $item['name'];
            $itemtype = new $item['itemtype']();
            $itemtype->getFromDB($item["items_id"]);
            $tmp['##typology.itemtype##'] = $itemtype->getTypeName();
            $tmp['##typology.items_id##'] = $itemtype->getName();
            $tmp['##typology.error##'] = PluginTypologyTypology_Item::displayErrors($item['error'], false);
            $tmp['##typology.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=PluginTypologyTypology_".
               $item['plugin_typology_typologies_id']);
            $tmp['##typology.itemurl##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=".
               Toolbox::strtolower($item['itemtype'])."_".$item["items_id"]);
            $tmp['##typology.itemuser##'] = $dbu->getUserName($itemtype->fields["users_id"]);
            $tmp['##typology.itemlocation##'] = Dropdown::getDropdownName("glpi_locations",
               $itemtype->fields['locations_id']);

            $this->data['typologyitems'][] = $tmp;
         }
      }
   }

   /**
    * @return array|void
    */
   function getTags() {

      $tags = ['typology.name'             => PluginTypologyTypology::getTypeName(1),
                   'typology.itemtype'          => __('Type'),
                   'typology.items_id'          => __('Name'),
                   'typology.error'             => __('Error'),
                   'typology.url'               => __('Link to the typology', 'typology'),
                   'typology.itemurl'           => __('Link to the element', 'typology'),
                   'typology.itemuser'          => __('User'),
                   'typology.itemlocation'      => __('Location')];
      foreach ($tags as $tag => $label) {
         $this->addTagToList(['tag'=>$tag,'label'=>$label,
                                   'value'=>true]);
      }
      asort($this->tag_descriptions);
   }

   public static function install() {
      global $DB;

      $template     = new NotificationTemplate();
      $query_id     = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginTypologyTypology'
                       AND `name` = 'Alert no validated typology'";
      $result       = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp = [
            'name'     => 'Alert no validated typology',
            'itemtype' => 'PluginTypologyTypology',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $dbu = new DbUtils();
         $translation = new NotificationTemplateTranslation();
         if (!$dbu->countElementsInTable($translation->getTable(),
                                         ["notificationtemplates_id" => $templates_id])) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##typology.action## : ##typology.entity##';
            $tmp['content_text']            = '##FOREACHitems##
   ##lang.typology.name## : ##typology.name##
   ##lang.typology.itemtype## : ##typology.itemtype##
   ##lang.typology.items_id## : ##typology.items_id##
   ##lang.typology.itemlocation## : ##typology.itemlocation##
   ##lang.typology.itemuser## : ##typology.itemuser##
   ##lang.typology.error## : ##typology.error##
   ##ENDFOREACHitems##';
            $tmp['content_html']             = '&lt;table class="tab_cadre" border="1" cellspacing="2" cellpadding="3"&gt;
   &lt;tbody&gt;
   &lt;tr&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.typology.name##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.typology.itemtype##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.typology.items_id##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.typology.itemlocation##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.typology.itemuser##&lt;/span&gt;&lt;/td&gt;
   &lt;td style="text-align: left;" bgcolor="#cccccc"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##lang.typology.error##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##FOREACHtypologyitems##
   &lt;tr&gt;
   &lt;td&gt;&lt;a href="##typology.url##" target="_blank"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##typology.name##&lt;/span&gt;&lt;/a&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##typology.itemtype##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;a href="##typology.itemurl##" target="_blank"&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##typology.items_id##&lt;/span&gt;&lt;/a&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##typology.itemlocation##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##typology.itemuser##&lt;/span&gt;&lt;/td&gt;
   &lt;td&gt;&lt;span style="font-family: Verdana; font-size: 11px; text-align: left;"&gt;##typology.error##&lt;/span&gt;&lt;/td&gt;
   &lt;/tr&gt;
   ##ENDFOREACHtypologyitems##
   &lt;/tbody&gt;
   &lt;/table&gt;';

            $translation->add($tmp);
         }

         $notifs = [
            'Alert no validated typology'     => 'AlertNotValidatedTypology',
         ];
         $notification = new Notification();
         $notificationtemplate = new Notification_NotificationTemplate();
         $dbu = new DbUtils();
         foreach ($notifs as $label => $name) {
            if (!$dbu->countElementsInTable("glpi_notifications",
                                            ["itemtype" => 'PluginTypologyTypology',
                                             "event"    => $name])) {
               $tmp = [
                  'name'                     => $label,
                  'entities_id'              => 0,
                  'itemtype'                 => 'PluginTypologyTypology',
                  'event'                    => $name,
                  'comment'                  => '',
                  'is_recursive'             => 1,
                  'is_active'                => 1,
                  'date_mod'                 => $_SESSION['glpi_currenttime'],
               ];
               $notification_id = $notification->add($tmp);

               $notificationtemplate->add(['notificationtemplates_id' => $templates_id,
                                           'mode'                     => 'mailing',
                                           'notifications_id'         => $notification_id]);
            }
         }
      }

   }
}
