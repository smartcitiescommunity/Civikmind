<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z orthagh $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   geninventorynumber
 @author    the geninventorynumber plugin team
 @copyright Copyright (c) 2008-2017 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/pluginsGLPI/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2008
 ---------------------------------------------------------------------- */

include ('../../../inc/includes.php');

$config = new PluginGeninventorynumberConfig();
$plugin = new Plugin();
$config->getFromDB(1);
if ($plugin->isInstalled("geninventorynumber")
   && $plugin->isActivated("geninventorynumber")) {
   Html::header(__('Inventory number generation', 'geninventorynumber'),
                $_SERVER['PHP_SELF'], "tools", "plugins", "geninventorynumber");
   if (isset($_GET['glpi_tab'])) {
      $_SESSION['glpi_tabs']['plugingeninventorynumberconfig'] = $_GET['glpi_tab'];
      Html::redirect(Toolbox::getItemTypeFormURL($config->getType()));
   }
   $config->showTabsContent();
   Html::footer();
}
