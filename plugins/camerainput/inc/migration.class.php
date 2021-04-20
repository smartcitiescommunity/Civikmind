<?php
/**
 * -------------------------------------------------------------------------
 *  Camera Input
 *  Copyright (C) 2020-2021 by Curtis Conard
 *  https://github.com/cconard96/glpi-camerainput-plugin
 *  -------------------------------------------------------------------------
 *  LICENSE
 *  This file is part of Camera Input.
 *  Camera Input is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  Camera Input is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  You should have received a copy of the GNU General Public License
 *  along with Camera Input. If not, see <http://www.gnu.org/licenses/>.
 *  --------------------------------------------------------------------------
 */

/**
 * Handles migrating between plugin versions
 */
class PluginCamerainputMigration
{
	private const BASE_VERSION = '1.0.0';

	/** @var Migration */
	protected $glpiMigration;

	/** @var DBmysql */
	protected $db;


	public function __construct(string $version)
	{
		global $DB;
		$this->glpiMigration = new Migration($version);
		$this->db = $DB;
	}


	public function applyMigrations()
	{
		$rc = new ReflectionClass($this);
		$otherMigrationFunctions = array_map(static function ($rm) use ($rc) {
		   return $rm->getShortName();
		}, array_filter($rc->getMethods(), static function ($m) {
		   return preg_match('/(?<=^apply_)(.*)(?=_migration$)/', $m->getShortName());
		}));

		if (count($otherMigrationFunctions)) {
		   // Map versions to functions
		   $versionMap = [];
		   foreach ($otherMigrationFunctions as $function) {
		      $ver = str_replace(['apply_', '_migration', '_'], ['', '', '.'], $function);
		      $versionMap[$ver] = $function;
		   }

		   // Sort semantically
		   uksort($versionMap, 'version_compare');

		   // Get last known recorded version. If none exists, assume this is 1.0.0 (start migration from beginning).
		   // Migrations should be replayable so nothing should be lost on multiple runs.
		   $lastKnownVersion = Config::getConfigurationValues('plugin:camerainput')['plugin_version'] ?? self::BASE_VERSION;

		   // Call each migration in order starting from the last known version
		   foreach ($versionMap as $version => $func) {
		      // Last known version is the same or greater than release version
		      if (version_compare($lastKnownVersion, $version, '<=')) {
		         $this->$func();
		         $this->glpiMigration->executeMigration();
		         if ($version !== self::BASE_VERSION) {
		            $this->setPluginVersionInDB($version);
		            $lastKnownVersion = $version;
		         }
		      }
		   }
		}
	}


	private function setPluginVersionInDB($version)
	{
		$this->db->updateOrInsert(Config::getTable(), [
		   'value'     => $version,
		   'context'   => 'plugin:camerainput',
		   'name'      => 'plugin_version'
		], [
		   'context'   => 'plugin:camerainput',
		   'name'      => 'plugin_version'
		]);
	}


	/**
	 * Apply the migrations for the base plugin version (1.0.0).
	 */
	private function apply_1_0_0_migration()
	{
	}
}
