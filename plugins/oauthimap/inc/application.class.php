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
use GlpiPlugin\Oauthimap\Provider\Azure;
use GlpiPlugin\Oauthimap\Provider\Google;
use GlpiPlugin\Oauthimap\Provider\ProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;

class PluginOauthimapApplication extends CommonDropdown {

   static $rightname     = 'config';

   /**
    * Random integer used in some form field id.
    * @var int
    */
   private $form_rand;

   public static function getTypeName($nb = 0) {
      return _n('Oauth IMAP application', 'Oauth IMAP applications', $nb, 'oauthimap');
   }

   static function getMenuContent() {

      $menu = [];
      if (Config::canUpdate()) {
         $menu['title'] = self::getMenuName();
         $menu['page']  = '/' . Plugin::getWebDir('oauthimap', false) . '/front/application.php';
         $menu['icon']  = self::getIcon();
      }
      if (count($menu)) {
         return $menu;
      }
      return false;
   }

   public static function getIcon() {
      return 'fas fa-sign-in-alt';
   }

   static function canCreate() {
      return static::canUpdate();
   }

   static function canPurge() {
      return static::canUpdate();
   }

   public function getAdditionalFields() {
      return [
         [
            'name'  => 'is_active',
            'label' => __('Active'),
            'type'  => 'bool'
         ],
         [
            'name'     => 'provider',
            'label'    => __('Oauth provider', 'oauthimap'),
            'type'     => 'oauth_provider',
            'list'     => true,
         ],
         [
            'name'     => 'client_id',
            'label'    => __('Client ID', 'oauthimap'),
            'type'     => 'text',
            'list'     => true,
         ],
         [
            'name'     => 'client_secret',
            'label'    => __('Client secret', 'oauthimap'),
            'type'     => 'secured_field',
            'list'     => false,
         ],
         [
            'name'     => 'tenant_id',
            'label'    => __('Tenant ID', 'oauthimap'),
            'type'     => 'additionnal_param',
            'list'     => false,
            'provider' => Azure::class,
         ],
      ];
   }

   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'provider',
         'name'               => __('Oauth provider', 'oauthimap'),
         'searchtype'         => ['equals', 'notequals'],
         'datatype'           => 'specific',
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'client_id',
         'name'               => __('Client ID', 'oauthimap'),
         'datatype'           => 'text',
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'tenant_id',
         'name'               => __('Tenant ID', 'oauthimap'),
         'datatype'           => 'text',
      ];

      return $tab;
   }

   function defineTabs($options = []) {

      $tabs = parent::defineTabs($options);

      $this->addStandardTab(MailCollectorFeature::class, $tabs, $options);
      $this->addStandardTab(PluginOauthimapAuthorization::class, $tabs, $options);

      return $tabs;
   }

   public function displaySpecificTypeField($ID, $field = []) {

      $field_name  = $field['name'];
      $field_type  = $field['type'];
      $field_value = $this->fields[$field_name];

      switch ($field_type) {
         case 'oauth_provider':
            $values = [];
            $icons = [];
            foreach (self::getSupportedProviders() as $provider_class) {
               $values[$provider_class] = $provider_class::getName();
               $icons[$provider_class] = $provider_class::getIcon();
            }
            Dropdown::showFromArray(
               $field_name,
               $values,
               [
                  'display_emptychoice' => true,
                  'rand'                => $this->form_rand,
                  'value'               => $field_value,
               ]
            );

            echo '<a href="" target="_blank" class="help-link" title="' . __('Developer help for this provider', 'oauthimap') . '">';
            echo '<i class="fa fa-question-circle fa-2x" style="color: #FF9700; vertical-align: middle;"></i>';
            echo '</a>';

            $json_icons = json_encode($icons);
            $js = <<<JAVASCRIPT
               $(function() {
                  var icons = $json_icons;
                  var displayOptionIcon = function(item) {
                     if (!item.id || !icons[item.id]) {
                        return item.text;
                     }
                     return $('<span><i class="fab fa-lg ' + icons[item.id] + '"></i>&nbsp;' + item.text + '</span>');
                  };

                  $("#dropdown_{$field_name}{$this->form_rand}").select2({
                     dropdownAutoWidth: true,
                     templateSelection: displayOptionIcon,
                     templateResult: displayOptionIcon,
                     width: ''
                  });
               });
JAVASCRIPT;
            echo Html::scriptBlock($js);
            break;
         case 'secured_field':
            echo Html::input(
               $field_name,
               [
                  'autocomplete' => 'off',
                  'size'         => 50,
                  'value'        => Toolbox::sodiumDecrypt($field_value),
               ]
            );
            break;
         case 'additionnal_param':
            echo Html::input(
               $field_name,
               [
                  'data-provider' => $field['provider'],
                  'size'          => 50,
                  'value'         => $field_value,
               ]
            );
            break;
         default:
            throw new \RuntimeException(sprintf('Unknown type %s.', $field_type));
      }
   }

   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }

      switch ($field) {
         case 'provider' :
            $value = $values[$field];
            if (in_array($value, self::getSupportedProviders())) {
               return '<i class="fab fa-lg ' . $value::getIcon() . '"></i> ' . $value::getName();
            }
            return $value;
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }

      switch ($field) {
         case 'provider' :
            $selected = '';
            $elements = ['' => Dropdown::EMPTY_VALUE];
            foreach (self::getSupportedProviders() as $class) {
               $elements[$class] = $class::getName();
               if (Toolbox::stripslashes_deep($class) === $values[$field]) {
                  $selected = $class; // Too many slashes removed in $values[$field]
               }
            }
            return Dropdown::showFromArray(
               $name,
               $elements,
               [
                  'display' => false,
                  'value'   => $selected,
               ]
            );
            break;
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   public function showForm($id, $options = []) {
      $this->form_rand = mt_rand();

      $documentation_urls_json = json_encode(self::getProvidersDocumentationUrls());

      // Display/hide additionnal params and update documentation link depending on selected provider
      $additionnal_params_js = <<<JAVASCRIPT
         (function($) {
            var documentation_urls = {$documentation_urls_json};

            var updateDocumentationLink = function () {
               var provider = $('#dropdown_provider{$this->form_rand}').val();

               var url = documentation_urls.hasOwnProperty(provider)
                  ? documentation_urls[provider]
                  : null;

               $('.help-link').attr('href', url).toggle(url !== null);
            };

            var onProviderChange = function () {
               var provider = $.escapeSelector($(this).val()); // escape selector as it contains slashes
               $('[data-provider="' + provider + '"]').closest('tr').find('td').show();
               $('[data-provider]:not([data-provider="' + provider + '"])').closest('tr').find('td').hide();

               updateDocumentationLink();
            };

            $('#dropdown_provider{$this->form_rand}').change(onProviderChange);
            onProviderChange.call($('#dropdown_provider{$this->form_rand}'));
         })(jQuery);
JAVASCRIPT;
      echo Html::scriptBlock($additionnal_params_js);

      parent::showForm($id, $options);
   }

   public function showFormButtons($options = []) {

      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo '<label for="_callback_url_' . $this->form_rand  . '">';
      echo __('Callback url', 'oauthimap');
      echo '<i class="fa fa-info pointer" title="' . __('copy it in the management console of provider', 'oauthimap') . '"></i>';
      echo '</label>';
      echo '</td>';
      echo '<td colspan="3">';
      echo '<span class="copy_to_clipboard_wrapper">';
      echo Html::input(
         '',
         [
            'value'    => $this->getCallbackUrl(),
            'size'     => 50,
            'readonly' => 'readonly',
         ]
      );
      echo '</span>';
      echo '</td>';
      echo '</tr>';

      parent::showFormButtons($options);
   }

   function prepareInputForAdd($input) {
      if (!($input = $this->prepareInput($input))) {
         return false;
      }
      return parent::prepareInputForAdd($input);
   }

   function prepareInputForUpdate($input) {
      // Unset encrypted fields input if corresponding to current value
      // (encryption produces a different value each time,
      // so GLPI will consider them as updated on each form submit)
      foreach (['client_secret'] as $field_name) {
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
      if (array_key_exists('name', $input) && empty(trim($input['name']))) {
         Session::addMessageAfterRedirect(__('Name cannot be empty', 'oauthimap'), false, ERROR);
         return false;
      }

      if (array_key_exists('provider', $input)
          && !in_array(stripslashes($input['provider']), self::getSupportedProviders())) {
         Session::addMessageAfterRedirect(__('Invalid provider', 'oauthimap'), false, ERROR);
         return false;
      }

      foreach (['client_secret'] as $field_name) {
         if (array_key_exists($field_name, $input)
             && !empty($input[$field_name]) && $input[$field_name] !== 'NULL') {
            $input[$field_name] = Toolbox::sodiumEncrypt($input[$field_name]);
         }
      }

      return $input;
   }

   function pre_updateInDB() {
      if (in_array('provider', $this->updates)
          || in_array('client_id', $this->updates)
          || in_array('client_secret', $this->updates)) {
         // Remove codes and tokens if any credentials parameter changed
         $this->deleteChildrenAndRelationsFromDb(
            [
               PluginOauthimapApplication::class,
            ]
         );
      }
   }

   function post_updateItem($history = 1) {
      if (in_array('is_active', $this->updates) && !$this->fields['is_active']) {
         MailCollectorFeature::postDeactivateApplication($this);
      }
   }

   /**
    * Redirect to authorization URL corresponding to credentials.
    *
    * @param callable|null $callback_callable   Callable to call on authorization callback
    * @param array         $callback_params     Parameters to pass to callable
    *
    * @return void
    */
   public function redirectToAuthorizationUrl(?callable $callback_callable = null, array $callback_params = []): void {

      if (!$this->areCredentialsValid()) {
         throw new \RuntimeException('Invalid credentials.');
      }

      $provider = $this->getProvider();

      $auth_url = $provider->getAuthorizationUrl(
         [
            'scope' => self::getProviderScopes($this->fields['provider'])
         ]
      );

      $_SESSION['oauth2state'] = $provider->getState();
      $_SESSION[$this->getForeignKeyField()] = $this->fields['id'];

      $_SESSION['plugin_oauthimap_callback_callable'] = $callback_callable;
      $_SESSION['plugin_oauthimap_callback_params']   = $callback_params;

      Html::redirect($auth_url);
   }

   /**
    * Check if credentials are valid (i.e. all fields are correclty set).
    *
    * @return bool
    */
   private function areCredentialsValid(): bool {
      return !$this->isNewItem()
         && array_key_exists('provider', $this->fields)
         && in_array($this->fields['provider'], self::getSupportedProviders())
         && array_key_exists('client_id', $this->fields)
         && !empty($this->fields['client_id'])
         && array_key_exists('client_secret', $this->fields)
         && !empty($this->fields['client_secret']);
   }

   /**
    * Get list of supported providers classnames.
    *
    * @return array
    */
   private static function getSupportedProviders(): array {
      return [
         Azure::class,
         Google::class,
      ];
   }

   /**
    * Returns oauth provider class instance.
    *
    * @return AbstractProvider|ProviderInterface|null
    */
   public function getProvider(): ?AbstractProvider {

      if (!$this->areCredentialsValid()) {
         throw new \RuntimeException('Invalid credentials.');
      }

      if (!is_a($this->fields['provider'], ProviderInterface::class, true)) {
         throw new \RuntimeException(sprintf('Unknown provider %s.', $this->fields['provider']));
      }

      $params = [
         'clientId'     => $this->fields['client_id'],
         'clientSecret' => Toolbox::sodiumDecrypt($this->fields['client_secret']),
         'redirectUri'  => $this->getCallbackUrl(),
         'scope'        => self::getProviderScopes($this->fields['provider']),
      ];

      // Specific parameters
      switch ($this->fields['provider']) {
         case Azure::class:
            $params['authWithResource'] = false;
            $params['pathAuthorize']    = '/oauth2/v2.0/authorize';
            $params['pathToken']        = '/oauth2/v2.0/token';
            if (!empty($this->fields['tenant_id'])) {
               $params['tenant'] = $this->fields['tenant_id'];
            }
            break;
         case Google::class:
            $params['accessType'] = 'offline';
            break;
      }

      return new $this->fields['provider']($params);
   }

   /**
    * Get required scopes for given provider.
    *
    * @param string $provider Provider classname
    *
    * @return array
    */
   private static function getProviderScopes(string $provider): array {
      $scopes = [];

      switch ($provider) {
         case Azure::class:
            $scopes = [
               'openid', 'email', // required to be able to fetch owner details
               'offline_access',
               'https://outlook.office.com/IMAP.AccessAsUser.All',
            ];
            break;
         case Google::class:
            $scopes = [
               'https://mail.google.com/',
            ];
            break;
      }
      return $scopes;
   }

   /**
    * Get documentation URLs.
    * Keys are providers classnames, values are URL.
    *
    * @return array
    */
   private static function getProvidersDocumentationUrls(): array {
      return [
         Azure::class  => 'https://docs.microsoft.com/en-us/exchange/client-developer/legacy-protocols/how-to-authenticate-an-imap-pop-smtp-application-by-using-oauth',
         Google::class => 'https://developers.google.com/gmail/imap/xoauth2-protocol',
      ];
   }

   /**
    * Get callback URL used during authorization process.
    *
    * @return string
    */
   private function getCallbackUrl(): string {
      return Plugin::getWebDir('oauthimap', true, true) . '/front/authorization.callback.php';
   }

   function cleanDBonPurge() {
      $this->deleteChildrenAndRelationsFromDb(
         [
            PluginOauthimapApplication::class,
         ]
      );
   }


   /**
    * Install all necessary data for this class.
    */
   public static function install(Migration $migration) {

      global $DB;

      $table = self::getTable();

      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                      `is_active` tinyint(1) NOT NULL DEFAULT '0',
                      `comment` text COLLATE utf8_unicode_ci,
                      `date_creation` timestamp NULL DEFAULT NULL,
                      `date_mod` timestamp NULL DEFAULT NULL,
                      `provider` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                      `client_id` text COLLATE utf8_unicode_ci NOT NULL,
                      `client_secret` text COLLATE utf8_unicode_ci NOT NULL,
                      `tenant_id` varchar(255) COLLATE utf8_unicode_ci NULL,
                      PRIMARY KEY (`id`),
                      KEY `name` (`name`),
                      KEY `is_active` (`is_active`),
                      KEY `date_creation` (`date_creation`),
                      KEY `date_mod` (`date_mod`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }

      // Add display preferences
      $migration->updateDisplayPrefs(
         [
            'PluginOauthimapApplication' => [1, 5, 6, 7, 121, 19],
         ]
      );
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
