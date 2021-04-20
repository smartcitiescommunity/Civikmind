<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesReportConfig
 */
class PluginResourcesReportConfig extends CommonDBTM {

   static $rightname = 'plugin_resources';

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {

      return _n('Notification', 'Notifications', $nb);
   }

   /**
    * Have I the global right to "view" the Object
    *
    * Default is true and check entity if the objet is entity assign
    *
    * May be overloaded if needed
    *
    * @return booleen
    **/
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * Get Tab Name used for itemtype
    *
    * NB : Only called for existing object
    *      Must check right on what will be displayed + template
    *
    * @since 0.83
    *
    * @param CommonGLPI $item         Item on which the tab need to be displayed
    * @param boolean    $withtemplate is a template object ? (default 0)
    *
    *  @return string tab name
    **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'PluginResourcesResource' && $this->canView()) {
         return self::getTypeName(2);
      }
      return '';
   }

   /**
    * show Tab content
    *
    * @since 0.83
    *
    * @param CommonGLPI $item         Item on which the tab need to be displayed
    * @param integer    $tabnum       tab number (default 1)
    * @param boolean    $withtemplate is a template object ? (default 0)
    *
    * @return boolean
    **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType() == 'PluginResourcesResource') {
         $ID = $item->getField('id');
         self::showReports($ID, $withtemplate);

         if ($item->can($ID, UPDATE) && !self::checkIfReportsExist($ID)) {
            $self = new self();
            $self->showForm("", ['plugin_resources_resources_id' => $ID,
                                      'target'                        => $CFG_GLPI['root_doc'] . "/plugins/resources/front/reportconfig.form.php"]);
         }

         if ($item->can($ID, UPDATE) && self::checkIfReportsExist($ID) && !$withtemplate) {
            PluginResourcesResource::showReportForm(['id'     => $ID,
                                                          'target' => $CFG_GLPI['root_doc'] . "/plugins/resources/front/resource.form.php"]);
         }
      }
      return true;
   }

   /**
    * Prepare input datas for adding the item
    *
    * @param array $input datas used to add the item
    *
    * @return array the modified $input array
    **/
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_resources_resources_id']) || $input['plugin_resources_resources_id'] <= 0) {
         return false;
      }
      return $input;
   }

   /**
    * @param $ID
    *
    * @return bool
    */
   static function checkIfReportsExist($ID) {

      $restrict = ["plugin_resources_resources_id" => $ID];
      $dbu      = new DbUtils();
      $reports  = $dbu->getAllDataFromTable("glpi_plugin_resources_reportconfigs", $restrict);

      if (!empty($reports)) {
         foreach ($reports as $report) {
            return $report["id"];
         }
      } else {
         return false;
      }
   }

   /**
    * @param $plugin_resources_resources_id
    *
    * @return bool
    */
   function getFromDBByResource($plugin_resources_resources_id) {
      global $DB;

      $query = "SELECT * FROM `" . $this->getTable() . "`
                  WHERE `plugin_resources_resources_id` = '" . $plugin_resources_resources_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   /**
    * Duplicate item resources from an item template to its clone
    *
    * @since version 0.84
    *
    * @param $itemtype     itemtype of the item
    * @param $oldid        ID of the item to clone
    * @param $newid        ID of the item cloned
    * @param $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
    * */
   static function cloneItem($oldid, $newid) {
      global $DB;

      $query = "SELECT *
                 FROM `glpi_plugin_resources_reportconfigs`
                 WHERE `plugin_resources_resources_id` = '$oldid';";

      foreach ($DB->request($query) as $data) {
         $report = new self();
         $report->add(['plugin_resources_resources_id' => $newid,
                            'information'                   => addslashes($data["information"]),
                            'comment'                       => addslashes($data["comment"]),
                            'send_transfer_notif'           => $data["send_transfer_notif"],
                            'send_report_notif'             => $data["send_report_notif"],
                            'send_other_notif'              => $data["send_other_notif"]]);
      }
   }

   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {

      if (!$this->canview()) {
         return false;
      }

      $plugin_resources_resources_id = -1;
      if (isset($options['plugin_resources_resources_id'])) {
         $plugin_resources_resources_id = $options['plugin_resources_resources_id'];
      }

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         $resource = new PluginResourcesResource();
         $resource->getFromDB($plugin_resources_resources_id);
         // Create item
         $input = ['plugin_resources_resources_id' => $plugin_resources_resources_id];
         $this->check(-1, UPDATE, $input);
      }

      $options["colspan"] = 1;
      //$this->showTabs($options);
      $this->showFormHeader($options);

      echo Html::hidden('plugin_resources_resources_id', ['value' => $plugin_resources_resources_id]);
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Comments');
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='100' rows='6' name='comment' >" . $this->fields["comment"] . "</textarea>";
      echo "</td></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo _n('Information', 'Informations', 2);
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='100' rows='6' name='information' >" . $this->fields["information"] . "</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>";
      echo __('Send resource creation report notification', 'resources');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('send_report', $this->fields["send_report_notif"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>";
      echo __('Send resource transfer notification', 'resources');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('send_transfer_notif', $this->fields["send_transfer_notif"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>";
      echo __('Send other notification', 'resources');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('send_report', $this->fields["send_other_notif"]);
      echo "</td>";
      echo "</tr>";

      $options['candel'] = false;
      $this->showFormButtons($options);

      return true;
   }

   /**
    * @param        $ID
    * @param string $withtemplate
    */
   static function showReports($ID, $withtemplate = '') {
      global $DB;

      $rand     = mt_rand();
      $resource = new PluginResourcesResource();
      $resource->getFromDB($ID);
      $canedit = $resource->can($ID, UPDATE);

      Session::initNavigateListItems("PluginResourcesReportConfig", PluginResourcesResource::getTypeName(1) . " = " . $resource->fields["name"]);

      $query = "SELECT `glpi_plugin_resources_reportconfigs`.`id`,
               `glpi_plugin_resources_reportconfigs`.`plugin_resources_resources_id`,
                `glpi_plugin_resources_reportconfigs`.`information`, 
                `glpi_plugin_resources_reportconfigs`.`send_report_notif`, 
                `glpi_plugin_resources_reportconfigs`.`send_other_notif`, 
                `glpi_plugin_resources_reportconfigs`.`send_transfer_notif`,
                `glpi_plugin_resources_reportconfigs`.`comment`
                 FROM `glpi_plugin_resources_reportconfigs` ";
      $query .= " LEFT JOIN `glpi_plugin_resources_resources` ON (`glpi_plugin_resources_resources`.`id` = `glpi_plugin_resources_reportconfigs`.`plugin_resources_resources_id`)";
      $query .= " WHERE `glpi_plugin_resources_reportconfigs`.`plugin_resources_resources_id` = '$ID' LIMIT 1";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $i       = 0;
      $row_num = 1;
      if ($number != "0") {
         if ($withtemplate < 2) {
            echo "<form method='post' name='form_reports$rand' id='form_reports$rand' action=\"./reportconfig.form.php\">";
         }
         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='2'>" . __('Notification configuration', 'resources') . "</th></tr>";

         while ($data = $DB->fetchArray($result)) {
            $i++;
            $row_num++;
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Comments') . "</td>";
            echo "<td>";
            echo "<textarea cols='100' rows='6' name='comment' >" . $data["comment"] . "</textarea>";
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . _n('Information', 'Informations', 2) . "</td>";
            echo "<td>";
            echo "<textarea cols='100' rows='6' name='information' >" . $data["information"] . "</textarea>";
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_2'>";
            echo "<td>";
            echo __('Send resource creation report notification', 'resources');
            echo "</td>";
            echo "<td>";
            Dropdown::showYesNo('send_report_notif', $data["send_report_notif"]);
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_2'>";
            echo "<td>";
            echo __('Send resource transfer notification', 'resources');
            echo "</td>";
            echo "<td>";
            Dropdown::showYesNo('send_transfer_notif', $data["send_transfer_notif"]);
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_2'>";
            echo "<td>";
            echo __('Send other notification', 'resources');
            echo "</td>";
            echo "<td>";
            Dropdown::showYesNo('send_other_notif', $data["send_other_notif"]);
            echo "</td>";
            echo "</tr>";

            if ($withtemplate < 2 && $canedit) {
               echo "<tr class='tab_bg_1 center'>";
               echo "<td colspan='2'>";
               echo "<input type='submit' name='update' value='" . _sx('button', 'Save') . "' class='submit' />";
               echo "</td>";
               echo "</tr>";

               echo "<tr class='tab_bg_1 center'>";
               echo "<td colspan='2' class='right'>";
               echo "<input type='submit' name='delete' value='" . _sx('button', 'Delete permanently') . "' class='submit' />";
               echo "<input type='hidden' name='id' value='" . $data["id"] . "' />";
               echo Html::hidden('plugin_resources_resources_id', ['value' => $ID]);
               echo "</td>";
               echo "</tr>";
            }
         }

         echo "</table></div>";

         if ($withtemplate < 2) {
            Html::closeForm();
         }
      }
   }

}

