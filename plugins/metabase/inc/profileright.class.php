<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMetabaseProfileright extends CommonDBTM {

   /**
    * Necessary right to edit the rights of this plugin.
    */
   static $rightname = 'profile';

   /**
    * {@inheritDoc}
    * @see CommonGLPI::getTypeName()
    */
   static function getTypeName($nb = 0) {

      return __('Metabase', 'metabase');
   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::getTabNameForItem()
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (Profile::class === $item->getType() && Session::haveRight('profile', READ)) {
         return self::createTabEntry(self::getTypeName());
      }
      return '';
   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::displayTabContentForItem()
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case Profile::class:

            if (!Session::haveRight('profile', READ)) {
               break;
            }

            $profileright = new self();
            $profileright->showForm($item->fields['id']);

            break;
      }

      return true;
   }

   /**
    * Display profile rights form.
    *
    * @param integer $id Profile id
    * @param array $options
    *
    * @return void
    */
   function showForm($id, $options = []) {

      if (!Session::haveRight('profile', READ)) {
         return;
      }

      echo '<form method="post" action="' . self::getFormURL() . '">';
      echo '<div class="spaced" id="tabsbody">';
      echo '<table class="tab_cadre_fixe" id="mainformtable">';

      echo '<tr class="headerRow"><th colspan="2">' . self::getTypeName() . '</th></tr>';

      Plugin::doHook("pre_item_form", ['item' => $this, 'options' => &$options]);

      echo '<tr><th colspan="2">' . __('Rights management', 'metabase') . '</th></tr>';

      echo '<input type="hidden" name="profiles_id" value="' . $id . '" />';

      if (Session::haveRight('profile', UPDATE)) {
         echo '<tr class="tab_bg_4">';
         echo '<td colspan="2" class="center">';
         echo '<button type="submit" name="set_rights_to_all" value="1">' . __('Allow access to all', 'metabase') . '</button>';
         echo ' &nbsp; ';
         echo '<button type="submit" name="set_rights_to_all" value="0">' . __('Disallow access to all', 'metabase') . '</button>';
         echo '</td>';
         echo '</tr>';
      }

      $apiclient = new PluginMetabaseAPIClient();
      $dashboards = $apiclient->getDashboards();

      foreach ($dashboards as $dashboard) {
         echo '<tr class="tab_bg_1">';
         echo '<td>' . $dashboard['name'] . '</td>';
         echo '<td>';
         Profile::dropdownRight(
            sprintf('dashboard[%d]', $dashboard['id']),
            [
               'value'   => self::getProfileRightForDashboard($id, $dashboard['id']),
               'nonone'  => 0,
               'noread'  => 0,
               'nowrite' => 1,
            ]
         );
         echo '</td>';
         echo '</tr>';
      }

      if (Session::haveRight('profile', UPDATE)) {
         echo '<tr class="tab_bg_4">';
         echo '<td colspan="2" class="center">';
         echo '<input type="submit" name="update" value="' . _sx('button', 'Save') . '" class="submit" />';
         echo '</td>';
         echo '</tr>';
      }

      echo '</table>';
      echo '</div>';

      Html::closeForm();
   }

   /**
    * Check if profile is able to view at least one dashboard.
    *
    * @param integer $profileId
    * @param integer $dashboardUuid
    *
    * @return boolean
    */
   static function canProfileViewDashboards($profileId) {

      global $DB;

      $iterator = $DB->request(
         [
            'FROM'  => self::getTable(),
            'WHERE' => [
               'profiles_id' => $profileId,
            ]
         ]
      );

      while ($right = $iterator->next()) {
         if ($right['rights'] & READ) {
            return true;
         }
      }

      return false;
   }

   /**
    * Check if profile is able to view given dashboard.
    *
    * @param integer $profileId
    * @param integer $dashboardUuid
    *
    * @return boolean
    */
   static function canProfileViewDashboard($profileId, $dashboardUuid) {

      return self::getProfileRightForDashboard($profileId, $dashboardUuid) & READ;
   }

   /**
    * Returns profile rights for given dashboard.
    *
    * @param integer $profileId
    * @param integer $dashboardUuid
    *
    * @return integer
    */
   private static function getProfileRightForDashboard($profileId, $dashboardUuid) {

      $rightCriteria = [
         'profiles_id'    => $profileId,
         'dashboard_uuid' => $dashboardUuid,
      ];

      $profileRight = new self();
      if ($profileRight->getFromDBByCrit($rightCriteria)) {
         return $profileRight->fields['rights'];
      }

      return 0;
   }

   /**
    * Defines profile rights for dashboard.
    *
    * @param integer $profileId
    * @param integer $dashboardUuid
    * @param integer $rights
    *
    * @return void
    */
   static function setDashboardRightsForProfile($profileId, $dashboardUuid, $rights) {

      $profileRight = new self();

      $rightsExists = $profileRight->getFromDBByCrit(
         [
            'profiles_id' =>$profileId,
            'dashboard_uuid' => $dashboardUuid
         ]
      );

      if ($rightsExists) {
         $profileRight->update(
            [
               'id'     => $profileRight->fields['id'],
               'rights' => $rights,
            ]
         );
      } else {
         $profileRight->add(
            [
               'profiles_id'    => $profileId,
               'dashboard_uuid' => $dashboardUuid,
               'rights'         => $rights,
            ]
         );
      }
   }

   /**
    * Install profiles database.
    *
    * @param Migration $migration
    *
    * @return void
    */
   static function install(Migration $migration) {

      global $DB;

      $table = self::getTable();

      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `profiles_id` int(11) NOT NULL,
                     `dashboard_uuid` int(11) NOT NULL,
                     `rights` int(11) NOT NULL,
                     PRIMARY KEY (`id`),
                     UNIQUE `profiles_id_dashboard_uuid` (`profiles_id`, `dashboard_uuid`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      }
   }

   /**
    * Uninstall profiles database.
    *
    * @return void
    */
   static function uninstall() {

      global $DB;

      $DB->query('DROP TABLE IF EXISTS `' . self::getTable() . '`');
   }
}
