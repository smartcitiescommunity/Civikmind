<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginActivityTicketTask extends CommonDBTM {

   var $dohistory = false;

   static $rightname = "plugin_activity";

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return _n('Cumulative ticket task', 'Cumulative ticket tasks', $nb, 'activity');
   }

   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         ['tickettasks_id' => $item->getField('id')]
      );
   }


   static public function postForm($params) {
      $item       = $params['item'];
      switch ($item->getType()) {
         case 'TicketTask':
            $self = new self();
            if ($item->getID() && !empty($item->getID())) {
               $self->getFromDBForTask($item->getID());
            } else {
               $self->getEmpty();
            }

            $is_cra_default = 0;
            $opt            = new PluginActivityOption();
            $opt->getFromDB(1);
            if ($opt) {
               $is_cra_default = $opt->fields['is_cra_default'];
            }

            if (Session::haveRight("plugin_activity_statistics", 1)) {
               echo "<tr class='tab_bg_1'>";
               echo "<td colspan='3'></td>";
               echo '<td>';
               echo "<div id='is_oncra_" . $item->getID() . "' class='fa-label'>
               <i class='far fa-flag fa-fw'
                  title='" . __('Use in CRA', 'activity') . "'></i>";
               Dropdown::showYesNo('is_oncra',
                                   (isset($self->fields['id']) && $self->fields['id']) > 0 ? $self->fields['is_oncra'] : $is_cra_default,
                                   -1,
                                   ['value' => 1]);
               echo '</div></td>';
               echo '</tr>';

            } else {
               echo "<input type='hidden' value='1' name='is_oncra'>";
            }
            break;
      }
   }


   function getFromDBForTask($tickettasks_id) {
      $dbu  = new DbUtils();
      $data = $dbu->getAllDataFromTable($this->getTable(), [$dbu->getForeignKeyFieldForTable('glpi_tickettasks') => $tickettasks_id]);

      $this->fields = array_shift($data);
   }

   function isDisplayableOnCra($tickettasks_id) {
      $data = $this->getFromDBForTask($tickettasks_id);
      if ($data['is_oncra']) {
         return true;
      }

      return false;
   }



   static function setTicketTask(TicketTask $item) {

      if (self::canCreate()) {
         $tickettask = new PluginActivityTicketTask();
         $is_exist   = $tickettask->getFromDBByCrit(["tickettasks_id=" . $item->getID()]);



         if (isset($item->input['id'])
             && isset($item->input['is_oncra'])) {
            $tickettask->getFromDBForTask($item->input['id']);


            if (!empty($tickettask->fields)) {
               $tickettask->update(['id'             => $tickettask->fields['id'],
                                    'is_oncra'       => $item->input['is_oncra'],
                                    'tickettasks_id' => $item->input['id']]);

            } else if (!$is_exist) {
               $tickettask->add(['is_oncra'       => $item->input['is_oncra'],
                                 'tickettasks_id' => $item->getID()]);
            }
         } else {
            $is_cra_default = 0;
            $opt            = new PluginActivityOption();
            $opt->getFromDB(1);
            if ($opt) {
               $is_cra_default = $opt->fields['is_cra_default'];
            }
            if (!$is_exist) {
               $tickettask->add(['is_oncra'       => isset($item->input['is_oncra']) ? $item->input['is_oncra'] : $is_cra_default,
                                 'tickettasks_id' => $item->getID()]);
            }
         }
      }
   }

   static function taskUpdate(TicketTask $item) {

      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }
      self::setTicketTask($item);
   }

   static function taskAdd(TicketTask $item) {

      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }
      self::setTicketTask($item);
   }
}