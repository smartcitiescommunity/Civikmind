<?php
/*
 * @version $Id: groups.php 148 2013-07-10 09:30:56Z yllen $
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
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (Session::haveRight(PluginUninstallProfile::$rightname, READ)) {
   switch ($_POST["id"]) {
      case 'old' :
         echo "<input type='hidden' name='groups_id' value='-1'>";
         echo Dropdown::EMPTY_VALUE;
         break;

      case 'set' :
         Group::dropdown(['value'       => $_POST["groups_id"],
                          'entity'      => $_POST["entities_id"],
                          'entity_sons' => $_POST["entity_sons"],
                          'emptylabel'  => __('None')]);
         break;
   }
}
