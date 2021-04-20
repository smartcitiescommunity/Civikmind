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
 * Class PluginResourcesResourceBadge
 */
class PluginResourcesResourceBadge extends CommonDBTM {

   static $rightname = 'plugin_resources_resting';
   public $dohistory = true;

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {

      return _n('Badge management', 'Badges management', 2, 'resources');
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
    * Display of the link to configure the badge interface
    */
   function showFormConfig() {
      echo "<br>";
      echo "<form name='form' method='post' action='" . self::getFormURL() . "'>";
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th>" . self::getTypeName(2) . "</th></tr>";
      echo "<tr class='tab_bg_1'><td class='center'>";
      echo "<a href=\"./resourcebadge.form.php?config\">" . PluginMetademandsMetademand_Resource::getTypeName(2) . "</a>";
      echo "</td></tr></table></div>";
      Html::closeForm();
      echo "<br>";
   }

   /**
    * Choose link with metademand
    *
    * @return bool
    */
   function showFormBadge() {

      if (!$this->canView()) {
         return false;
      }
      if (!$this->canCreate()) {
         return false;
      }

      $used_data = [];
      $data      = $this->find();

      $is_present = false;

      if ($data) {
         foreach ($data as $field) {
            $used_data[] = $field['plugin_metademands_metademands_id'];

            if ($field['entities_id'] == $_SESSION['glpiactive_entity']) {
               $is_present = true;
            }
         }
      }
      $canedit = $this->canCreate();

      if ($canedit) {
         if ($is_present) {
            echo "<div align='center'>";
            __('The current entity is already linked to a meta-demand', 'resources');
            echo "</div>";
         } else {
            //form to choose the metademand
            echo "<form name='form' method='post' action='" .
                 Toolbox::getItemTypeFormURL('PluginResourcesResourceBadge') . "'>";

            echo "<div align='center'><table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'><th>" . PluginMetademandsMetademand_Resource::getTypeName(2) . "</th></tr>";
            echo "<tr class='tab_bg_1'><td class='center'>";
            echo PluginMetademandsMetademand::getTypeName(1) . '&nbsp;';
            Dropdown::show('PluginMetademandsMetademand', ['name'   => 'plugin_metademands_metademands_id',
                                                           'used'   => $used_data,
                                                           'entity' => $_SESSION['glpiactive_entity']]);
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td class='tab_bg_2 center'><input type=\"submit\" name=\"add_metademand\" class=\"submit\"
            value=\"" . _sx('button', 'Add') . "\" >";
            echo "<input type='hidden' name='entities_id' value='" . $_SESSION['glpiactive_entity'] . "'>";

            echo "</td></tr>";
            echo "</table></div>";
            Html::closeForm();
         }
      }
      //list metademands
      $this->listItems($data, $canedit);
   }

   /**
    * List of metademands
    *
    * @param $fields
    * @param $canedit
    */
   private function listItems($fields, $canedit) {
      if (!empty($fields)) {
         $rand = mt_rand();
         echo "<div class='center'>";
         if ($canedit) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
         }
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='3'>" . __('Meta-demands linked', 'metademands') . "</th>";
         echo "</tr>";
         echo "<tr>";
         if ($canedit) {
            echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
         }
         echo "<th>" . __('Name') . "</th>";
         echo "<th>" . __('Entity') . "</th>";
         foreach ($fields as $field) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
               echo "</td>";
            }
            //DATA LINE
            echo "<td>" . Dropdown::getDropdownName('glpi_plugin_metademands_metademands', $field['plugin_metademands_metademands_id']) . "</td>";
            echo "<td>" . Dropdown::getDropdownName('glpi_entities', $field['entities_id']) . "</td>";
            echo "</tr>";
         }

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         echo "</div>";
      }
   }

   /**
    * Display Menu
    */
   function showMenu() {
      global $CFG_GLPI;

      $plugin = new Plugin();

      echo "<div align='center'><table class='tab_cadre' width='30%' cellpadding='5'>";
      echo "<tr><th colspan='2'>" . _n('Badge management', 'Badges management', 2, 'resources') . "</th></tr>";

      $canresting = Session::haveright('plugin_resources_resting', UPDATE);

      echo "<tr class='tab_bg_1'>";
      if ($canresting) {
         $colspan = 1;
         if ($plugin->isActivated("metademands")) {
            //Add resting resource
            echo "<td class='center'>";
            echo "<a href=\"./resourcebadge.form.php?new\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/badges/pics/badgerequest.png' alt='" . __('Request new badge', 'resources') . "'>";
            echo "<br>" . __('Request new badge', 'resources') . "</a>";
            echo "</td>";
         } else {
            $colspan = 2;
         }
         //List resting resource
         echo "<td class='center' colspan='$colspan'>";
         echo "<a href=\"./resourcebadge.form.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/badges/pics/badgereturn.png' alt='" . __('Badge restitution', 'resources') . "'>";
         echo "<br>" . __('Badge restitution', 'resources') . "</a>";
         echo "</td>";
      }
      echo "</tr></table>";
      Html::closeForm();

      echo "</div>";

   }

   /**
    * Show form from helpdesk to badge restitution of a resource
    */
   function showForm() {
      global $CFG_GLPI;

      echo "<div align='center'>";

      echo "<form method='post' action=\"" . $CFG_GLPI["root_doc"] . "/plugins/resources/front/resourcebadge.form.php\">";

      echo "<table class='plugin_resources_wizard' style='margin-top:1px;'>";
      echo "<tr>";
      echo "<td class='plugin_resources_wizard_left_area' valign='top'>";
      echo "<div class='plugin_resources_presentation_logo'>";
      echo "<img src='../pics/newresting.png' alt='newresting' /></div>";
      echo "</td>";

      echo "<td class='plugin_resources_wizard_right_area' style='width:500px' valign='top'>";

      echo "<div class='plugin_resources_wizard_title'>";
      echo __('Badge restitution', 'resources');
      echo "</div>";

      echo "<table>";
      //choose resources
      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td>" . PluginResourcesResource::getTypeName(1) . "</td>";

      echo "<td class='left'>";
      $rand = PluginResourcesResource::dropdown(['name'      => 'plugin_resources_resources_id',
                                                 'display'   => true,
                                                 'on_change' => 'plugin_resources_load_badge()',
                                                 'entity'    => $_SESSION['glpiactiveentities']]);

      //display list of badges
      echo "<script type='text/javascript'>";
      echo "function plugin_resources_load_badge(){";
      $params = ['action' => 'loadBadge', 'plugin_resources_resources_id' => '__VALUE__'];
      Ajax::updateItemJsCode('plugin_resources_badge', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcebadge.php',
                             $params, 'dropdown_plugin_resources_resources_id' . $rand);
      $params = ['action' => 'cleanButtonRestitution'];
      Ajax::updateItemJsCode('plugin_resources_button_restitution', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcebadge.php',
                             $params, 'dropdown_plugin_resources_resources_id' . $rand);
      echo "}";

      echo "</script>";
      echo "</td></tr>";

      //tr for badge
      echo "<tr class='plugin_resources_wizard_explain' id='plugin_resources_badge'>";
      echo "</td></tr>";

      echo "</table>";
      echo "</div></td>";
      echo "</tr>";

      echo "<tr><td class='plugin_resources_wizard_button' colspan='2'>";
      echo "<div class='preview'>";
      echo "<a href=\"" . $CFG_GLPI['root_doc'] . "/plugins/badges/front/badge.php\">";
      echo __('List of badges', 'resources');
      echo "</a>";
      echo "</div>";
      echo "<div class='next' id='plugin_resources_button_restitution'>";

      echo "</div>";
      echo "</td></tr></table>";
      Html::closeForm();

      echo "</div>";
   }

   /**
    * List of badges linked to the user
    *
    * @param $users_id
    */
   function loadBadge($plugin_resources_resources_id) {
      global $CFG_GLPI;

      echo "<td>" . PluginBadgesBadge::getTypeName(1) . "</td>";

      $condition = ["plugin_resources_resources_id" => $plugin_resources_resources_id,
                    "itemtype"                      => 'User'];
      $dbu       = new DbUtils();
      $infos     = $dbu->getAllDataFromTable('glpi_plugin_resources_resources_items', $condition);
      $users     = [];
      if (!empty($infos)) {
         foreach ($infos as $info) {
            $users[] = $info['items_id'];
         }
      }

      echo "<td class='left'>";
      $rand = PluginBadgesBadge::dropdown(['name'      => 'badges_id',
                                           'condition' => "`users_id` IN ('" . implode("','", $users) . "')",
                                           'on_change' => 'plugin_resources_load_badge_restitution()'
                                          ]);

      //Button display
      echo "<script type='text/javascript'>";
      echo "function plugin_resources_load_badge_restitution(){";
      $params = ['action' => 'loadBadgeRestitution'];
      Ajax::updateItemJsCode('plugin_resources_button_restitution', $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/resourcebadge.php', $params, 'dropdown_badges_id' . $rand);
      echo "}";

      echo "</script>";
   }

   /**
    * Button display
    */
   function loadBadgeRestitution() {

      echo "<input type='submit' name='plugin_resources_badge_restitution' value='" . _sx('button', 'Save') . "' class='submit' />";
   }


   /**
    * Creation of ticket for restitution badge
    *
    * @param $data
    *
    * @return bool
    */
   static function createTicket($plugin_resources_resources_id, $options = []) {

      $resource = new PluginResourcesResource();
      $resource->getFromDB($plugin_resources_resources_id);

      //Preparation of ticket data
      $data                       = [];
      $data['itilcategories_id']  = 0;
      $data['tickettemplates_id'] = 0;

      //Search for the entity-related category for that action
      $resource_change = new PluginResourcesResource_Change();
      if ($resource_change->getFromDBByCrit(['actions_id'  => PluginResourcesResource_Change::BADGE_RESTITUTION,
                                             'entities_id' => $resource->fields['entities_id']])) {
         $data['itilcategories_id'] = $resource_change->fields['itilcategories_id'];

         //Search of the ticket template
         $itil_category = new ITILCategory();
         if ($itil_category->getFromDB($data['itilcategories_id'])) {
            $data['tickettemplates_id'] = $itil_category->fields['tickettemplates_id_demand'];
         }
      }

      $result = false;
      $tt     = new TicketTemplate();

      // Create ticket based on ticket template and entity informations of ticketrecurrent
      if ($tt->getFromDB($data['tickettemplates_id'])) {
         // Get default values for ticket
         $input = Ticket::getDefaultValues($resource->fields['entities_id']);
         // Apply tickettemplates predefined values
         $ttp        = new TicketTemplatePredefinedField();
         $predefined = $ttp->getPredefinedFields($data['tickettemplates_id'], true);

         if (count($predefined)) {
            foreach ($predefined as $predeffield => $predefvalue) {
               $input[$predeffield] = $predefvalue;
            }
         }
      } else {

      }

      // Set date to creation date
      $createtime                             = date('Y-m-d H:i:s');
      $input['date']                          = $createtime;
      $input['type']                          = Ticket::DEMAND_TYPE;
      $input['entities_id']                   = $resource->fields['entities_id'];
      $input['plugin_resources_resources_id'] = $plugin_resources_resources_id;
      $input['itilcategories_id']             = $data['itilcategories_id'];
      $input['tickettemplates_id']            = $data['tickettemplates_id'];

      $input['users_id_recipient']  = Session::getLoginUserID();
      $input['_users_id_requester'] = Session::getLoginUserID();
      $input["items_id"]            = ['PluginResourcesResource' => [$plugin_resources_resources_id],
                                       'PluginBadgesBadge'       => [$options['badges_id']]];

      // Compute time_to_resolve if predefined based on create date
      if (isset($predefined['time_to_resolve'])) {
         $input['time_to_resolve'] = Html::computeGenericDateTimeSearch($predefined['time_to_resolve'], false,
                                                                        strtotime($createtime));
      }

      $input["name"]    = __('Badge restitution', 'resources') . '&nbsp;:&nbsp;' . " " . PluginResourcesResource::getResourceName($plugin_resources_resources_id);
      $input["content"] = __('Badge restitution', 'resources') . '&nbsp;:&nbsp;' . " " . PluginResourcesResource::getResourceName($plugin_resources_resources_id) . "\n";
      $input["content"] .= PluginBadgesBadge::getTypeName(1) . '&nbsp;:&nbsp;' . " " . Dropdown::getDropdownName('glpi_plugin_badges_badges', $options['badges_id']);
      $input["content"] .= addslashes("\n\n");
      $input['id']      = 0;
      $ticket           = new Ticket();
      $input            = Toolbox::addslashes_deep($input);

      if ($tid = $ticket->add($input)) {
         $msg    = __('Create a end treatment ticket', 'resources') . " OK - ($tid)"; // Success
         $result = true;
      } else {
         $msg = __('Failed operation'); // Failure
      }
      if ($tid) {
         $changes[0] = 0;
         $changes[1] = '';
         $changes[2] = addslashes($msg);
         Log::history($input['plugin_resources_resources_id'], "PluginResourcesResource", $changes, '', Log::HISTORY_LOG_SIMPLE_MESSAGE);
      }
      return $result;
   }


}
