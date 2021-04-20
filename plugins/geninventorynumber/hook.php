<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z orthagh $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   geninventorynumber
 @author    the geninventorynumber plugin team
 @copyright Copyright (c) 2008-2017 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/pluginsGLPI/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2008
---------------------------------------------------------------------- */

function plugin_geninventorynumber_postinit() {
   global $GENINVENTORYNUMBER_TYPES, $PLUGIN_HOOKS;

   foreach ($GENINVENTORYNUMBER_TYPES as $type) {
      $PLUGIN_HOOKS['pre_item_add']['geninventorynumber'][$type]
        = ['PluginGeninventorynumberGeneration', 'preItemAdd'];
      $PLUGIN_HOOKS['pre_item_update']['geninventorynumber'][$type]
        = ['PluginGeninventorynumberGeneration', 'preItemUpdate'];
   }
}

function plugin_geninventorynumber_MassiveActions($type) {
   global $GENINVENTORYNUMBER_TYPES;

   $actions = [];
   if (in_array($type, $GENINVENTORYNUMBER_TYPES)) {
      $fields = PluginGeninventorynumberConfigField::getConfigFieldByItemType($type);

      if (PluginGeninventorynumberConfigField::isActiveForItemType($type)) {
         if (Session::haveRight("plugin_geninventorynumber", CREATE)) {
            $actions['PluginGeninventorynumberGeneration'.
               MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_geninventorynumber_generate']
               = __('Generate inventory number', 'geninventorynumber');
         }
         if (Session::haveRight("plugin_geninventorynumber", UPDATE)) {
            $actions['PluginGeninventorynumberGeneration'.
               MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_geninventorynumber_overwrite']
              = __('Regenerate inventory number (overwrite)', 'geninventorynumber');
         }
      }
   }
   return $actions;
}

function plugin_geninventorynumber_install() {
   $php_dir = Plugin::getPhpDir('geninventorynumber');

   $migration = new Migration("0.85+1.0");
   include_once($php_dir . '/inc/config.class.php');
   include_once($php_dir . '/inc/profile.class.php');
   include_once($php_dir . '/inc/configfield.class.php');
   PluginGeninventorynumberConfig::install($migration);
   PluginGeninventorynumberProfile::install($migration);
   PluginGeninventorynumberConfigField::install($migration);
   return true;
}

function plugin_geninventorynumber_uninstall() {
   $php_dir = Plugin::getPhpDir('geninventorynumber');

   $migration = new Migration("0.85+1.0");
   include_once($php_dir . '/inc/config.class.php');
   include_once($php_dir . '/inc/profile.class.php');
   include_once($php_dir . '/inc/configfield.class.php');
   PluginGeninventorynumberConfig::uninstall($migration);
   PluginGeninventorynumberProfile::removeRightsFromSession();
   PluginGeninventorynumberProfile::uninstallProfile();
   PluginGeninventorynumberConfigField::uninstall($migration);
   return true;
}
