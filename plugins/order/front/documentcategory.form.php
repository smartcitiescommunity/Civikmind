<?php
/*
 -------------------------------------------------------------------------
 Order plugin for GLPI
Copyright (C) 2013 by the Order Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Order.

 Order is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include ('../../../inc/includes.php');

$documentCategory = new PluginOrderDocumentCategory();

if (isset($_POST["update"])) {
   if (!$documentCategory->getFromDBByCrit(['documentcategories_id' => $_POST['documentcategories_id']])) {
      $documentCategory->add($_POST);
   } else {
      $_POST['id'] = $documentCategory->fields['id'];
      $documentCategory->update($_POST);
   }

   Html::back();
}
