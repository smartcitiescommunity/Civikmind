<?php
/**
 -------------------------------------------------------------------------
 oauthimap plugin for GLPI
 Copyright (C) 2018-2020 by the oauthimap Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of oauthimap.

 oauthimap is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 oauthimap is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with oauthimap. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$application   = new PluginOauthimapApplication();
$authorization = new PluginOauthimapAuthorization();

$application_id = $_SESSION[PluginOauthimapApplication::getForeignKeyField()] ?? null;

$success = false;
if (array_key_exists('error', $_GET) && !empty($_GET['error'])
    || array_key_exists('error_description', $_GET) && !empty($_GET['error_description'])) {
   // Got an error, probably user denied access
   Session::addMessageAfterRedirect(
      sprintf(__('Authorization failed with error: %s', 'oauthimap'), $_GET['error_description'] ?? $_GET['error']),
      false,
      ERROR
   );
} else if ($application_id === null
    || !array_key_exists('state', $_GET)
    || !array_key_exists('oauth2state', $_SESSION)
    || $_GET['state'] !== $_SESSION['oauth2state']) {
   Session::addMessageAfterRedirect(__('Unable to verify authorization code', 'oauthimap'), false, ERROR);
} else if (!array_key_exists('code', $_GET)) {
   Session::addMessageAfterRedirect(__('Unable to get authorization code', 'oauthimap'), false, ERROR);
} else if (!$authorization->createFromCode($application_id, $_GET['code'])) {
   Session::addMessageAfterRedirect(__('Unable to save authorization code', 'oauthimap'), false, ERROR);
} else {
   $success = true;
}

$callback_callable = $_SESSION['plugin_oauthimap_callback_callable'] ?? null;

if (is_callable($callback_callable)) {
   $callback_params = $_SESSION['plugin_oauthimap_callback_params'] ?? [];
   call_user_func_array($callback_callable, [$success, $authorization, $callback_params]);
}

// Redirect to application form if callback action does not exit yet
if ($application->getFromDB($application_id)) {
   Html::redirect($application->getLinkURL());
}

Html::displayErrorAndDie('lost');
