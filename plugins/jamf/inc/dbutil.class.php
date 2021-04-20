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
 * DB utilities for Jamf plugin.
 * Contains several methods not yet available in the core for interacting with the DB and tables.
 */
 class PluginJamfDBUtil {

    public static function dropTable(string $table)
    {
       global $DB;

       return $DB->query('DROP TABLE'.$DB::quoteName($table));
    }

    public static function dropTableOrDie(string $table, string $message = '')
    {
        global $DB;

        if (!$DB->tableExists($table)) {
           return true;
        }
        $res = $DB->query('DROP TABLE'.$DB::quoteName($table));
        if (!$res) {
           //TRANS: %1$s is the description, %2$s is the query, %3$s is the error message
            $message = sprintf(
                _x('error', '%1$s - Error during the drop of the table %2$s - Error is %3$s', 'jamf'),
                $message,
                $table,
                $DB->error()
            );
            if (isCommandLine()) {
                throw new \RuntimeException($message);
            }

           echo $message . "\n";
           die(1);
        }
        return $res;
    }
 }