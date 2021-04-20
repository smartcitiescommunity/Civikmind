<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMetabaseConfig extends Config {

   static function getTypeName($nb = 0) {
      return __('Metabase', 'metabase');
   }

   /**
    * Return the current config of the plugin store in the glpi config table
    *
    * @return array config with keys => values
    */
   static function getConfig() {
      return Config::getConfigurationValues('plugin:metabase');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Config":
            return self::createTabEntry(self::getTypeName());
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item,
                                            $tabnum = 1,
                                            $withtemplate = 0) {
      switch ($item->getType()) {
         case "Config":
            return self::showForConfig($item, $withtemplate);
      }

      return true;
   }

   static function showForConfig(Config $config, $withtemplate = 0) {
      global $CFG_GLPI;

      if (!self::canView()) {
         return false;
      }

      $current_config = self::getConfig();
      $canedit        = Session::haveRight(self::$rightname, UPDATE);
      echo "<div class='metabase_config'>";
      echo "<h1>".__("Configuration of Metabase integration")."</h1>";

      if ($canedit) {
         echo "<form name='form' action='".Toolbox::getItemTypeFormURL("Config")."' method='post'>";
      }

      echo "<div id='base_config' class='metabase_config_block'>";
      echo self::showField([
         'label' => __("Metabase host", 'metabase'),
         'attrs' => [
            'name'  => 'host',
            'value'       => $current_config['host'],
            'placeholder' => '127.0.0.1',
         ]
      ]);
      echo self::showField([
         'label' => __("Metabase port", 'metabase'),
         'attrs' => [
            'name'  => 'port',
            'value'       => $current_config['port'],
            'required'    => false,
            'placeholder' => '3000',
         ]
      ]);
      echo self::showField([
         'label' => __("username (metabase admin)", 'metabase'),
         'attrs' => [
            'name'  => 'username',
            'value' => $current_config['username'],
            'style' => 'width:200px;',
         ]
      ]);
      if (!empty($CFG_GLPI['proxy_name'])) {
         echo self::showField([
            'label'     =>      __("Use proxy"),
            'inputtype' => 'yesno',
            'attrs'     =>      [
               'name'  => 'use_proxy',
               'value' => $current_config['use_proxy'],
               'style' => 'width:200px;',
            ]
         ]);
      }
      echo self::showField([
         'inputtype' => 'password',
         'label'     => __("password"),
         'attrs'     => [
            'name'     => 'password',
            'value'    => '',
            'style'    => 'width:200px;',
            'required' => false,
         ]
      ]);

      echo self::showField([
         'label' => __("Metabase embedded token (to display dashboard in GLPI)", 'metabase'),
         'attrs' => [
            'name'  => 'embedded_token',
            'value'       => $current_config['embedded_token'],
            'placeholder' => '',
            'required'    => false
         ]
      ]);

      echo self::showField([
         'label' => __("Metabase url", 'metabase'),
         'help' => __("You may want to have a different dashboard url (with https for example) than the host (used to push the data) ", 'metabase'),
         'attrs' => [
            'name'  => 'metabase_url',
            'value'       => $current_config['metabase_url'],
            'placeholder' => 'http(s)://...',
         ]
      ]);

      echo self::showField([
         'inputtype' => 'number',
         'label'     => __("Timeout for sending data (in seconds)", 'metabase'),
         'attrs'     => [
            'name'   => 'timeout',
            'value'       => $current_config['timeout'],
            'placeholder' => '',
            'required'    => false
         ]
      ]);

      if ($canedit) {
         echo Html::hidden('config_class', ['value' => __CLASS__]);
         echo Html::hidden('config_context', ['value' => 'plugin:metabase']);
         echo Html::submit(_sx('button', 'Save'), [
            'name' => 'update'
         ]);
      }
      echo "</div>";

      Html::closeForm();

      if (self::isValid()) {
         echo "<h1>".__("API status", 'metabase')."</h1>";
         $apiclient    = new PluginMetabaseAPIClient;
         $all_status   = $apiclient->status();

         echo "<ul>";
         foreach ($all_status as $status_label => $status) {
            $color_png = "greenbutton.png";
            if (!$status) {
               $color_png = "redbutton.png";
            }
            echo "<li>";
            echo Html::image($CFG_GLPI['root_doc']."/pics/$color_png");
            echo "&nbsp;".$status_label;
            echo "</li>";
         }
         echo "</ul>";

         $error = $apiclient->getLastError();
         if (count($error)) {
            echo "<h1>".__("Last Error", 'metabase')."</h1>";
            if (isset($error['exception'])) {
               echo $error['exception'];
            } else {
               Html::printCleanArray($error);
            }

            echo "<p><strong>".GLPINetwork::getErrorMessage()."</strong></p>";
         }

         echo "<div id='actions'>";
         if ($canedit) {
            echo "<form name='form' action='".self::getFormUrl()."' method='post'>";
         }

         echo "<h1>".__("Action(s)", 'metabase')."</h1>";

         // If session is OK but database cannot be found, it has been probably deleted on metabase side
         $previousDbNotFound = $current_config['glpi_db_id'] != 0
             && $apiclient->checkSession()
             && false === $apiclient->getGlpiDatabase();

         if ($current_config['glpi_db_id'] == 0 || $previousDbNotFound) {
            if ($previousDbNotFound) {
               echo '<p><strong>' . __('Previously stored database is not existing anymore.', 'metabase') . '</strong></p>';
            }
            echo Html::submit(__("Create GLPI database in local Metabase", 'metabase'), [
               'name' => 'create_database'
            ]);

            $databases = $apiclient->getDatabases();
            if (is_array($databases) && count($databases) > 0) {
               echo __("OR set an existing database: ", 'metabase');

               Dropdown::showFromArray('db_id', array_column($databases, 'name', 'id'));

               echo Html::submit(__("Set database", 'metabase'), [
                  'name' => 'set_database'
               ]);
            }
         } else if ($apiclient->checkSession()) {
            echo Html::hidden('glpi_db_id', ['value' => $current_config['glpi_db_id']]);

            if ($current_config['datamodel_done']) {
               echo Html::submit(__("Push reports and dashboards in Metabase", 'metabase'), [
                  'name' => 'push_json'
               ]);
            }

            echo Html::submit(__("(Re)generate datamodel in Metabase", 'metabase'), [
               'name' => 'push_datamodel'
            ]);

            echo '<a href="' . Plugin::getWebDir('metabase') . '/front/collections.php" class="vsubmit">'
               . __('Show reports and dashboards specifications', 'metabase')
               . '</a>';
         }

         Html::closeForm();
         echo "</div>"; // #actions

      }
      echo "</div>"; // .metabase_config
   }

   static function createGLPIDatabase() {
      $apiclient = new PluginMetabaseAPIClient;

      // Remove previous database configuration
      Config::setConfigurationValues(
         'plugin:metabase',
         [
            'glpi_db_id'     => 0,
            'datamodel_done' => 0,
         ]
      );

      $data = $apiclient->createGlpiDatabase();

      if ($data !== false) {
         self::setExistingDatabase($data['id']);
         return true;
      }

      return false;
   }

   static function setExistingDatabase($db_id) {
      return Config::setConfigurationValues('plugin:metabase', [
         'glpi_db_id'=> $db_id
      ]);
   }

   static function createDataModel($db_id) {
      $api = new PluginMetabaseAPIClient;
      self::loadTablesAndFields($db_id);

      // detect foreign keys
      $fk_count = 0;
      $tables = $_SESSION['metabase']['tables'];
      $fields = $_SESSION['metabase']['fields'];
      foreach ($fields as $fieldname => $f_id_src) {
         list($table, $field) = explode('.', $fieldname);

         if (($fk_table = getTableNameForForeignKeyField($field)) !== ""
             && isset($tables[$fk_table])) {

            // create foreign key
            if (isset($fields[$fk_table.".id"])) {
               $api->createForeignKey($f_id_src, $fields[$fk_table.".id"]);
               $fk_count++;
            }
         }
      }

      Session::addMessageAfterRedirect("Foreign keys created: $fk_count");

      // map value for itilobjects harcoded fields
      $harcoded = $api->setItiObjectHardcodedMapping();

      // set config done
      if ($fk_count
          && $harcoded) {
         Config::setConfigurationValues('plugin:metabase', [
            'datamodel_done' => 1
         ]);
      }

      return true;
   }

   static function loadTablesAndFields($db_id) {
      $api = new PluginMetabaseAPIClient;
      $metadata = $api->getDatabaseMetadata($db_id);

      if ((!array_key_exists('tables', $metadata) || count($metadata['tables']) == 0)
          && (!array_key_exists(ERROR, $_SESSION["MESSAGE_AFTER_REDIRECT"]) || count($_SESSION["MESSAGE_AFTER_REDIRECT"][ERROR]) == 0)) {
         Session::addMessageAfterRedirect('Issue with db metadata, no tables found. You should discard saved field values for this db in metabase databases administration.', true, ERROR);
      }

      //flat all tables and fields to have their id
      $tables = [];
      $fields = [];
      if (isset($metadata['tables'])) {
         foreach ($metadata['tables'] as $table) {
            $tables[$table['name']] = $table['id'];

            foreach ($table['fields'] as $field) {
               $fields[$table['name'].".".$field['name']] = $field['id'];
            }
         }
      }

      $_SESSION['metabase']['tables'] = $tables;
      $_SESSION['metabase']['fields'] = $fields;
   }

   static function pushReports() {
      $current_config = self::getConfig();
      self::loadReports();
      self::loadTablesAndFields($current_config['glpi_db_id']);
      $api = new PluginMetabaseAPIClient;
      $col_counts = 0;
      $rep_counts = 0;

      // create collections
      $collections = array_unique(array_column($_SESSION['metabase']['reports'], 'collection'));
      $collections_keys = [];
      foreach ($collections as $collection) {
         if ($collection_id = $api->createOrGetCollection($collection)) {
            $col_counts++;
            $collections_keys[$collection] = $collection_id;
         }
      }

      Session::addMessageAfterRedirect("Collections created: $col_counts");

      // create reports (cards or questions in metabase)
      foreach ($_SESSION['metabase']['reports'] as &$report) {
         if ($report['card_id'] = $api->createOrUpdateCard($report['title'], [
            'database_id'            => $current_config['glpi_db_id'],
            'sql'                    => $report['sql'],
            'collection_id'          => $collections_keys[$report['collection']],
            'description'            => $report['description'],
            'display'                => $report['display'],
            'visualization_settings' => $report['visualization_settings'],
            'template_tags'          => $report['template_tags'],
         ])) {
            $rep_counts++;
         }
      }

      Session::addMessageAfterRedirect("questions created: $rep_counts");
   }

   static function pushDashboards() {
      self::loadDashboards();
      $api = new PluginMetabaseAPIClient;
      $dsh_count = 0;

      // Build collection "name to ID" mapping
      $collections = $api->getCollections();
      $collections_keys = [];
      foreach ($collections as $collection) {
         $collections_keys[$collection['name']] = $collection['id'];
      }

      // create reports (cards or questions in metabase)
      foreach ($_SESSION['metabase']['dashboards'] as &$dashboard) {

         // Defines collection ID using "name to ID" mapping
         $dashboard['collection_id'] = $collections_keys[$dashboard['collection']];
         unset($dashboard['collection']);

         if ($dashboard['id'] = $api->createOrGetDashboard($dashboard['name'], $dashboard)) {
            $dsh_count++;

            // prepare parameters (tags)
            $dashboard_cfg = [];
            $r_index = 0;
            foreach ($dashboard['reports'] as $report_key => &$report_cfg) {
               if (isset($_SESSION['metabase']['reports'][$report_key]['card_id'])) {
                  $card_id = $_SESSION['metabase']['reports'][$report_key]['card_id'];

                  $report_cfg['card_id'] = (int) $card_id;

                  $r_index++;

                  $final_parameter_mapping = [];
                  if (isset($report_cfg['parameter_mappings'])) {
                     foreach ($report_cfg['parameter_mappings'] as $map_slug => $fieldname) {
                        $parameter_index = array_search($map_slug,
                                                        array_column($dashboard['parameters'], 'slug'));
                        $parameters_id   = $dashboard['parameters'][$parameter_index]['id'];
                        $final_parameter_mapping[] = [
                           'card_id'      => (int) $card_id,
                           'parameter_id' => $parameters_id,
                           'target'       => [
                              'dimension',
                              [
                                 "template-tag",
                                 $map_slug
                              ]
                           ]
                        ];
                     }
                  }
                  $report_cfg['parameter_mappings'] = $final_parameter_mapping;

                  $dashboard_cfg[] = $report_cfg;
               } else {
                  Session::addMessageAfterRedirect("Report not found: $report_key");
               }
            }
            $api->setDashboardCards($dashboard['id'], ['cards' => $dashboard_cfg]);
         }
      }

      Session::addMessageAfterRedirect("Dashboards created: $dsh_count");
   }

   static function loadReports() {
      return self::loadDir(PLUGINMETABASE_REPORTS_DIR, 'reports');
   }

   static function loadDashboards() {
      return self::loadDir(PLUGINMETABASE_DASHBOARDS_DIR, 'dashboards');
   }

   static function loadDir($path = '', $session_key = '') {
      $_SESSION['metabase'][$session_key] = [];
      $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
      $success = true;
      foreach ($iterator as $file) {
         if ($file->isFile() && 'json' === $file->getExtension()) {
            $json = file_get_contents($file->getPathname());
            if (($report_array = json_decode($json, true))) {
               $_SESSION['metabase'][$session_key][$file->getBasename(".json")] = $report_array;
            } else {
               Session::addMessageAfterRedirect(sprintf(__("Cannot validate json for file %s"),
                                                        $file->getPathname()));
               $success = false;
            }
         }
      }

      return $success;
   }

   static function displayQuestionJson($question_id) {
      $apiclient = new PluginMetabaseAPIClient;
      $card      = $apiclient->getCard($question_id);
      $extract   = [
         'title'                  => $card['name'],
         'description'            => $card['description'],
         'collection'             => isset($card['collection']['name'])
                                       ? $card['collection']['name']
                                       : "",
         'display'                => $card['display'],
         'visualization_settings' => $card['visualization_settings'],
         'template_tags'          => [],
         'sql'                    => $card['dataset_query']['native']['query'],
      ];

      foreach ($card['dataset_query']['native']['template-tags'] as $tag_name => $tag) {
         $extract['template_tags'][$tag_name] = [
            'type'         => $tag['type'],
            'display_name' => $tag['display-name'],
         ];

         if (isset($tag['default'])) {
            $extract['template_tags'][$tag_name]['default'] = $tag['default'];
         }

         if (isset($tag['required'])) {
            $extract['template_tags'][$tag_name]['required'] = (bool) $tag['required'];
         }

         if (isset($tag['widget-type'])) {
            $extract['template_tags'][$tag_name]['widget_type'] = $tag['widget-type'];
         }

         if (isset($tag['dimension'][1])) {
            $extract['template_tags'][$tag_name]['field']
                = array_search($tag['dimension'][1], $_SESSION['metabase']['fields']);
         }
      }

      self::displayPrettyJson($extract);
   }

   static function displayDashboardJson($dashboard_id) {
      $apiclient = new PluginMetabaseAPIClient;
      $dashboard = $apiclient->getDashboard($dashboard_id);

      $extract = [
         'name'        => $dashboard['name'],
         'description' => $dashboard['description'],
         'reports'     => [],
         'parameters'  => [],
      ];

      $parameters_id = [];
      foreach ($dashboard['parameters'] as $parameter) {
         $extract['parameters'][] = [
            'default' => isset($parameter['default'])
                           ? $parameter['default']
                           : "",
            'name'    => $parameter['name'],
            'slug'    => $parameter['slug'],
            'type'    => $parameter['type'],
         ];

         $parameters_id[$parameter['id']] = $parameter['slug'];
      }

      foreach ($dashboard['ordered_cards'] as $card) {
         if (isset($card['card_id']) // only question (TODO support markdown cards)
             && $card['card']['dataset_query']['type'] === "native") { // only native questions
            $key = null;
            foreach ($_SESSION['metabase']['reports'] as $session_key => $session_report) {
               if ($session_report['title'] === $card['card']['name']) {
                  $key = $session_key;
               }
            }

            if ($key !== null) {
               $extract['reports'][$key] = [
                  'col'     => $card['col'],
                  'row'     => $card['row'],
                  'sizeX'   => $card['sizeX'],
                  'sizeY'   => $card['sizeY'],
               ];

               foreach ($card['parameter_mappings'] as $mapping) {
                  $mapping_key = $mapping['target'][1][1];
                  $field_id    = $card['card']['dataset_query']
                                      ['native']['template-tags']
                                      [$mapping_key]['dimension'][1];
                  $field_name  = array_search($field_id, $_SESSION['metabase']['fields']);
                  if ($field_name !== false) {
                     $extract['reports'][$key]['parameter_mappings']
                        [$parameters_id[$mapping['parameter_id']]] = $field_name;
                  }
               }
            }
         }
      }

      self::displayPrettyJson($extract);
      Html::printCleanArray($dashboard);
   }

   static function displayPrettyJson($array = []) {
      echo Html::css("lib/prism/prism.css");
      echo Html::script("lib/prism/prism.js");

      echo "<pre><code class='language-json'>";
      echo preg_replace("/(^|\G) {4}/m", "   ", // replace indentation from 4 to 3 spaces
                        json_encode($array, JSON_PRETTY_PRINT
                                          + JSON_UNESCAPED_UNICODE
                                          + JSON_UNESCAPED_SLASHES));
      echo "</code></pre>";
   }

   /**
    * Show a single config field
    * Generic method who call the different GLPI function to display a field
    *
    * @param  array  $options a list of options:
    *                            - inputtype (string), can be
    *                               * text
    *                               * password
    *                               * yesno
    *                               * dropdown
    *                            - itemtype (only for input=dropdown)
    *                            - label, <label> tag to append to the field
    *                            - attrs, an array containing html attributes
    * @return string the html
    */
   static function showField($options = []) {
      $rand            = mt_rand();
      $default_options = [
         'inputtype' => 'text',
         'itemtype'  => '',
         'label'     => '',
         'help'      => '',
         'attrs'     => [
            'name'        => '',
            'value'       => '',
            'placeholder' => '',
            'style'       => 'width:50%;',
            'id'          => "metabaseconfig_field_$rand",
            'class'       => 'metabase_input',
            'required'    => 'required',
            'on_change'   => ''
         ]
      ];
      $options = array_replace_recursive($default_options, $options);

      if ($options['attrs']['required'] === false) {
         unset($options['attrs']['required']);
      }

      $out = "";
      $out.= "<div class='metabase_field'>";

      // call the field according to its type
      switch ($options['inputtype']) {
         default:
         case 'text':
            $out.= Html::input('fakefield', ['style' => 'display:none;']);
            $out.= Html::input($options['attrs']['name'], $options['attrs']);
            break;

         case 'password':
            $out.=  "<input type='password' name='fakefield' style='display:none;'>";
            $out.=  "<input type='password'";
            foreach ($options['attrs'] as $key => $value) {
               $out.= "$key='$value' ";
            }
            $out.= ">";
            break;

         case 'yesno':
            $options['attrs']['display'] = false;
            $out.= Dropdown::showYesNo($options['attrs']['name'], $options['attrs']['value'], -1, $options['attrs']);
            break;

         case 'dropdown':
            $options['attrs']['display'] = false;
            $out.= Dropdown::show($options['itemtype'], $options['attrs']);
            break;

         case 'number':
            $options['attrs']['display'] = false;
            $out.= Dropdown::showNumber($options['attrs']['name'], $options['attrs']);
            break;
      }

      $out.= "<label class='metabase_label' for='{$options['attrs']['id']}'>
              {$options['label']}</label>";

      if (strlen($options['help'])) {
         $out.= "<i class='fa metabase_help fa-info-circle' title='{$options['help']}'></i>";
      }

      $out.= "</div>";

      return $out;
   }

   /**
    * Check if current saved config is valid
    * @param  boolean $with_api also check api status
    * @return boolean
    */
   static function isValid($with_api = false) {
      $current_config = self::getConfig();
      $valid_config =  (!empty($current_config['host'])
                        && !empty($current_config['username'])
                        && !empty($current_config['password']));

      $valid_api = true;
      if ($with_api) {
         $apiclient = new PluginMetabaseAPIClient;
         $apiclient->connect();
         $valid_api = !in_array(false, $apiclient->status());
      }

      return ($valid_config && $valid_api);
   }

   /**
    * Hook called when updating plugin configuration.
    *
    * @param array $input
    * @return array
    * @see Config::prepareInputForUpdate()
    */
   public static function configUpdate($input) {

      if (isset($input["password"])) {
         if (empty($input["password"])) {
            unset($input["password"]);
         } else {
            if (version_compare(GLPI_VERSION, '9.5.3', '<')) {
               // Since GLPI 9.5.3, encrypt is done by GLPI core config class.
               $input["password"] = Toolbox::sodiumEncrypt($input["password"]);
            }

            // Remove existing metabase session token to force reconnection
            unset($_SESSION['metabase']['session_token']);
         }
      }

      return $input;
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   static function install(Migration $migration) {
      $current_config = self::getConfig();

      // Encrypt password with sodium if previously stored without sodium encryption
      if (!array_key_exists('is_password_sodium_encrypted', $current_config) || !$current_config['is_password_sodium_encrypted']) {
         if (!empty($current_config['password'])) {
            if (array_key_exists('is_password_random_encrypted', $current_config) && $current_config['is_password_random_encrypted']) {
               // Decrypt using randomized key
               $current_config['password'] = Toolbox::decrypt($current_config['password']);
            } else if (array_key_exists('is_password_encrypted', $current_config) && $current_config['is_password_encrypted']) {
               // Decrypt using GLPIKEY
               $current_config['password'] = Toolbox::decrypt($current_config['password'], GLPIKEY);
            }
            $password = version_compare(GLPI_VERSION, '9.5.3', '>=')
               ? $current_config['password'] // Since GLPI 9.5.3, encrypt is done by GLPI core config class
               : Toolbox::sodiumEncrypt($current_config['password']);
            Config::setConfigurationValues(
               'plugin:metabase',
               [
                  'password' => $password,
               ]
            );
         }

         // Add flag in config to prevent re-encrypt
         Config::setConfigurationValues('plugin:metabase', ['is_password_sodium_encrypted' => 1]);
         Config::deleteConfigurationValues('plugin:metabase', ['is_password_encrypted', 'is_password_random_encrypted']);
      }

      // fill config table with default values if missing
      foreach ([
         // api access
         'host'           => '',
         'port'           => 3000,
         'username'       => '',
         'password'       => '',
         'glpi_db_id'     => 0,
         'datamodel_done' => 0,
         'embedded_token' => '',
         'metabase_url'   => '',
         'use_proxy'      => 1,
         'timeout'        => 30,
      ] as $key => $value) {
         if (!isset($current_config[$key])) {
            Config::setConfigurationValues('plugin:metabase', [$key => $value]);
         }
      }
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   static function uninstall() {
      $config = new Config();
      $config->deleteByCriteria(['context' => 'plugin:metabase']);

      return true;
   }
}