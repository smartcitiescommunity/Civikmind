<?php
/**
 -------------------------------------------------------------------------
 oauthimap plugin for GLPI
 Copyright (C) 2018-2020 by the oauthimap Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of oauthimap.

 oauthimap is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 oauthimap is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with oauthimap. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

use GlpiPlugin\Oauthimap\MailCollectorFeature;
use GlpiPlugin\Oauthimap\Oauth\OwnerDetails;
use League\OAuth2\Client\Token\AccessToken;

class PluginOauthimapAuthorization extends CommonDBChild {

   // From CommonGlpi
   protected $displaylist  = false;

   // From CommonDBTM
   public $dohistory       = true;

   // From CommonDBChild
   static public $itemtype = 'PluginOauthimapApplication';
   static public $items_id = 'plugin_oauthimap_applications_id';

   /**
    * Authorization owner details.
    * @var OwnerDetails
    */
   private $owner_details;

   public static function getTypeName($nb = 0) {
      return _n('Oauth authorization', 'Oauth authorizations', $nb, 'oauthimap');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $count = 0;
      if ($_SESSION['glpishow_count_on_tabs']) {
         $count = countElementsInTable(
            $this->getTable(),
            [
               PluginOauthimapApplication::getForeignKeyField() => $item->getID(),
            ]
         );
      }
      return self::createTabEntry(self::getTypeName(1), $count);
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if (!($item instanceof PluginOauthimapApplication)) {
         return;
      }

      global $DB;

      $iterator = $DB->request(
         [
            'FROM'  => self::getTable(),
            'WHERE' => [
               PluginOauthimapApplication::getForeignKeyField() => $item->getID(),
            ]
         ]
      );

      echo '<div class="center">';

      echo '<form method="POST" action="' . Plugin::getWebDir('oauthimap') . '/front/application.form.php">';
      echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
      echo Html::hidden('id', ['value' => $item->getID()]);
      echo '<button type="submit" class="vsubmit" name="request_authorization" value="1">';
      echo '<i class="fas fa-plus"></i> ';
      echo __('Create an authorization', 'oauthimap');
      echo '</button>';
      echo '</form>';

      echo '<br />';

      echo '<table class="tab_cadre_fixehov">';
      if ($iterator->count() === 0) {
         echo '<tr><th>' . __('No authorizations.', 'oauthimap') . '</th></tr>';
      } else {
         echo '<tr>';
         echo '<th>' . __('Email', 'oauthimap') . '</th>';
         echo '<th></th>';
         echo '</tr>';

         foreach ($iterator as $row) {
            echo '<tr class="tab_bg_2">';

            echo '<td>' . $row['email'] . '</td>';

            echo '<td>';
            echo '<a class="vsubmit" href="' . self::getFormURLWithID($row['id']) . '">';
            echo __('Update', 'oauthimap');
            echo '</a>';
            echo ' ';
            echo '<form method="POST" action="' . self::getFormURL() . '" style="display:inline-block;">';
            echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
            echo Html::hidden('id', ['value' => $row['id']]);
            echo '<button type="submit" class="vsubmit" name="delete" value="1">';
            echo '<i class="fas fa-trash-alt"></i> ';
            echo __('Delete', 'oauthimap');
            echo '</button>';
            echo '</form>';
            echo '</td>';

            echo '</tr>';
         }
      }
      echo '</table>';
      echo '</div>';

      return true;
   }

   public function showForm($id, $options = []) {

      $options['colspan'] = 1;

      $this->initForm($id, $options);
      $this->showFormHeader($options);

      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo __('Email', 'oauthimap');
      echo ' ';
      echo Html::showToolTip(
         __('This email address corresponds to the "user" field of the SASL XOAUTH2 authentication query.'),
         ['display' => false]
      );
      echo '</td>';
      echo '<td>';
      echo Html::input(
         'email',
         [
            'value' => $this->fields['email'],
            'style' => 'width:90%'
         ]
      );
      echo '</td>';
      echo '</tr>';

      $this->showFormButtons($options + ['candel' => false]);

      return true;
   }

   function prepareInputForAdd($input) {
      if (!($input = $this->prepareInput($input))) {
         return false;
      }
      return parent::prepareInputForAdd($input);
   }

   function prepareInputForUpdate($input) {
      // Unset encrypted fields input if corresponding to current value
      // (encryption produces a different value each time, so GLPI will consider them as updated on each form submit)
      foreach (['code', 'token'] as $field_name) {
         if (array_key_exists($field_name, $input)
             && !empty($input[$field_name]) && $input[$field_name] !== 'NULL'
             && $input[$field_name] === Toolbox::sodiumDecrypt($this->fields[$field_name])) {
            unset($input[$field_name]);
         }
      }

      if (!($input = $this->prepareInput($input))) {
         return false;
      }
      return parent::prepareInputForUpdate($input);
   }

   /**
    * Encrypt values of secured fields.
    *
    * @param array $input
    *
    * @return bool|array
    */
   private function prepareInput($input) {
      foreach (['code', 'token'] as $field_name) {
         if (array_key_exists($field_name, $input)
             && !empty($input[$field_name]) && $input[$field_name] !== 'NULL') {
            $input[$field_name] = Toolbox::sodiumEncrypt($input[$field_name]);
         }
      }

      return $input;
   }

   /**
    * Create an authorization based on authorizarion code.
    *
    * @param int    $application_id
    * @param string $code
    *
    * @return bool
    */
   public function createFromCode(int $application_id, string $code): bool {
      $application = new PluginOauthimapApplication();
      if (!$application->getFromDB($application_id)) {
         return false;
      }

      $provider = $application->getProvider();

      // Get token
      try {
         $token = $provider->getAccessToken('authorization_code', ['code'  => $code]);
      } catch (\Throwable $e) {
         trigger_error(
            sprintf('Error during authorization code fetching: %s', $e->getMessage()),
            E_USER_WARNING
         );
         return false;
      }

      // Get user details
      $this->owner_details = $provider->getOwnerDetails($token);
      $email = $this->owner_details->email;
      if ($email === null) {
         trigger_error('Unable to get user email', E_USER_WARNING);
         return false;
      }

      // Save informations
      $input = [
         $application->getForeignKeyField() => $application_id,
         'code'                             => $code,
         'token'                            => json_encode($token->jsonSerialize()),
         'email'                            => $email,
      ];

      $exists = $this->getFromDBByCrit(
         [
            $application->getForeignKeyField() => $application_id,
            'email'                            => $email,
         ]
      );
      if ($exists) {
         return $this->update(['id' => $this->fields['id']] + $input);
      } else {
         return $this->add($input);
      }
   }

   /**
    * Get a fresh access token related to given email using given application.
    *
    * @param int    $application_id
    * @param string $email
    *
    * @return string|null
    */
   public static function getAccessTokenForApplicationAndEmail($application_id, $email): ?string {
      $application = new PluginOauthimapApplication();
      if (!$application->getFromDB($application_id)) {
         return null;
      }

      $self = new self();
      if (!$self->getFromDBByCrit([$application->getForeignKeyField() => $application_id, 'email' => $email])) {
         return null;
      }

      try {
         $token = new AccessToken(json_decode(Toolbox::sodiumDecrypt($self->fields['token']), true));
      } catch (\Throwable $e) {
         return null; // Field value may be corrupted
      }

      if ($token->hasExpired()) {
         // Token has expired, refresh it
         $provider = $application->getProvider();
         $token = $provider->getAccessToken(
            'refresh_token',
            [
               'refresh_token' => $token->getRefreshToken(),
            ]
         );
         $self->update(['id' => $self->fields['id'], 'token' => json_encode($token->jsonSerialize())]);
      }

      return $token->getToken();
   }

   /**
    * Get existing access token.
    *
    * @return AccessToken|null
    */
   public function getAccessToken(): ?AccessToken {

      try {
         $token = new AccessToken(json_decode(Toolbox::sodiumDecrypt($this->fields['token']), true));
      } catch (\Throwable $e) {
         return null; // Field value may be corrupted
      }

      return $token;
   }

   /**
    * Returns owner details fetched when creating authorization.
    *
    * @return OwnerDetails|null
    */
   public function getOwnerDetails(): ?OwnerDetails {
      return $this->owner_details;
   }

   function post_updateItem($history = 1) {
      MailCollectorFeature::postUpdateAuthorization($this);
      parent::post_updateItem($history);
   }

   function post_purgeItem() {
      MailCollectorFeature::postPurgeAuthorization($this);
   }

   /**
    * Install all necessary data for this class.
    */
   public static function install(Migration $migration) {

      global $DB;

      $table = self::getTable();
      $application_fkey = PluginOauthimapApplication::getForeignKeyField();

      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `$application_fkey` int(11) NOT NULL DEFAULT '0',
                      `code` text COLLATE utf8_unicode_ci,
                      `token` text COLLATE utf8_unicode_ci,
                      `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                      `date_creation` timestamp NULL DEFAULT NULL,
                      `date_mod` timestamp NULL DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `$application_fkey` (`$application_fkey`),
                      KEY `date_creation` (`date_creation`),
                      KEY `date_mod` (`date_mod`),
                      UNIQUE KEY `unicity` (`$application_fkey`,`email`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }
   }

   /**
    * Uninstall previously installed data for this class.
    */
   public static function uninstall(Migration $migration) {

      $table = self::getTable();
      $migration->displayMessage("Uninstalling $table");
      $migration->dropTable($table);
   }
}
