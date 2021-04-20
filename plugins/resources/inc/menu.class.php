<?php
/**
 * Created by PhpStorm.
 * User: mate
 * Date: 11/04/2019
 * Time: 09:19
 */

class PluginResourcesMenu extends CommonDBTM{

   /**
    * Display menu
    */
   static function showMenu(CommonDBTM $item) {
      global $CFG_GLPI;

      echo "<div align='center'><table class='tab_cadre' width='30%' cellpadding='5'>";
      echo "<tr><th colspan='6'>" . __('Menu', 'resources') . "</th></tr>";
      $plugin = new Plugin();
      $canresting       = Session::haveright('plugin_resources_resting', UPDATE);
      $canholiday       = Session::haveright('plugin_resources_holiday', UPDATE);
      $canhabilitation  = Session::haveright('plugin_resources_habilitation', UPDATE);
      $canemployment    = Session::haveright('plugin_resources_employment', UPDATE);
      $canseeemployment = Session::haveright('plugin_resources_employment', READ);
      $canseebudget     = Session::haveright('plugin_resources_budget', READ);
      $canbadges        = Session::haveright('plugin_badges', READ) && $plugin->isActivated("badges");
      $canImport        = Session::haveright('plugin_resources_import', READ);

      if ($item->canCreate()) {
         echo "<tr><th colspan='6'>" . __('Resources management', 'resources') . "</th></tr>";

         echo "<tr class='tab_bg_1'>";

         //Add a resource
         echo "<td class='center' colspan='2' width='200'>";
         echo "<a href=\"./wizard.form.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/newresource.png' alt='" . __('Declare an arrival', 'resources') . "'>";
         echo "<br>" . __('Declare an arrival', 'resources') . "</a>";
         echo "</td>";

         //Add a change
         echo "<td class='center' colspan='2'  width='200'>";
         echo "<a href=\"./resource.change.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/recap.png' alt='" . __('Declare a change', 'resources') . "'>";
         echo "<br>" . __('Declare a change', 'resources') . "</a>";
         echo "</td>";

         //Remove resources
         echo "<td class='center' colspan='2'  width='200'>";
         echo "<a href=\"./resource.remove.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/removeresource.png' alt='" . __('Declare a departure', 'resources') . "'>";
         echo "<br>" . __('Declare a departure', 'resources') . "</a>";
         echo "</td>";

         echo "</tr>";
      }

      if ($canresting || $canholiday || $canbadges || $canhabilitation) {
         echo "<tr><th colspan='6'>" . __('Others declarations', 'resources') . "</th></tr>";
         $num_col = 0;
         if ($canresting) {
            $num_col += 1;
         }
         if ($canholiday) {
            $num_col += 1;
         }
         if ($canhabilitation && $plugin->isActivated("metademands")) {
            $num_col += 1;
         }
         if ($canbadges && $plugin->isActivated("badges")) {
            $num_col += 1;
         }
         if ($num_col == 0) {
            $colspan = 0;
         } else {
            $colspan = floor(6 / $num_col);
         }

         echo "<tr class='tab_bg_1'>";
         if ($colspan == 1) {
            echo "<td></td>";
         }
         if ($canresting) {
            //Management of a non contract period
            echo "<td colspan=$colspan class='center'>";
            echo "<a href=\"./resourceresting.form.php?menu\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/deleteresting.png' alt='" . _n('Non contract period management', 'Non contract periods management', 2, 'resources') . "'>";
            echo "<br>" . _n('Non contract period management', 'Non contract periods management', 2, 'resources') . "</a>";
            echo "</td>";
         }

         if ($canholiday) {
            //Management of a non contract period
            echo "<td colspan=$colspan class='center'>";
            echo "<a href=\"./resourceholiday.form.php?menu\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/holidayresource.png' alt='" . __('Forced holiday management', 'resources') . "'>";
            echo "<br>" . __('Forced holiday management', 'resources') . "</a>";
            echo "</td>";
         }

         if ($canhabilitation && $plugin->isActivated("metademands")) {
            //Management of a super habilitation
            echo "<td colspan=$colspan class='center'>";
            echo "<a href=\"./confighabilitation.form.php?menu\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/habilitation.png' alt='" . PluginResourcesConfigHabilitation::getTypeName(1) . "'>";
            echo "<br>" . PluginResourcesConfigHabilitation::getTypeName(1) . "</a>";
            echo "</td>";
         }

         if ($canbadges && $plugin->isActivated("badges")) {
            //Management of a non contract period
            echo "<td colspan=$colspan class='center'>";
            echo "<a href=\"./resourcebadge.form.php?menu\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/badges/badges.png' alt='" . _n('Badge management', 'Badges management', 2, 'resources') . "'>";
            echo "<br>" . _n('Badge management', 'Badges management', 2, 'resources') . "</a>";
            echo "</td>";
         }
         if ($colspan == 1) {
            echo "<td></td>";
         }
         echo "</tr>";
      }

      if ($item->canView()) {
         echo "<tr><th colspan='6'>" . __('Others actions', 'resources') . "</th></tr>";
         echo "<tr class='tab_bg_1'>";

         $opt                              = [];
         $opt['reset']                     = 'reset';
         $opt['criteria'][0]['field']      = 27;
         $opt['criteria'][0]['searchtype'] = 'equals';
         $opt['criteria'][0]['value']      = Session::getLoginUserID();
         $opt['criteria'][0]['link']       = 'AND';

         $url = $CFG_GLPI["root_doc"] . "/plugins/resources/front/resource.php?" . Toolbox::append_params($opt, '&amp;');

         echo "<td class='center' colspan='2'>";
         echo "<a href=\"$url\">";
         echo "<i class='fas fa-user-tie fa-5x' style='color:steelblue;' title='" . __('View my resources as a commercial', 'resources') . "'></i>";
         echo "<br>" . __('View my resources as a commercial', 'resources') . "</a>";
         echo "</td>";

         //See resources
         echo "<td class='center' colspan='2'>";
         echo "<a href=\"./resource.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/resourcelist.png' alt='" . __('Search resources', 'resources') . "'>";
         echo "<br>" . __('Search resources', 'resources') . "</a>";
         echo "</td>";

         echo "<td class='center' colspan='2'>";
         echo "<a href=\"./resource.card.form.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/detailresource.png' alt='" . __('See details of a resource', 'resources') . "'>";
         echo "<br>" . __('See details of a resource', 'resources') . "</a>";
         echo "</td>";

         echo "</tr>";
         echo "<tr class='tab_bg_1'>";

         echo "<td class='center' colspan='2'>";
         echo "<a href=\"./directory.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/directory.png' alt='" . PluginResourcesDirectory::getTypeName(1) . "'>";
         echo "<br>" . PluginResourcesDirectory::getTypeName(1) . "</a>";
         echo "</td>";

         echo "<td class='center' colspan='4'>";
         echo "</td>";
         echo "</tr>";
      }

      if ($canseeemployment || $canseebudget) {
         $colspan = 0;

         echo "<tr><th colspan='6'>" . __('Employments / budgets management', 'resources') . "</th></tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td class='center'>";
         echo "</td>";

         if ($canseeemployment) {
            if ($canemployment) {
               //Add an employment
               echo "<td class='center'>";
               echo "<a href=\"./employment.form.php\">";
               echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/employment.png' alt='" . __('Declare an employment', 'resources') . "'>";
               echo "<br>" . __('Declare an employment', 'resources') . "</a>";
               echo "</td>";
            } else {
               $colspan += 1;
            }
            //See managment employments
            echo "<td class='center'>";
            echo "<a href=\"./employment.php\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/employmentlist.png' alt='" . __('Employment management', 'resources') . "'>";
            echo "<br>" . __('Employment management', 'resources') . "</a>";
            echo "</td>";
         } else {
            $colspan += 1;
         }
         if ($canseebudget) {
            //See managment budgets
            echo "<td class='center'>";
            echo "<a href=\"./budget.php\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/budgetlist.png' alt='" . __('Budget management', 'resources') . "'>";
            echo "<br>" . __('Budget management', 'resources') . "</a>";
            echo "</td>";
         } else {
            $colspan += 1;
         }

         if ($canseeemployment) {
            //See recap ressource / employment
            echo "<td class='center'>";
            echo "<a href=\"./recap.php\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/recap.png' alt='" . __('List Employments / Resources', 'resources') . "'>";
            echo "<br>" . __('List Employments / Resources', 'resources') . "</a>";
            echo "</td>";
         } else {
            $colspan += 1;
         }

         echo "<td class='center' colspan='" . ($colspan + 1) . "'></td>";

         echo "</tr>";
      }

      if ($canImport) {
         //See import External
         echo "<tr><th colspan='6'>" . __('Import resources', 'resources') . "</th></tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='2'>";
         echo "<a href='".PluginResourcesImportResource::getIndexUrl()."?type=".PluginResourcesImportResource::UPDATE_RESOURCES."'>";
         echo "<i class=\"fas fa-user-edit fa-5x\"></i>";
         echo "<br>" . __('Update GLPI Resources', 'resources') . "</a>";
         echo "</td>";

         echo "<td class='center' colspan='2'>";
         echo "<a href='".PluginResourcesImportResource::getIndexUrl()."?type=".PluginResourcesImportResource::VERIFY_FILE."'>";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/csv_check.png' />";
         echo "<br>" . __('Verify CSV file', 'resources') . "</a>";
         echo "</td>";

         echo "<td class='center' colspan='2'>";
         echo "<a href='".PluginResourcesImportResource::getIndexUrl()."?type=".PluginResourcesImportResource::VERIFY_GLPI."'>";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/resource_check.png' />";
         echo "<br>" . __('Verify GLPI resources', 'resources') . "</a>";
         echo "</td>";

         echo "</tr>";

         echo "<tr class='tab_bg_1'>";

         echo "<td class='center' colspan='2'>";
         echo "<a href='".PluginResourcesImport::getIndexUrl()."'>";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/conf.png' />";
         echo "<br>" . __('Configure Imports', 'resources') . "</a>";
         echo "</td>";

         echo "<td class='center' colspan='2'>";
         echo "<a href='".PluginResourcesImportResource::getFormURL()."?reset-imports=1'>";
         echo "<i class=\"fas fa-trash fa-5x\"></i>";
         echo "<br>" . __('Purge imported resources', 'resources') . "</a>";
         echo "</td>";

         echo "<td colspan='2'></td>";

         echo "</tr>";
      }

      echo " </table></div>";
   }

   /**
    * get menu content
    *
    * @return array array for menu
    **/
   static function getMenuContent() {
      $plugin_page = "/plugins/resources/front/menu.php";

      $menu        = [];
      //Menu entry in admin
      $menu['title']           = PluginResourcesResource::getTypeName(2);
      $menu['page']            = $plugin_page;
      $menu['links']['search'] = "/plugins/resources/front/resource.php";

      if (Session::haveright("plugin_resources", CREATE)) {

         $menu['links']['add'] = '/plugins/resources/front/wizard.form.php';
         $menu['links']['template'] = '/plugins/resources/front/setup.templates.php?add=0';
      }

      // Resource directory
      $menu['links']["<i class='far fa-address-book fa-2x' title='" . __('Directory', 'resources') . "'></i>"] = '/plugins/resources/front/directory.php';

      // Resting
      if (Session::haveright("plugin_resources_resting", UPDATE)) {
         $menu['links']["<i class='fas fa-file-signature fa-2x' title='" . __('List of non contract periods', 'resources') . "'></i>"] = '/plugins/resources/front/resourceresting.php';
      }

      // Holiday
      if (Session::haveright("plugin_resources_holiday", UPDATE)) {
         $menu['links']["<i class='fas fa-atlas fa-2x' title='" . __('List of forced holidays', 'resources') . "'></i>"] = '/plugins/resources/front/resourceholiday.php';
      }

      // Employment
      if (Session::haveright("plugin_resources_employment", READ)) {
         $menu['links']["<i class='fas fa-list-ul fa-2x' title='" . __('Employment management', 'resources') . "'></i>"]     = '/plugins/resources/front/employment.php';
         $menu['links']["<i class='fas fa-city fa-2x' title='" . __('List Employments / Resources', 'resources') . "'></i>"] = '/plugins/resources/front/recap.php';
      }

      // Budget
      if (Session::haveright("plugin_resources_budget", READ)) {
         $menu['links']["<i class='fas fa-coins fa-2x' title='" . __('Budget management', 'resources') . "'></i>"] = '/plugins/resources/front/budget.php';
      }

      // Task
      if (Session::haveright("plugin_resources_task", READ)) {
         $menu['links']["<i class='fas fa-tasks fa-2x' title='" . __('Tasks list', 'resources') . "'></i>"] = '/plugins/resources/front/task.php';
      }

      // Checklist
      if (Session::haveright("plugin_resources_checklist", READ)) {
         $menu['links']["<i class='far fa-calendar-check fa-2x' title='" . _n('Checklist', 'Checklists', 2, 'resources') . "'></i>"] = '/plugins/resources/front/checklistconfig.php';
      }

      $opt                              = [];
      $opt['reset']                     = 'reset';
      $opt['criteria'][0]['field']      = 27; // validation status
      $opt['criteria'][0]['searchtype'] = 'equals';
      $opt['criteria'][0]['value']      = Session::getLoginUserID();
      $opt['criteria'][0]['link']       = 'AND';

      $url = "/plugins/resources/front/resource.php?" . Toolbox::append_params($opt, '&amp;');

      $menu['links']["<i class='fas fa-user-tie fa-2x' title='" . __('View my resources as a commercial', 'resources') . "'></i>"] = $url;

      // Import page
      if(Session::haveRight('plugin_resources_import', READ)){
         $menu['links']["<i class='fas fa-cog fa-2x' title='" . __('Import configuration','resources') . "'></i>"]
            = '/plugins/resources/front/import.php';
      }

      // Config page
      if (Session::haveRight("config", UPDATE)) {
         $menu['links']['config'] = '/plugins/resources/front/config.form.php';
      }

      // Add menu to class
      $menu = PluginResourcesBudget::getMenuOptions($menu);
      $menu = PluginResourcesChecklist::getMenuOptions($menu);
      $menu = PluginResourcesEmployment::getMenuOptions($menu);

      $menu['icon'] = self::getIcon();
      
      return $menu;
   }

   static function getIcon() {
      return "fas fa-user-friends";
   }

}