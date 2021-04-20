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

function plugin_itilcategorygroups_install() {
   $dir = Plugin::getPhpDir('itilcategorygroups');

   $migration = new Migration("0.84");

   //order is important for install
   include_once($dir . "/inc/category.class.php");
   include_once($dir . "/inc/category_group.class.php");
   include_once($dir . "/inc/group_level.class.php");
   PluginItilcategorygroupsCategory::install($migration);
   PluginItilcategorygroupsCategory_Group::install($migration);
   PluginItilcategorygroupsGroup_Level::install($migration);
   return true;
}

function plugin_itilcategorygroups_uninstall() {
   $dir = Plugin::getPhpDir('itilcategorygroups');

   include_once($dir . "/inc/category_group.class.php");
   include_once($dir . "/inc/category.class.php");
   include_once($dir . "/inc/group_level.class.php");
   PluginItilcategorygroupsCategory_Group::uninstall();
   PluginItilcategorygroupsCategory::uninstall();
   PluginItilcategorygroupsGroup_Level::uninstall();
   return true;
}

function plugin_itilcategorygroups_getAddSearchOptions($itemtype) {
   if (isset($_SESSION['glpiactiveentities'])) {
      $options = PluginItilcategorygroupsGroup_Level::getAddSearchOptions($itemtype);
      return $options;
   } else {
      return null;
   }
}

function plugin_itilcategorygroups_giveItem($type, $ID, $data, $num) {

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];
   $value = $data['raw']["ITEM_$num"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_itilcategorygroups_groups_levels.lvl" :
         switch ($value) {
            case 1:
            case 2:
            case 3:
            case 4:
               return __('Level '.$value, 'itilcategorygroups');
         }
   }
   return "";
}

// Display specific massive actions for plugin fields
function plugin_itilcategorygroups_MassiveActionsFieldsDisplay($options = []) {

   $table     = $options['options']['table'];
   $field     = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];

   // Table fields
   switch ($table.".".$field) {
      case "glpi_plugin_itilcategorygroups_groups_levels.lvl" :
         Dropdown::showFromArray('lvl',
                                 [null => "---",
                                  1    => __('Level 1', 'itilcategorygroups'),
                                  2    => __('Level 2', 'itilcategorygroups'),
                                  3    => __('Level 3', 'itilcategorygroups'),
                                  4    => __('Level 4', 'itilcategorygroups')]);
         return true;
   }

   // Need to return false on non display item
   return false;
}


// Hook done on update item case
function plugin_pre_item_update_itilcategorygroups($item) {
   if (isset($_REQUEST['massiveaction'])
       && isset($_REQUEST['lvl'])
       && $item instanceof Group) {
      $group_level = new PluginItilcategorygroupsGroup_Level();
      if (! $group_level->getFromDB($item->fields['id'])) {
         $group_level->add(['groups_id'=> $item->fields['id'],
                            'lvl'    => $_REQUEST['lvl']]);
      } else {
         $group_level->update(['groups_id'=> $item->fields['id'],
                               'lvl'    => $_REQUEST['lvl']]);
      }

   }
   return $item;
}
