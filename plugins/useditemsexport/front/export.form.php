<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of useditemsexport.
 *
 * useditemsexport is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * useditemsexport is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright © 2015 - 2018 Teclib'
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/useditemsexport
 * @link      https://pluginsglpi.github.io/useditemsexport/
 * -------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$PluginUseditemsexportExport = new PluginUseditemsexportExport();

if (isset($_REQUEST['generate'])) {

   if ($PluginUseditemsexportExport::generatePDF($_POST['users_id'])) {
      Session::addMessageAfterRedirect(__('PDF successfully generated.', 'useditemsexport'), true);
      Html::back();
   }
}

if (isset($_REQUEST["purgeitem"])) {

   foreach ($_POST["useditemsexport"] as $key => $val) {
      $input = ['id' => $key];
      if ($val == 1) {
         $PluginUseditemsexportExport->delete($input, true);
      }
   }
   Html::back();
}
