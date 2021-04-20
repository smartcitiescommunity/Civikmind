<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginActualtimeConfig
 */
class PluginActualtimeConfig extends CommonDBTM {

   static $rightname = 'config';
   static private $_config = null;

   /**
    * @param bool $update
    *
    * @return PluginActualtimeConfig
    */
   static function getConfig($update = false) {

      if (!isset(self::$_config)) {
         self::$_config = new self();
      }
      if ($update) {
         self::$_config->getFromDB(1);
      }
      return self::$_config;
   }

   /**
    * PluginActualtimeConfig constructor.
    */
   function __construct() {
      global $DB;

      if ($DB->tableExists($this->getTable())) {
         $this->getFromDB(1);
      }
   }

   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }


   static function canView() {
      return Session::haveRight('config', READ);
   }

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {
      return __("Task timer configuration", "actualtime");
   }

   function showForm() {
      $rand = mt_rand();

      $this->getFromDB(1);
      $this->showFormHeader();

      echo "<input type='hidden' name='id' value='1'>";

      $values = [
         0 => __('In Standard interface only (default)', 'actualtime'),
         1 => __('Both in Standard and Helpdesk interfaces', 'actualtime'),
      ];
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Enable timer on tasks", "actualtime") . "</td><td>";
      Dropdown::showFromArray(
         'displayinfofor',
         $values,
         [
            'value' => $this->fields['displayinfofor']
         ]
      );
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Display pop-up window with current running timer", "actualtime") . "</td><td>";
      Dropdown::showYesNo('showtimerpopup', $this->showTimerPopup(), -1);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Display actual time in closed task box ('Processing ticket' list)", "actualtime") . "</td><td>";
      Dropdown::showYesNo('showtimerinbox', $this->showTimerInBox(), -1);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' name='optional$rand'>";
      echo "<td>" . __("Automatically open new created tasks", "actualtime") . "</td><td>";
      Dropdown::showYesNo('autoopennew', $this->autoOpenNew(), -1);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' name='optional$rand'>";
      echo "<td>" . __("Automatically open task with timer running", "actualtime") . "</td><td>";
      Dropdown::showYesNo('autoopenrunning', $this->autoOpenRunning(), -1);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' name='optional$rand'>";
      echo "<td>" . __("Automatically update the duration", "actualtime") . "</td><td>";
      Dropdown::showYesNo('autoupdate_duration', $this->autoUpdateDuration(), -1);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      $this->showFormButtons(['candel'=>false]);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType()=='Config') {
            return __("Actual time", "actualtime");
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType()=='Config') {
         $instance = self::getConfig();
         $instance->showForm();
      }
      return true;
   }

   /**
    * Is displaying timer pop-up on every page enabled in plugin settings?
    *
    * @return boolean
    */
   function showTimerPopup() {
      return ($this->fields['showtimerpopup'] ? true : false);
   }

   /**
    * Is actual time information (timers) shown also in Helpdesk interface?
    *
    * @return boolean
    */
   function showInHelpdesk() {
      return ($this->fields['displayinfofor'] == 1);
   }

   /**
    * Is timer shown in closed task box at 'Actions historical' page?
    *
    * @return boolean
    */
   function showTimerInBox() {
      return ($this->fields['showtimerinbox'] ? true : false);
   }

   /**
    * Auto open the form for the task that was just created (new tasks)?
    *
    * @return boolean
    */
   function autoOpenNew() {
      return ($this->fields['autoopennew'] ? true : false);
   }

   /**
    * Auto open the form for the task with a currently running timer
    * when listing tickets' tasks?
    *
    * @return boolean
    */
   function autoOpenRunning() {
      return ($this->fields['autoopenrunning'] ? true : false);
   }

   function autoUpdateDuration(){
    return $this->fields['autoupdate_duration'];
   }

   static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();
      if (! $DB->tableExists($table)) {

         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS $table (
                      `id` int(11) NOT NULL auto_increment,
                      `displayinfofor` smallint NOT NULL DEFAULT 0,
                      `showtimerpopup` boolean NOT NULL DEFAULT true,
                      `showtimerinbox` boolean NOT NULL DEFAULT true,
                      `autoopennew` boolean NOT NULL DEFAULT false,
                      `autoopenrunning` boolean NOT NULL DEFAULT false,
                      `autoupdate_duration` TINYINT(1) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`)
                   )
                   ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      } else {

         $fields = $DB->list_fields($table, false);

         if (! isset($fields['displayinfofor'])) {
            // For whom the actualtime timers are displayed?
            // 0 - Only in standard/central interface (default)
            // 1 - Both in standard and helpdesk interfaces
            $migration->addField(
               $table,
               'displayinfofor',
               'smallint',
               [
                  'update' => 0,
                  'value'  => 0,
                  'after' => 'id'
               ]
            );
         }

         if (! isset($fields['showtimerpopup'])) {
            // Add new field showtimerpopup
            $migration->addField(
               $table,
               'showtimerpopup',
               'boolean',
               [
                  'update' => true,
                  'value'  => true,
                  'after' => 'displayinfofor'
               ]
            );
         }

         if (! isset($fields['showtimerinbox'])) {
            // Add new field showtimerinbox
            $migration->addField(
               $table,
               'showtimerinbox',
               'boolean',
               [
                  'update' => true,
                  'value'  => true,
                  'after' => 'showtimerpopup'
               ]
            );
         }

         if (! $DB->fieldExists($table, 'autoopennew')) {
            // Add new field autoopennew
            $migration->addField(
               $table,
               'autoopennew',
               'boolean',
               [
                  'update' => false,
                  'value'  => false,
                  'after'  => 'showtimerinbox',
               ]
            );
         }

         if (! $DB->fieldExists($table, 'autoopenrunning')) {
            // Add new field autoopenrunning
            $migration->addField(
               $table,
               'autoopenrunning',
               'boolean',
               [
                  'update' => false,
                  'value'  => false,
                  'after'  => 'autoopennew',
               ]
            );
         }

         // Old not used field in version 1.1.1
         if (isset($fields['enable'])) {
            $migration->dropField(
               $table,
               'enable'
            );
         }

      }

      // Create default record (if it does not exist)
      $reg = $DB->request($table);
      if (! count($reg)) {
         $DB->insert(
            $table, [
               'displayinfofor' => 0
            ]
         );
      }

      $migration->addField(
        $table,
        'autoupdate_duration',
        'bool'
      );

   }

   static function uninstall(Migration $migration) {
      global $DB;

      $table = self::getTable();
      if ($DB->TableExists($table)) {
         $migration->displayMessage("Uninstalling $table");
         $migration->dropTable($table);
      }
   }
}
