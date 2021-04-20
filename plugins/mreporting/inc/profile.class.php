<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2011 by the mreporting Development Team.

 https://forge.indepnet.net/projects/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMreportingProfile extends CommonDBTM {
   static $rightname = 'profile';

   static function getTypeName($nb = 0) {
      return __("More Reporting", 'mreporting');
   }

   //if profile deleted
   static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(['profiles_id' => $prof->getField("id")]);
   }


   //if reports add
   static function addReport(PluginMreportingConfig $config) {
      $plugprof = new self();
      $plugprof->addRightToReports($config->getField("id"));
   }


   //if reports  deleted
   static function purgeProfilesByReports(PluginMreportingConfig $config) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(['reports' => $config->getField("id")]);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getField('interface') == 'helpdesk') {
         return false;
      }

      switch ($item->getType()) {
         case 'Profile':
            return self::getTypeName();
         case 'PluginMreportingConfig':
            return __("Rights management", 'mreporting');
         default:
            return '';
      }
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile' && $item->getField('interface') != 'helpdesk') {
         $ID = $item->getField('id');
         $prof = new self();

         if (!$prof->getFromDBByProfile($item->getField('id'))) {
            $prof->createAccess($item->getField('id'));
         }
         $prof->showForm($item->getField('id'));
      } else if ($item->getType()=='PluginMreportingConfig') {
         $reportProfile = new self();
         $reportProfile->showFormForManageProfile($item);
      }
      return true;
   }

   function getFromDBByProfile($profiles_id) {
      global $DB;

      $query = "SELECT * FROM `{$this->getTable()}`
         WHERE `profiles_id` = '".$profiles_id."'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         return (is_array($this->fields) && count($this->fields));
      }
      return false;
   }


   /**
   * @param $right array
   */
   static function addRightToAllProfiles() {
      global $DB;

      $result_config = $DB->request("SELECT `id` FROM `glpi_plugin_mreporting_configs`");
      foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
         foreach ($result_config as $report) {
            $DB->query("REPLACE INTO `glpi_plugin_mreporting_profiles`
                           (`profiles_id`,`reports`,`right`)
                        VALUES
                           ('".$prof['id']."','".$report['id']."',NULL)");
         }
      }
   }


   static function getRight() {
      global $DB;

      $query = "SELECT `profiles_id`
               FROM `glpi_plugin_mreporting_profiles`
               WHERE `reports` = ".READ;

      $right = [];
      foreach ($DB->request($query) as $profile) {
         $right[] = $profile['profiles_id'];
      }

      return $right;
   }

   /**
   * Function to add right on report to a profile
   * @param $idProfile
   */
   public static function addRightToProfile($idProfile) {
      global $DB;

      //get all reports
      $config = new PluginMreportingConfig();
      foreach ($config->find() as $report) {
         // add right for any reports for profile
         // Add manual request because Add function get error : right is set to NULL
         $query = "REPLACE INTO `glpi_plugin_mreporting_profiles` SET
                     `profiles_id` = $idProfile,
                     `reports` = {$report['id']},
                     `right` = " . READ;
         $DB->query($query) or die('An error occurs during profile initialisation.');
      }

   }


   /**
   * Function to add right of a new report
   * @param $report_id
   */
   function addRightToReports($report_id) {
      global $DB;

      $reportProfile = new self();

      foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
         $reportProfile->add(['profiles_id' => $prof['id'],
                              'reports'   => $report_id,
                              'right' => READ]);
      }
   }

   function createAccess($ID) {
      $this->add(['profiles_id' => $ID]);
   }

   static function changeProfile() {
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_mreporting_profile"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_mreporting_profile"]);
      }
   }

   /**
   * Form to manage report right on profile
   * @param $ID (id of profile)
   * @param array $options
   * @return bool
   */
   function showForm($ID, $options = []) {
      global $LANG, $CFG_GLPI;

      if (!Session::haveRight("profile", READ)) {
         return false;
      }

      echo '<form method="post" action="' . self::getFormURL() . '">';
      echo '<div class="spaced" id="tabsbody">';
      echo '<table class="tab_cadre_fixe" id="mainformtable">';

      echo '<tr class="headerRow"><th colspan="3">' . self::getTypeName() . '</th></tr>';

      Plugin::doHook("pre_item_form", ['item' => $this, 'options' => &$options]);

      echo "<tr><th colspan='3'>".__("Rights management", 'mreporting')."</th></tr>\n";

      $config = new PluginMreportingConfig();
      foreach ($config->find() as $report) {
         $mreportingConfig = new PluginMreportingConfig();
         $mreportingConfig->getFromDB($report['id']);

         // If classname doesn't exists, don't display the report
         if (class_exists($mreportingConfig->fields['classname'])) {
            $profile = $this->findByProfileAndReport($ID, $report['id']);
            $index = str_replace('PluginMreporting', '', $mreportingConfig->fields['classname']);
            $title = $LANG['plugin_mreporting'][$index][$report['name']]['title'];

            echo "<tr class='tab_bg_1'>";
            echo "<td>".$mreportingConfig->getLink()."&nbsp(".$title."): </td>";
            echo "<td>";
            Profile::dropdownRight($report['id'],
                                   ['value'   => $profile->fields['right'],
                                    'nonone'  => 0,
                                    'noread'  => 0,
                                    'nowrite' => 1]);
            echo "</td>";
            echo "</tr>\n";
         }
      }

      echo "<tr class='tab_bg_4'>";
      echo "<td colspan='2'>";

      echo "<div class='center'>";
      echo "<input type='submit' name='update' value=\""._sx('button', 'Save')."\" class='submit'>";
      echo "</div>";

      echo "<input type='hidden' name='profile_id' value=".$ID.">";

      echo "<div style='float:right;'>";
      echo "<input type='submit'
               style='background-image: url(".
                  $CFG_GLPI['root_doc']."/pics/add_dropdown.png);background-repeat:no-repeat;width:14px;border:none;cursor:pointer;'
               name='giveReadAccessForAllReport' value='' title='".__('Select all')."'>";

      echo "<input type='submit'
               style='background-image: url(".
                  $CFG_GLPI['root_doc']."/pics/sub_dropdown.png);background-repeat:no-repeat;width:14px;border:none;cursor:pointer;'
               name='giveNoneAccessForAllReport' value='' title='".__('Deselect all')."'>";

      echo "<br><br>";

      echo "</div>";

      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }


   /**
   * Form to manage right on reports
   * @param $items
   */
   function showFormForManageProfile($items, $options = []) {
      global $DB, $CFG_GLPI;

      if (!Session::haveRight("config", READ)) {
         return false;
      }

      $target = isset($options['target']) ? $options['target'] : $this->getFormURL();

      echo '<form action="'.$target.'" method="post" name="form">';
      echo "<table class='tab_cadre_fixe'>\n";
      echo "<tr><th colspan='3'>".__("Rights management", 'mreporting')."</th></tr>\n";

      $query = "SELECT `id`, `name`
               FROM `glpi_profiles`
               ORDER BY `name`";

      foreach ($DB->request($query) as $profile) {
         $reportProfiles = new self();
         $reportProfiles = $reportProfiles->findByProfileAndReport($profile['id'], $items->fields['id']);

         $prof = new Profile();
         $prof->getFromDB($profile['id']);

         echo "<tr class='tab_bg_1'>";
         echo "<td>".$prof->getLink()."</td>";
         echo "<td>";
         Profile::dropdownRight($profile['id'],
                                ['value'   => $reportProfiles->fields['right'],
                                 'nonone'  => 0,
                                 'noread'  => 0,
                                 'nowrite' => 1]);
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_4'>";
      echo "<td colspan='2'>";
      echo "<div style='float:right;'>";
      echo "<input type='submit' style='background-image: url(".$CFG_GLPI['root_doc'].
           "/pics/add_dropdown.png);background-repeat:no-repeat; width:14px;border:none;cursor:pointer;' ".
           "name='giveReadAccessForAllProfile' value='' title='".__('Select all')."'>";

      echo "<input type='submit' style='background-image: url(".$CFG_GLPI['root_doc'].
           "/pics/sub_dropdown.png);background-repeat:no-repeat; width:14px;border:none;cursor:pointer;' ".
           "name='giveNoneAccessForAllProfile' value='' title='".__('Deselect all')."'><br><br>";
      echo "</div>";

      echo "<div class='center'>";
      echo "<input type='hidden' name='report_id' value=".$items->fields['id'].">";
      echo "<input type='submit' name='add' value=\""._sx('button', 'Save')."\" class='submit'>";
      echo "</div>";

      echo "</td></tr>";
      echo "</table>\n";
      Html::closeForm();
   }


   function findByProfileAndReport($profil_id, $report_id) {
      $prof = new self();
      $prof->getFromDBByCrit(
         [
            'profiles_id' => $profil_id,
            'reports'     => $report_id,
         ]
      );
      return $prof;
   }

   function findReportByProfiles($profil_id) {
      $prof = new self();
      $prof->getFromDBByCrit(
         [
            'profiles_id' => $profil_id,
         ]
      );
      return $prof;
   }


   static function canViewReports($profil_id, $report_id) {
      $prof = new self();
      $res = $prof->getFromDBByCrit(
         [
            'profiles_id' => $profil_id,
            'reports'     => $report_id,
         ]
      );

      if ($res && $prof->fields['right'] == READ) {
         return true;
      }

      return false;
   }

   // Hook done on add item case
   static function addProfiles(Profile $item) {
      if ($item->getType()=='Profile' && $item->getField('interface') != 'helpdesk') {
         PluginMreportingProfile::addRightToProfile($item->getID());
      }

      return true;
   }
}
