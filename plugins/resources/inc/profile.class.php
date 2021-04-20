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
 * Class PluginResourcesProfile
 */
class PluginResourcesProfile extends Profile {

   static $rightname = "profile";

   /**
    * @param \CommonGLPI $item
    * @param int         $withtemplate
    *
    * @return string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'Profile') {
         return PluginResourcesResource::getTypeName(2);
      }
      return '';
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $tabnum
    * @param int         $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID, [
            'plugin_resources' => ALLSTANDARDRIGHT + READNOTE + UPDATENOTE,
            'plugin_resources_task' => 0,
            'plugin_resources_checklist' => 0,
            'plugin_resources_employee' => 0,
            'plugin_resources_resting' => 0,
            'plugin_resources_holiday' => 0,
            'plugin_resources_habilitation' => 0,
            'plugin_resources_employment' => 0,
            'plugin_resources_budget' => 0,
            'plugin_resources_dropdown_public' => 0,
            'plugin_resources_import' => 0,
            'plugin_resources_open_ticket' => 0,
            'plugin_resources_all' => 0
         ]);
         $prof->showForm($ID);
      }

      return true;
   }

   /**
    * @param $profiles_id
    */
   static function createFirstAccess($profiles_id)
   {
      self::addDefaultProfileInfos($profiles_id, [
         'plugin_resources' => ALLSTANDARDRIGHT + READNOTE + UPDATENOTE,
         'plugin_resources_task' => ALLSTANDARDRIGHT,
         'plugin_resources_checklist' => ALLSTANDARDRIGHT,
         'plugin_resources_employee' => ALLSTANDARDRIGHT,
         'plugin_resources_resting' => ALLSTANDARDRIGHT,
         'plugin_resources_holiday' => ALLSTANDARDRIGHT,
         'plugin_resources_habilitation' => ALLSTANDARDRIGHT,
         'plugin_resources_employment' => ALLSTANDARDRIGHT,
         'plugin_resources_budget' => ALLSTANDARDRIGHT,
         'plugin_resources_dropdown_public' => ALLSTANDARDRIGHT,
         'plugin_resources_import' => 0,
         'plugin_resources_open_ticket' => 1,
         'plugin_resources_all' => 1
      ], true);

   }

   /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      $dbu          = new DbUtils();
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {

         if ($dbu->countElementsInTable('glpi_profilerights',
                                   ["profiles_id" => $profiles_id, "name" => $right]) && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
         }
         if (!$dbu->countElementsInTable('glpi_profilerights',
                                   ["profiles_id" => $profiles_id, "name" => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   /**
    * @param int  $profiles_id
    * @param bool $openform
    * @param bool $closeform
    *
    * @return bool|void
    */
   function showForm($profiles_id = 0, $openform = true, $closeform = true) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $generalRights = $this->getAllRights(false, ['general']);
      $profile->displayRightsChoiceMatrix($generalRights, ['canedit'       => $canedit,
                                                                'default_class' => 'tab_bg_2',
                                                                'title'         => __('General')]);

      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'><th colspan='4'>".__('Helpdesk')."</th></tr>\n";

      $effective_rights = ProfileRight::getProfileRights($profiles_id, ['plugin_resources_open_ticket', 'plugin_resources_all']);
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Associable items to a ticket')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_resources_open_ticket',
                               'checked' => $effective_rights['plugin_resources_open_ticket']]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('All resources access', 'resources')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_resources_all',
                               'checked' => $effective_rights['plugin_resources_all']]);
      echo "</td></tr>\n";

      echo "</table>";

      $ssiiRights = $this->getAllRights(false, ['ssii']);
      $profile->displayRightsChoiceMatrix($ssiiRights, ['canedit'       => $canedit,
                                                             'default_class' => 'tab_bg_2',
                                                             'title'         => __('Service company management', 'resources')]);

      $publicRights = $this->getAllRights(false, ['public']);
      $profile->displayRightsChoiceMatrix($publicRights, ['canedit'       => $canedit,
                                                               'default_class' => 'tab_bg_2',
                                                               'title'         => __('Public service management', 'resources')]);
      $config = new PluginResourcesConfig();
      $config->getFromDB(1);
      if($config->getField('import_external_datas')==1){
         $importRights = $this->getAllRights(false, ['import']);
         $profile->displayRightsChoiceMatrix($importRights, ['canedit'       => $canedit,
                                                             'default_class' => 'tab_bg_2',
                                                             'title'         => __('Import external', 'resources')]);
      }

      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

      $this->showLegend();

   }

   /**
    * @param bool  $all
    * @param array $types
    *
    * @return array
    */
   static function getAllRights($all = true, $types = []) {

      $rights = [
         ['itemtype' => 'PluginResourcesResource',
            'label' => _n('Human resource', 'Human resources', 1, 'resources'),
            'field' => 'plugin_resources',
            'type' => 'general'
         ],
         ['itemtype' => 'PluginResourcesTask',
            'label' => _n('Task', 'Tasks', 1),
            'field' => 'plugin_resources_task',
            'type' => 'general'
         ],
         ['itemtype' => 'PluginResourcesBudget',
            'label' => _n('Budget', 'Budgets', 1),
            'field' => 'plugin_resources_budget',
            'type' => 'public'
         ],
         ['itemtype' => 'PluginResourcesChecklist',
            'label' => _n('Checklist', 'Checklists', 1, 'resources'),
            'field' => 'plugin_resources_checklist',
            'type' => 'general'
         ],
         ['itemtype' => 'PluginResourcesEmployee',
            'label' => _n('Employee', 'Employees', 1, 'resources'),
            'field' => 'plugin_resources_employee',
            'type' => 'general'
         ],
         ['itemtype' => 'PluginResourcesResourceResting',
            'label' => _n('Non contract period management', 'Non contract periods management', 1, 'resources'),
            'field' => 'plugin_resources_resting',
            'type' => 'ssii'
         ],
         ['itemtype' => 'PluginResourcesResourceHoliday',
            'label' => _n('Holiday', 'Holidays', 1, 'resources'),
            'field' => 'plugin_resources_holiday',
            'type' => 'ssii'
         ],
         ['itemtype' => 'PluginResourcesResourceHabilitation',
            'label' => _n('Super habilitation', 'Super habilitations', 1, 'resources'),
            'field' => 'plugin_resources_habilitation',
            'type' => 'ssii'
         ],
         ['itemtype' => 'PluginResourcesEmployment',
            'label' => _n('Employment', 'Employments', 1, 'resources'),
            'field' => 'plugin_resources_employment',
            'type' => 'public'
         ],
         ['itemtype' => 'PluginResourcesResource',
            'label' => __('Dropdown management', 'resources'),
            'field' => 'plugin_resources_dropdown_public',
            'type' => 'public'
         ],
         ['itemtype' => 'PluginResourcesImport',
            'label' => __('Import external', 'resources'),
            'field' => 'plugin_resources_import',
            'type' => 'import',
            'rights' => [
               READ => __('Read'),
               UPDATE => __('Update'),
               CREATE => __('Create'),
               PURGE => __('Purge')
            ]
         ]
      ];

      if ($all) {
         $rights[] = ['itemtype' => 'PluginResourcesResource',
                           'label'    =>  __('All resources access', 'resources'),
                           'field'    => 'plugin_resources_all'];

         $rights[] = ['itemtype' => 'PluginResourcesResource',
                           'label'    =>  __('Associable items to a ticket'),
                           'field'    => 'plugin_resources_open_ticket'];
      }
      if (!$all) {
         $customRights = [];
         foreach ($rights as $right) {
            if (in_array($right['type'], $types)) {
               $customRights[] = $right;
            }
         }

         return $customRights;
      }

      return $rights;
   }

   /**
    * Init profiles
    *
    **/

   static function translateARight($old_right) {
      switch ($old_right) {
         case '':
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
         case '0':
         case '1':
            return $old_right;

         default :
            return 0;
      }
   }


   /**
   * @since 0.85
   * Migration rights from old system to the new one for one profile
   * @param $profiles_id the profile ID
   */
   static function migrateOneProfile($profiles_id) {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!$DB->tableExists('glpi_plugin_resources_profiles')) {
         return true;
      }

      foreach ($DB->request('glpi_plugin_resources_profiles',
                            "`profiles_id`='$profiles_id'") as $profile_data) {

         $matching = [
            'resources' => 'plugin_resources',
            'task' => 'plugin_resources_task',
            'checklist' => 'plugin_resources_checklist',
            'employee' => 'plugin_resources_employee',
            'resting' => 'plugin_resources_resting',
            'holiday' => 'plugin_resources_holiday',
            'habilitation' => 'plugin_resources_habilitation',
            'employment' => 'plugin_resources_employment',
            'budget' => 'plugin_resources_budget',
            'dropdown_public' => 'plugin_resources_dropdown_public',
            'import'          => 'plugin_resources_import',
            'open_ticket' => 'plugin_resources_open_ticket',
            'all' => 'plugin_resources_all'
         ];

         $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
         foreach ($matching as $old => $new) {
            if (!isset($current_rights[$old])) {
               $query = "UPDATE `glpi_profilerights` 
                         SET `rights`='".self::translateARight($profile_data[$old])."' 
                         WHERE `name`='$new' AND `profiles_id`='$profiles_id'";
               $DB->query($query);
            }
         }
      }
   }

   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();
      $dbu     = new DbUtils();
      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if ($dbu->countElementsInTable("glpi_profilerights",
                                        ["name" => $data['field']]) == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }

      //Migration old rights in new ones
      foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
         self::migrateOneProfile($prof['id']);
      }
      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."' 
                              AND `name` LIKE '%plugin_resources%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }

   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }

}

