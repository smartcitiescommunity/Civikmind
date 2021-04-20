<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::checkRight('plugin_mantis_use', UPDATE);

if (isset($_POST['update'])) {

   if (isset($_POST['followFollow'])) {
      $_POST['followFollow'] = 1;
   } else {
      $_POST['followFollow'] = 0;
   }

   if (isset($_POST['followTitle'])) {
      $_POST['followTitle'] = 1;
   } else {
      $_POST['followTitle'] = 0;
   }

   if (isset($_POST['followTask'])) {
      $_POST['followTask'] = 1;
   } else {
      $_POST['followTask'] = 0;
   }

   if (isset($_POST['followCategorie'])) {
      $_POST['followCategorie'] = 1;
   } else {
      $_POST['followCategorie'] = 0;
   }

   if (isset($_POST['followDescription'])) {
      $_POST['followDescription'] = 1;
   } else {
      $_POST['followDescription'] = 0;
   }

   if (isset($_POST['followLinkedItem'])) {
      $_POST['followLinkedItem'] = 1;
   } else {
      $_POST['followLinkedItem'] = 0;
   }

   if (isset($_POST['followAttachment'])) {
      $_POST['followAttachment'] = 1;
   } else {
      $_POST['followAttachment'] = 0;
   }

   $userpref = new PluginMantisUserpref();
   $userpref->update($_POST);
}

Html::back();
