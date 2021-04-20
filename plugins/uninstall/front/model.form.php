<?php
/*
 * @version $Id: model.form.php 148 2013-07-10 09:30:56Z yllen $
 LICENSE

 This file is part of the uninstall plugin.

 Uninstall plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Uninstall plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with uninstall. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   uninstall
 @author    the uninstall plugin team
 @copyright Copyright (c) 2010-2013 Uninstall plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/uninstall
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ('../../../inc/includes.php');

Session::checkSeveralRightsOr(['uninstall:profile' => READ,
                               'uninstall:profile' => PluginUninstallProfile::RIGHT_REPLACE]);

if (!isset ($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

if (isset ($_GET["id"])) {
   $id = $_GET["id"];
} else if (isset ($_POST["id"])) {
   $id = $_POST["id"];
} else {
   $id = -1;
}

$model = new PluginUninstallModel();

if (isset ($_POST["add"])) {
   $model->check(-1, UPDATE, $_POST);
   $model->add($_POST);
   Html::back();

} else if (isset ($_POST["update"])) {
   $model->check($_POST['id'], UPDATE);
   $model->update($_POST);
   Html::back();

} else if (isset($_POST['purge'])) {
   $model->check($_POST['id'], DELETE);
   $model->delete($_POST);
   $model->redirectToList();

} else {

   Html::header(PluginUninstallModel::getTypeName(), $_SERVER['PHP_SELF'], "admin",
                "PluginUninstallModel", "model");

   if ($model->getFromDB($id)) {
      if ($model->fields['types_id'] == PluginUninstallModel::TYPE_MODEL_REPLACEMENT) {
         if (!Session::haveRight('uninstall:profile',
                                 PluginUninstallProfile::RIGHT_REPLACE)) {
            Html::displayRightError();
         }
      }
   }

   $model->display(['id'           => $id,
                    'withtemplate' => $_GET["withtemplate"]]);

   Html::footer();
}
