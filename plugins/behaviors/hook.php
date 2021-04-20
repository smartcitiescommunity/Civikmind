<?php
/**
 * @version $Id: hook.php 338 2021-03-30 12:36:31Z yllen $
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2021 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
 */


function plugin_behaviors_install() {

   $migration = new Migration(220);

   // No autoload when plugin is not activated
   require 'inc/config.class.php';
   PluginBehaviorsConfig::install($migration);

   $migration->executeMigration();

   return true;
}


function plugin_behaviors_uninstall() {

   // No autoload when plugin is not activated
   require 'inc/config.class.php';

   $migration = new Migration(220);

   PluginBehaviorsConfig::uninstall($migration);

   $migration->executeMigration();

   return true;
}
