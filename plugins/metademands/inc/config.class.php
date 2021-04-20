<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMetademandsConfig
 */
class PluginMetademandsConfig extends CommonDBTM {

   static $rightname = 'plugin_metademands';

   private static $instance;

   /**
    * Have I the global right to "view" the Object
    *
    * Default is true and check entity if the objet is entity assign
    *
    * May be overloaded if needed
    *
    * @return bool|int
    */
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return bool
    */
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * @return bool
    */
   function showForm() {
      if (!$this->canCreate() || !$this->canView()) {
         return false;
      }

      $config = PluginMetademandsConfig::getInstance();

      echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL('PluginMetademandsConfig') . "'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='6'>" . __('Configuration of the meta-demand plugin', 'metademands') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Enable the update / add of simple ticket to metademand', 'metademands');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('simpleticket_to_metademand', $config['simpleticket_to_metademand']);
      echo "</td>";

      echo "<td>";
      echo __("Enable display metademands via icons", 'metademands');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("display_type", $config['display_type']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Parent ticket tag', 'metademands');
      echo "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "parent_ticket_tag", ['value' => stripslashes($config["parent_ticket_tag"])]);
      echo "</td>";

      echo "<td>";
      echo __('Son ticket tag', 'metademands');
      echo "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "son_ticket_tag", ['value' => stripslashes($config["son_ticket_tag"])]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Show requester informations', 'metademands');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('show_requester_informations', $config['show_requester_informations']);
      echo "</td>";

      echo "<td>";
      echo __('Create PDF', 'metademands');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('create_pdf', $config['create_pdf']);
      //echo "<input type='hidden' name='create_pdf' value='0'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>";
      echo __('Childs tickets get parent content', 'metademands');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('childs_parent_content', $config['childs_parent_content']);
      echo "</td>";

      echo "<td>";
      echo __('Display metademands list into ServiceCatalog plugin', 'metademands');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('display_buttonlist_servicecatalog', $config['display_buttonlist_servicecatalog']);
      echo "</td>";
      echo "</tr>";

      if ($config['display_buttonlist_servicecatalog'] == 1) {

         echo "<tr><th colspan='6'>" . __('Configuration of the Service Catalog plugin', 'metademands') . "</th></tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'>" . __('Title for Service Catalog widget', 'metademands') . "</td>";
         echo "<td colspan='2'>";
         Html::textarea(['name'            => 'title_servicecatalog',
                         'value'           => $config['title_servicecatalog'],
                         'enable_richtext' => false,
                         'cols'            => 80,
                         'rows'            => 3]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'>" . __('Comment for Service Catalog widget', 'metademands') . "</td>";
         echo "<td colspan='2'>";
         Html::textarea(['name'            => 'comment_servicecatalog',
                         'value'           => $config['comment_servicecatalog'],
                         'enable_richtext' => false,
                         'cols'            => 80,
                         'rows'            => 3]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'>";
         echo __('Icon for Service Catalog widget', 'metademands');
         echo "<br>" . __('Example : ', 'metademands') . "fas fa-share-alt";
         echo "</td>";
         echo "<td colspan='2'>";
         $opt = [
            'value'     => $config['fa_servicecatalog'],
            'maxlength' => 250,
            'size'      => 80,
         ];
         echo Html::input('fa_servicecatalog', $opt);
         if (isset($config['fa_servicecatalog'])
             && !empty($config['fa_servicecatalog'])) {
            $icon = $config['fa_servicecatalog'];
            echo "<br><br><i class='fas-sc sc-fa-color $icon fa-3x' ></i>";
         }
         echo "</td>";
         echo "</tr>";
      }
      echo "<tr><td class='tab_bg_2 center' colspan='6'><input type=\"submit\" name=\"update_config\" class=\"submit\"
         value=\"" . _sx('button', 'Update') . "\" ></td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }

   /**
    * @return bool|mixed
    */
   public static function getInstance() {
      if (!isset(self::$instance)) {
         $temp = new PluginMetademandsConfig();

         $data = $temp->getConfigFromDB();
         if ($data) {
            self::$instance = $data;
         }
      }

      return self::$instance;
   }

   /**
    * getConfigFromDB : get all configs in the database
    *
    * @param array $options
    *
    * @return bool|mixed
    */
   function getConfigFromDB($options = []) {
      $table = $this->getTable();
      $where = [];
      if (isset($options['where'])) {
         $where = $options['where'];
      }
      $dbu        = new DbUtils();
      $dataConfig = $dbu->getAllDataFromTable($table, $where);
      if (count($dataConfig) > 0) {
         return array_shift($dataConfig);
      }

      return false;
   }
}
