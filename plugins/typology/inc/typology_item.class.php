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
 * Class PluginTypologyTypology_Item
 */
class PluginTypologyTypology_Item extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1          = 'PluginTypologyTypology';
   static public $items_id_1          = 'plugin_typology_typologies_id';
   static public $take_entity_1 = false;

   static public $itemtype_2          = 'itemtype';
   static public $items_id_2          = 'items_id';
   static public $take_entity_2 = true;

   static $rightname = "plugin_typology";

   const LOG_ADD        = 1;
   const LOG_UPDATE     = 2;
   const LOG_DELETE     = 3;
   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    **/
   public static function getTypeName($nb = 0) {

      return _n('Element', 'Elements', $nb, 'typology');
   }

   /**
    * Display typology-item's tab for each computer
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'PluginTypologyTypology') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               $dbu = new DbUtils();
               return self::createTabEntry(self::getTypeName(2),
                                           $dbu->countElementsInTable($this->getTable(),
                                                                      ["plugin_typology_typologies_id" => $item->getID()]));
            }
               return self::getTypeName(2);
         } else if (in_array($item->getType(), PluginTypologyTypology::getTypes(true))
            && $this->canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginTypologyTypology::getTypeName(2), self::countForItem($item));
            }
            return PluginTypologyTypology::getTypeName(2);
         }
      }
      return '';
   }


   /**
    * @param \CommonDBTM $item
    *
    * @return int
    */
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_typology_typologies_items',
                                  ["itemtype" => $item->getType(),
                                   "items_id" => $item->getID()]);
   }

   /**
    * Display tab's content for each computer
    *
    * @static
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType()=='PluginTypologyTypology') {

         self::showForTypology($item);

      } else if (in_array($item->getType(), PluginTypologyTypology::getTypes(true))) {
         self::showPluginFromItems($item->getType(), $item->getField('id'));
      }
      return true;
   }

   //if item deleted

   /**
    * @param \CommonDBTM $item
    */
   static function cleanItemTypology(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         ['itemtype' => $item->getType(),
               'items_id' => $item->getField('id')]
      );
   }

   /**
    * Add Logs
    *
    * @return nothing
    **/
   static function addLog($input, $logtype) {

         $new_value = $_SESSION["glpiname"]." ";
      if ($logtype == self::LOG_ADD) {
         $new_value .= __('Add element to the typology', 'typology')." : ";
      } else if ($logtype == self::LOG_UPDATE) {
         $new_value .= __('Update element to the typology', 'typology')." : ";
      } else if ($logtype == self::LOG_DELETE) {
         $new_value .= __('Element out of the typology', 'typology')." : ";
      }

         $item = new $input['itemtype']();
         $item->getFromDB($input['items_id']);

         $new_value .= $item->getName(0)." - ".
         Dropdown::getDropdownName('glpi_plugin_typology_typologies', $input['plugin_typology_typologies_id']);

      self::addHistory($input['plugin_typology_typologies_id'],
         "PluginTypologyTypology", "", $new_value);

      self::addHistory($input['items_id'],
         $input['itemtype'], "", $new_value);

   }

   /**
    * Add an history
    *
    * @return nothing
    **/
   static function addHistory($ID, $type, $old_value = '', $new_value = '') {
      $changes[0] = 0;
      $changes[1] = $old_value;
      $changes[2] = $new_value;
      Log::history($ID, $type, $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
   }

   /**
    * allow to control data before adding in bdd
    *
    * @param datas $input
    * @return bool|datas|the
    */
   function prepareInputForAdd($input) {

      if (isset($input['_ruleid'])) {
         $input=self::checkValidated($input);
      } else {

         $values = ['plugin_typology_typologies_id' => $input['plugin_typology_typologies_id'],
                            'items_id'      => $input['items_id'],
                            'itemtype'      => $input['itemtype']];

         $ID = self::checkIfExist($values);
         if ($ID == -1) {
            return false;
         } else {
            $input=self::checkValidated($input);
         }
      }

      return $input;
   }

   /**
    * Check is typology is exist
    *
    * @param $input
    *
    * @return true / false

    **/
   static function checkIfExist($input, $display = true) {

      //to control if item has already a typo
      $restrict = ["items_id" => $input["items_id"],
                   "itemtype" => $input["itemtype"]];
      //item a déjà une typo, action annulee + message erreur
      $dbu        = new DbUtils();
      $typo_items = $dbu->getAllDataFromTable("glpi_plugin_typology_typologies_items", $restrict);
      if (!empty($typo_items)) {
         foreach ($typo_items as $typo_item) {
            $typoID = $typo_item["plugin_typology_typologies_id"];
            $ID     = $typo_item["id"];
         }
      }
      if (isset($typoID) && $typoID > 0) {

         if ($display) {
            $itemtype_table = $dbu->getTableForItemType($input["itemtype"]);
            $message = Dropdown::getDropdownName($itemtype_table, $input["items_id"])." : ".
               __('You cannot assign this typology to this material as he has already a typology : ', 'typology').
               Dropdown::getDropdownName('glpi_plugin_typology_typologies', $typoID);
            Session::addMessageAfterRedirect($message, ERROR, true);
            return -1;
         }
         return $ID;
      }

      return 0;
   }

   /**
    * Check is typology is validated
    *
    * @param $input
    *
    * @return error list / result

    **/
   static function checkValidated($input) {

      $res = self::showManagementConsole($input["items_id"],
         $input["plugin_typology_typologies_id"],
         false);
      if (count($res) == 0) {
         $input["is_validated"] = '1';
         $input["error"]='NULL';
      } else {
         $input["is_validated"] = '0';
         $list='';
         $i=0;
         Foreach ($res as $critID) {
            if ($i==0) {
               $list.=$critID;
               $i++;
            } else {
               $list.=','.$critID;
            }
         }
         $input["error"]=$list;
      }

      return $input;
   }


   /**
    * See typology errors
    *
    * @param $error list
    *
    * @return nothing

    **/
   static function displayErrors($error, $withlink = true) {
      global $CFG_GLPI;

      $items = explode(",", $error);

      $i = 0;
      $display = "";
      if ($items[0]!= null) {
         foreach ($items as $critID) {
            $crit = new PluginTypologyTypologyCriteria();
            if ($crit->getFromDB($critID)) {

               if ($i==0) {
                  $display.=" ";
                  $i++;
               } else {
                  $display.=", ";
               }
               $itemtype = $crit->fields["itemtype"];
               $criteria= "";
               if ($withlink) {
                  $criteria="<a href='".$CFG_GLPI["root_doc"].PLUGIN_TYPOLOGY_DIR_NOFULL.
                     "/front/typologycriteria.form.php?id=".
                     $crit->fields["id"]."' target='_blank'>";
               }
               $criteria.=$crit->fields["name"];
               if ($_SESSION["glpiis_ids_visible"]||empty($crit->fields["name"])) {
                  $criteria.=" (".$crit->fields["id"].")";
               }
               if ($withlink) {
                  $criteria.= "</a>";
               }
               $criteria.= " (".$itemtype::getTypeName(0).")";

               $display.=$criteria;
            }

         }
      }

      return $display;
   }


   /**
    * Actions done after the ADD of a item in the database
    *
    * @param $item : the item added
    *
    * @return nothing
    **/
   static function addItem($item) {

      $values = [];
      $plugin = new Plugin();
      if ($plugin->isActivated("typology")) {
         $ruleCollection = new PluginTypologyRuleTypologyCollection($item->fields['entities_id']);
         $fields= [];
         //si massive action ajouter tous les champs de la rules
         //         if(!isset($item->input['_update'])){
            $rule = new PluginTypologyRuleTypology();
         foreach ($rule->getCriterias() as $ID => $crit) {
            if (!isset($item->input[$ID])) {
               if (isset($item->fields[$ID])) {
                  $item->input[$ID] = $item->fields[$ID];
               }
            }
         }
         //         }
         $fields=$ruleCollection->processAllRules($item->input, $fields, []);
         //Store rule that matched
         if (isset($fields['_ruleid'])) {

            $values = ['plugin_typology_typologies_id' => $fields['plugin_typology_typologies_id'],
                            'items_id'      => $item->fields['id'],
                            'itemtype'      => $item->getType()];

            //verifie si tu as deja une typo
            $ID = self::checkIfExist($values, false);
            //si pas de typo, ajout
            if ($ID == 0) {
               $values['_ruleid'] = $fields['_ruleid'];
               $self = new self();
               $self->add($values);

               self::addLog($values, self::LOG_ADD);

            } else {
               //si typo, return ID typo_item
               $values['id'] = $ID;
            }
         } else {
            //math no rules
            $values = ['items_id'      => $item->fields['id'],
                            'itemtype'      => $item->getType()];
            $ID = self::checkIfExist($values, false);
            //si typo, delete
            if ($ID != '0') {

               $values['id'] = $ID;
               $self = new self();
               $self->getFromDB($ID);
               $values['plugin_typology_typologies_id'] = $self->fields['plugin_typology_typologies_id'];
               $self->delete($values);

               self::addLog($values, self::LOG_DELETE);
               return false;
            }
         }
         return $values;
      }
   }

   /**
    * Actions done after the ADD of a computer in the database
    *
    * @param $item : the item added
    *
    * @return nothing
    **/
   static function updateItem($item) {

      $plugin = new Plugin();
      if ($plugin->isActivated("typology")) {

         $values = self::addItem($item);

         if (isset($values['id'])) {
            $input=self::checkValidated($values);

            $self = new self();
            $input["id"] = $values['id'];
            $self->update($input);

            self::addLog($values, self::LOG_UPDATE);

         }
      }
   }

   /**
    * Display a link to add directly an item to a typo.
    *
    * @param $itemtype of item class
    * @param $ID : id item
    *
    * @return Nothing (displays)
    **/
   public static function showPluginFromItems($itemtype, $ID, $withtemplate = '') {
      global $DB,$CFG_GLPI;

      $typo_item = new PluginTypologyTypology_Item();
      $table_typo_item = $typo_item->getTable();
      $item = new $itemtype();

      if (!Session::haveRight("plugin_typology", READ)) {
         return false;
      }

      if (!$item->can($ID, READ)) {
         return false;
      }

      $canread = $item->can($ID, READ);
      $canedit = $item->can($ID, UPDATE);

      $restrict = ["items_id" => $ID,
                   "itemtype" => $itemtype];

      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }

      $used=[];

      if ($withtemplate!=2) {
         echo "<form method='post' action=\"".
               $CFG_GLPI["root_doc"]. PLUGIN_TYPOLOGY_DIR_NOFULL . "/front/typology.form.php\">";
      }

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      $dbu = new DbUtils();
      //typologie attribuée
      if ($dbu->countElementsInTable($table_typo_item, $restrict) > 0) {
         $dbu   = new DbUtils();
         $typos = $dbu->getAllDataFromTable($table_typo_item, $restrict);
         if (!empty($typos)) {
            foreach ($typos as $typo) {
               $typo_ID = $typo["plugin_typology_typologies_id"];
            }
         }

         $query = "SELECT `" . $table_typo_item . "`.`id` AS typo_items_id,
                           `" . $table_typo_item . "`.`is_validated`,
                           `" . $table_typo_item . "`.`error`,
                             `glpi_plugin_typology_typologies`.*"
                  . " FROM `" . $table_typo_item . "`,`glpi_plugin_typology_typologies` "
                  . " LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_typology_typologies`.`entities_id`) "
                  . " WHERE `" . $table_typo_item . "`.`plugin_typology_typologies_id` = '" . $typo_ID . "' "
                  . " AND `" . $table_typo_item . "`.`items_id` = '" . $ID . "' "
                  . " AND `" . $table_typo_item . "`.`itemtype` = '" . $itemtype . "'
                    AND `" . $table_typo_item . "`.`plugin_typology_typologies_id`=`glpi_plugin_typology_typologies`.`id`"
                  . $dbu->getEntitiesRestrictRequest(" AND ", "glpi_plugin_typology_typologies", '', '', true);

         $result = $DB->query($query);

         echo "<tr><th>".__('Typology assigned to this material', 'typology')."</th>";

         if (Session::isMultiEntitiesMode()) {
            echo "<th>".__('Entity')."</th>";
         }

         echo "<th>".__('Responding to typology\'s criteria', 'typology')."</th>";

         echo "<th>".__('Actions', 'typology')."</th>";

         echo "</tr>";

         while ($data=$DB->fetchArray($result)) {
            $typo_ID=$data["id"];
            $used[]=$typo_ID;

            echo "<tr class='tab_bg_1'>";
            if ($withtemplate!=3
                  && $canread
                  && ((in_array($data['entities_id'], $_SESSION['glpiactiveentities'])
                                 || $data["is_recursive"]))) {
               echo "<td class='center'><a href='".
                        $CFG_GLPI["root_doc"]. PLUGIN_TYPOLOGY_DIR_NOFULL . "/front/typology.form.php?id=".
                        $data["id"]."'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]||empty($data["name"])) {
                  echo " (".$data["id"].")";
               }
               echo "</a></td>";
            } else {
               echo "<td class='center'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]||empty($data["name"])) {
                  echo " (".$data["id"].")";
               }
               echo "</td>";
            }
            if (Session::isMultiEntitiesMode()) {
               echo "<td  class='center'>".
                  Dropdown::getDropdownName("glpi_entities", $data['entities_id'])."</td>";
            }

            if ($data["is_validated"] > 0) {
               $critTypOK = __('Yes');
            } else {
               $critTypOK = "<span class='typology_font_red_bold'>".__('No')." ".__('for the criteria', 'typology')." ";
               $i=0;

               $critTypOK.=self::displayErrors($data["error"]);

               $critTypOK.="</span>";
            }

            echo "<td class ='center'><b>".$critTypOK."</b></td>";

            echo "<td class='center'  width = '25%'>";

            echo "<input type='hidden' name='items_id' value='$ID'>";
            echo "<input type='hidden' name='itemtype' value='$itemtype'>";
            echo "<input type='hidden' name='id' value='".$data["typo_items_id"]."'>";
            echo "<input type='hidden' name='plugin_typology_typologies_id' value='".$data["id"]."'>";

            //actualiser la typologie

            echo "<input type='submit' name='update_item' value=\""._sx('button', 'Upgrade').
               "\" class='submit'>";

            //retirer la typologie
            if ($canedit) {

                echo "&nbsp;&nbsp;<input type='submit' name='delete_item' value=\""._sx('button', 'Delete permanently').
                     "\" class='submit'>";
            }
            echo "</td>";
            echo "</tr>";
         }

         self::showManagementConsole($ID, $typo_ID);

         //typologie non attribuée
      } else {
         echo "<tr><th colspan='".(5+$colsup)."'>".__('Assign a typology to this material', 'typology')."</th></tr>";

         //Affecter une typologie
         if ($canedit) {

            if ($withtemplate<2) {
               if ($typo_item->canCreate()) {

                  $typo_item->showAdd($itemtype, $ID);
               }
            }
         }
      }

      echo "</table></div>";
      if ($withtemplate!=2) {
         Html::closeForm();
      }
   }

   /**
    * Display a link to add directly an item to a typo.
    *
    * @param $itemtype of item class
    *
    * @return Nothing (displays)
    **/
   function showAdd ($itemtype, $ID) {

      $item = new $itemtype();
      $dbu = new DbUtils();

      if ($item->isRecursive()) {
         $entities = $dbu->getSonsOf('glpi_entities', $item->getEntityID());
      } else {
         $entities = $item->getEntityID();
      }

      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<input type='hidden' name='items_id' value='$ID'>";
      echo "<input type='hidden' name='itemtype' value='$itemtype'>";
      echo "<td class='center' class='tab_bg_2'>";
      echo PluginTypologyTypology::getTypeName(2)." ";
      Dropdown::show('PluginTypologyTypology',
                     ['name' => "plugin_typology_typologies_id",
                           'entities_id' => $entities]);
      echo "</td><td class='center' class='tab_bg_2'>";
      echo "<input type='submit' name='add_item' value=\""._sx('button', 'Post')."\" class='submit'></td></tr></div>";
   }

   /**
    * Display all the linked computers of a defined typology
    *
    *@param $typoID = typo ID.
    *
    *@return Nothing (displays)
    **/
   public static function showForTypology(PluginTypologyTypology $typo) {
      global $CFG_GLPI;

      $typoID = $typo->fields['id'];
      $dbu = new DbUtils();

      if (!$typo->can($typoID, READ)) {
         return false;
      }

      $canedit = $typo->can($typoID, UPDATE);
      $rand    = mt_rand();

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      if ($canedit) {

         echo "<div class='firstbloc'>";
         echo "<form method='post' name='typologies_form$rand' id='typologies_form$rand' action='" .
            $CFG_GLPI["root_doc"]. PLUGIN_TYPOLOGY_DIR_NOFULL . "/front/typology.form.php'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='7'>" . __('Add an item') . "</th></tr>";

         echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
         echo "<input type='hidden' name='plugin_typology_typologies_id' value='$typoID'>";
         Dropdown::showSelectItemFromItemtypes(['items_id_name'   => "items_id",
                                                'entity_restrict' => ($typo->fields['is_recursive'] ? -1 : $typo->fields['entities_id']),
                                                'itemtypes'       => PluginTypologyTypology::getTypes()
                                               ]);
         echo "</td>";
         echo "<td colspan='3' class='center' class='tab_bg_2'>";

         echo "<input type='submit' name='add_item' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
         echo "</td></tr>";

         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      $types = PluginTypologyTypology::getTypes();
      $title = __('Linked elements', 'typology');
      $type  = Session::getSavedOption(__CLASS__, 'onlytype', '');

      if (!in_array($type, $types)) {
         $type = 'Computer';
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'><th colspan='3'>$title</tr>";
      echo "<tr class='tab_bg_1'><td class='center'>";
      echo __('Type') . "&nbsp;";
      Dropdown::showItemType($types, ['value'      => $type,
                                          'name'       => 'onlytype',
                                          'plural'     => true,
                                          'on_change'  => 'reloadTab("start=0&onlytype="+this.value)',
                                          'checkright' => true]);
      echo "</td></tr></table>";

      if (empty($type)) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th class='center'>";
         echo __('Select a type', 'typology');
         echo "</th></tr>";
         echo "</table>";

      } else {

         $datas = [];

         $start  = (isset($_GET['start']) ? intval($_GET['start']) : 0);
         $number = self::getDataItems($type, $start, $datas, $typoID);

         Html::printAjaxPager('', $start, $number);

         if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => $typo, 'container' => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='" . ($canedit ? (7 + $colsup) : (6 + $colsup)) . "'>";

         if ($number == 0) {
            echo __('No linked element', 'typology');
         } else {
            echo __('Linked elements', 'typology');
         }

         echo "</th></tr><tr>";

         if ($canedit && $number) {
            echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
         }

         echo "<th>" . __('Type') . "</th>";
         echo "<th>" . __('Name') . "</th>";
         if (Session::isMultiEntitiesMode()) {
            echo "<th>" . __('Entity') . "</th>";
         }
         echo "<th>" . __('Serial number') . "</th>";
         echo "<th>" . __('Inventory number') . "</th>";
         echo "<th>" . __('Responding to typology\'s criteria', 'typology') . "</th>";
         echo "</tr>";

         $itemtype = $type;
         $item     = $dbu->getItemForItemtype($itemtype);

         foreach ($datas as $data) {

            Session::initNavigateListItems($type, PluginTypologyTypology::getTypeName(1) .
               " = " . $typo->fields['name']);

            $ID = "";

            Session::addToNavigateListItems($itemtype, $data["id"]);

            if ($itemtype == 'User') {
               $format = $dbu->formatUserName($data["id"], $data["name"], $data["realname"], $data["firstname"], 1);
            } else {
               $format = $data["name"];
            }

            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
               $ID = " (" . $data["id"] . ")";
            }

            $link = Toolbox::getItemTypeFormURL($itemtype);

            $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\">" . $format;

            if ($itemtype != 'User') {
               $name.= "&nbsp;" . $ID;
            }

            $name.= "</a>";

            echo "<tr class='tab_bg_1'>";

            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["IDP"]);
               echo "</td>";
            }

            echo "<input type='hidden' name='plugin_typology_typologies_id' value='$typoID'>";
            echo "<td class='center'>" . $item->getTypeName() . "</td>";
            echo "<td class='center' " . (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") . ">" . $name . "</td>";
            if (Session::isMultiEntitiesMode()) {
               if ($itemtype != 'User') {
                  echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entity']) . "</td>";
               } else {
                  echo "<td class='center'>-</td>";
               }
            }
            echo "<td class='center'>" . (isset($data["serial"]) ? "" . $data["serial"] . "" : "-") . "</td>";
            echo "<td class='center'>" . (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";

            if ($data["is_validated"] > 0) {
               $critTypOK = __('Yes');
            } else {
               $critTypOK = "<span class='typology_font_red'>" . __('No') . " " .
                  __('for the criteria', 'typology') . " ";
               $i         = 0;

               $critTypOK.=self::displayErrors($data["error"]);

               $critTypOK.="</font>";
            }

            echo "<td class ='center'><b>" . $critTypOK . "</b></td>";

            echo "</tr>";
         }
         echo "</table>";
         if ($canedit && $number) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }

         echo "</div>";
      }
   }

   /**
    * @param       $itemtype
    * @param       $start
    * @param array $res
    * @param       $typoID
    *
    * @return mixed|void
    */
   static function getDataItems($itemtype, $start, array &$res, $typoID) {
      global $DB;

      $dbu = new DbUtils();
      if (!($item = $dbu->getItemForItemtype($itemtype))) {
         return;
      }
      if (!$item->canView()) {
         return;
      }
      $itemtable = $dbu->getTableForItemType($itemtype);
      $column    = "name";

      $max = $_SESSION['glpilist_limit'];
      $res = [];

      $query = "SELECT count(`glpi_plugin_typology_typologies_items`.`id`) AS count"
         . " FROM `glpi_plugin_typology_typologies_items`, `" . $itemtable
         . "` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `" . $itemtable . "`.`entities_id`) "
         . " WHERE `" . $itemtable . "`.`id` = `glpi_plugin_typology_typologies_items`.`items_id`
                              AND `glpi_plugin_typology_typologies_items`.`itemtype` = '$itemtype'
                              AND `glpi_plugin_typology_typologies_items`.`plugin_typology_typologies_id` = '$typoID'";
      if ($itemtype != 'User') {
         $query.= $dbu->getEntitiesRestrictRequest(" AND ", $itemtable, '', '', $item->maybeRecursive());
      }

      if ($item->maybeTemplate()) {
         $query.=" AND " . $itemtable . ".is_template='0'";
      }
      $result = $DB->query($query);
      $number = $DB->result($result, 0, "count");

      $query = "SELECT `" . $itemtable . "`.*,
                                `glpi_plugin_typology_typologies_items`.`id` AS IDP,
                                `glpi_plugin_typology_typologies_items`.`is_validated`,
                                `glpi_plugin_typology_typologies_items`.`error`,
                                `glpi_entities`.`id` AS entity "
         . " FROM `glpi_plugin_typology_typologies_items`, `" . $itemtable
         . "` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `" . $itemtable . "`.`entities_id`) "
         . " WHERE `" . $itemtable . "`.`id` = `glpi_plugin_typology_typologies_items`.`items_id`
                              AND `glpi_plugin_typology_typologies_items`.`itemtype` = '$itemtype'
                              AND `glpi_plugin_typology_typologies_items`.`plugin_typology_typologies_id` = '$typoID'";
      if ($itemtype != 'User') {
         $query.=$dbu->getEntitiesRestrictRequest(" AND ", $itemtable, '', '', $item->maybeRecursive());
      }

      if ($item->maybeTemplate()) {
         $query.=" AND " . $itemtable . ".is_template='0'";
      }

      $query.=" ORDER BY `glpi_entities`.`completename`, `" . $itemtable . "`.`$column`
                  LIMIT $start, $max";

      foreach ($DB->request($query) as $data) {
         $res[] = $data;
         $max--;
      }

      return $number;
   }

   /**
    * Display a management console in order to see the difference between the typology linkked
    * and data from each computer
    *
    * @param $ID : id item
    *
    * @return Nothing (displays)
    **/
   static function showManagementConsole($ID, $typo_ID, $display = true) {

      $notOK = [];

      //image type
      $options['seeResult'] = 1;
      $options['seeItemtype']=1;

      $restrict  = ["plugin_typology_typologies_id" => $typo_ID,
                    "is_active" => 1]+
                    ["ORDER" => "itemtype"];

      $dbu       = new DbUtils();
      $criterias = $dbu->getAllDataFromTable('glpi_plugin_typology_typologycriterias', $restrict);

      //typology criteria exist -> managmentconsole will be not empty
      if (!empty($criterias)) {

         if ($display) {
            echo "<div class='center'><table class='tab_cadre_fixe'>";

            //title
            echo "<tr><th colspan='7'>";
            echo __('Management console', 'typology');
            echo "</th></tr>";

            //column name
            echo "<tr>";
            echo "<th rowspan='2'>".__('Item')."</th>";
            echo "<th colspan='2' rowspan='2'>"._n('Field', 'Fields', 2)."</th>";
            echo "<th rowspan='2'>".__('Comparison', 'typology')."</th>";
            echo "<th colspan='2'>".__('Detail of the assigned typology', 'typology')."</th>";
            echo "<th>".__('Detail of the encountered configuration', 'typology')."</th>";
            echo "</tr>";

            //sub-column name
            echo "<tr>";
            echo "<th class='center b'>".__('Logical operator')."</th>";
            echo "<th class='center b'>".__('Waiting value', 'typology')."</th>";
            echo "<th class='center b'>".__('Real value', 'typology')."</th>";
            echo "</tr>";
         }

         foreach ($criterias as $criteria) {
            $tabCrit[$criteria['itemtype']][] = $criteria['id'];
         }

         foreach ($tabCrit as $itemtype => $tabCritID) {

            $datas = PluginTypologyTypologyCriteriaDefinition::getConsoleData($tabCritID, $ID, $itemtype, $display);
            if (!$display) {
               foreach ($datas[$itemtype] as $k=>$critId) {
                  if ($critId['result'] == 'not_ok') {
                     $notOK[]=$k;
                  }
               }
            } else {
               foreach ($datas as $itemtype=>$allCrit) {
                  if (!empty($allCrit)) {
                     foreach ($allCrit as $key1=>$allDef) {
                        if (!empty($allDef)) {
                           foreach ($allDef as $key2=>$def) {
                              if (!empty($def)) {
                                 echo "<tr class='tab_bg_1'>";
                                 $def['itemtype'] = $itemtype;

                                 PluginTypologyTypologyCriteriaDefinition::showMinimalDefinitionForm($def, $options);
                                 echo "</tr>";
                              }
                           }
                        }
                        echo "<tr>";
                        echo "<th colspan='7' class='center b'></th>";
                        echo "</tr>";
                     }
                  }
               }
            }
         }

         if ($display) {
            echo "</table></div>";
         }
         return $notOK;
      }
   }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @since version 0.84
    *
    * @return an array of massive actions
    **/
   public function getForbiddenStandardMassiveAction() {
      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';

      return $forbidden;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         //add item to a typo
         case "add_item":
            echo "</br>&nbsp;".PluginTypologyTypology::getTypeName(2)." : ";
            Dropdown::show('PluginTypologyTypology',
                     ['name' => "plugin_typology_typologies_id"]);
            echo "&nbsp;" .
            Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         //delete item to a typo
         case "delete_item":
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         //update item to a typo
         case "update_allitem":
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
      }
   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $typo_item = new PluginTypologyTypology_Item();
      $dbu = new DbUtils();

      switch ($ma->getAction()) {
         case "add_item":
            $input = $ma->getInput();

            foreach ($ids as $id) {

               $input = ['plugin_typology_typologies_id' => $input['plugin_typology_typologies_id'],
                              'items_id'      => $id,
                              'itemtype'      => $item->getType()];

               if ($item->getFromDB($id)) {
                  $ruleCollection = new PluginTypologyRuleTypologyCollection($item->fields['entities_id']);
                  $fields= [];
                  $item->input = $input['plugin_typology_typologies_id'];
                  $fields=$ruleCollection->processAllRules($item->fields, $fields, []);
                  //Store rule that matched

                  if (isset($fields['_ruleid'])) {
                     if ($input['plugin_typology_typologies_id'] != $fields['plugin_typology_typologies_id']) {
                        $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                        $ma->addMessage(Dropdown::getDropdownName($dbu->getTableForItemType($item->getType()), $id)." : "
                                        .__('Element not match with rules for assigning a typology', 'typology'));
                     } else {
                        if ($typo_item->can(-1, UPDATE, $input)) {
                           if ($typo_item->add($input)) {
                              PluginTypologyTypology_Item::addLog($input, PluginTypologyTypology_Item::LOG_ADD);
                              $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                           } else {
                              $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                           }
                        } else {
                           $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                     }
                  } else {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                     $ma->addMessage(Dropdown::getDropdownName($dbu->getTableForItemType($item->getType()), $id)." : "
                                    .__('Element not match with rules for assigning a typology', 'typology'));
                  }
               }
            }
         return;
         case "delete_item":
            foreach ($ids as $id) {

               if ($typo_item->getFromDBByCrit(["`items_id` = $id
                                                AND `itemtype` = '".$item->getType()."'"])) {
                  $values = ['plugin_typology_typologies_id' => $typo_item->fields['plugin_typology_typologies_id'],
                                 'items_id'      => $typo_item->fields['items_id'],
                                 'itemtype'      => $typo_item->fields['itemtype']];

                  if ($typo_item->delete(['id'=>$typo_item->fields['id']])) {

                        PluginTypologyTypology_Item::addLog($values, PluginTypologyTypology_Item::LOG_DELETE);

                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  }
               }
            }
         return;
         case "update_allitem":
            foreach ($ids as $id) {
               if ($typo_item->getFromDBByCrit(["`items_id` = $id 
                                                AND `itemtype` = '".$item->getType()."'"])) {
                  $result = PluginTypologyTypology_Item::checkValidated(['items_id' => $typo_item->fields['items_id'],
                                                                              'plugin_typology_typologies_id'=>$typo_item->fields['plugin_typology_typologies_id'],
                                                                              'id'=>$typo_item->fields['id']]);
                  if ($typo_item->update($result)) {
                     $values = ['plugin_typology_typologies_id' => $typo_item->fields['plugin_typology_typologies_id'],
                                    'items_id'      => $typo_item->fields['items_id'],
                                    'itemtype'      => $typo_item->fields['itemtype']];

                     PluginTypologyTypology_Item::addLog($values, PluginTypologyTypology_Item::LOG_UPDATE);
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  }
               }
            }
         return;

      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }
}
