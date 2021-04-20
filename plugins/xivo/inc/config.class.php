<?php
/*
 -------------------------------------------------------------------------
 xivo plugin for GLPI
 Copyright (C) 2017 by the xivo Development Team.

 https://github.com/pluginsGLPI/xivo
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivo.

 xivo is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 xivo is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with xivo. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginXivoConfig extends Config {

   static function getTypeName($nb = 0) {
      return __('Xivo', 'xivo');
   }

   /**
    * Return the current config of the plugin store in the glpi config table
    *
    * @return array config with keys => values
    */
   static function getConfig() {
      return Config::getConfigurationValues('plugin:xivo');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Config":
            return self::createTabEntry(self::getTypeName());
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Config":
            return self::showForConfig($item, $withtemplate);
      }

      return true;
   }

   static function showForConfig(Config $config, $withtemplate = 0) {
      global $CFG_GLPI;

      if (!self::canView()) {
         return false;
      }

      $current_config = self::getConfig();
      $canedit        = Session::haveRight(self::$rightname, UPDATE);
      echo "<div class='xivo_config'>";
      if ($canedit) {
         echo "<form name='form' action='".Toolbox::getItemTypeFormURL("Config")."' method='post'>";
      }

      echo "<h1>".__("Configuration of XIVO integration", 'xivo')."</h1>";

      echo "<h4>";
      echo self::showField([
         'inputtype' => 'yesno',
         'label'     => __("XUC integration (click2call, presence, etc)", 'xivo'),
         'attrs'     => [
            'name'      => 'enable_xuc',
            'value'     => $current_config['enable_xuc'],
            'on_change' => '$("#xuc_config").toggleFromValue(this.value);',
         ]
      ]);
      echo "</h4>";

      $style = "";
      if (!$current_config['enable_xuc']) {
         $style = "display: none;";
      }
      echo "<div id='xuc_config' class='xivo_config_block' style='$style'>";

      echo self::showField([
         'label' => __("XUC url (with port)", 'xivo'),
         'attrs' => [
            'name'         => 'xuc_url',
            'value'        => $current_config['xuc_url'],
            'placeholder'  => 'https://xup_ip:8090',
         ]
      ]);
      echo self::showField([
         'inputtype' => 'yesno',
         'label'     => __("Secure connection to WebSocket", 'xivo'),
         'attrs'     => [
            'name'  => 'xuc_secure',
            'value' => $current_config['xuc_secure'],
         ]
      ]);
      echo self::showField([
         'inputtype' => 'yesno',
         'label'     => __("Enable for Self-service users", 'xivo'),
         'attrs'     => [
            'name'      => 'enable_xuc_selfservice',
            'value'     => $current_config['enable_xuc_selfservice'],
         ]
      ]);

      echo "<div class='xivo_config_block inline_fields sub_config'>";
      echo "<h5>".__("Features")."</h5>";
      echo self::showField([
         'inputtype' => 'yesno',
         'width' => '80px',
         'label'     => __("Click2call", 'xivo'),
         'attrs'     => [
            'name'  => 'enable_click2call',
            'value' => $current_config['enable_click2call'],
         ]
      ]);

      if (PLUGIN_XIVO_ENABLE_PRESENCE) {
         echo self::showField([
            'inputtype' => 'yesno',
            'width' => '80px',
            'label'     => __("Presence", 'xivo'),
            'attrs'     => [
               'name'  => 'enable_presence',
               'value' => $current_config['enable_presence'],
            ]
         ]);
      }

      if (PLUGIN_XIVO_ENABLE_CALLCENTER) {
         echo self::showField([
            'inputtype' => 'yesno',
            'width' => '80px',
            'label'     => __("Callcenter features", 'xivo'),
            'attrs'     => [
               'name'  => 'enable_callcenter',
               'value' => $current_config['enable_callcenter'],
            ]
         ]);
      }

      echo self::showField([
         'inputtype' => 'yesno',
         'width' => '200px',
         'label'     => __("Auto-open user/ticket on call", 'xivo'),
         'attrs'     => [
            'name'  => 'enable_auto_open',
            'value' => $current_config['enable_auto_open'],
         ]
      ]);
      echo self::showField([
         'inputtype' => 'yesno',
         'width' => '200px',
         'label'     => __("Auto-open in new window", 'xivo'),
         'attrs'     => [
            'name'  => 'auto_open_blank',
            'value' => $current_config['auto_open_blank'],
         ]
      ]);
      echo self::showField([
         'inputtype' => 'yesno',
         'width' => '200px',
         'label'     => __("Keep xuc session", 'xivo'),
         'attrs'     => [
            'name'  => 'xuc_local_store',
            'value' => $current_config['xuc_local_store'],
         ]
      ]);
      echo "</div>"; // .inline_fields
      echo "<div class='inline_fields_clear'></div>";

      echo "</div>"; // #xuc_config

      echo "<h4>";
      echo self::showField([
         'inputtype' => 'yesno',
         'label'     => __("Import assets into GLPI inventory", 'xivo'),
         'attrs'     => [
            'name'      => 'import_assets',
            'value'     => $current_config['import_assets'],
            'on_change' => '$("#import_assets").toggleFromValue(this.value);',
         ]
      ]);
      echo "</h4>";

      $style = "";
      if (!$current_config['import_assets']) {
         $style = "display: none;";
      }
      echo "<div id='import_assets' class='xivo_config_block' style='$style'>";
      echo "<div class='inline_fields'>";
      echo self::showField([
         'label' => __("API REST url", 'xivo'),
         'width' => '300px',
         'attrs' => [
            'name'        => 'api_url',
            'value'       => $current_config['api_url'],
            'placeholder' => 'https://...',
            'style' => 'width:285px;',
         ]
      ]);
      echo self::showField([
         'inputtype' => 'yesno',
         'width'     => '120px',
         'label'     => __("Check certificate"),
         'attrs'     => [
            'name'  => 'api_ssl_check',
            'value' => $current_config['api_ssl_check'],
         ]
      ]);
      echo "</div>"; // .inline_fields
      echo "<div class='inline_fields_clear'></div>";

      echo "<div class='inline_fields'>";
      echo self::showField([
         'label' => __("Login"),
         'width' => '150px',
         'attrs' => [
            'name'  => 'api_username',
            'value' => $current_config['api_username'],
            'style' => 'width:90%;',
         ]
      ]);
      echo self::showField([
         'inputtype' => 'password',
         'width'     => '150px',
         'label'     => __("Password"),
         'attrs'     => [
            'name'  => 'api_password',
            'value' => $current_config['api_password'],
            'style' => 'width:90%;',
         ]
      ]);
      echo "</div>"; // .inline_fields
      echo "<div class='inline_fields_clear'></div>";

      echo self::showField([
         'inputtype' => 'dropdown',
         'itemtype'  => 'Entity',
         'label'     => __("Default entity"),
         'attrs' => [
            'name'  => 'default_entity',
            'value' => $current_config['default_entity'],
         ]
      ]);
      echo self::showField([
         'inputtype' => 'yesno',
         'label'     => __("Import phones", 'xivo'),
         'attrs'     => [
            'name'  => 'import_phones',
            'value' => $current_config['import_phones'],
            'on_change' => '$("#import_phones").toggleFromValue(this.value);',
         ]
      ]);
      $style = "";
      if (!$current_config['import_phones']) {
         $style = "display: none;";
      }
      echo "<div id='import_phones' class='xivo_config_block inline_fields sub_config' style='$style'>";
      echo "<p>".__('Also import phones with', 'xivo')."</p>";
      echo self::showField([
         'inputtype' => 'yesno',
         'width'     => '100px',
         'label'     => __("empty serial", 'xivo'),
         'attrs'     => [
            'name'  => 'import_empty_sn',
            'value' => $current_config['import_empty_sn'],
         ]
      ]);
      echo self::showField([
         'inputtype' => 'yesno',
         'width'     => '100px',
         'label'     => __("empty mac", 'xivo'),
         'attrs'     => [
            'name'  => 'import_empty_mac',
            'value' => $current_config['import_empty_mac'],
         ]
      ]);
      echo self::showField([
         'inputtype' => 'yesno',
         'width'     => '200px',
         'label'     => __("'not_configured' state", 'xivo'),
         'attrs'     => [
            'name'  => 'import_notconfig',
            'value' => $current_config['import_notconfig'],
         ]
      ]);
      echo "</div>";
      echo "<div class='inline_fields_clear'></div>";

      echo self::showField([
         'inputtype' => 'yesno',
         'label'     => __("Import lines", 'xivo'),
         'attrs'     => [
            'name'  => 'import_lines',
            'value' => $current_config['import_lines'],
            'on_change' => '$("#import_lines").toggleFromValue(this.value);',
         ]
      ]);
      $style = "";
      if (!$current_config['import_lines']) {
         $style = "display: none;";
      }
      echo "<div id='import_lines' class='xivo_config_block sub_config' style='$style'>";
      echo self::showField([
         'inputtype' => 'yesno',
         'label'     => __("Import phones-lines relation", 'xivo'),
         'attrs'     => [
            'name'  => 'import_phonelines',
            'value' => $current_config['import_phonelines'],
         ]
      ]);
      echo "</div>";

      if (self::isValid()) {
         echo Html::link(__("Force synchronization"), self::getFormURL()."?forcesync");
      }

      echo "</div>";

      if ($canedit) {
         echo Html::hidden('config_class', ['value' => __CLASS__]);
         echo Html::hidden('config_context', ['value' => 'plugin:xivo']);
         echo Html::submit(_sx('button', 'Save'), [
            'name' => 'update'
         ]);
      }

      Html::closeForm();

      if (self::isValid()) {
         echo "<h1>".__("REST API status", 'xivo')."</h1>";
         $apiclient    = new PluginXivoAPIClient;
         $data_connect = $apiclient->connect();
         $all_status   = $apiclient->status();

         echo "<ul>";
         $error = false;
         foreach ($all_status as $status_label => $status) {
            $color_png = "greenbutton.png";
            if (!$status) {
               $color_png = "redbutton.png";
               $error = true;
            }
            echo "<li>";
            echo Html::image($CFG_GLPI['root_doc']."/pics/$color_png");
            echo "&nbsp;".$status_label;
            echo "</li>";
         }
         echo "</ul>";

         if ($error) {
            echo "<h1>".__("Last Error", 'xivo')."</h1>";
            $error = $apiclient->getLastError();
            echo $error['exception'];
         }

         if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
            echo "<h1>".__("DEBUG")."</h1>";

            // display token
            if (isset($data_connect['data']['token'])) {
               echo "<h2>".__("Auth token", 'xivo')."</h2>";
               echo $data_connect['data']['token'];
            }

            // display acl
            if (isset($data_connect['data']['acls'])) {
               echo "<h2>".__("ACL", 'xivo')." (".count($data_connect['data']['acls']).")</h2>";
               echo "<ul>";
               foreach ($data_connect['data']['acls'] as $right) {
                  echo "<li>$right</li>";
               }
               echo "</ul>";
            }
         }
      }
      echo "</div>"; // .xivo_config
   }

   /**
    * Show a single config field
    * Generic method who call the different GLPI function to display a field
    *
    * @param  array  $options a list of options:
    *                            - inputtype (string), can be
    *                               * text
    *                               * password
    *                               * yesno
    *                               * dropdown
    *                            - itemtype (only for input=dropdown)
    *                            - label, <label> tag to append to the field
    *                            - attrs, an array containing html attributes
    * @return string the html
    */
   static function showField($options = []) {
      $rand            = mt_rand();
      $default_options = [
         'inputtype' => 'text',
         'itemtype'  => '',
         'label'     => '',
         'attrs'     => [
            'name'        => '',
            'value'       => '',
            'placeholder' => '',
            'style'       => 'width:50%;',
            'id'          => "xivoconfig_field_$rand",
            'class'       => 'xivo_input',
            'required'    => 'required',
            'on_change'   => ''
         ]
      ];
      $options = array_replace_recursive($default_options, $options);

      if (isset($options['attrs']['required'])) {
         $options['attrs']['_required'] = $options['attrs']['required'];
      }

      $out   = "";
      $width = "";
      if (isset($options['width'])) {
         $width = "style='width: ".$options['width']."'";
      }
      $out.= "<div class='xivo_field' $width>";

      // call the field according to its type
      switch ($options['inputtype']) {
         default:
         case 'text':
            $out.= Html::input('fakefield', ['style' => 'display:none;']);
            $out.= Html::input($options['attrs']['name'], $options['attrs']);
            break;

         case 'password':
            $out.=  "<input type='password' name='fakefield' style='display:none;'>";
            $out.=  "<input type='password'";
            foreach ($options['attrs'] as $key => $value) {
               $out.= "$key='$value' ";
            }
            $out.= ">";
            break;

         case 'yesno':
            $options['attrs']['display'] = false;
            $out.= Dropdown::showYesNo($options['attrs']['name'], $options['attrs']['value'], -1, $options['attrs']);
            break;

         case 'dropdown':
            $options['attrs']['display'] = false;
            $out.= Dropdown::show($options['itemtype'], $options['attrs']);
            break;
      }

      $out.= "<label class='xivo_label' for='{$options['attrs']['id']}'>
              {$options['label']}</label>";
      $out.= "</div>";

      return $out;
   }

   /**
    * Check if current saved config is valid
    * @param  boolean $with_api also check api status
    * @return boolean
    */
   static function isValid($with_api = false) {
      $current_config = self::getConfig();
      $valid_config =  (!empty($current_config['api_url'])
                        && !empty($current_config['api_username'])
                        && !empty($current_config['api_password']));

      $valid_api = true;
      if ($with_api) {
         $apiclient = new PluginXivoAPIClient;
         $apiclient->connect();
         //$statuses = $apiclient->status();
         $valid_api = !in_array(false, $apiclient->status());
      }

      return ($valid_config && $valid_api);
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   static function install(Migration $migration) {
      $current_config = self::getConfig();

      // migrate from old versions
      if (isset($current_config['import_devices'])
          && $current_config['import_devices']) {
         Config::setConfigurationValues('plugin:xivo', ['import_assets' => 1]);
         Config::setConfigurationValues('plugin:xivo', ['import_phones' => 1]);
         Config::setConfigurationValues('plugin:xivo', ['import_lines' => 1]);
         Config::setConfigurationValues('plugin:xivo', ['import_phonelines' => 1]);
         Config::deleteConfigurationValues('plugin:xivo', ['import_devices']);
         $current_config = self::getConfig();
      }

      // fill config table with default values if missing
      foreach ([
         // api access
         'import_assets'          => 0,
         'import_phones'          => 0,
         'import_lines'           => 0,
         'import_phonelines'      => 0,
         'api_url'                => '',
         'api_username'           => '',
         'api_password'           => '',
         'api_ssl_check'          => 1,
         'import_empty_sn'        => 0,
         'import_empty_mac'       => 0,
         'import_notconfig'       => 0,
         'default_entity'         => 0,
         'enable_xuc'             => 0,
         'enable_xuc_selfservice' => 0,
         'xuc_url'                => '',
         'xuc_secure'             => 0,
         'enable_click2call'      => 0,
         'enable_presence'        => 0,
         'enable_auto_open'       => 0,
         'enable_callcenter'      => 0,
         'auto_open_blank'        => 1,
         'xuc_local_store'        => 1,
      ] as $key => $value) {
         if (!isset($current_config[$key])) {
            Config::setConfigurationValues('plugin:xivo', [$key => $value]);
         }
      }
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   static function uninstall() {
      $config = new Config();
      $config->deleteByCriteria(['context' => 'plugin:xivo']);

      return true;
   }
}