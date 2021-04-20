<?php

class PluginItilcategorygroupsGroup_Level extends CommonDBChild {

   // From CommonDBChild
   public static $itemtype = 'Group';
   public static $items_id = 'groups_id';

   static function getIndexName() {
      return self::$items_id;
   }

   static function getTypeName($nb = 0) {
      return __('Level attribution', 'itilcategorygroups');
   }

   static function canView() {
      return Session::haveRight('config', READ);
   }

   static function canCreate() {
      return Session::haveRight('config', CREATE);
   }

   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      return $DB->query("CREATE TABLE IF NOT EXISTS `$table` (
         `id`                int(11) NOT NULL auto_increment,
         `groups_id` int(11) NOT NULL,
         `lvl`             int(11) DEFAULT NULL,
         PRIMARY KEY (`id`),
         KEY         `groups_id` (`groups_id`),
         KEY         `lvl` (`lvl`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
   }

   static function uninstall() {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      return $DB->query("DROP TABLE IF EXISTS `$table`");
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (! $withtemplate) {
         switch ($item->getType()) {
            case 'Group' :
               return __('ItilCategory Groups', 'itilcategorygroups');
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Group') {
         self::showForGroup($item);
      }
      return true;
   }

   static function showForGroup(Group $group) {
      global $DB;

      $ID = $group->getField('id');
      if (! $group->can($ID, READ)) {
         return false;
      }

      $canedit = $group->can($ID, UPDATE);
      // Get data
      $item = new self();
      if (!$item->getFromDB($ID)) {
         $item->getEmpty();
      }

      $rand = mt_rand();
      echo "<form name='group_level_form$rand' id='group_level_form$rand' method='post'
             action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<input type='hidden' name='".self::$items_id."' value='$ID' />";

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'><th>".__('Level attribution', 'itilcategorygroups')."</th></tr>";

      echo "<tr class='tab_bg_2'><td class='center'>";
      Dropdown::showFromArray('lvl',
                              [null => "---",
                               1    => __('Level 1', 'itilcategorygroups'),
                               2    => __('Level 2', 'itilcategorygroups'),
                               3    => __('Level 3', 'itilcategorygroups'),
                               4    => __('Level 4', 'itilcategorygroups')],
                              ['value' => $item->fields['lvl']]);
      echo "</td></tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'><td class='center'>";
         if ($item->fields["id"]) {
            echo "<input type='hidden' name='id' value='".$item->fields["id"]."'>";
            echo "<input type='submit' name='update' value=\"".__('Save')."\"
                   class='submit'>";
         } else {
            echo "<input type='submit' name='add' value=\"".__('Save')."\" class='submit'>";
         }
         echo "</td></tr>";
      }
      echo "</table></div>";
      Html::closeForm();
   }

   static function getAddSearchOptions($itemtype) {

      $opt = [];

      if ($itemtype == 'Group') {
         $opt[9978]['table']      = getTableForItemType(__CLASS__);
         $opt[9978]['field']      = 'lvl';
         $opt[9978]['name']       = __('Level attribution', 'itilcategorygroups');
         $opt[9978]['linkfield']  = 'lvl';
         $opt[9978]['joinparams'] = ['jointype' => 'child'];
      }

      return $opt;
   }

   static function getAllGroupForALevel($level, $entities_id = -1) {
      global $DB;

      if ($entities_id === -1) {
         $entities_id = $_SESSION['glpiactive_entity'];
      }

      $groups_id = [];
      $query = "SELECT gl.groups_id
                FROM ".getTableForItemType(__CLASS__)." gl
                LEFT JOIN glpi_groups gr
                    ON gl.groups_id = gr.id
                WHERE gl.lvl = $level".
                getEntitiesRestrictRequest(" AND ", "gr", 'entities_id',
                                           $entities_id, true);
      foreach ($DB->request($query) as $data) {
         $groups_id[] = $data['groups_id'];
      }
      return $groups_id;
   }
}