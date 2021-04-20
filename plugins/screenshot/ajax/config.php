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

include ('../../../inc/includes.php');

header("Content-Type: application/json; charset=UTF-8", true);

$plugin = new Plugin();
if (!$plugin->isActivated('screenshot')) {
   Html::displayNotFoundError();
}

Html::header_nocache();

Session::checkLoginUser();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
   // Bad request method
   die(405);
}

$config = Config::getConfigurationValues('plugin:screenshot');
echo json_encode($config, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);