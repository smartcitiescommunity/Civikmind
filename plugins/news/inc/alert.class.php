<?php
/*
 *
 -------------------------------------------------------------------------
 Plugin GLPI News
 Copyright (C) 2015 by teclib.
 http://www.teclib.com
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Plugin GLPI News.
 Plugin GLPI News is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Plugin GLPI News is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Plugin GLPI News. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginNewsAlert extends CommonDBTM {
   static $rightname = 'reminder_public';
   public $dohistory = true;

   const GENERAL = 1;
   const INFO    = 2;
   const WARNING = 3;
   const PROBLEM = 4;

   static function canDelete() {
      return self::canPurge();
   }

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return __('Alerts', 'news');
   }

   /**
    * @see CommonGLPI::defineTabs()
   **/
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong)
           ->addStandardTab('PluginNewsAlert_Target', $ong, $options)
           ->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   public function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'               => 1,
         'table'            => $this->getTable(),
         'field'            => 'name',
         'name'             => __('Name'),
         'datatype'         => 'itemlink',
         'itemlink_type'    => $this->getType(),
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 2,
         'table'            => $this->getTable(),
         'field'            => 'date_start',
         'name'             => __('Visibility start date'),
         'datatype'         => 'date',
      ];

      $tab[] = [
         'id'               => 3,
         'table'            => $this->getTable(),
         'field'            => 'date_end',
         'name'             => __('Visibility end date'),
         'datatype'         => 'date',
      ];

      $tab[] = [
         'id'               => 4,
         'table'            => 'glpi_entities',
         'field'            => 'completename',
         'name'             => __('Entity'),
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 5,
         'table'            => $this->getTable(),
         'field'            => 'is_recursive',
         'name'             => __('Recursive'),
         'datatype'         => 'bool',
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 6,
         'table'            => PluginNewsAlert_Target::getTable(),
         'field'            => 'items_id',
         'name'             => PluginNewsAlert_Target::getTypename(),
         'datatype'         => 'specific',
         'forcegroupby'     => true,
         'joinparams'       => ['jointype' => 'child'],
         'additionalfields' => ['itemtype'],
      ];

      $tab[] = [
         'id'               => 7,
         'table'            => $this->getTable(),
         'field'            => 'is_close_allowed',
         'name'             => __('Can close alert', 'news'),
         'datatype'         => 'bool',
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 8,
         'table'            => $this->getTable(),
         'field'            => 'is_displayed_onlogin',
         'name'             => __('Show on login page', 'news'),
         'datatype'         => 'bool',
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 9,
         'table'            => $this->getTable(),
         'field'            => 'is_displayed_onhelpdesk',
         'name'             => __('Show on helpdesk page', 'news'),
         'datatype'         => 'bool',
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 10,
         'table'            => $this->getTable(),
         'field'            => 'is_active',
         'name'             => __('Active'),
         'datatype'         => 'bool',
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 19,
         'table'            => $this->getTable(),
         'field'            => 'date_mod',
         'name'             => __('Last update'),
         'datatype'         => 'datetime',
         'massiveaction'    => false,
      ];

      $tab[] = [
         'id'               => 121,
         'table'            => $this->getTable(),
         'field'            => 'date_creation',
         'name'             => __('Creation date'),
         'datatype'         => 'datetime',
         'massiveaction'    => false,
      ];

      return $tab;
   }


   public static function findAllToNotify($params = []) {
      global $DB;

      $p['show_only_login_alerts']     = false;
      $p['show_only_central_alerts']   = false;
      $p['show_hidden_alerts']         = false;
      $p['show_only_helpdesk_alerts']  = false;
      $p['entities_id']                = false;
      foreach ($params as $key => $value) {
         $p[$key] = $value;
      }

      $alerts   = [];
      $today    = date('Y-m-d H:i:s');
      $table    = self::getTable();
      $utable   = PluginNewsAlert_User::getTable();
      $ttable   = PluginNewsAlert_Target::getTable();
      $hidstate = PluginNewsAlert_User::HIDDEN;
      $users_id = isset($_SESSION['glpiID'])? $_SESSION['glpiID']: -1;
      $group_u  = new Group_User();
      $fndgroup = [];
      if (isset($_SESSION['glpiID'])
          && $fndgroup_user = $group_u->find(['users_id' => $_SESSION['glpiID']])) {
         foreach ($fndgroup_user as $group) {
            $fndgroup[] = $group['groups_id'];
         }
         $fndgroup = implode(',', $fndgroup);
      }
      if (empty($fndgroup)) {
         $fndgroup = "-1";
      }

      // filters for query
      $targets_sql           = "";
      $login_sql             = "";
      $login_show_hidden_sql = " `$utable`.`id` IS NULL ";
      $entity_sql            = "";
      $show_helpdesk_sql     = '';
      $show_central_sql      = '';
      if (isset($_SESSION['glpiID']) && isset($_SESSION['glpiactiveprofile']['id'])) {
         $targets_sql = "AND (
                           `$ttable`.`itemtype` = 'Profile'
                           AND (
                              `$ttable`.`items_id` = ".$_SESSION['glpiactiveprofile']['id']."
                              OR `$ttable`.`items_id` = -1
                           )
                           OR `$ttable`.`itemtype` = 'Group'
                              AND `$ttable`.`items_id` IN ($fndgroup)
                           OR `$ttable`.`itemtype` = 'User'
                              AND `$ttable`.`items_id` = ".$_SESSION['glpiID']."
                        )";
      } else if ($p['show_only_login_alerts']) {
         $login_sql = " AND `$table`.`is_displayed_onlogin` = '1'";
      }

      if ($p['show_hidden_alerts']) {
         //dont show hidden alert if they should no longer be visible
         $login_show_hidden_sql = " `$utable`.`id` IS NOT NULL";
      }

      if ($p['show_only_central_alerts']) {
         //dont show central alert if they should no longer be visible
         $show_central_sql = " AND `$table`.`is_displayed_oncentral`='1'";
      }

      //If the alert must be displayed on helpdesk form : filter by ticket's entity
      //and not the current entity
      if ($p['show_only_helpdesk_alerts']) {
         $show_helpdesk_sql = " AND `$table`.`is_displayed_onhelpdesk`='1'";
      }
      if (!$p['show_only_login_alerts']) {
         $entity_sql = getEntitiesRestrictRequest("AND", $table, "", $p['entities_id'], true);
      }

      $query = "SELECT DISTINCT `$table`.`id`, `$table`.*
                  FROM `$table`
                  LEFT JOIN `$utable`
                     ON `$utable`.`plugin_news_alerts_id` = `$table`.`id`
                     AND `$utable`.`users_id` = $users_id
                     AND `$utable`.`state` = $hidstate
                  INNER JOIN `$ttable`
                     ON `$ttable`.`plugin_news_alerts_id` = `$table`.`id`
                  $targets_sql
                  WHERE ($login_show_hidden_sql $login_sql $show_central_sql $show_helpdesk_sql)
                     AND (`$table`.`date_start` < '$today'
                           OR `$table`.`date_start` = '$today'
                           OR `$table`.`date_start` IS NULL
                     )
                     AND (`$table`.`date_end` IS NULL
                           OR `$table`.`date_end` > '$today'
                           OR `$table`.`date_end` = '$today'
                     )
                  AND `is_deleted`='0' AND `is_active`='1'
                  $entity_sql";
      $iterator = $DB->request($query);
      if ($iterator->numrows() < 1) {
         return false;
      }
      foreach ($iterator as $data) {
         $alerts[] = $data;
      }

      return $alerts;
   }

   public static function getMenuContent() {
      $menu  = parent::getMenuContent();
      $menu['links']['search'] = PluginNewsAlert::getSearchURL(false);

      return $menu;
   }


   public function checkDate($datetime) {
      if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $datetime)) {
         $datetime = explode(" ", $datetime);
         list($year , $month , $day) = explode('-', $datetime[0]);
         return checkdate($month, $day, $year);
      }
      return false;
   }

   public function prepareInputForAdd($input) {
      $errors = [];

      if (!$input['name']) {
         array_push($errors, __('Please enter a name.', 'news'));
      }

      if (!$input['message']) {
         array_push($errors, __('Please enter a message.', 'news'));
      }

      if (!empty($input['date_start'])
          && !empty($input['date_end'])) {
         if (strtotime($input['date_end']) < strtotime($input['date_start'])) {
            array_push($errors, __('The end date must be greater than the start date.', 'news'));
         }
      }

      if ($errors) {
         Session::addMessageAfterRedirect(implode('<br />', $errors));
      }

      return $errors ? false : $input;
   }

   public function prepareInputForUpdate($input) {
      return $this->prepareInputForAdd($input);
   }

   function post_addItem() {
      $target = new PluginNewsAlert_Target;
      $target->add(['plugin_news_alerts_id' => $this->getID(),
                    'itemtype'              => 'Profile',
                    'items_id'              => -1]);
   }

   function getEmpty() {
      parent::getEmpty();
      $this->fields['is_close_allowed'] = 1;
   }

   public function showForm($ID, $options = []) {
      $this->initForm($ID, $options);

      $canedit = $this->can($ID, UPDATE);

      if ($this->getField('message') == NOT_AVAILABLE) {
         $this->fields['message'] = "";
      }

      $this->showFormHeader($options);

      echo "<tr  class='tab_bg_1'>";
      echo '<td style="width: 150px">' . __('Name') .'</td>';
      echo '<td colspan="3"><input name="name" type="text" value="'.Html::cleanInputText($this->getField('name')).'" style="width: 565px" /></td>';
      echo '</tr>';

      echo "<tr class='tab_bg_1'><td>".__('Active')."</td><td colspan='3'>";
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo "</td></tr>";

      echo '<tr>';
      echo '<td>' . __('Description') .'</td>';
      echo '<td colspan="3">';
      echo '<textarea name="message" rows="12" cols="80">'.$this->getField('message').'</textarea>';
      Html::initEditorSystem('message');
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td style="width: 150px">' . __("Visibility start date") .'</td>';
      echo '<td>';
      Html::showDateTimeField("date_start",
                              ['value'      => $this->fields["date_start"],
                               'timestep'   => 1,
                               'maybeempty' => true,
                               'canedit'    => $canedit]);
      echo '</td>';
      echo '<td style="width: 150px">' . __("Visibility end date") .'</td>';
      echo '<td>';
      Html::showDateTimeField("date_end",
                              ['value'      => $this->fields["date_end"],
                               'timestep'   => 1,
                               'maybeempty' => true,
                               'canedit'    => $canedit]);
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>' . __("Type (to add an icon before alert title)", 'news') .'</td>';
      echo '</td>';
      echo '<td>';
      Dropdown::showFromArray('type', self::getTypes(),
                              ['value'               => $this->fields['type'],
                               'display_emptychoice' => true]);
      echo '</td>';

      echo '<td>' . __("Can close alert", 'news') .'</td>';
      echo '</td>';
      echo '<td>';
      Dropdown::showYesNo('is_close_allowed', $this->fields['is_close_allowed']);
      echo '</td>';

      echo '</tr>';

      echo '<tr>';
      echo '<td>' . __("Show on login page", 'news') .'</td>';
      echo '</td>';
      echo '<td>';
      Dropdown::showYesNo('is_displayed_onlogin', $this->fields['is_displayed_onlogin']);
      echo '</td>';

      echo '<td>' . __("Show on helpdesk page", 'news') .'</td>';
      echo '</td>';
      echo '<td>';
      Dropdown::showYesNo('is_displayed_onhelpdesk', $this->fields['is_displayed_onhelpdesk']);
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>' . __("Show on central page", 'news') .'</td>';
      echo '</td>';
      echo '<td>';
      Dropdown::showYesNo('is_displayed_oncentral', $this->fields['is_displayed_oncentral']);
      echo '</td>';
      echo '</tr>';

      $this->showFormButtons($options);
   }

   static function displayOnCentral() {
      echo "<tr><th colspan='2'>";
      self::displayAlerts(['show_only_central_alerts' => true]);
      echo "</th></tr>";
   }

   static function displayOnLogin() {
      echo Html::css(Plugin::getPhpDir('news', false)."/css/styles.css");
      echo "<div class='plugin_news_alert-login'>";
      self::displayAlerts(['show_only_login_alerts' => true]);
      echo "</div>";
   }

   static function displayAlerts($params = []) {
      global $CFG_GLPI;

      $p['show_only_login_alerts']     = false;
      $p['show_only_central_alerts']      = false;
      $p['show_hidden_alerts']         = false;
      $p['show_only_helpdesk_alerts']  = false;
      $p['entities_id']                = false;
      foreach ($params as $key => $value) {
         $p[$key] = $value;
      }

      echo "<div class='plugin_news_alert-container'>";
      if ($alerts = self::findAllToNotify($p)) {
         foreach ($alerts as $alert) {
            $title      = $alert['name'];
            $type       = $alert['type'];
            $date_start = Html::convDateTime($alert['date_start']);
            $date_end   = Html::convDateTime($alert['date_end']);
            if (!empty($date_end)) {
               $date_end = " - $date_end";
            }
            $content    = Html::entity_decode_deep($alert['message']);
            echo "<div class='plugin_news_alert' data-id='".$alert['id']."'>";
            if ($alert['is_close_allowed'] && !$p['show_hidden_alerts']) {
               echo "<a class='plugin_news_alert-close'></a>";
            }
            if ($p['show_only_login_alerts']) {
               echo "<a class='plugin_news_alert-toggle'></a>";
            }
            echo "<div class='plugin_news_alert-title ui-widget-header'>";
            echo "<span class='plugin_news_alert-icon type_$type'></span>";
            echo "<div class='plugin_news_alert-title-content'>$title</div>";
            echo "<div class='plugin_news_alert-date'>$date_start$date_end</div>";
            echo "</div>";
            echo "<div class='plugin_news_alert-content ui-widget-content'>$content</div>";
            echo "</div>";
         }
      }

      $hidden_params = [
         'show_hidden_alerts'          => true,
         'show_only_login_alerts'      => false,
         'show_only_central_alerts'    => $p['show_only_central_alerts'],
         'show_only_helpdesk_alerts'   => $p['show_only_helpdesk_alerts'],
         'entities_id'                 => $p['entities_id']
      ];

      if (!$p['show_only_login_alerts']
         && $alerts = self::findAllToNotify($hidden_params)
          && !$p['show_hidden_alerts']) {
         echo "<div class='center'>";
         echo "<a href='".Plugin::getWebDir('news')."/front/hidden_alerts.php'>";
         echo __("You have hidden alerts valid for current date", 'news');
         echo "</a>";
         echo "</div>";
      }
      echo "</div>";

      if ($p['show_only_login_alerts']) {
         echo Html::script(Plugin::getPhpDir('news', false)."/js/news.js");
      }
   }

   static function getTypes() {
      return [self::GENERAL => __("General", 'news'),
              self::INFO    => __("Information", 'news'),
              self::WARNING => __("Warning", 'news'),
              self::PROBLEM => __("Problem", 'news')];
   }

   function cleanDBOnPurge() {
      $target = new PluginNewsAlert_Target();
      $target->deleteByCriteria(['plugin_news_alerts_id' => $this->getID()]);
   }

   static function preItemForm($params = []) {
      if (isset($params['item'])
          && $params['item'] instanceof CommonITILObject) {
         $item        = $params['item'];
         $itemtype    = get_class($item);
         $entities_id = isset($params['item']->fields['entities_id'])
            ? $params['item']->fields['entities_id']
            : false; // false to use current entity
         self::displayAlerts(['show_only_helpdesk_alerts'   => true,
                              'show_hidden_alerts'          => false,
                              'entities_id'                 => $entities_id
                             ]);
         echo "</br>";
      }
   }


   static function getIcon() {
      return "fas fa-bell";
   }
}
