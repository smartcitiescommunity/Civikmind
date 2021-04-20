<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 typology plugin for GLPI
 Copyright (C) 2009-2016 by the typology Development Team.

 https://github.com/InfotelGLPI/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of typology.

 typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginTypologyTypologyCriteria
 */
class PluginTypologyTypologyCriteria extends CommonDBTM {

   // From CommonDBTM
   var       $dohistory           = true;
   static    $rightname           = "plugin_typology";
   protected $criteria_id_field   = 'plugin_typology_typologycriterias_id';
   protected $critdefinitionclass = 'PluginTypologyTypologyCriteriaDefinition';

   var $definitions = [];

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   public static function getTypeName($nb = 0) {

      return _n('Criterion', 'Criteria', $nb);
   }

   /**
    * Is the object may be recursive
    *
    * Can be overloaded (ex : infocom)
    *
    * @return boolean
    **/
   function maybeRecursive() {
      return true;
   }

   /**
    * Is the object assigned to an entity
    *
    * Can be overloaded (ex : infocom)
    *
    * @return boolean
    **/
   function isEntityAssign() {
      return true;
   }

   /**
    * is_active = 1 during a creation
    *
    * @return nothing|void
    */
   function post_getEmpty() {

      $this->fields['is_active'] = '1';
   }

   /**
    * Actions done when a typocriteria is deleted from the database
    *
    * @return nothing
    **/
   function cleanDBonPurge() {

      $temp1 = new PluginTypologyTypologyCriteriaDefinition();
      $temp1->deleteByCriteria(['plugin_typology_typologycriterias_id' => $this->fields['id']]);

   }

   /**
    * Display typlogycriteria's tab for each typololgy
    *
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'PluginTypologyTypology' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $dbu = new DbUtils();
                  return self::createTabEntry(self::getTypeName(2),
                                              $dbu->countElementsInTable($this->getTable(),
                                                                         ["plugin_typology_typologies_id" => $item->getID()]));
               }
               return self::getTypeName(2);
         }
      }
      return '';

   }

   /**
    * Display tab's content for each typology
    *
    * @static
    *
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'PluginTypologyTypology') {
         if ($item->canView()) {
            self::showForTypology($item);
         }
      }
      return true;
   }

   /**
    * display tab for typologycriteria
    **/
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginTypologyTypologyCriteriaDefinition', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   /**
    * Display the typologycriteria form, typology side
    *
    * @param PluginTypologyTypology $typo
    * @param bool                   $showAdd
    */
   public static function showForTypology(PluginTypologyTypology $typo, $showAdd = true) {

      $ID   = $typo->getField('id');
      $crit = new PluginTypologyTypologyCriteria();

      $canedit = $typo->can($ID, UPDATE);

      $rand = mt_rand();

      if ($canedit) {
         if ($showAdd) {
            echo "<div class='center first-bloc'>";
            echo "<form name='typocrit_form$rand' id='typocrit_form$rand' method='post' action='";
            echo Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'><th colspan='7'>" . __('Add a criterion', 'typology') . "</tr>";

            echo "<tr class='tab_bg_2'><td class='center'>" . __('Name') . "</td>";
            echo "<input type='hidden' name='plugin_typology_typologies_id' value='$ID'>";
            echo "<input type='hidden' name='entities_id' value='" . $typo->getEntityID() . "'>";
            echo "<input type='hidden' name='is_recursive' value='" . $typo->isRecursive() . "'>";
            echo "</td><td class='center'>";
            Html::autocompletionTextField($crit, "name");
            echo "</td><td class='center'>" . __('Item') . "</td><td class='center' width='20%'>";
            PluginTypologyTypologyCriteria::dropdownItemtype();
            echo "</td><td>" . __('Logical operator') . "</td><td>";
            Dropdown::showFromArray('link', [0 => __('and'), 1 => __('or')]);
            echo "</td><td>";
            echo "<input type='hidden' name='is_active' value='1'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";

            echo "</table>";
            Html::closeForm();

            echo "</div>";
         }

         //         echo "<form name='massiveaction_form$rand' id='massiveaction_form$rand' method='post'
         //                     action=\"../ajax/massiveaction.php\">";

         $restrict = ["plugin_typology_typologies_id" => $ID] +
                     ["ORDER" => "itemtype"];
         $dbu       = new DbUtils();
         $criterias = $dbu->getAllDataFromTable('glpi_plugin_typology_typologycriterias', $restrict);

         Session::initNavigateListItems("PluginTypologyTypologyCriteria", PluginTypologyTypology::getTypeName(1) .
                                                                          " = " . $typo->getName());
         if (!empty($criterias)) {

            echo "<div class='spaced'>";
            if ($canedit) {
               Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
               $massiveactionparams = [];
               Html::showMassiveActions($massiveactionparams);
            }
            echo "<table class='tab_cadre_fixe'>";
            if ($canedit) {
               echo "<tr>";
               echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
               echo "<th colspan=5>" . __('Criteria\'s list', 'typology') . "</th>";
               echo "</tr>";
            }
            foreach ($criterias as $criteria) {

               Session::addToNavigateListItems("PluginTypologyTypologyCriteria", $criteria["id"]);

               if ($showAdd) {
                  $colspan = 6;
               } else {
                  $colspan = 5;
               }

               if ($showAdd) {
                  $type = 'tab_cadre_fixehov';
               } else {
                  $type = 'tab_cadre';
               }

               echo "<div class='center'><table class=$type>";

               if ($showAdd) {
                  echo "<tr><th colspan=$colspan>" . PluginTypologyTypologyCriteria::getTypeName(1) . "</th>";
               } else {
                  echo "<tr><th colspan=$colspan>" . __('Detail of the assigned typology', 'typology') . "</th>";
               }
               echo "</tr>";

               echo "<tr class='tab_bg_2'>";
               if ($showAdd) {
                  echo "<th colspan='2'>" . __('Name') . "</th>";
               }
               echo "<th class='center b'>" . __('Active') . "</th>";
               echo "<th class='center b'>" . __('Item') . "</th>";
               echo "<th class='center b'>" . __('Logical operator') . "</th>";
               echo "<th class='center b'>" . PluginTypologyTypologyCriteriaDefinition::getTypeName(2) . "</th>";
               echo "</tr>";

               echo "<tr class='tab_bg_2'>";

               if ($canedit && $showAdd) {
                  echo "<td width='10'>";
                  //                  echo "<input type='checkbox' name='item[".$criteria["id"]."]' value='1'>";
                  Html::showMassiveActionCheckBox(__CLASS__, $criteria["id"]);
                  echo "</td>";
               }
               echo "<td width='10%'>";

               if ($canedit) {
                  echo "<a href='" . Toolbox::getItemTypeFormURL('PluginTypologyTypologyCriteria') .
                       "?id=" . $criteria["id"] . "'>";
               }

               echo $criteria["name"];
               if (empty($criteria["name"])) {
                  echo "(" . $criteria['id'] . ")";
               }
               if ($canedit) {
                  echo "</a>";
               }
               echo "</td>";

               echo "<td width='10%' align='center'>";
               echo Dropdown::getYesNo($criteria['is_active']);
               echo "</td>";

               $item = new $criteria['itemtype']();

               echo "<td width='10%'>" . $item::getTypeName(0) . "</td>";

               echo "<td width='10%' align='center'>";
               if ($criteria['link'] == 0) {
                  echo __('and');
               } else if ($criteria['link'] == 1) {
                  echo __('or');
               }
               echo "</td><td>";

               $condition   = ["plugin_typology_typologycriterias_id" => $criteria['id']] +
                              ["ORDER" => "id"];
               $definitions = $dbu->getAllDataFromTable('glpi_plugin_typology_typologycriteriadefinitions',
                                                        $condition);
               if (!empty($definitions)) {
                  echo "<table class='tab_cadre' width='100%'>";
                  echo "<tr>";
                  echo "<th class='center b' width='33%'>" . _n('Field', 'Fields', 2) . "</th>";
                  echo "<th class='center b' width='33%'>" . __('Logical operator') . "</th>";
                  echo "<th class='center b'>" . __('Value') . "</th>";
                  echo "</tr>";

                  foreach ($definitions as $definition) {
                     echo "<tr>";
                     $definition['itemtype'] = $criteria['itemtype'];
                     PluginTypologyTypologyCriteriaDefinition::showMinimalDefinitionForm($definition);
                     echo "</tr>";
                  }
                  echo "</table>";
               }
               echo "</td></tr>";
            }
            echo "</table>";
            if ($canedit) {
               $paramsma['ontop'] = false;
               Html::showMassiveActions($paramsma);
               Html::closeForm();
            }

            echo "</div>";

            //            if ($showAdd){
            //               Html::openArrowMassives("massiveaction_form$rand",true);
            //               self::dropdownMassiveAction($rand);
            //               Html::closeArrowMassives(array());
            //            }
         }
      }
      //      Html::closeForm();
   }

   //   static function dropdownMassiveAction($rand) {
   //      global $CFG_GLPI;
   //
   //      echo "<select name=\"massiveaction\" id='massiveaction$rand'>";
   //      echo "<option value=\"-1\" selected>".Dropdown::EMPTY_VALUE."</option>";
   //      echo "<option value=\"deleteAll\">".__('Delete permantly')."</option>";
   //      echo "<option value=\"updateAll\">".__('Update')."</option>";
   //      echo "</select>";
   //
   //      $params=array('action'=>'__VALUE__',
   //      );
   //
   //      Ajax::updateItemOnSelectEvent("massiveaction$rand","show_massiveaction$rand",
   //         PLUGIN_TYPOLOGY_DIR . "/ajax/dropdownMassiveAction.php",$params);
   //
   //      echo "<span id='show_massiveaction$rand'>&nbsp;</span>\n";
   //
   //   }

   /**
    * Show the criteria form
    *
    * @param $ID ID of the criteria
    * @param $options options
    *
    * @return nothing
    **/
   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $itemtype = $this->fields["itemtype"];

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . "</td><td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td>" . __('Item') . "</td><td>";
      echo $itemtype::getTypeName(0) . "</td>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Logical operator') . "</td><td>";
      Dropdown::showFromArray('link',
                              [0 => __('and'), 1 => __('or')],
                              ['value' => $this->fields["link"]]);
      echo "</td>";

      echo "<td>" . __('Active') . "</td><td>";
      Dropdown::showYesNo('is_active', $this->fields['is_active']);
      echo "</td>";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>";

      $typo = new PluginTypologyTypology();
      $typo->getFromDB($this->fields['plugin_typology_typologies_id']);
      echo "<td>" . PluginTypologyTypology::getTypeName(1) . "</td>";
      echo "<td>";
      echo $typo->getLink();
      echo "</td>";

      echo "<td>" . __('Last update') . "</td>";
      echo "<td>" . ($this->fields["date_mod"] ? Html::convDateTime($this->fields["date_mod"])
            : __('Never'));

      echo "</tr>\n";

      $this->showFormButtons($options);
   }

   /**
    * Display a dropdown which contains all the available itemtypes
    *
    * @param $typocrit_id the field widget item id
    * @param value the selected value
    *
    * @return nothing
    **/
   static function dropdownItemtype() {

      //Add definition : display dropdown
      $types = PluginTypologyTypology::getTypesCriteria();

      $options[0] = Dropdown::EMPTY_VALUE;

      foreach ($types as $itemtype) {
         $item               = new $itemtype();
         $options[$itemtype] = $item->getTypeName($itemtype);
      }

      asort($options);
      return Dropdown::showFromArray('itemtype', $options);
   }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @since version 0.84
    *
    * @return an array of massive actions
    **/
   public function getForbiddenStandardMassiveAction() {
      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      $forbidden[] = 'purge';

      return $forbidden;
   }

   /**
    * Get the specific massive actions
    *
    * @since version 0.84
    *
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    **/
   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);
      if ($isadmin) {
         $actions['PluginTypologyTypologyCriteria' . MassiveAction::CLASS_ACTION_SEPARATOR . 'deleteAll'] = _sx('button', 'Delete permanently');
         $actions['PluginTypologyTypologyCriteria' . MassiveAction::CLASS_ACTION_SEPARATOR . 'updateAll'] = _sx('button', 'Upgrade');
      }

      return $actions;
   }

   /**
    * @param MassiveAction $ma
    *
    * @return bool|false
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $CFG_GLPI;

      switch ($ma->getAction()) {
         case "deleteAll":
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
                 _sx('button', 'Post') . "\" >";
            return true;
            break;
         case "updateAll":
            $values = [0 => Dropdown::EMPTY_VALUE,
               'is_active' => __('Active')];
            $rand = Dropdown::showFromArray('field', $values);

            $params = ['field'  => '__VALUE__',
                       'action' => $_POST["action"]];

            Ajax::updateItemOnSelectEvent("dropdown_field$rand", "show_massiveaction_field",
               $CFG_GLPI["root_doc"]. PLUGIN_TYPOLOGY_DIR_NOFULL . "/ajax/dropdownMassiveActionField.php",
                                          $params);

            echo "&nbsp;<span id='show_massiveaction_field'>&nbsp;</span>\n";
            return true;
            break;

      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    *
    * @param MassiveAction $ma
    * @param CommonDBTM    $item
    * @param array         $ids
    *
    * @return nothing|void
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $criteria = new PluginTypologyTypologyCriteria();
      $input    = $ma->getInput();

      switch ($ma->getAction()) {
         case "deleteAll":
            foreach ($ids as $key) {
               if ($criteria->can($key, UPDATE)) {
                  if ($criteria->delete(['id' => $key])) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            break;
         case "updateAll":
            foreach ($ids as $key) {
               if ($criteria->can($key, UPDATE)) {
                  $values = ['id'        => $key,
                             'is_active' => $input['is_active']];
                  if ($criteria->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            break;

         default :
            parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
      }
   }
}
