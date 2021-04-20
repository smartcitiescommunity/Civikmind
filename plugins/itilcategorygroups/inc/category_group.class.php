<?php

class PluginItilcategorygroupsCategory_Group extends CommonDBChild {
   static public $itemtype = "PluginItilcategorygroupsCategory";
   static public $items_id = "plugin_itilcategorygroups_categories_id";

   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!$DB->tableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
         `id`                                      INT(11)    NOT NULL AUTO_INCREMENT,
         `plugin_itilcategorygroups_categories_id` INT(11)    NOT NULL DEFAULT '0',
         `level`                                   TINYINT(1) NOT NULL DEFAULT '0',
         `itilcategories_id`                       INT(11)    NOT NULL DEFAULT '0',
         `groups_id`                               INT(11)    NOT NULL DEFAULT '0',
         PRIMARY KEY (`id`),
         UNIQUE KEY `group_lvl_unicity` (plugin_itilcategorygroups_categories_id, level, groups_id),
         KEY `plugin_itilcategorygroups_categories_id` (`plugin_itilcategorygroups_categories_id`),
         KEY `level`                                   (`level`),
         KEY `itilcategories_id`                       (`itilcategories_id`),
         KEY `groups_id`                               (`groups_id`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
         $DB->query($query);
      }

      $parent_table = "glpi_plugin_itilcategorygroups_categories";

      //we must migrate groups datas in sub table
      if ($DB->fieldExists($parent_table, 'groups_id_levelone')) {
         $all_lvl = $cat_groups = [];

         //foreach old levels
         foreach ([1=>'one', 2=>'two', 3=>'three', 4=>'four'] as $lvl_num => $lvl_str) {
            $query = "SELECT id, itilcategories_id, groups_id_level$lvl_str FROM $parent_table";
            $res = $DB->query($query);
            while ($data = $DB->fetchAssoc($res)) {
               //specific case (all group of this lvl), store it for further treatment
               if ($data["groups_id_level$lvl_str"] == -1) {
                  $all_lvl[$data['itilcategories_id']][$lvl_num] = $lvl_str;
               }

               if ($data["groups_id_level$lvl_str"] > 0) {
                  $cat_groups[] = [
                     'plugin_itilcategorygroups_categories_id' => $data['id'],
                     'level'                                   => $lvl_num,
                     'itilcategories_id'                       => $data['itilcategories_id'],
                     'groups_id'                               => $data["groups_id_level$lvl_str"]];
               }
            }

            //insert "all groups for this lvl'
            foreach ($all_lvl as $itilcategories_id => $lvl) {
               foreach ($lvl as $lvl_num => $lvl_str) {
                  $DB->query("UPDATE $parent_table SET view_all_lvl$lvl_num = 1
                              WHERE itilcategories_id = $itilcategories_id");
               }
            }

            //insert groups in sub table
            foreach ($cat_groups as $cat_groups_data) {
               $DB->query("REPLACE INTO glpi_plugin_itilcategorygroups_categories_groups
                              (plugin_itilcategorygroups_categories_id,
                               level,
                               itilcategories_id,
                               groups_id)
                           VALUES (
                              ".$cat_groups_data['plugin_itilcategorygroups_categories_id'].",
                              ".$cat_groups_data['level'].",
                              ".$cat_groups_data['itilcategories_id'].",
                              ".$cat_groups_data['groups_id']."
                           )");
            }
         }

         //drop migrated fields
         $migration->dropField($parent_table, "groups_id_levelone");
         $migration->dropField($parent_table, "groups_id_leveltwo");
         $migration->dropField($parent_table, "groups_id_levelthree");
         $migration->dropField($parent_table, "groups_id_levelfour");
         $migration->migrationOneTable($parent_table);
      }

      return true;
   }

   static function uninstall() {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      $DB->query("DROP TABLE IF EXISTS`$table`");
      return true;
   }
}
