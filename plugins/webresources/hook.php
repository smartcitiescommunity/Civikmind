<?php

/*
 -------------------------------------------------------------------------
 Web Resources Plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/glpi-webresources-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Web Resources Plugin for GLPI.
 Web Resources Plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Web Resources Plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Web Resources Plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_webresources_install()
{
   global $DB;

   $res_table = PluginWebresourcesResource::getTable();
   $res_entity_table = PluginWebresourcesResource_Entity::getTable();
   $res_profile_table = PluginWebresourcesResource_Profile::getTable();
   $res_group_table = PluginWebresourcesResource_Group::getTable();
   $res_user_table = PluginWebresourcesResource_User::getTable();
   $clean_install = false;

   if (!$DB->tableExists($res_table)) {
      $query = "CREATE TABLE `{$res_table}` (
                  `id` int(11) NOT NULL auto_increment,
                  `users_id` int(11) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `link` varchar(255) NOT NULL,
                  `icon` varchar(255) DEFAULT NULL,
                  `color` varchar(16) NOT NULL DEFAULT '#000000',
                  `plugin_webresources_categories_id` int(11) DEFAULT 0,
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, 'Error creating Web Resource table' . $DB->error());
      $clean_install = true;
   }
   if (!$DB->tableExists($res_entity_table)) {
      $query = "CREATE TABLE `{$res_entity_table}` (
                  `id` int(11) NOT NULL auto_increment,
                  `plugin_webresources_resources_id` int(11) NOT NULL,
                  `entities_id` int(11) NOT NULL,
                  `is_recursive` tinyint(1) DEFAULT 0,
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, 'Error creating Web Resource Entity table' . $DB->error());
   }
   if (!$DB->tableExists($res_profile_table)) {
      $query = "CREATE TABLE `{$res_profile_table}` (
                  `id` int(11) NOT NULL auto_increment,
                  `plugin_webresources_resources_id` int(11) NOT NULL,
                  `profiles_id` int(11) NOT NULL,
                  `entities_id` int(11) NOT NULL,
                  `is_recursive` tinyint(1) DEFAULT 0,
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, 'Error creating Web Resource Profile table' . $DB->error());
   }
   if (!$DB->tableExists($res_group_table)) {
      $query = "CREATE TABLE `{$res_group_table}` (
                  `id` int(11) NOT NULL auto_increment,
                  `plugin_webresources_resources_id` int(11) NOT NULL,
                  `groups_id` int(11) NOT NULL,
                  `entities_id` int(11) NOT NULL,
                  `is_recursive` tinyint(1) DEFAULT 0,
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, 'Error creating Web Resource Group table' . $DB->error());
   }
   if (!$DB->tableExists($res_user_table)) {
      $query = "CREATE TABLE `{$res_user_table}` (
                  `id` int(11) NOT NULL auto_increment,
                  `plugin_webresources_resources_id` int(11) NOT NULL,
                  `users_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, 'Error creating Web Resource User table' . $DB->error());
   }

   $cat_table = PluginWebresourcesCategory::getTable();
   if (!$DB->tableExists($cat_table)) {
      $query = "CREATE TABLE `{$cat_table}` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) NOT NULL,
                  `comment` TEXT DEFAULT NULL,
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, 'Error creating Web Resource Category table' . $DB->error());
   }

   if (!$DB->tableExists('glpi_plugin_webresources_autoicons')) {
      $query = "CREATE TABLE `glpi_plugin_webresources_autoicons` (
                  `itemtype` varchar(100) NOT NULL,
                  `items_id` int(11) NOT NULL,
                  `icon` varchar(255) DEFAULT NULL,
                  `color` varchar(16) NOT NULL DEFAULT '#000000',
                PRIMARY KEY (`itemtype`, `items_id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, 'Error creating Web Resource auto-icon table' . $DB->error());
   }

   if (!count(Config::getConfigurationValues('plugin:Webresources'))) {
      Config::setConfigurationValues('plugin:Webresources', [
         'config_class'    => PluginWebresourcesConfig::class,
         'use_duckduckgo'  => 0,
         'use_google'      => 0,
      ]);
   }

   $migration = new Migration(PLUGIN_WEBRESOURCES_VERSION);
   if ($clean_install) {
      $migration->addRight(PluginWebresourcesResource::$rightname);
   }
   $migration->executeMigration();
	return true;
}

function plugin_webresources_uninstall()
{
   global $DB;

   $tables = [PluginWebresourcesResource::getTable(), PluginWebresourcesResource_Entity::getTable(),
      PluginWebresourcesResource_Profile::getTable(), PluginWebresourcesResource_Group::getTable(),
      PluginWebresourcesResource_User::getTable(), PluginWebresourcesCategory::getTable(), 'glpi_plugin_webresources_autoicons'];

   foreach ($tables as $table) {
      if ($DB->tableExists($table)) {
         $DB->queryOrDie('DROP TABLE'.$DB::quoteName($table));
      }
   }
   Config::deleteConfigurationValues('plugin:Webresources', [
      'config_class',
      'use_duckduckgo',
      'use_google'
   ]);
	return true;
}

function plugin_webresources_getDropdown() {
   return ['PluginWebresourcesCategory' => PluginWebresourcesCategory::getTypeName(2)];
}

function plugin_webresources_showPostItemForm(array $params)
{
   global $DB;

   static $supported_types = [Entity::class, Supplier::class];
   $item = $params['item'];
   if (in_array($item::getType(), $supported_types, true)) {
      if ($item::getType() === 'Entity' && $_REQUEST['_glpi_tab'] !== 'Entity$main') {
         return;
      }
      $iterator = $DB->request([
         'SELECT' => ['icon', 'color'],
         'FROM'   => 'glpi_plugin_webresources_autoicons',
         'WHERE'  => [
            'itemtype'  => $item::getType(),
            'items_id'  => $item->getID()
         ]
      ]);
      $ico = '';
      $color = '#000000';
      if (count($iterator)) {
         $data = $iterator->next();
         $ico = $data['icon'];
         $color = $data['color'];
      }
      $out = '<tr><td>'.__('Icon', 'webresources').'</td><td>';
      $out .= Html::input('webresources_icon', [
         'value'  => $ico
      ]);
      $out .= '</td><td>'.__('Icon color', 'webresources').'</td><td>';
      $out .= Html::showColorField('webresources_color', [
         'value'  => $color,
         'display'   => false
      ]);
      $out .= '</td></tr>';
      echo $out;
   }
}

function plugin_webresources_preupdateitem(CommonDBTM $item)
{
   global $DB;

   static $supported_types = [Entity::class, Supplier::class, Appliance::class];
   if (isset($item->input['webresources_icon']) && in_array($item::getType(), $supported_types, true)) {
      $DB->updateOrInsert('glpi_plugin_webresources_autoicons', [
         'itemtype'  => $item::getType(),
         'items_id'  => $item->getID(),
         'icon'      => $item->input['webresources_icon'],
         'color'     => $item->input['webresources_color']
      ], [
         'itemtype'  => $item::getType(),
         'items_id'  => $item->getID(),
      ]);
      unset($item->input['webresources_icon']);
      unset($item->input['webresources_color']);
   }
}

function plugin_webresources_preItemPurge(CommonDBTM $item)
{
   global $DB;

   static $supported_types = [Entity::class, Supplier::class, Appliance::class];
   if (in_array($item::getType(), $supported_types, true)) {
      $DB->delete('glpi_plugin_webresources_autoicons', [
         'itemtype'  => $item::getType(),
         'items_id'  => $item->getID(),
      ]);
   }
}