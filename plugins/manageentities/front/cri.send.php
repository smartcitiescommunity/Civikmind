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

if (isset($_GET["file"])) { // for other file
   $splitter = explode("/", $_GET["file"]);

   if (count($splitter) == 3) {

      if (file_exists(GLPI_DOC_DIR . "/" . $_GET["file"])) {
         if (!isset($_GET["seefile"])) {
            Toolbox::sendFile(GLPI_DOC_DIR . "/" . $_GET["file"], $splitter[2]);
         } else {
            $doc                     = new Document();
            $doc->fields['filepath'] = $_GET["file"];
            $doc->fields['mime']     = 'application/pdf';
            $doc->fields['filename'] = $splitter[2];

            //Document send method that has changed.
            //Because of : document.class.php
            //if (!in_array($extension, array('jpg', 'png', 'gif', 'bmp'))) {
            //   $attachment = " attachment;";
            //}
            $cri = new PluginManageentitiesCri();
            $cri->send($doc);
         }
      } else {
         Html::displayErrorAndDie(__('Unauthorized access to this file'), true);
      }
   } else {
      Html::displayErrorAndDie(__('Invalid filename'), true);
   }
}