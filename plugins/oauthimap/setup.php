<?php
/**
 -------------------------------------------------------------------------
 oauthimap plugin for GLPI
 Copyright (C) 2018-2020 by the oauthimap Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of oauthimap.

 oauthimap is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 oauthimap is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with oauthimap. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_OAUTHIMAP_VERSION', '1.1.0');

// Minimal GLPI version, inclusive
define('PLUGIN_OAUTHIMAP_MIN_GLPI', '9.5');
// Maximum GLPI version, exclusive
define('PLUGIN_OAUTHIMAP_MAX_GLPI', '9.6');

define('PLUGIN_OAUTHIMAP_ROOT', Plugin::getPhpDir('oauthimap'));

use GlpiPlugin\Oauthimap\MailCollectorFeature;

function plugin_init_oauthimap() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['oauthimap'] = true;

   $plugin = new Plugin();
   if ($plugin->isInstalled('oauthimap') && $plugin->isActivated('oauthimap')) {
      include_once(__DIR__ . '/vendor/autoload.php');

      // Config page: redirect to dropdown page
      $PLUGIN_HOOKS['config_page']['oauthimap'] = 'front/config.form.php';

      // Menu link
      $PLUGIN_HOOKS['menu_toadd']['oauthimap'] = [
         'config' => 'PluginOauthimapApplication',
      ];

      // Secured fields that are encrypted
      $PLUGIN_HOOKS['secured_fields']['oauthimap'] = [
         PluginOauthimapApplication::getTableField('client_secret'),
         PluginOauthimapApplication::getTableField('code'),
         PluginOauthimapApplication::getTableField('token'),
      ];

      // MailCollector hooks
      $PLUGIN_HOOKS['mail_server_protocols']['oauthimap'] = function (array $additionnal_protocols) {
         return array_merge($additionnal_protocols, MailCollectorFeature::getMailProtocols());
      };
      $PLUGIN_HOOKS['post_item_form']['oauthimap'] = [MailCollectorFeature::class, 'alterMailCollectorForm'];
      $PLUGIN_HOOKS['pre_item_update']['oauthimap'] = [
         'MailCollector' => [MailCollectorFeature::class, 'forceMailCollectorUpdate'],
      ];
      $PLUGIN_HOOKS['item_add']['oauthimap'] = [
         'MailCollector' => [MailCollectorFeature::class, 'handleMailCollectorSaving'],
      ];
      $PLUGIN_HOOKS['item_update']['oauthimap'] = [
         'MailCollector' => [MailCollectorFeature::class, 'handleMailCollectorSaving'],
      ];
   }
}

function plugin_version_oauthimap() {
   return [
      'name'           => __('Oauth IMAP', 'oauthimap'),
      'version'        => PLUGIN_OAUTHIMAP_VERSION,
      'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
      'license'        => 'GPL v2+',
      'homepage'       => 'http://www.teclib.com',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_OAUTHIMAP_MIN_GLPI,
            'max' => PLUGIN_OAUTHIMAP_MAX_GLPI,
         ]
      ]
   ];
}

function plugin_oauthimap_check_prerequisites() {
   if (!is_file(__DIR__ . '/vendor/autoload.php') || !is_readable(__DIR__ . '/vendor/autoload.php')) {
      echo __('Run "composer install --no-dev" in the plugin directory.', 'oauthimap');
      return false;
   }

   return true;
}
