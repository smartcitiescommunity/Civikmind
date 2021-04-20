<?php

define('PLUGIN_PROJECTBRIDGE_VERSION', '2.3RC3');
define('PLUGIN_PROJECTBRIDGE_MIN_GLPI_VERSION', '9.4');
define('PLUGIN_PROJECTBRIDGE_MAX_GLPI_VERSION', '9.6');

if (!defined("PLUGIN_PROJECTBRIDGE_DIR")) {
   define('PLUGIN_PROJECTBRIDGE_DIR', Plugin::getPhpDir("projectbridge"));
}
if (!defined("PLUGIN_PROJECTBRIDGE_WEB_DIR")) {
   define("PLUGIN_PROJECTBRIDGE_WEB_DIR", Plugin::getWebDir("projectbridge"));
}

if (!class_exists('PluginProjectbridgeConfig')) {
    require_once(__DIR__.'/inc/config.class.php');
}

/**
 * Plugin description
 *
 * @return boolean
 */
function plugin_version_projectbridge() {
    return [
        'name' => 'Projectbridge',
        'version' => PLUGIN_PROJECTBRIDGE_VERSION,
        'author' => '<a href="http://www.probesys.com">Probesys</a>',
        'license' => 'GLPv3',
        'homepage'       => 'https://github.com/Probesys/glpi-plugins-projectbridge',
        'requirements'   => [
         'glpi'   => [
            'min' => PLUGIN_PROJECTBRIDGE_MIN_GLPI_VERSION,
            'max' => PLUGIN_PROJECTBRIDGE_MAX_GLPI_VERSION,
         ],
         'php'    => [
            'min' => '7.0'
         ] 
        ]
    ];
}

/**
 * Initialize plugin
 *
 * @return boolean
 */
function plugin_init_projectbridge() {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant'][PluginProjectbridgeConfig::NAMESPACE] = true;
    $PLUGIN_HOOKS['config_page'][PluginProjectbridgeConfig::NAMESPACE] = 'front/config.form.php';
    $PLUGIN_HOOKS['post_show_item'][PluginProjectbridgeConfig::NAMESPACE] = 'plugin_projectbridge_post_show_item';
    $PLUGIN_HOOKS['post_show_tab'][PluginProjectbridgeConfig::NAMESPACE] = 'plugin_projectbridge_post_show_tab';
    $PLUGIN_HOOKS['pre_item_update'][PluginProjectbridgeConfig::NAMESPACE] = [
        'Entity' => 'plugin_projectbridge_pre_entity_update',
        'Contract' => 'plugin_projectbridge_pre_contract_update',
        'Ticket' => 'plugin_projectbridge_ticket_update',
        'TicketTask' => 'plugin_projectbridge_ticketask_update',
    ];

    $PLUGIN_HOOKS['item_add'][PluginProjectbridgeConfig::NAMESPACE] = [
        'Contract' => 'plugin_projectbridge_contract_add',
        'TicketTask' => 'plugin_projectbridge_ticketask_add',
    ];

    $PLUGIN_HOOKS['use_massive_action'][PluginProjectbridgeConfig::NAMESPACE] = 1;

    $PLUGIN_HOOKS['helpdesk_menu_entry'][PluginProjectbridgeConfig::NAMESPACE] = false;
    $PLUGIN_HOOKS['menu_toadd'][PluginProjectbridgeConfig::NAMESPACE] = [
        'tools' => 'PluginProjectbridgeTask',
    ];
}

/**
 * Check if plugin prerequisites are met
 *
 * @return boolean
 */
function plugin_projectbridge_check_prerequisites() {
    $prerequisites_check_ok = false;

   try {
      if (version_compare(GLPI_VERSION, PLUGIN_PROJECTBRIDGE_MIN_GLPI_VERSION, '<')) {
          throw new Exception('This plugin requires GLPI >= ' . PLUGIN_PROJECTBRIDGE_MIN_GLPI_VERSION);
      }

         $prerequisites_check_ok = true;
   } catch (Exception $e) {
       echo $e->getMessage();
   }

    return $prerequisites_check_ok;
}

/**
 * Check if config is compatible with plugin
 *
 * @return boolean
 */
function plugin_projectbridge_check_config() {
    // nothing to do
    return true;
}
