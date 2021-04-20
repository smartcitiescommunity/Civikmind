<?php
/*
 -------------------------------------------------------------------------
 advancedplanning plugin for GLPI
 Copyright (C) 2019 by the advancedplanning Development Team.

 https://github.com/pluginsGLPI/advancedplanning
 -------------------------------------------------------------------------

 LICENSE

 This file is part of advancedplanning.

 advancedplanning is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 advancedplanning is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with advancedplanning. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_advancedplanning_install() {
   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_advancedplanning_uninstall() {
   return true;
}

function testhook() {
   return "resourceTimeline";
}