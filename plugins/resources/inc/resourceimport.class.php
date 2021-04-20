<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesResourceImport
 */
class PluginResourcesResourceImport extends CommonDBChild {

   static $rightname = 'plugin_resources_import';
   public $dohistory = true;

   static public $itemtype = PluginResourcesResource::class;
   static public $items_id = 'plugin_resources_resources_id';

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Import', 'Imports', $nb, 'resources');
   }

   /**
    * Get Tab Name used for itemtype
    *
    * NB : Only called for existing object
    *      Must check right on what will be displayed + template
    *
    * @param CommonDBTM|CommonGLPI $item CommonDBTM object for which the tab need to be displayed
    * @param bool|int $withtemplate boolean  is a template object ? (default 0)
    *
    * @return string tab name
    * @since version 0.83
    *
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      // can exists for template
      if ($item->getType() == PluginResourcesResource::class) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            $dbu = new DbUtils();
            $table = $dbu->getTableForItemType(__CLASS__);
            return self::createTabEntry(self::getTypeName(),
               $dbu->countElementsInTable($table,
                  [self::$items_id => $item->getID()]));
         }
         return self::getTypeName();
      }
      return '';
   }

   /**
    * Create New Resource and linked ResourceImport
    * Delete ImportResource and ImportResourceData
    *
    * @param array $input
    * @param array $options
    * @param bool $history
    * @return int|null
    */
   function add(array $input, $options = [], $history = true){

      $importID = $input['importID'];

      $pluginResourcesImportResource = new PluginResourcesImportResource();
      $pluginResourcesImportResourceData = new PluginResourcesImportResourceData();
      $pluginResourcesImportColumn = new PluginResourcesImportColumn();

      if (!$pluginResourcesImportResource->getFromDB($importID)) {
         Html::displayErrorAndDie('ImportResource not found');
      }

      $resourceInputs = [];
      $resourceInputs['entities_id'] = $_SESSION['glpiactive_entity'];

      $resourceImportInputs = [];

      foreach ($input['datas'] as $importResourceDataID => $inputValue) {

         if (!$pluginResourcesImportResourceData->getFromDB($importResourceDataID)) {
            Html::displayErrorAndDie('ImportResourceData not found');
         }

         if (!$pluginResourcesImportColumn->getFromDB($pluginResourcesImportResourceData->getField('plugin_resources_importcolumns_id'))) {
            Html::displayErrorAndDie('ImportColumn not found');
         }

         switch ($pluginResourcesImportColumn->getField('resource_column')) {
            case "10": //others
               $resourceImportInputs[] = [
                  'name' => $pluginResourcesImportResourceData->getField('name'),
                  'value' => $inputValue
               ];
               break;
            default:
               $resourceTableColumnName = PluginResourcesResource::getResourceColumnNameFromDataNameID(
                  $pluginResourcesImportColumn->getField('resource_column')
               );
               $resourceInputs[$resourceTableColumnName] = $inputValue;
               break;
         }
      }

      $resource = new PluginResourcesResource();

      // Bypass check required fields
      $keys = array_keys($resourceInputs);

      if(!in_array('locations_id', $keys)){
         $resourceInputs['locations_id'] = 0;
      }

      if(!in_array('users_id_sales', $keys)){
         $resourceInputs['users_id_sales'] = 0;
      }

      if(!in_array('plugin_resources_departments_id', $keys)){
         $resourceInputs['plugin_resources_departments_id'] = 0;
      }

      $resourceInputs['force'] = 1;
      $resourceID = $resource->add($resourceInputs);

      if (!$resourceID) {
         Html::displayErrorAndDie('Failed to create resources');
      }

      foreach ($resourceImportInputs as $resourceImportInput) {
         $resourceImportInput[PluginResourcesResourceImport::$items_id] = $resourceID;
         if (!parent::add($resourceImportInput)) {
            Html::displayErrorAndDie('Failed to create resourceimports');
         }
      }
   }

   function update(array $input, $history = 1, $options = []) {

      $resourceID = $input['resourceID'];

      $pluginResourcesImportResourceData = new PluginResourcesImportResourceData();
      $pluginResourcesResourceImport = new PluginResourcesResourceImport();
      $pluginResourcesImportColumn = new PluginResourcesImportColumn();

      $resourceInputs = ['id' => $resourceID];

      foreach ($input['datas'] as $importResourceDataID => $inputValue) {

         if (!$pluginResourcesImportResourceData->getFromDB($importResourceDataID)) {
            Html::displayErrorAndDie('ImportResourceData not found');
         }

         if (!$pluginResourcesImportColumn->getFromDB($pluginResourcesImportResourceData->getField('plugin_resources_importcolumns_id'))) {
            Html::displayErrorAndDie('ImportColumn not found');
         }

         $resourceColumn = $pluginResourcesImportColumn->getField('resource_column');

         switch ($resourceColumn) {
            case 10:
               $criterias = [
                  PluginResourcesResourceImport::$items_id => $resourceID,
                  'name' => $pluginResourcesImportResourceData->getField('name')
               ];

               // Resource Import already exist
               if ($pluginResourcesResourceImport->getFromDBByCrit($criterias)) {
                  $resourceImportInput = [
                     PluginResourcesResourceImport::getIndexName() => $pluginResourcesResourceImport->getID(),
                     "plugin_resources_resources_id" => $resourceID,
                     'value' => $inputValue
                  ];

                  if (!parent::update($resourceImportInput)) {
                     Html::displayErrorAndDie('Error when updating Resource Import');
                  }
                  // Resource import doesn't exist yet
               } else {
                  $resourceImportInput = [
                     "plugin_resources_resources_id" => $resourceID,
                     'name' => $pluginResourcesImportResourceData->getField('name'),
                     'value' => $inputValue
                  ];

                  if (!parent::add($resourceImportInput)) {
                     Html::displayErrorAndDie('Error when creating Resource Import');
                  }
               }
               break;
            default:

               // Get the column name from resource_column
               $fieldName = PluginResourcesResource::getResourceColumnNameFromDataNameID(
                  $pluginResourcesImportColumn->getField('resource_column')
               );

               $resourceInputs[$fieldName] = $inputValue;
         }
      }

      $resource = new PluginResourcesResource();

      // Update resource column
      if (!$resource->update($resourceInputs)) {
         Html::displayErrorAndDie('Error when updating Resource Import');
      }
   }

   /**
    * show Tab content
    *
    * @param          $item                  CommonGLPI object for which the tab need to be displayed
    * @param          $tabnum       integer  tab number (default 1)
    * @param bool|int $withtemplate boolean  is a template object ? (default 0)
    *
    * @return true
    * @since version 0.83
    *
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == PluginResourcesResource::class) {
         self::showImportResources($item, $withtemplate);
      }
      return true;
   }

   static function showImportResources($item, $withtemplate) {

      $pluginResourcesResourceImport = new PluginResourcesResourceImport();
      $resourceImports = $pluginResourcesResourceImport->find([
         'plugin_resources_resources_id' => $item->getID()
      ]);

      echo "<div align='central'>";

      echo "<table class='tab_cadrehov'>";
      echo "<tr>" . __("Imported values", 'resources') . "</tr>";
      echo "<tr>";
      echo "<th>" . __("Name") . "</th>";
      echo "<th>" . __("Value") . "</th>";
      echo "</tr>";

      foreach ($resourceImports as $resourceImport) {
         echo "<tr>";
         echo "<td style='text-align:center'>";
         echo $resourceImport['name'];
         echo "</td>";
         echo "<td style='text-align:center'>";
         echo $resourceImport['value'];
         echo "</td>";
         echo "<tr>";
      }

      echo "</table>";

      echo "</div>";
   }
}