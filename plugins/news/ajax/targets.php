<?php
/*
 *
 -------------------------------------------------------------------------
 Plugin GLPI News
 Copyright (C) 2015 by teclib.
 http://www.teclib.com
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Plugin GLPI News.
 Plugin GLPI News is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Plugin GLPI News is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Plugin GLPI News. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

$AJAX_INCLUDE = 1;
include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_POST['type']) && !empty($_POST['type'])) {
   echo "<table class='tab_format'>";
   echo "<tr>";
   echo "<td>";
   switch ($_POST['type']) {
      case 'User' :
         User::dropdown(['name'        => 'items_id',
                         'right'       => 'all',
                         'entity'      => $_POST['entities_id'],
                         'entity_sons' => $_POST['is_recursive'],]);
         break;

      case 'Group' :
         Group::dropdown(['name' => 'items_id']);
         break;

      case 'Profile' :
         Profile::dropdown(['name'  => 'items_id',
                            'toadd' => [-1 => __('All')]]);
         break;
   }
   echo "</td>";
   echo "<td><input type='submit' name='addvisibility' value=\""._sx('button', 'Add')."\"
                   class='submit'></td>";
   echo "</tr>";
   echo "</table>";
}