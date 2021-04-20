<?php
/*
 * @version $Id
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2011 by the mreporting Development Team.

 https://forge.indepnet.net/projects/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginMreportingInventory Extends PluginMreportingBaseclass {
   /* ==== SPECIFIC SELECTORS FOR INVENTORY ==== */
   static function selectorMultipleStates() {
      self::selectorForMultipleStates('states_id', [], _sx("item", "State"));
   }

   static function selectorForMultipleStates($field, $condition = [], $label = '') {
      global $DB;

      $selected_states = [];
      if (isset($_SESSION['mreporting_values'][$field])) {
         $selected_states = $_SESSION['mreporting_values'][$field];
      } else {
         $selected_states = self::getDefaultState();
      }
      $datas = [];
      foreach (getAllDataFromTable('glpi_states', $condition) as $data) {
         $datas[$data['id']] = $data['completename'];
      }

      $param = ['multiple' => true,
                'display'  => true,
                'size'     => count($selected_states),
                'values'   => $selected_states];

      echo "<br /><b>".$label." : </b><br />";
      Dropdown::showFromArray($field, $datas, $param);
   }

   static function getDefaultState() {
      global $DB;

      $states = [];
      $query  = "SELECT `id`FROM `glpi_states` WHERE `name` IN ('En service')";
      foreach ($DB->request($query) as $data) {
         $states[] = $data['id'];
      }
      return $states;
   }

   static function getStateCondition($field, $as_array = false) {
      $sql_states = ($as_array ? [] : "");
      if (isset($_SESSION['mreporting_values']['states_id'])) {
         if (is_array($_SESSION['mreporting_values']['states_id'])) {
            if ($as_array) {
               $sql_states[$field] = $_SESSION['mreporting_values']['states_id'];
            } else {
               $sql_states = " AND $field IN (".implode(',', $_SESSION['mreporting_values']['states_id']).")";
            }
         } else if ($_SESSION['mreporting_values']['states_id'] > 0) {
            if ($as_array) {
               $sql_states[$field] = $_SESSION['mreporting_values']['states_id'];
            } else {
               $sql_states = " AND $field = ".$_SESSION['mreporting_values']['states_id'];
            }
         }
      }
      return $sql_states;
   }

   /* ==== MANUFACTURERS REPORTS ==== */
   function reportPieComputersByFabricant($config = []) {
      $_SESSION['mreporting_selector']['reportPieComputersByFabricant'] = ['multiplestates'];
      return $this->computersByFabricant($config);
   }

   function reportHbarComputersByFabricant($config = []) {
      $_SESSION['mreporting_selector']['reportHbarComputersByFabricant'] = ['multiplestates'];
      return $this->computersByFabricant($config);
   }

   function computersByFabricant($config = []) {
      global $DB;

      $sql_entities = " AND c.`entities_id` IN ({$this->where_entities})";
      $sql_states   = self::getStateCondition('c.states_id');

      $query = "SELECT m.`name`   as Manufacturer,
                       count(*) as Total,
                       count(*) * 100 / (SELECT count(*)
                           FROM glpi_computers     as c,
                                glpi_manufacturers as m
                           WHERE c.`is_deleted` = 0
                                 AND c.`is_template` = 0
                                 AND c.`manufacturers_id` = m.`id`
                                 $sql_entities
                                 $sql_states) as Percent
         FROM glpi_computers     as c,
              glpi_manufacturers as m
         WHERE c.`manufacturers_id` = m.`id`
               $sql_entities
               $sql_states
               AND c.`is_deleted` = 0
               AND c.`is_template` = 0
         GROUP BY m.`name`
         ORDER BY Total DESC";
      $result = $DB->query($query);

      $datas = [];
      while ($computer = $DB->fetchAssoc($result)) {
         if ($computer['Total']) {
            $percent = round($computer['Percent'], 2);
            $datas['datas'][$computer['Manufacturer']." ($percent %)"] = $computer['Total'];
         }
      }

      return $datas;
   }

   /* ==== COMPUTER'S TYPE REPORTS ==== */
   function reportPieComputersByType($config = []) {
      $_SESSION['mreporting_selector']['reportPieComputersByType'] = ['multiplestates'];
      return $this->computersByType($config);
   }

   function reportHbarComputersByType($config = []) {
      $_SESSION['mreporting_selector']['reportHbarComputersByType'] = ['multiplestates'];
      return $this->computersByType($config);
   }

   function computersByType($config = []) {
      global $DB;

      $sql_entities = " AND c.`entities_id` IN ({$this->where_entities})";
      $sql_states   = self::getStateCondition('c.states_id');
      $query = "SELECT t.`name`   as Type,
                       count(*) as Total,
                       count(*) * 100 / (SELECT count(*)
                           FROM glpi_computers     as c,
                                glpi_computertypes as t
                           WHERE c.`is_deleted` = 0
                                 AND c.`is_template` = 0
                                 AND c.`computertypes_id` = t.`id`
                                 $sql_entities
                                 $sql_states) as Percent
         FROM glpi_computers     as c,
              glpi_computertypes as t
         WHERE c.`computertypes_id` = t.`id`
               $sql_entities
               $sql_states
               AND c.`is_deleted` = 0
               AND c.`is_template` = 0
         GROUP BY t.`name`
         ORDER BY Total DESC";
      $result = $DB->query($query);
      $datas = [];
      while ($computer = $DB->fetchAssoc($result)) {
         $percent = round($computer['Percent'], 2);
         $datas['datas'][$computer['Type']." ($percent %)"] = $computer['Total'];
      }

      return $datas;
   }

   /* ==== COMPUTER'S AGE REPORTS ==== */
   function reportPieComputersByAge($config = []) {
      $_SESSION['mreporting_selector']['reportPieComputersByAge'] = ['multiplestates'];
      return $this->computersByAge($config);
   }

   function reportHbarComputersByAge($config = []) {
      $_SESSION['mreporting_selector']['reportHbarComputersByAge'] = ['multiplestates'];
      return $this->computersByAge($config);
   }

   function computersByAge($config = []) {
      global $DB;

      $sql_entities = " AND c.`entities_id` IN ({$this->where_entities})";
      $sql_states   = self::getStateCondition('c.states_id');
      $datas = [];

      $query = "SELECT '< 1 year' Age, count(*) Total, count(*) * 100 / (SELECT count(*)
                           FROM glpi_computers as c,
                                glpi_infocoms  as i
                           WHERE c.`id` = i.`items_id`
                             AND c.`is_deleted` = 0
                             AND c.`is_template` = 0
                             AND itemtype = 'Computer'
                             $sql_entities
                             $sql_states) Percent
         FROM glpi_computers as c,
              glpi_infocoms  as i
         WHERE c.`id` = i.`items_id`
           AND c.`is_deleted` = 0
           AND c.`is_template` = 0
           AND itemtype = 'Computer'
           AND i.`warranty_date` > CURRENT_DATE - INTERVAL 1 YEAR
           $sql_entities
           $sql_states
         UNION
         SELECT '1-3 years' Age, count(*) Total, count(*) * 100 / (SELECT count(*)
                                    FROM glpi_computers c,  glpi_infocoms i
                                    WHERE c.`id` = i.`items_id`
                                    AND c.`is_deleted` = 0
                                    AND c.`is_template` = 0
                                    AND itemtype = 'Computer'
                                    $sql_entities
                                    $sql_states) Percent
         FROM glpi_computers as c,
              glpi_infocoms  as i
         WHERE c.`id` = i.`items_id`
           AND c.`is_deleted` = 0
           AND c.`is_template` = 0
           AND itemtype = 'Computer'
           AND i.`warranty_date` <= CURRENT_DATE - INTERVAL 1 YEAR
           AND i.`warranty_date` > CURRENT_DATE - INTERVAL 3 YEAR
           $sql_entities
           $sql_states
         UNION
         SELECT '3-5 years' Age, count(*) Total, count(*) * 100 / (SELECT count(*)
                                    FROM glpi_computers c,  glpi_infocoms i
                                    WHERE c.`id` = i.`items_id`
                                    AND c.`is_deleted` = 0
                                    AND c.`is_template` = 0
                                    AND itemtype = 'Computer'
                                    $sql_entities
                                    $sql_states) Percent
         FROM glpi_computers as c,
              glpi_infocoms  as i
         WHERE c.`id` = i.`items_id`
           AND c.`is_deleted` = 0
           AND c.`is_template` = 0
           AND itemtype = 'Computer'
           AND i.`warranty_date` <= CURRENT_DATE - INTERVAL 3 YEAR
           AND i.`warranty_date` > CURRENT_DATE - INTERVAL 5 YEAR
           $sql_entities
           $sql_states
         UNION
         SELECT '> 5 years' Age, count(*) Total, count(*) * 100 / (SELECT count(*)
                                    FROM glpi_computers c,  glpi_infocoms i
                                    WHERE c.`id` = i.`items_id`
                                    AND c.`is_deleted` = 0
                                    AND c.`is_template` = 0
                                    AND itemtype = 'Computer'
                                    $sql_entities
                                    $sql_states) Percent
         FROM glpi_computers as c,
              glpi_infocoms  as i
         WHERE c.`id` = i.`items_id`
           AND c.`is_deleted` = 0
           AND c.`is_template` = 0
           AND itemtype = 'Computer'
           AND i.`warranty_date` <= CURRENT_DATE - INTERVAL 5 YEAR
           $sql_entities
           $sql_states
         UNION
         SELECT 'Undefined' Age, count(*) Total, count(*) * 100 / (SELECT count(*)
                                    FROM glpi_computers c,  glpi_infocoms i
                                    WHERE c.`id` = i.`items_id`
                                    AND c.`is_deleted` = 0
                                    AND c.`is_template` = 0
                                    AND itemtype = 'Computer'
                                    $sql_entities
                                    $sql_states) Percent
         FROM glpi_computers as c,
              glpi_infocoms  as i
         WHERE c.`id` = i.`items_id`
           AND c.`is_deleted` = 0
           AND c.`is_template` = 0
           AND itemtype = 'Computer'
            AND i.`warranty_date` IS NULL
            $sql_entities
            $sql_states";
      $result = $DB->query($query);

      while ($computer = $DB->fetchAssoc($result)) {
         $percent = round($computer['Percent'], 2);

         $datas['datas'][__($computer['Age'], 'mreporting')." ($percent %)"] = $computer['Total'];
      }

      return $datas;

   }


   /* === OS REPORTS === */
   function reportPieComputersByOS($config = []) {
      $_SESSION['mreporting_selector']['reportPieComputersByOS'] = ['multiplestates'];
      return $this->computersByOS($config);
   }

   function reportHbarComputersByOS($config = []) {
      $_SESSION['mreporting_selector']['reportHbarComputersByOS'] = ['multiplestates'];
      return $this->computersByOS($config);
   }

   function computersByOS($config = []) {
      global $DB;

      $sql_entities = " AND c.`entities_id` IN ({$this->where_entities})";
      $sql_states   = self::getStateCondition('c.states_id');
      $oses = ['Windows' => 'Windows',
               'Linux'   => 'Linux|Ubuntu|openSUSE',
               'Solaris' => 'Solaris',
               'AIX'     => 'AIX',
               'BSD'     => 'BSD',
               'VMWare'  => 'VMWare',
               'MAC'     => 'MAC',
               'Android' => 'Android',
               'HP-UX'   => 'HP-UX'];
       $query = "";
       $first = true;
       $notlike = "";
      foreach ($oses as $os => $search) {
         $query.=(!$first?" UNION ":"")
           ."\n SELECT '$os' AS OS, count(*) AS Total, count(*) * 100 / (SELECT count(*)
                                                                        FROM glpi_computers        as c,
                                                                             glpi_operatingsystems as os,
                                                                             glpi_items_operatingsystems as ios
                                                                        WHERE c.`is_deleted`='0' AND c.`is_template`='0'
                                                                        AND ios.`operatingsystems_id` = os.`id`
                                                                        AND ios.items_id = c.id
                                                                        AND ios.itemtype = 'Computer'
                                                                            $sql_entities
                                                                            $sql_states) AS Percent
               FROM glpi_computers        as c,
                    glpi_operatingsystems as os,
                    glpi_items_operatingsystems as ios
               WHERE ios.`operatingsystems_id` = os.`id`
                     AND ios.items_id = c.id
                     AND ios.itemtype = 'Computer'
                     AND c.`is_deleted`='0'
                     AND c.`is_template`='0'
                     AND os.`name` REGEXP '$search'
                     $sql_entities
                     $sql_states";

         $notlike.= " AND os.`name` NOT REGEXP '$search'";
         $first = false;
      }
        $query .= " UNION
         SELECT '".__("Others")."' AS OS, count(*) Total, count(*) * 100 / (SELECT count(*)
                                    FROM glpi_computers        as c,
                                         glpi_operatingsystems as os,
                                         glpi_items_operatingsystems as ios
                                    WHERE c.`is_deleted`= 0
                                          AND c.`is_template`=0
                                          AND ios.`operatingsystems_id` = os.`id`
                                          AND ios.items_id = c.id
                                          $sql_entities
                                          $sql_states) as Percent
         FROM glpi_computers        as c,
              glpi_operatingsystems as os,
              glpi_items_operatingsystems as ios
         WHERE ios.`operatingsystems_id` = os.`id`
           AND ios.items_id = c.id
           AND ios.itemtype = 'Computer'
           AND c.`is_deleted` = 0
           AND c.`is_template`=0
           $notlike
           $sql_entities
           $sql_states";

        $query.=" ORDER BY Total DESC";
        $result = $DB->query($query);

        $datas = [];
      while ($computer = $DB->fetchAssoc($result)) {
          $percent = round($computer['Percent'], 2);
         if ($computer['Total']) {
            $datas['datas'][$computer['OS']." ($percent %)"] = $computer['Total'];
         }
      }

        return $datas;
   }


   function reportHbarWindows($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarWindows'] = ['multiplestates'];

      $sql_states   = self::getStateCondition('glpi_computers.states_id', true);
      $total_computers = countElementsInTable(
         'glpi_computers', [
            'is_deleted'   => 0,
            'is_template'  => 0,
            'entities_id'  => $this->where_entities_array
         ]
      );

      $list_windows = ['Windows 3.1', 'Windows 95', 'Windows 98', 'Windows 2000 Pro',
                       'Windows XP', 'Windows 7', 'Windows Vista', 'Windows 8', 'Windows 10',
                       'Windows 2000 Server', 'Server 2003', 'Server 2008', 'Server 2012'];
      $data = [];
      foreach ($list_windows as $windows) {
         $oses = [];
         $ositerator = $DB->request('glpi_operatingsystems', ['name' => ['LIKE', "%$windows%"]]);
         while ($os = $ositerator->next()) {
            $oses[] = $os['id'];
         }
         if (!empty($oses)) {
            $number = countElementsInTable(
               'glpi_computers', [
                  'INNER JOIN' => [
                     'glpi_items_operatingsystems' => [
                        'FKEY' => [
                           'glpi_computers'              => 'id',
                           'glpi_items_operatingsystems' => 'items_id'
                        ]
                     ]
                  ],
                  'WHERE' => [
                     'glpi_items_operatingsystems.operatingsystems_id'  => $oses,
                     'glpi_items_operatingsystems.itemtype'             => 'Computer',
                     'glpi_computers.is_deleted'                        => 0,
                     'glpi_computers.is_template'                       => 0,
                     'glpi_computers.entities_id'                       => $this->where_entities_array
                  ] + $sql_states
               ]
            );

            $percent = round($number * 100 / $total_computers). " % du parc";
            if ($number) {
               $data['datas'][$windows." ($percent)"] = $number;
            }
         }
      }
      if (isset($data['datas']) && !empty($data['datas'])) {
         arsort($data['datas']);
      }
      return $data;
   }

   function reportHbarLinux($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarLinux'] = ['multiplestates'];
      $sql_states = self::getStateCondition('glpi_computers.states_id', true);
      $sql_states2 = self::getStateCondition('c.states_id', true);

      $data = [];
      foreach ($DB->request('glpi_operatingsystems', "name LIKE '%Linux%' OR name LIKE '%Ubuntu%' OR name LIKE '%openSUSE%'") as $os) {
         $iterator = $DB->request(
            'glpi_computers', [
               'SELECT' => [
                  'glpi_operatingsystemversions.name'
               ],
               'COUNT' => 'cpt',
               'INNER JOIN' => [
                  'glpi_items_operatingsystems' => [
                     'FKEY' => [
                        'glpi_computers'              => 'id',
                        'glpi_items_operatingsystems' => 'items_id'
                     ]
                  ]
               ],
               'LEFT JOIN' => [
                  'glpi_operatingsystemversions' => [
                     'FKEY' => [
                        'glpi_items_operatingsystems'    => 'operatingsystemversions_id',
                        'glpi_operatingsystemversions'   => 'id'
                     ]
                  ]
               ],
               'WHERE' => [
                  'glpi_items_operatingsystems.operatingsystems_id'  => $os['id'],
                  'glpi_items_operatingsystems.itemtype'             => 'Computer',
                  'glpi_computers.is_deleted'                        => 0,
                  'glpi_computers.is_template'                       => 0,
                  'glpi_computers.entities_id'                       => $this->where_entities_array,
               ] + $sql_states + $sql_states2,
               'GROUPBY' => ['operatingsystemversions_id'],
               'ORDER' => ['glpi_operatingsystemversions.name']
            ]
         );

         while ($version = $iterator->next()) {
            if ($version['name'] != '' && $version['cpt']) {
               $data['datas'][$os['name']. " ".$version['name']] = $version['cpt'];
            }
         }
      }
      if (isset($data['datas']) && !empty($data['datas'])) {
         arsort($data['datas']);
      }
      return $data;
   }

   function reportHbarLinuxDistro($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarLinuxDistro'] = ['multiplestates'];
      $sql_states = self::getStateCondition('glpi_computers.states_id', true);

      $data = [];
      foreach ($DB->request('glpi_operatingsystems', "name LIKE '%Linux%' OR name LIKE '%Ubuntu%' OR name LIKE '%openSUSE%'") as $os) {
         $number = countElementsInTable(
            'glpi_computers', [
               'INNER JOIN' => [
                  'glpi_items_operatingsystems' => [
                     'FKEY' => [
                        'glpi_computers'              => 'id',
                        'glpi_items_operatingsystems' => 'items_id'
                     ]
                  ]
               ],
               'WHERE' => [
                  'glpi_items_operatingsystems.operatingsystems_id'  => $os['id'],
                  'glpi_items_operatingsystems.itemtype'             => 'Computer',
                  'glpi_computers.is_deleted'                        => 0,
                  'glpi_computers.is_template'                       => 0,
                  'glpi_computers.entities_id'                       => $this->where_entities_array
               ] + $sql_states
            ]
         );

         if ($number) {
            $data['datas'][$os['name']] = $number;
         }
      }
      if (isset($data['datas']) && !empty($data['datas'])) {
         arsort($data['datas']);
      }
      return $data;
   }

   function reportHbarMac($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarMac'] = ['multiplestates'];
      $sql_states  = self::getStateCondition('glpi_computers.states_id', true);
      $sql_states2 = self::getStateCondition('c.states_id', true);

      $data = [];
      $ositerator = $DB->request('glpi_operatingsystems', ['name' => ['LIKE', '%Mac OS%']]);
      while ($os = $ositerator->next()) {
         $iterator = $DB->request(
            'glpi_computers', [
               'SELECT' => [
                  'glpi_operatingsystemversions.name'
               ],
               'COUNT' => 'cpt',
               'INNER JOIN' => [
                  'glpi_items_operatingsystems' => [
                     'FKEY' => [
                        'glpi_computers'              => 'id',
                        'glpi_items_operatingsystems' => 'items_id'
                     ]
                  ]
               ],
               'LEFT JOIN' => [
                  'glpi_operatingsystemversions' => [
                     'FKEY' => [
                        'glpi_items_operatingsystems'    => 'operatingsystemversions_id',
                        'glpi_operatingsystemversions'   => 'id'
                     ]
                  ]
               ],
               'WHERE' => [
                  'glpi_items_operatingsystems.operatingsystems_id'  => $os['id'],
                  'glpi_items_operatingsystems.itemtype'             => 'Computer',
                  'glpi_computers.is_deleted'                        => 0,
                  'glpi_computers.is_template'                       => 0,
                  'glpi_computers.entities_id'                       => $this->where_entities_array,
               ] + $sql_states + $sql_states2,
               'GROUPBY' => ['operatingsystemversions_id'],
               'ORDER' => ['glpi_operatingsystemversions.name']
            ]
         );

         while ($version = $iterator->next()) {
            if ($version['name'] != '' && $version['cpt']) {
               $data['datas'][$os['name']. " ".$version['name']] = $version['cpt'];
            }
         }
      }
      return $data;
   }

   function reportHbarMacFamily($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarMacFamily'] = ['multiplestates'];
      $sql_states  = self::getStateCondition('glpi_computers.states_id', true);
      $sql_states2 = self::getStateCondition('c.states_id', true);

      $data = [];
      $ositerator = $DB->request('glpi_operatingsystems', ['name' => ['LIKE', '%Mac OS%']]);
      while ($os = $ositerator->next()) {
         $iterator = $DB->request(
            'glpi_computers', [
               'SELECT' => [
                  'glpi_operatingsystemversions.name'
               ],
               'COUNT' => 'cpt',
               'INNER JOIN' => [
                  'glpi_items_operatingsystems' => [
                     'FKEY' => [
                        'glpi_computers'              => 'id',
                        'glpi_items_operatingsystems' => 'items_id'
                     ]
                  ]
               ],
               'LEFT JOIN' => [
                  'glpi_operatingsystemversions' => [
                     'FKEY' => [
                        'glpi_items_operatingsystems'    => 'operatingsystemversions_id',
                        'glpi_operatingsystemversions'   => 'id'
                     ]
                  ]
               ],
               'WHERE' => [
                  'glpi_items_operatingsystems.operatingsystems_id'  => $os['id'],
                  'glpi_items_operatingsystems.itemtype'             => 'Computer',
                  'glpi_computers.is_deleted'                        => 0,
                  'glpi_computers.is_template'                       => 0,
                  'glpi_computers.entities_id'                       => $this->where_entities_array,
               ] + $sql_states + $sql_states2,
               'GROUPBY' => ['operatingsystemversions_id'],
               'ORDER' => ['glpi_operatingsystemversions.name']
            ]
         );

         while ($version = $iterator->next()) {
            if ($version['name'] != '' && $version['cpt']) {
               if (preg_match("/(10.[0-9]+)/", $version['name'], $results)) {
                  if (!isset($data['datas'][$os['name']. " ".$results[1]])) {
                     $data['datas'][$os['name']. " ".$results[1]] = $version['cpt'];
                  } else {
                     $data['datas'][$os['name']. " ".$results[1]] += $version['cpt'];
                  }
               }
            }
         }
      }
      if (isset($data['datas']) && count($data['datas'])) {
         arsort($data['datas']);
      }
      return $data;
   }


   /* ==== FUSIONINVENTORY REPORTS ==== */
   function reportPieFusionInventory($config = []) {
      $_SESSION['mreporting_selector']['reportPieFusionInventory'] = ['multiplestates'];
      return $this->fusionInventory($config);
   }

   function reportHbarFusionInventory($config = []) {
      $_SESSION['mreporting_selector']['reportHbarFusionInventory'] = ['multiplestates'];
      return $this->fusionInventory($config);
   }

   function fusionInventory($config = []) {
      global $DB;

      $plugin = new Plugin();
      if (!$plugin->isActivated('fusioninventory')) {
         return [];
      }
      $sql_states = self::getStateCondition('glpi_computers.states_id', true);
      $total_computers = countElementsInTable(
         'glpi_computers',
         [
            'is_deleted'  => 0,
            'is_template' => 0,
            'entities_id' => $this->where_entities_array,
         ] + $sql_states
      );

      $query = "SELECT count(*) AS cpt, `useragent`
                FROM `glpi_plugin_fusioninventory_agents`
                WHERE `computers_id` > 0
                GROUP BY `useragent`
                ORDER BY cpt DESC";

      $data = [];
      foreach ($DB->request($query) as $agent) {
         $values = [];
         if (preg_match('/FusionInventory-Agent_v(.*)/i', $agent['useragent'], $values)) {
            $useragent = $values['1'];
         } else {
            $useragent = $agent['useragent'];
         }
         $data['datas'][$useragent] = $agent['cpt'];

      }
      return $data;
   }

   /* ==== MONITOR REPORST ==== */
   function reportHbarMonitors($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarMonitors'] = ['multiplestates'];
      $sql_states = self::getStateCondition('c.`states_id`');

      $query = "SELECT COUNT(*) AS cpt
                FROM `glpi_computers_items` AS ci,
                     `glpi_computers` AS c
                WHERE `ci`.`itemtype` = 'Monitor'
                  AND `c`.`is_deleted` = '0'
                  AND `ci`.`computers_id` = c.`id`
                  AND `c`.`is_template` = '0'
                  AND c.`entities_id` IN ({$this->where_entities})
                  $sql_states
                GROUP BY `ci`.`computers_id`
                ORDER BY `cpt`";
      $data = [];
      foreach ($DB->request($query) as $result) {
         $label = $result['cpt']." "._n('Monitor', 'Monitors', $result['cpt']);
         if (!isset($data['datas'][$label])) {
            $data['datas'][$label] = 0;
         }
         $data['datas'][$label] = $data['datas'][$label]+1;
      }

      return $data;
   }

   /* ==== COMPUTER'S STATE REPORTS ==== */
   function reportHbarComputersByStatus($config = []) {
      global $DB;

      $query = "SELECT t.`name` status, count(*) Total, count(*) * 100 / (SELECT count(*)
                           FROM glpi_computers as c,
                                glpi_states    as t
                           WHERE c.`states_id` = t.`id`
                             AND c.`entities_id` IN ({$this->where_entities})
                             AND c.`is_deleted` = 0
                             AND c.`is_template` = 0) Percent
         FROM glpi_computers as c,
              glpi_states    as t
         WHERE c.`states_id` = t.`id`
           AND c.`entities_id` IN ({$this->where_entities})
           AND c.`is_deleted` = 0
           AND c.`is_template` = 0
         GROUP BY t.`name`";
      $result = $DB->query($query);
      $datas = [];
      while ($computer = $DB->fetchAssoc($result)) {
         $percent = round($computer['Percent'], 2);
         $datas['datas'][$computer['status']." ($percent %)"] = $computer['Total'];
      }

      return $datas;
   }

   function reportHbarPrintersByStatus($config = []) {
      global $DB;

      $datas = [];

      $condition = " AND c.entities_id IN (".$this->where_entities.")";

      $query = "SELECT t.name status, count(*) Total, count(*)*100/(
                     SELECT count(*)
                     FROM glpi_printers c, glpi_states t
                     WHERE c.`is_deleted`=0 AND c.`is_template`=0
                     AND c.states_id = t.id $condition) Pourcentage
                FROM glpi_printers c, glpi_states t
                WHERE c.states_id = t.id $condition  AND c.`is_deleted`=0 AND c.`is_template`=0
                GROUP BY t.name";
      $result = $DB->query($query);

      while ($printer = $DB->fetchAssoc($result)) {
         $pourcentage = round($printer['Pourcentage'], 2);
         $datas['datas'][$printer['status']." ($pourcentage %)"] = $printer['Total'];
      }

      return $datas;

   }

   /* ==== COMPUTER'S ENTITIES REPORTS ==== */
   function reportHbarComputersByEntity($config = []) {
      global $DB, $CFG_GLPI;

      $_SESSION['mreporting_selector']['reportHbarComputersByEntity'] = ['multiplestates',
                                                                         'entityLevel'];

       $this->where_entities_level = PluginMreportingCommon::getSQLEntityLevel("`glpi_entities`.`level`");

       $datas = [];

       $entity = new Entity();
       $entity->getFromDB($_SESSION['glpiactive_entity']);
       $entities_first_level = [$_SESSION['glpiactive_entity'] => $entity->getName()];

       $query = "SELECT `id`, `name`
                  FROM `glpi_entities`
                  WHERE `entities_id` = '".$_SESSION['glpiactive_entity']."'
                  AND {$this->where_entities_level}
                  ORDER BY `name`";
       $result = $DB->query($query);

      while ($data = $DB->fetchAssoc($result)) {
         $entities_first_level[$data['id']] = $data['name'];
      }
        $entities = [];
      foreach ($entities_first_level as $entities_id=>$entities_name) {
         if ($entities_id == $_SESSION['glpiactive_entity']) {
            $restrict = " = '".$entities_id."'";
         } else {
            $restrict = "IN (".implode(',', getSonsOf('glpi_entities', $entities_id)).")";
         }
            $query = "SELECT count(*) Total
                     FROM `glpi_computers`
                     WHERE `entities_id` " . $restrict . "
                     AND `is_deleted` = 0
                     AND `is_template` = 0";
            $result = $DB->query($query);

         while ($computer = $DB->fetchAssoc($result)) {
            $datas['tmp'][$entities_name." (pourcentage %)"] = $computer['Total'];
            $entities[$entities_name." (pourcentage %)"] = $entities_id;
         }
      }
        $total = array_sum($datas['tmp']);
      foreach ($datas['tmp'] as $key=>$value) {
         if ($value == 0) {
            $percent = 0;
         } else {
             $percent = round((100 * $value) / $total);
         }
           $ent_id = $entities[$key];
           $key = str_replace('pourcentage', $percent, $key);
           $datas['datas'][$key] = $value;
           $type = 'under';
         if ($ent_id == $_SESSION['glpiactive_entity']) {
            $type = 'equals';
         }
           $datas['links'][$key] = $CFG_GLPI["root_doc"].'/front/computer.php?is_deleted=0&criteria[0][field]=80&criteria[0][searchtype]='.$type.'&criteria[0][value]='.$ent_id.'&itemtype=Computer&start=0';
      }
        unset($datas['tmp']);

        return $datas;
   }
}
