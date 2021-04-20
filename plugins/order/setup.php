<?php
/*
 -------------------------------------------------------------------------
 {NAME} plugin for GLPI
 Copyright (C) 2011-2017 by the Order Development Team.

 https://github.com/pluginsGLPI/order
 -------------------------------------------------------------------------

 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

define('PLUGIN_ORDER_VERSION', '2.6.0');

// Minimal GLPI version, inclusive
define("PLUGIN_ORDER_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_ORDER_MAX_GLPI", "9.6");

if (!defined('PLUGIN_ORDER_DIR')) {
   define("PLUGIN_ORDER_DIR", Plugin::getPhpDir('order'));
}

if (!defined('PLUGIN_ORDER_TEMPLATE_DIR')) {
   define("PLUGIN_ORDER_TEMPLATE_DIR", GLPI_PLUGIN_DOC_DIR."/order/templates/");
}
if (!defined('PLUGIN_ORDER_SIGNATURE_DIR')) {
   define("PLUGIN_ORDER_SIGNATURE_DIR", GLPI_PLUGIN_DOC_DIR."/order/signatures/");
}
if (!defined('PLUGIN_ORDER_TEMPLATE_CUSTOM_DIR')) {
   define("PLUGIN_ORDER_TEMPLATE_CUSTOM_DIR", GLPI_PLUGIN_DOC_DIR."/order/generate/");
}
if (!defined('PLUGIN_ORDER_TEMPLATE_LOGO_DIR')) {
   define("PLUGIN_ORDER_TEMPLATE_LOGO_DIR", GLPI_PLUGIN_DOC_DIR."/order/logo/");
}

if (!defined('PLUGIN_ORDER_TEMPLATE_EXTENSION')) {
   define("PLUGIN_ORDER_TEMPLATE_EXTENSION", "odt");
}
if (!defined('PLUGIN_ORDER_SIGNATURE_EXTENSION')) {
   define("PLUGIN_ORDER_SIGNATURE_EXTENSION", "png");
}
global $CFG_GLPI;
if (!defined('PLUGIN_ORDER_NUMBER_STEP')) {
   define("PLUGIN_ORDER_NUMBER_STEP", 1 / pow(10, $CFG_GLPI["decimal_number"]));
}

// Autoload
if (!defined('PCLZIP_TEMPORARY_DIR')) {
   define('PCLZIP_TEMPORARY_DIR', GLPI_DOC_DIR . '/_tmp/pclzip');
}
include_once PLUGIN_ORDER_DIR . "/vendor/autoload.php";


/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_order() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $ORDER_TYPES;

   Plugin::registerClass('PluginOrderProfile');
   $PLUGIN_HOOKS['csrf_compliant']['order'] = true;

   /* Init current profile */
   $PLUGIN_HOOKS['change_profile']['order'] = ['PluginOrderProfile', 'initProfile'];

   $plugin = new Plugin();
   if ($plugin->isActivated('order')) {
      $PLUGIN_HOOKS['migratetypes']['order'] = 'plugin_order_migratetypes';

      $PLUGIN_HOOKS['assign_to_ticket']['order'] = true;

      //Itemtypes in use for an order
      $ORDER_TYPES = [
         'Computer',
         'Monitor',
         'NetworkEquipment',
         'Peripheral',
         'Printer',
         'Phone',
         'ConsumableItem',
         'CartridgeItem',
         'Contract',
         'PluginOrderOther',
         'SoftwareLicense',
         'Certificate',
         'Rack',
         'Enclosure',
         'Pdu',
      ];

      $CFG_GLPI['plugin_order_types'] = $ORDER_TYPES;

      $PLUGIN_HOOKS['pre_item_purge']['order'] = [
         'Profile'          => ['PluginOrderProfile', 'purgeProfiles'],
         'DocumentCategory' => ['PluginOrderDocumentCategory', 'purgeItem'],
      ];

      $PLUGIN_HOOKS['pre_item_update']['order'] = [
         'Infocom'  => ['PluginOrderOrder_Item', 'updateItem'],
         'Contract' => ['PluginOrderOrder_Item', 'updateItem'],
      ];
      $PLUGIN_HOOKS['item_add']['order'] = [
         'Document' => ['PluginOrderOrder', 'addDocumentCategory']
      ];

      include_once(PLUGIN_ORDER_DIR . "/inc/order_item.class.php");
      foreach (PluginOrderOrder_Item::getClasses(true) as $type) {
         $PLUGIN_HOOKS['item_purge']['order'][$type] = 'plugin_item_purge_order';
      }

      Plugin::registerClass('PluginOrderOrder', [
         'document_types'              => true,
         'unicity_types'               => true,
         'notificationtemplates_types' => true,
         'helpdesk_visible_types'      => true,
         'ticket_types'                => true,
         'contract_types'              => true,
         'linkuser_types'              => true,
         'addtabon'                    => ['Budget']
      ]);

      Plugin::registerClass('PluginOrderReference', ['document_types' => true]);
      Plugin::registerClass('PluginOrderProfile', ['addtabon' => ['Profile']]);

      $values['notificationtemplates_types'] = true;
      //If the new infocom display hook (introduced in 9.1) is available, use it !
      if (method_exists('Infocom', 'addPluginInfos')) {
         $PLUGIN_HOOKS['infocom']['order'] = ['PluginOrderOrder_Item', 'showForInfocom'];
      } else {
         $values['addtabon'] = PluginOrderOrder_Item::getClasses(true);
      }
      Plugin::registerClass('PluginOrderOrder_Item', $values);

      if (PluginOrderOrder::canView()) {
         Plugin::registerClass('PluginOrderDocumentCategory', ['addtabon' => ['DocumentCategory']]);
         Plugin::registerClass('PluginOrderOrder_Supplier', ['addtabon' => ['Supplier']]);
         Plugin::registerClass('PluginOrderPreference', ['addtabon' => ['Preference']]);
      }

      /*if glpi is loaded */
      if (Session::getLoginUserID()) {
         $PLUGIN_HOOKS['add_css']['order'][] = 'order.css';

         /* link to the config page in plugins menu */
         if (Session::haveRight("config", UPDATE)) {
            $PLUGIN_HOOKS['config_page']['order'] = 'front/config.form.php';
         }

         if (PluginOrderOrder::canView()
            || PluginOrderReference::canView()
            || PluginOrderBill::canView()) {
            $PLUGIN_HOOKS['menu_toadd']['order']['management'] = 'PluginOrderMenu';
         }

         $PLUGIN_HOOKS['assign_to_ticket']['order'] = true;
         $PLUGIN_HOOKS['use_massive_action']['order'] = 1;
         $PLUGIN_HOOKS['plugin_datainjection_populate']['order'] = "plugin_datainjection_populate_order";
      }
   }
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_order() {
   return [
      'name'           => __("Orders management", "order"),
      'version'        => PLUGIN_ORDER_VERSION,
      'author'         => 'The plugin order team',
      'homepage'       => 'https://github.com/pluginsGLPI/order',
      'license'        => 'GPLv2+',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_ORDER_MIN_GLPI,
            'max' => PLUGIN_ORDER_MAX_GLPI,
            'dev' => true, //Required to allow 9.2-dev
         ]
      ]
   ];
}


/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_order_check_prerequisites() {
   if (!is_readable(__DIR__ . '/vendor/autoload.php') || !is_file(__DIR__ . '/vendor/autoload.php')) {
      echo "Run composer install --no-dev in the plugin directory<br>";
      return false;
   }

   return true;
}
