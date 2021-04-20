<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Tasklists plugin for GLPI
 Copyright (C) 2003-2016 by the Tasklists Development Team.

 https://github.com/InfotelGLPI/tasklists
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Tasklists.

 Tasklists is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Tasklists is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Tasklists. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include("../../../inc/includes.php");

Session::checkLoginUser();

Html::header_nocache();
header("Content-Type: text/html; charset=UTF-8");

//Html::requireJs('tinymce');
echo "<script type='text/javascript'  src='../../../public/lib/tinymce.js'></script>";

if (isset($_GET['newContext'])) {
   $options = [
      'from_edit_ajax' => true,

      'withtemplate' => 0
   ];
   $task    = new PluginTasklistsTaskState();
   $task->showForm(0, $options);

}
Html::ajaxFooter();