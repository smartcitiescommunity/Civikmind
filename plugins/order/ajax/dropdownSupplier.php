<?php
/*
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (strpos($_SERVER['PHP_SELF'], "dropdownSupplier.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();

// Make a select box
if (isset($_POST["suppliers_id"])) {

   // Make a select box
   $query = "SELECT c.`id`, c.`name`, c.`firstname`
             FROM `glpi_contacts` c
             LEFT JOIN `glpi_contacts_suppliers` s ON (s.`contacts_id` = c.`id`)
             WHERE s.`suppliers_id` = '{$_POST['suppliers_id']}'
             ORDER BY c.`name`";
   $result = $DB->query($query);
   $number = $DB->numrows($result);

   $values = [0 => Dropdown::EMPTY_VALUE];
   if ($number) {
      while ($data = $DB->fetchAssoc($result)) {
         $values[$data['id']] = formatUserName('', '', $data['name'], $data['firstname']);
      }
   }
   Dropdown::showFromArray($_POST['fieldname'], $values);
}
