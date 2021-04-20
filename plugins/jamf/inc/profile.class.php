<?php

/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * PluginJamfProfile class. Adds plugin related rights tab to Profiles.
 * @since 1.0.0
 */
class PluginJamfProfile extends Profile
{

   public static $rightname = "config";

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      return self::createTabEntry(_x('plugin_info', 'Jamf plugin', 'jamf'));
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      $jamfprofile = new self();
      if ($item->fields['interface'] == 'central') {
         $jamfprofile->showForm($item->getID());
      } else {
         $jamfprofile->showFormHelpdesk($item->getID());
      }
      return true;
   }

   /**
    * Print the Jamf plugin right form for the current profile
    *
    * @param int $profiles_id Current profile ID
    * @param bool $openform Open the form (true by default)
    * @param bool $closeform Close the form (true by default)
    *
    * @return bool|void
    */
   public function showForm($profiles_id = 0, $openform = true, $closeform = true)
   {
      global $CFG_GLPI;

      if (!self::canView()) {
         return false;
      }

      echo "<div class='spaced'>";
      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      if ($openform && ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))) {
         echo "<form method='post' action='" . $profile::getFormURL() . "'>";
      }

      $rights = [
         [
            'itemtype' => 'PluginJamfMobileDevice',
            'label' => PluginJamfMobileDevice::getTypeName(Session::getPluralNumber()),
            'field' => 'plugin_jamf_mobiledevice'
         ],
         [
            'itemtype' => 'PluginJamfComputer',
            'label' => PluginJamfComputer::getTypeName(Session::getPluralNumber()),
            'field' => 'plugin_jamf_computer'
         ],
         [
            'itemtype' => 'PluginJamfRuleImport',
            'label' => _nx('right', 'Import rule', 'Import rules', Session::getPluralNumber(), 'jamf'),
            'field' => 'plugin_jamf_ruleimport'
         ],
         [
            'itemtype' => 'PluginJamfUser_JSSAccount',
            'label' => PluginJamfUser_JSSAccount::getTypeName(Session::getPluralNumber()),
            'field' => PluginJamfUser_JSSAccount::$rightname
         ],
         [
            'itemtype' => 'PluginJamfItem_MDMCommand',
            'label' => PluginJamfItem_MDMCommand::getTypeName(Session::getPluralNumber()),
            'field' => PluginJamfItem_MDMCommand::$rightname
         ]
      ];
      $matrix_options['title'] = _x('plugin_info', 'Jamf plugin', 'jamf');
      $profile->displayRightsChoiceMatrix($rights, $matrix_options);

      if ($canedit
         && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo '</div>';
   }

   /**
    * Print the Jamf plugin helpdesk right form for the current profile
    *
    * @param int $profiles_id Current profile ID
    * @param bool $openform Open the form (true by default)
    * @param bool $closeform Close the form (true by default)
    *
    * @return bool|void
    */
   function showFormHelpdesk($profiles_id = 0, $openform = true, $closeform = true)
   {
      global $CFG_GLPI;

      if (!self::canView()) {
         return false;
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      echo "<div class='spaced'>";
      if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
         echo "<form method='post' action='" . $profile::getFormURL() . "'>";
      }

      $matrix_options = ['canedit' => $canedit,
         'default_class' => 'tab_bg_2'];

      $rights = [['itemtype' => 'PluginJamfMobileDevice',
         'label' => PluginJamfMobileDevice::getTypeName(Session::getPluralNumber()),
         'field' => 'plugin_jamf_mobiledevice',
         'rights' => [READ => __('Read')]]];
      $matrix_options['title'] = _x('plugin_info', 'Jamf plugin', 'jamf');
      $profile->displayRightsChoiceMatrix($rights, $matrix_options);

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='4' class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</td></tr>\n";
         echo "</table>\n";
         Html::closeForm();
      } else {
         echo "</table>\n";
      }
      echo '</div>';
   }
}
