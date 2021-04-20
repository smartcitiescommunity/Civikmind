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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginTasklistsTask_Comment
 */
class PluginTasklistsTask_Comment extends CommonDBTM {

   static function getTypeName($nb = 0) {
      return _n('Comment', 'Comments', $nb);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$item->canUpdateItem()) {
         return '';
      }

      $nb = 0;
      if ($_SESSION['glpishow_count_on_tabs']) {
         $where = [];
         if ($item->getType() == PluginTasklistsTask::getType()) {
            $where = [
               'plugin_tasklists_tasks_id' => $item->getID(),
               'language'                  => null
            ];
         } else {
            $where = [
               'plugin_tasklists_tasks_id' => $item->fields['plugin_tasklists_tasks_id'],
               'language'                  => $item->fields['language']
            ];
         }

         $nb = countElementsInTable(
            'glpi_plugin_tasklists_tasks_comments',
            $where
         );
      }
      return self::createTabEntry(self::getTypeName($nb), $nb);
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      self::showForItem($item, $withtemplate);
      return true;
   }

   /**
    * Show linked items of a task
    *
    * @param $item                     CommonDBTM object
    * @param $withtemplate    integer  withtemplate param (default 0)
    **/
   static function showForItem(CommonDBTM $item, $withtemplate = 0) {
      global $DB, $CFG_GLPI;

      $plugin_tasklists_tasks_id = null;
      $item_id                   = $item->getID();
      $item_type                 = $item::getType();
      if (isset($_GET["start"])) {
         $start = intval($_GET["start"]);
      } else {
         $start = 0;
      }

      // Total Number of comments
      if ($item->getType() == PluginTasklistsTask::getType()) {
         $where                     = [
            'plugin_tasklists_tasks_id' => $item->getID(),
            'language'                  => null
         ];
         $plugin_tasklists_tasks_id = $where['plugin_tasklists_tasks_id'];
      } else {
         $where                     = [
            'plugin_tasklists_tasks_id' => $item->fields['plugin_tasklists_tasks_id'],
            'language'                  => $item->fields['language']
         ];
         $plugin_tasklists_tasks_id = $where['plugin_tasklists_tasks_id'];
      }

      $task = new PluginTasklistsTask();
      $task->getFromDB($plugin_tasklists_tasks_id);

      $number = countElementsInTable(
         'glpi_plugin_tasklists_tasks_comments',
         $where
      );

      $entry = new PluginTasklistsTask();
      $entry->getFromDB($task->fields['id']);
      $cancomment = true;
      if ($cancomment) {
         echo "<div class='firstbloc'>";

         $lang = null;
         //         if ($item->getType() == PluginTasklistsTaskTranslation::getType()) {
         //            $lang = $item->fields['language'];
         //         }

         echo self::getCommentForm($plugin_tasklists_tasks_id, $lang);
         echo "</div>";
      }

      // No comments in database
      if ($number < 1) {
         $no_txt = __('No comments');
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>$no_txt</th></tr>";
         echo "</table>";
         echo "</div>";
         return;
      }

      // Output events
      echo "<div class='forcomments timeline_history'>";
      echo "<ul class='comments left'>";
      $comments = self::getCommentsForTaskItem($plugin_tasklists_tasks_id, $where['language']);

      $html = self::displayComments($comments, $cancomment);
      echo $html;

      echo "</ul>";
      echo "<script type='text/javascript'>
              $(function() {
                 var _bindForm = function(form) {
                     form.find('input[type=reset]').on('click', function(e) {
                        e.preventDefault();
                        form.remove();
                        $('.displayed_content').show();
                     });
                 };

                 $('.add_answer').on('click', function() {
                    var _this = $(this);
                    var _data = {
                       'plugin_tasklists_tasks_id': _this.data('plugin_tasklists_tasks_id'),
                       'answer'   : _this.data('id')
                    };

                    if (_this.data('language') != undefined) {
                       _data.language = _this.data('language');
                    }

                    if (_this.parents('.comment').find('#newcomment' + _this.data('id')).length > 0) {
                       return;
                    }

                    $.ajax({
                       url: '{$CFG_GLPI["root_doc"]}/plugins/tasklists/ajax/getTaskComment.php',
                       method: 'post',
                       cache: false,
                       data: _data,
                       success: function(data) {
                          var _form = $('<div class=\"newcomment\" id=\"newcomment'+_this.data('id')+'\">' + data + '</div>');
                          _bindForm(_form);
                          _this.parents('.h_item').after(_form);
                       },
                       error: function() { " .
           Html::jsAlertCallback(__('Contact your GLPI admin!'), __('Unable to load comment!')) . "
                       }
                    });
                 });

                 $('.edit_item').on('click', function() {
                    var _this = $(this);
                    var _data = {
                       'plugin_tasklists_tasks_id': _this.data('plugin_tasklists_tasks_id'),
                       'edit'     : _this.data('id')
                    };

                    if (_this.data('language') != undefined) {
                       _data.language = _this.data('language');
                    }

                    if (_this.parents('.comment').find('#editcomment' + _this.data('id')).length > 0) {
                       return;
                    }

                    $.ajax({
                       url: '{$CFG_GLPI["root_doc"]}/plugins/tasklists/ajax/getTaskComment.php',
                       method: 'post',
                       cache: false,
                       data: _data,
                       success: function(data) {
                          var _form = $('<div class=\"editcomment\" id=\"editcomment'+_this.data('id')+'\">' + data + '</div>');
                          _bindForm(_form);
                          _this
                           .parents('.displayed_content').hide()
                           .parent()
                           .append(_form);
                       },
                       error: function() { " .
           Html::jsAlertCallback(__('Contact your GLPI admin!'), __('Unable to load comment!')) . "
                       }
                    });
                 });


              });
            </script>";

      echo "</div>";
   }

   /**
    * Gat all comments for specified Task entry
    *
    * @param integer $plugin_tasklists_tasks_id Task entry ID
    * @param string  $lang Requested language
    * @param integer $parent Parent ID (defaults to 0)
    *
    * @return array
    */
   static public function getCommentsForTaskItem($plugin_tasklists_tasks_id, $lang, $parent = null) {
      global $DB;

      $where = [
         'plugin_tasklists_tasks_id' => $plugin_tasklists_tasks_id,
         'language'                  => $lang,
         'parent_comment_id'         => $parent
      ];

      $db_comments = $DB->request(
         'glpi_plugin_tasklists_tasks_comments',
         $where + ['ORDER' => 'id ASC']
      );

      $comments = [];
      foreach ($db_comments as $db_comment) {
         $db_comment['answers'] = self::getCommentsForTaskItem($plugin_tasklists_tasks_id, $lang, $db_comment['id']);
         $comments[]            = $db_comment;
      }

      return $comments;
   }

   /**
    * Display comments
    *
    * @param array   $comments Comments
    * @param boolean $cancomment Whether user can comment or not
    * @param integer $level Current level, defaults to 0
    *
    * @return string
    */
   static public function displayComments($comments, $cancomment, $level = 0) {
      $html = '';
      foreach ($comments as $comment) {
         $user = new User();
         $user->getFromDB($comment['users_id']);

         $html .= "<li class='comment" . ($level > 0 ? ' subcomment' : '') . "' id='taskcomment{$comment['id']}'>";
         $html .= "<div class='h_item left'>";
         if ($level === 0) {
            $html .= '<hr/>';
         }
         $html     .= "<div class='h_info'>";
         $html     .= "<div class='h_date'>" . Html::convDateTime($comment['date_creation']) . "</div>";
         $html     .= "<div class='h_user'>";
         $html     .= "<div class='tooltip_picture_border'>";
         $html     .= "<img class='user_picture' alt='' src='" .
                      User::getThumbnailURLForPicture($user->fields['picture']) . "'>";
         $html     .= "</div>";
         $html     .= "<span class='h_user_name'>";
         $userdata = getUserName($user->getID(), 2);
         $html     .= $user->getLink() . "&nbsp;";
         $html     .= Html::showToolTip($userdata["comment"],
                                        ['link' => $userdata['link'], 'display' => false]);
         $html     .= "</span>";
         $html     .= "</div>"; // h_user
         $html     .= "</div>"; //h_info

         $html .= "<div class='h_content ItilFollowup'>";
         $html .= "<div class='displayed_content'>";

         if ($cancomment) {
            if (Session::getLoginUserID() == $comment['users_id']) {
               $html .= "<span class='edit_item'
                  data-plugin_tasklists_tasks_id='{$comment['plugin_tasklists_tasks_id']}'
                  data-lang='{$comment['language']}'
                  data-id='{$comment['id']}'></span>";
            }
         }

         $html .= "<div class='item_content'>";
         $html .= "<p>";
         $html .= Toolbox::unclean_cross_side_scripting_deep($comment['comment']);
         $html .= "</p>";
         $html .= "</div>";
         $html .= "</div>"; // displayed_content

         if ($cancomment) {
            $html .= "<span class='add_answer' title='" . __('Add an answer') . "'
               data-plugin_tasklists_tasks_id='{$comment['plugin_tasklists_tasks_id']}'
               data-lang='{$comment['language']}'
               data-id='{$comment['id']}'></span>";
         }

         $html .= "</div>"; //end h_content
         $html .= "</div>";

         if (isset($comment['answers']) && count($comment['answers']) > 0) {

            $html .= "<input type='checkbox' id='toggle_{$comment['id']}'
                             class='toggle_comments' checked='checked'>";
            $html .= "<label for='toggle_{$comment['id']}' class='toggle_label'>&nbsp;</label>";
            $html .= "<ul>";
            $html .= self::displayComments($comment['answers'], $cancomment, $level + 1);
            $html .= "</ul>";
         }

         $html .= "</li>";
      }
      return $html;
   }

   /**
    * Get comment form
    *
    * @param integer       $plugin_tasklists_tasks_id PluginTasklistsTask item ID
    * @param string        $lang Related item language
    * @param false|integer $edit Comment id to edit, or false
    * @param false|integer $answer Comment id to answer to, or false
    *
    * @return string
    */
   static public function getCommentForm($plugin_tasklists_tasks_id, $lang = null, $edit = false, $answer = false) {
      $rand = mt_rand();

      $content = '';
      if ($edit !== false) {
         $comment = new PluginTasklistsTask();
         $comment->getFromDB($edit);
         $content = $comment->fields['comment'];
      }

      $html = '';
      $html .= "<form name='taskcomment_form$rand' id='taskcomment_form$rand'
                      class='comment_form' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

      $html .= "<table class='tab_cadre_fixe'>";

      $form_title = ($edit === false ? __('New comment') : __('Edit comment'));
      $html       .= "<tr class='tab_bg_2'><th colspan='3'>$form_title</th></tr>";

      $html .= "<tr class='tab_bg_1'><td><label for='comment'>" . __('Comment') . "</label>
         &nbsp;<span class='red'>*</span></td><td>";
      $html .= "<textarea name='comment' id='comment' required='required'>{$content}</textarea>";
      $html .= "</td><td class='center'>";

      $btn_text = _sx('button', 'Add');
      $btn_name = 'add';

      if ($edit !== false) {
         $btn_text = _sx('button', 'Edit');
         $btn_name = 'edit';
      }
      $html .= "<input type='submit' name='$btn_name' value='{$btn_text}' class='submit'>";
      if ($edit !== false || $answer !== false) {
         $html .= "<input type='reset' name='cancel' value='" . __('Cancel') . "' class='submit'>";
      }

      $html .= "<input type='hidden' name='plugin_tasklists_tasks_id' value='$plugin_tasklists_tasks_id'>";
      if ($lang !== null) {
         $html .= "<input type='hidden' name='language' value='$lang'>";
      }
      if ($answer !== false) {
         $html .= "<input type='hidden' name='parent_comment_id' value='{$answer}'/>";
      }
      if ($edit !== false) {
         $html .= "<input type='hidden' name='id' value='{$edit}'/>";
      }
      $html .= "</td></tr>";
      $html .= "</table>";
      $html .= Html::closeForm(false);
      return $html;
   }

   function prepareInputForAdd($input) {
      if (!isset($input["users_id"])) {
         $input["users_id"] = 0;
         if ($uid = Session::getLoginUserID()) {
            $input["users_id"] = $uid;
         }
      }

      return $input;
   }
}
