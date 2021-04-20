<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_POST['survey_id']) || !isset($_POST['action'])) {
   exit();
}

global $CFG_GLPI;
$redirection = Plugin::getWebDir('satisfaction')."/front/survey.form.php?id=";

$translation = new PluginSatisfactionSurveyTranslation();

switch($_POST['action']){
   case 'GET':
      header("Content-Type: text/html; charset=UTF-8");
      Html::header_nocache();
      Session::checkLoginUser();
      $translation->showForm($_POST);
      Html::ajaxFooter();
      break;
   case 'NEW':
      $translation->newSurveyTranslation($_POST);
      Html::redirect($redirection.$_POST['survey_id']);
      break;
   case 'EDIT':
      $translation->editSurveyTranslation($_POST);
      Html::redirect($redirection.$_POST['survey_id']);
      break;
}