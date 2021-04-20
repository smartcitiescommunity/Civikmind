<?php

/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use mageekguy\atoum\writers\std\err;

/**
 * JSS Classic API interface class
 * @since 1.0.0
 */
class PluginJamfAPIClassic
{
   /** PluginJamfConnection object representing the connection to a JSS server */
   private static $connection;

   /**
    * Get data from a JSS Classic API endpoint.
    * @param string $endpoint The API endpoint.
    * @param bool $raw If true, data is returned as JSON instead of decoded into an array.
    * @param string $response_type
    * @return mixed JSON string or associative array depending on the value of $raw.
    * @throws PluginJamfRateLimitException
    * @since 1.0.0
    */
   private static function get(string $endpoint, $raw = false, $response_type = 'application/json')
   {
      if (!self::$connection) {
         self::$connection = new PluginJamfConnection();
      }
      $url = (self::$connection)->getAPIUrl($endpoint);
      $curl = curl_init($url);
      // Set the username and password in an authentication header
      self::$connection->setCurlAuth($curl);
      self::$connection->setCurlSecurity($curl);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
         'Content-Type: application/json',
         "Accept: {$response_type}"
      ]);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);

      if (!$response) {
         return null;
      }
      if ($httpcode == 500) {
         $response = json_decode($response, true);
         if (isset($response['fault'])) {
            $fault = $response['fault'];
            switch ($fault['detail']['errorcode']) {
               case 'policies.ratelimit.QuotaViolation':
                  // We are making too many API calls in a short time.
                  throw new PluginJamfRateLimitException($fault['faultstring']);
            }
         }
         throw new RuntimeException(_x('error', 'Unknown JSS API Error', 'jamf'));
      }

      return ($raw ? $response : json_decode($response, true));
   }

   /**
    * Add a new item through a JSS Classic API endpoint.
    * @param string $endpoint The API endpoint.
    * @param string $payload XML payload of data to post to the endpoint.
    * @return int|bool True if successful, or the HTTP return code if it is not 201.
    * @since 1.1.0
    */
   private static function add(string $endpoint, string $payload)
   {
      if (!self::$connection) {
         self::$connection = new PluginJamfConnection();
      }
      $url = (self::$connection)->getAPIUrl($endpoint);
      $curl = curl_init($url);
      // Set the username and password in an authentication header
      self::$connection->setCurlAuth($curl);
      self::$connection->setCurlSecurity($curl);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
         'Content-Type: application/xml',
         'Accept: application/json'
      ]);
      curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      return ($httpcode == 201) ? true : $httpcode;
   }

   /**
    * Update an item through a JSS Classic API endpoint.
    * @param string $endpoint The API endpoint.
    * @param array $data Associative array of data to put to the endpoint.
    * @return int|bool True if successful, or the HTTP return code if it is not 201.
    * @since 1.1.0
    */
   private static function update(string $endpoint, array $data)
   {
      if (!self::$connection) {
         self::$connection = new PluginJamfConnection();
      }
      $url = (self::$connection)->getAPIUrl($endpoint);
      $curl = curl_init($url);
      // Set the username and password in an authentication header
      self::$connection->setCurlAuth($curl);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      self::$connection->setCurlSecurity($curl);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
         'Content-Type: application/json',
         'Accept: application/json'
      ]);
      curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      return ($httpcode == 201) ? true : $httpcode;
   }

   /**
    * Delete an item through a JSS Classic API endpoint.
    * @param string $endpoint The API endpoint.
    * @return int|bool True if successful, or the HTTP return code if it is not 200.
    * @since 1.1.0
    */
   private static function delete(string $endpoint)
   {
      if (!self::$connection) {
         self::$connection = new PluginJamfConnection();
      }
      $url = (self::$connection)->getAPIUrl($endpoint);
      $curl = curl_init($url);
      // Set the username and password in an authentication header
      self::$connection->setCurlAuth($curl);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
      self::$connection->setCurlSecurity($curl);
      curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      return ($httpcode == 200) ? true : $httpcode;
   }

   /**
    * Construct a parameter query string for an API endpoint.
    * @param array $params API inputs.
    * @return string The constructed parameter string.
    * @since 1.0.0
    */
   private static function getParamString(array $params = [])
   {
      $param_str = '';
      foreach ($params as $key => $value) {
         $param_str = "{$param_str}/{$key}/{$value}";
      }
      return $param_str;
   }

   /**
    * Get data for a specified JSS itemtype and parameters.
    * @param string $itemtype The type of data to fetch. This matches up with endpoint names.
    * @param array $params API input parameters such as udid, name, or subset.
    * @param bool $user_auth True if the user's linked JSS account privileges should be checked for requested resource.
    * @return array Associative array of the decoded JSON response.
    * @since 1.0.0
    */
   public static function getItems(string $itemtype, array $params = [], $user_auth = false)
   {
      if ($user_auth && !PluginJamfUser_JSSAccount::canReadJSSItem($itemtype)) {
         return null;
      }
      $param_str = self::getParamString($params);
      $endpoint = "$itemtype$param_str";
      $response = self::get($endpoint);
      // Strip first key (usually like mobile_devices or mobile_device)
      // No other first level keys exist
      return ($response !== null && count($response)) ? reset($response) : null;
   }

   /**
    * Add an item of the specified JSS itemtype and parameters.
    * @param string $itemtype The type of data to fetch. This matches up with endpoint names.
    * @param string $payload XML payload of data to post to the endpoint.
    * @param bool $user_auth True if the user's linked JSS account privileges should be checked for requested resource.
    * @return bool|int
    * @since 1.1.0
    */
   public static function addItem(string $itemtype, string $payload, $user_auth = false)
   {
      if ($itemtype == 'mobiledevicecommands') {
         $param_str = '/command';
         $meta = (string) simplexml_load_string($payload)->general->command;
      } else {
         $param_str = '';
         $meta = null;
      }
      if ($user_auth && !PluginJamfUser_JSSAccount::canCreateJSSItem($itemtype, $meta)) {
         return null;
      }

      $endpoint = "$itemtype$param_str";
      return self::add($endpoint, $payload);
   }

   /**
    * Update an item of the specified JSS itemtype and parameters.
    * @param string $itemtype The type of data to fetch. This matches up with endpoint names.
    * @param array $params API input parameters such as udid, name, or subset.
    * @param array $fields Associative array of item fields.
    * @param bool $user_auth True if the user's linked JSS account privileges should be checked for requested resource.
    * @return bool|int
    * @since 1.1.0
    */
   public static function updateItem(string $itemtype, array $params = [], array $fields = [], $user_auth = false)
   {
      if ($user_auth && !PluginJamfUser_JSSAccount::canUpdateJSSItem($itemtype)) {
         return null;
      }
      $param_str = self::getParamString($params);
      $endpoint = "$itemtype$param_str";
      return self::update($endpoint, $fields);
   }

   /**
    * Delete an item of the specified JSS itemtype and parameters.
    * @param string $itemtype The type of data to fetch. This matches up with endpoint names.
    * @param array $params API input parameters such as udid, name, or subset.
    * @param bool $user_auth True if the user's linked JSS account privileges should be checked for requested resource.
    * @return bool|int
    * @since 1.1.0
    */
   public static function deleteItem(string $itemtype, array $params = [], $user_auth = false)
   {
      if ($user_auth && !PluginJamfUser_JSSAccount::canDeleteJSSItem($itemtype)) {
         return null;
      }
      $param_str = self::getParamString($params);
      $endpoint = "$itemtype$param_str";
      return self::delete($endpoint);
   }

   private static function getJSSGroupActionRights($groupid)
   {
      $response = self::get("accounts/groupid/$groupid");
      return $response['group']['privileges']['jss_actions'];
   }

   public static function getJSSAccountRights($userid, $user_auth = false)
   {
      if ($user_auth && !PluginJamfUser_JSSAccount::canReadJSSItem('accounts')) {
         return null;
      }
      $response = self::get("accounts/userid/$userid", true, 'application/xml');
      $account = simplexml_load_string($response);

      $access_level = reset($account->access_level);
      $rights = [
         'jss_objects'  => [],
         'jss_actions'  => [],
         'jss_settings' => []
      ];
      if ($access_level === 'Group Access') {
         $group_count = count($account->groups->group);
         //$groups = $account->groups->group;
         for ($i = 0; $i < $group_count; $i++) {
            $group = $account->groups->group[$i];
            if (isset($group->privileges->jss_objects)) {
               $c = count($group->privileges->jss_objects->privilege);
               if ($c > 0) {
                  for ($j = 0; $j < $c; $j++) {
                     $rights['jss_objects'][] = reset($group->privileges->jss_objects->privilege[$j]);
                  }
               }
            }
            // Why are jss_actions not included in the group when all other rights are?
            $action_privileges = self::getJSSGroupActionRights(reset($group->id));
            $rights['jss_actions'] = $action_privileges;

            if (isset($group->privileges->jss_settings)) {
               $c = count($group->privileges->jss_settings->privilege);
               if ($c > 0) {
                  for ($j = 0; $j < $c; $j++) {
                     $rights['jss_settings'][] = reset($group->privileges->jss_settings->privilege[$j]);
                  }
               }
            }
         }
      } else {
         $privileges = $account->privileges;
         if (isset($privileges->jss_objects)) {
            $c = count($privileges->jss_objects->privilege);
            if ($c > 0) {
               for ($j = 0; $j < $c; $j++) {
                  $rights['jss_objects'][] = reset($privileges->jss_objects->privilege[$j]);
               }
            }
         }
         if (isset($privileges->jss_actions)) {
            $c = count($privileges->jss_actions->privilege);
            if ($c > 0) {
               for ($j = 0; $j < $c; $j++) {
                  $rights['jss_actions'][] = reset($privileges->jss_actions->privilege[$j]);
               }
            }
         }
         if (isset($privileges->jss_settings)) {
            $c = count($privileges->jss_settings->privilege);
            if ($c > 0) {
               for ($j = 0; $j < $c; $j++) {
                  $rights['jss_settings'][] = reset($privileges->jss_settings->privilege[$j]);
               }
            }
         }
      }
      return $rights;
   }
}

