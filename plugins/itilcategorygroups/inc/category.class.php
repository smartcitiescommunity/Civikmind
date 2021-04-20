<?php
/*
 * @version $Id: setup.php 19 2012-06-27 09:19:05Z walid $
 LICENSE

  This file is part of the itilcategorygroups plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with itilcategorygroups. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   itilcategorygroups
 @author    the itilcategorygroups plugin team
 @copyright Copyright (c) 2010-2011 itilcategorygroups plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/itilcategorygroups
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

class PluginItilcategorygroupsCategory extends CommonDropdown {

   public $first_level_menu      = "plugins";
   public $second_level_menu     = "itilcategorygroups";
   public $display_dropdowntitle = false;

   static $rightname         = 'config';

   var $dohistory = true;

   static function getTypeName($nb = 0) {
      return __('Link ItilCategory - Groups', 'itilcategorygroups');
   }

   static function canCreate() {
      return static::canUpdate();
   }

   static function canPurge() {
      return static::canUpdate();
   }

   function showForm($id, $options = []) {

      if (! $this->can($id, READ)) {
         return false;
      }

      $this->initForm($id);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td><label>".__('Name')." :</label></td>";
      echo "<td style='width:30%'>";
      echo Html::autocompletionTextField($this, "name");
      echo "</td>";

      $rand = mt_rand();
      echo "<td><label for='dropdown_is_active$rand'>".__('Active')." :</label></td>";
      echo "<td style='width:30%'>";
      Dropdown::showYesNo('is_active', $this->fields['is_active'], -1, ['rand' => $rand]);
      echo "</td></tr>";

      $rand = mt_rand();
      echo "<tr class='tab_bg_1'>";
      echo "<td><label for='dropdown_itilcategories_id$rand'>".__('Category')." :</label></td>";
      echo "<td>";
      Dropdown::show('ITILCategory', [
         'value' => $this->fields['itilcategories_id'],
         'rand' => $rand]);
      echo "</td>";

      // Groups restriction
      $rand = mt_rand();
      echo "<td><label for='dropdown_is_groups_restriction$rand'>".__('Display only the groups on the next level')." :</label></td>";
      echo "<td style='width:30%'>";
      Dropdown::showYesNo('is_groups_restriction', $this->fields['is_groups_restriction'], -1, ['rand' => $rand]);
      echo "</td></tr>";

      $rand = mt_rand();
      echo "<tr class='tab_bg_1'>";
      echo "<td><label for='dropdown_is_incident$rand'>".__('Visible for an incident')." :</label></td>";
      echo "<td>";
      Dropdown::showYesNo('is_incident', $this->fields['is_incident'], -1, ['rand' => $rand]);
      echo "</td>";

      $rand = mt_rand();
      echo "<td><label for='dropdown_is_request$rand'>".__('Visible for a request')." :</label></td>";
      echo "<td>";
      Dropdown::showYesNo('is_request', $this->fields['is_request'], -1, ['rand' => $rand]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td><label for='comment'>".__('Comments') . " : </label></td>";
      echo "<td align='left'>";
      echo "<textarea name='comment' id='comment' style='width:100%; height:70px;'>";
      echo $this->fields["comment"] . "</textarea>";
      echo "</td><td colspan='2'></td></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'><hr></td></tr>";

      echo "<tr class='tab_bg_1'><td><label for='groups_id_level1[]'>".ucfirst(__('Level 1', 'itilcategorygroups'))." :</label></td>";
      echo "<td>";
      $this->multipleDropdownGroup(1);
      echo "</td>";
      echo "<td><label for='groups_id_level2[]'>".ucfirst(__('Level 2', 'itilcategorygroups'))." :</label></td>";
      echo "<td>";
      $this->multipleDropdownGroup(2);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td><label for='groups_id_level3[]'>".ucfirst(__('Level 3', 'itilcategorygroups'))." :</label></td>";
      echo "<td>";
      $this->multipleDropdownGroup(3);
      echo "</td>";
      echo "<td><label for='groups_id_level4[]'>".ucfirst(__('Level 4', 'itilcategorygroups'))." :</label></td>";
      echo "<td>";
      $this->multipleDropdownGroup(4);
      echo "</td></tr>";

      $this->showFormButtons($options);
      Html::closeForm();

   }

   function multipleDropdownGroup($level) {
      global $DB;

      // find current values for this select
      $values = [];
      if ($this->getID()) {
         $res_val = $DB->query("SELECT `groups_id`
            FROM glpi_plugin_itilcategorygroups_categories_groups
            WHERE (`itilcategories_id` = {$this->fields['itilcategories_id']}
               OR `plugin_itilcategorygroups_categories_id` = {$this->getID()}
            )
            AND level = $level");
         while ($data_val = $DB->fetchAssoc($res_val)) {
            $values[] = $data_val['groups_id'];
         }
      }

      // find possible values for this select
      $res_gr = $DB->query("SELECT gr.id, gr.name
         FROM glpi_groups gr
         INNER JOIN glpi_plugin_itilcategorygroups_groups_levels gr_lvl
            ON gr_lvl.groups_id = gr.id
            AND gr_lvl.lvl = ".intval($level).
            getEntitiesRestrictRequest(" AND", "gr", '', $_SESSION["glpiactiveentities"], true));

      if ($this->fields["view_all_lvl$level"] == 1) {
         $checked = "checked='checked'";
         $disabled = "disabled='disabled'";
      } else {
         $checked = "";
         $disabled = "";
      }

      echo "<span id='select_level_$level'>";
      echo "<select name='groups_id_level".$level."[]' id='groups_id_level".$level."[]' $disabled multiple='multiple' class='chzn-select' data-placeholder='-----' style='width:160px;'>";
      while ($data_gr = $DB->fetchAssoc($res_gr)) {
         if (in_array($data_gr['id'], $values)) {
            $selected = "selected";
         } else {
            $selected = "";
         }
         echo "<option value='".$data_gr['id']."' $selected>".$data_gr['name']."</option>";
      }
      echo "</select>";
      echo "</span>";
      echo '<script>$("#select_level_'.$level.' select").select2();</script>';
      echo "<input type='hidden' name='view_all_lvl$level' value='0'>";
      echo "&nbsp;<label for='view_all_lvl$level'>".__('All')." ?&nbsp;</label>".
           "<input type='checkbox' name='view_all_lvl$level' id='view_all_lvl$level' $checked onclick='toggleSelect($level)'/>";
   }

   function prepareInputForAdd($input) {
      $cat = new self();
      $found_cat = $cat->find(['itilcategories_id' => $this->input["itilcategories_id"]]);
      if (count($found_cat) > 0) {
         Session::addMessageAfterRedirect(__("A link with this category already exists", "itilcategorygroups"));
         return false;
      }

      return $this->prepareInputForUpdate($input);
   }

   function prepareInputForUpdate($input) {
      foreach ($input as &$value) {
         if ($value === "on") {
            $value = 1;
         }
      }
      return $input;
   }

   function post_addItem() {
      $this->input["id"] = $this->fields["id"];
      $this->post_updateItem();
   }

   function post_updateItem($history = 1) {

      // quick fix :
      if (isset($_REQUEST['massiveaction'])) {
         return;
      }

      $cat_group = new PluginItilcategorygroupsCategory_Group();

      for ($lvl=1; $lvl <= 4; $lvl++) {

         if ($this->input["view_all_lvl$lvl"] != 1) {

            //delete old groups values
            $found_cat_groups = $cat_group->find(
               [
                  'itilcategories_id' => $this->input["itilcategories_id"],
                  'level' => $lvl
               ]
            );
            foreach ($found_cat_groups as $id => $current_cat_group) {
               $cat_group->delete(['id' => $current_cat_group['id']]);
            }

            //insert new saved
            if (isset($this->input["groups_id_level$lvl"])) {
               foreach ($this->input["groups_id_level$lvl"] as $groups_id) {
                  $cat_group->add(['plugin_itilcategorygroups_categories_id' => $this->input["id"],
                                   'level'                                   => $lvl,
                                   'itilcategories_id'                       => $this->input["itilcategories_id"],
                                   'groups_id'                               => $groups_id]);
               }
            }
         }
      }

   }

   /**
    * get SQL condition for filtered dropdown assign groups
    * @param int $tickets_id
    * @param int $itilcategories_id
    * @return string
    */
   static function getSQLCondition($tickets_id, $itilcategories_id, $type) {
      $ticket = new Ticket();
      $group  = new Group();
      $params = ['entities_id'  => $_SESSION['glpiactive_entity'],
                 'is_recursive' => 1];

      if (!empty($tickets_id) && $ticket->getFromDB($tickets_id)) {
         // == UPDATE EXISTING TICKET ==
         $params['entities_id'] = $ticket->fields['entities_id'];
         $params['condition'] = " AND ".($ticket->fields['type'] == Ticket::DEMAND_TYPE?
            "`is_request`='1'" : "`is_incident`='1'");
      } else {
         if ($type == Ticket::DEMAND_TYPE) {
            $params['condition'] = " AND `is_request` ='1'";
         } else {
            $params['condition'] = " AND `is_incident` = '1'";
         }
      }
      // == CHECKS FOR LEVEL VISIBILITY ==
      $level = 0;
      $categoryGroup = new PluginItilcategorygroupsCategory_Group();
      $table = getTableForItemType(get_class($categoryGroup));
      // All groups assigned to the ticket
      foreach ($ticket->getGroups(2) as $element) {
         $groupsId = $element['groups_id'];
         $data_level = self::getFirst("SELECT level FROM `$table` WHERE itilcategories_id = '$itilcategories_id' AND groups_id = '$groupsId'", 'level');
         if (!empty($data_level)) {
            $level = $data_level > $level ? $data_level : $level;
         }
         // Don't display groups already assigned to the ticket in the dropdown
         $params['condition'] .= " AND cat_gr.groups_id <> '$groupsId'";
      }
      // No group assigned to the ticket
      // Selects the level min that will be displayed
      if ($level == 0) {
         $level = self::getFirst("SELECT MIN(level) as level FROM `$table` WHERE itilcategories_id = '$itilcategories_id'", 'level');
         $params['condition'] .= " AND cat_gr.level = '$level'";
      } else {
         $level_max = $level + 1;
         $params['condition'] .= " AND (cat_gr.level = '$level' OR cat_gr.level = '$level_max')";
      }
      $found_groups = self::getGroupsForCategory($itilcategories_id, $params, $type);
      $groups_id_toshow = []; //init
      if (!empty($found_groups)) {
         for ($lvl=1; $lvl <= 4; $lvl++) {
            if (isset($found_groups['groups_id_level'.$lvl])) {
               if ($found_groups['groups_id_level'.$lvl] === "all") {
                  foreach (PluginItilcategorygroupsGroup_Level::getAllGroupForALevel($lvl, $params['entities_id']) as $groups_id) {
                     if ($group->getFromDB($groups_id)) {
                        $groups_id_toshow[] = $group->getID();
                     }
                  }

               } else {
                  foreach ($found_groups['groups_id_level'.$lvl] as $groups_id) {
                     if (countElementsInTableForEntity("glpi_groups", $ticket->getEntityID(),
                                                       ['id' => $groups_id]) > 0) {
                        $group->getFromDB($groups_id);
                        $groups_id_toshow[] = $group->getID();
                     }
                  }
               }
            }
         }
      }

      $condition = [];
      if (count($groups_id_toshow) > 0) {
         // transform found groups (2 dimensions) in a flat array
         $groups_id_toshow_flat = [];
         array_walk_recursive($groups_id_toshow, function($v, $k) use(&$groups_id_toshow_flat) {
            array_push($groups_id_toshow_flat, $v);
         });

         $condition['id'] = $groups_id_toshow_flat;
      }
      return $condition;
   }

   /**
    * get groups for category
    * @param int $itilcategories_id
    * @param array $params
    * @return array
    */
   static function getGroupsForCategory($itilcategories_id, $params = []) {
      global $DB;

      //define default options
      $options['entities_id']  = 0;
      $options['is_recursive'] = 0;
      $options['condition']    = " AND cat.is_incident = '1'";

      // override default options with params
      foreach ($params as $key => $value) {
         $options[$key] = $value;
      }

      $groups   = [];
      $category = new ITILCategory();
      $table    = getTableForItemType(__CLASS__);

      if ($category->getFromDB($itilcategories_id)) {
         $entity_restrict = getEntitiesRestrictRequest(" AND ", "cat", "entities_id",
                                                       $options['entities_id'],
                                                       $options['is_recursive']);

         // increase size of group concat to avoid errors
         $DB->query("SET SESSION group_concat_max_len = 1000000");

         // retrieve all groups associated to this cat
         $query = "SELECT
                     cat.*,
                     GROUP_CONCAT(\"{\\\"gr_id\\\":\",
                                  cat_gr.groups_id,
                                  \", \\\"lvl\\\": \",
                                  cat_gr.level,
                                  \"}\") as groups_level
                   FROM `$table` cat
                   LEFT JOIN glpi_plugin_itilcategorygroups_categories_groups cat_gr
                     ON cat_gr.plugin_itilcategorygroups_categories_id = cat.id
                   WHERE cat.itilcategories_id = '$itilcategories_id' ".
                   $options['condition'].$entity_restrict.
                   " AND cat.is_active = '1'
                   ORDER BY cat.entities_id DESC";
         foreach ($DB->request($query) as $data) {
            $groups_level = json_decode("[".$data['groups_level']."]", true);

            for ($level = 1; $level <= 4; $level++) {
               if ($data["view_all_lvl$level"]) {
                  $groups["groups_id_level$level"] = "all";
               } else {
                  foreach ($groups_level as $current_group_level) {
                     if ($current_group_level['lvl'] == $level) {
                        $groups["groups_id_level$level"][] = $current_group_level['gr_id'];
                     }
                  }
               }
            }
         }
      }

      return $groups;
   }
   /**
    * Helper to make a database request and extract the first element
    * @param string $query
    * @param string $selector
    * @return mixed
    */
   public static function getFirst($query, $selector) {
      global $DB;
      $data = $DB->request($query);
      if (count($data)) {
         $data = json_decode("[" . $data->next()["$selector"] . "]", true);
         return array_shift($data);
      }
      return null;
   }
   /**
    * Method used to check if the default filter must be applied
    * @param string $itilcategories_id
    * @return bool
    */
   public static function canApplyFilter($itilcategories_id) {
      global $DB;
      $category = new ITILCategory();
      if ($category->getFromDB($itilcategories_id)) {
         $table = getTableForItemType(__CLASS__);
         $query = "SELECT is_active FROM `$table` WHERE itilcategories_id = $itilcategories_id AND is_active = '1' AND is_groups_restriction = '1'";
         $data = $DB->request($query);
         // A category rule exist for this ticket
         if (count($data)) {
            return true;
         }
      }
      return false;
   }


   static function getOthersGroupsID($level = 0) {
      global $DB;

      $res = $DB->query("SELECT gr.id
                        FROM glpi_groups gr
                        LEFT JOIN glpi_plugin_itilcategorygroups_groups_levels gl
                           ON gl.groups_id = gr.id
                        WHERE gl.lvl != $level
                        AND gr.is_assign
                        OR gl.lvl IS NULL");
      $groups_id = [];
      while ($row = $DB->fetchAssoc($res)) {
         $groups_id[$row['id']] = $row['id'];
      }

      return $groups_id;
   }

   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'               => 'common',
         'name'             => __('Link ItilCategory - Groups', 'itilcategorygroups'),
      ];

      $tab[] = [
         'id'               => 1,
         'table'            => $this->getTable(),
         'field'            => 'name',
         'name'             => __('Name'),
         'datatype'         => 'itemlink',
         'checktype'        => 'text',
         'displaytype'      => 'text',
         'injectable'       => true,
         'massiveaction'    => false,
         'autocomplete'     => true,
      ];

      $tab[] = [
         'id'               => 2,
         'table'            => $this->getTable(),
         'field'            => 'is_incident',
         'name'             => __('Visible for an incident'),
         'datatype'         => 'bool',
         'checktype'        => 'bool',
         'displaytype'      => 'bool',
         'injectable'       => true,
      ];

      $tab[] = [
         'id'               => 3,
         'table'            => $this->getTable(),
         'field'            => 'is_request',
         'name'             => __('Visible for a request'),
         'datatype'         => 'bool',
         'checktype'        => 'bool',
         'displaytype'      => 'bool',
         'injectable'       => true,
      ];

      $tab[] = [
         'id'               => 4,
         'table'            => 'glpi_itilcategories',
         'field'            => 'name',
         'name'             => __('Category'),
         'datatype'         => 'itemlink',
         'checktype'        => 'text',
         'displaytype'      => 'text',
         'injectable'       => true,
      ];

      $tab[] = [
         'id'               => 5,
         'table'            => $this->getTable(),
         'field'            => 'is_active',
         'name'             => __('Active'),
         'datatype'         => 'bool',
         'checktype'        => 'bool',
         'displaytype'      => 'bool',
         'injectable'       => true,
      ];

      $tab[] = [
         'id'               => 16,
         'table'            => $this->getTable(),
         'field'            => 'comment',
         'name'             => __('Comments'),
         'datatype'         => 'text',
         'checktype'        => 'text',
         'displaytype'      => 'multiline_text',
         'injectable'       => true,
      ];

      $tab[] = [
         'id'               => 26,
         'table'            => 'glpi_groups',
         'field'            => 'name',
         'name'             => __('Level 1', 'itilcategorygroups'),
         'forcegroupby'     => true,
         'joinparams'       => [
            'beforejoin' => [
               'table'      => 'glpi_plugin_itilcategorygroups_categories_groups',
               'joinparams' => [
                  'condition'  => 'AND NEWTABLE.level = 1',
                  'jointype'   => 'child',
                  'beforejoin' => [
                     'table'      => 'glpi_plugin_itilcategorygroups_categories',
                     'joinparams' => [
                        'jointype'  => 'child'
                     ]
                  ]
               ]
            ]
         ],
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 27,
         'table'            => 'glpi_groups',
         'field'            => 'name',
         'name'             => __('Level 2', 'itilcategorygroups'),
         'forcegroupby'     => true,
         'joinparams'       => [
            'beforejoin' => [
               'table'      => 'glpi_plugin_itilcategorygroups_categories_groups',
               'joinparams' => [
                  'condition'  => 'AND NEWTABLE.level = 2',
                  'jointype'   => 'child',
                  'beforejoin' => [
                     'table'      => 'glpi_plugin_itilcategorygroups_categories',
                     'joinparams' => [
                        'jointype'  => 'child'
                     ]
                  ]
               ]
            ]
         ],
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 28,
         'table'            => 'glpi_groups',
         'field'            => 'name',
         'name'             => __('Level 3', 'itilcategorygroups'),
         'forcegroupby'     => true,
         'joinparams'       => [
            'beforejoin' => [
               'table'      => 'glpi_plugin_itilcategorygroups_categories_groups',
               'joinparams' => [
                  'condition'  => 'AND NEWTABLE.level = 3',
                  'jointype'   => 'child',
                  'beforejoin' => [
                     'table'      => 'glpi_plugin_itilcategorygroups_categories',
                     'joinparams' => [
                        'jointype'  => 'child'
                     ]
                  ]
               ]
            ]
         ],
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 29,
         'table'            => 'glpi_groups',
         'field'            => 'name',
         'name'             => __('Level 4', 'itilcategorygroups'),
         'forcegroupby'     => true,
         'joinparams'       => [
            'beforejoin' => [
               'table'      => 'glpi_plugin_itilcategorygroups_categories_groups',
               'joinparams' => [
                  'condition'  => 'AND NEWTABLE.level = 4',
                  'jointype'   => 'child',
                  'beforejoin' => [
                     'table'      => 'glpi_plugin_itilcategorygroups_categories',
                     'joinparams' => [
                        'jointype'  => 'child'
                     ]
                  ]
               ]
            ]
         ],
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 30,
         'table'            => $this->getTable(),
         'field'            => 'id',
         'name'             => __('ID'),
         'injectable'       => false,
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 35,
         'table'            => $this->getTable(),
         'field'            => 'date_mod',
         'name'             => __('Last update'),
         'datatype'         => 'datetime',
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 80,
         'table'            => 'glpi_entities',
         'field'            => 'completename',
         'name'             => __('Entity'),
         'injectable'       => false,
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 86,
         'table'            => $this->getTable(),
         'field'            => 'is_recursive',
         'name'             => __('Child entities'),
         'datatype'         => 'bool',
         'checktype'        => 'bool',
         'displaytype'      => 'bool',
         'injectable'       => true,
      ];

      return $tab;
   }

   //----------------------------- Install process --------------------------//
   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);

      if ($DB->tableExists("glpi_plugin_itilcategorygroups_categories_groups")
          && $DB->fieldExists("glpi_plugin_itilcategorygroups_categories_groups", 'is_active')) {
         $migration->renameTable("glpi_plugin_itilcategorygroups_categories_groups", $table);
      }

      if (!$DB->tableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
         `id` INT(11) NOT NULL AUTO_INCREMENT,
         `is_active` TINYINT(1) NOT NULL DEFAULT '0',
         `name` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '',
         `comment` TEXT COLLATE utf8_unicode_ci,
         `date_mod` DATE default NULL,
         `itilcategories_id` INT(11) NOT NULL DEFAULT '0',
         `view_all_lvl1` TINYINT(1) NOT NULL DEFAULT '0',
         `view_all_lvl2` TINYINT(1) NOT NULL DEFAULT '0',
         `view_all_lvl3` TINYINT(1) NOT NULL DEFAULT '0',
         `view_all_lvl4` TINYINT(1) NOT NULL DEFAULT '0',
         `entities_id` INT(11) NOT NULL DEFAULT '0',
         `is_recursive` TINYINT(1) NOT NULL DEFAULT '1',
         `is_incident` TINYINT(1) NOT NULL DEFAULT '1',
         `is_request` TINYINT(1) NOT NULL DEFAULT '1',
         `is_groups_restriction` TINYINT(1) NOT NULL DEFAULT '0',
         PRIMARY KEY (`id`),
         KEY `entities_id` (`entities_id`),
         KEY `itilcategories_id` (`itilcategories_id`),
         KEY `is_incident` (`is_incident`),
         KEY `is_request` (`is_request`),
         KEY `is_recursive` (`is_recursive`),
         KEY date_mod (date_mod)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
         $DB->query($query);
      }

      if (!$DB->fieldExists($table, 'view_all_lvl1')) {
         $migration->addField($table, 'view_all_lvl1', "TINYINT(1) NOT NULL DEFAULT '0'",
                              ['after' => 'itilcategories_id']);
         $migration->addField($table, 'view_all_lvl2', "TINYINT(1) NOT NULL DEFAULT '0'",
                              ['after' => 'itilcategories_id']);
         $migration->addField($table, 'view_all_lvl3', "TINYINT(1) NOT NULL DEFAULT '0'",
                              ['after' => 'itilcategories_id']);
         $migration->addField($table, 'view_all_lvl4', "TINYINT(1) NOT NULL DEFAULT '0'",
                              ['after' => 'itilcategories_id']);
         $migration->migrationOneTable($table);
      }

      if (!$DB->fieldExists($table, 'is_groups_restriction')) {
         $migration->addField($table, 'is_groups_restriction', "TINYINT(1) NOT NULL DEFAULT '0'",
                              ['after' => 'itilcategories_id']);
         $migration->migrationOneTable($table);
      }

      return true;
   }

   static function uninstall() {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      $DB->query("DROP TABLE IF EXISTS`$table`");
      return true;
   }

   static function getIcon() {
      return "fas fa-users";
   }
}

