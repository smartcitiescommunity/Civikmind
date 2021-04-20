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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesImportResource
 */
class PluginResourcesImportResource extends CommonDBTM {

   const UPDATE_RESOURCES = 0;

   // Pages
   const VERIFY_FILE = 1;
   const VERIFY_GLPI = 2;
   const IDENTICAL = 0;
   const DIFFERENT = 1;

   // Status
   const NOT_IN_GLPI = 2;
   const BEFORE = 0;
   const AFTER = 1;

   // Orders
   const DEFAULT_LIMIT = 20;
   const FILE_READ_MAX_LINE = 50;
   const IMPORT_RECOVERY_LIMIT = 50;

   // Limitation
   const SELECTED_FILE_DROPDOWN_NAME = 'selected-file';
   // We read line by 50 iteration to don't use too much ram
   const SELECTED_IMPORT_DROPDOWN_NAME = 'selected-import';
   // Number of import that can be recovered from the database at ones
   const SESSION_IMPORT_ID = 'import-display-last-id';
   const SESSION_IMPORT_START = 'import-display-last-start';
   const FILE_IMPORTER = false;

   // Display types
   const DISPLAY_HTML = 0;
   const DISPLAY_STATISTICS = 1;
   const DISPLAY_CSV = 2;

   static $rightname = 'plugin_resources_importresources';
   static $keyInOtherTables = 'plugin_resources_importresources_id';
   static $currentStart;
   static $currentVerifiedFile;

   /**
    * @param $name
    *
    * @return array
    */
   static function cronInfo($name) {

      switch ($name) {
         case 'ResourceImport':
            return ['description' => __('Resource files imports', 'resources')];   // Optional
            break;
      }
      return [];
   }

   /**
    * Cron action
    *
    * @param  $task for log
    * @global $CFG_GLPI
    *
    * @global $DB
    */
   static function cronResourceImport($task = NULL) {

      $CronTask = new CronTask();
      if ($CronTask->getFromDBbyName(PluginResourcesImportResource::class, 'ResourceImport')) {
         if ($CronTask->fields['state'] == CronTask::STATE_DISABLE) {
            return 0;
         }
      } else {
         return 0;
      }

      $import = new self();
      return $import->importResourcesFromCSVFile($task);
   }

   static function getLocationOfVerificationFiles() {
      return GLPI_PLUGIN_DOC_DIR . '/resources/import/verify';
   }

   static function getResourceImportFormUrl() {
      return PluginResourcesResourceImport::getFormURL(true);
   }

   static function getIndexUrl() {
      global $CFG_GLPI;
      return $CFG_GLPI['root_doc'] . '/plugins/resources/front/importresource.php';
   }

   /**
    * Copy of html::showDateFieldWithoutDiv
    *
    * Underscore removed from name
    * Change self reference to Html
    *
    **/
   static function showDateFieldWithoutDiv($name, $options = []) {
      $p['value'] = '';
      $p['maybeempty'] = true;
      $p['canedit'] = true;
      $p['min'] = '';
      $p['max'] = '';
      $p['showyear'] = true;
      $p['display'] = true;
      $p['rand'] = mt_rand();
      $p['yearrange'] = '';

      foreach ($options as $key => $val) {
         if (isset($p[$key])) {
            $p[$key] = $val;
         }
      }
      $output = "<input id='showdate" . $p['rand'] . "' type='text' size='10' name='$name' " . "value='" . Html::convDate($p['value']) . "'>";
      $output .= Html::hidden($name, ['value' => $p['value'], 'id' => "hiddendate" . $p['rand']]);
      if ($p['maybeempty'] && $p['canedit']) {
         $output .= "<span class='fas fa-times-circle pointer' title='" . __s('Clear') . "' id='resetdate" . $p['rand'] . "'>" . "<span class='sr-only'>" . __('Clear') . "</span></span>";
      }

      $js = '$(function(){';
      if ($p['maybeempty'] && $p['canedit']) {
         $js .= "$('#resetdate" . $p['rand'] . "').click(function(){
                  $('#showdate" . $p['rand'] . "').val('');
                  $('#hiddendate" . $p['rand'] . "').val('');
                  });";
      }
      $js .= "$( '#showdate" . $p['rand'] . "' ).datepicker({
                  altField: '#hiddendate" . $p['rand'] . "',
                  altFormat: 'yy-mm-dd',
                  firstDay: 1,
                  showOtherMonths: true,
                  selectOtherMonths: true,
                  showButtonPanel: true,
                  changeMonth: true,
                  changeYear: true,
                  showOn: 'both',
                  showWeek: true,
                  buttonText: '<i class=\'far fa-calendar-alt\'></i>'";

      if (!$p['canedit']) {
         $js .= ',disabled: true';
      }

      if (!empty($p['min'])) {
         $js .= ",minDate: '" . self::convDate($p['min']) . "'";
      }

      if (!empty($p['max'])) {
         $js .= ",maxDate: '" . self::convDate($p['max']) . "'";
      }

      if (!empty($p['yearrange'])) {
         $js .= ",yearRange: '" . $p['yearrange'] . "'";
      }

      switch ($_SESSION['glpidate_format']) {
         case 1 :
            $p['showyear'] ? $format = 'dd-mm-yy' : $format = 'dd-mm';
            break;

         case 2 :
            $p['showyear'] ? $format = 'mm-dd-yy' : $format = 'mm-dd';
            break;

         default :
            $p['showyear'] ? $format = 'yy-mm-dd' : $format = 'mm-dd';
      }
      $js .= ",dateFormat: '" . $format . "'";

      $js .= "}).next('.ui-datepicker-trigger').addClass('pointer');";
      $js .= "});";
      $output .= Html::scriptBlock($js);

      if ($p['display']) {
         echo $output;
         return $p['rand'];
      }
      return $output;
   }

   public function purgeDatabase() {
      global $DB;

      $query = "DELETE FROM `" . self::getTable() . "`";
      return $DB->query($query);
   }

   function importResourcesFromCSVFile($task) {
      // glpi files folder
      $path = GLPI_PLUGIN_DOC_DIR . '/resources/import/';
      // List of files in path
      $files = scandir($path);
      // Exclude dot and dotdot
      $files = array_diff($files, array('.', '..'));

      foreach ($files as $file) {

         $importSuccess = false;

         $filePath = $path . $file;

         // Ignore directories
         if (is_dir($filePath)) {
            continue;
         }

         if (file_exists($filePath)) {
            // Initialize existingImports Array
            // Used to prevent multiple get imports from database
            // Speed up execution time
            $this->resetExistingImportsArray();
            $this->initExistingImportsArray();

            $temp = $this->readCSVLines($filePath, 0, 1);
            $header = array_shift($temp);

            $importID = $this->checkHeader($header);

            if ($importID) {
               $lines = $this->readCSVLines($filePath, 1, INF);

               foreach ($lines as $line) {
                  $datas = $this->parseFileLine($header, $line, $importID);
                  $this->manageImport($datas, $importID);
               }
               $importSuccess = true;
            }
         }
         if ($importSuccess) {
            // Move file to done folder
            $output = $path . 'done/' . $file;
            rename(str_replace('\\', '/', $filePath), str_replace('\\', '/', $output));

         } else {
            // Move file to fail folder
            $output = $path . 'fail/' . $file;
            rename(str_replace('\\', '/', $filePath), str_replace('\\', '/', $output));
         }
      }

      return true;
   }

   /**
    * Insert or update imports
    *
    * @param $datas
    * @param $importID
    */
   function manageImport($datas, $importID) {

      $importResourceID = $this->isExistingImportResourceByDataFromFile($datas);

      // Override data of existing importResource
      if (!is_null($importResourceID)) {

         $this->updateDatas($datas, $importResourceID);

      } else {
         // Create new Import Resource
         $importResourceInput = [
            'date_creation' => date('Y-m-d H:i:s'),
            PluginResourcesImport::$keyInOtherTables => $importID
         ];

         $newImportId = $this->add($importResourceInput);

         $importResourceData = new PluginResourcesImportResourceData();

         // Create new Import resource data
         foreach ($datas as $item) {

            $importResourceDataInput = $importResourceData->prepareInput(
               addslashes($item['name']),
               addslashes($item['value']),
               $newImportId,
               $item['plugin_resources_importcolumns_id']
            );

            $importResourceData->add($importResourceDataInput);
         }
      }
   }

   /**
    * Search if a resource exist with the same identifiers
    *
    * @param $columnDatas
    * @return mixed|null
    */
   function isExistingImportResourceByDataFromFile($columnDatas) {

      $pluginResourcesImportResourceData = new PluginResourcesImportResourceData();

      // List of existing imports
      $this->initExistingImportsArray();

      foreach ($this->existingImports as $existingImportResource) {

         $firstLevelIdentifiers = $pluginResourcesImportResourceData->getFromParentAndIdentifierLevel($existingImportResource['id'], 1);

         $firstLevelIdentifierFounded = true;

         foreach ($firstLevelIdentifiers as $firstLevelIdentifier) {

            foreach ($columnDatas as $columnData) {

               if ($columnData['name'] != $firstLevelIdentifier['name']) {
                  continue;
               }

               if ($columnData['value'] != $firstLevelIdentifier['value']) {
                  $firstLevelIdentifierFounded = false;
                  break;
               }
            }
         }

         if ($firstLevelIdentifierFounded) {
            return $existingImportResource['id'];
         }

         $secondLevelIdentifiers = $pluginResourcesImportResourceData->getFromParentAndIdentifierLevel($existingImportResource['id'], 2);
         $secondLevelIdentifierFounded = true;

         foreach ($secondLevelIdentifiers as $secondLevelIdentifier) {

            foreach ($columnDatas as $columnData) {

               if ($columnData['name'] != $secondLevelIdentifier['name']) {
                  continue;
               }

               if ($columnData['value'] != $secondLevelIdentifier['value']) {
                  $secondLevelIdentifierFounded = false;
               }
            }
         }

         if ($secondLevelIdentifierFounded) {
            return $existingImportResource['id'];
         }
      }
      return null;
   }

   /**
    * Update child Import Resources Datas
    *
    * @param $datas
    * @param $importResourceID
    */
   function updateDatas($datas, $importResourceID) {

      $pluginResourcesImportResourceData = new PluginResourcesImportResourceData();

      $crit = [
         PluginResourcesImportResourceData::$items_id => $importResourceID
      ];

      $importResourceDatas = $pluginResourcesImportResourceData->find($crit);

      foreach ($importResourceDatas as $importResourceData) {

         foreach ($datas as $data) {

            if ($data['name'] != $importResourceData['name']) {
               continue;
            }

            if ($data['value'] == $importResourceData['value']) {
               continue;
            }

            $input = [
               PluginResourcesImportResourceData::getIndexName() => $importResourceData['id'],
               'value' => addslashes($data['value'])
            ];

            $pluginResourcesImportResourceData->update($input);
            break;
         }
      }
   }

   function importFileToVerify($params = []) {

      $filePath = GLPI_DOC_DIR . '/_tmp/' . $params['_filename'][0];

      $temp = $this->readCSVLines($filePath, 0, 1);
      $header = array_shift($temp);

      $importId = $this->checkHeader($header);

      // Verify file compatibility
      if (is_null($importId)) {
         return;
      }

      if (!document::moveDocument($params, $params['_filename'][0])) {
         die('ERROR WHEN MOVING FILE !');
      }
   }

   /**
    * Verify the header of the csv file
    *
    * Return the index of the configured import that match to this header
    *
    * @param $header
    * @return bool
    */
   function checkHeader(&$header) {

      $pluginResourcesImport = new PluginResourcesImport();
      $pluginResourcesImportColumn = new PluginResourcesImportColumn();

      $imports = $pluginResourcesImport->find();

      foreach ($imports as $import) {

         $columns = $pluginResourcesImportColumn->find([
            PluginResourcesImport::$keyInOtherTables => $import['id']
         ]);

         // Test number of columns
         if (count($columns) != count($header)) {
            continue;
         }

         $foundImport = true;
         foreach ($columns as $column) {

            $foundColumnInHeader = false;
            foreach ($header as $item) {
               if ($item == $column['name']) {
                  $foundColumnInHeader = true;
                  break;
               }
            }
            // Import column not found in header
            if (!$foundColumnInHeader) {
               $foundImport = false;
               break;
            }
         }
         if ($foundImport) {
            return $import['id'];
         }
      }
      return false;
   }

   /**
    * this function return the number of rows of file
    *
    * @param $filePath
    * @return int
    */
   function countRowsInFile($filePath) {
      if (file_exists($filePath)) {
         return count(file($filePath));
      }
      return null;
   }

   /**
    * Delete Import Resources and all child Import Resources Datas
    *
    * @param array $input
    * @param int $force
    * @param int $history
    * @return bool|void
    */
   function delete(array $input, $force = 0, $history = 1) {

      if (!isset($input[self::getIndexName()])) {
         Html::displayErrorAndDie('Import resources not found');
      }

      $pluginResourcesImportResourceData = new PluginResourcesImportResourceData();

      $dataCrit = [
         self::$keyInOtherTables => $input[self::getIndexName()]
      ];

      $datas = $pluginResourcesImportResourceData->find($dataCrit);
      // Remove datas
      foreach ($datas as $data) {
         $pluginResourcesImportResourceData->delete([PluginResourcesImportResourceData::getIndexName() => $data['id']]);
      }

      // Remove item
      parent::delete($input, $force, $history);
   }

   function displayPageByType($params = []) {
      switch ($params['type']) {
         case self::VERIFY_FILE:
         case self::VERIFY_GLPI:
            $this->verifyFilePage($params);
            break;
         case self::UPDATE_RESOURCES:
            $this->importFilePage($params);
            break;
         default:
            Html::displayErrorAndDie('Lost');
      }
   }

   /**
    * Display the header of the view
    *
    * @param $type
    * @param $import
    */
   function showHead($params) {
      global $CFG_GLPI;
      $js = '';

      echo '<thead>';

      // FIRST LINE HEADER
      echo '<tr>';

      switch ($params['type']) {
         case self::UPDATE_RESOURCES:
            echo "<th colspan='16'>" . __('Update GLPI Resources', 'resources');

            $title = sprintf(
               __('%1$s : %2$s'),
               __('Be careful, new resources will be created in the entity', 'resources'),
               Dropdown::getDropdownName('glpi_entities', $_SESSION['glpiactive_entity'])
            );

            echo "<br><span class='red'> " . $title . '</span></th>';
            break;
         case self::VERIFY_FILE:
            $title = __('Compare File with GLPI Resources', 'resources');
            echo "<th colspan='21'>" . $title . "</th>";
            break;
         case self::VERIFY_GLPI:
            $title = __('Compare GLPI Resources with File', 'resources');
            echo "<th colspan='21'>" . $title . "</th>";
            break;
      }

      echo '</tr>';

      // SECOND LINE HEADER
      echo '<tr>';
      switch ($params['type']) {
         case self::VERIFY_FILE:
         case self::VERIFY_GLPI:
            if (self::FILE_IMPORTER) {
               echo '<td>';
               self::showFileImporter();
               echo '</td>';
            }
            echo '<td>';
            self::showFileSelector($params);
            echo '</td>';
            break;
         case self::UPDATE_RESOURCES:
            echo '<td>';
            self::showImportSelector($params);
            echo '</td>';
            break;
      }
      echo '</tr>';

      // THIRD LINE HEADER
      if (isset($params[self::SELECTED_FILE_DROPDOWN_NAME]) && !empty($params[self::SELECTED_FILE_DROPDOWN_NAME])) {

         echo '<tr>';
         echo '<td>';
         echo "<div style='text-align:center'>";
         switch ($params['type']) {
            case self::VERIFY_FILE:
               echo '<ul>';
               echo '<li>';
               echo 'Identique à GLPI';
               echo "&nbsp;&nbsp;&nbsp;<span id='identical'>?</span>";
               echo '</li>';
               echo '<li>';
               echo 'Non Trouvé dans GLPI';
               echo "&nbsp;&nbsp;&nbsp;<span id='not_found'>?</span>";
               echo '</li>';
               echo '<li>';
               echo 'Different de GLPI';
               echo "&nbsp;&nbsp;&nbsp;<span id='different'>?</span>";
               echo '</li>';
               echo '<li>';
               echo 'Total';
               echo "&nbsp;&nbsp;&nbsp;<span id='total'>?</span>";
               echo '</li>';
               echo '<li>';
               echo "<button id='calculate' class='button'>Calculate</button>";
               echo '</li>';
               echo '</ul>';

               $initElemJs = "
                  $('#identical').html('?');
                  $('#not_found').html('?');
                  $('#different').html('?');
                  $('#total').html('?');";

               $updateResultJs = "
                  $('#identical').html(results.identical);
                  $('#not_found').html(results.not_found);
                  $('#different').html(results.different);
                  $('#total').html(results.total);";

               break;
            case self::VERIFY_GLPI:
               echo '<ul>';
               echo '<li>';
               echo 'Trouvé dans le fichier avec un identifiant de premier niveau';
               echo "&nbsp;&nbsp;&nbsp;<span id='found_first_identifier'>?</span>";
               echo '</li>';
               echo '<li>';
               echo 'Trouvé dans le fichier avec un identifiant de second niveau';
               echo "&nbsp;&nbsp;&nbsp;<span id='found_second_identifier'>?</span>";
               echo '</li>';
               echo '<li>';
               echo 'Pas dans le fichier';
               echo "&nbsp;&nbsp;&nbsp;<span id='not_found'>?</span>";
               echo '</li>';
               echo '<li>';
               echo 'Total lignes dans le fichier';
               echo "&nbsp;&nbsp;&nbsp;<span id='total'>?</span>";
               echo '</li>';
               echo '<li>';
               echo "<button id='calculate' class='button'>Calculate</button>";
               echo '</li>';
               echo '</ul>';

               $initElemJs = "
                  $('#found_first_identifier').html('?');
                  $('#found_second_identifier').html('?');
                  $('#not_found').html('?');
                  $('#total').html('?');";

               $updateResultJs = "
                  $('#found_first_identifier').html(results.found_first_identifier);
                  $('#found_second_identifier').html(results.found_second_identifier);
                  $('#not_found').html(results.not_found);
                  $('#total').html(results.total);";

               break;
         }
         echo '</div>';
         echo '</td>';
         echo '</tr>';

         $url = $CFG_GLPI['root_doc'] . '/plugins/resources/ajax/verifyCSVStatistics.php';

         $js = "$('#calculate').click(function () {
                    
                    " . $initElemJs . "
                    
                    $('#ajax_loader').show();
                    $.ajax({
                        url: '" . $url . "',
                        data: {
                            page: '" . $params['type'] . "',
                            file: $('[name=\"selected-file\"] option:selected').text()
                        },
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            // Update values
                            let results = data;
                            " . $updateResultJs . "
                            $('#ajax_loader').hide();
                        },
                        error: function (xhr, status) {
                            // Error message
                            console.error(xhr);
                            console.error(status);
                            $('#ajax_loader').hide();
                        },
                        complete: function (xhr, status) {
                            // After finish
                        }
                    });
                });";
      }

      echo '</thead>';
      echo Html::scriptBlock($js);
   }

   /**
    * Display the error header
    *
    * @param $title
    * @param null $linkText
    * @param null $url
    */
   function showErrorHeader($title, $linkText = null, $url = null) {
      echo '<thead>';
      echo '<tr>';

      echo '<th colspan="21">' . $title;

      if (!is_null($linkText) && !is_null($url)) {
         echo '<br>';
         echo "<a href='$url'>";
         echo $linkText;
         echo '</a>';
      }

      echo '</th>';
      echo '</thead>';
      echo '</tr>';
   }

   function showListHeader($params) {

      switch ($params['type']) {
         case self::UPDATE_RESOURCES:
            echo '<tr>';
            self::displayCheckAll();
            echo '<th>';
            echo __('Resource', 'resources');
            echo '</th>';
            self::displayImportColumnNames($params['import']);
            echo '</tr>';
            break;
         case self::VERIFY_FILE:
            echo '<tr>';
            foreach ($params['titles'] as $key => $title) {

               echo '<th>';
               echo $this->encodeUtf8($title);
               echo '</th>';
            }

            echo '<th>';
            echo __('Status');
            echo '</th>';

            echo '</tr>';
            break;
         case self::VERIFY_GLPI:
            echo '<tr>';
            echo '<th>';
            echo 'ID';
            echo '</th>';

            echo '<th>';
            echo __('Last name');
            echo '</th>';

            echo '<th>';
            echo __('First name');
            echo '</th>';

            echo '<th>';
            echo __('Identification', 'resources');
            echo '</th>';

            echo '<th>';
            echo __('Informations from file', 'resources');
            echo '</th>';
            echo '</tr>';
            break;
      }

   }

   function displayCheckAll() {

      $script = "function checkAll(state) {";
      $script .= "var cases = document.getElementsByTagName('input');";
      $script .= "for(var i=0; i<cases.length; i++){";
      $script .= "if(cases[i].type == 'checkbox'){";
      $script .= "cases[i].checked = state;";
      $script .= "}";
      $script .= "}";
      $script .= "}";

      echo Html::scriptBlock($script);

      echo "<th class=''>";
      echo "<div class='form-group-checkbox'>";
      echo "<input title='" . __("Check all") . "' type='checkbox' class='new_checkbox' name='checkall_imports' id='checkall_imports'";
      echo "onclick='checkAll(this.checked);' >";

      echo "<label class='label-checkbox' for='checkall_imports' title='" . __("Check all") . "'>";
      echo "<span class='check'></span>";
      echo "<span class='box'></span>";
      echo "</label>";
      echo "</div>";
      echo "</th>";
   }

   function validateDate($date, $delimiter = "/") {

      $test_arr = explode($delimiter, $date);
      if (count($test_arr) == 3) {
         if (checkdate($test_arr[0], $test_arr[1], $test_arr[2]) // English date
            || checkdate($test_arr[1], $test_arr[0], $test_arr[2])) { // French date
            return true;
         }
      }
      return false;
   }

   /**
    * Display an import line
    *
    * @param $importResourceId
    * @param $type
    * @param $resourceID
    */
   function showOne($importResourceId, $type, $resourceID = null, $borderColor = false) {

      global $CFG_GLPI;

      /*
      The date need to be send to form are :
         - ResourceID
         - Data
            - resource_column
            - value
      */

      $inputs = "import[$importResourceId][%s]";

      $oldCSS = "display:block;border-bottom:solid 1px red";
      $newCSS = "display:block;border-top:solid 1px green;margin-top:1px;";

      $pluginResourcesImportResourceData = new PluginResourcesImportResourceData();

      // Get all import data
      $datas = $pluginResourcesImportResourceData->getFromParentAndIdentifierLevel($importResourceId, null, ['resource_column']);

      if (!is_null($resourceID)) {
         $pluginResourcesResource = new PluginResourcesResource();
         $pluginResourcesResource->getFromDB($resourceID);
      }

      /*
       * %s 1 : ImportID
       * %s 2 : ColumnID
       */

      echo "<td class='center' width='10'";

      if (!is_null($borderColor)) {
         echo " style='border-left:solid 5px " . $borderColor . "'";
      }

      echo ">";

      Html::showCheckbox(["name" => "select[" . $importResourceId . "]"]);
      echo "</td>";

      $pluginResourcesResource = new PluginResourcesResource();
      if($pluginResourcesResource->getFromDB($resourceID)){
         $link = Toolbox::getItemTypeFormURL(PluginResourcesResource::getType());
         $link .= "?id=$resourceID";

         echo "<td style='text-align:center'><a href='$link'>" . $resourceID . "</a></td>";
      }
      else{
         echo "<td style='text-align:center'>".__('New resource', 'resources')."</td>";
      }

      $numberOfOthersValues = 0;

      foreach ($datas as $data) {
         if ($data['resource_column'] == 10) {
            $numberOfOthersValues++;
         }
      }

      $otherIndex = 0;

      foreach ($datas as $key => $data) {

         echo "<td style='text-align:center;padding:0;'>";

         $hValue = sprintf($inputs, $data['id']);

         $textInput = "<input name='$hValue' type='hidden' value='%s'>";

         echo "<span>";

         $oldValues = $resourceID && $pluginResourcesResource->hasDifferenciesWithValueByDataNameID(
               $resourceID,
               $data['resource_column'],
               $data['name'],
               $data['value']
            );

         switch ($data['resource_column']) {
            case 0:
            case 1:
               echo sprintf($textInput, $data['value']);

               if ($oldValues) {
                  echo "<ul>";
                  echo "<li style='$oldCSS'>";
                  $pluginResourcesResource->getFieldByDataNameID($data['resource_column']);
                  echo "</li>";
                  echo "<li style='$newCSS'>";
               }
               echo $data['value'];
               if ($oldValues) {
                  echo "</li>";
                  echo "</ul>";
               }
               break;
            case 2:
               if ($oldValues) {
                  echo "<ul>";
                  echo "<li style='$oldCSS'>";

                  $pluginResourcesContractType = new PluginResourcesContractType();
                  $pluginResourcesContractType->getFromDB($pluginResourcesResource->getFieldByDataNameID($data['resource_column']));
                  echo $pluginResourcesContractType->getName();

                  echo "</li>";
                  echo "<li style='$newCSS'>";
               }
               Dropdown::show(PluginResourcesContractType::class, [
                  'name' => $hValue,
                  'value' => $data['value'],
                  'entity' => $_SESSION['glpiactive_entity'],
                  'entity_sons' => true
               ]);
               if ($oldValues) {
                  echo "</li>";
                  echo "</ul>";
               }
               break;
            case 3:
               if ($oldValues) {
                  echo "<ul>";
                  echo "<li style='$oldCSS'>";

                  $user = new User();
                  $user->getFromDB($pluginResourcesResource->getFieldByDataNameID($data['resource_column']));
                  echo $user->getName();

                  echo "</li>";
                  echo "<li style='$newCSS'>";
               }
               User::dropdown([
                  'name' => $hValue,
                  'value' => $data['value'],
                  'entity' => $_SESSION['glpiactive_entity'],
                  'right' => 'all'
               ]);
               if ($oldValues) {
                  echo "</li>";
                  echo "</ul>";
               }
               break;
            case 4:
               if ($oldValues) {
                  echo "<ul>";
                  echo "<li style='$oldCSS'>";

                  $oldLocation = new Location();
                  $oldLocation->getFromDB($pluginResourcesResource->getFieldByDataNameID($data['resource_column']));

                  echo $oldLocation->getField('completename');
                  echo "</li>";
                  echo "<li style='$newCSS'>";
               }

               Dropdown::show(Location::class, [
                  'name' => $hValue,
                  'value' => ($data['value'] == -1) ? 0 : $data['value'],
                  'entity' => $_SESSION['glpiactive_entity'],
                  'entity_sons' => true
               ]);

               if ($oldValues) {
                  echo "</li>";
                  echo "</ul>";
               }
               break;
            case 5:
               if ($oldValues) {
                  echo "<ul>";
                  echo "<li style='$oldCSS'>";

                  $user = new User();
                  $user->getFromDB($pluginResourcesResource->getFieldByDataNameID($data['resource_column']));
                  echo $user->getName();

                  echo "</li>";
                  echo "<li style='$newCSS'>";
               }
               $config = new PluginResourcesConfig();
               if ($config->getField('resource_manager') != "") {

                  $tableProfileUser = Profile_User::getTable();
                  $tableUser = User::getTable();
                  $profile_User = new  Profile_User();
                  $prof = [];
                  foreach (json_decode($config->getField('resource_manager')) as $profs) {
                     $prof[$profs] = $profs;
                  }
                  $ids = join("','", $prof);
                  $restrict = getEntitiesRestrictCriteria($tableProfileUser, 'entities_id', $_SESSION['glpiactive_entity'], true);
                  $restrict = array_merge([$tableProfileUser . ".profiles_id" => [$ids]], $restrict);
                  $profiles_User = $profile_User->find($restrict);
                  $used = [];
                  foreach ($profiles_User as $profileUser) {
                     $user = new User();
                     $user->getFromDB($profileUser["users_id"]);
                     $used[$profileUser["users_id"]] = $user->getFriendlyName();
                  }


                  Dropdown::showFromArray($hValue, $used, ['value' => $data['value'], 'display_emptychoice' => true]);

               } else {


                  User::dropdown([
                     'name' => $hValue,
                     'value' => $data['value'],
                     'entity' => $_SESSION['glpiactive_entity'],
                     'entity_sons' => true,
                     'right' => 'all'
                  ]);

               }

               if ($oldValues) {
                  echo "</li>";
                  echo "</ul>";
               }
               break;
            case 6:
               if ($oldValues) {
                  echo "<ul>";
                  echo "<li style='$oldCSS'>";

                  $pluginResourcesDepartment = new PluginResourcesDepartment();
                  $pluginResourcesDepartment->getFromDB($pluginResourcesResource->getFieldByDataNameID($data['resource_column']));
                  echo $pluginResourcesDepartment->getName();

                  echo "</li>";
                  echo "<li style='$newCSS'>";
               }

               Dropdown::show(PluginResourcesDepartment::class, [
                  'name' => $hValue,
                  'value' => $data['value'],
                  'entity' => $_SESSION['glpiactive_entity'],
                  'entity_sons' => true
               ]);
               if ($oldValues) {
                  echo "</li>";
                  echo "</ul>";
               }
               break;
            case 7:
            case 8:
               if ($oldValues) {
                  echo "<ul>";
                  echo "<li style='$oldCSS'>";

                  echo $pluginResourcesResource->getFieldByDataNameID($data['resource_column']);
                  echo "</li>";
                  echo "<li style='$newCSS'>";
               }
               $this->showDateFieldWithoutDiv($hValue, ['value' => $data['value']]);
               if ($oldValues) {
                  echo "</li>";
                  echo "</ul>";
               }
               break;
            case 9:
               if ($oldValues) {
                  echo "<ul>";
                  echo "<li style='$oldCSS'>";

                  $user = new User();
                  $user->getFromDB($pluginResourcesResource->getFieldByDataNameID($data['resource_column']));
                  echo $user->getName();

                  echo "</li>";
                  echo "<li style='$newCSS'>";

               }
               $config = new PluginResourcesConfig();
               if (($config->getField('sales_manager') != "")) {

                  echo "<div class=\"bt-feature bt-col-sm-3 bt-col-md-3\">";
                  $tableProfileUser = Profile_User::getTable();
                  $tableUser = User::getTable();
                  $profile_User = new  Profile_User();
                  $prof = [];
                  foreach (json_decode($config->getField('sales_manager')) as $profs) {
                     $prof[$profs] = $profs;
                  }

                  $ids = join("','", $prof);
                  $restrict = getEntitiesRestrictCriteria($tableProfileUser, 'entities_id', $_SESSION['glpiactive_entity'], true);
                  $restrict = array_merge([$tableProfileUser . ".profiles_id" => [$ids]], $restrict);
                  $profiles_User = $profile_User->find($restrict);
                  $used = [];
                  foreach ($profiles_User as $profileUser) {
                     $user = new User();
                     $user->getFromDB($profileUser["users_id"]);
                     $used[$profileUser["users_id"]] = $user->getFriendlyName();
                  }

                  Dropdown::showFromArray($hValue, $used, ['value' => $data['value'], 'display_emptychoice' => true]);;
               } else {
                  User::dropdown([
                     'name' => $hValue,
                     'value' => $data['value'],
                     'entity' => $_SESSION['glpiactive_entity'],
                     'entity_sons' => true,
                     'right' => 'all'
                  ]);
               }


               if ($oldValues) {
                  echo "</li>";
                  echo "</ul>";
               }
               break;
            case 10:
               echo sprintf($textInput, $data['value']);

               if ($otherIndex == 0) {
                  echo "<table class='tab_cadrehov' style='margin:0;width:100%;'>";
               }

               echo "<tr>";

               echo "<td>" . $data['name'] . "</td>";

               echo "<td style='color: red;'>";

               if ($oldValues) {
                  echo $pluginResourcesResource->getResourceImportValueByName($resourceID, $data['name']);
               }
               echo "</td>";

               echo "<td style='color: green;'>" . $data['value'] . "</td>";

               echo "</tr>";

               if ($otherIndex == $numberOfOthersValues - 1) {
                  echo "</table>";
               }

               $otherIndex++;
               break;
         }
         echo "</span>";


         echo "</td>";
      }
   }

   private function resetExistingImportsArray() {
      $this->existingImports = null;
   }

   private function initExistingImportsArray() {
      if (is_null($this->existingImports)) {
         $this->existingImports = $this->find();
      }
   }

   private function encodeUtf8($value) {

      $detectEncoding = mb_detect_encoding($value, 'ASCII,UTF-8,ISO-8859-15');

      if ($detectEncoding) {
         return mb_convert_encoding($value, "UTF-8", $detectEncoding);
      }
      Toolbox::logDebug("Can't detect encoding of string");
      return $value;
   }

   private function verifyFilePage($params = []) {

      $defaultFileSelected = "";
      if (isset($params[self::SELECTED_FILE_DROPDOWN_NAME]) && !empty($params[self::SELECTED_FILE_DROPDOWN_NAME])) {
         $defaultFileSelected = $params[self::SELECTED_FILE_DROPDOWN_NAME];
      }

      $locationOfFiles = self::getLocationOfVerificationFiles();

      echo "<div id='ajax_loader' class=\"ajax_loader hidden\"></div>";

      echo "<div align='center'>";
      echo "<table border='0' class='tab_cadrehov'>";

      $params['location'] = $locationOfFiles;
      $params['default'] = $defaultFileSelected;

      $this->showHead($params);

      // Verify user select a file
      if (isset($params[self::SELECTED_FILE_DROPDOWN_NAME]) && !empty($params[self::SELECTED_FILE_DROPDOWN_NAME])) {

         $absoluteFilePath = self::getLocationOfVerificationFiles() . "/" . $params[self::SELECTED_FILE_DROPDOWN_NAME];

         // Verify file exist
         if (!file_exists($absoluteFilePath)) {
            $title = __("File not found", "resources");
            self::showErrorHeader($title);
         } else {

            $temp = $this->readCSVLines($absoluteFilePath, 0, 1);
            $header = array_shift($temp);

            $importId = $this->checkHeader($header);

            // Verify file header match a configured import
            if (!$importId) {
               $title = __("The selected file doesn't match any configured import", "resources");
               self::showErrorHeader($title);
            } else {

               $listParams = $this->fillVerifyParams(
                  $params['start'],
                  $params['limit'],
                  $params['type'],
                  $absoluteFilePath,
                  $importId,
                  $params[self::SELECTED_FILE_DROPDOWN_NAME],
                  self::DISPLAY_HTML
               );

               switch ($params['type']) {
                  case self::VERIFY_FILE:
                     self::showVerificationFileList($listParams);
                     break;
                  case self::VERIFY_GLPI:
                     self::showVerificationGLPIFromFileList($listParams);
                     break;
               }
            }
         }
      }

      echo "</table>";
      echo "</div>";
   }

   private function showFileImporter() {

      $formURL = self::getResourceImportFormUrl();

      echo "<form name='file-importer' method='post' action ='" . $formURL . "' >";
      echo "<div align='center'>";
      echo "<table>";

      echo "<tr>";
      echo "<td>";
      Html::file();
      echo "</td>";
      echo "<td>";
      echo "<input type='submit' name='import-file' class='submit' value='" . __('Import file', 'resources') . "' >";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }

   private function showFileSelector($params) {

      $locationOfFiles = $params['location'];
      $type = $params['type'];
      $defaultFileSelected = $params['default'];

      $action = PluginResourcesImportResource::getIndexUrl();
      $action .= "?type=" . $type;

      echo "<form name='file-selector' method='post' action ='" . $action . "' >";
      echo "<div align='center'>";
      echo "<table>";

      echo "<tr>";
      echo "<td>";

      $dropdownParams = [
         'name' => self::SELECTED_FILE_DROPDOWN_NAME,
         'folder' => $locationOfFiles,
         'default' => $defaultFileSelected
      ];

      self::dropdownFileInFolder($dropdownParams);
      echo "</td>";
      echo "<td>";
      echo "<input type='submit' name='verify' class='submit' value='" . __('Verify file', 'resources') . "' >";
      echo "</td>";
      // TODO Move the verified file to parent folder to import it auto
//      echo "<td>";
//      echo "<input type='submit' name='valid' class='submit' value='" . __('Set file ready to import', 'resources') . "' >";
//      echo "</td>";
      echo "</tr>";

      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }

   /**
    * TOTO Recursive not implemented yet
    *
    * @param $name
    * @param $absoluteFolderPath
    * @param null $defaultValue
    * @param bool $recursive
    */
   private function dropdownFileInFolder($params) {

      $name = $params['name'];
      $defaultValue = isset($params['default']) ? $params['default'] : null;
      $absoluteFolderPath = $params['folder'];

      if (!is_null($absoluteFolderPath) && !empty($absoluteFolderPath) && file_exists($absoluteFolderPath)) {

         // List of files in path
         $files = scandir($absoluteFolderPath);
         // Exclude dot and dotdot
         $files = array_diff($files, array('.', '..'));

         foreach ($files as $key => $file) {
            // Ignore directories
            if (is_dir($absoluteFolderPath . $file)) {
               unset($files[$key]);
            }
         }

         if (empty($files)) {
            echo __("no file to compare", "resources");
         } else {

            $names = [];

            foreach ($files as $file) {
               if (is_null($defaultValue)) {
                  $defaultValue = $file;
               }
               $names[$file] = $file;
            }

            Dropdown::showFromArray($name, $names, [
               'value' => $defaultValue
            ]);
         }
      } else {
         echo "<p style='color:red'>" . __("The folder you expected to display content doesn't exist.", 'resources') . "</p>";
      }
   }

   private function showImportSelector($params) {
      global $CFG_GLPI;
      $type = $params['type'];
      $imports = $params['imports'];

      if (!count($imports)) {
         $title = __("No imports configured", "resources");
         $linkText = __("Configure a new import", "resources");
         $link = $CFG_GLPI["root_doc"] . "/plugins/resources/front/import.php";

         self::showErrorHeader($title, $linkText, $link);
      } else {
         $action = PluginResourcesImportResource::getIndexUrl();
         $action .= "?type=" . $type;

         echo "<form name='file-selector' method='post' action ='" . $action . "' >";
         echo "<div align='center'>";
         echo "<table>";

         echo "<tr>";
         echo "<td>";
         self::dropdownImports($params);
         echo "</td>";
         echo "<td>";
         echo "<input type='submit' name='select' class='submit' value='" . __('Choose', 'resources') . "' >";
         echo "</td>";
         echo "</tr>";

         echo "</table>";
         echo "</div>";
         Html::closeForm();
      }
   }

   private function dropdownImports($params) {
      $defaultValue = isset($params['selected-import']) ? $params['selected-import'] : null;

      $pluginResourcesImport = new PluginResourcesImport();

      $names = [];
      $results = $pluginResourcesImport->find();

      foreach ($results as $result) {
         $names[$result['name']] = $result['name'];
      }

      Dropdown::showFromArray(self::SELECTED_IMPORT_DROPDOWN_NAME, $names, [
         'value' => $defaultValue
      ]);
   }

   public function fillVerifyParams($start, $limit, $type, $filePath, $importId, $fileSelected, $display) {
      return [
         'start' => $start,
         'limit' => $limit,
         'type' => $type,
         'file-path' => $filePath,
         'import-id' => $importId,
         self::SELECTED_FILE_DROPDOWN_NAME => $fileSelected,
         'display' => $display
      ];
   }

   public function showVerificationFileList(array $params) {

      $start = $params['start'];
      $type = $params['type'];
      $limit = $params['limit'];
      $importId = $params['import-id'];
      $absoluteFilePath = $params['file-path'];
      $display = $params['display'];

      // Number of lines in csv - header
      $nbLines = $this->countCSVLines($absoluteFilePath) - 1;

      // The first line is header
      $startLine = ($start === 0) ? 1 : $start;
      $limitLine = ($start === 0) ? $limit + 1 : $limit;

      $lines = $this->readCSVLines($absoluteFilePath, $startLine, $limitLine);

      // Recover the header of file FIRST LINE
      $temp = $this->readCSVLines($absoluteFilePath, 0, 1);
      $header = array_shift($temp);

      switch ($display) {
         case self::DISPLAY_HTML:
            // Generate pager parameters
            $parameters = "type=" . $type;
            $parameters .= "&" . self::SELECTED_FILE_DROPDOWN_NAME;
            $parameters .= "=" . $params[self::SELECTED_FILE_DROPDOWN_NAME];

            $formURL = self::getIndexUrl() . "?" . $parameters;

            echo "<form name='form' method='post' id='verify' action ='$formURL' >";
            echo "<div align='center'>";

            Html::printPager($start, $nbLines, $_SERVER['PHP_SELF'], $parameters);

            echo "<table border='0' class='tab_cadrehov'>";

            $listHeaderParams = [
               'type' => $params['type'],
               'titles' => $header
            ];

            self::showListHeader($listHeaderParams);
            break;
         case self::DISPLAY_STATISTICS:
            $result = [
               'identical' => 0,
               'different' => 0,
               'not_found' => 0,
               'total' => 0
            ];
            break;
      }

//      $lines = array_slice($lines, 0, 50);

      foreach ($lines as $line) {

         $datas = self::parseFileLine($header, $line, $importId);

         // Find identifiers
         $firstLevelIdentifiers = [];
         $secondLevelIdentifiers = [];
         $allDatas = [];

         foreach ($datas as $data) {

            $pluginResourcesImportColumn = new PluginResourcesImportColumn();
            $pluginResourcesImportColumn->getFromDB($data['plugin_resources_importcolumns_id']);

            $element = [
               'name' => $data['name'],
               'value' => $data['value'],
               'type' => $data['plugin_resources_importcolumns_id'],
               'resource_column' => $pluginResourcesImportColumn->getField('resource_column')
            ];

            $allDatas[] = $element;

            switch ($pluginResourcesImportColumn->getField('is_identifier')) {
               case 1:
                  $firstLevelIdentifiers[] = $element;
                  break;
               case 2:
                  $secondLevelIdentifiers[] = $element;
                  break;
            }
         }

         $status = null;

         $resourceID = $this->findResource($firstLevelIdentifiers);
         if (is_null($resourceID) && count($secondLevelIdentifiers) > 0) {
            $resourceID = $this->findResource($secondLevelIdentifiers);
         }

         $pluginResourcesResource = new PluginResourcesResource();

         switch ($display) {
            case self::DISPLAY_HTML:
               if (!$resourceID) {
                  $status = self::NOT_IN_GLPI;
               } else {
                  // Test Field in resources
                  if ($pluginResourcesResource->isDifferentFromImportResourceDatas($resourceID, $allDatas)) {
                     $status = self::DIFFERENT;
                  } else {
                     $status = self::IDENTICAL;
                  }
               }

               echo "<tr>";

               foreach ($allDatas as $data) {
                  if (!$resourceID || $pluginResourcesResource->isDifferentFromImportResourceData($resourceID, $data)) {
                     echo "<td class='center' style='color:red'>";
                  } else {
                     echo "<td class='center'>";
                  }

                  if ($data['value'] == -1) {
                     echo "";
                  } else {
                     $dataType = $data['resource_column'] > count(PluginResourcesResource::getDataTypes()) ? null : PluginResourcesResource::getDataType($data['resource_column']);

                     switch ($dataType) {
                        case User::class:
                           echo "<a href='" . User::getFormURLWithID($data['value']) . "'> ";
                           echo getUserName($data['value']);
                           echo "</a>";
                           break;
                        case Location::class:

                           $locationDBTM = new Location();
                           $locationDBTM->getFromDB($data['value']);

                           echo "<a href='" . Location::getFormURLWithID($data['value']) . "'> ";
                           echo $locationDBTM->getField('name');
                           echo "</a>";
                           break;
                        case PluginResourcesDepartment::class:

                           $pluginResourcesDepartmentDBTM = new PluginResourcesDepartment();
                           $pluginResourcesDepartmentDBTM->getFromDB($data['value']);

                           echo "<a href='" . PluginResourcesDepartment::getFormURLWithID($data['value']) . "'> ";
                           echo $pluginResourcesDepartmentDBTM->getField('name');
                           echo "</a>";
                           break;
                        case PluginResourcesContractType::class:

                           $pluginResourcesContractType = new PluginResourcesContractType();
                           $pluginResourcesContractType->getFromDB($data['value']);

                           echo "<a href='" . PluginResourcesContractType::getFormURLWithID($data['value']) . "'> ";
                           echo $pluginResourcesContractType->getField('name');
                           echo "</a>";
                           break;
                        default:
                           echo $data['value'];
                     }
                  }

                  echo "</td>";
               }

               echo "<td class='center'>";
               echo self::getStatusTitle($status);
               echo "</td>";

               echo "</tr>";
               break;
            case self::DISPLAY_STATISTICS:
               if (!$resourceID) {
                  $result['not_found']++;
               } else {
                  // Test Field in resources
                  if ($pluginResourcesResource->isDifferentFromImportResourceDatas($resourceID, $allDatas)) {
                     $result['different']++;
                  } else {
                     $result['identical']++;
                  }
               }
               break;
         }
      }

      switch ($display) {
         case self::DISPLAY_HTML:
            echo "</table>";
            echo "</div>";
            Html::closeForm();
            break;
         case self::DISPLAY_STATISTICS:
            echo json_encode($result);
            break;
      }
   }

   private function countCSVLines($absoluteFilePath) {
      $nb = 0;
      if (file_exists($absoluteFilePath)) {
         $handle = fopen($absoluteFilePath, 'r');
         while (($line = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $nb++;
         }
      }
      return $nb;
   }

   /**
    * Read lines in csv file
    * Carefull the first line is the header
    *
    * @param $absoluteFilePath
    * @param $start
    * @param $limit
    */
   public function readCSVLines($absoluteFilePath, $start, $limit = INF) {

      $lines = [];
      if (file_exists($absoluteFilePath)) {
         $handle = fopen($absoluteFilePath, 'r');

         $lineIndex = 0;
         while (($line = fgetcsv($handle, 1024, ';')) !== FALSE) {

            // Loop through each field
            foreach ($line as &$field) {
               // Remove any invalid or hidden characters
               $field = $this->encodeUtf8($field);
            }


            if ($lineIndex >= $start) {
               // Read line
               $lines[] = $line;
            }

            // End condition
            if ($limit != INF && $lineIndex == $start + $limit) {
               break;
            }

            $lineIndex++;
         }
         fclose($handle);
      }
      return $lines;
   }

   private function displayImportColumnNames($import) {
      global $CFG_GLPI;
      if (is_null($import)) {
         return;
      }
      $resourceColumnNames = PluginResourcesResource::getDataNames();

      $pluginResourcesImportColumn = new PluginResourcesImportColumn();

      $importColumns = $pluginResourcesImportColumn->getColumnsByImport($import['id'], true);

      foreach ($importColumns as $importColumn) {
         echo "<th>";
         echo "<img style='vertical-align: middle;' src='" .
            $CFG_GLPI["root_doc"] . "/plugins/resources/pics/csv_file.png'" .
            " title='" . __("Data from file", "resources") . "'" .
            " width='30' height='30'>";

         $name = $resourceColumnNames[$importColumn['resource_column']];

         echo "<span style='vertical-align:middle'>" . $name . "</span>";
         echo "</th>";
      }
   }

   /**
    * Transform data in csv file to match glpi data types
    *
    * @param $header
    * @param $line
    * @param $importID
    * @return array
    */
   public function parseFileLine($header, $line, $importID) {

      $column = new PluginResourcesImportColumn();
      $datas = [];

      $headerIndex = 0;
      foreach ($header as $columnName) {

         $utf8ColumnName = addslashes($columnName);
         $utf8ColumnName = $this->encodeUtf8($utf8ColumnName);

         $crit = [
            'name' => $utf8ColumnName,
            PluginResourcesImport::$keyInOtherTables => $importID
         ];

         if (!$column->getFromDBByCrit($crit)) {
            Html::displayErrorAndDie("Import column not found");
         }

         $outType = PluginResourcesResource::getDataType($column->getField('resource_column'));

         $value = null;
         if ($this->isCastable($column->getField('type'), $outType)) {
            $value = $this->castValue($line[$headerIndex], $column->getField('type'), $outType);
         }

         $datas[] = [
            "name" => $column->getName(),
            "value" => $value,
            "plugin_resources_importcolumns_id" => intval($column->getID())
         ];

         $headerIndex++;
      }

      return $datas;
   }

   /**
    * Test if input type is castable to output type
    *
    * @param $in
    * @param $out
    * @return bool
    */
   private function isCastable($in, $out) {

      switch ($in) {
         case 0: //Integer
            switch ($out) {
               case "String":
                  return true;
               case PluginResourcesContractType::class:
                  return true;
               case User::class:
                  return true;
               case Location::class:
                  return true;
               case PluginResourcesDepartment::class:
                  return true;
               case "Date":
                  return false;
            }
         case 1: //Decimal
            switch ($out) {
               case "String":
                  return true;
               case PluginResourcesContractType::class:
                  return false;
               case User::class:
                  return false;
               case Location::class:
                  return false;
               case PluginResourcesDepartment::class:
                  return false;
               case "Date":
                  return false;
            }
         case 2: //String
            switch ($out) {
               case "String":
                  return true;
               case PluginResourcesContractType::class:
                  return true;
               case User::class:
                  return true;
               case Location::class:
                  return true;
               case PluginResourcesDepartment::class:
                  return true;
               case "Date":
                  return false;
            }
         case 3: //Date
            switch ($out) {
               case "String":
                  return true;
               case PluginResourcesContractType::class:
                  return false;
               case User::class:
                  return false;
               case Location::class:
                  return false;
               case PluginResourcesDepartment::class:
                  return false;
               case "Date":
                  return true;
            }
      }
      return false;
   }

   /**
    * Cast value from input type to output type
    *
    * @param $value
    * @param $in
    * @param $out
    * @return int|string|null
    */
   private function castValue($value, $in, $out) {
      switch ($in) {
         case 0: //Integer
            switch ($out) {
               case "String":
                  return "$value";
               case PluginResourcesContractType::class:
               case User::class:
               case Location::class:
               case PluginResourcesDepartment::class:
                  return $value;
            }
         case 1: //Decimal
            switch ($out) {
               case "String":
                  return $value;
            }
         case 2: //String

            $utf8String = $this->encodeUtf8($value);

            switch ($out) {
               case "String":
                  return $utf8String;
               case PluginResourcesContractType::class:
                  // CAREFUL : PluginResourcesContractType is translated in database
                  $objectID = $this->getObjectIDByClassNameAndName(PluginResourcesContractType::class, $utf8String);

                  if ($objectID === 0 || $objectID === -1) {

                     // TODO find an alternative to find in code, maybe alternative_name variable with an array ?
                     $pluginResourcesContractTypeDBTM = new PluginResourcesContractType();
                     if ($pluginResourcesContractTypeDBTM->getFromDBByCrit(['code' => $utf8String])) {
                        $objectID = $pluginResourcesContractTypeDBTM->getID();
                     }
                  }
                  return $objectID;

               case User::class:
                  $userList = $this->getUserByFullname($utf8String);

                  if (count($userList)) {
                     $u = array_pop($userList);
                     return $u['id'];
                  }

                  return -1;
               case Location::class:
                  return $this->getObjectIDByClassNameAndName(Location::class, $utf8String);
               case PluginResourcesDepartment::class:
                  return $this->getObjectIDByClassNameAndName(PluginResourcesDepartment::class, $utf8String);
            }
         case 3: //Date
            switch ($out) {
               case "String":
                  return $value;
               case "Date":
                  return $this->formatDate($value);
            }
      }
      return null;
   }

   /**
    * Recover object from database by class and name
    *
    * @param $classname
    * @param $name
    * @return int
    */
   private function getObjectIDByClassNameAndName($classname, $name) {

      $item = new $classname();

      if ($item) {
         $item->getFromDBByCrit(['name' => $name]);
         return $item->getID();
      }

      // 0 is the default ID of items
      return 0;
   }

   /**
    * The fullname must be firstname + 1 space + lastname
    *
    * @param $fullname
    * @return array
    * @throws GlpitestSQLError
    */
   private function getUserByFullname($fullname) {
      global $DB;
      $query = "SELECT id FROM " . User::getTable() . ' WHERE CONCAT(firstname," ",realname) LIKE "' . $fullname . '"';


      $results = $DB->query($query);
      $result = [];

      while ($data = $DB->fetchAssoc($results)) {
         $result[] = $data;
      }
      return $result;
   }

   private function formatDate($value) {
      if (self::validateDate($value)) {
         return DateTime::createFromFormat('d/m/Y', $value)->format('Y-m-d');
      } else {
         return null;
      }
   }

   /**
    * BE CAREFULL IDENTIFIERS VALUE CANNOT BE EMPTY
    *
    * @param $identifiers
    * @return |null
    */
   public function findResource($identifiers) {
      global $DB;
      $crit = [];
      $needLink = false;
      $pluginResourcesResource = new PluginResourcesResource();
      foreach ($identifiers as $identifier) {

         if (is_string($identifier['value'])) {
            $value = "'" . addslashes($identifier['value']) . "'";
         } else if (is_null($identifier['value'])) {
            $value = "NULL";
         } else {
            $value = $identifier['value'];
         }

         if ($identifier['resource_column'] !== "10") {
            $crit[] = "r." . addslashes($pluginResourcesResource->getResourceColumnNameFromDataNameID($identifier['resource_column'])) . " = " . $value;
         } else {
            $needLink = true;
            $crit[] = "rd.name = '" . addslashes($identifier['name']) . "'";
            $crit[] = "rd.value = " . $value;
         }
      }

      $query = "SELECT r.id";
      $query .= " FROM " . PluginResourcesResource::getTable() . " as r";

      if ($needLink) {
         $query .= " INNER JOIN " . PluginResourcesResourceImport::getTable() . " as rd";
         $query .= " ON rd." . PluginResourcesResourceImport::$items_id;
         $query .= " = r.id";
      }

      for ($i = 0; $i < count($crit); $i++) {

         if ($i == 0) {
            $query .= " WHERE ";
         } else if ($i > 0) {
            $query .= " AND ";
         }

         $query .= $crit[$i];
      }

      $results = $DB->query($query);

      while ($data = $results->fetchArray()) {
         return $data['id'];
      }

      return false;
   }

   private function getStatusTitle($status) {
      switch ($status) {
         case self::IDENTICAL:
            return __('Identical to GLPI', 'resources');
         case self::DIFFERENT:
            return __('Different to GLPI', 'resources');
         case self::NOT_IN_GLPI:
            return __('Not in GLPI', 'resources');
      }
   }

   public function showVerificationGLPIFromFileList(array $params) {

      $start = $params['start'];
      $type = $params['type'];
      $limit = $params['limit'];
      $importId = $params['import-id'];
      $absoluteFilePath = $params['file-path'];
      $display = $params['display'];

      // Resource identifiers
      $pluginResourcesImportColumn = new PluginResourcesImportColumn();
      $crit = [$pluginResourcesImportColumn::$items_id => $importId];
      $columns = $pluginResourcesImportColumn->find($crit);

      // Get resources

      switch ($display) {
         case self::DISPLAY_STATISTICS:
            $pluginResourcesResource = new PluginResourcesResource();
            $resources = $pluginResourcesResource->find();
            $result = [
               'found_first_identifier' => 0,
               'found_second_identifier' => 0,
               'not_found' => 0,
               'total' => 0
            ];
            break;
         case self::DISPLAY_HTML:
            $resources = self::getResources($start, $limit);
            break;
      }

      $nbOfResources = (new DBUtils)->countElementsInTable(PluginResourcesResource::getTable());

      switch ($display) {
         case self::DISPLAY_HTML:
            // Generate pager parameters
            $parameters = "type=" . $type;
            $parameters .= "&" . self::SELECTED_FILE_DROPDOWN_NAME;
            $parameters .= "=" . $params[self::SELECTED_FILE_DROPDOWN_NAME];
            $formURL = self::getIndexUrl() . "?" . $parameters;

            echo "<form name='form' method='post' id='verify' action ='$formURL' >";
            echo "<div align='center'>";

            Html::printPager($start, $nbOfResources, $_SERVER['PHP_SELF'], $parameters);

            echo "<table border='0' class='tab_cadrehov'>";

            $listHeaderParams = [
               'type' => $params['type']
            ];

            self::showListHeader($listHeaderParams);
            break;
      }

      $temp = $this->readCSVLines($absoluteFilePath, 0, 1);
      $header = array_shift($temp);

      $firstLevelResourceColumns = [];
      $secondLevelResourceColumns = [];

      $columnTitles = [];

      foreach ($columns as $column) {

         $columnTitles[] = $column['name'];

         // Target : table Resource or ResourceImport
         // Name : name of the column in table
         $identifier = [
            'target' => null,
            'name' => null
         ];

         switch ($column['resource_column']) {
            case 10:
               $identifier['target'] = PluginResourcesResourceImport::class;
               $identifier['name'] = $column['name'];
               break;
            default:
               $identifier['target'] = PluginResourcesResource::class;
               $identifier['name'] = PluginResourcesResource::getColumnName($column['resource_column'], ['date_declaration DESC']);
               break;
         }

         foreach ($header as $key => $headerItem) {
            if ($headerItem == $column['name']) {
               $identifier['columnKey'] = $key;
            }
         }

         switch ($column['is_identifier']) {
            case 1:
               $firstLevelResourceColumns[] = $identifier;
               break;
            case 2:
               $secondLevelResourceColumns[] = $identifier;
               break;
         }
      }

      // The line 0 is header
      $fileReadStart = 1;

      // Find resource in file
      $lines = $this->readCSVLines($absoluteFilePath, $fileReadStart);

      $pluginResourcesResourceImport = new PluginResourcesResourceImport();

      function getHeaderIndex($header, $toFind) {
         foreach ($header as $key => $value) {
            if ($toFind == $value) {
               return $key;
            }
         }
      }

      foreach ($resources as $resource) {

         $firstLevel = false;
         $secondLevel = false;

         // Values to display in differences tooltip
         $tooltipArray = [];

         $foundedLineIndex = null;

         foreach ($lines as $key => $line) {

            $foundedFirstLevel = true;

            // Find first level
            foreach ($firstLevelResourceColumns as $firstLevelResourceColumn) {

               $lineValue = $line[$firstLevelResourceColumn['columnKey']];

               switch ($firstLevelResourceColumn['target']) {
                  case PluginResourcesResourceImport::class:

                     $crit = [
                        'plugin_resources_resources_id' => $resource['id'],
                        'name' => $pluginResourcesResourceImport->getField('name')
                     ];

                     if ($pluginResourcesResourceImport->getFromDBByCrit($crit)) {
                        if (is_string($lineValue)) {
                           $foundedFirstLevel = strcasecmp($lineValue, $pluginResourcesResourceImport->getField('value') == 0);
                        } else {
                           $foundedFirstLevel = ($lineValue == $firstLevelResourceColumn);
                        }
                     } else {
                        $foundedFirstLevel = false;
                     }
                     break;
                  case PluginResourcesResource::class:
                     $resourceValue = $resource[$firstLevelResourceColumn['name']];

                     if (is_string($lineValue)) {
                        $foundedFirstLevel = strcasecmp($lineValue, $resourceValue) == 0;
                     } else {
                        $foundedFirstLevel = ($lineValue == $firstLevelResourceColumn);
                     }
                     break;
               }

               if ($foundedFirstLevel == false) {
                  break;
               }
            }

            if ($foundedFirstLevel == true) {
               $foundedLineIndex = $key;
               $tooltipArray = $line;
               $firstLevel = true;
               break;
            }
         }

         if (!$firstLevel && count($secondLevelResourceColumns) > 0) {
            foreach ($lines as $key => $line) {

               $foundedSecondLevel = true;

               // Find first level
               foreach ($secondLevelResourceColumns as $secondLevelResourceColumn) {

                  $lineValue = $line[$secondLevelResourceColumn['columnKey']];

                  switch ($secondLevelResourceColumn['target']) {
                     case PluginResourcesResourceImport::class:

                        $crit = [
                           'plugin_resources_resources_id' => $resource['id'],
                           'name' => $pluginResourcesResourceImport->getField('name')
                        ];

                        if ($pluginResourcesResourceImport->getFromDBByCrit($crit)) {
                           if (is_string($lineValue)) {
                              $foundedSecondLevel = strcasecmp($lineValue, $pluginResourcesResourceImport->getField('value') == 0);
                           } else {
                              $foundedSecondLevel = ($lineValue == $secondLevelResourceColumn);
                           }
                        } else {
                           $foundedSecondLevel = false;
                        }
                        break;
                     case PluginResourcesResource::class:
                        $resourceValue = $resource[$secondLevelResourceColumn['name']];

                        if (is_string($lineValue)) {
                           $foundedSecondLevel = strcasecmp($lineValue, $resourceValue) == 0;
                        } else {
                           $foundedSecondLevel = ($lineValue == $secondLevelResourceColumn);
                        }
                        break;
                  }

                  if ($foundedSecondLevel == false) {
                     break;
                  }
               }

               if ($foundedSecondLevel == true) {
                  $foundedLineIndex = $key;
                  $tooltipArray = $line;
                  $secondLevel = true;
                  break;
               }
            }
         }

         // Speed up next search
         if (!is_null($foundedLineIndex)) {
            unset($lines[$foundedLineIndex]);
         }

         switch ($display) {
            case self::DISPLAY_STATISTICS:
               if (!$firstLevel && !$secondLevel) {
                  $result['not_found']++;
               } else {
                  if ($firstLevel) {
                     $result['found_first_identifier']++;
                  } else if ($secondLevel) {
                     $result['found_second_identifier']++;
                  }
               }
               $result['total']++;
               break;
            case self::DISPLAY_HTML:
               echo "<tr>";
               echo "<td class='center' ";
               if ($resource['is_deleted']) {
                  echo "style='border-left:solid 5px red;'";
               }
               echo ">";

               $link = Toolbox::getItemTypeFormURL(PluginResourcesResource::getType());
               $link .= "?id=" . $resource['id'];
               echo "<a href='$link'>" . $resource['id'] . "</a>";
               echo "</td>";
               echo "<td class='center'>";
               echo $resource['name'];
               echo "</td>";
               echo "<td class='center'>";
               echo $resource['firstname'];
               echo "</td>";
               echo "<td class='center'>";

               if (!$firstLevel && !$secondLevel) {
                  echo __("Not in file", "resources");
               } else {
                  $level = "";
                  if ($firstLevel) {
                     $level = __("first level", "resources");
                  } else if ($secondLevel) {
                     $level = __("second level", "resources");
                  }

                  $identificationText = __("Find in file with %s identifier", "resources");

                  echo sprintf($identificationText, $level);
               }
               echo "</td>";
               echo "<td class='center'>";
               if ($firstLevel || $secondLevel) {
                  self::showToolTipWithArray($columnTitles, $tooltipArray);
               }
               echo "</td>";
               echo "</tr>";
               break;
         }
      }

      switch ($display) {
         case self::DISPLAY_STATISTICS:
            echo json_encode($result);
            break;
         case self::DISPLAY_HTML:
            echo "</table>";
            echo "</div>";
            Html::closeForm();
            break;
      }
   }

   public function getResources($start, $limit) {
      global $DB;

      $query = "SELECT *";
      $query .= " FROM " . PluginResourcesResource::getTable();
      $query .= " LIMIT " . intval($start);
      $query .= ", " . intval($limit);

      $resources = [];
      if ($result = $DB->query($query)) {
         while ($data = $DB->fetchAssoc($result)) {
            $resources[] = $data;
         }
      }

      return $resources;
   }

   private function showToolTipWithArray($titles, $values, $title = null) {

      if (count($titles) == count($values)) {
         $content = "<table border='0' class='tab_cadrehov'>";

         if (!is_null($title)) {
            $content .= "<tr>";
            $content .= "<th>";
            $content .= $title;
            $content .= "</th>";
            $content .= "</tr>";
         }
         $content .= "<tbody>";

         for ($i = 0; $i < count($titles); $i++) {

            $content .= "<tr>";
            $content .= "<td class='center'>";
            $content .= $titles[$i];
            $content .= "</td>";
            $content .= "<td class='center'>";
            $content .= $values[$i];
            $content .= "</td>";
            $content .= "</tr>";
         }

         $content .= "</tbody>";
         $content .= "</table>";

         Html::showToolTip($content);
      } else {
         Html::showToolTip(__("Number of titles and values of tooltip doesn't match", "resources"));
      }
   }

   private function importFilePage($params) {

      echo "<div align='center'>";
      echo "<table border='0' class='tab_cadrehov'>";

      $pluginResourcesImport = new PluginResourcesImport();
      $imports = $pluginResourcesImport->find();

      $additionalParams = [
         'imports' => $imports
      ];

      $this->showHead(array_merge($params, $additionalParams));

      // Message when no import configured
      if (isset($params[self::SELECTED_IMPORT_DROPDOWN_NAME]) && !empty($params[self::SELECTED_IMPORT_DROPDOWN_NAME])) {
         self::showImportList2($params);
      }

      echo "</table>";
      echo "</div>";
   }

   public function getResourcesImports($imports_id, $start, $limit) {

      global $DB;
      $query = "SELECT * FROM " . $this->getTable();
      $query .= " WHERE plugin_resources_imports_id = " . $imports_id;
      $query .= " LIMIT " . $start . ", " . $limit;

      $resourcesImports = [];
      if ($result = $DB->query($query)) {
         while ($data = $DB->fetchAssoc($result)) {
            $resourcesImports[] = $data;
         }
      }
      return $resourcesImports;
   }

   private function showImportList2(array $params) {

      $start = $params['start'];
      $limit = $params['limit'];
      $type = intval($params['type']);

      $dbu = new DbUtils();
      $pluginResourcesResourcesDBTM = new PluginResourcesResource();
      $pluginResourcesImportDBTM = new PluginResourcesImport();
      $pluginResourcesImportColumnDBTM = new PluginResourcesImportColumn();
      $pluginResourcesImportResourceDataDBTM = new PluginResourcesImportResourceData();

      $pluginResourcesImportDBTM->getFromDBByCrit(['name' => $params[self::SELECTED_IMPORT_DROPDOWN_NAME]]);

      $columns = $pluginResourcesImportColumnDBTM->find(['plugin_resources_imports_id' => $pluginResourcesImportDBTM->getID()]);

      $numberOfFirstLevelIdentifiers = 0;
      $numberOfSecondLevelIdentifiers = 0;

      foreach ($columns as $column) {
         switch ($column['is_identifier']) {
            case 1:
               $numberOfFirstLevelIdentifiers++;
               break;
            case 2:
               $numberOfSecondLevelIdentifiers++;
               break;
         }
      }

      // Get all imports from the selected type of import
      $importResources = $this->getResourcesImports($pluginResourcesImportDBTM->getID(), $start, $limit);

      $critNbImports = ['plugin_resources_imports_id' => $pluginResourcesImportDBTM->getID()];
      $nbImports = $dbu->countElementsInTable(PluginResourcesImportResource::getTable(), $critNbImports);

      $elementDisplayed = 0;

      if (!is_array($importResources) || !count($importResources)) {
         self::showErrorHeader(__('No Imports', 'resources'));
      } else {

         // Generate pager parameters
         $parameters = "type=" . $params['type'];
         $parameters .= "&" . self::SELECTED_IMPORT_DROPDOWN_NAME;
         $parameters .= "=" . $params[self::SELECTED_IMPORT_DROPDOWN_NAME];
         $formURL = self::getResourceImportFormUrl() . "?" . $parameters;

         Html::printPager($params['start'], $nbImports, $_SERVER['PHP_SELF'], $parameters);

         echo "<form name='form' method='post' id='import' action ='$formURL' >";
         echo "<div align='center'>";
         echo "<table border='0' class='tab_cadrehov'>";

         self::showImportListButtons();

         $headParams = [
            'type' => $params['type'],
            'import' => $pluginResourcesImportDBTM->fields
         ];

         self::showListHeader($headParams);

         foreach ($importResources as $importResource) {

            // Find identifiers
            $firstLevelIdentifiers = [];
            $secondLevelIdentifiers = [];

            $datas = $pluginResourcesImportResourceDataDBTM->find(["plugin_resources_importresources_id" => $importResource['id']]);

            foreach ($datas as $data) {

               // Speed up loop
               if (count($firstLevelIdentifiers) == $numberOfFirstLevelIdentifiers
                  && count($secondLevelIdentifiers) == $numberOfSecondLevelIdentifiers) {
                  break;
               }

               $column = $columns[$data['plugin_resources_importcolumns_id']];

               switch ($column['is_identifier']) {
                  case 1:
                     $element = [
                        'name' => $data['name'],
                        'value' => $data['value'],
                        'type' => $data['plugin_resources_importcolumns_id'],
                        'resource_column' => $column['resource_column']
                     ];

                     if (is_string($element['value']) && empty($element['value'])) {
                        $element['value'] = null;
                     }
                     $firstLevelIdentifiers[] = $element;
                     break;
                  case 2:
                     $element = [
                        'name' => $data['name'],
                        'value' => $data['value'],
                        'type' => $data['plugin_resources_importcolumns_id'],
                        'resource_column' => $column['resource_column']
                     ];

                     if (is_string($element['value']) && empty($element['value'])) {
                        $element['value'] = null;
                     }
                     $secondLevelIdentifiers[] = $element;
                     break;
               }
            }

            if (count($firstLevelIdentifiers) > 0) {
               $resourceID = $this->findResource($firstLevelIdentifiers);
            }

            if (!$resourceID && count($secondLevelIdentifiers) > 0) {
               $resourceID = $this->findResource($secondLevelIdentifiers);
            }

            $borderColor = null;

            echo "<tr valign='center' ";
            if ($pluginResourcesResourcesDBTM->getFromDB($resourceID)) {
               if ($pluginResourcesResourcesDBTM->fields['is_deleted']) {
                  $borderColor = 'red';
               }
               else{
                  $borderColor = 'orange';
               }
            }
            else{
               $borderColor = 'green';
            }
            echo ">";

            $resourceInput = "resource[" . $importResource['id'] . "]";
            echo "<input type='hidden' name='$resourceInput' value='" . $resourceID . "'>";

            $this->showOne($importResource['id'], $params['type'], $resourceID, $borderColor);

            echo "</tr>";

            if ($elementDisplayed == $limit) {
               break;
            }
         }
      }

      self::showImportListButtons();

      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }

////// CRON FUNCTIONS ///////
   //Cron action

   private function getImportResources($importID, $importId, $order, $limit = null) {
      global $DB;

      $query = "SELECT *";
      $query .= " FROM " . self::getTable();
      $query .= " WHERE plugin_resources_imports_id = " . $importID;

      $query .= " AND id ";
      $query .= ($order == self::BEFORE) ? "<" : ">";
      $query .= " " . $importId;

      if (!is_null($limit)) {
         $query .= " LIMIT " . intval($limit);
      }

      $imports = [];
      if ($result = $DB->query($query)) {
         while ($data = $DB->fetchAssoc($result)) {
            $imports[] = $data;
         }
      }

      return $imports;
   }

   private function showImportListButtons() {
      echo "<tr>";
      echo "<td class='center' colspan='100'>";
      echo "<input type='submit' name='save' class='submit' value='" . _sx('button', 'Save') . "' >";
      echo "&nbsp;&nbsp;<input type='submit' name='delete' class='submit' value='" . _sx('button', 'Remove an item') . "' >";
      echo "</td>";
      echo "</tr>";
   }
}