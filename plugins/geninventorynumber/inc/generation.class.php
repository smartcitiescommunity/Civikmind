<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z orthagh $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   geninventorynumber
 @author    the geninventorynumber plugin team
 @copyright Copyright (c) 2008-2017 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/pluginsGLPI/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2008
 ---------------------------------------------------------------------- */


class PluginGeninventorynumberGeneration {

   static function autoName($config, CommonDBTM $item) {
      $template = $config['template'];
      $len      = strlen($template);
      $suffix = strpos($template, '&lt;');

      if ($len > 8
         && $suffix !== false
            && substr($template, $len - 4, 4) === '&gt;') {

         $autoNum = substr($template, $suffix+4, $len-(4+$suffix+4));
         $mask    = '';

         if (preg_match("/\\#{1,10}/", $autoNum, $mask)) {
            $serial = (isset ($item->fields['serial']) ? $item->fields['serial'] : '');
            $name   = (isset ($item->fields['name']) ? $item->fields['name'] : '');

            $global  = strpos($autoNum, '\\g') !== false && $type != INFOCOM_TYPE ? 1 : 0;
            $autoNum = str_replace( ['\\y', '\\Y', '\\m', '\\d', '_', '%', '\\g', '\\s', '\\n'],
                                    [date('y'), date('Y'), date('m'), date('d'), '\\_',
                                           '\\%', '', $serial, $name], $autoNum);
            $mask    = $mask[0];
            $pos     = strpos($autoNum, $mask) + 1;
            $len     = strlen($mask);
            $like    = str_replace('#', '_', $autoNum);

            if ($config['use_index']) {
               $index = PluginGeninventorynumberConfig::getNextIndex();
            } else {
               $index = PluginGeninventorynumberConfigField::getNextIndex($config['itemtype']);
            }

            $next_number = str_pad($index, $len, '0', STR_PAD_LEFT);
            $prefix      = substr($template, 0, $suffix);
            $template    = $prefix . str_replace( [$mask, '\\_', '\\%'],
                            [$next_number,  '_',  '%'], $autoNum);
         }
      }
      return $template;
   }

   /**
    * @override CommonDBTM::preItemAdd
    */
   static function preItemAdd(CommonDBTM $item) {
      $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType(get_class($item));

      if (in_array(get_class($item), PluginGeninventorynumberConfigField::getEnabledItemTypes())) {
         if ((!Session::haveRight("plugin_geninventorynumber", CREATE))) {
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(__('You can\'t modify inventory number',
                                                'geninventorynumber'), true, ERROR);
            }
            return;
         }

         if (PluginGeninventorynumberConfig::isGenerationActive()
            && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))) {
            $item->input['otherserial'] = self::autoName($config, $item);
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(__('An inventory number have been generated', 'geninventorynumber'), true);
            }

            if ($config['use_index']) {
               PluginGeninventorynumberConfig::updateIndex();
            } else {
               PluginGeninventorynumberConfigField::updateIndex(get_class($item));
            }
         }
      }
   }

   /**
    * @override CommonDBTM::preItemUpdate
    */
   static function preItemUpdate(CommonDBTM $item) {
      if (!Session::haveRight("plugin_geninventorynumber", UPDATE)) {
         return;
      }

      if (PluginGeninventorynumberConfig::isGenerationActive()
         && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))
            && !isset($item->input['massiveaction'])) {

         if (isset($item->fields['otherserial'])
            && isset($item->input['otherserial'])
               && $item->fields['otherserial'] != $item->input['otherserial']) {
            $item->input['otherserial'] = $item->fields['otherserial'];
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(
                  __('You can\'t modify inventory number', 'geninventorynumber'),
                  true, ERROR);
            }
         }
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $GENINVENTORYNUMBER_TYPES;

      // KK TODO: check if MassiveAction itemtypes are concerned
      //if (in_array ($options['itemtype'], $GENINVENTORYNUMBER_TYPES)) {
      switch ($ma->action) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_overwrite" :
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
               _sx('button', 'Add') . "\" >";
            break;
         default :
            break;
      }
       return true;
   }

   /**
    * Generate numbers from a massive update
    *
    * @since 9.1+1.0
    *
    * @param CommonDBTM $item Existing item to update
    */
   static function doMassiveUpdate(CommonDBTM $item) {
      $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType(get_class($item));

      if (in_array(get_class($item), PluginGeninventorynumberConfigField::getEnabledItemTypes())) {
         $tmp    = clone $item;
         $values = ['id' => $item->getID()];

         if (PluginGeninventorynumberConfig::isGenerationActive()
            && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))) {
            $values['otherserial']   = self::autoName($config, $item);
            $values['massiveaction'] = true;
            $tmp->update($values);

            if ($config['use_index']) {
               PluginGeninventorynumberConfig::updateIndex();
            } else {
               PluginGeninventorynumberConfigField::updateIndex(get_class($item));
            }
            return ['ok'];
         } else {
            $values['otherserial'] = '';
            $tmp->update($values);
         }
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      $results = ['ok' => 0, 'ko' => 0, 'noright' => 0, 'messages' => []];

      switch ($ma->action) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_overwrite" :
            //KK Not sure we could have multiple itemtimes
            foreach ($ma->items as $itemtype => $val) {
               foreach ($val as $key => $item_id) {
                  $item = new $itemtype;
                  $item->getFromDB($item_id);
                  if ($ma->action == "plugin_geninventorynumber_generate") {
                     //Only generates inventory number for object without it !
                     if (isset ($item->fields["otherserial"])
                        && ($item->fields["otherserial"] == "")) {

                        if (!Session::haveRight("plugin_geninventorynumber", CREATE)) {
                           $results['noright']++;
                        } else {
                           $myresult = self::doMassiveUpdate($item);
                           $results[$myresult[0]]++;
                        }
                     } else {
                        $results['ko']++;
                     }
                  }

                  //Or is overwrite action is selected
                  if (($ma->action == "plugin_geninventorynumber_overwrite")) {
                     if (!Session::haveRight("plugin_geninventorynumber", UPDATE)) {
                        $results['noright']++;
                     } else {
                        $myresult = self::doMassiveUpdate($item);
                        $results[$myresult[0]]++;
                     }
                  }
               }
            }
            break;

         default :
            break;
      }
      $ma->results=$results;
   }
}
