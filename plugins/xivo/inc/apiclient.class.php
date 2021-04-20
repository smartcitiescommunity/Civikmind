<?php
/*
 -------------------------------------------------------------------------
 xivo plugin for GLPI
 Copyright (C) 2017 by the xivo Development Team.

 https://github.com/pluginsGLPI/xivo
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivo.

 xivo is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 xivo is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with xivo. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\GuzzleException;

class PluginXivoAPIClient extends CommonGLPI {
   private $api_config      = [];
   private $auth_token      = '';
   private $current_port    = 0;
   private $current_version = 0;
   private $last_error      = [];

   function __construct() {
      // retrieve plugin config
      $this->api_config = PluginXivoConfig::getConfig();
   }


   function __destruct() {
      //destroy current token
      $this->disconnect();
   }


   /**
    * Use Xivo Auth backend
    *
    * @return nothing (set private properties)
    */
   function useXivoAuth() {
      $this->current_port    = 9497;
      $this->current_version = 0.1;
   }


   /**
    * Use Xivo confd backend
    *
    * @return nothing (set private properties)
    */
   function useXivoConfd() {
      $this->current_port    = 9486;
      $this->current_version = 1.1;
   }


   /**
    * Check with Xivo API the mandatory actions
    *
    * @return array of [label -> boolean]
    */
   function status() {
      $device = $this->getDevices([
         'query' => [
            'limit' => 1
         ]
      ]);
      $device_id = is_array($device) ? end($device['items'])['id'] : false;
      $line = $this->getLines([
         'query' => [
            'limit' => 1
         ]
      ]);
      $line_id = is_array($line) ? end($line['items'])['id'] : false;

      return [
         __('REST API access', 'xivo')
            => !empty($this->auth_token),
         __('Get phone devices', 'xivo')." (confd.devices.read)"
            => is_array($device),
         __('Get single device', 'xivo')." (confd.devices.#.read)"
            => is_array($this->getSingleDevice($device_id, [
               'query' => [
                  'limit' => 1
               ]
            ])) && is_array($this->getSingleDeviceLines($device_id, [
               'query' => [
                  'limit' => 1
               ]
            ])),
         __('Get lines', 'xivo')." (confd.lines.read)"
            => is_array($line),
         __('Get single line', 'xivo')." (confd.lines.#.read)"
            => is_array($this->getSingleLine($line_id, [
               'query' => [
                  'limit' => 1
               ]
            ])),
         __('Get users', 'xivo')." (confd.users.read)"
            => is_array($this->getUsers([
               'query' => [
                  'limit' => 1
               ]
            ] )),
      ];
   }


   /**
    * Attempt an http connection on xivo api
    * if suceed, set auth_token private properties
    *
    * @return array data returned by the api
    */
   function connect() {
      // we use Xivo-auth api
      $this->useXivoAuth();

      // send connect with http query
      $data = $this->httpQuery('token', [
         'auth' => [
            $this->api_config['api_username'],
            $this->api_config['api_password'],
         ],
         'json' => [
            'backend'    => 'xivo_service',
            'expiration' => HOUR_TIMESTAMP,
         ]
      ], 'POST');

      if (is_array($data)) {
         if (isset($data['data']['token'])) {
            $this->auth_token = $data['data']['token'];
         }
      }

      return $data;
   }


   /**
    * Destroy session on xivo api (auth endpoint)
    *
    * @return array data returned by the api
    */
   function disconnect() {
      return;
      // we use Xivo-auth api
      $this->useXivoAuth();

      // send disconnect with http query
      return $this->httpQuery('token', [
         'verify' => boolval($this->api_config['api_ssl_check']),
         'json' => [
            'token' => $this->auth_token,
         ]
      ], 'DELETE');
   }


   /**
    * Retrieve list of devices (phones) in xivo api
    *
    * @param  array  $params http params (@see self::httpQuery params)
    * @return array  data returned by the api
    */
   function getDevices($params = []) {
      return $this->getList('devices', $params);
   }

   /**
    * Retrieve list of lines in xivo api
    *
    * @param  array  $params http params (@see self::httpQuery params)
    * @return array  data returned by the api
    */
   function getLines($params = []) {
      return $this->getList('lines', $params);
   }

   /**
    * Retrieve list of users in xivo api
    *
    * @param  array  $params http params (@see self::httpQuery params)
    * @return array  data returned by the api
    */
   function getUsers($params = []) {
      return $this->getList('users', $params);
   }

   /**
    * Retrieve a list from a specified endpoint
    *
    * @param  string $endpoint the resource to retrieve (ex: devices, users, etc)
    * @param  array  $params http params (@see self::httpQuery params)
    * @return array  data returned by the api
    */
   function getList($endpoint = '', $params = []) {
      // declare default params
      $default_params = [
         'query' => [
            'limit'     => 50,
            'direction' => 'asc',
            'offset'    => 0,
            'order'     => '',
            'search'    => '',
         ]
      ];

      // merge default params
      $params = array_replace_recursive($default_params, $params);

      // check connection
      if (empty($this->auth_token)) {
         $this->connect();
      }

      // we use Xivo-confd api
      $this->useXivoConfd();

      // get devices with http query
      $data = $this->httpQuery($endpoint, $params, 'GET');

      return $data;
   }


   /**
    * Paginate function getList to avoid a big http query
    *
    * @param  string $function function to use, will be prefixed by get (ex: getDevice, getUsers, etc)
    * @return array  data returned by the api
    */
   function paginate($function = "Devices") {
      $offset = 0;
      $limit  = 200;
      $items  = [];

      if (!method_exists($this, "get$function")) {
         return false;
      }

      do {
         $page = $this->{"get$function"}([
            'query' => [
               'offset'         => $offset,
               'limit'          => $limit,
            ],
            '_with_metadata' => true
         ]);

         $items = array_merge($items, $page['items']);
         $offset+= $limit;
      } while ($offset < $page['total']);

      return $items;
   }

   /**
    * Retrieve a single resource with xivo api
    *
    * @param  string $endpoint the resource to retrieve (ex: devices, users, etc)
    * @param  string $id       the id to retrive in xivo api
    * @return array  data returned by the api
    */
   function getSingle($endpoint = '', $id = '') {
      // check connection
      if (empty($this->auth_token)) {
         $this->connect();
      }

      // we use Xivo-confd api
      $this->useXivoConfd();

      // get devices with http query
      $data = $this->httpQuery("$endpoint/$id", [], 'GET');

      return $data;
   }

   /**
    * Get a single device (phone) with xivo api
    *
    * @param  string $id the xivo id of the device
    * @return array  data returned by the api
    */
   function getSingleDevice($id = "") {
      return $this->getSingle('devices', $id);
   }

   /**
    * Get a single line with xivo api
    *
    * @param  string $id the xivo id of the line
    * @return array  data returned by the api
    */
   function getSingleLine($id = "") {
      return $this->getSingle('lines', $id);
   }

   /**
    * Get lines of a single device (phone) with xivo api
    *
    * @param  string $id the xivo id of the device
    * @return array  data returned by the api
    */
   function getSingleDeviceLines($id = "") {
      $lines_items = $this->getList("devices/$id/lines")['items'];
      if (!is_array($lines_items)) {
         return false;
      }
      $lines = [];
      foreach ($lines_items as $item) {
         $lines[] = $this->getSingleLine($item['line_id']);
      }
      return $lines;
   }

   /**
    * Return the XIVO API base uri constructed from config
    *
    * @return string the uri
    */
   function getAPIBaseUri() {
      return trim($this->api_config['api_url'], '/').":{$this->current_port}/{$this->current_version}/";
   }

   /**
    * Send an http query to the xivo api
    *
    * @param  string $resource the endpoint to use
    * @param  array  $params   an array containg these possible options:
    *                             - _with_metadata (bool, default false)
    *                             - allow_redirects (bool, default false)
    *                             - timeout (int, default 5)
    *                             - connect_timeout (int, default 5)
    *                             - connect_timeout (int, default 5)
    *                             - debug (bool, default false)
    *                             - verify (bool, default based on plugin config), check ssl certificate
    *                             - query (array) url parameters
    *                             - body (string) raw data to send in body
    *                             - json (array) array to pass into the body chich will be json_encoded
    *                             - json (headers) http headers
    * @param  string $method   Http verb (ex: GET, POST, etc)
    * @return array  data returned by the api
    */
   function httpQuery($resource = '', $params = [], $method = 'GET') {
      global $CFG_GLPI;

      // declare default params
      $default_params = [
         '_with_metadata'  => false,
         'allow_redirects' => false,
         'timeout'         => 5,
         'connect_timeout' => 5,
         'debug'           => false,
         'verify'          => boolval($this->api_config['api_ssl_check']),
         'query'           => [], // url parameter
         'body'            => '', // raw data to send in body
         'json'            => [], // json data to send
         'headers'         => ['content-type'  => 'application/json',
                               'Accept'        => 'application/json'],
      ];
      // if connected, append auth token
      if (!empty($this->auth_token)) {
         $default_params['headers']['X-Auth-Token'] = $this->auth_token;
      }
      // append proxy params if exists
      if (!empty($CFG_GLPI['proxy_name'])) {
         $proxy = $CFG_GLPI['proxy_user'].
                  ":".$CFG_GLPI['proxy_passwd'].
                  "@".preg_replace('#https?://#', '', $CFG_GLPI['proxy_name']).
                  ":".$CFG_GLPI['proxy_port'];

         $default_params['proxy'] = [
            'http'  => "tcp://$proxy",
            'https' => "tcp://$proxy",
         ];
      }
      // merge default params
      $params = array_replace_recursive($default_params, $params);
      //remove empty values
      $params = plugin_xivo_recursive_remove_empty($params);

      // init guzzle
      $http_client = new GuzzleHttp\Client(['base_uri' => $this->getAPIBaseUri()]);

      // send http request
      try {
         $response = $http_client->request($method,
                                           $resource,
                                           $params);
      } catch (GuzzleException $e) {
         $this->last_error = [
            'title'     => "XIVO API error",
            'exception' => $e->getMessage(),
            'params'    => $params,
            'request'   => Psr7\str($e->getRequest()),
         ];
         if ($e->hasResponse()) {
            $this->last_error['response'] = Psr7\str($e->getResponse());
         }
         if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
            Toolbox::logDebug($this->last_error);
         }
         return false;
      }

      // parse http response
      $http_code     = $response->getStatusCode();
      $reason_phrase = $response->getReasonPhrase();
      $headers       = $response->getHeaders();

      // check http errors
      if (intval($http_code) > 400) {
         // we have an error if http code is greater than 400
         return false;
      }
      // cast body as string, guzzle return strems
      $json        = (string) $response->getBody();
      $prelude_res = json_decode($json, true);

      $data =  json_decode($json, true);

      //append metadata
      if ($params['_with_metadata']) {
         $data['_headers']   = $headers;
         $data['_http_code'] = $http_code;
      }

      return $data;
   }

   /**
    * Return the error encountered with an http query
    *
    * @return array the error
    */
   function getLastError() {
      return $this->last_error;
   }

}

