<?php

/*
 * -------------------------------------------------------------------------
 * JAMF plugin for GLPI
 * Copyright (C) 2019-2020 by Curtis Conard
 * https://github.com/cconard96/jamf
 * -------------------------------------------------------------------------
 * LICENSE
 * This file is part of JAMF plugin for GLPI.
 * JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * JAMF plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

/**
 * JSS Extension Attribute class
 *
 * @since 1.1.0
 */
class PluginJamfExtensionAttribute extends CommonDBTM {

    public static function getTypeName($nb = 1)
    {
       return _nx('itemtype', 'Extension attribute', 'Extension attributes', $nb, 'jamf');
    }

    public function addOrUpdate($input)
    {
       global $DB;

       if (!isset($input['jamf_id'])) {
          return false;
       }
       $jamf_id = $input['jamf_id'];
       unset($input['jamf_id']);
       return $DB->updateOrInsert(self::getTable(), $input, ['jamf_id' => $jamf_id]);
    }

    public static function dashboardCards()
    {
       global $DB;

       $table = self::getTable();
       $iterator = $DB->request([
          'SELECT'   => ['name'],
          'FROM'  => $table
       ]);
       $cards = [];

       while ($data = $iterator->next()) {
          $slug = strtolower(str_replace(' ', '_', $data['name']));
          $cards["plugin_jamf_extensionattribute_{$slug}"] = [
             'widgettype'  => ['halfdonut'],
             'label'       => sprintf(_x('dashboard', 'Jamf Attribute - %s', 'jamf'), $data['name']),
             'provider'    => 'PluginJamfExtensionAttribute::cardProvider',
             'args'        => ['name' => $data['name']]
          ];
       }

       return $cards;
    }

    public static function cardProvider($name, array $params = [])
    {
       global $DB;

       $rel_table = PluginJamfItem_ExtensionAttribute::getTable();
       $table = self::getTable();
       $iterator = $DB->request([
          'SELECT'   => [
             'value',
             'COUNT' => "{$rel_table}.id as cpt"
          ],
          'FROM'  => $table,
          'JOIN'  => [
             $rel_table => [
                'ON' => [
                   $rel_table => 'glpi_plugin_jamf_extensionattributes_id',
                   $table     => 'id'
                ]
             ]
          ],
          'WHERE' => ['name' => $name],
          'GROUP' => "{$rel_table}.value"
       ]);

       $card_data = [];
       while ($data = $iterator->next()) {
          $card_data[] = [
             'label'    => $data['value'],
             'number'   => $data['cpt'],
             'url'      => '#'
          ];
       }
       return [
          'label' => sprintf(_x('dashboard', 'Jamf Attribute - %s', 'jamf'), $name),
          'data'  => $card_data
       ];
    }
}