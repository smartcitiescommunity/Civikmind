<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 databases plugin for GLPI
 Copyright (C) 2009-2016 by the databases Development Team.

 https://github.com/InfotelGLPI/databases
 -------------------------------------------------------------------------

 LICENSE

 This file is part of databases.

 databases is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 databases is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with databases. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginDatabasesDatabase
 */
class PluginDatabasesDatabase extends CommonDBTM {

   public    $dohistory  = true;
   static    $rightname  = "plugin_databases";
   protected $usenotepad = true;

   static $types = ['Computer', 'Software', 'SoftwareLicense', 'Appliance'];

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {

      return _n('Database', 'Databases', $nb, 'databases');
   }

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string|translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'Supplier') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(2), self::countForItem($item));
         }
         return self::getTypeName(2);
      }
      return '';
   }


   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Supplier') {
         $self = new self();
         $self->showPluginFromSupplier($item->getField('id'));
      }
      return true;
   }

   /**
    * @param CommonDBTM $item
    *
    * @return int
    */
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_databases_databases',
                                        ["suppliers_id" => $item->getID()]);
   }

   /**
    * clean if databases are deleted
    */
   function cleanDBonPurge() {

      $temp = new PluginDatabasesDatabase_Item();
      $temp->deleteByCriteria(['plugin_databases_databases_id' => $this->fields['id']]);
   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType()
      ];

      $tab[] = [
         'id'       => '2',
         'table'    => 'glpi_plugin_databases_databasecategories',
         'field'    => 'name',
         'name'     => PluginDatabasesDatabaseCategory::getTypeName(1),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '4',
         'table'    => 'glpi_plugin_databases_servertypes',
         'field'    => 'name',
         'name'     => PluginDatabasesServerType::getTypeName(1),
         'datatype' => 'dropdown'
      ];

      $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

      $tab[] = [
         'id'       => '5',
         'table'    => 'glpi_suppliers',
         'field'    => 'name',
         'name'     => __('Supplier'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '6',
         'table'    => 'glpi_manufacturers',
         'field'    => 'name',
         'name'     => __('Editor', 'databases'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'            => '7',
         'table'         => 'glpi_plugin_databases_databases_items',
         'field'         => 'items_id',
         'nosearch'      => true,
         'massiveaction' => false,
         'name'          => _n('Associated item', 'Associated items', 2),
         'forcegroupby'  => true,
         'joinparams'    => [
            'jointype' => 'child'
         ]
      ];

      $tab[] = [
         'id'       => '9',
         'table'    => $this->getTable(),
         'field'    => 'comment',
         'name'     => __('Comments'),
         'datatype' => 'text'
      ];

      $tab[] = [
         'id'       => '10',
         'table'    => 'glpi_plugin_databases_databasetypes',
         'field'    => 'name',
         'name'     => PluginDatabasesDatabaseType::getTypeName(1),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'        => '11',
         'table'     => 'glpi_users',
         'field'     => 'name',
         'linkfield' => 'users_id',
         'name'      => __('Technician in charge of the hardware'),
         'datatype'  => 'dropdown',
         'right'     => 'interface'
      ];

      $tab[] = [
         'id'        => '12',
         'table'     => 'glpi_groups',
         'field'     => 'name',
         'linkfield' => 'groups_id',
         'name'      => __('Group in charge of the hardware'),
         'condition' => '`is_assign`',
         'datatype'  => 'dropdown'
      ];

      $tab[] = [
         'id'       => '13',
         'table'    => $this->getTable(),
         'field'    => 'is_helpdesk_visible',
         'name'     => __('Associable to a ticket'),
         'datatype' => 'bool'
      ];

      $tab[] = [
         'id'            => '14',
         'table'         => $this->getTable(),
         'field'         => 'date_mod',
         'massiveaction' => false,
         'name'          => __('Last update'),
         'datatype'      => 'datetime'
      ];

      $tab[] = [
         'id'                 => '15',
         'table'              => $this->getTable(),
         'field'              => 'link',
         'name'               => __('URL'),
         'datatype'           => 'weblink'
      ];

      $tab[] = [
         'id'       => '30',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'number'
      ];

      $tab[] = [
         'id'       => '80',
         'table'    => 'glpi_entities',
         'field'    => 'completename',
         'name'     => __('Entity'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'    => '81',
         'table' => 'glpi_entities',
         'field' => 'entities_id',
         'name'  => __('Entity') . "-" . __('ID')
      ];

      $tab[] = [
         'id'       => '86',
         'table'    => $this->getTable(),
         'field'    => 'is_recursive',
         'name'     => __('Child entities'),
         'datatype' => 'bool'
      ];

      return $tab;
   }

   //define header form

   /**
    * @param array $options
    *
    * @return array
    */
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addImpactTab($ong, $options);
      $this->addStandardTab('PluginDatabasesDatabase_Item', $ong, $options);
      $this->addStandardTab('PluginDatabasesInstance', $ong, $options);
      $this->addStandardTab('PluginDatabasesScript', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   /**
    * @return string
    */
   function getSelectLinkedItem() {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_databases_databases_items`
              WHERE `plugin_databases_databases_id`='" . $this->fields['id'] . "'";
   }

   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td>" . PluginDatabasesDatabaseCategory::getTypeName(1) . "</td>";
      echo "<td>";
      Dropdown::show('PluginDatabasesDatabaseCategory', ['name'   => "plugin_databases_databasecategories_id",
                                                         'value'  => $this->fields["plugin_databases_databasecategories_id"],
                                                         'entity' => $this->fields["entities_id"]]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Location') . "</td>";
      echo "<td>";
      Location::dropdown(['value'  => $this->fields["locations_id"],
                          'entity' => $this->fields["entities_id"]]);
      echo "</td>";

      echo "<td>" . PluginDatabasesServerType::getTypeName(1) . "</td>";
      echo "<td>";
      Dropdown::show('PluginDatabasesServerType', ['name'  => "plugin_databases_servertypes_id",
                                                   'value' => $this->fields["plugin_databases_servertypes_id"]]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Technician in charge of the hardware') . "</td><td>";
      User::dropdown(['name'   => "users_id",
                      'value'  => $this->fields["users_id"],
                      'entity' => $this->fields["entities_id"],
                      'right'  => 'interface']);
      echo "</td>";

      echo "<td>" . PluginDatabasesDatabaseType::getTypeName(1) . "</td>";
      echo "<td>";
      Dropdown::show('PluginDatabasesDatabaseType', ['name'   => "plugin_databases_databasetypes_id",
                                                     'value'  => $this->fields["plugin_databases_databasetypes_id"],
                                                     'entity' => $this->fields["entities_id"]]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Group in charge of the hardware') . "</td><td>";
      Group::dropdown(['name'      => 'groups_id',
                       'value'     => $this->fields['groups_id'],
                       'entity'    => $this->fields['entities_id'],
                       'condition' => ['is_assign' => 1]]);
      echo "</td>";

      echo "<td>" . __('Editor', 'databases') . "</td>";
      echo "<td>";
      Dropdown::show('Manufacturer', ['name'   => "manufacturers_id",
                                      'value'  => $this->fields["manufacturers_id"],
                                      'entity' => $this->fields["entities_id"]]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Supplier') . "</td>";
      echo "<td>";
      Dropdown::show('Supplier', ['name'   => "suppliers_id",
                                  'value'  => $this->fields["suppliers_id"],
                                  'entity' => $this->fields["entities_id"]]);
      echo "</td>";

      echo "<td>" . __('Associable to a ticket') . "</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('URL') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "link");
      echo "&nbsp;<a target='_blank' href='" . $this->getField("link") . "'><i class=\"fas fa-link\"></i></a>";
      echo "</td>";

      echo "<td></td><td>";

      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td class='center' colspan = '4'>";
      printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo __('Comments') . "</td></tr>";
      echo "<tr>";
      echo "<td class='center'>";
      echo "<textarea cols='125' rows='8' name='comment'>" . $this->fields["comment"] . "</textarea>";
      echo "</td></tr></table>";
      echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * Make a select box for link database
    *
    * Parameters which could be used in options array :
    *    - name : string / name of the select (default is plugin_databases_databasetypes_id)
    *    - entity : integer or array / restrict to a defined entity or array of entities
    *                   (default -1 : no restriction)
    *    - used : array / Already used items ID: not to display in dropdown (default empty)
    *
    * @param $options array of possible options
    *
    * @return nothing (print out an HTML select box)
    **/
   static function dropdownDatabase($options = []) {
      global $DB, $CFG_GLPI;

      $p['name']    = 'plugin_databases_databases_id';
      $p['entity']  = '';
      $p['used']    = [];
      $p['display'] = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }
      $dbu   = new DbUtils();
      $where = " WHERE `glpi_plugin_databases_databases`.`is_deleted` = '0' " .
               $dbu->getEntitiesRestrictRequest("AND", "glpi_plugin_databases_databases", '', $p['entity'], true);

      $p['used'] = array_filter($p['used']);
      if (count($p['used'])) {
         $where .= " AND `id` NOT IN (0, " . implode(",", $p['used']) . ")";
      }

      $query  = "SELECT *
                FROM `glpi_plugin_databases_databasetypes`
                WHERE `id` IN (SELECT DISTINCT `plugin_databases_databasetypes_id`
                               FROM `glpi_plugin_databases_databases`
                             $where)
                ORDER BY `name`";
      $result = $DB->query($query);

      $values = [0 => Dropdown::EMPTY_VALUE];

      while ($data = $DB->fetchAssoc($result)) {
         $values[$data['id']] = $data['name'];
      }
      $rand     = mt_rand();
      $out      = Dropdown::showFromArray('_databasetype', $values, ['width'   => '30%',
                                                                     'rand'    => $rand,
                                                                     'display' => false]);
      $field_id = Html::cleanId("dropdown__databasetype$rand");

      $params = ['databasetype' => '__VALUE__',
                 'entity'       => $p['entity'],
                 'rand'         => $rand,
                 'myname'       => $p['name'],
                 'used'         => $p['used']];

      $out .= Ajax::updateItemOnSelectEvent($field_id, "show_" . $p['name'] . $rand,
                                            $CFG_GLPI["root_doc"].PLUGIN_DATABASES_DIR_NOFULL . "/ajax/dropdownTypeDatabases.php",
                                            $params, false);
      $out .= "<span id='show_" . $p['name'] . "$rand'>";
      $out .= "</span>\n";

      $params['databasetype'] = 0;
      $out                    .= Ajax::updateItem("show_" . $p['name'] . $rand,
                                                  $CFG_GLPI["root_doc"].PLUGIN_DATABASES_DIR_NOFULL . "/ajax/dropdownTypeDatabases.php",
                                                  $params, false);
      if ($p['display']) {
         echo $out;
         return $rand;
      }
      return $out;
   }

   /**
    * For other plugins, add a type to the linkable types
    *
    * @param $type string class name
    **@since version 1.3.0
    *
    */
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    **/
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * @param        $ID
    * @param string $withtemplate
    */
   function showPluginFromSupplier($ID, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      $item    = new Supplier();
      $canread = $item->can($ID, READ);
      $dbu     = new DbUtils();

      $query = "SELECT `glpi_plugin_databases_databases`.* "
               . "FROM `glpi_plugin_databases_databases` "
               . " LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_databases_databases`.`entities_id`) "
               . " WHERE `suppliers_id` = '$ID' "
               . $dbu->getEntitiesRestrictRequest(" AND ", "glpi_plugin_databases_databases", '', '', $this->maybeRecursive());
      $query .= " ORDER BY `glpi_plugin_databases_databases`.`name` ";

      $result = $DB->query($query);
      $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      if ($withtemplate != 2) {
         echo "<form method='post' action=\"" . $CFG_GLPI["root_doc"].PLUGIN_DATABASES_DIR_NOFULL . "/front/database.form.php\">";
      }

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='" . (4 + $colsup) . "'>" . _n('Database associated', 'Databases associated', 2, 'databases') . "</th></tr>";
      echo "<tr><th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>" . __('Entity') . "</th>";
      }
      echo "<th>" . PluginDatabasesDatabaseCategory::getTypeName(1) . "</th>";
      echo "<th>" . __('Type') . "</th>";
      echo "<th>" . __('Comments') . "</th>";

      echo "</tr>";

      while ($data = $DB->fetchArray($result)) {

         echo "<tr class='tab_bg_1" . ($data["is_deleted"] == '1' ? "_2" : "") . "'>";
         if ($withtemplate != 3 && $canread && (in_array($data['entities_id'], $_SESSION['glpiactiveentities']) || $data["is_recursive"])) {
            echo "<td class='center'><a href='" . $CFG_GLPI["root_doc"].PLUGIN_DATABASES_DIR_NOFULL . "/front/database.form.php?id=" . $data["id"] . "'>" . $data["name"];
            if ($_SESSION["glpiis_ids_visible"]) {
               echo " (" . $data["id"] . ")";
            }
            echo "</a></td>";
         } else {
            echo "<td class='center'>" . $data["name"];
            if ($_SESSION["glpiis_ids_visible"]) {
               echo " (" . $data["id"] . ")";
            }
            echo "</td>";
         }
         echo "</a></td>";
         if (Session::isMultiEntitiesMode()) {
            echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entities_id']) . "</td>";
         }
         echo "<td>" . Dropdown::getDropdownName("glpi_plugin_databases_databasetypes", $data["plugin_databases_databasetypes_id"]) . "</td>";
         echo "<td>" . Dropdown::getDropdownName("glpi_plugin_databases_servertypes", $data["plugin_databases_servertypes_id"]) . "</td>";
         echo "<td>" . $data["comment"] . "</td></tr>";
      }
      echo "</table></div>";
      Html::closeForm();
   }

   /**
    * @param null $checkitem
    *
    * @return array
    * @since version 0.85
    *
    * @see CommonDBTM::getSpecificMassiveActions()
    *
    */
   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if (Session::getCurrentInterface() == 'central') {
         if ($isadmin) {
            $actions['PluginDatabasesDatabase' . MassiveAction::CLASS_ACTION_SEPARATOR . 'install']   = _x('button', 'Associate');
            $actions['PluginDatabasesDatabase' . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall'] = _x('button', 'Dissociate');

            if (Session::haveRight('transfer', READ)
                && Session::isMultiEntitiesMode()
            ) {
               $actions['PluginDatabasesDatabase' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __('Transfer');
            }
         }
      }
      return $actions;
   }

   /**
    * @param MassiveAction $ma
    *
    * @return bool|false
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    *
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'plugin_databases_add_item':
            self::dropdownDatabase([]);
            echo "&nbsp;" .
                 Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "install" :
            Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'item_item',
                                                   'itemtype_name' => 'typeitem',
                                                   'itemtypes'     => self::getTypes(true),
                                                   'checkright'
                                                                   => true,
                                                  ]);
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "uninstall" :
            Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'item_item',
                                                   'itemtype_name' => 'typeitem',
                                                   'itemtypes'     => self::getTypes(true),
                                                   'checkright'
                                                                   => true,
                                                  ]);
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }


   /**
    * @param MassiveAction $ma
    * @param CommonDBTM    $item
    * @param array         $ids
    *
    * @return nothing|void
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    *
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $database_item = new PluginDatabasesDatabase_Item();

      switch ($ma->getAction()) {
         case "plugin_databases_add_item":
            $input = $ma->getInput();
            foreach ($ids as $id) {
               $input = ['plugin_databases_databasetypes_id' => $input['plugin_databases_databasetypes_id'],
                         'items_id'                          => $id,
                         'itemtype'                          => $item->getType()];
               if ($database_item->can(-1, UPDATE, $input)) {
                  if ($database_item->add($input)) {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
               }
            }

            return;
         case "transfer" :
            $input = $ma->getInput();
            if ($item->getType() == 'PluginDatabasesDatabase') {
               foreach ($ids as $key) {
                  $item->getFromDB($key);
                  $type = PluginDatabasesDatabaseType::transfer($item->fields["plugin_databases_databasetypes_id"], $input['entities_id']);
                  if ($type > 0) {
                     $values["id"]                                = $key;
                     $values["plugin_databases_databasetypes_id"] = $type;
                     $item->update($values);
                  }

                  unset($values);
                  $values["id"]          = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;

         case 'install' :
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($item->can($key, UPDATE)) {
                  $values = ['plugin_databases_databases_id' => $key,
                             'items_id'                      => $input["item_item"],
                             'itemtype'                      => $input['typeitem']];
                  if ($database_item->add($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                  $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
               }
            }
            return;

         case 'uninstall':
            $input = $ma->getInput();
            foreach ($ids as $key) {
               if ($database_item->deleteItemByDatabasesAndItem($key, $input['item_item'], $input['typeitem'])) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }
            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

}
