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
 * Class PluginResourcesChecklist
 */
class PluginResourcesChecklist extends CommonDBTM {

   static $rightname = 'plugin_resources_checklist';

   const RESOURCES_CHECKLIST_IN       = 1;
   const RESOURCES_CHECKLIST_OUT      = 2;
   const RESOURCES_CHECKLIST_TRANSFER = 3;

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {

      return _n('Checklist', 'Checklists', $nb, 'resources');
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
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]);
   }

   /**
    * Clean object veryfing criteria (when a relation is deleted)
    *
    * @param $crit array of criteria (should be an index)
    */
   public function clean($crit) {
      global $DB;

      foreach ($DB->request($this->getTable(), $crit) as $data) {
         $this->delete($data);
      }
   }

   /**
    * Get Tab Name used for itemtype
    *
    * NB : Only called for existing object
    *      Must check right on what will be displayed + template
    *
    * @param CommonGLPI $item Item on which the tab need to be displayed
    * @param boolean    $withtemplate is a template object ? (default 0)
    *
    * @return string tab name
    **@since 0.83
    *
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getID() && $this->canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
            }
            return self::getTypeName(2);
         }
      }
      return '';
   }

   /**
    * show Tab content
    *
    * @param CommonGLPI $item Item on which the tab need to be displayed
    * @param integer    $tabnum tab number (default 1)
    * @param boolean    $withtemplate is a template object ? (default 0)
    *
    * @return boolean
    **@since 0.83
    *
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      $ID = $item->getField('id');
      if (self::checkifChecklistExist($ID, 0)) {
         $checklist = new self();
         if ($checklist->canCreate()) {
            self::showFromResources($ID, self::RESOURCES_CHECKLIST_IN, $withtemplate);
         }
         self::showFromResources($ID, self::RESOURCES_CHECKLIST_OUT, $withtemplate);
         if ($checklist->canCreate()) {
            self::showFromResources($ID, self::RESOURCES_CHECKLIST_TRANSFER, $withtemplate);
         }
      } else {
         self::showAddForm($ID);
      }
      return true;
   }

   /**
    * @param $item
    *
    * @return int
    */
   static function countForItem($item) {

      if ($item->getField('is_leaving') == 1) {
         $checklist_type = self::RESOURCES_CHECKLIST_OUT;
      } else {
         $checklist_type = self::RESOURCES_CHECKLIST_IN;
      }
      $dbu      = new DbUtils();
      $restrict = ["plugin_resources_resources_id" => $item->getField('id'),
                   "checklist_type"                => $checklist_type,
                   "NOT"                           => ["is_checked" => 1]];
      $nb       = $dbu->countElementsInTable(['glpi_plugin_resources_checklists'], $restrict);

      return $nb;
   }

   /**
    * @param $ID
    *
    * @param $type_checklist
    *
    * @return bool
    */
   static function checkifChecklistExist($ID, $type_checklist) {

      $restrict = ["plugin_resources_resources_id" => $ID];
      if ($type_checklist > 0) {
         $restrict[] = ["checklist_type" => $type_checklist];
      }
      $dbu        = new DbUtils();
      $checklists = $dbu->getAllDataFromTable("glpi_plugin_resources_checklists", $restrict);

      if (!empty($checklists)) {
         foreach ($checklists as $checklist) {
            return $checklist["id"];
         }
      } else {
         return false;
      }
   }

   /**
    * @param $input
    *
    * @return bool
    */
   static function checkifChecklistFinished($input) {

      $restrict   = ["plugin_resources_resources_id" => $input['plugin_resources_resources_id'],
                     "checklist_type"                => $input['checklist_type']];
      $dbu        = new DbUtils();
      $checklists = $dbu->getAllDataFromTable("glpi_plugin_resources_checklists", $restrict);

      $nok = 0;
      if (!empty($checklists)) {
         foreach ($checklists as $checklist) {
            if ($checklist["is_checked"] < 1) {
               $nok += 1;
            }
         }
         if ($nok > 0) {
            return false;
         } else {
            return true;
         }
      } else {
         return false;
      }
   }

   /**
    * @param $input
    *
    * @return bool
    */
   function openFinishedChecklist($input) {

      $restrict   = ["plugin_resources_resources_id" => $input['plugin_resources_resources_id'],
                     "checklist_type"                => $input['checklist_type']];
      $dbu        = new DbUtils();
      $checklists = $dbu->getAllDataFromTable("glpi_plugin_resources_checklists", $restrict);

      if (!empty($checklists)) {
         foreach ($checklists as $checklist) {
            $this->update(["id"         => $checklist["id"],
                           "is_checked" => 0]);
         }
      } else {
         return false;
      }
   }

   /**
    * @param $data
    *
    * @return bool
    */
   static function createTicket($data) {

      $result = false;
      $tt     = new TicketTemplate();

      // Create ticket based on ticket template and entity informations of ticketrecurrent
      if ($tt->getFromDB($data['tickettemplates_id'])) {
         // Get default values for ticket
         $input = Ticket::getDefaultValues($data['entities_id']);
         // Apply tickettemplates predefined values
         $ttp        = new TicketTemplatePredefinedField();
         $predefined = $ttp->getPredefinedFields($data['tickettemplates_id'], true);

         if (count($predefined)) {
            foreach ($predefined as $predeffield => $predefvalue) {
               $input[$predeffield] = $predefvalue;
            }
         }
         // Set date to creation date
         $createtime    = date('Y-m-d H:i:s');
         $input['date'] = $createtime;
         // Compute time_to_resolve if predefined based on create date
         if (isset($predefined['time_to_resolve'])) {
            $input['time_to_resolve'] = Html::computeGenericDateTimeSearch($predefined['time_to_resolve'], false,
                                                                           strtotime($createtime));
         }
         // Set entity
         $input['entities_id'] = $data['entities_id'];
         $input['actiontime']  = $data['actiontime'];
         $res                  = new PluginResourcesResource();

         $default_use_notif = Entity::getUsedConfig('is_notif_enable_default', $input['entities_id'], '', 1);

         if ($res->getFromDB($data['plugin_resources_resources_id'])) {

            $input['users_id_recipient']                            = $res->fields['users_id_recipient'];
            $input['_users_id_requester']                           = [$res->fields['users_id_recipient']];
            $input['_users_id_requester_notif']['use_notification'] = [$default_use_notif];
            $alternativeEmail                                       = '';
            if (filter_var(Session::getLoginUserID(), FILTER_VALIDATE_EMAIL) !== false) {
               $alternativeEmail = Session::getLoginUserID();
            }
            $input['_users_id_requester_notif']['alternative_email'] = [$alternativeEmail];

            if (isset($res->fields['users_id'])) {
               $input['_users_id_observer']       = $res->fields['users_id'];
               $input['_users_id_observer_notif'] = [];
            }

            if (isset($data['users_id'])) {
               $input['_users_id_assign'] = $data['users_id'];
            } else {
               $input['_users_id_assign'] = Session::getLoginUserID();
            }

            $input["items_id"] = ['PluginResourcesResource' => [$data['plugin_resources_resources_id']]];
            $input["name"]     .= addslashes(" " . PluginResourcesResource::getResourceName($data['plugin_resources_resources_id']));
         }

         //TODO : ADD checklist lists or add config into plugin ?
         $input["content"] .= addslashes("\n\n");
         $input['status']  = Ticket::CLOSED;
         $input['id']      = 0;
         $ticket           = new Ticket();
         $input            = Toolbox::addslashes_deep($input);

         if ($tid = $ticket->add($input)) {
            $msg    = __('Create a end treatment ticket', 'resources') . " OK - ($tid)"; // Success
            $result = true;
         } else {
            $msg = __('Failed operation'); // Failure
         }
      } else {
         $msg = __('No selected element or badly defined operation'); // Not defined
      }
      if ($tid) {
         $changes[0] = 0;
         $changes[1] = '';
         $changes[2] = addslashes($msg);
         Log::history($data['plugin_resources_resources_id'], "PluginResourcesResource", $changes, '', Log::HISTORY_LOG_SIMPLE_MESSAGE);
      }
      return $result;
   }

   /**
    * @param     $name
    * @param int $value
    *
    * @return bool|int|string
    */
   function dropdownChecklistType($name, $value = 0) {

      $checklists = [self::RESOURCES_CHECKLIST_IN       => __('At the arriving of a resource', 'resources'),
                     self::RESOURCES_CHECKLIST_OUT      => __('At the leaving of a resource', 'resources'),
                     self::RESOURCES_CHECKLIST_TRANSFER => __('At the transfer of a resource', 'resources')];

      if (!empty($checklists)) {
         return Dropdown::showFromArray($name, $checklists, ['value' => $value]);
      } else {
         return false;
      }
   }

   /**
    * @param $value
    *
    * @return string
    */
   static function getChecklistType($value) {

      switch ($value) {
         case self::RESOURCES_CHECKLIST_IN :
            return __('At the arriving of a resource', 'resources');
         case self::RESOURCES_CHECKLIST_OUT :
            return __('At the leaving of a resource', 'resources');
         case self::RESOURCES_CHECKLIST_TRANSFER :
            return __('At the transfer of a resource', 'resources');
         default :
            return "";
      }
   }

   /**
    * Prepare input datas for adding the item
    *
    * @param array $input datas used to add the item
    *
    * @return array the modified $input array
    **/
   function prepareInputForAdd($input) {
      global $DB;

      $query         = "SELECT MAX(`rank`) 
               FROM `" . $this->getTable() . "` 
               WHERE `checklist_type` = '" . $input['checklist_type'] . "' 
               AND `plugin_resources_contracttypes_id` = '" . $input['plugin_resources_contracttypes_id'] . "' 
               AND `plugin_resources_resources_id` = '" . $input['plugin_resources_resources_id'] . "' 
               AND `entities_id` = '" . $input['entities_id'] . "' ";
      $result        = $DB->query($query);
      $input["rank"] = $DB->result($result, 0, 0) + 1;

      return $input;
   }

   /**
    * @param $ID
    */
   static function showAddForm($ID) {

      echo "<div align='center'>";
      echo "<form action='" . Toolbox::getItemTypeFormURL('PluginResourcesResource') . "' method='post'>";
      echo "<table class='tab_cadre' width='50%'>";
      echo "<tr>";
      echo "<th colspan='2'>";
      echo __('Create checklists', 'resources');
      echo "</th></tr>";
      echo "<tr class='tab_bg_2 center'>";
      echo "<td colspan='2'>";
      echo "<input type='submit' name='add_checklist_resources' value='" . _sx('button', 'Post') . "' class='submit' />";
      echo "<input type='hidden' name='id' value='" . $ID . "'>";
      echo "</td></tr></table>";
      Html::closeForm();
      echo "</div>";
   }

   /**
    * Modify checklist's ranking and automatically reorder all checklists
    *
    * @param $ID the checklist ID whose ranking must be modified
    * @param $checklist_type IN or OUT
    * @param $plugin_resources_resources_id the resources ID
    * @param $action up or down
    * */
   function changeRank($input) {
      global $DB;

      $sql = "SELECT `rank`
              FROM `" . $this->getTable() . "`
              WHERE `id` ='" . $input['id'] . "'";

      if ($result = $DB->query($sql)) {
         if ($DB->numrows($result) == 1) {
            $current_rank = $DB->result($result, 0, 0);
            // Search rules to switch
            $sql2 = "SELECT `ID`,`rank` 
                     FROM `" . $this->getTable() . "` 
                     WHERE `checklist_type` = '" . $input['checklist_type'] . "' 
                     AND `plugin_resources_resources_id` = '" . $input['plugin_resources_resources_id'] . "' ";

            switch ($input['action']) {
               case "up" :
                  $sql2 .= " AND `rank` < '$current_rank'
                           ORDER BY `rank` DESC
                           LIMIT 1";
                  break;

               case "down" :
                  $sql2 .= " AND `rank` > '$current_rank'
                           ORDER BY `rank` ASC
                           LIMIT 1";
                  break;

               default :
                  return false;
            }

            if ($result2 = $DB->query($sql2)) {
               if ($DB->numrows($result2) == 1) {
                  list($other_ID, $new_rank) = $DB->fetchArray($result2);

                  return ($this->update(['id'   => $input['id'],
                                         'rank' => $new_rank]) && $this->update(['id'   => $other_ID,
                                                                                 'rank' => $current_rank]));
               }
            }
         }
         return false;
      }
   }

   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {

      if (!$this->canView()) {
         return false;
      }

      $plugin_resources_contracttypes_id = -1;
      if (isset($options['plugin_resources_contracttypes_id'])) {
         $plugin_resources_contracttypes_id = $options['plugin_resources_contracttypes_id'];
      }

      $checklist_type = -1;
      if (isset($options['checklist_type'])) {
         $checklist_type = $options['checklist_type'];
      }

      $plugin_resources_resources_id = -1;

      if (isset($options['plugin_resources_resources_id'])) {
         $plugin_resources_resources_id = $options['plugin_resources_resources_id'];
         $item                          = new PluginResourcesResource();
         if ($item->getFromDB($plugin_resources_resources_id)) {
            $options["entities_id"] = $item->fields["entities_id"];
         }
      }

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $this->check(-1, UPDATE, $input);
      }

      $this->showFormHeader($options);

      echo Html::hidden('plugin_resources_resources_id', ['value' => $plugin_resources_resources_id]);
      if ($ID > 0) {
         echo "<input type='hidden' name='plugin_resources_contracttypes_id' value='" . $this->fields["plugin_resources_contracttypes_id"] . "'>";
         echo "<input type='hidden' name='checklist_type' value='" . $this->fields["checklist_type"] . "'>";
      } else {
         echo "<input type='hidden' name='plugin_resources_contracttypes_id' value='$plugin_resources_contracttypes_id'>";
         echo "<input type='hidden' name='checklist_type' value='$checklist_type'>";
      }

      echo "<tr class='tab_bg_1'>";

      echo "<td >" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name", ['size' => "40"]);
      echo "</td>";

      echo "<td>";
      echo __('Important', 'resources');
      echo "</td><td>";
      Dropdown::showYesNo("tag", $this->fields["tag"]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td >" . __('Link', 'resources') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "address", ['size' => "75"]);
      echo "</td>";

      echo "<td></td>";
      echo "<td></td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td class='left' colspan = '4'>";
      echo __('Description') . "<br>";
      echo "<textarea cols='150' rows='6' name='comment'>" . $this->fields["comment"] . "</textarea>";
      echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);
      return true;
   }

   /**
    * show from resources
    *
    * @param        $plugin_resources_resources_id
    * @param        $checklist_type
    * @param string $withtemplate
    *
    * @return bool
    */
   static function showFromResources($plugin_resources_resources_id, $checklist_type, $withtemplate = '') {
      global $CFG_GLPI;

      if (!self::canView()) {
         return false;
      }

      $target          = "./resource.form.php";
      $targetchecklist = "./checklist.form.php";
      $targettask      = "./task.form.php";
      $resource        = new PluginResourcesResource();
      $resource->getFromDB($plugin_resources_resources_id);
      $canedit                           = $resource->can($plugin_resources_resources_id, UPDATE);
      $entities_id                       = $resource->fields["entities_id"];
      $plugin_resources_contracttypes_id = $resource->fields["plugin_resources_contracttypes_id"];
      $rand                              = mt_rand();
      $title                             = null;

      // Check type values
      switch ($checklist_type) {
         case self::RESOURCES_CHECKLIST_IN:
            $viewId          = 'checklist_view_in_mode';
            $viewId_finished = 'checklist_finished_view_in_mode';
            $addLinkName     = __('Add a task at the arriving checklist', 'resources');
            break;
         case self::RESOURCES_CHECKLIST_OUT:
            $viewId          = 'checklist_view_out_mode';
            $viewId_finished = 'checklist_finished_view_out_mode';
            $addLinkName     = __('Add a task at the leaving checklist', 'resources');
            break;
         case self::RESOURCES_CHECKLIST_TRANSFER:
            $viewId          = 'checklist_view_transfer_mode';
            $viewId_finished = 'checklist_finished_view_transfer_mode';
            $addLinkName     = __('Add a task at the transfer checklist', 'resources');
            break;
      }

      // Is check list finished ?
      $values                                      = [];
      $values["checklist_type"]                    = $checklist_type;
      $values["plugin_resources_resources_id"]     = $plugin_resources_resources_id;
      $values["plugin_resources_contracttypes_id"] = $plugin_resources_contracttypes_id;
      $values["entities_id"]                       = $entities_id;
      $isfinished                                  = self::checkifChecklistFinished($values);

      if ($isfinished) {
         $title = "<i style='color:green' class='fas fa-check-circle fa-2x' ></i>";
      }
      $title .= self::getChecklistType($checklist_type);
      if ($isfinished) {
         $title .= " - " . __('Check list done', 'resources');
      }

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th width='15px'>";
      // Show / hide checklist
      echo "<span id='menu_navigate'>";
      echo "<a href=\"javascript:showHideDiv('$viewId',
                        'checklistimg$rand','fa-angle-double-down fa-2x',
                        'fa-angle-double-up fa-2x')\">";
      echo "<i id='checklistimg$rand' style='color:orange' class='fas fa-angle-double-up fa-2x' ></i>";
      echo "</a>";
      echo "</span>";
      echo "</th>";
      echo "<th height='30px'>";
      echo $title;
      echo "</th>";
      echo "</tr>";

      echo "<tr>";
      echo "<td class='center' colspan='2'>";
      echo "<div align='center' id='$viewId'>";

      // New check form
      if (self::canCreate() && $canedit) {
         echo "<div id='viewchecklisttask" . "$rand'></div>\n";
         echo "<div style='margin:10px'>";
         echo "<script type='text/javascript' >\n";
         echo "function viewAddChecklistTask" . "$rand(){\n";
         $params = ['type'                              => __CLASS__,
                    'target'                            => $targetchecklist,
                    'plugin_resources_contracttypes_id' => $plugin_resources_contracttypes_id,
                    'plugin_resources_resources_id'     => $plugin_resources_resources_id,
                    'checklist_type'                    => $checklist_type,
                    'id'                                => -1];
         Ajax::updateItemJsCode("viewchecklisttask" . "$rand", $CFG_GLPI["root_doc"] . "/plugins/resources/ajax/viewchecklisttask.php", $params, false);
         echo "};";
         echo "</script>\n";
         echo "<a class='vsubmit' href='javascript:viewAddChecklistTask" . "$rand();'>$addLinkName</a>";
         echo "</div>";
      }

      // Get check list
      $restrict   = ["entities_id"                   => $entities_id,
                     "plugin_resources_resources_id" => $plugin_resources_resources_id,
                     "checklist_type"                => $checklist_type] +
                    ["ORDER" => "rank"];
      $dbu        = new DbUtils();
      $checklists = $dbu->getAllDataFromTable("glpi_plugin_resources_checklists", $restrict);
      $numrows    = $dbu->countElementsInTable("glpi_plugin_resources_checklists", $restrict);
      if (!empty($checklists)) {
         if (!$isfinished && self::canCreate() && $canedit && Session::getCurrentInterface() == "central") {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
         }

         if ($isfinished) {
            echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL('PluginResourcesResource') . "'>";
         }

         echo "<input type='hidden' name='plugin_resources_resources_id' value='$plugin_resources_resources_id' data-glpicore-ma-tags='common'>";
         echo "<input type='hidden' name='checklist_type' value='$checklist_type' data-glpicore-ma-tags='common'>";
         echo "<input type='hidden' name='plugin_resources_contracttypes_id' value='$plugin_resources_contracttypes_id' data-glpicore-ma-tags='common'>";
         echo "<input type='hidden' name='entities_id' value='$entities_id' data-glpicore-ma-tags='common'>";

         // Actions on finished checklist
         if ($isfinished && self::canCreate() && $canedit) {
            echo "<table class='tab_cadre' width='100%'>";
            echo "<tr>";
            echo "<th colspan = '4'>" . __('Create a end treatment ticket', 'resources') . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Templates') . "</td>";
            echo "<td>";
            Dropdown::show('TicketTemplate', ['name'        => 'tickettemplates_id',
                                              'entities_id' => $entities_id]);
            echo "</td>";
            echo "<td>" . __('Assigned to') . "</td>";
            echo "<td>";
            User::dropdown(['name' => "users_id", 'right' => 'interface']);
            echo "</td>";

            echo "</tr>";
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Total duration') . "</td>";
            echo "<td>";
            Dropdown::showTimeStamp('actiontime', ['addfirstminutes' => true]);
            echo "</td>";
            echo "<td colspan='2'></td>";
            echo "</tr>";
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='4' class='center'><input type='submit' class='submit' value='" . _sx('button', 'Add') . "' name='close_checklist'></td>";
            echo "</tr>";

            echo "<tr class='tab_bg_2'>";
            echo "<th colspan = '2'>" . __('Reset the checklist', 'resources') . "</th>";
            echo "<td colspan='2' class='center'><input type='submit' class='submit' value='" . _sx('button', 'Post') . "' name='open_checklist'></td>";
            echo "</tr>";

            echo "</table>";
         }

         $style = '';

         // Display list
         if ($isfinished) {
            echo "<br>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr>";
            echo "<th width='15px'>";
            echo "<span id='menu_navigate'>";
            echo "<a href=\"javascript:showHideDiv('$viewId_finished',
                        'checklistfinished$rand','fa-eye fa-2x',
                        'fa-eye-slash fa-2x')\">";
            echo "<i id='checklistfinished$rand' style='color:black' class='fas fa-eye fa-2x'></i>";
            echo "</a>";
            echo "</span>";
            echo "</th>";
            echo "<th height='30px' colspan='4'>";
            echo PluginResourcesChecklist::getTypeName(0);
            echo "</th>";
            echo "</tr>";
            echo "</table>";

            $style = 'style="display: none;"';

         }
         echo "<div align='center' id='$viewId_finished' $style>";
         echo "<table class='tab_cadre' width='100%'>";
         echo "<tr>";
         if (!$isfinished) {
            echo "<th width='10'>";
            if (self::canCreate() && $canedit && Session::getCurrentInterface() == "central") {
               echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            }
            echo "</th>";
         }
         echo "<th>" . __('Name') . "</th>";
         echo "<th>" . __('Important', 'resources') . "</th>";
         if (Session::haveRight("plugin_resources_task", UPDATE) && $canedit) {
            echo "<th>" . __('Linked task', 'resources') . "</th>";
         }
         echo "<th>" . _x('location', 'State') . "</th>";
         echo "<th>&nbsp;</th>";
         echo "<th>&nbsp;</th>";
         echo "</tr>";

         Session::initNavigateListItems("PluginResourcesChecklist", PluginResourcesResource::getTypeName(1) . " = " . $resource->fields['name']);

         $i = 0;
         foreach ($checklists as $checklist) {
            $ID = $checklist["id"];

            Session::addToNavigateListItems("PluginResourcesChecklist", $ID);

            echo "<tr class='tab_bg_1'>";
            if (!$isfinished) {
               echo "<td width='10'>";
               if (self::canCreate() && $canedit && Session::getCurrentInterface() == "central") {
                  Html::showMassiveActionCheckBox(__CLASS__, $ID);
               }
               echo "</td>";
            }

            echo "<td width='30%'>";
            echo "<a href='" . $targetchecklist . "?id=" . $ID . "&amp;plugin_resources_resources_id=" .
                 $plugin_resources_resources_id . "&amp;plugin_resources_contracttypes_id=" .
                 $plugin_resources_contracttypes_id . "&amp;checklist_type=" . $checklist_type . "' >";
            echo $checklist["name"];
            echo "</a>&nbsp;";

            echo "<input type='hidden' value='" . $checklist["comment"] . "' name='comment'>";

            if (!empty($checklist["address"])) {
               echo "&nbsp;";
               $link = str_replace("&", "&amp;", $checklist["address"]);
               Html::showToolTip($checklist["address"], ['link' => $link, 'linktarget' => '_blank']);
            }
            echo "</td>";

            echo "<td>";
            if ($checklist["tag"]) {
               echo "<span class='plugin_resources_date_over_color'>";
            }
            echo nl2br($checklist["comment"]);
            if ($checklist["tag"]) {
               echo "</span>";
            }
            echo "</td>";

            if (Session::haveRight("plugin_resources_task", UPDATE) && $canedit) {
               echo "<td class='center'>";
               if (!empty($checklist["plugin_resources_tasks_id"])) {
                  echo "<a href='" . $targettask . "?id=" . $checklist["plugin_resources_tasks_id"] . "&amp;plugin_resources_resources_id=" . $plugin_resources_resources_id . "&amp;central=1'>";
               }
               echo Dropdown::getYesNo($checklist["plugin_resources_tasks_id"]);
               if (!empty($checklist["plugin_resources_tasks_id"])) {
                  echo "</a>";
               }
               echo "</td>";
            }

            echo "<td class='center'>";
            echo "<input type='checkbox' disabled='true' name='is_checked' ";
            if ($checklist["is_checked"]) {
               echo "checked";
            }
            echo " >";
            echo "<input type='hidden' value='" . (($checklist["is_checked"] > 0) ? 0 : 1) . "' name='is_checked$ID' data-glpicore-ma-tags='common'>";
            echo "</td>";

            if ($i != 0 && self::canCreate() && $canedit && !$isfinished) {
               echo "<td>";
               Html::showSimpleForm($target, 'move', __('Bring up'), ['action'                        => 'up',
                                                                      'id'                            => $ID,
                                                                      'plugin_resources_resources_id' => $plugin_resources_resources_id,
                                                                      'checklist_type'                => $checklist_type], $CFG_GLPI["root_doc"] . "/pics/deplier_up.png");
               echo "</td>";
            } else {
               echo "<td>&nbsp;</td>";
            }

            if ($i != $numrows - 1 && self::canCreate() && $canedit && !$isfinished) {
               echo "<td>";
               Html::showSimpleForm($target, 'move', __('Bring down'), ['action'                        => 'down',
                                                                        'id'                            => $ID,
                                                                        'plugin_resources_resources_id' => $plugin_resources_resources_id,
                                                                        'checklist_type'                => $checklist_type], $CFG_GLPI["root_doc"] . "/pics/deplier_down.png");
               echo "</td>";
            } else {
               echo "<td>&nbsp;</td>";
            }
            echo "</tr>";

            $i++;
         }
         echo "</table>";
         echo "</div>";
         if (!$isfinished && self::canCreate() && $canedit && Session::getCurrentInterface() == "central") {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
         }
         Html::closeForm();
      }
      echo "</div>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "<br>";
   }

   /**
    * Get the specific massive actions
    *
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    * *@since version 0.84
    *
    */
   function getSpecificMassiveActions($checkitem = null) {

      $actions = parent::getSpecificMassiveActions($checkitem);

      if (Session::haveRight("plugin_resources_checklist", UPDATE)) {
         $actions['PluginResourcesChecklist' . MassiveAction::CLASS_ACTION_SEPARATOR . 'update_checklist'] = __('Modify state', 'resources');
      }

      if (Session::haveRight("plugin_resources_task", UPDATE)) {
         $actions['PluginResourcesChecklist' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_task'] = __('Link a task', 'resources');
      }

      if (Session::haveRight("ticket", Ticket::READALL)) {
         $actions['PluginResourcesChecklist' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_ticket'] = __('Add ticket', 'resources');
      }

      return $actions;
   }

   /**
    * @param \MassiveAction $ma
    *
    * @return bool
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      $input = $ma->getInput();
      foreach ($input as $key => $val) {
         if (!is_array($val)) {
            echo "<input type='hidden' name='$key' value='$val'>";
         }
      }

      switch ($ma->getAction()) {
         case "add_task":
            echo "&nbsp;" . __('Assigned to') . "&nbsp;";
            User::dropdown(['name' => "users_id", 'right' => 'interface']);
            break;
      }

      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    * */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      global $CFG_GLPI;

      $input      = $ma->getInput();
      $isfinished = self::checkifChecklistFinished($input);

      switch ($ma->getAction()) {
         case "update_checklist" :
            if (!$isfinished) {
               foreach ($ids as $key => $val) {
                  if ($item->can($key, UPDATE, $input)) {
                     $varchecked = "is_checked" . $key;
                     if ($item->update(["id"         => $key,
                                        "is_checked" => $input[$varchecked]])) {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                     } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                     }
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                  }
               }
            } else {
               $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
               Session::addMessageAfterRedirect(__('The checklist is finished', 'resources'), true, ERROR);
            }
            break;

         case "add_ticket" :
            if (!$isfinished) {
               unset($input["id"]);
               if (Session::haveRight("ticket", Ticket::READALL)) {
                  $cat    = new PluginResourcesTicketCategory();
                  $rules  = new RuleTicketCollection();
                  $ticket = new Ticket();
                  foreach ($ids as $key => $val) {
                     $item->getFromDB($key);

                     $input2["content"]           = $item->fields["comment"];
                     $input2["name"]              = $item->fields["name"];
                     $input2["itemtype"]          = "PluginResourcesResource";
                     $input2["items_id"]          = ["PluginResourcesResource" => [$item->fields["plugin_resources_resources_id"]]];
                     $input2["requesttypes_id"]   = "6";
                     $input2["urgency"]           = "3";
                     $input2["_users_id_assign"]  = 0;
                     $input2['_groups_id_assign'] = 0;
                     $input2["entities_id"]       = $item->fields["entities_id"];

                     if ($cat->getFromDB(1)) {
                        $input2["itilcategories_id"] = $cat->fields["ticketcategories_id"];
                     } else {
                        $input2["itilcategories_id"] = 0;
                     }

                     $input2 = $rules->processAllRules($input2, $input2);

                     if ($ticket->add($input2)) {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                     } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                     }
                  }
               } else {
                  $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
               }
            } else {
               $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
               Session::addMessageAfterRedirect(__('The checklist is finished', 'resources'), true, ERROR);
            }
            break;

         case "add_task" :
            if (!$isfinished) {
               unset($input["id"]);
               $task = new PluginResourcesTask();
               if ($task->canCreate()) {
                  $tasks_id = [];
                  foreach ($ids as $key => $val) {
                     $item->getFromDB($key);
                     if (empty($item->fields["plugin_resources_tasks_id"])) {
                        $input2                = $input;
                        $input2["name"]        = addslashes($item->fields["name"]);
                        $input2["comment"]     = addslashes($item->fields["comment"]);
                        $input2["entities_id"] = $item->fields["entities_id"];
                        $newID                 = $task->add($input2);
                        $tasks_id[$newID]      = $newID;
                        if ($item->update(["id"                        => $key,
                                           "plugin_resources_tasks_id" => $newID])) {
                           $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                        } else {
                           $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                        }
                     }
                  }
                  //send notifications
                  $PluginResourcesResource = new PluginResourcesResource();
                  if ($CFG_GLPI["notifications_mailing"]) {
                     if ($PluginResourcesResource->getFromDB($item->fields["plugin_resources_resources_id"])) {
                        NotificationEvent::raiseEvent("newtask", $PluginResourcesResource, ['tasks_id' => $tasks_id]);
                     }
                  }
               } else {
                  $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
               }
            } else {
               $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
               Session::addMessageAfterRedirect(__('The checklist is finished', 'resources'), true, ERROR);
            }
            break;

         default :
            return parent::doSpecificMassiveActions($input);
      }
   }

   /**
    * @return array
    */
   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();

      $forbidden[] = 'update';
      $forbidden[] = 'restore';

      return $forbidden;
   }

   /**
    * @param $is_leaving
    */
   function showOnCentral($is_leaving) {
      global $DB, $CFG_GLPI;

      if ($this->canView()) {

         if (Session::isMultiEntitiesMode()) {
            $colsup = 1;
         } else {
            $colsup = 0;
         }

         if ($is_leaving) {
            $query = self::queryChecklists(true, 1);
         } else {
            $query = self::queryChecklists(true);
         }
         $result = $DB->query($query);
         $number = $DB->numrows($result);

         if ($number > 0) {
            echo "<div align='center'><table class='tab_cadre' width='100%'>";
            if ($is_leaving) {
               $title = __('Leaving resource - checklist needs to verificated', 'resources');
            } else {
               $title = __('New resource - checklist needs to verificated', 'resources');
            }
            echo "<tr><th colspan='" . (5 + $colsup) . "'>" . $title . " </th></tr>";
            echo "<tr><th>" . PluginResourcesResource::getTypeName(1) . "</th>";
            if ($is_leaving) {
               echo "<th>" . __('Departure date', 'resources') . "</th>";
            } else {
               echo "<th>" . __('Arrival date', 'resources') . "</th>";
            }
            if (Session::isMultiEntitiesMode()) {
               echo "<th>" . __('Entity') . "</th>";
            }
            echo "<th>" . __('Location') . "</th>";
            echo "<th>" . PluginResourcesContractType::getTypeName(1) . "</th>";
            echo "<th>" . __('Checklist needs to verificated', 'resources') . "</th></tr>";

            while ($data = $DB->fetchArray($result)) {
               echo "<tr class='tab_bg_1'>";

               echo "<td class='center'>";
               echo "<a href='" . $CFG_GLPI["root_doc"] . "/plugins/resources/front/resource.form.php?id=" . $data["plugin_resources_resources_id"] . "'>";
               echo $data["resource_name"] . " " . $data["resource_firstname"];
               if ($_SESSION["glpiis_ids_visible"]) {
                  echo " (" . $data["plugin_resources_resources_id"] . ")";
               }
               echo "</a></td>";

               echo "<td class='center'>";
               if ($is_leaving) {
                  if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"])) {
                     echo "<div class='deleted'>" . Html::convDate($data["date_end"]) . "</div>";
                  } else {
                     echo "<div class='plugin_resources_date_day_color'>";
                     echo Html::convDate($data["date_end"]);
                     echo "</div>";
                  }
               } else {
                  if ($data["date_begin"] <= date('Y-m-d') && !empty($data["date_begin"])) {
                     echo "<div class='deleted'>" . Html::convDate($data["date_begin"]) . "</div>";
                  } else {
                     echo "<div class='plugin_resources_date_day_color'>";
                     echo Html::convDate($data["date_begin"]);
                     echo "</div>";
                  }
               }
               echo "</td>";

               if (Session::isMultiEntitiesMode()) {
                  echo "<td class='center'>";
                  echo Dropdown::getDropdownName("glpi_entities", $data['entities_id']);
                  echo "</td>";
               }
               echo "<td class='center'>";
               echo Dropdown::getDropdownName("glpi_locations", $data['locations_id']);
               echo "</td>";

               echo "<td class='center'>";
               echo Dropdown::getDropdownName("glpi_plugin_resources_contracttypes", $data['plugin_resources_contracttypes_id']);
               echo "</td>";

               echo "<td width='40%'>";
               if ($is_leaving) {
                  $query_checklists = self::queryListChecklists($data["plugin_resources_resources_id"], self::RESOURCES_CHECKLIST_OUT);
               } else {
                  $query_checklists = self::queryListChecklists($data["plugin_resources_resources_id"], self::RESOURCES_CHECKLIST_IN);
               }
               $result_checklists = $DB->query($query_checklists);

               echo "<table class='tab_cadre' width='100%'>";
               while ($data_checklists = $DB->fetchArray($result_checklists)) {
                  echo "<tr class='tab_bg_1'><td>";
                  if ($data_checklists["tag"]) {
                     echo "<span class='plugin_resources_date_over_color'>";
                  }
                  echo $data_checklists["name"];
                  if ($_SESSION["glpiis_ids_visible"]) {
                     echo " (" . $data_checklists["id"] . ")";
                  }
                  if ($data_checklists["tag"]) {
                     echo "</span>";
                  }
                  echo "</td>";
                  echo "</tr>";
               }
               echo "</table>";
               echo "</td></tr>";
            }
            echo "</table></div>";
         }
      }
   }

   // Cron action

   /**
    * @param $name
    *
    * @return array
    */
   static function cronInfo($name) {

      switch ($name) {
         case 'ResourcesChecklist':
            return [
               'description' => __('Checklists Verification', 'resources')];   // Optional
            break;
      }
      return [];
   }

   /**
    * @param     $entity_restrict
    * @param int $is_leaving
    *
    * @return string
    */
   static function queryChecklists($entity_restrict, $is_leaving = 0) {

      $resource = new PluginResourcesResource();

      if ($is_leaving > 0) {
         $field          = "date_end";
         $checklist_type = self::RESOURCES_CHECKLIST_OUT;
      } else {
         $field          = "date_begin";
         $checklist_type = self::RESOURCES_CHECKLIST_IN;
      }
      $query = "SELECT `glpi_plugin_resources_checklists`.*,
                     `glpi_plugin_resources_resources`.`id` AS plugin_resources_resources_id,
                      `glpi_plugin_resources_resources`.`name` AS resource_name,
                       `glpi_plugin_resources_resources`.`firstname` AS resource_firstname,
                        `glpi_plugin_resources_resources`.`entities_id`,
                         `glpi_plugin_resources_resources`.`date_begin`,
                        `glpi_plugin_resources_resources`.`locations_id`,
                        `glpi_plugin_resources_resources`.`plugin_resources_departments_id`,
                        `glpi_plugin_resources_resources`.`plugin_resources_resourcestates_id`,
                        `glpi_plugin_resources_resources`.`users_id`,
                        `glpi_plugin_resources_resources`.`users_id_sales`,
                        `glpi_plugin_resources_resources`.`users_id_recipient`,
                        `glpi_plugin_resources_resources`.`date_declaration`,
                        `glpi_plugin_resources_resources`.`date_begin`,
                        `glpi_plugin_resources_resources`.`date_end`,
                        `glpi_plugin_resources_resources`.`users_id_recipient_leaving`,
                        `glpi_plugin_resources_resources`.`date_declaration_leaving`,
                        `glpi_plugin_resources_resources`.`is_leaving`,
                        `glpi_plugin_resources_resources`.`is_helpdesk_visible`,
                        `glpi_plugin_resources_resources`.`plugin_resources_contracttypes_id` ";
      $query .= " FROM `glpi_plugin_resources_checklists`,`glpi_plugin_resources_resources` ";
      $query .= " WHERE `glpi_plugin_resources_resources`.`is_template` = 0 
                  AND `glpi_plugin_resources_resources`.`is_leaving` = " . $is_leaving . " 
                  AND `glpi_plugin_resources_resources`.`is_deleted` = 0 
                  AND `glpi_plugin_resources_checklists`.`checklist_type` = '" . $checklist_type . "' 
                  AND `glpi_plugin_resources_checklists`.`is_checked` = 0 
                  AND `glpi_plugin_resources_checklists`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id` ";

      if ($entity_restrict && $resource->isEntityAssign()) {
         $LINK  = " AND ";
         $dbu   = new DbUtils();
         $query .= $dbu->getEntitiesRestrictRequest($LINK, "glpi_plugin_resources_resources");
      }

      $query .= " GROUP BY `glpi_plugin_resources_resources`.`id`  ORDER BY `glpi_plugin_resources_resources`.`" . $field . "`";

      return $query;
   }

   /**
    * @param $ID
    * @param $checklist_type
    *
    * @return string
    */
   static function queryListChecklists($ID, $checklist_type) {

      $query = "SELECT `glpi_plugin_resources_checklists`.*  ";
      $query .= " FROM `glpi_plugin_resources_checklists`,`glpi_plugin_resources_resources` ";
      $query .= " WHERE `glpi_plugin_resources_resources`.`id` = " . $ID . " 
                        AND `glpi_plugin_resources_checklists`.`checklist_type` = '" . $checklist_type . "' 
                        AND `glpi_plugin_resources_checklists`.`is_checked` = 0 
                        AND `glpi_plugin_resources_checklists`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id` ";
      $query .= "  ORDER BY `glpi_plugin_resources_checklists`.`rank` ASC;";

      return $query;
   }

   /**
    * Cron action on checklists
    *
    * @param $task for log, if NULL display
    *
    * */
   static function cronResourcesChecklist($task = null) {
      global $DB, $CFG_GLPI;

      if (!$CFG_GLPI["notifications_mailing"]) {
         return 0;
      }

      $message       = [];
      $cron_status   = 0;
      $query_arrival = self::queryChecklists(false);
      $query_leaving = self::queryChecklists(false, 1);

      $querys = [Alert::NOTICE => $query_arrival, Alert::END => $query_leaving];

      $checklist_infos    = [];
      $checklist_messages = [];

      foreach ($querys as $type => $query) {
         $checklist_infos[$type] = [];
         foreach ($DB->request($query) as $data) {
            $entity                            = $data['entities_id'];
            $message                           = "checklists" . ": " . $data["resource_name"] . " " . $data["resource_firstname"] . "<br>\n";
            $checklist_infos[$type][$entity][] = $data;

            if (!isset($checklist_messages[$type][$entity])) {
               $checklist_messages[$type][$entity] = __('Checklists Verification', 'resources') . "<br />";
            }
            $checklist_messages[$type][$entity] .= $message;
         }
      }

      foreach ($querys as $type => $query) {
         foreach ($checklist_infos[$type] as $entity => $checklists) {
            Plugin::loadLang('resources');

            if (NotificationEvent::raiseEvent(($type == Alert::NOTICE ? "AlertArrivalChecklists" : "AlertLeavingChecklists"), new PluginResourcesResource(), ['entities_id' => $entity,
                                                                                                                                                              'checklists'  => $checklists, 'tasklists' => $checklists])) {
               $message     = $checklist_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities", $entity) . ":  $message\n");
                  $task->addVolume(1);
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities", $entity) . ":  $message");
               }
            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities", $entity) .
                             ":  Send checklists resources alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities", $entity) .
                                                   ":  Send checklists resources alert failed", false, ERROR);
               }
            }
         }
      }

      return $cron_status;
   }

   /**
    * @param \PluginPdfSimplePDF $pdf
    * @param \CommonGLPI         $item
    * @param                     $tab
    *
    * @return bool
    */
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType() == 'PluginResourcesResource') {
         self::pdfForResource($pdf, $item, self::RESOURCES_CHECKLIST_IN);
         self::pdfForResource($pdf, $item, self::RESOURCES_CHECKLIST_OUT);
         self::pdfForResource($pdf, $item, self::RESOURCES_CHECKLIST_TRANSFER);
      } else {
         return false;
      }
      return true;
   }

   /**
    * Show for PDF an resources : checklists informations
    *
    * @param $pdf object for the output
    * @param $ID of the resources
    */
   static function pdfForResource(PluginPdfSimplePDF $pdf, PluginResourcesResource $appli, $checklist_type) {
      global $DB;

      $ID = $appli->fields['id'];

      if (!$appli->can($ID, READ)) {
         return false;
      }

      if (!Session::haveRight("plugin_resources", READ)) {
         return false;
      }

      $query  = "SELECT * 
               FROM `glpi_plugin_resources_checklists` 
               WHERE `plugin_resources_resources_id` = '$ID' 
               AND `checklist_type` = '$checklist_type' 
               ORDER BY `rank` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $i = $j = 0;

      $pdf->setColumnsSize(100);
      if ($number > 0) {
         $pdf->displayTitle('<b>' . self::getChecklistType($checklist_type) . '</b>');
         $pdf->setColumnsSize(85, 10, 5);
         $pdf->displayTitle('<b><i>' .
                            __('Name'), __('Linked task', 'resources'), __('Checked', 'resources') . '</i></b>'
         );

         $i++;

         while ($j < $number) {
            $checkedID = $DB->result($result, $j, "is_checked");
            $name      = $DB->result($result, $j, "name");
            $task_id   = $DB->result($result, $j, "plugin_resources_tasks_id");

            if ($checkedID == 1) {
               $checked = __('Yes');
            } else {
               $checked = __('No');
            }
            $pdf->displayLine(
               $name, Dropdown::getYesNo($task_id), $checked
            );
            $j++;
         }

      } else {
         $pdf->displayLine(__('No checklist found', 'resources'));
      }

      $pdf->displaySpace();
   }

   /**
    * @param $menu
    *
    * @return mixed
    */
   static function getMenuOptions($menu) {

      $plugin_page = '/plugins/resources/front/checklistconfig.php';
      $itemtype    = strtolower(self::getType());

      //Menu entry in admin
      $menu['options'][$itemtype]['title']           = self::getTypeName();
      $menu['options'][$itemtype]['page']            = $plugin_page;
      $menu['options'][$itemtype]['links']['search'] = $plugin_page;

      // Add
      if (Session::haveright(self::$rightname, UPDATE)) {
         $menu['options'][$itemtype]['links']['add'] = '/plugins/resources/front/checklistconfig.form.php?new=1';
      }

      // Config
      if (Session::haveRight("config", UPDATE)) {
         $menu['options'][$itemtype]['links']['config'] = '/plugins/resources/front/config.form.php';
      }

      return $menu;
   }

}

