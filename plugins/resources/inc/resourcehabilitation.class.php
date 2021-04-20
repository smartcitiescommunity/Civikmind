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
 * Class PluginResourcesResourceHabilitation
 */
class PluginResourcesResourceHabilitation extends CommonDBTM {

   static $rightname = 'plugin_resources';
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

      return _n('Habilitation', 'Habilitations', $nb, 'resources');
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

      if ($item->getType() == 'PluginResourcesResource'
          && $this->canView()) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForResource($item));
         }
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

      if ($item->getType() == 'PluginResourcesResource') {

         $self = new self();
         $self->showItem($item);
      }
      return true;
   }

   /**
    * @param \PluginResourcesResource $item
    *
    * @return int
    */
   static function countForResource(PluginResourcesResource $item) {

      $restrict = ["plugin_resources_resources_id" => $item->getField('id')];
      $dbu      = new DbUtils();
      $nb       = $dbu->countElementsInTable(['glpi_plugin_resources_resourcehabilitations'], $restrict);

      return $nb;
   }

   /**
    * @param $item
    *
    * @return bool
    */
   function showItem($item) {
      if (!$this->canView()) {
         return false;
      }

      $canedit = $this->canCreate();

      $data = $this->find(['plugin_resources_resources_id' => $item->getField('id')]);

      if ($canedit) {
         $used = [];
         foreach ($data as $habilitation) {
            $used[] = $habilitation['plugin_resources_habilitations_id'];
         }
         echo "<form name='form' method='post' action='" .
              Toolbox::getItemTypeFormURL('PluginResourcesResourceHabilitation') . "'>";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th colspan='2'>" . __('Add additional habilitation', 'resources') . "</th></tr>";
         echo "<tr class='tab_bg_1'><td class='center'>";
         echo self::getTypeName(1) . "</td>";
         echo "<td class='center'>";
         Dropdown::show('PluginResourcesHabilitation', ['used'   => $used,
                                                        'entity' => $item->getField("entities_id")]);
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'><td colspan='2' class='tab_bg_2 center'><input type=\"submit\" name=\"add\" 
                    class=\"submit\" value=\"" . _sx('button', 'Add') . "\" >";
         echo Html::hidden('plugin_resources_resources_id', ['value' => $item->getField('id')]);

         echo "</td></tr>";
         echo "</table></div>";
         Html::closeForm();
      }
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
         echo "<th colspan='2'>" . self::getTypeName() . "</th>";
         echo "</tr>";
         echo "<tr>";
         if ($canedit) {
            echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
         }
         echo "<th>" . __('Name') . "</th>";
         foreach ($fields as $field) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
               echo "</td>";
            }
            //DATA LINE
            echo "<td class='center'>" . Dropdown::getDropdownName('glpi_plugin_resources_habilitations', $field['plugin_resources_habilitations_id']) . "</td>";
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
    * Duplicate item resources from an item template to its clone
    *
    * @since version 0.84
    *
    * @param $itemtype     itemtype of the item
    * @param $oldid        ID of the item to clone
    * @param $newid        ID of the item cloned
    * @param $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
    **/
   static function cloneItem($oldid, $newid) {
      global $DB;

      $query = "SELECT *
                 FROM `glpi_plugin_resources_resourcehabilitations`
                 WHERE `plugin_resources_resources_id` = '$oldid';";

      foreach ($DB->request($query) as $data) {
         $habilitation = new self();
         $habilitation->add(['plugin_resources_resources_id'     => $newid,
                             'plugin_resources_habilitations_id' => $data["plugin_resources_habilitations_id"]]);
      }
   }

   function post_addItem() {
      $changes[0] = 0;
      $changes[1] = '';
      $changes[2] = addslashes(sprintf(__('Adding the habilitation: %s', 'resources'),
                                       Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                                 $this->input['plugin_resources_habilitations_id'])));
      Log::history($this->input['plugin_resources_resources_id'], "PluginResourcesResource", $changes, '',
                   Log::HISTORY_LOG_SIMPLE_MESSAGE);
   }

   /**
    * @return \nothing|void
    */
   function post_deleteFromDB() {
      $changes[0] = 0;
      $changes[1] = '';
      $changes[2] = addslashes(sprintf(__('Suppression of the habilitation: %s', 'resources'),
                                       Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                                 $this->fields['plugin_resources_habilitations_id'])));
      Log::history($this->fields['plugin_resources_resources_id'], "PluginResourcesResource", $changes, '',
                   Log::HISTORY_LOG_SIMPLE_MESSAGE);
   }

   /**
    * Wizard for habilitations
    *
    * @param $plugin_resources_resources_id
    *
    * @return bool
    */
   function wizardSixForm($plugin_resources_resources_id) {
      global $CFG_GLPI, $DB;

      if (!$this->canView()) {
         return false;
      }

      $resource = new PluginResourcesResource();
      $resource->getFromDB($plugin_resources_resources_id);

      if ($plugin_resources_resources_id) {

         $habilitation_level = new PluginResourcesHabilitationLevel();
         $habilitation       = new PluginResourcesHabilitation();

         $dbu = new DbUtils();

         $condition  = $dbu->getEntitiesRestrictCriteria($habilitation_level->getTable(), 'entities_id',$resource->getEntityID(), $habilitation_level->maybeRecursive());
         $levels    = $habilitation_level->find($condition, "name");

         echo Html::css("/plugins/resources/css/style_bootstrap_main.css");
         echo Html::css("/plugins/resources/css/style_bootstrap_ticket.css");
         echo Html::script("/plugins/resources/lib/bootstrap/3.2.0/js/bootstrap.min.js");

         echo "<div id ='content'>";

         echo "<div class='bt-container resources_wizard_resp'>";
         echo "<div class='bt-block bt-features' >";

         echo "<form action='" . Toolbox::getItemTypeFormURL('PluginResourcesWizard') . "' method='post'>";

         echo "<div class=\"bt-row\">";
         echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12\" style=\"border-bottom: #CCC;border-bottom-style: solid;\">";
         echo "<h4 class=\"bt-title-divider\">";
         echo "<img class='resources_wizard_resp_img' src='" . $CFG_GLPI['root_doc'] .
              "/plugins/resources/pics/newresource.png' alt='newresource'/>&nbsp;";
         echo __('Enter habilitations about the resource', 'resources');
         echo "</h4></div></div>";

         if (count($levels) > 0) {

            $cpt=1;
            //One line per level
            foreach ($levels as $level) {
               echo "<div class=\"bt-row\">";
               echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12\">";

               if ($habilitation_level->getFromDB($level['id'])) {
                  $mandatory = "";
                  if ($habilitation_level->getField('is_mandatory_creating_resource')) {
                     $mandatory = "red";
                  }
                  //list of habilitations according to level
                  $habilitations = $habilitation->getHabilitationsWithLevel($habilitation_level,
                                                                            $resource->fields["entities_id"]);

                  // check if habilitation is already set for this level
                  $query_habilitations  = "SELECT `glpi_plugin_resources_habilitations` .*
                              FROM `glpi_plugin_resources_resourcehabilitations`
                              LEFT JOIN `glpi_plugin_resources_habilitations` 
                              ON `glpi_plugin_resources_habilitations`.id = `glpi_plugin_resources_resourcehabilitations`.`plugin_resources_habilitations_id`
                              WHERE `plugin_resources_resources_id` = $plugin_resources_resources_id
                              AND `plugin_resources_habilitationlevels_id` = $cpt";
                  $result_habilitations = $DB->query($query_habilitations);
                  while ($data_habilitation = $DB->fetchAssoc($result_habilitations)) {
                     if(!is_null($data_habilitation)) {
                        $value = $data_habilitation['name'];
                        $id = $data_habilitation['id'];
                        if(!empty($data_habilitation["comment"])){
                           $value .= " - " . $data_habilitation["comment"];
                        }
                     }
                  }
                  if(isset($value) && isset($id)){
                     $key = array_search($value,$habilitations);
                     // Cleaning to avoid duplicate
                     $cleaning_query = "DELETE FROM glpi_plugin_resources_resourcehabilitations
                                        WHERE plugin_resources_resources_id= $plugin_resources_resources_id
                                        AND plugin_resources_habilitations_id= $id";
                     $DB->query($cleaning_query);
                  }
                  $cpt++;

                  echo "<div class=\"bt-row\">";
                  echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 $mandatory\">";
                  echo $habilitation_level->getName();
                  echo "</div>";
                  echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
                  if ($habilitation_level->getField('number')) {
                     Dropdown::showFromArray(str_replace(" ", "_", $habilitation_level->getName()) . "__" . $habilitation_level->getID(),
                        $habilitations,
                        ['multiple' => true,
                         'width'    => 200]);
                  } else {
                     if(isset($key)) {
                        Dropdown::showFromArray(str_replace(" ", "_", $habilitation_level->getName()) . "__" . $habilitation_level->getID(),
                           $habilitations,
                           ['value' => $key]);
                     }else{
                        Dropdown::showFromArray(str_replace(" ", "_", $habilitation_level->getName()) . "__" . $habilitation_level->getID(),
                           $habilitations);
                     }
                  }
                  echo "</div></div>";
               }
               echo "</div></div>";
            }

         } else {

            //No level of habilitations no addition of authorizations to the resource
            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12\">";
            echo __('No habilitation level, you cannot add habilitation for this resource.', 'resources');
            echo "</div></div>";

         }

         if ($this->canCreate()) {
            echo "<div class=\"bt-row\">";
            echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12 \">";
            echo "<div class='preview'>";
            echo "<input type='submit' name='undo_six_step' value='" . _sx('button', '< Previous', 'resources') . "' class='submit' />";
            echo "</div>";
            echo "<div class='next'>";
            echo "<input type='submit' name='six_step' value='" . _sx('button', 'Next >', 'resources') . "' class='submit' />";
            echo Html::hidden('plugin_resources_resources_id', ['value' => $plugin_resources_resources_id]);
            echo "</div>";
            echo "</div>";
            echo "</div>";
         }

         Html::closeForm();
         echo "</div></div>";
         echo "</div>";

      }
   }

   /**
    * Adding habilitations to the resource via the wizard
    *
    * @param $params
    */
   function addResourceHabilitation($params) {
      $habilitation_level = new PluginResourcesHabilitationLevel();

      foreach ($params as $key => $val) {
         if (strpos($key, '__') > 0) {
            list($name, $id) = explode('__', $key);
            if (is_array($val)
                && ($habilitation_level->getFromDB($id))) {
               foreach ($val as $v) {
                  $this->addResourceHabilitationInDb($v, $params);
               }
            } else if ($habilitation_level->getFromDB($id)) {
               $this->addResourceHabilitationInDb($val, $params);
            }
         }
      }
   }

   /**
    * @param $id
    * @param $params
    */
   function addResourceHabilitationInDb($id, $params) {
      $resourceHabilitation = new self();
      $habilitation         = new PluginResourcesHabilitation();

      if ($habilitation->getFromDB($id)) {
         $params["plugin_resources_habilitations_id"] = $id;
         $resourceHabilitation->add($params);
      }
   }

   /**
    * Verification if level of mandatory habilitations
    * return true if required fields are completed correctly
    * false if not
    *
    * @param array $params
    *
    * @return bool
    */
   function checkRequiredFields($params = []) {

      $resource = new PluginResourcesResource();
      $resource->getFromDB($params['plugin_resources_resources_id']);
      $dbu = new DbUtils();

      $habilitation_level = new PluginResourcesHabilitationLevel();
      $condition  = ['is_mandatory_creating_resource' => 1] + $dbu->getEntitiesRestrictCriteria($habilitation_level->getTable(), 'entities_id',$resource->getEntityID(), $habilitation_level->maybeRecursive());
      $levels             = $habilitation_level->find($condition, "name");

      foreach ($levels as $level) {
         if (!isset($params[str_replace(" ", "_", $level['name']) . '__' . $level['id']])
             || (isset($params[str_replace(" ", "_", $level['name'] . '__' . $level['id'])])
                 && empty($params[str_replace(" ", "_", $level['name'] . '__' . $level['id'])]))) {

            return false;

         }

      }
      return true;
   }
}
