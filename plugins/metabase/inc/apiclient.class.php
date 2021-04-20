<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class PluginMetabaseAPIClient extends CommonGLPI {
   private $api_config      = [];
   private $current_port    = 0;
   private $last_error      = [];

   function __construct() {
      // retrieve plugin config
      $this->api_config = PluginMetabaseConfig::getConfig();
   }


   /**
    * Check with metabase API the mandatory actions
    *
    * @return array of [label -> boolean]
    */
   function status() {
      return [
         __("API: login", 'metabase')
            => $this->connect(),
         __("API: get current user", 'metabase')
            => $this->getCurrentUser() !== false,
         __("API: get users", 'metabase')
            => $this->getUsers() !== false,
         __("API: get databases", 'metabase')
            => $this->getDatabases() !== false,
         __("API: get GLPI database", 'metabase')
            => $this->getGlpiDatabase() !== false,
      ];
   }


   /**
    * Attempt an http connection on metabase api
    * if suceed, set auth_token private properties
    *
    * @return array data returned by the api
    */
   function connect() {
      if (isset($_SESSION['metabase']['session_token'])) {
         return true;
      }

      // send connect with http query
      $data = $this->httpQuery('session', [
         'json' => [
            'username' => $this->api_config['username'],
            'password' => Toolbox::sodiumDecrypt($this->api_config['password']),
         ]
      ], 'POST');

      if (is_array($data)) {
         if (isset($data['id'])) {
            $_SESSION['metabase']['session_token'] = $data['id'];
         }
      }

      return ($data !== false && count($data) > 0);
   }

   function checkSession() {
      // do a simple query
      $this->getCurrentUser(true);

      // check session token, if set, we still have a valid token
      if (isset($_SESSION['metabase']['session_token'])) {
         return true;
      }

      // so reconnect
      $this->connect();

      // check again session token, if set, we now have a valid token
      if (isset($_SESSION['metabase']['session_token'])) {
         return true;
      }

      return false;
   }

   function getCurrentUser($skip_session_check = false) {
      if (!$skip_session_check
          && !$this->checkSession()) {
         return false;
      }

      $data = $this->httpQuery('user/current');

      return $data;
   }

   function getUsers() {
      if (!$this->checkSession()) {
         return false;
      }

      $data = $this->httpQuery('user');

      return $data;
   }

   function getDatabases() {
      if (!$this->checkSession()) {
         return false;
      }

      $data = $this->httpQuery('database');

      return $data;
   }

   function getDatabase($db_id = 0) {
      if (!$this->checkSession()) {
         return false;
      }

      $data = $this->httpQuery("database/$db_id");

      return $data;
   }

   function getGlpiDatabase() {
      // we already have stored the id of glpi database
      if (($db_id = $this->api_config['glpi_db_id']) != 0) {
         return $this->getDatabase($db_id);
      }

      if (($databases = $this->getDatabases()) === false) {
         return false;
      }

      foreach ($databases as $database) {
         if ($database['name'] == 'GLPI (plugin auto-generated)') {
            return $database;
         }
      }

      $this->last_error[] = __("No auto-generated GLPI database found", 'metabase');
      return false;
   }

   function createGlpiDatabase() {
      global $DB;

      if (($data = $this->getGlpiDatabase()) === false) {
         // try to switch to slave db
         DBConnection::switchToSlave();

         // post conf for the glpi database
         $data = $this->httpQuery('database', [
            'timeout' => $this->api_config['timeout'],
            'json'    => [
               'name'         => 'GLPI (plugin auto-generated)',
               'engine'       => 'mysql',
               'is_full_sync' => true,
               'details'       => [
                  'host'        => $DB->dbhost,
                  'port'        => 3306,
                  'dbname'      => $DB->dbdefault,
                  'user'        => $DB->dbuser,
                  'password'    => $DB->dbpassword,
                  'tunnel-port' => 22,
               ],
            ]
         ], 'POST');

         // switch back to master
         DBConnection::switchToMaster();
      }

      return $data;
   }

   function getDatabaseMetadata($db_id = 0) {
      if (!$this->checkSession()) {
         return false;
      }

      $data = $this->httpQuery("database/$db_id/metadata");

      return $data;
   }

   function createForeignKey($f_id_src = 0, $f_id_trgt = 0) {
      if (!$this->checkSession()) {
         return false;
      }

      $data = $this->httpQuery("/api/field/$f_id_src", [
         'json' => [
            'special_type'       => "type/FK",
            'fk_target_field_id' => $f_id_trgt,
         ]
      ], 'PUT');

      return $data;
   }

   function setItiObjectHardcodedMapping() {
      if (!isset($_SESSION['metabase']['fields'])) {
         return false;
      }

      $ticket  = new Ticket;
      $problem = new Problem;
      $change  = new Change;

      return $this->setTicketTypeMapping()
          && $this->setITILStatusMapping($ticket)
          && $this->setITILMatrixMapping($ticket)
          && $this->setITILStatusMapping($problem)
          && $this->setITILMatrixMapping($problem)
          && $this->setITILStatusMapping($change)
          && $this->setITILMatrixMapping($change);
   }


   function setTicketTypeMapping() {
      $field_id = $_SESSION['metabase']['fields']['glpi_tickets.type'];
      $this->setFieldCustomMapping($field_id, __("Type"));
      $data = $this->httpQuery("/api/field/$field_id/values", [
         'json' => [
            'values' => [
               [Ticket::INCIDENT_TYPE, __("Incident")],
               [Ticket::DEMAND_TYPE, __("Request")]
            ],
         ]
      ], 'POST');
      return isset($data['status']) && $data['status'] === "success";
   }

   function setITILStatusMapping(CommonItilObject $item) {
      $statuses = $item::getAllStatusArray();
      $statuses_topush = [];
      foreach ($statuses as $key => $label) {
         $statuses_topush[] = [$key, $label];
      }
      $table = $item::getTable();
      $field_id = $_SESSION['metabase']['fields']["$table.status"];
      $this->setFieldCustomMapping($field_id, __("Status"));
      $data = $this->httpQuery("/api/field/$field_id/values", [
         'json' => [
            'values' => $statuses_topush,
         ]
      ], 'POST');
      return isset($data['status']) && $data['status'] === "success";
   }

   function setITILMatrixMapping(CommonItilObject $item) {
      $table = $item::getTable();
      foreach (['urgency', 'impact', 'priority'] as $matrix_field) {
         $field_id = $_SESSION['metabase']['fields']["$table.$matrix_field"];
         $this->setFieldCustomMapping($field_id, __(mb_convert_case($matrix_field, MB_CASE_TITLE)));
         $data_topush = [
            [5, _x($matrix_field, 'Very high')],
            [4, _x($matrix_field, 'High')],
            [3, _x($matrix_field, 'Medium')],
            [2, _x($matrix_field, 'Low')],
            [1, _x($matrix_field, 'Very low')],
         ];
         if ($matrix_field === 'priority') {
            array_unshift($data_topush, [6, _x($matrix_field, 'Major')]);
         }
         $data = $this->httpQuery("/api/field/$field_id/values", [
            'json' => [
               'values' => $data_topush
            ]
         ], 'POST');

         if (!isset($data['status'])
             || $data['status'] !== "success") {
            return false;
         }
      }

      return true;
   }

   function setFieldCustomMapping($field_id, $label = "") {

      $data = $this->httpQuery("/api/field/$field_id", [
         'json' => [
            'special_type'       => 'type/Category',
            'has_field_values'   => 'list',
         ]
      ], 'PUT');

      $data = $this->httpQuery("/api/field/$field_id/dimension", [
         'json' => [
            'human_readable_field_id' => null,
            'type'                    => 'internal',
            'name'                    => $label
         ]
      ], 'POST');
   }

   function createOrGetCollection($collection_name, $params = []) {
      $default_params = [
         'color'       => '#000000',
         'description' => 'auto-generated by GLPI'
      ];
      $params = array_merge($default_params, $params);

      if ($collection_id = $this->retrieveCollection($collection_name)) {
         return $collection_id;
      }

      $data = $this->httpQuery('collection', [
         'json' => [
            'name'        => $collection_name,
            'color'       => $params['color'],
            'description' => $params['description'],
         ]
      ], 'POST');

      return isset($data['id'])
         ? $data['id']
         : false;
   }

   function retrieveCollection($collection_name) {
      if (($collections = $this->getCollections()) !== false) {
         $collections = array_column($collections, 'id', 'name');

         if (isset($collections[$collection_name])) {
            return $collections[$collection_name];
         }
      }

      return false;
   }

   function getCollections() {
      if (!$this->checkSession()) {
         return false;
      }

      return $this->httpQuery('collection');
   }

   function createOrGetDashboard($dashboard_name, &$params = []) {
      $default_params = [
         'name'        => $dashboard_name,
         'description' => '',
         'parameters'  => [],
      ];
      $params = array_merge($default_params, $params);

      if (isset($params['parameters'])) {
         foreach ($params['parameters'] as  &$parameter) {
            if (!isset($parameter['id'])) {
               $parameter['id'] = $this->generateUuid([8]);
            }
         }
      }

      $send_params = $params;
      unset($send_params['reports']);

      if ($id = $this->retrieveDashboard($dashboard_name)) {
         // update existing
         $data = $this->httpQuery("dashboard/$id", [
            'timeout' => $this->api_config['timeout'],
            'json'    => $send_params
         ], 'PUT');
      } else {
         // create new
         $data = $this->httpQuery('dashboard', [
            'timeout' => $this->api_config['timeout'],
            'json'    => $send_params
         ], 'POST');
      }

      return isset($data['id'])
         ? $data['id']
         : false;
   }

   function setDashboardCards($dashboard_id, $params) {
      if ($cards = $this->getDashboardCards($dashboard_id)) {

         // delete old cards
         foreach ($cards as $card) {
            $this->httpQuery("dashboard/$dashboard_id/cards", [
               'query' => [
                  'dashcardId' => $card['id']
               ]
            ], 'DELETE');
         }
      }

      // (re)create dashboard-cards
      foreach ($params['cards'] as $c_index => &$card) {
         // create card
         $c_params = [
            'cardId' => $card['card_id']
         ];
         $c_data = $this->httpQuery("dashboard/$dashboard_id/cards", [
            'timeout' => $this->api_config['timeout'],
            'json'    => $c_params
         ], 'POST');

         $card['id'] = isset($c_data['id'])
            ? $c_data['id']
            : false;
      };

      // append cards to dashboard
      $data = $this->httpQuery("dashboard/$dashboard_id/cards", [
         'timeout' => $this->api_config['timeout'],
         'json'    => $params
      ], 'PUT');

      return isset($data['id'])
         ? $data['id']
         : false;
   }

   function retrieveDashboard($dashboard_name) {
      if (($dashboards = $this->getDashboards()) !== false) {
         $dashboards = array_column($dashboards, 'id', 'name');

         if (isset($dashboards[$dashboard_name])) {
            return $dashboards[$dashboard_name];
         }
      }

      return false;
   }

   function getDashboard($dashboard_id) {
      if (!$this->checkSession()) {
         return false;
      }

      return $this->httpQuery("dashboard/$dashboard_id");
   }

   function getDashboards() {
      if (!$this->checkSession()) {
         return false;
      }

      $data = $this->httpQuery('dashboard');

      return $data;
   }

   function getDashboardCards($id) {
      $data = $this->httpQuery("dashboard/$id", [], 'GET');

      return isset($data['ordered_cards'])
         ? $data['ordered_cards']
         : false;
   }

   function createOrUpdateCard($card_name, $params = []) {
      $default_params = [
         'name'                   => $card_name,
         'sql'                    => '',
         'database_id'            => null,
         'collection_id'          => null,
         'visualization_settings' => [],
         'description'            => 'auto-generated by GLPI',
         'display'                => 'pie',
         'template_tags'          => [],
      ];
      $params = array_merge($default_params, $params);

      // check visualization_settings (must be a map)
      if (count($params['visualization_settings']) == 0) {
         $params['visualization_settings'] =  [
            '_glpi_forcemap' => true,
         ];
      }

      // prepare tags
      $templates_tags = [];
      foreach ($params['template_tags'] as $t_name => $tag) {
         $templates_tags[$t_name] = [
            'id'           => $this->generateUuid(),
            'display_name' => $t_name,
            'name'         => $t_name,
            'type'         => $tag['type'],
         ];

         if (isset($tag['widget_type'])) {
            $templates_tags[$t_name]['widget_type'] = $tag['widget_type'];
         }

         if (isset($tag['default'])) {
            $templates_tags[$t_name]['default'] = $tag['default'];
         }

         if ($tag['type'] == "dimension"
             && isset($_SESSION['metabase']['fields'][$tag['field']])) {
            $templates_tags[$t_name]['dimension'] = [
               "field-id",
               $_SESSION['metabase']['fields'][$tag['field']]
            ];
         }
      }
      unset($params['template_tags']);

      // prepare query post
      $params['dataset_query'] = [
         'database' => (int) $params['database_id'],
         'type'     => 'native',
         'native'   => [
            'query'         => $params['sql'],
            'template_tags' => $templates_tags
         ]
      ];
      unset($params['database_id']);
      unset($params['sql']);

      if ($card_id = $this->retrieveCard($card_name, $params['collection_id'])) {
         $params['original_card_id'] = $card_id;
         // update existing
         $data = $this->httpQuery("card/$card_id", [
            'timeout' => $this->api_config['timeout'],
            'json' => $params
         ], 'PUT');
      } else {
         // create new
         $data = $this->httpQuery('card', [
            'timeout' => $this->api_config['timeout'],
            'json' => $params
         ], 'POST');
      }

      return isset($data['id'])
         ? $data['id']
         : false;
   }

   function getCard($card_id) {
      if (!$this->checkSession()) {
         return false;
      }

      return $this->httpQuery("card/$card_id");
   }

   function retrieveCard($card_name, $collection_id) {
      if (($cards = $this->getCards($collection_id)) !== false) {
         $cards = array_column($cards, 'id', 'name');

         if (isset($cards[$card_name])) {
            return $cards[$card_name];
         }
      }

      return false;
   }

   /**
    * Get cards.
    *
    * @param integer $collection_id
    *
    * @return boolean|array Array of cards, false if an error occurs.
    */
   function getCards($collection_id = null) {
      if (!$this->checkSession()) {
         return false;
      }

      $cards = $this->httpQuery('card');

      if (!is_array($cards)) {
         return $cards;
      }

      $cards = array_filter(
         $cards,
         function ($card) use ($collection_id) {
            return is_array($card)
               && array_key_exists('collection_id', $card)
               && $collection_id === $card['collection_id'];
         }
      );

      return $cards;
   }

   /**
    * Enable embedded display on given dashboards.
    *
    * @param integer[] $uuids
    * @return boolean
    */
   function enableDashboardsEmbeddedDisplay($uuids) {
      if (!$this->checkSession()) {
         return false;
      }

      if (!$this->enableEmbedding()) {
         return false;
      }

      foreach ($this->getDashboards() as $dashboard) {
         if (in_array($dashboard['id'], $uuids) && !$dashboard['enable_embedding']) {
            $result = $this->httpQuery(
               'dashboard/' . $dashboard['id'],
               [
                  'json' => [
                     'enable_embedding' => true
                  ]
               ],
               'PUT'
            );

            if (false === $result) {
               Session::addMessageAfterRedirect(
                  sprintf(
                     __('Enabling embedded display fails for dashboard %s.', 'metabase'),
                     $dashboard['name']
                  ),
                  true,
                  ERROR
               );
            }
         }
      }

      return true;
   }

   /**
    * Defines enable-embedding setting to true.
    *
    * @return boolean
    */
   function enableEmbedding() {
      if (!$this->checkSession()) {
         return false;
      }

      $enabled = $this->httpQuery('setting/enable-embedding', [
         'json' => [
            'default'        => false,
            'description'    => "Enable admins to create embeddable code for Questions and Dashboards?",
            'env_name'       => "MB_ENABLE_EMBEDDING",
            'is_env_setting' => false,
            'originalValue'  => null,
            'placeholder'    => false,
            'value'          => true,
         ]
      ], 'PUT');

      return $enabled;
   }

   /**
    * Destroy session on metabase api (auth endpoint)
    *
    * @return array data returned by the api
    */
   function disconnect() {
      if (!isset($_SESSION['metabase']['session_token'])) {
         return true;
      }

      // send disconnect with http query
      $data = $this->httpQuery('session', [
         'json' => [
            'session_id' => $_SESSION['metabase']['session_token'],
         ]
      ], 'DELETE');

      unset($_SESSION['metabase']['session_token']);

      return $data !== false;
   }

   /**
    * format of metabase uuid in string lenght, separated by -
    */
   function generateUuid($specs = [8, 4, 4, 4, 12]) {
      $uuid = "";
      foreach ($specs as $nb) {
         $uuid .= substr(uniqid(), -$nb)."-";
      }

      return trim($uuid, '-');
   }

   /**
    * Return the metabase API base uri constructed from config
    *
    * @return string the uri
    */
   function getAPIBaseUri() {
      $url = trim($this->api_config['host'], '/');
      if (!empty($this->api_config['port'])) {
         $url.= ":{$this->api_config['port']}";
      }
      $url.= "/api/";
      return $url;
   }

   /**
    * Send an http query to the metabase api
    *
    * @param  string $resource the endpoint to use
    * @param  array  $params   an array containg these possible options:
    *                             - _with_metadata (bool, default false)
    *                             - allow_redirects (bool, default false)
    *                             - timeout (int, default 5)
    *                             - connect_timeout (int, default 2)
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
         'connect_timeout' => 2,
         'debug'           => false,
         'verify'          => false,
         'query'           => [], // url parameter
         'body'            => '', // raw data to send in body
         'json'            => [], // json data to send
         'headers'         => ['content-type'  => 'application/json',
                               'Accept'        => 'application/json'],
      ];
      // if connected, append auth token
      if (isset($_SESSION['metabase']['session_token'])) {
         $default_params['headers']['X-Metabase-Session'] = $_SESSION['metabase']['session_token'];
      }
      // append proxy params if exists
      if (!empty($CFG_GLPI['proxy_name'])
          && $this->api_config['use_proxy']) {
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
      $params = plugin_metabase_recursive_remove_empty($params);

      // init guzzle
      $http_client = new GuzzleHttp\Client(['base_uri' => $this->getAPIBaseUri()]);

      // send http request
      try {
         $response = $http_client->request($method,
                                           $resource,
                                           $params);
      } catch (GuzzleException $e) {
         $this->last_error = [
            'title'     => "Metabase API error",
            'exception' => $e->getMessage(),
            'params'    => $params,
         ];

         if ($e instanceof RequestException) {
            $this->last_error['request'] = Psr7\str($e->getRequest());

            if ($e->hasResponse()) {
               $response = $e->getResponse();
               $this->last_error['response'] = Psr7\str($response);

               // session with metabase ko, unset our token
               if ($response->getStatusCode() == 401) {
                  unset($_SESSION['metabase']['session_token']);
               }
            }
         }

         if ($e instanceof ConnectException) {
            Session::addMessageAfterRedirect(
               __("Query to metabase failed because operation timed out. Maybe you should increase the timeout value in plugin configuration", 'metabase'),
               true, ERROR);
         }

         if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
            Toolbox::backtrace();
            Toolbox::logDebug($this->last_error);
         }
         return false;
      }

      // parse http response
      $http_code     = $response->getStatusCode();
      $headers       = $response->getHeaders();

      // check http errors
      if (intval($http_code) > 400) {
         // we have an error if http code is greater than 400
         return false;
      }

      // cast body as string, guzzle return strems
      $json = (string) $response->getBody();
      $data = json_decode($json, true);

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
