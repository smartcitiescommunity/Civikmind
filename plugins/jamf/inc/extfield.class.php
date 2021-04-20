<?php

/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * PluginJamfExtField class. This represents an extra field for a GLPI item.
 * As an example, the Phone itemtype does not have a UUID field so one is added using this class/table.
 */
class PluginJamfExtField extends CommonDBTM {

    public static function getValue($itemtype, $items_id, $name) {
        $ext_field = new self();
        $match = $ext_field->find([
            'itemtype'  => $itemtype,
            'items_id'  => $items_id,
            'name'      => $name
        ], [], 1);
        if (count($match)) {
            return reset($match)['value'];
        }

       return '';
    }

    public static function setValue($itemtype, $items_id, $name, $value) {
        global $DB;

        $DB->updateOrInsert(self::getTable(), [
            'value' => $value
        ], [
            'itemtype'  => $itemtype,
            'items_id'  => $items_id,
            'name'      => $name
        ]);
    }
}