<?php

/*
 *

  This file is part of the Modifications plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 Stevenes Donato; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with itilcategorygroups. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   modifications
 @author    Stevenes Donato
 @copyright Copyright (c) 2020 Stevenes Donato
 @license   GPLv3
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/stdonato/glpi-modifications
 @link      http://www.glpi-project.org/
 @since     2018
 --------------------------------------------------------------------------
 */

/**
 * @name plugin_mod_install
 * @access public
 * @return boolean
 */

function plugin_mod_install(){
		
	//include('install.php');        	
	return true;
}

function plugin_mod_uninstall(){
		
	//include('uninstall.php');
	return true;
}

?>
