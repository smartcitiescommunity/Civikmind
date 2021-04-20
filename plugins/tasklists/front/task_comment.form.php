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

use Glpi\Event;

include('../../../inc/includes.php');

Session::checkLoginUser();

$comment = new PluginTasklistsTask_Comment();
if (!isset($_POST['plugin_tasklists_tasks_id'])) {
   $message = __('Mandatory fields are not filled!');
   Session::addMessageAfterRedirect($message, false, ERROR);
   Html::back();
}
$task = new PluginTasklistsTask();
$task->getFromDB($_POST['plugin_tasklists_tasks_id']);
//if (!$task->canComment()) {
//    Html::displayRightError();
//}

if (isset($_POST["add"])) {
   if (!isset($_POST['plugin_tasklists_tasks_id']) || !isset($_POST['comment'])) {
      $message = __('Mandatory fields are not filled!');
      Session::addMessageAfterRedirect($message, false, ERROR);
      Html::back();
   }

   if ($newid = $comment->add($_POST)) {
      Session::addMessageAfterRedirect(
         "<a href='#taskcomment$newid'>" . __('Your comment has been added') . "</a>",
         false,
         INFO
      );
   }
   Html::back();
}

if (isset($_POST["edit"])) {
   if (!isset($_POST['plugin_tasklists_tasks_id']) || !isset($_POST['id']) || !isset($_POST['comment'])) {
      $message = __('Mandatory fields are not filled!');
      Session::addMessageAfterRedirect($message, false, ERROR);
      Html::back();
   }

   $comment->getFromDB($_POST['id']);
   $data = array_merge($comment->fields, $_POST);
   if ($comment->update($data)) {
      Session::addMessageAfterRedirect(
         "<a href='#taskcomment{$comment->getID()}'>" . __('Your comment has been edited') . "</a>",
         false,
         INFO
      );
   }
   Html::back();
}

Html::displayErrorAndDie("lost");
