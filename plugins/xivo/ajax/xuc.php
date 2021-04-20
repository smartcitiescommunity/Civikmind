<?php
/*
 -------------------------------------------------------------------------
 xivo plugin for GLPI
 Copyright (C) 2017 by the xivo Development Team.

 https://github.com/pluginsGLPI/xivo
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivo.

 xivo is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 xivo is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with xivo. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

if (!isset($_REQUEST['action'])) {
   exit;
}

$xuc = new PluginXivoXuc;
switch ($_REQUEST['action']) {
   case 'get_login_form':
      echo $xuc->getLoginForm();
      break;

   case 'get_logged_form':
      echo $xuc->getLoggedForm();
      break;

   case 'get_user_infos_by_phone':
      $data = $xuc->getUserInfosByPhone($_REQUEST);
      echo json_encode($data);
      break;

   case 'get_call_link':
      $data = [];
      if (isset($_REQUEST['id'])) {
         $data = $xuc->getCallLink((int) $_REQUEST['id']);
         echo json_encode($data);
      }
      break;
}
