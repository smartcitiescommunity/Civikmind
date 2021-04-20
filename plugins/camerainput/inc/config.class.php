<?php

class PluginCamerainputConfig extends CommonGLPI
{
   static protected $notable = true;

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      if (!$withtemplate && $item->getType() === 'Config') {
         return __('Camera Input', 'camerainput');
      }
      return '';
   }

   public function showForm()
   {
      if (!Session::haveRight('config', UPDATE)) {
         return false;
      }
      $config = self::getConfig(true);

      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL('Config')."\" method='post'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . __('General', 'camerainput') . '</th></thead><td>';
      echo "<input type='hidden' name='config_class' value='".__CLASS__."'>";
      echo "<input type='hidden' name='config_context' value='plugin:camerainput'>";
      echo __('Enabled barcode formats', 'camerainput') . '</td>';
      echo '<td>';
      Dropdown::showFromArray('barcode_formats', [
         'code_128_reader'    => 'Code 128',
         'ean_reader'         => 'EAN',
         'ean_8_reader'       => 'EAN 8',
         'code_39_reader'     => 'Code 39',
         'code_39_vin_reader' => 'Code 39 VIN',
         'codabar_reader'     => 'Codabar',
         'upc_reader'         => 'UPC',
         'upc_e_reader'       => 'UPC E',
         'i2of5_reader'       => 'Interleaved 2 of 5',
         '2of5_reader'        => '2 of 5',
         'code_93_reader'     => 'Code 93',
      ], [
         'multiple'  => true,
         'values'    => isset($config['barcode_formats']) ? importArrayFromDB($config['barcode_formats']) : ['code_39_reader'],
         'size'      => 3
      ]);
      echo '</td><td></td><td>';
      echo '</td></tr>';
      echo '</table>';

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save'). '">';
      echo '</td></tr>';
      echo '</table>';
      echo '</div>';
      Html::closeForm();
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      if ($item->getType() === 'Config') {
         $config = new self();
         $config->showForm();
      }
   }

   public static function undiscloseConfigValue($fields)
   {
      $to_hide = [];
      foreach ($to_hide as $f) {
         if (in_array($f, $fields, true)) {
            unset($fields[$f]);
         }
      }
      return $fields;
   }

   public static function getConfig(bool $force_all = false) : array
   {
      static $config = null;
      if ($config === null) {
         $config = Config::getConfigurationValues('plugin:camerainput');
      }
      if (!$force_all) {
         return self::undiscloseConfigValue($config);
      }

      return $config;
   }

   public static function preAddOrUpdateConfig(CommonDBTM $item)
   {
      if ($item->input['config_class'] !== self::class) {
         return;
      }
      if (isset($item->input['barcode_formats']) && is_array($item->input['barcode_formats'])) {
         $item->input['barcode_formats'] = exportArrayToDB($item->input['barcode_formats']);
      }
   }
}
