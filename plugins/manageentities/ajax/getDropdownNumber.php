<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

$used = [];

if (isset($_POST['used'])) {
   $used = $_POST['used'];
}

if (!isset($_POST['value'])) {
   $_POST['value'] = 0;
}

$one_item = -1;
if (isset($_POST['_one_id'])) {
   $one_item = $_POST['_one_id'];
}

if (!isset($_POST['page'])) {
   $_POST['page']       = 1;
   $_POST['page_limit'] = $CFG_GLPI['dropdown_max'];
}

if (isset($_POST['toadd'])) {
   $toadd = $_POST['toadd'];
} else {
   $toadd = [];
}

$datas = [];
// Count real items returned
$count = 0;

if ($_POST['page'] == 1) {
   if (count($toadd)) {
      foreach ($toadd as $key => $val) {
         if (($one_item < 0) || ($one_item == $key)) {
            array_push($datas, ['id'   => $key,
                                'text' => strval(stripslashes($val))]);
         }
      }
   }
}

$values = [];
if (!empty($_POST['searchText'])) {
   for ($i = $_POST['min']; $i <= $_POST['max']; $i += $_POST['step']) {
      if (strstr($i, $_POST['searchText'])) {
         $values[$i] = $i;
      }
   }
} else {
   for ($i = $_POST['min']; $i <= $_POST['max']; $i += $_POST['step']) {
      $values[] = $i;
   }
}
if ($one_item < 0 && count($values)) {
   $start  = ($_POST['page'] - 1) * $_POST['page_limit'];
   $tosend = array_splice($values, $start, $_POST['page_limit']);
   foreach ($tosend as $i) {
      $txt = $i;
      if (isset($_POST['unit'])) {
         $txt = Dropdown::getValueWithUnit($i, $_POST['unit']);
      }
      array_push($datas, ['id'   => $i,
                          'text' => strval($txt)]);
      $count++;
   }

} else {
   if (!isset($toadd[$one_item])) {
      if (isset($_POST['unit'])) {
         $txt = Dropdown::getValueWithUnit($one_item, $_POST['unit']);
      }
      array_push($datas, ['id'   => $one_item,
                          'text' => strval(stripslashes($txt))]);
      $count++;
   }
}

if (($one_item >= 0)
    && isset($datas[0])) {
   echo json_encode($datas[0]);
} else {
   $ret['results'] = $datas;
   $ret['count']   = $count;
   echo json_encode($ret);
}