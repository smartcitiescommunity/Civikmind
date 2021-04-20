<?php

/*
 -------------------------------------------------------------------------
 Screenshot
 Copyright (C) 2020-2021 by Curtis Conard
 https://github.com/cconard96/glpi-screenshot-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Screenshot.
 Screenshot is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Screenshot is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Screenshot. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginScreenshotProfile extends CommonGLPI
{
   public static $rightname = "config";

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      return self::createTabEntry(__('Screenshot', 'screenshot'));
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      $profile = new self();
      $profile->showForm($item->getID());
      return true;
   }

   public function showForm($profiles_id = 0, $openform = true, $closeform = true)
   {
      if (!self::canView()) {
         return false;
      }

      echo "<div class='spaced'>";
      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $can_edit = Session::haveRight(self::$rightname, UPDATE);
      if ($openform && $can_edit) {
         echo "<form method='post' action='" . $profile::getFormURL() . "'>";
      }

      $matrix_options = ['canedit' => $can_edit,
         'default_class' => 'tab_bg_2'];
      $rights = [
         [
            'itemtype'  => PluginScreenshotScreenshot::class,
            'label'     => __('Screen recordings', 'screenshot'),
            'field'     => 'plugin_screenshot_recording',
            'rights'    => [
               CREATE   => __('Create')
            ]
         ]
      ];
      $matrix_options['title'] = __('Screenshot', 'screenshot');
      $profile->displayRightsChoiceMatrix($rights, $matrix_options);

      if ($can_edit && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo '</div>';
   }
}