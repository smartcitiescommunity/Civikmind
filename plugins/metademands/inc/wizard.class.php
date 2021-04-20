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

/**
 * Class PluginMetademandsWizard
 */
class PluginMetademandsWizard extends CommonDBTM {

   static $rightname = 'plugin_metademands';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    *
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {
      return __('Wizard overview', 'metademands');
   }

   /**
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
   function canUpdateRequester() {
      return Session::haveRight('plugin_metademands_requester', 1);
   }

   /**
    * @param \User $user
    */
   static function showUserInformations(User $user) {

      echo "<span class='speech'>";
      echo "<button type='button' class='speechcloseButton' onclick='$(this).parent().hide();'>x</button>";
      $infos = getUserName($user->getID(), 2);
      echo $infos['comment'];

      $cond['is_requester'] = 1;
      $groups               = PluginMetademandsField::getUserGroup($_SESSION['glpiactiveentities'],
                                                                   $user->getID(),
                                                                   $cond,
                                                                   false);
      if (count($groups) > 0) {
         echo "<b>" . _n('Group', 'Groups', count($groups)) . "</b> :<br>";
         foreach ($groups as $group) {
            echo Dropdown::getDropdownName("glpi_groups", $group) . "<br>";
         }
      }
      echo "</span>";
   }

   /**
    * @param $options
    *
    * @return bool
    * @throws \GlpitestSQLError
    */
   function showWizard($options) {
      global $CFG_GLPI;

      $config = PluginMetademandsConfig::getInstance();

      $parameters = ['step'              => PluginMetademandsMetademand::STEP_INIT,
                     'metademands_id'    => 0,
                     'preview'           => false,
                     'tickets_id'        => 0,
                     'resources_id'      => 0,
                     'resources_step'    => '',
                     'itilcategories_id' => 0];

      // if given parameters, override defaults
      foreach ($options as $key => $value) {
         if (isset($parameters[$key])) {
            $parameters[$key] = $value;
         }
      }
      $_SESSION['servicecatalog']['sc_itilcategories_id'] = $parameters['itilcategories_id'];
      // Retrieve session values
      if (isset($_SESSION['plugin_metademands']['fields']['tickets_id'])) {
         $parameters['tickets_id'] = $_SESSION['plugin_metademands']['fields']['tickets_id'];
      }
      if (isset($_SESSION['plugin_metademands']['fields']['resources_id'])) {
         $parameters['resources_id'] = $_SESSION['plugin_metademands']['fields']['resources_id'];
      }
      if (isset($_SESSION['plugin_metademands']['fields']['resources_step'])) {
         $parameters['resources_step'] = $_SESSION['plugin_metademands']['fields']['resources_step'];
      }
      Html::requireJs("metademands");
      echo Html::css("/plugins/metademands/css/style_bootstrap_main.css");
      echo Html::css("/plugins/metademands/css/style_bootstrap_ticket.css");
      echo Html::css("/public/lib/base.css");
      echo Html::script("/plugins/metademands/lib/bootstrap/4.5.3/js/bootstrap.bundle.min.js");
      echo "<div id ='content'>";
      if (!$parameters['preview']) {
         echo "<div class='bt-container-fluid metademands_wizard_rank' > ";
      }
      $style = "";
      if ($parameters['preview']) {
         $style = "style='width: 1000px;'";
      }
      echo "<div class='bt-block bt-features' $style> ";

      echo "<form name    = 'wizard_form'
                  method  = 'post'
                  action  = '" . Toolbox::getItemTypeFormURL(__CLASS__) . "'
                  enctype = 'multipart/form-data' 
                  class = 'metademands_img'> ";

      // Case of simple ticket convertion
      echo "<input type = 'hidden' value = '" . $parameters['tickets_id'] . "' name = 'tickets_id' > ";
      // Resources id
      echo "<input type = 'hidden' value = '" . $parameters['resources_id'] . "' name = 'resources_id' > ";
      // Resources step
      echo "<input type = 'hidden' value = '" . $parameters['resources_step'] . "' name = 'resources_step' > ";


      $icon = '';
      if ($parameters['step'] == PluginMetademandsMetademand::STEP_LIST) {
         // Wizard title
         echo "<div class=\"form-row\">";
         echo "<div class=\"bt-feature col-md-12 metademands_wizard_border\">";
         echo "<h4 class=\"bt-title-divider\"><span>";
         $icon = "fa-share-alt";
         $meta = new PluginMetademandsMetademand();
         if ($meta->getFromDB($parameters['metademands_id'])) {
            if (isset($meta->fields['icon']) && !empty($meta->fields['icon'])) {
               $icon = $meta->fields['icon'];
            }
         }
         echo "<i class='fa-2x fas $icon'></i>&nbsp;";
         echo __('Demand choice', 'metademands');
         echo "</span></h4></div></div>";

      } else if ($parameters['step'] >= PluginMetademandsMetademand::STEP_LIST) {
         // Wizard title
         echo "<div class=\"form-row\">";
         echo "<div class=\"bt-feature col-md-12 metademands_wizard_border\">";
         echo "<h4 class=\"bt-title-divider\"><span>";
         $meta = new PluginMetademandsMetademand();
         if ($meta->getFromDB($parameters['metademands_id'])) {
            if (isset($meta->fields['icon']) && !empty($meta->fields['icon'])) {
               $icon = $meta->fields['icon'];
            }
         }
         echo "<i class='fa-2x fas $icon'></i>&nbsp;";
         if (empty($n = PluginMetademandsMetademand::displayField($meta->getID(), 'name'))) {
            echo $meta->getName();
         } else {
            echo $n;
         }
         //         echo Dropdown::getDropdownName('glpi_plugin_metademands_metademands', $parameters['metademands_id']);
         if (Session::haveRight('plugin_metademands', UPDATE)) {
            echo "&nbsp;<a href='" . Toolbox::getItemTypeFormURL('PluginMetademandsMetademand') . "?id=" . $parameters['metademands_id'] . "'>
                        <i class='fas fa-wrench'></i></a>";
         }
         echo "</span></h4>";
         if ($meta->getFromDB($parameters['metademands_id'])
             && !empty($meta->fields['comment'])) {
            if (empty($comment = PluginMetademandsMetademand::displayField($meta->getID(), 'comment'))) {
               $comment = $meta->fields['comment'];
            }
            echo "<label><i>" . nl2br($comment) . "</i></label>";
         }
         echo "</div></div>";

         $plugin = new Plugin();
         if ($plugin->isActivated('servicecatalog')) {
            $configsc = new PluginServicecatalogConfig();
            if ($_SESSION['servicecatalog']['sc_itilcategories_id'] > 0
                && $configsc->seeCategoryDetails()) {
               $helpdesk_category = new PluginServicecatalogCategory();
               if ($helpdesk_category->getFromDBByCategory($_SESSION['servicecatalog']['sc_itilcategories_id'])) {
                  echo "<div class=\"form-row\">";
                  echo "<div class=\"bt-feature col-md-12 \">";
                  echo ($helpdesk_category->fields['comment'] != null) ?
                     "<p class='speech'><button type='button' class='speechcloseButton' onclick='$(this).parent().hide();'>x</button>
                    <span class='titlespeech'>" . __('Description') . "</span><br><br>" . nl2br($helpdesk_category->fields['comment']) . "</p>" : "";
                  echo ($helpdesk_category->fields['service_detail'] != null) ?
                     "<p class='speech'><button type='button' class='speechcloseButton' onclick='$(this).parent().hide();'>x</button>
                    <span class='titlespeech'>" . __('How can i use it', 'servicecatalog') . "</span><br><br>" . Html::clean(nl2br($helpdesk_category->fields['service_detail'])) . "</p>" : "";
                  echo ($helpdesk_category->fields['service_users'] != null) ?
                     "<p class='speech'><button type='button' class='speechcloseButton' onclick='$(this).parent().hide();'>x</button>
                    <span class='titlespeech'>" . __('Who can benefit from this service?', 'servicecatalog') . "</span><br><br>" . Html::clean(nl2br($helpdesk_category->fields['service_users'])) . "</p>" : "";
                  echo ($helpdesk_category->fields['service_ttr'] != null) ?
                     "<p class='speech'><button type='button' class='speechcloseButton' onclick='$(this).parent().hide();'>x</button>
                    <span class='titlespeech'>" . __('Lead time', 'servicecatalog') . "</span><br><br>" . Html::clean(nl2br($helpdesk_category->fields['service_ttr'])) . "</p>" : "";
                  echo ($helpdesk_category->fields['service_use'] != null) ?
                     "<p class='speech'><button type='button' class='speechcloseButton' onclick='$(this).parent().hide();'>x</button>
                    <span class='titlespeech'>" . __('How to obtain the software in case of request?', 'servicecatalog') . "</span><br><br>" . Html::clean(nl2br($helpdesk_category->fields['service_use'])) . "</p>" : "";
                  echo ($helpdesk_category->fields['service_supervision'] != null) ?
                     "<p class='speech'><button type='button' class='speechcloseButton' onclick='$(this).parent().hide();'>x</button>
                        <span class='titlespeech'>" . __('Availability of service', 'servicecatalog') . "</span><br><br>" . Html::clean(nl2br($helpdesk_category->fields['service_supervision'])) . "</p>" : "";
                  echo ($helpdesk_category->fields['service_rules'] != null) ?
                     "<p class='speech'><button type='button' class='speechcloseButton' onclick='$(this).parent().hide();'>x</button>
                    <span class='titlespeech'>" . __('What are the rules to follow ?', 'servicecatalog') . "</span><br><br>" . Html::clean(nl2br($helpdesk_category->fields['service_rules'])) . "</p>" : "";
                  echo "</div></div>";
               }
            }
         }

         // Display user informations
         $userid = Session::getLoginUserID();
         // If ticket exists we get its first requester
         if ($parameters['tickets_id']) {
            $users_id_requester = PluginMetademandsTicket::getUsedActors($parameters['tickets_id'], CommonITILActor::REQUESTER, 'users_id');
            if (count($users_id_requester)) {
               $userid = $users_id_requester[0];
            }
         }

         // Retrieve session values
         if (isset($_SESSION['plugin_metademands']['fields']['_users_id_requester'])) {
            $userid = $_SESSION['plugin_metademands']['fields']['_users_id_requester'];
         }

         $user = new User();
         $user->getFromDB($userid);

         // Rights management
         if (!empty($parameters['tickets_id'])
             && !Session::haveRight('ticket', UPDATE)) {
            self::showMessage(__("You don't have the right to update tickets", 'metademands'), true);
            return false;
            echo "</div>";
            echo "</div>";
            echo "</div>";

         } else if (!self::canCreate()
                    && !PluginMetademandsGroup::isUserHaveRight($parameters['metademands_id'])
         ) {
            self::showMessage(__("You don't have the right to create meta-demand", 'metademands'), true);
            echo "</div>";
            echo "</div>";
            echo "</div>";
            return false;
         }

         //         if ($config['show_requester_informations']) {
         //            echo "<div class=\"form-row\">";
         //            echo "<div class=\"bt-feature col-md-12 metademands_wizard_border\">";
         //            echo "<h4 class=\"bt-title-divider\"><span>";
         //            echo __('General informations', 'metademands');
         //            echo "</span></h4></div>";
         //
         //            // If profile have right on requester update
         //            if ($this->canUpdateRequester() && empty($parameters['tickets_id'])) {
         //               $rand = mt_rand();
         //
         //               echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-6\">";
         //               echo __('Requester') . '&nbsp;:&nbsp;';
         //               User::dropdown(['name'      => "_users_id_requester",
         //                               'value'     => $userid,
         //                               'right'     => 'all',
         //                               'rand'      => $rand,
         //                               'on_change' => "showRequester$rand()"]);
         //               echo "<script type='text/javascript' >\n";
         //               echo "function showRequester$rand() {\n";
         //               $params = ['value'     => '__VALUE__',
         //                          'old_value' => $userid];
         //               Ajax::updateItemJsCode("show_users_id_requester",
         //                                      $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/dropdownWizardUser.php",
         //                                      $params,
         //                                      "dropdown__users_id_requester$rand");
         //
         //               $params = ['value'          => '__VALUE__',
         //                          'old_value'      => $userid,
         //                          'metademands_id' => $parameters['metademands_id']];
         //               Ajax::updateItemJsCode("show_items_id_requester",
         //                                      $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/dropdownWizardItems.php",
         //                                      $params,
         //                                      "dropdown__users_id_requester$rand");
         //
         //               echo "}";
         //               echo "</script>\n";
         //               echo "</div>";
         //            } else {
         //               echo "<input type='hidden' value='" . $userid . "' name='_users_id_requester'>";
         //            }
         //            echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-6\">";
         //            echo "<span id='show_users_id_requester'>";
         //            self::showUserInformations($user);
         //            echo "</span>";
         //            echo "</div>";
         //
         //            echo "</div>";
         //         } else {
         echo "<input type='hidden' value='" . $userid . "' name='_users_id_requester'>";
         //         }

      }
      $options['resources_id']      = $parameters['resources_id'];
      $options['itilcategories_id'] = $parameters['itilcategories_id'];
      self::showWizardSteps($parameters['step'], $parameters['metademands_id'], $parameters['preview'], $options);
      Html::closeForm();
      echo "</div>";
      if (!$parameters['preview']) {
         echo "</div>";
      }
      echo "</div>";
   }

   /**
    * @param       $step
    * @param int   $metademands_id
    * @param bool  $preview
    * @param array $options
    *
    * @throws \GlpitestSQLError
    */
   static function showWizardSteps($step, $metademands_id = 0, $preview = false, $options = []) {

      switch ($step) {
         case PluginMetademandsMetademand::STEP_CREATE:
            $values = isset($_SESSION['plugin_metademands']) ? $_SESSION['plugin_metademands'] : [];
            self::createMetademands($metademands_id, $values, $options);
            break;

         case PluginMetademandsMetademand::STEP_LIST:
            self::listMetademands();
            unset($_SESSION['plugin_metademands']);
            unset($_SESSION['servicecatalog']['sc_itilcategories_id']);
            break;

         case PluginMetademandsMetademand::STEP_INIT:
            self::chooseType($step);
            unset($_SESSION['plugin_metademands']);
            unset($_SESSION['servicecatalog']['sc_itilcategories_id']);
            break;

         default:
            self::showMetademands($metademands_id, $step, $preview, $options);
            break;

      }
      echo "<input type='hidden' name='step' value='" . $step . "'>";
   }

   /**
    * @param $file_data
    */
   //   function uploadFiles($file_data) {
   //
   //      echo "<div class=\"form-row\">";
   //      echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-6\">";
   //      echo "<form name='wizard_form' method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "' enctype='multipart/form-data'>";
   //      echo "<h1>";
   //      echo __('Add documents on the demand', 'metademands');
   //      echo "</h1>";
   //
   //      $ticket = new Ticket();
   //      $ticket->getFromDB($file_data['tickets_id']);
   //
   //      $docadded = $ticket->addFiles($file_data['tickets_id'], 0);
   //      if (count($docadded) > 0) {
   //         foreach ($docadded as $name) {
   //            echo __('Added document', 'metademands') . " $name";
   //         }
   //      }
   //      echo "</div>";
   //      echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-6\">";
   //      echo "<input type='submit' class='submit' name='return' value='" . _sx('button', 'Finish', 'metademands') . "'>";
   //      echo "</div>";
   //
   //      Html::closeForm();
   //      echo "</div>";
   //      echo "</div>";
   //   }

   /**
    * @param $step
    */
   static function chooseType($step) {

      echo "<div class=\"form-row\">";
      echo "<div class=\"bt-feature col-md-12 metademands_wizard_border\">";
      echo "<h4 class=\"bt-title-divider\"><span>";
      echo sprintf(__('Step %d - Ticket type choice', 'metademands'), $step);
      echo "</span></h4>";
      echo "</div>";
      echo "</div>";

      echo "<div class=\"form-row\">";
      echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-6\">";
      // Type
      echo '<b>' . __('Type') . '</b>';
      echo "</div>";
      echo "<div class=\"bt-feature bt-col-sm-6 bt-col-md-6\">";
      $types    = PluginMetademandsTask::getTaskTypes();
      $types[0] = Dropdown::EMPTY_VALUE;
      ksort($types);
      Dropdown::showFromArray('type', $types, ['width' => 150]);
      echo "</div>";
      echo "</div>";

      echo "<div class=\"form-row\">";
      echo "<div class=\"bt-feature col-md-12 right\">";
      echo "<input type='submit' class='submit' name='next' value='" . __('Next') . "'>";
      echo "</div>";
      echo "</div>";
   }

   /**
    * @param string $limit
    *
    * @param int    $type
    *
    * @return array
    * @throws \GlpitestSQLError
    */
   static function selectMetademands($limit = "", $type = Ticket::DEMAND_TYPE) {
      global $DB;

      $dbu   = new DbUtils();
      $query = "SELECT `id`,`name`
                   FROM `glpi_plugin_metademands_metademands`
                   WHERE (is_order = 1  OR `glpi_plugin_metademands_metademands`.`itilcategories_id` <> '')
                   AND type = $type    
                        AND `id` NOT IN (SELECT `plugin_metademands_metademands_id` FROM `glpi_plugin_metademands_metademands_resources`) "
               . $dbu->getEntitiesRestrictRequest(" AND ", 'glpi_plugin_metademands_metademands', '', '', true);
      $query .= "AND is_active ORDER BY `name` $limit";

      $metademands = [];
      $result      = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            //         if (PluginMetademandsGroup::isUserHaveRight($data['id'])) {
            if (empty($name = PluginMetademandsMetademand::displayField($data['id'], 'name'))) {
               $name = $data['name'];
            }
            $metademands[$data['id']] = $name;
            //         }

         }
      }
      return $metademands;
   }

   /**
    * @throws \GlpitestSQLError
    */
   static function listMetademands() {
      global $CFG_GLPI;

      echo Html::css("/plugins/metademands/css/wizard.php");

      $metademands = self::selectMetademands();
      $config      = new PluginMetademandsConfig();
      $config->getFromDB(1);
      $meta = new PluginMetademandsMetademand();
      if ($config->getField('display_type') == 1) {
         $data                        = [];
         $data[Ticket::DEMAND_TYPE]   = Ticket::getTicketTypeName(Ticket::DEMAND_TYPE);
         $data[Ticket::INCIDENT_TYPE] = Ticket::getTicketTypeName(Ticket::INCIDENT_TYPE);

         //         foreach ($data as $type => $typename) {
         //
         //            echo "<a class='bt-buttons' href=''>";
         //            echo '<div class="btnsc-normal" >';
         //            $fasize = "fa-6x";
         //            echo "<div class='center'>";
         //            $icon = "fa-share-alt";
         //            echo "<i class='bt-interface fa-menu-md fas $icon $fasize'></i>";//$style
         //            echo "</div>";
         //            echo "<br><p>";;
         //            echo $typename;
         //            echo "<br><em><span style=\"font-weight: normal;font-size: 11px;padding-left:5px\">";
         //            echo "</span></em>";
         //            echo "</p></div></a>";
         //         }
         echo "<div style='margin-bottom: 10px'>";
         $rand = Dropdown::showFromArray("type", $data, ["value" => Ticket::DEMAND_TYPE]);
         echo "</div>";

         $params = ['type' => '__VALUE__', "action" => "icon"];
         Ajax::updateItemOnSelectEvent("dropdown_type$rand",
                                       "listmeta",
                                       $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/updatelistmeta.php",
                                       $params);
         echo "<div id='listmeta' >";
         foreach ($metademands as $id => $name) {

            $meta = new PluginMetademandsMetademand();
            if ($meta->getFromDB($id)) {

               echo "<a class='bt-buttons' href='" . $CFG_GLPI['root_doc'] . "/plugins/metademands/front/wizard.form.php?metademands_id=" . $id . "&step=2'>";
               echo '<div class="btnsc-normal" >';
               $fasize = "fa-6x";
               echo "<div class='center'>";
               $icon = "fa-share-alt";
               if (!empty($meta->fields['icon'])) {
                  $icon = $meta->fields['icon'];
               }
               echo "<i class='bt-interface fa-menu-md fas $icon $fasize'></i>";//$style
               echo "</div>";

               echo "<br><p>";
               if (empty($n = PluginMetademandsMetademand::displayField($meta->getID(), 'name'))) {
                  echo $meta->getName();
               } else {
                  echo $n;
               }

               echo "<br><em><span style=\"font-weight: normal;font-size: 11px;padding-left:5px\">";
               if (empty($comm = PluginMetademandsMetademand::displayField($meta->getID(), 'comment'))) {
                  echo $meta->fields['comment'];
               } else {
                  echo $comm;
               }
               echo "</span></em>";

               echo "</p></div></a>";
            }
         }
         echo "</div>";
      } else {
         $data                        = [];
         $data[Ticket::DEMAND_TYPE]   = Ticket::getTicketTypeName(Ticket::DEMAND_TYPE);
         $data[Ticket::INCIDENT_TYPE] = Ticket::getTicketTypeName(Ticket::INCIDENT_TYPE);
         echo "<div style='margin-bottom: 10px'>";
         $rand = Dropdown::showFromArray("type", $data, ["value" => Ticket::DEMAND_TYPE]);
         echo "</div>";
         $params = ['type' => '__VALUE__', "action" => "dropdown"];
         Ajax::updateItemOnSelectEvent("dropdown_type$rand",
                                       "listmeta",
                                       $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/updatelistmeta.php",
                                       $params);
         echo "<div id='listmeta' class=\"bt-row\">";
         echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12 \">";
         // METADEMAND list
         echo Ticket::getTicketTypeName(Ticket::DEMAND_TYPE) . "&nbsp;";
         $options['empty_value'] = true;
         $options['type']        = Ticket::DEMAND_TYPE;
         $data                   = $meta->listMetademands(false, $options);
         Dropdown::showFromArray('metademands_id', $data, ['width' => 250]);
         echo "</div>";
         echo "</div>";

         echo "<br/>";
         echo "<div class=\"bt-row\">";
         echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12 right\">";
         echo "<input type='submit' class='submit' name='next' value='" . __('Next') . "'>";
         echo "</div>";
         echo "</div>";
      }
      if (count($metademands) == 0) {
         echo '<div class="bt-feature bt-col-sm-5 bt-col-md-2">';
         echo '<h5 class="bt-title">';
         echo '<span class="de-em">' . __('No advanced request found', 'metademands') . '</span></h5></a>';
         echo '</div>';
      }
   }

   /**
    * @param       $metademands_id
    * @param       $step
    * @param bool  $preview
    *
    * @param array $options
    *
    * @throws \GlpitestSQLError
    */
   static function showMetademands($metademands_id, $step, $preview = false, $options = []) {

      $parameters = ['itilcategories_id' => 0];

      // if given parameters, override defaults
      foreach ($options as $key => $value) {
         if (isset($parameters[$key])) {
            $parameters[$key] = $value;
         }
      }

      $metademands      = new PluginMetademandsMetademand();
      $metademands_data = $metademands->constructMetademands($metademands_id);
      $metademands->getFromDB($metademands_id);

      echo "<div class='md-wizard'>";
      //      echo "<div width='100%'>";
      //Delete metademand wich need to be hide from $metademands_data
      if (isset($_SESSION['metademands_hide'])) {
         foreach ($metademands_data as $form_step => $data) {
            foreach ($data as $form_metademands_id => $line) {
               if (in_array($form_metademands_id, $_SESSION['metademands_hide'])) {
                  unset($metademands_data[$form_step]);
               }
            }
         }
         //Reorder array
         $metademands_data = array_values($metademands_data);
         array_unshift($metademands_data, "", "");
         unset($metademands_data[0]);
         unset($metademands_data[1]);
      }

      if (count($metademands_data)) {
         if ($step - 1 > count($metademands_data) && !$preview) {
            self::showWizardSteps(PluginMetademandsMetademand::STEP_CREATE, $metademands_id, $preview);
         } else {
            echo "</div>";

            foreach ($metademands_data as $form_step => $data) {
               if ($form_step == $step) {
                  foreach ($data as $form_metademands_id => $line) {

                     if ($metademands->fields['is_order'] == 1) {
                        if (!$preview && countElementsInTable("glpi_plugin_metademands_basketlines",
                                                              ["plugin_metademands_metademands_id" => $metademands->fields['id'],
                                                               "users_id"                          => Session::getLoginUserID()])) {
                           echo "<div class='left-div'>";
                        }
                     }
                     if (!isset($_POST['form_metademands_id']) ||
                         (isset($_POST['form_metademands_id']) && $form_metademands_id != $_POST['form_metademands_id'])) {
                        if (!isset($_SESSION['metademands_hide'][$form_metademands_id])) {
                           self::constructForm($metademands_data, $line['form'], $preview, $parameters['itilcategories_id']);
                        } else {
                           $step++;
                        }
                     } else {
                        self::constructForm($metademands_data, $line['form'], $preview, $parameters['itilcategories_id']);

                     }
                     if ($metademands->fields['is_order'] == 1) {
                        if (!$preview && countElementsInTable("glpi_plugin_metademands_basketlines",
                                                              ["plugin_metademands_metademands_id" => $metademands->fields['id'],
                                                               "users_id"                          => Session::getLoginUserID()])) {
                           echo "<div style='text-align: center; margin-top: 20px; margin-bottom : 20px;' class=\"bt-feature col-md-12\">";
                           echo "<input type='submit' class='submit' id='add_to_basket' name='add_to_basket' value='"
                                . _sx('button', 'Add to basket', 'metademands') . "'>";
                           echo "</div>";

                           echo "</div>";
                        }

                        PluginMetademandsBasketline::constructBasket($metademands_id, $line['form'], $preview);
                     }
                     echo "<input type='hidden' name='form_metademands_id' value='" . $form_metademands_id . "'>";
                  }
               }
            }

            if (!$preview) {

               echo "<div class=\"middle-div bt-container-fluid\">";
               echo "<div class=\"bt-feature col-md-12 \">";
               echo "</div>";
               echo "</div>";

               echo "<div class=\"form-row\">";

               echo "<div class=\"bt-feature col-md-12 \">";
               echo "<input type='hidden' name='metademands_id' value='" . $metademands_id . "'>";
               echo "<input type='hidden' name='update_fields'>";
               //verify if have sons metademand
               if ($step - 1 >= count($metademands_data)) {
                  echo "<input type='hidden' name='create_metademands'>";
                  echo "<a href='#' class='metademand_middle_button' onclick='window.print();return false;'>";
                  echo "<i class='fas fa-2x fa-print' style='color:#e3e0e0;'></i>";
                  echo "</a>";
                  if ($metademands->fields['is_order'] == 1) {
                     if (!countElementsInTable("glpi_plugin_metademands_basketlines",
                                               ["plugin_metademands_metademands_id" => $metademands->fields['id'],
                                                "users_id"                          => Session::getLoginUserID()])) {
                        echo "<input type='submit' class='submit metademand_next_button' id='add_to_basket' name='add_to_basket' value='"
                             . _sx('button', 'Add to basket', 'metademands') . "'>";
                     } else {
                        echo "<input type='submit' class='submit metademand_next_button' name='next' value='" . _sx('button', 'Validate your basket', 'metademands') . "'>";
                     }
                  } else {
                     echo "<input type='submit' class='submit metademand_next_button' name='next' value='" . _sx('button', 'Post') . "'>";
                  }

               } else {
                  echo "<input type='submit' class='metademand_next_button submit' name='next' value='" . __('Next') . "'>";
               }
               echo "<input type='submit' class='metademand_previous_button submit' name='previous' value='" . __('Previous') . "'>";
               echo "</div>";
               echo "</div>";
               echo "</div>";
            }

            //            if ($metademands->getField('is_order')) {
            //               PluginMetademandsBasketline::constructBasket($metademands_id, $line['form'], $preview);
            //            }
         }
      } else {
         echo "</div>";
         echo "<div class='center first-bloc'>";
         echo "<div class=\"form-row\">";
         echo "<div class=\"bt-feature col-md-12 \">";
         echo __('No item to display');
         echo "</div></div>";
         echo "<div class=\"form-row\">";
         echo "<div class=\"bt-feature col-md-12 \">";
         echo "<input type='submit' class='submit' name='previous' value='" . __('Previous') . "'>";
         echo "<input type='hidden' name='previous_metademands_id' value='" . $metademands_id . "'>";
         echo "</td>";
         echo "</tr>";
         echo "</div></div>";
      }
   }

   /**
    * @param array $line
    * @param       $metademands_data
    * @param bool  $preview
    * @param int   $itilcategories_id
    */
   static function constructForm($metademands_data, $line = [], $preview = false, $itilcategories_id = 0) {

      $count   = 0;
      $columns = 2;

      if (count($line)) {
         $style            = '';
         $style_left_right = '';
         $keys             = array_keys($line);
         $keyIndexes       = array_flip($keys);

         $rank  = $line[$keys[0]]['rank'];
         echo "<div bloc-id='bloc" . $rank . "'>";
         // Color
         if ($preview) {

            $color = PluginMetademandsField::setColor($rank);
            $style = 'padding-top:5px; 
                      border-top :3px solid #' . $color . ';
                      border-left :3px solid #' . $color . ';
                      border-right :3px solid #' . $color;
            echo '<style type="text/css">
                       .preview-md-';
            echo $rank;
            echo ':before {
                         content: attr(data-title);
                         background: #';
            echo $color . ";";
            echo 'position: absolute;
                               padding: 0 20px;
                               color: #fff;
                               right: 0;
                               top: 0;
                           }
                          </style>';

            echo "<div class=\"form-row preview-md preview-md-$rank\" data-title='" . $rank . "' style='$style'>";
         } else {
            echo "<div class=\"form-row\" style='$style'>";
         }
         foreach ($line as $key => $data) {

            $config_link = "";
            if ($preview) {
               $config_link = "&nbsp;<a href='" . Toolbox::getItemTypeFormURL('PluginMetademandsField') . "?id=" . $data['id'] . "'>";
               $config_link .= "<i class='fas fa-wrench'></i></a>";
            }
            // Manage ranks
            if (isset($keyIndexes[$key])
                && isset($keys[$keyIndexes[$key] - 1])
                && $data['rank'] != $line[$keys[$keyIndexes[$key] - 1]]['rank']) {
               echo "</div>";
               echo "</div>";
               echo "<div bloc-id='bloc" . $data["rank"] . "'>";
               if ($preview) {
                  $rank  = $data['rank'];
                  $color = PluginMetademandsField::setColor($data['rank']);
                  echo '<style type="text/css">
                       .preview-md-';
                  echo $rank;
                  echo ':before {
                         content: attr(data-title);
                         background: #';
                  echo $color . ";";
                  echo 'position: absolute;
                               padding: 0 20px;
                               color: #fff;
                               right: 0;
                               top: 0;
                           }
                          </style>';
                  $style = 'padding-top:5px; 
                            border-top :3px solid #' . $color . ';
                            border-left :3px solid #' . $color . ';
                            border-right :3px solid #' . $color;
                  echo "<div class=\"form-row preview-md preview-md-$rank\" data-title='" . $rank . "' style='$style'>";
               } else {
                  echo "<div class=\"form-row\" style='$style'>";
               }

               $count = 0;
            }

            // If values are saved in session we retrieve it
            if (isset($_SESSION['plugin_metademands']['fields'])) {

               foreach ($_SESSION['plugin_metademands']['fields'] as $id => $value) {
                  if ($data['id'] == $id) {
                     $data['value'] = $value;
                  } else if ($data['id'] . '-2' == $id) {
                     $data['value-2'] = $value;
                  }
               }
            }

            // Title field
            if ($data['type'] == 'title') {
               echo "<div class=\"bt-feature col-md-12 metademands_wizard_border\" style='width: 100%'>";
               echo "<h4 class=\"bt-title-divider\"><span style='color:" . $data['color'] . ";'>";

               if (empty($label = PluginMetademandsField::displayField($data['id'], 'name'))) {
                  $label = $data['name'];
               }

               echo $label;
               echo $config_link;
               if (isset($data['label2']) && !empty($data['label2'])) {
                  echo "&nbsp;";
                  if (empty($label2 = PluginMetademandsField::displayField($data['id'], 'label2'))) {
                     $label2 = $data['label2'];
                  }
                  Html::showToolTip(Html::clean($label2),
                                    ['awesome-class' => 'fa-info-circle']);
               }
               echo "</span></h4>";
               if (!empty($data['comment'])) {
                  if (empty($comment = PluginMetademandsField::displayField($data['id'], 'comment'))) {
                     $comment = $data['comment'];
                  }
                  $comment = htmlspecialchars_decode(stripslashes($comment));
                  echo "<label><i>" . $comment . "</i></label>";
               }

               echo "</div>";
               $count = $count + $columns;

               // Other fields
            } else {
               $style = "";
               $class = "";
               if ($data['type'] == 'informations') {
                  $color = $data['color'];
                  $style = "style='background-color: $color;'";
                  $class = "metademands_wizard_informations";
               }
               if ($data['row_display'] == 1) {
                  echo "<div id-field='field" . $data["id"] . "' $style class=\"form-group col-md-11 $class\">";
                  $count++;
               } else {
                  echo "<div id-field='field" . $data["id"] . "' $style class=\"form-group col-md-5 $class\">";
               }
               //see fields
               PluginMetademandsField::getFieldType($data, $metademands_data, $preview, $config_link, $itilcategories_id);

               // Label 2 (date interval)
               if (!empty($data['label2'])
                   && $data['type'] != 'link') {
                  $required = "";
                  if ($data['is_mandatory']) {
                     $required = "required";
                  }

                  if ($data['type'] == 'datetime_interval' || $data['type'] == 'date_interval') {
                     echo "</div><div class=\"form-group col-md-5\">";
                  } else {
                     echo "<div class=\"form-group metademands_wizard_label2\">";
                  }
                  if (empty($label2 = PluginMetademandsField::displayField($data['id'], 'label2'))) {
                     $label2 = htmlspecialchars_decode(stripslashes($data['label2']));

                  }
                  if ($data['type'] != 'datetime_interval' && $data['type'] != 'date_interval') {
                     echo "<label class='col-form-label col-form-label-sm'>" . $label2 . "</label>";
                  } else {
                     echo "<label  $required for='field[" . $data['id'] . "-2]' class='col-form-label col-form-label-sm'>" . $label2 . "</label>";
                  }
                  $value2 = '';
                  if (isset($data['value-2'])) {
                     $value2 = $data['value-2'];
                  }

                  switch ($data['type']) {
                     case 'date_interval':
                        Html::showDateField("field[" . $data['id'] . "-2]", ['value' => $value2]);
                        $count++; // If date interval : pass to next line
                        break;
                     case 'datetime_interval':
                        Html::showDateTimeField("field[" . $data['id'] . "-2]", ['value' => $value2]);
                        $count++; // If date interval : pass to next line
                        break;
                  }
                  if ($data['type'] != 'datetime_interval' && $data['type'] != 'date_interval') {
                     echo "</div>";
                  }
               }
               echo "</div>";
            }

            // If next field is date interval : pass to next line
            if (isset($keyIndexes[$key])
                && isset($keys[$keyIndexes[$key] + 1])
                && ($line[$keys[$keyIndexes[$key] + 1]]['type'] == 'datetime_interval' || $line[$keys[$keyIndexes[$key] + 1]]['type'] == 'date_interval')) {
               $count++;

            }

            $count++;

            // Next row
            if ($count >= $columns) {
               if ($preview) {
                  $color            = PluginMetademandsField::setColor($data['rank']);
                  $style_left_right = 'border-left :3px solid #' . $color . ';
                                       border-right :3px solid #' . $color;
               }

               echo "</div>";
               echo "<div class=\"form-row\" style='$style_left_right'>";
               $count = 0;
            }
         }
         echo "</div>";
         echo "</div>";
         if ($preview) {
            $color = PluginMetademandsField::setColor($line[$keys[count($keys) - 1]]['rank']);
            echo "<div class=\"form-row\" style='border-bottom: 3px solid #" . $color . ";' >";
            echo "</div>";
         }

         // Fields linked
         foreach ($line as $data) {
            if (!empty($data['fields_link'])
                && is_array(PluginMetademandsField::_unserialize($data['fields_link']))) {

               $script = "";
               if ($data['fields_link']) {
                  $fields_link  = PluginMetademandsField::_unserialize($data['fields_link']);
                  $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                  $fields_link2 = $fields_link;
                  if (count($fields_link) > 0) {

                     foreach ($fields_link as $key => $fields) {
                        $rand = mt_rand();
                        if (isset($check_value[$key])) {
                           $script .= "var metademandWizard$rand = $(document).metademandWizard();";
                           $script .= "metademandWizard$rand.metademand_setMandatoryField(
                         'metademands_wizard_red" . $fields_link[$key] . "', 
                         'field[" . $data['id'] . "]',[";
                           if ($check_value[$key] > 0) {
                              $script .= $check_value[$key];
                           }
                           foreach ($fields_link2 as $key2 => $fields2) {
                              if ($key != $key2) {
                                 if ($fields_link[$key] == $fields_link[$key2]) {
                                    $script .= "," . $check_value[$key2];
                                 }
                              }
                           }

                           $script .= "], '" . $data['item'] . "');";
                        }
                     }
                  }
               }
               echo Html::scriptBlock('$(document).ready(function() {' . $script . '});');
            }
         }

         foreach ($line as $data) {
            if (!empty($data['hidden_link'])) {
               switch ($data['type']) {
                  case 'yesno':
                     $script2 = "";
                     $script  = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_link']))) {
                        $hidden_link = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value = PluginMetademandsField::_unserialize($data['check_value']);
                        if (is_array($check_value) && count($check_value) > 0) {
                           foreach ($hidden_link as $key => $fields) {
                              $script .= "
                          if($(this).val() == $check_value[$key]){
                            $('[id-field =\"field" . $hidden_link[$key] . "\"]').show();
                            
                          }else{
                           $('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();
                          }
                           ";
                              if ($check_value[$key] == $data["custom_values"]) {
                                 $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                 if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                     && $_SESSION['plugin_metademands']['fields'][$data["id"]] != $check_value[$key]) {
                                    $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                                 }
                              } else {
                                 $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                                 if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                     && $_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key]) {
                                    $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                 }
                              }
                           }
                        }
                     }
                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_link    = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              $idc = isset($check_value[$k]) ? $check_value[$k] : 0;
                              $idv = ($idc > 0) ? $hidden_link[$idc] : 0;
                              if ($idv > 0) {
                                 $script .= " $('[id-field =\"field" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');

                     break;
                  case 'dropdown_multiple':
                     if ($data["display_type"] == PluginMetademandsField::CLASSIC_DISPLAY) {
                        $script = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";

                        $script2 = "";
                        if (is_array(PluginMetademandsField::_unserialize($data['hidden_link']))) {
                           $hidden_link  = PluginMetademandsField::_unserialize($data['hidden_link']);
                           $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                           $custom_value = PluginMetademandsField::_unserialize($data['custom_values']);
                           $script       .= "var tohide = {};";
                           foreach ($hidden_link as $key => $fields) {
                              $script .= "
                           if($fields in tohide){
                              
                           }else{
                              tohide[$fields] = true;                        
                           }
                           ";
                           }
                           $script .= "
                          $.each($(this).siblings('span.select2').children().find('li.select2-selection__choice'), function( key, value ) {
                          ";
                           foreach ($check_value as $key => $fields) {
                              if ($fields != 0) {
                                 if ($data["item"] == "other") {
                                    $script .= "
                                       if($(value).attr('title') == '$custom_value[$fields]'){
                                          tohide[" . $hidden_link[$key] . "] = false;
                                       }
                                    ";
                                 } else {
                                    $script .= "
                                       if($(value).attr('title') == '" . $data["item"]::getFriendlyNameById($fields) . "'){
                                          tohide[" . $hidden_link[$key] . "] = false;
                                       }
                                    ";
                                 }

                                 $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                                 if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                     && $_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key]) {
                                    $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                 }
                                 if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])) {
                                    foreach ($_SESSION['plugin_metademands']['fields'][$data["id"]] as $fieldSession) {
                                       if ($fieldSession == $check_value[$key]) {
                                          $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                       }
                                    }
                                 }
                              }
                           }

                           $script .= "});";
                           $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[id-field =\"field'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[id-field =\"field'+key+'\"]').show();
                            
                                    }
                                   
                                 });";
                           $script .= "});";

                        }

                        //Initialize id default value
                        if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                           $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                           $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                           $hidden_link    = PluginMetademandsField::_unserialize($data['hidden_link']);
                           $check_value    = array_flip($check_value);
                           foreach ($default_values as $k => $v) {
                              if ($v == 1) {
                                 $idc = isset($check_value[$k]) ? $check_value[$k] : 0;
                                 $idv = ($idc > 0) ? $hidden_link[$idc] : 0;
                                 if ($idv > 0) {
                                    $script .= " $('[id-field =\"field" . $idv . "\"]').show();";
                                 }
                              }
                           }
                        }
                        echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     } else {
                        $script = "$('[name^=\"field[" . $data["id"] . "]\"]').on('DOMSubtreeModified',function() {";

                        $script2 = "";
                        if (is_array(PluginMetademandsField::_unserialize($data['hidden_link']))) {
                           $hidden_link  = PluginMetademandsField::_unserialize($data['hidden_link']);
                           $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                           $custom_value = PluginMetademandsField::_unserialize($data['custom_values']);
                           $script       .= "var tohide = {};";
                           foreach ($hidden_link as $key => $fields) {
                              $script .= "
                           if($fields in tohide){
                              
                           }else{
                              tohide[$fields] = true;                        
                           }
                           ";
                           }
                           $script .= "
                          $.each($('#multiselect" . $data["id"] . "_to').children(), function( key, value ) {
                          ";
                           foreach ($check_value as $key => $fields) {
                              if ($fields != 0) {
                                 $script  .= " 
                           if($(value).attr('value') == '$fields'){
                           
                              tohide[" . $hidden_link[$key] . "] = false;
                           }
                        ";
                                 $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                                 if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                     && $_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key]) {
                                    $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                 }
                                 if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])) {
                                    foreach ($_SESSION['plugin_metademands']['fields'][$data["id"]] as $fieldSession) {
                                       if ($fieldSession == $check_value[$key]) {
                                          $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                       }
                                    }
                                 }
                              }
                           }

                           $script .= "});";
                           $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[id-field =\"field'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[id-field =\"field'+key+'\"]').show();
                            
                                    }
                                   
                                 });";
                           $script .= "});";
                           //                           $script .= "$('[name^=\"field[" . $data["id"] . "]\"]').on('DOMSubtreeModified', function(){
                           //                                     console.log('changed');
                           //                                 });";

                        }

                        //Initialize id default value
                        if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                           $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                           $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                           $hidden_link    = PluginMetademandsField::_unserialize($data['hidden_link']);
                           $check_value    = array_flip($check_value);
                           foreach ($default_values as $k => $v) {
                              if ($v == 1) {
                                 $idc = isset($check_value[$k]) ? $check_value[$k] : 0;
                                 $idv = ($idc > 0) ? $hidden_link[$idc] : 0;
                                 if ($idv > 0) {
                                    $script .= " $('[id-field =\"field" . $idv . "\"]').show();";
                                 }
                              }
                           }
                        }
                        echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     }

                     break;
                  case 'checkbox':
                     $script = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";
                     //             $script .= "      alert( \"Handler for .change() called.  \"+$(this).val()  );";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_link']))) {
                        $hidden_link = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value = PluginMetademandsField::_unserialize($data['check_value']);
                        $script2     = "";
                        $script      .= "var tohide = {};";
                        if (is_array($check_value) && count($check_value) > 0) {
                           //                     $('[name^=\"field[".$data["id"]."]\"]').each()
                           $script .= " if (this.checked){ ";
                           foreach ($hidden_link as $key => $fields) {

                              $script .= " if($(this).val() == $check_value[$key] || $check_value[$key] == -1){
                           if($fields in tohide){
                           
                           }else{
                              tohide[$fields] = true;                        
                           }
                           tohide[$fields] = false;
                        }
                         ";

                              $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && is_array($_SESSION['plugin_metademands']['fields'][$data["id"]])) {
                                 foreach ($_SESSION['plugin_metademands']['fields'][$data["id"]] as $fieldSession) {
                                    if ($fieldSession == $check_value[$key]) {
                                       $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                    }
                                 }
                              }
                           }


                           $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[id-field =\"field'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[id-field =\"field'+key+'\"]').show();
                            
                                    }
                                   
                                 });";
                           $script .= "} else {";
                           foreach ($hidden_link as $key => $fields) {
                              $script .= "if($(this).val() == $check_value[$key]){
                           if($fields in tohide){
                           
                           }else{
                              tohide[$fields] = true;                        
                           }
                           $.each( $('[name^=\"field[" . $data["id"] . "]\"]:checked'),function( index, value ){
                             ";
                              foreach ($hidden_link as $key2 => $fields2) {
                                 $script .= "if($(value).val() == $check_value[$key2] || $check_value[$key2] == -1){
                              tohide[$fields2] = false;
                           }
                          ";
                              }
                              $script .= " 
                           });
                        }";

                              $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && is_array($_SESSION['plugin_metademands']['fields'][$data["id"]])) {
                                 foreach ($_SESSION['plugin_metademands']['fields'][$data["id"]] as $fieldSession) {
                                    if ($fieldSession == $check_value[$key]) {
                                       $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                    }
                                 }
                              }
                           }

                           $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[id-field =\"field'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[id-field =\"field'+key+'\"]').show();
                            
                                    }
                                   
                                 });";
                           $script .= "}";
                        }
                     }
                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_link    = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              $idc = isset($check_value[$k]) ? $check_value[$k] : 0;
                              $idv = ($idc > 0) ? $hidden_link[$idc] : 0;
                              if ($idv > 0) {
                                 $script .= " $('[id-field =\"field" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;

                  case 'text':
                  case 'textarea':
                     $script  = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";
                     $script2 = "";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_link']))) {
                        $hidden_link = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value = PluginMetademandsField::_unserialize($data['check_value']);
                        foreach ($hidden_link as $key => $fields) {
                           if (isset($check_value[$key]) && $check_value[$key] == 1) {
                              $script  .= "
                           if($(this).val().trim().length < 1){
                              $('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();
                           }else{
                              $('[id-field =\"field" . $hidden_link[$key] . "\"]').show();
                           }
                        
                         ";
                              $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && $_SESSION['plugin_metademands']['fields'][$data["id"]] != "") {
                                 $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                              }
                           } else {
                              $script .= "
                           if($(this).val().trim().length < 1){
                                 $('[id-field =\"field" . $hidden_link[$key] . "\"]').show();
                              }else{
                                 $('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();
                              }
                         ";

                              $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && $_SESSION['plugin_metademands']['fields'][$data["id"]] == "") {
                                 $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                              }
                           }

                        }
                     }
                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_link    = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              $idc = isset($check_value[$k]) ? $check_value[$k] : 0;
                              $idv = ($idc > 0) ? $hidden_link[$idc] : 0;
                              if ($idv > 0) {
                                 $script .= " $('[id-field =\"field" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;


                  case 'radio':
                     $script2 = "";

                     $script = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";
                     //             $script .= "      alert( \"Handler for .change() called.  \"+$(this).val()  );";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_link']))) {
                        $hidden_link = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value = PluginMetademandsField::_unserialize($data['check_value']);
                        $script      .= "var tohide = {};";
                        foreach ($hidden_link as $key => $fields) {
                           $script  .= "
                        if($fields in tohide){
                        
                        }else{
                           tohide[$fields] = true;                        
                        }
                        if($(this).val() == $check_value[$key] || $check_value[$key] == -1){
                           tohide[$fields] = false;
                        }
                         ";
                           $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                           if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                               && ($_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key] || $check_value[$key] == -1)) {
                              $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                           }
                        }
                        $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[id-field =\"field'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[id-field =\"field'+key+'\"]').show();
                                    
                                    }
                                   
                                 });";
                     }
                     //                     else {
                     //                        $script .= "if($(this).val() == " . $data['check_value'] . "){
                     //                           $('[id-field =\"field" . $data['hidden_link'] . "\"]').show();
                     //                        }else{
                     //                            $('[id-field =\"field" . $data['hidden_link'] . "\"]').hide();
                     //                        }
                     //                         ";
                     //                        $script2 = "$('[id-field =\"field" . $data['hidden_link'] . "\"]').hide();";
                     //                     }
                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_link    = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              $idc = isset($check_value[$k]) ? $check_value[$k] : 0;
                              $idv = ($idc > 0) ? $hidden_link[$idc] : 0;
                              if ($idv > 0) {
                                 $script .= " $('[id-field =\"field" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;

                  case 'group':
                  case 'dropdown':
                  case 'dropdown_object':
                  case 'dropdown_meta':
                     $script = "$('[name=\"field[" . $data["id"] . "]\"]').change(function() {";
                     //             $script .= "      alert( \"Handler for .change() called.  \"+$(this).val()  );";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_link']))) {
                        $hidden_link = PluginMetademandsField::_unserialize($data['hidden_link']);
                        $check_value = PluginMetademandsField::_unserialize($data['check_value']);
                        $script2     = "";
                        $script      .= "var tohide = {};";
                        if (is_array($check_value) && count($check_value) > 0) {
                           foreach ($hidden_link as $key => $fields) {
                              $script .= "
                        if($fields in tohide){
                        
                        }else{
                           tohide[$fields] = true;                        
                        }
                        if($(this).val() != 0 && ($(this).val() == $check_value[$key] || $check_value[$key] == 0 ) ){
                           tohide[$fields] = false;
                        }";

                              $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && ($_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key] || ($_SESSION['plugin_metademands']['fields'][$data["id"]] != 0 && $check_value[$key] == 0))) {
                                 $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                              } else {
                                 if ($data['type'] == "dropdown_object" && $data['item'] == 'User') {
                                    if (Session::getLoginUserID() == $check_value[$key]) {
                                       $script2 .= "$('[id-field =\"field" . $hidden_link[$key] . "\"]').show();";
                                    }
                                 }
                              }
                           }
                           $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[id-field =\"field'+key+'\"]').hide();
                                 
                                    }else{
                                    $('[id-field =\"field'+key+'\"]').show();
                                   
                                    }
                                   
                                 });";
                        }
                     }
                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_link    = PluginMetademandsField::_unserialize($data['hidden_link']);
                        if (is_array($check_value) && count($check_value) > 0) {
                           $check_value = array_flip($check_value);

                           foreach ($default_values as $k => $v) {
                              if ($v == 1) {
                                 foreach ($check_value as $key => $val) {
                                    if ($k == $key || $key == 0) {
                                       $idv = $hidden_link[$val];
                                       if ($idv > 0) {
                                          $script .= " $('[id-field =\"field" . $idv . "\"]').show();";
                                       }
                                    }
                                 }

                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;

               }

            }
            if (!empty($data['hidden_block'])) {
               switch ($data['type']) {
                  case 'yesno':
                     $script2 = "";
                     $script  = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_block']))) {
                        $hidden_block = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                        if (is_array($check_value) && count($check_value) > 0) {
                           foreach ($hidden_block as $key => $fields) {
                              $script .= "
                          if($(this).val() == $check_value[$key]){
                            $('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();
                            
                          }else{
                           $('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();
                          }
                           ";
                              if ($check_value[$key] == $data["custom_values"]) {
                                 $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                                 if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                     && $_SESSION['plugin_metademands']['fields'][$data["id"]] != $check_value[$key]) {
                                    $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                                 }
                              } else {
                                 $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                                 if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                     && $_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key]) {
                                    $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                                 }
                              }
                           }
                        }
                     }
                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_block   = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              if (isset($check_value[$k])) {
                                 $idc     = $check_value[$k];
                                 $idv     = $hidden_block[$idc];
                                 $script2 .= "$('[bloc-id =\"bloc" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');


                     //                  case 'PluginResourcesResource':
                     //                  case 'PluginMetademandsITILApplication':
                     //                  case 'PluginMetademandsITILEnvironment':

                     break;
                  case 'dropdown_multiple':
                     $script  = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";
                     $script2 = "";
                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_block']))) {
                        $hidden_block = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                        $custom_value = PluginMetademandsField::_unserialize($data['custom_values']);
                        $script       .= "var tohide = {};";
                        foreach ($hidden_block as $key => $fields) {
                           $script .= "
                           if($fields in tohide){
                              
                           }else{
                              tohide[$fields] = true;                        
                           }
                           ";
                        }
                        $script .= "
                          $.each($(this).siblings('span.select2').children().find('li.select2-selection__choice'), function( key, value ) {
                          ";
                        foreach ($check_value as $key => $fields) {
                           if ($fields != 0) {
                              $script  .= "
                           if($(value).attr('title') == '$custom_value[$fields]'){
                              tohide[" . $hidden_block[$key] . "] = false;
                           }
                        ";
                              $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && $_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key]) {
                                 $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                              }
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])) {
                                 foreach ($_SESSION['plugin_metademands']['fields'][$data["id"]] as $fieldSession) {
                                    if ($fieldSession == $check_value[$key]) {
                                       $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                                    }
                                 }
                              }
                           }
                        }

                        $script .= "});";
                        $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[bloc-id =\"bloc'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[bloc-id =\"bloc'+key+'\"]').show();
                            
                                    }
                                   
                                 });";
                        $script .= "});";

                     }

                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_block   = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              if (isset($check_value[$k])) {
                                 $idc     = $check_value[$k];
                                 $idv     = $hidden_block[$idc];
                                    $script2 .= "$('[bloc-id =\"bloc" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;
                  case 'checkbox':
                     $script = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";
                     //             $script .= "      alert( \"Handler for .change() called.  \"+$(this).val()  );";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_block']))) {
                        $hidden_block = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                        $script2      = "";
                        $script       .= "var tohide = {};";
                        if (is_array($check_value) && count($check_value) > 0) {
                           //                     $('[name^=\"field[".$data["id"]."]\"]').each()
                           $script .= " if (this.checked){ ";
                           foreach ($hidden_block as $key => $fields) {
                              $script .= "
                        
                        
                        
                        if($(this).val() == $check_value[$key] || $check_value[$key] == -1 ){
                           if($fields in tohide){
                           
                           }else{
                              tohide[$fields] = true;                        
                           }
                           tohide[$fields] = false;
                        }
                         ";

                              $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && is_array($_SESSION['plugin_metademands']['fields'][$data["id"]])) {
                                 foreach ($_SESSION['plugin_metademands']['fields'][$data["id"]] as $fieldSession) {
                                    if ($fieldSession == $check_value[$key] || $check_value[$key] == -1) {
                                       $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                                    }
                                 }
                              }
                           }


                           $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[bloc-id =\"bloc'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[bloc-id =\"bloc'+key+'\"]').show();
                            
                                    }
                                   
                                 });";
                           $script .= "} else {";
                           foreach ($hidden_block as $key => $fields) {
                              $script .= "
                        
                        
                        
                        if($(this).val() == $check_value[$key]){
                           if($fields in tohide){
                           
                           }else{
                              tohide[$fields] = true;                        
                           }
                           $.each( $('[name^=\"field[" . $data["id"] . "]\"]:checked'),function( index, value ){
                             ";
                              foreach ($hidden_block as $key2 => $fields2) {
                                 $script .= "if($(value).val() == $check_value[$key2] || $check_value[$key2] == -1 ){
                              tohide[$fields2] = false;
                           }
                          ";
                              }
                              $script .= " 
                           });
                        }";

                              $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && is_array($_SESSION['plugin_metademands']['fields'][$data["id"]])) {
                                 foreach ($_SESSION['plugin_metademands']['fields'][$data["id"]] as $fieldSession) {
                                    if ($fieldSession == $check_value[$key] || $check_value[$key] == -1) {
                                       $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                                    }
                                 }
                              }
                           }

                           $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[bloc-id =\"bloc'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[bloc-id =\"bloc'+key+'\"]').show();
                            
                                    }
                                   
                                 });";
                           $script .= "}";
                        }
                     }
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_block   = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              if (isset($check_value[$k])) {
                                 $idc     = $check_value[$k];
                                 $idv     = $hidden_block[$idc];
                                 $script2 .= "$('[bloc-id =\"bloc" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     $script .= "});";

                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;

                  case 'text':
                  case 'textarea':
                     $script  = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";
                     $script2 = "";
                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_block']))) {
                        $hidden_block = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                        foreach ($hidden_block as $key => $fields) {
                           if (isset($check_value[$key]) && $check_value[$key] == 1) {
                              $script  .= "
                           if($(this).val().trim().length < 1){
                              $('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();
                           }else{
                              $('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();
                           }
                        
                         ";
                              $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && $_SESSION['plugin_metademands']['fields'][$data["id"]] != "") {
                                 $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                              }
                           } else {
                              $script .= "
                           if($(this).val().trim().length < 1){
                                 $('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();
                              }else{
                                 $('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();
                              }
                         ";

                              $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && $_SESSION['plugin_metademands']['fields'][$data["id"]] == "") {
                                 $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                              }
                           }

                        }
                     }
                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_block   = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              if (isset($check_value[$k])) {
                                 $idc     = $check_value[$k];
                                 $idv     = $hidden_block[$idc];
                                 $script2 .= "$('[bloc-id =\"bloc" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;


                  case 'radio':
                     $script2 = "";
                     $script  = "$('[name^=\"field[" . $data["id"] . "]\"]').change(function() {";
                     //             $script .= "      alert( \"Handler for .change() called.  \"+$(this).val()  );";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_block']))) {
                        $hidden_block = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                        $script       .= "var tohide = {};";
                        foreach ($hidden_block as $key => $fields) {
                           $script  .= "
                        if($fields in tohide){
                        
                        }else{
                           tohide[$fields] = true;                        
                        }
                        if($(this).val() == $check_value[$key] || $check_value[$key] == -1){
                           tohide[$fields] = false;
                        }
                         ";
                           $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                           if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                               && ($_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key] || $check_value[$key] == -1)) {
                              $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                           }
                        }
                        $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[bloc-id =\"bloc'+key+'\"]').hide();
                                   
                                    }else{
                                    $('[bloc-id =\"bloc'+key+'\"]').show();
                                    
                                    }
                                   
                                 });";
                     }

                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_block   = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              if (isset($check_value[$k])) {
                                 $idc     = $check_value[$k];
                                 $idv     = $hidden_block[$idc];
                                 $script2 .= "$('[bloc-id =\"bloc" . $idv . "\"]').show();";
                              }
                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;

                  case 'group':
                  case 'dropdown':
                  case 'dropdown_object':
                  case 'dropdown_meta':
                     $script = "$('[name=\"field[" . $data["id"] . "]\"]').change(function() {";
                     //             $script .= "      alert( \"Handler for .change() called.  \"+$(this).val()  );";

                     if (is_array(PluginMetademandsField::_unserialize($data['hidden_block']))) {
                        $hidden_block = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value  = PluginMetademandsField::_unserialize($data['check_value']);
                        $script2      = "";
                        $script       .= "var tohide = {};";
                        if (is_array($check_value) && count($check_value) > 0) {
                           foreach ($hidden_block as $key => $fields) {
                              $script .= "
                        if($fields in tohide){
                        
                        }else{
                           tohide[$fields] = true;                        
                        }
                        if($(this).val() == $check_value[$key] || ($(this).val() != 0 &&  $check_value[$key] == 0 ) ){
                        
                           tohide[$fields] = false;
                        }
                        
                         ";

                              $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').hide();";
                              if (isset($_SESSION['plugin_metademands']['fields'][$data["id"]])
                                  && ($_SESSION['plugin_metademands']['fields'][$data["id"]] == $check_value[$key] || ($_SESSION['plugin_metademands']['fields'][$data["id"]] != 0 && $check_value[$key] == 0))) {
                                 $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                              } else {
                                 if ($data['type'] == "dropdown_object" && $data['item'] == 'User') {
                                    if (Session::getLoginUserID() == $check_value[$key]) {
                                       $script2 .= "$('[bloc-id =\"bloc" . $hidden_block[$key] . "\"]').show();";
                                    }
                                 }
                              }
                           }
                           $script .= "$.each( tohide, function( key, value ) {
                                    if(value == true){
                                     $('[bloc-id =\"bloc'+key+'\"]').hide();
                                 
                                    }else{
                                    $('[bloc-id =\"bloc'+key+'\"]').show();
                                   
                                    }
                                   
                                 });";
                        }
                     }
                     $script .= "});";
                     //Initialize id default value
                     if (is_array(PluginMetademandsField::_unserialize($data['default_values']))) {
                        $default_values = PluginMetademandsField::_unserialize($data['default_values']);
                        $check_value    = PluginMetademandsField::_unserialize($data['check_value']);
                        $hidden_block   = PluginMetademandsField::_unserialize($data['hidden_block']);
                        $check_value    = array_flip($check_value);
                        foreach ($default_values as $k => $v) {
                           if ($v == 1) {
                              foreach ($check_value as $key => $val) {
                                 if ($k == $key || $key == 0) {
                                    $idv = $hidden_link[$val];
                                    if ($idv > 0) {
                                       $script2 .= "$('[bloc-id =\"bloc" . $idv . "\"]').show();";
                                    }
                                 }
                              }

                           }
                        }
                     }
                     echo Html::scriptBlock('$(document).ready(function() {' . $script2 . " " . $script . '});');
                     break;
                  default:
                     break;
               }

            }
         }

      } else {
         echo "<div class='center'><b>" . __('No item to display') . "</b></div>";
      }
   }


   /**
    * @param       $metademands_id
    * @param       $values
    * @param array $options
    *
    * @throws \GlpitestSQLError
    */
   static function createMetademands($metademands_id, $values, $options = []) {
      global $CFG_GLPI;

      $self        = new self();
      $metademands = new PluginMetademandsMetademand();
      $metademands->getFromDB($metademands_id);

      if ($metademands->fields['is_order'] == 1
          && isset($values['basket'])) {
         $basketclass = new PluginMetademandsBasketline();
         if ($metademands->fields['create_one_ticket'] == 0) {
            //create one ticket for each basket
            foreach ($values['basket'] as $k => $basket) {
               $datas           = [];
               $datas['basket'] = $basket;

               if (isset($values['fields']['_filename'])) {
                  unset($values['fields']['_filename']);
               }
               if (isset($values['fields']['_prefix_filename'])) {
                  unset($values['fields']['_prefix_filename']);
               }
               if (isset($values['fields']['_tag_filename'])) {
                  unset($values['fields']['_tag_filename']);
               }
               $filename   = [];
               $prefixname = [];
               $tagname    = [];
               foreach ($basket as $key => $val) {
                  $line = $k + 1;

                  $check = $basketclass->getFromDBByCrit(["plugin_metademands_metademands_id" => $metademands_id,
                                                          'plugin_metademands_fields_id'      => $key,
                                                          'line'                              => $line,
                                                          'users_id'                          => Session::getLoginUserID(),
                                                          'name'                              => "upload"
                                                         ]);
                  if ($check) {
                     if (!empty($val)) {
                        $files = json_decode($val, 1);
                        foreach ($files as $file) {
                           $filename[]   = $file['_filename'];
                           $prefixname[] = $file['_prefix_filename'];
                           $tagname[]    = $file['_tag_filename'];
                        }
                     }
                  }
               }

               $values['fields']['_filename']        = $filename;
               $values['fields']['_prefix_filename'] = $prefixname;
               $values['fields']['_tag_filename']    = $tagname;

               $datas['fields'] = $values['fields'];


               $result = $metademands->addMetademands($metademands_id, $datas, $options);
               Session::addMessageAfterRedirect($result['message']);
            }
            $basketclass->deleteByCriteria(['plugin_metademands_metademands_id' => $metademands_id,
                                            'users_id'                          => Session::getLoginUserID()]);
         } else {
            //create one ticket for all basket
            if (isset($values['fields']['_filename'])) {
               unset($values['fields']['_filename']);
            }
            if (isset($values['fields']['_prefix_filename'])) {
               unset($values['fields']['_prefix_filename']);
            }
            if (isset($values['fields']['_tag_filename'])) {
               unset($values['fields']['_tag_filename']);
            }
            $filename   = [];
            $prefixname = [];
            $tagname    = [];
            foreach ($values['basket'] as $k => $basket) {
               foreach ($basket as $key => $val) {
                  $line  = $k + 1;
                  $check = $basketclass->getFromDBByCrit([
                                                            "plugin_metademands_metademands_id" => $metademands_id,
                                                            'plugin_metademands_fields_id'      => $key,
                                                            'line'                              => $line,
                                                            'users_id'                          => Session::getLoginUserID(),
                                                            'name'                              => "upload"
                                                         ]);
                  if ($check) {
                     if (!empty($val)) {
                        $files = json_decode($val, 1);
                        foreach ($files as $file) {
                           $filename[]   = $file['_filename'];
                           $prefixname[] = $file['_prefix_filename'];
                           $tagname[]    = $file['_tag_filename'];
                        }
                     }
                  }
               }
            }
            $values['fields']['_filename']        = $filename;
            $values['fields']['_prefix_filename'] = $prefixname;
            $values['fields']['_tag_filename']    = $tagname;

            $basketclass->deleteByCriteria(['plugin_metademands_metademands_id' => $metademands_id,
                                            'users_id'                          => Session::getLoginUserID()]);

            $result = $metademands->addMetademands($metademands_id, $values, $options);
            Session::addMessageAfterRedirect($result['message']);
         }

      } else {
         //not in basket
         $result = $metademands->addMetademands($metademands_id, $values, $options);
         Session::addMessageAfterRedirect($result['message']);
      }

      $itilcategories_id = isset($_SESSION['servicecatalog']['sc_itilcategories_id']) ?
         $_SESSION['servicecatalog']['sc_itilcategories_id'] : 0;

      unset($_SESSION['plugin_metademands']);

      if (!empty($options['resources_id'])) {
         Html::redirect($CFG_GLPI["root_doc"] . "/plugins/resources/front/wizard.form.php");
      } else {

         $plugin = new Plugin();
         if ($plugin->isActivated('servicecatalog')
             && Session::haveRight("plugin_servicecatalog", READ)
             && $itilcategories_id > 0) {
            $type = $metademands->fields['type'];
            Html::redirect($CFG_GLPI["root_doc"] . "/plugins/servicecatalog/front/main.form.php?choose_category&type=$type&level=1");
         } else {
            Html::redirect($self->getFormURL() . "?step=" . $step = PluginMetademandsMetademand::STEP_LIST);
         }
      }
   }

   /**
    * @param      $message
    * @param bool $error
    */
   static function showMessage($message, $error = false) {
      $class = $error ? "style='color:red'" : "";

      echo "<br><div class='box'>";
      echo "<div class='box-tleft'><div class='box-tright'><div class='box-tcenter'>";
      echo "</div></div></div>";
      echo "<div class='box-mleft'><div class='box-mright'><div class='box-mcenter center'>";
      echo "<h3 $class>" . $message . "</h3>";
      echo "</div></div></div>";
      echo "<div class='box-bleft'><div class='box-bright'><div class='box-bcenter'>";
      echo "</div></div></div>";
      echo "</div>";
   }

   /**
    * @param       $value
    * @param       $id
    * @param       $post
    * @param       $fieldname
    * @param false $on_basket
    *
    * @return array
    */
   static function checkvalues($value, $id, $post, $fieldname, $on_basket = false) {

      $KO      = false;
      $content = [];
      if (($value['type'] == 'date_interval' || $value['type'] == 'datetime_interval') && !isset($value['second_date_ok'])) {
         $value['second_date_ok'] = true;
         $value['id']             = $id . '-2';
         $value['name']           = $value['label2'];
         $data[$id . '-2']        = $value;
      }

      if (isset($post[$fieldname][$id])
          && $value['type'] != 'checkbox'
          && $value['type'] != 'radio'
          && $value['item'] != 'ITILCategory_Metademands'
          && $value['type'] != 'upload') {

         if (!self::checkMandatoryFields($fieldname, $value, ['id'    => $id,
                                                              'value' => $post[$fieldname][$id]],
                                         $post)) {
            $KO = true;
         } else {
            $_SESSION['plugin_metademands']['fields'][$id] = $post[$fieldname][$id];
         }

      } else if ($value['item'] == 'ITILCategory_Metademands') {

         $content[$id]['plugin_metademands_fields_id'] = $id;
         if ($on_basket == false) {
            $content[$id]['value'] = $post['field_plugin_servicecatalog_itilcategories_id'];
         } else {
            $content[$id]['value'] = $post['basket_plugin_servicecatalog_itilcategories_id'];
         }

         $content[$id]['value2'] = "";
         $content[$id]['item']   = $value['item'];
         $content[$id]['type']   = $value['type'];

      } else if ($value['type'] == 'checkbox') {

         if (!self::checkMandatoryFields($fieldname, $value, ['id' => $id, 'value' => $post[$fieldname][$id]], $post)) {
            $KO = true;
         } else {
            $_SESSION['plugin_metademands']['fields'][$id] = $post[$fieldname][$id];
         }
      } else if ($value['type'] == 'radio') {

         if (!self::checkMandatoryFields($fieldname, $value, ['id' => $id, 'value' => $post[$fieldname][$id]], $post)) {
            $KO = true;
         } else {
            $_SESSION['plugin_metademands']['fields'][$id] = $post[$fieldname][$id];
         }
      } else if ($value['type'] == 'upload') {

         if ($value['is_basket'] == 1
             && isset($post[$fieldname][$id]) && !empty($post[$fieldname][$id])) {
            $files = json_decode($post[$fieldname][$id], 1);
            foreach ($files as $file) {
               $post['_filename'][]        = $file['_filename'];
               $post['_prefix_filename'][] = $file['_prefix_filename'];
               $post['_tag_filename'][]    = $file['_tag_filename'];
            }
         }
         if (!self::checkMandatoryFields($fieldname, $value, ['id' => $id, 'value' => 1], $post)) {
            $KO = true;
         } else {
            //not in basket mode
            if (isset($post['_filename'])) {
               foreach ($post['_filename'] as $key => $filename) {
                  $_SESSION['plugin_metademands']['fields']['files'][$post['form_metademands_id']]['_prefix_filename'][] = $post['_prefix_filename'][$key];
                  $_SESSION['plugin_metademands']['fields']['files'][$post['form_metademands_id']]['_tag_filename'][]    = $post['_tag_filename'][$key];
                  $_SESSION['plugin_metademands']['fields']['files'][$post['form_metademands_id']]['_filename'][]        = $post['_filename'][$key];
               }
            }
         }
      } else if ($value['type'] == 'dropdown_multiple') {

         if (!isset($post[$fieldname][$id])) {
            if (!self::checkMandatoryFields($fieldname, $value, ['id'    => $id,
                                                                 'value' => []],
                                            $post)) {
               $KO                                            = true;
               $_SESSION['plugin_metademands']['fields'][$id] = [];

            } else {
               $_SESSION['plugin_metademands']['fields'][$id] = [];
            }
         } else {
            $_SESSION['plugin_metademands']['fields'][$id] = $post[$fieldname][$id];
         }


      }
      //INFO : not used for update basket
      if ($value['item'] != 'ITILCategory_Metademands' && $KO === false && isset($post[$fieldname][$id])) {
         $content[$id]['plugin_metademands_fields_id'] = $id;
         if ($value['type'] != "upload") {
            $content[$id]['value'] = (is_array($post[$fieldname][$id])) ? PluginMetademandsField::_serialize($post[$fieldname][$id]) : $post[$fieldname][$id];
         }
         $content[$id]['value2'] = (isset($post[$fieldname][$id . "-2"])) ? $post[$fieldname][$id . "-2"] : "";
         $content[$id]['item']   = $value['item'];
         $content[$id]['type']   = $value['type'];

         if (isset($post['_filename']) && $value['type'] == "upload") {
            $files = [];
            foreach ($post['_filename'] as $key => $filename) {
               $files[$key]['_prefix_filename'] = $post['_prefix_filename'][$key];
               $files[$key]['_tag_filename']    = $post['_tag_filename'][$key];
               $files[$key]['_filename']        = $post['_filename'][$key];
            }
            $content[$id]['value'] = json_encode($files);
         }
      }

      return ['result' => $KO, 'content' => $content];
   }

   /**
    * @param array $value
    * @param array $fields
    * @param       $fieldname
    * @param array $post
    *
    * @return bool
    */
   static function checkMandatoryFields($fieldname, $value = [], $fields = [], $post = []) {

      //TODO To Translate ?
      $checkKo             = [];
      $checkKoDateInterval = [];
      $checkNbDoc          = [];
      $checkRegex          = [];
      $msg                 = [];
      $msg2                = [];
      $msg3                = [];
      $all_fields          = $post[$fieldname];

      if ($value['type'] != 'parent_field') {
         // Check fields empty
         if ($value['is_mandatory']
             && empty($fields['value'])
             && $value['type'] != 'radio'
             && $value['type'] != 'checkbox'
             && $value['type'] != 'informations'
             && $value['type'] != 'upload') {
            $msg[]     = $value['name'];
            $checkKo[] = 1;
         }

         // Check linked field mandatory
         if (!empty($value['fields_link'])
             && !empty($value['check_value'])
             && PluginMetademandsTicket_Field::isCheckValueOK($fields['value'], $value['check_value'], $value['type'])
             && (empty($all_fields[$value['fields_link']]) || $all_fields[$value['fields_link']] == 'NULL')
         ) {

            $field        = new PluginMetademandsField();
            $fields_links = PluginMetademandsField::_unserialize($value['fields_link']);

            if (is_array($fields_links)) {
               foreach ($fields_links as $k => $fields_link) {
                  if ($fields_link > 0) {
                     $field->getFromDB($fields_link);
                     $msg[]     = $field->fields['name'] . ' ' . $field->fields['label2'];
                     $checkKo[] = 1;
                  }
               }
            }
         }
         //radio
         if ($value['type'] == 'radio'
             && $value['is_mandatory']) {
            if ($fields['value'] == NULL) {
               $msg[]     = $value['name'];
               $checkKo[] = 1;
            }
         }

         //checkbox
         if ($value['type'] == 'checkbox'
             && $value['is_mandatory']) {
            if ($fields['value'] == NULL) {
               $msg[]     = $value['name'];
               $checkKo[] = 1;
            }
         }

         // Check date
         if ($value['type'] == "date"
             || $value['type'] == "datetime"
             || $value['type'] == "date_interval"
             || $value['type'] == "datetime_interval") {
            // date Null
            if ($value['is_mandatory']
                && $fields['value'] == 'NULL') {
               $msg[]     = $value['name'];
               $checkKo[] = 1;
            }
            // date not < today
            if ($fields['value'] != 'NULL'
                && !empty($fields['value'])
                && !empty($value['check_value'])
                && !(strtotime($fields['value']) >= strtotime(date('Y-m-d')))) {
               $msg[]     = sprintf(__("Date %s cannot be less than today's date", 'metademands'), $value['name']);
               $checkKo[] = 1;
            }
         }

         // Check date interval is right
         if (($value['type'] == 'date_interval' || $value['type'] == 'datetime_interval')
             && isset($all_fields[$fields['id'] . '-2'])) {
            if (strtotime($fields['value']) > strtotime($all_fields[$fields['id'] . '-2'])) {
               $msg[]                 = sprintf(__('Date %1$s cannot be greater than date %2$s', 'metademands'), $value['name'], $value['label2']);
               $checkKoDateInterval[] = 1;
            }
         }

         // Check File upload field
         if ($value['type'] == "upload"
             && $value['is_mandatory']) {
            if (isset($post['_filename'])) {
               if (empty($post['_filename'][0])) {
                  $msg[]     = $value['name'];
                  $checkKo[] = 1;
               }
            } else {
               $msg[]     = $value['name'];
               $checkKo[] = 1;
            }
         }
         // Check File upload field
         if ($value['type'] == "upload"
             && !empty($value["max_upload"])
             && isset($post['_filename'])) {
            if ($value["max_upload"] < count($post['_filename'])) {
               $msg2[]       = $value['name'];
               $checkNbDoc[] = 1;
            }
         }

         // Check text with regex
         if ($value['type'] == "text"
             && !empty($value["regex"])) {
            if ((!empty($fields['value']) && $value['is_mandatory'] == 0) || $value['is_mandatory'] == 1) {
               if (!preg_match(($value['regex']), $fields['value'])) {
                  $msg3[]       = $value['name'];
                  $checkRegex[] = 1;
               }
            }
         }

      }
      if (in_array(1, $checkKo)
          || in_array(1, $checkKoDateInterval)) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), false, ERROR);
         return false;
      }
      if (in_array(1, $checkNbDoc)) {
         Session::addMessageAfterRedirect(sprintf(__("Too much documents are upload, max %s. Please correct: %s", "metademands"), $value["max_upload"], implode(', ', $msg2)), false, ERROR);
         return false;
      }
      if (in_array(1, $checkRegex)) {
         Session::addMessageAfterRedirect(sprintf(__("Field do not correspond to the expected format. Please correct: %s", "metademands"), implode(', ', $msg3)), false, ERROR);
         return false;
      }

      return true;
   }

   /**
    * @param       $name
    * @param       $data
    * @param array $options
    */
   function showDropdownFromArray($name, $data, $options = []) {
      $params['on_change'] = '';
      $params['no_empty']  = 0;
      $params['value']     = '';
      $params['tree']      = false;
      foreach ($options as $key => $val) {
         $params[$key] = $val;
      }
      //print_r($params['value']);
      echo "<select id='" . $name . "' name='" . $name . "' onchange='" . $params['on_change'] . "'>";
      if (!$params['no_empty']) {
         echo "<option value='0'>-----</option>";
      }
      foreach ($data as $id => $values) {

         $level = 0;
         $class = "";
         $raquo = "";

         if ($params['tree']) {
            $level = $values['level'];
            $class = " class='tree' ";
            $raquo = "&raquo;";

            if ($level == 1) {
               $class = " class='treeroot'";
               $raquo = "";
            }
         }

         echo "<option value='" . $id . "' $class " . ($params['value'] == $id ? 'selected' : '') . " >" . str_repeat("&nbsp;&nbsp;&nbsp;", $level) . $raquo;

         if ($params['tree']) {
            echo $values['name'];
         } else {
            echo $values;
         }
         echo "</option>";
      }
      echo "</select>";
   }

   /**
    * Used for check if hide child metademands
    *
    * @param $check_value
    * @param $plugin_metademands_tasks_id
    * @param $metademandtasks_tasks_id
    * @param $id
    * @param $value
    */
   function checkValueOk($check_value, $plugin_metademands_tasks_id, $metademandtasks_tasks_id, $id, $value, $post) {

      if (isset($post[$id])
          && $check_value != null
          && in_array($plugin_metademands_tasks_id, $metademandtasks_tasks_id)) {

         if (!PluginMetademandsTicket_Field::isCheckValueOK($post[$id], $check_value, $value['type'])) {
            $metademandToHide                                   = array_keys($metademandtasks_tasks_id, $plugin_metademands_tasks_id);
            $_SESSION['metademands_hide'][$metademandToHide[0]] = $metademandToHide[0];
            unset($_SESSION['son_meta'][$metademandToHide[0]]);
         }
      }
   }

   /**
    * Unset values in data & post for hiddens fields
    * Add metademands_hide in Session for hidden fields
    *
    * @param $data
    * @param $post
    */
   static function unsetHidden(&$data, &$post) {
      foreach ($data as $id => $value) {
         //if field is hidden remove it from Data & Post
         $unserialisedCheck      = PluginMetademandsField::_unserialize($value['check_value']);
         $unserialisedHiddenLink = PluginMetademandsField::_unserialize($value['hidden_link']);
         //$unserialisedHiddenBloc = PluginMetademandsField::_unserialize($value['hidden_block']);
         $unserialisedTaskChild = PluginMetademandsField::_unserialize($value['plugin_metademands_tasks_id']);
         if (is_array($unserialisedCheck) && is_array($unserialisedHiddenLink)) {
            $toKeep = [];
            foreach ($unserialisedHiddenLink as $key => $hiddenFields) {
               if (!isset($toKeep[$hiddenFields])) {
                  $toKeep[$hiddenFields] = false;
               }
               if (isset($post[$id]) && isset($unserialisedCheck[$key])) {
                  $test = PluginMetademandsTicket_Field::isCheckValueOKFieldsLinks($post[$id], $unserialisedCheck[$key], $value['type']);
               } else {
                  $test = false;
               }

               if ($test == true) {
                  $toKeep[$hiddenFields] = true;
                  if ($unserialisedTaskChild[$key] != 0) {
                     $metaTask = new PluginMetademandsMetademandTask();
                     $metaTask->getFromDB($unserialisedTaskChild[$key]);
                     $idChild = $metaTask->getField('plugin_metademands_metademands_id');
                     unset($_SESSION['metademands_hide'][$idChild]);
                  }
               } else {
                  if ($unserialisedTaskChild[$key] != 0) {
                     $metaTask = new PluginMetademandsMetademandTask();
                     $metaTask->getFromDB($unserialisedTaskChild[$key]);
                     $idChild                                = $metaTask->getField('plugin_metademands_metademands_id');
                     $_SESSION['metademands_hide'][$idChild] = $idChild;
                  }
               }
               //               if (!isset($post[$id]) || ((!is_array($post[$id]) && $unserialisedCheck[$key] != $post[$id]) || (is_array($post[$id]) && !in_array($unserialisedCheck[$key], $post[$id])))) {
               //
               //                  if (isset($post[$hiddenFields])) unset($post[$hiddenFields]);
               //                  if (isset($data[$hiddenFields])) unset($data[$hiddenFields]);
               //
               //                  //If the field is hidden and linked to a sub metademand
               //                  //Dont show the sub metademand
               //                  if (is_array($unserialisedTaskChild)) {
               //                     foreach ($unserialisedTaskChild as $child) {
               //                        if ($child != 0) {
               //                           $metaTask = new PluginMetademandsMetademandTask();
               //                           $metaTask->getFromDB($child);
               //                           $idChild                                = $metaTask->getField('plugin_metademands_metademands_id');
               //                           $_SESSION['metademands_hide'][$idChild] = $idChild;
               //                        }
               //                     }
               //                  }
               //               }
            }
            // if(is_array($unserialisedHiddenBloc)){
            // }
            foreach ($toKeep as $k => $v) {
               if ($v == false) {
                  if (isset($post[$k])) unset($post[$k]);
                  if (isset($data[$k])) unset($data[$k]);
               }
            }
         }
      }
   }

}
