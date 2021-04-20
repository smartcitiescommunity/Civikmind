<?php
/*
 -------------------------------------------------------------------------
 Screenshot
 Copyright (C) 2020-2021 by Curtis Conard
 https://github.com/cconard96/glpi-screenshot-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Screenshot.
 Screenshot is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Screenshot is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Screenshot. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_screenshot_install()
{
   if (empty($_SERVER['HTTPS']) && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
      echo 'This plugin requires GLPI be served over HTTPS';
      return false;
   }
   $migration = new PluginScreenshotMigration(PLUGIN_SCREENSHOT_VERSION);
   $migration->applyMigrations();
	return true;
}

function plugin_screenshot_uninstall()
{
	return true;
}