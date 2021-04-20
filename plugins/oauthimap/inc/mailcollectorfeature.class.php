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

namespace GlpiPlugin\Oauthimap;

use CommonGLPI;
use Dropdown;
use GlpiPlugin\Oauthimap\Imap\ImapOauthProtocol;
use GlpiPlugin\Oauthimap\Imap\ImapOauthStorage;
use Html;
use MailCollector;
use Plugin;
use PluginOauthimapApplication;
use PluginOauthimapAuthorization;
use Session;

class MailCollectorFeature {


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginOauthimapApplication::class:
            $count = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
               $collectors = MailCollectorFeature::getAssociatedMailCollectors(
                  MailCollectorFeature::getMailProtocolTypeIdentifier($item->getID()),
                  null,
                  false
               );
               $count = count($collectors);
            }

            return CommonGLPI::createTabEntry(MailCollector::getTypeName(Session::getPluralNumber()), $count);
            break;
      }

      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginOauthimapApplication::class:
            MailCollectorFeature::showMailCollectorsForApplication($item, $tabnum);
            break;
      }

      return false;
   }

   /**
    * Get mail protocols specs as expected by 'mail_server_protocols' hook.
    */
   public static function getMailProtocols() {
      $mail_protocols = [];

      $values = getAllDataFromTable(
         PluginOauthimapApplication::getTable(),
         [
            'WHERE' => [
               'is_active' => 1,
            ],
         ]
      );

      foreach ($values as $value) {
         $id = $value['id'];
         $protocol_class = function () use ($id) {
            return new ImapOauthProtocol($id);
         };
         $storage_class = function (array $params) use ($id) {
            $params['application_id'] = $id;
            return new ImapOauthStorage($params);
         };
         $mail_protocols[self::getMailProtocolTypeIdentifier($id)] = [
            'label'    => $value['name'],
            'protocol' => $protocol_class,
            'storage'  => $storage_class,
         ];
      }

      return $mail_protocols;
   }

   /**
    * Return mail protocol type identifier.
    *
    * @param int $application_id
    *
    * @return string
    */
   public static function getMailProtocolTypeIdentifier($application_id) {
      return sprintf('imap-oauth-%s', $application_id);
   }

   /**
    * Alter MailCollector form in order to handle IMAP Oauth connections.
    *
    * @param array $params
    *
    * @return void
    */
   static public function alterMailCollectorForm(array $params): void {

      $item = $params['item'];

      if (!($item instanceof MailCollector)) {
         return;
      }

      $locator_id = 'plugin_oauthimap_locator_'. mt_rand();
      $plugin_path = Plugin::getWebDir('oauthimap');

      echo '<span id="'. $locator_id . '" style="display:none;"></span>';
      $javascript = <<<JAVASCRIPT
         $(
            function () {
               var form = $('#{$locator_id}').closest('form');

               var server_type_field = form.find('[name="server_type"]');
               var password_field = form.find('[name="passwd"]');
               var login_field = form.find('[name="login"]');

               form.append('<input type="hidden" name="plugin_oauthimap_applications_id" />');
               var application_field = form.find('[name="plugin_oauthimap_applications_id"]');

               login_field.parent().append('<div id="auth_field_container" style="display:none;"></div>');
               var auth_field_container = $('#auth_field_container');

               server_type_field.on(
                  'change',
                  function (evt) {
                     if (/^\/imap-oauth-\d+$/.test($(this).val())) {
                        var application_id = $(this).val().replace('/imap-oauth-', '');

                        password_field.closest('tr').hide();
                        login_field.hide();
                        auth_field_container.show();

                        application_field.val(application_id);
                        auth_field_container.load(
                           '{$plugin_path}/ajax/dropdownAuthorization.php',
                           {
                              application_id: application_id,
                              selected: login_field.val()
                           }
                        );
                     } else {
                        password_field.closest('tr').show();
                        auth_field_container.hide();
                        login_field.show();

                        application_field.val('');
                     }
                  }
               );

               // Change login field value to trigger mail collector update
               auth_field_container.on(
                  'change',
                  'select',
                  function (evt) {
                     if ($(this).val() == -1) {
                        login_field.val('');
                     } else {
                        login_field.val($(this).find('option:selected').text());
                     }
                  }
               );

               server_type_field.trigger('change');
            }
         );
JAVASCRIPT;

      echo Html::scriptBlock($javascript);
   }

   /**
    * Force mailcollector update if oauth fields should trigger an authorization request.
    *
    * @param MailCollector $item
    *
    * @return void
    */
   public static function forceMailCollectorUpdate(MailCollector $item) {
      if (!array_key_exists('plugin_oauthimap_applications_id', $item->input)
          || !array_key_exists('plugin_oauthimap_authorizations_id', $item->input)) {
         // Plugin fields are not present, update was not made inside form.
         return true;
      }

      if (!($item->input['plugin_oauthimap_applications_id'] > 0)) {
         // No application selected => mail collector does not use Oauth.
         // Return true to continue update.
         return true;
      }
      if ($item->input['plugin_oauthimap_authorizations_id'] > 0) {
         // Existing authorization selected => no need to trigger authorization request.
         // Return true to continue update.
         return true;
      }

      // Defines "date_mod" field of mail collector to force its update.
      // Indeed, if no mail collector field changed, "item_update" hook will not be called and authorization request
      // will not be triggered.
      $item->input['date_mod'] = $_SESSION['glpi_currenttime'];

      return true;
   }

   /**
    * Handle authorization process after creation/update of a mail collector.
    *
    * @param MailCollector $item
    *
    * @return void
    */
   public static function handleMailCollectorSaving(MailCollector $item): void {
      if (!array_key_exists('plugin_oauthimap_applications_id', $item->input)
          || !array_key_exists('plugin_oauthimap_authorizations_id', $item->input)) {
         // Plugin fields are not present, update was not made inside form.
         return;
      }

      $applications_id   = $item->input['plugin_oauthimap_applications_id'];
      $authorizations_id = $item->input['plugin_oauthimap_authorizations_id'];

      if (!($applications_id > 0)) {
         // No application selected => mail collector does not use Oauth.
         return;
      }

      $application = new PluginOauthimapApplication();
      $application->getFromDB($applications_id);
      $authorization = new PluginOauthimapAuthorization();

      if ($authorizations_id > 0 && $authorization->getFromDB($authorizations_id)) {
         // Use existing authorization
         self::updateMailCollectorOnAuthorizationCallback(
            true,
            $authorization,
            [
               MailCollector::getForeignKeyField() => $item->getID(),
            ]
         );
      } else {
         // Create new authorization
         $application->redirectToAuthorizationUrl(
            [self::class, 'updateMailCollectorOnAuthorizationCallback'],
            [
               MailCollector::getForeignKeyField() => $item->getID(),
            ]
         );
      }
   }

   /**
    * Update login field of mail collector on authorization callback.
    *
    * @param bool                         $success
    * @param PluginOauthimapAuthorization $authorization
    * @param array                        $params
    *
    * @return void
    */
   public static function updateMailCollectorOnAuthorizationCallback(
      bool $success,
      PluginOauthimapAuthorization $authorization,
      array $params = []
   ): void {
      if ($success) {
         // Store authorized email into MailCollector
         $mailcollector = new MailCollector();
         $mailcollector_id = $params[$mailcollector->getForeignKeyField()] ?? null;
         if ($mailcollector_id !== null && $mailcollector->getFromDB($mailcollector_id)) {
            $mailcollector->update(
               [
                  'id'    => $mailcollector_id,
                  'login' => $authorization->fields['email'],
               ]
            );
         }
      }

      Html::redirect($mailcollector->getLinkURL());
   }

   /**
    * Deactivate mail collectors linked to the application.
    *
    * @param PluginOauthimapApplication $application
    *
    * @return void
    */
   public static function postDeactivateApplication(PluginOauthimapApplication $application): void {
      self::deactivateMailCollectors(
         self::getMailProtocolTypeIdentifier($application->getID())
      );
   }

   /**
    * Deactivate mail collectors linked to the authorization.
    *
    * @param PluginOauthimapAuthorization $authorization
    *
    * @return void
    */
   public static function postPurgeAuthorization(PluginOauthimapAuthorization $authorization): void {
      $application_id = $authorization->fields[PluginOauthimapApplication::getForeignKeyField()];
      self::deactivateMailCollectors(
         self::getMailProtocolTypeIdentifier($application_id),
         $authorization->fields['email']
      );
   }

   /**
    * Update mail collectors linked to the authorization.
    *
    * @param PluginOauthimapAuthorization $authorization
    *
    * @return void
    */
   public static function postUpdateAuthorization(PluginOauthimapAuthorization $authorization): void {
      if (in_array('email', $authorization->updates) && array_key_exists('email', $authorization->oldvalues)) {
         $collectors = self::getAssociatedMailCollectors(
            self::getMailProtocolTypeIdentifier($authorization->fields[PluginOauthimapApplication::getForeignKeyField()]),
            $authorization->oldvalues['email']
         );
         foreach ($collectors as $row) {
            $mailcollector = new MailCollector();
            $mailcollector->update(
               [
                  'id'    => $row['id'],
                  'login' => $authorization->fields['email'],
               ]
            );
            Session::addMessageAfterRedirect(
               sprintf(
                  __('Mail receiver "%s" has been updated.', 'oauthimap'),
                  $mailcollector->getName()
               )
            );
         }
      }
   }

   /**
    * Deactivate mail collectors using given protocol type and given login.
    *
    * @param string $protocol_type
    * @param string $login
    *
    * @return void
    */
   private static function deactivateMailCollectors(string $protocol_type, ?string $login = null) {
      $collectors = self::getAssociatedMailCollectors($protocol_type, $login);

      foreach ($collectors as $row) {
         $mailcollector = new MailCollector();
         $mailcollector->update(
            [
               'id'        => $row['id'],
               'is_active' => 0,
            ]
         );
         Session::addMessageAfterRedirect(
            sprintf(
               __('Mail receiver "%s" has been deactivated.', 'oauthimap'),
               $mailcollector->getName()
            )
         );
      }
   }

   /**
    * Return mail collectors using given protocol type and given login.
    *
    * @param string $protocol_type
    * @param string $login
    * @param bool   $only_active
    *
    * @return void
    */
   private static function getAssociatedMailCollectors(
      string $protocol_type, string $login = null, bool $only_active = true
   ) {
      $criteria = [];
      if ($only_active) {
         $criteria['is_active'] = 1;
      }
      if ($login !== null) {
         $criteria['login'] = $login;
      }

      $data = getAllDataFromTable(MailCollector::getTable(), $criteria);

      $result = [];

      foreach ($data as $row) {
         // type follows first found "/" and ends on next "/" (or end of server string)
         // server string is surrounded by "{}" and can be followed by a folder name
         // i.e. "{mail.domain.org/imap/ssl}INBOX", or "{mail.domain.org/pop}"
         //
         // see Toolbox::parseMailServerConnectString()
         $type = preg_replace('/^\{[^\/]+\/([^\/]+)(?:\/.+)*\}.*/', '$1', $row['host']);
         if ($type === $protocol_type) {
            $result[] = $row;
         }
      }

      return $result;
   }


   /**
    * Display "mail collectors" tab of application page.
    *
    * @param PluginOauthimapApplication $application
    *
    * @return void
    */
   public static function showMailCollectorsForApplication(PluginOauthimapApplication $application): void {
      $collectors = self::getAssociatedMailCollectors(
         self::getMailProtocolTypeIdentifier($application->getID()),
         null,
         false
      );

      echo '<table class="tab_cadre_fixehov">';
      if (count($collectors) === 0) {
         echo '<tr><th>' . __('No associated receivers.', 'oauthimap') . '</th></tr>';
      } else {
         echo '<tr>';
         echo '<th>' . __('Name', 'oauthimap') . '</th>';
         echo '<th>' . __('Connection string', 'oauthimap') . '</th>';
         echo '<th>' . __('Login', 'oauthimap') . '</th>';
         echo '<th>' . __('Is active ?', 'oauthimap') . '</th>';
         echo '</tr>';

         foreach ($collectors as $row) {
            $mailcollector = new MailCollector();
            $mailcollector->getFromDB($row['id']);

            $name = $mailcollector->canViewItem() ? $mailcollector->getLink() : $mailcollector->getNameID();

            echo '<tr class="tab_bg_2">';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $row['host'] . '</td>';
            echo '<td>' . $row['login'] . '</td>';
            echo '<td>' . Dropdown::getYesNo($row['is_active']) . '</td>';
            echo '</tr>';
         }
      }
      echo '</table>';
      echo '</div>';
   }
}
