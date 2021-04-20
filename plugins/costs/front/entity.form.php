<?php
/*
 -------------------------------------------------------------------------
 Costs plugin for GLPI
 Copyright (C) 2018 by the TICgal Team.

 https://github.com/ticgal/costs
 -------------------------------------------------------------------------

 LICENSE

 This file is part of the Costs plugin.

 Costs plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 Costs plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Costs. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   Costs
 @author    the TICgal team
 @copyright Copyright (c) 2018 TICgal team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://tic.gal
 @since     2018
 ----------------------------------------------------------------------
 */
include ('../../../inc/includes.php');

Session::haveRight("entity", UPDATE);

$Entity             = new Entity();
$PluginCostsEntity  = new PluginCostsEntity();

if (isset($_POST["update"])) {
   $PluginCostsEntity->updateCost($_POST['entities_id'], $_POST['fixed_cost'], $_POST['time_cost'], $_POST['cost_private']);
   Html::back();
}

Html::displayErrorAndDie("lost");