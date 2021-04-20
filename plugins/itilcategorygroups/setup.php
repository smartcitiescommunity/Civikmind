<?php
/*
 * @version $Id: setup.php 19 2012-06-27 09:19:05Z walid $
 LICENSE

  This file is part of the itilcategorygroups plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with itilcategorygroups. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   itilcategorygroups
 @author    the itilcategorygroups plugin team
 @copyright Copyright (c) 2010-2011 itilcategorygroups plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/itilcategorygroups
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

define ('PLUGIN_ITILCATEGORYGROUPS_VERSION', '2.4.1');

// Minimal GLPI version, inclusive
define("PLUGIN_ITILCATEGORYGROUPS_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_ITILCATEGORYGROUPS_MAX_GLPI", "9.6");

function plugin_init_itilcategorygroups() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['itilcategorygroups'] = true;

   $plugin = new Plugin();
   if ($plugin->isInstalled('itilcategorygroups')
       && $plugin->isActivated('itilcategorygroups')) {

      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['config_page']['itilcategorygroups'] = 'front/category.php';
      }

      Plugin::registerClass('PluginItilcategorygroupsCategory',
                            ['forwardentityfrom' => 'ITILCategory']);
      Plugin::registerClass('PluginItilcategorygroupsGroup_Level',
                            ['addtabon' => 'Group']);

      if (Session::haveRight('config', READ)) {
         // add to 'Admin' menu :
         $PLUGIN_HOOKS["menu_toadd"]['itilcategorygroups'] = ['admin' => 'PluginItilcategorygroupsMenu'];

         // other hook :
         $PLUGIN_HOOKS['pre_item_update']['itilcategorygroups'] = ['Group' => 'plugin_pre_item_update_itilcategorygroups'];
      }
      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['submenu_entry']['itilcategorygroups']['options']['PluginItilcategorygroupsCategory']['links']['add']
            = '/' . Plugin::getWebDir('itilcategorygroups', false) . '/front/category.form.php';
      }

      $PLUGIN_HOOKS['add_javascript']['itilcategorygroups'] = ['scripts/function.js',
                                                               'scripts/filtergroups.js.php',
                                                               'scripts/multiple_group.js'];
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_itilcategorygroups() {
   return [
      'name'           => __('ItilCategory Groups', 'itilcategorygroups'),
      'version'        => PLUGIN_ITILCATEGORYGROUPS_VERSION,
      'author'         => "<a href='http://www.teclib.com'>TECLIB'</a>",
      'homepage'       => 'https://github.com/pluginsGLPI/itilcategorygroups',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_ITILCATEGORYGROUPS_MIN_GLPI,
            'max' => PLUGIN_ITILCATEGORYGROUPS_MAX_GLPI,
          ]
       ]
   ];
}
