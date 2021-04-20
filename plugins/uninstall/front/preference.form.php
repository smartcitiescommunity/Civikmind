<?php
/*
 * @version $Id: preference.form.php 148 2013-07-10 09:30:56Z yllen $
 LICENSE

 This file is part of the uninstall plugin.

 Uninstall plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Uninstall plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with uninstall. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   uninstall
 @author    the uninstall plugin team
 @copyright Copyright (c) 2010-2013 Uninstall plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/uninstall
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ('../../../inc/includes.php');

// Save user preferences
if (isset ($_POST['update_user_preferences_uninstall'])) {
   $pref = new PluginUninstallPreference();
   foreach ($_POST["id"] as $prefid => $values) {
      $pref->update($values);
   }
   Html::back();
}
