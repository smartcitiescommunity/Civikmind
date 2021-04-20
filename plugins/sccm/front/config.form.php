<?php
/**
 * ------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of SCCM plugin.
 *
 * SCCM plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * SCCM plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ------------------------------------------------------------------------
 * @author    François Legastelois <flegastelois@teclib.com>
 * @copyright Copyright (C) 2014-2018 by Teclib' and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * @link      https://pluginsglpi.github.io/sccm/
 * ------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
require_once('../inc/config.class.php');

Session::haveRight("config", UPDATE);

Html::header(__("Setup - SCCM", "sccm"), $_SERVER["PHP_SELF"],
             "plugins", "sccm", "configuration");

$PluginSccmConfig = new PluginSccmConfig();

if (isset($_POST["update"])) {
   $PluginSccmConfig->update($_POST);

    $sccmDB = new PluginSccmSccmdb();
   if ($sccmDB->connect()) {
      Session::addMessageAfterRedirect("Connexion réussie !.", false, INFO, false);
   } else {
      Session::addMessageAfterRedirect("Connexion incorrecte.", false, ERROR, false);
   }


   Html::back();
}

$PluginSccmConfig->showConfigForm($PluginSccmConfig);

Html::footer();