<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::checkLoginUser();

if (isset($_GET["popup"])) {
   $_SESSION["glpipopup"]["name"] = $_GET["popup"];
}

if (isset($_SESSION["glpipopup"]["name"])) {
   switch ($_SESSION["glpipopup"]["name"]) {
      case "test_rule" :
         Html::popHeader(__('Test'), $_SERVER['PHP_SELF']);
         include GLPI_ROOT . "/front/rule.test.php";
         break;

      case "test_all_rules" :
         Html::popHeader(__('Test rules engine'), $_SERVER['PHP_SELF']);
         include GLPI_ROOT . "/front/rulesengine.test.php";
         break;

      case "show_cache" :
         Html::popHeader(__('Cache information'), $_SERVER['PHP_SELF']);
         include GLPI_ROOT . "/front/rule.cache.php";
         break;
   }
   echo "<div class='center'><br><a href='javascript:window.close()'>".__('Close')."</a>";
   echo "</div>";
   Html::popFooter();
}

