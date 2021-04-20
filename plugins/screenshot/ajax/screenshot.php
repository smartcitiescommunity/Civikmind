<?php
/*
 -------------------------------------------------------------------------
 Screenshot
 Copyright (C) 2020-2021 by Curtis Conard
 https://github.com/cconard96/glpi-screenshot-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Screenshot.
 Screenshot is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Screenshot is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Screenshot. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$plugin = new Plugin();
if (!$plugin->isActivated('screenshot')) {
   Html::displayNotFoundError();
}

Html::header_nocache();

Session::checkLoginUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   // Bad request method
   die(405);
}
if (!isset($_POST['itemtype'], $_POST['items_id'], $_POST['format'])) {
   if (!isset($_POST['img']) || !isset($_FILES['blob'])) {
      // Missing required data
      die(400);
   }
}

$config = Config::getConfigurationValues('plugin:screenshot');
if ((isset($_POST['img']) && $_POST['format'] !== $config['screenshot_format']) ||
   (isset($_FILES['blob']) && $_POST['format'] !== 'video/webm')) {
   die(400);
}

$ext = PluginScreenshotScreenshot::getExtensionForMime($_POST['format']);

$filename_timestamp = str_replace(':', '-', $_SESSION['glpi_currenttime']);

if (isset($_POST['img'])) {
   // Handle screenshot upload

   // Name format: Screenshot + Timestamp + random 5 character hex + extension
   $file_name = 'Screenshot ' . $filename_timestamp . ' ' . sprintf('%05X', random_int(0, 1048575)) . '.' . $ext;
   if (!Document::isValidDoc($file_name)) {
      Session::addMessageAfterRedirect('Unauthorized file type', false, ERROR);
      die(403);
   }

   $data = file_get_contents($_POST['img']);
   // Save image to tmp
   if (!is_writable(GLPI_TMP_DIR)) {
      Session::addMessageAfterRedirect('GLPI temp folder is not writable', false, ERROR);
      die(400);
   }
   $bytes_written = file_put_contents(GLPI_TMP_DIR . '/' . $file_name, $data);
   if ($bytes_written === false) {
      Session::addMessageAfterRedirect('Screenshot upload failed', false, ERROR);
      die(400);
   }

   // Add document and link. Adding the document will cleanup the temp file for us.
   $doc = new Document();
   $doc_id = $doc->add([
      'name'         => $file_name,
      '_filename'    => [$file_name]
   ]);
   $doc_item = new Document_Item();
   $doc_item->add([
      'documents_id' => $doc_id,
      'itemtype'     => $_POST['itemtype'],
      'items_id'     => $_POST['items_id'],
   ]);

// In case something fails and the temp file remains, remove it
   if (!empty($file_name) && file_exists(GLPI_TMP_DIR . '/' . $file_name)) {
      unlink(GLPI_TMP_DIR . '/' . $file_name);
   }
} else if (isset($_FILES['blob'])) {
   // Handle screen recording upload
   Session::checkRight('plugin_screenshot_recording', CREATE);

   // Name format: Screen Recording + Timestamp + random 5 character hex + extension
   $file_name = 'Screen Recording ' . $filename_timestamp . ' ' . sprintf('%05X', random_int(0, 1048575)) . '.' . $ext;
   if (!Document::isValidDoc($file_name)) {
      Session::addMessageAfterRedirect('Unauthorized file type', false, ERROR);
      die(403);
   }

   $data = $_FILES['blob'];

   if ($data['error'] === UPLOAD_ERR_OK) {
      move_uploaded_file($data['tmp_name'], GLPI_TMP_DIR . '/' . $file_name);

      // Add document and link. Adding the document will cleanup the temp file for us.
      $doc = new Document();
      $doc_id = $doc->add([
         'name'         => $file_name,
         '_filename'    => [$file_name]
      ]);
      $doc_item = new Document_Item();
      $doc_item->add([
         'documents_id' => $doc_id,
         'itemtype'     => $_POST['itemtype'],
         'items_id'     => $_POST['items_id'],
      ]);

      // In case something fails and the temp file remains, remove it
      if (file_exists($data['tmp_name'])) {
         unlink($data['tmp_name']);
      }
      if (file_exists(GLPI_TMP_DIR . '/' . $file_name)) {
         unlink(GLPI_TMP_DIR . '/' . $file_name);
      }
   }
}