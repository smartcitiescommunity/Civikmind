<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');
header('Content-Type: application/json');
Html::header_nocache();

Session::checkLoginUser();

if (isset($_GET['page']) && isset($_GET['file'])) {

   $pluginResourcesImportResource = new PluginResourcesImportResource();

   $absoluteFilePath = $pluginResourcesImportResource::getLocationOfVerificationFiles() . "/" . $_GET['file'];

   $temp = $pluginResourcesImportResource->readCSVLines($absoluteFilePath, 0, 1);
   $header = array_shift($temp);

   $importId = $pluginResourcesImportResource->checkHeader($header);

   $listParams = $pluginResourcesImportResource->fillVerifyParams(
      1,
      INF,
      $_GET['page'],
      $absoluteFilePath,
      $importId,
      $_GET['file'],
      $pluginResourcesImportResource::DISPLAY_STATISTICS,
      true
   );

   switch ($_GET['page']) {
      case PluginResourcesImportResource::VERIFY_FILE:
         $pluginResourcesImportResource->showVerificationFileList($listParams);
         break;
      case PluginResourcesImportResource::VERIFY_GLPI:
         $pluginResourcesImportResource->showVerificationGLPIFromFileList($listParams);
         break;
   }
}