<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include ('../../../inc/includes.php');

Session::checkLoginUser();

$plugin = new Plugin();

$pref = new PluginActivityPreference();

if (isset($_POST["add"])) {
   if ($pref->canCreate() && isset($_POST['users_id'])) {
      $pref->add($_POST);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   if ($pref->canCreate()) {
      $pref->delete(['id' => $_POST["id"]]);
   }
   Html::back();

} else {

   Html::redirect(Toolbox::getItemTypeSearchURL("Preference"));

}