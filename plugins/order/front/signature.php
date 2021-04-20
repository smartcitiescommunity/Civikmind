<?php
/*
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2018 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");

$sign = array_key_exists('sign', $_GET) ? $_GET['sign'] : '';

// Avoid access to another directory or to files that does not match allowed extension
if (preg_match('/(\\\\|\/)/', $sign) !== 0
    || preg_match('/\.' . preg_quote(PLUGIN_ORDER_SIGNATURE_EXTENSION, '/') . '$/', $sign) === 0) {
   header('HTTP/1.1 403 Forbidden');
   exit();
}

$filename = PLUGIN_ORDER_SIGNATURE_DIR . $sign;

if (empty($sign) || !@is_file($filename)) {
   header('HTTP/1.1 404 Not Found');
   exit();
}

Toolbox::sendFile($filename, $sign);
