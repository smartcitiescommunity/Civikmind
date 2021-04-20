<?php

class PluginTagTagItem extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1    = 'PluginTagTag';
   static public $items_id_1    = 'plugin_tag_tags_id';
   static public $take_entity_1 = true;

   static public $itemtype_2    = 'itemtype';
   static public $items_id_2    = 'items_id';
   static public $take_entity_2 = false;


   public static function getTypeName($nb = 1) {
      return PluginTagTag::getTypeName($nb);
   }

   public static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);

      if (!$DB->tableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
               `id` INT(11) NOT NULL AUTO_INCREMENT,
               `plugin_tag_tags_id` INT(11) NOT NULL DEFAULT '0',
               `items_id` INT(11) NOT NULL DEFAULT '1',
               `itemtype` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
               PRIMARY KEY (`id`),
               UNIQUE INDEX `unicity` (`itemtype`, `items_id`, `plugin_tag_tags_id`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB";
         $DB->query($query) or die($DB->error());
      }

      // fix indexes
      $migration->dropKey($table, 'name');
      $migration->addKey($table,
                         ['items_id', 'itemtype', 'plugin_tag_tags_id'],
                         'unicity',
                         'UNIQUE INDEX');
      $migration->migrationOneTable($table);

      return true;
   }

   public static function uninstall() {
      global $DB;

      return $DB->query("DROP TABLE IF EXISTS `" . getTableForItemType(__CLASS__) . "`")
         or die($DB->error());
   }

   /**
    * Display the list of available itemtype
    *
    * @param PluginTagTag $tag
    * @return boolean
    */
   static function showForTag(PluginTagTag $tag) {
      global $DB, $CFG_GLPI;

      $instID = $tag->fields['id'];
      if (!$tag->can($instID, READ)) {
         return false;
      }

      $canedit = $tag->can($instID, UPDATE);
      $table  = getTableForItemType(__CLASS__);

      $result = $DB->query("SELECT DISTINCT `itemtype`
                            FROM `$table`
                            WHERE `plugin_tag_tags_id` = '$instID'");

      $result2 = $DB->query("SELECT `itemtype`, items_id
                             FROM `$table`
                             WHERE `plugin_tag_tags_id` = '$instID'");

      $number = $DB->numrows($result);
      $rand   = mt_rand();

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='tagitem_form$rand' id='tagitem_form$rand' method='post'
               action='".Toolbox::getItemTypeFormURL('PluginTagTag')."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add an item')."</th></tr>";

         $itemtypes_to_show = json_decode($tag->fields['type_menu']);
         if (!is_array($itemtypes_to_show)) {
            $itemtypes_to_show = [];
            foreach ($CFG_GLPI['plugin_tag_itemtypes'] as $menu_entry) {
               foreach ($menu_entry as $default_itemtype) {
                  array_push($itemtypes_to_show, $default_itemtype);
               }
            }
         }
         echo "<tr class='tab_bg_1'><td class='right'>";
         Dropdown::showSelectItemFromItemtypes(['itemtypes' => $itemtypes_to_show,
                                                'entity_restrict'
                                                       => ($tag->fields['is_recursive']
                                                           ?getSonsOf('glpi_entities',
                                                                      $tag->fields['entities_id'])
                                                           :$tag->fields['entities_id']),
                                                'checkright' => true]);
         echo "</td><td class='center'>";
         echo "<input type='hidden' name='plugin_tag_tags_id' value='$instID'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);

         $massiveactionparams['specific_actions']
               = [ 'MassiveAction:purge'
                =>  _x('button', 'Delete permanently the relation with selected elements')];

         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit && $number) {
         echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand) . "</th>";
      }

      echo  "<th>" . __('Type') . "</th>";
      echo  "<th>" . __('Name') . "</th>";
      echo  "<th>" . __('Entity') . "</th>";
      echo  "<th>" . __('Serial number') . "</th>";
      echo  "<th>" . __('Inventory number') . "</th>";
      echo "</tr>";

      for ($i=0; $i < $number; $i++) {
         $itemtype = $DB->result($result, $i, "itemtype");
         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }
         $item_id = $DB->result($result2, $i, "items_id");

         if ($item->canView()) {
            $column = (strtolower(substr($itemtype, 0, 6)) == "device") ? "designation" : "name";

            // For rules itemtypes (example : ruledictionnaryphonemodel)
            if (strtolower(substr($itemtype, 0, 4)) == 'rule' || $itemtype == "PluginResourcesRulechecklist") {
               $itemtable = getTableForItemType('Rule');
            } else {
               $itemtable = getTableForItemType($itemtype);
            }

            $criteria = [
               'SELECT'     => [
                  $itemtable . '.*',
                  'glpi_plugin_tag_tagitems.id AS IDD',
               ],
               'FROM'       => 'glpi_plugin_tag_tagitems',
               'INNER JOIN' => [
                  $itemtable => [
                     'ON' => [
                        $itemtable                 => 'id',
                        'glpi_plugin_tag_tagitems' => 'items_id',
                        [
                           'AND' => [
                              'glpi_plugin_tag_tagitems.itemtype' => $itemtype
                           ]
                        ]
                     ]
                  ],
               ],
               'WHERE'      => [
                  'glpi_plugin_tag_tagitems.plugin_tag_tags_id' => $instID,
               ] + getEntitiesRestrictCriteria($itemtable, '', '', $item->maybeRecursive()),
               'ORDERBY'    => [
                  $itemtable . '.' . $column,
               ],
            ];

            if ($item->maybeTemplate()) {
               $criteria['WHERE'][$itemtable . '.is_template'] = 0;
            }

            switch ($itemtype) {
               case 'KnowbaseItem':
                  $criteria['SELECT'][] = new QueryExpression('-1 AS ' . $DB::quoteName('entity'));
                  $visibility_crit = KnowbaseItem::getVisibilityCriteria();
                  if (array_key_exists('LEFT JOIN', $visibility_crit)
                      && !empty($visibility_crit['LEFT JOIN'])) {
                     $criteria['LEFT JOIN'] = $visibility_crit['LEFT JOIN'];
                  }
                  break;
               case 'Profile':
               case 'RSSFeed':
               case 'Reminder':
               case 'Entity':
                  //Possible to add (in code) condition to visibility :
                  $criteria['SELECT'][] = new QueryExpression('-1 AS ' . $DB::quoteName('entity'));
                  break;
               default:
                  $obj = new $itemtype();
                  $obj->getFromDB($item_id);

                  if (isset($obj->fields['entities_id'])) {
                     $criteria['SELECT'][] = 'glpi_entities.id AS entity';
                     $criteria['LEFT JOIN'] = [
                        'glpi_entities' => [
                           'ON' => [
                              'glpi_entities' => 'id',
                              $itemtable      => 'entities_id',
                           ]
                        ],
                     ];
                     array_unshift($criteria['ORDERBY'], 'glpi_entities.completename');
                  } else {
                     $criteria['SELECT'][] = new QueryExpression('-1 AS ' . $DB::quoteName('entity'));
                  }
                  break;
            }

            $linked_iterator = $DB->request($criteria);

            while ($data = $linked_iterator->next()) {

               if ($itemtype == 'Softwarelicense') {
                  $soft = new Software();
                  $soft->getFromDB($data['softwares_id']);
                  $data["name"] .= ' - ' . $soft->getName(); //This add name of software
               } else if ($itemtype == "PluginResourcesResource") {
                  $data["name"] = formatUserName($data["id"], "", $data["name"],
                                     $data["firstname"]);
               }

               $linkname = $data[$column];

               if ($_SESSION["glpiis_ids_visible"] || empty($data[$column])) {
                  $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
               }

               $name = "<a href=\"".Toolbox::getItemTypeFormURL($itemtype)."?id=".$data["id"]."\">".$linkname."</a>";

               if ($itemtype == 'PluginProjetProjet'
                  || $itemtype == 'PluginResourcesResource') {
                  $pieces = preg_split('/(?=[A-Z])/', $itemtype);
                  $plugin_name = $pieces[2];

                  $datas = ["entities_id" => $data["entity"],
                            "ITEM_0"      => $data["name"],
                            "ITEM_0_2"    => $data["id"],
                            "id"          => $data["id"],
                            "META_0"      => $data["name"]]; //for PluginResourcesResource

                  if (isset($data["is_recursive"])) {
                     $datas["is_recursive"] = $data["is_recursive"];
                  }

                     Plugin::load(strtolower($plugin_name), true);
                     $function_giveitem = 'plugin_'.strtolower($plugin_name).'_giveItem';
                  if (function_exists($function_giveitem)) { // For security
                     $name = call_user_func($function_giveitem, $itemtype, 1, $datas, 0);
                  }

               }

               echo "<tr class='tab_bg_1'>";

               if ($canedit) {
                  echo "<td width='10'>";
                  if ($item->canUpdate()) {
                     Html::showMassiveActionCheckBox(__CLASS__, $data["IDD"]);
                  }
                  echo "</td>";
               }
               echo "<td class='center'>";

               // Show plugin name (is to delete remove any ambiguity) :
               $pieces = preg_split('/(?=[A-Z])/', $itemtype);
               if ($pieces[1] == 'Plugin') {
                  $plugin_name = $pieces[2];
                  if (function_exists("plugin_version_".$plugin_name)) { // For security
                     $tab = call_user_func("plugin_version_".$plugin_name);
                     echo $tab["name"]." : ";
                  }
               }

               echo $item->getTypeName(1)."</td>";
               echo "<td ".(isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'":"").">".$name."</td>";
               echo "<td class='center'>";

               $entity = $data['entity'];

               //for Plugins :
               if ($data["entity"] == -1) {
                  $item->getFromDB($data['id']);
                  if (isset($item->fields["entities_id"])) {
                     $entity = $item->fields["entities_id"];
                  }
               }
               echo Dropdown::getDropdownName("glpi_entities", $entity);

               echo "</td>";
               echo "<td class='center'>".
                   (isset($data["serial"]) ? "".$data["serial"]."" :"-")."</td>";
               echo "<td class='center'>".
                   (isset($data["otherserial"]) ? "".$data["otherserial"]."" :"-")."</td>";
               echo "</tr>";
            }
         }
      }
      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";

   }

   /**
    * Update all tags associated to an item
    *
    * @param  CommonDBTM $item [description]
    *
    * @return boolean
    */
   static function updateItem(CommonDBTM $item, $options = []) {

      if ($item->getID()
          && !isset($item->input["_plugin_tag_tag_process_form"])) {
         return true;
      }

      //merge default options with parameter one
      $default_options = [
         'delete_old' => true
      ];
      $options = array_merge($default_options, $options);

      // instanciate needed objects
      $tag      = new PluginTagTag();
      $tag_item = new self();

      // create new values
      $tag_values = !empty($item->input["_plugin_tag_tag_values"])
         ? $item->input["_plugin_tag_tag_values"]
         : [];
      foreach ($tag_values as &$tag_value) {
         if (strpos($tag_value, "newtag_") !== false) {
            $tag_value = str_replace("newtag_", "", $tag_value);
            $tag_value = $tag->add([
               'name' => $tag_value,
            ]);
         }
      }

      // process actions
      if ($options['delete_old']) {
         // purge old tags
         self::purgeItem($item);

      } else {
         // remove possible duplicates (to avoid sql errors on unique index)
         $found      = $tag_item->find(['items_id' => $item->getID(),
                                        'itemtype' => $item->getType()]);
         $tag_values = array_diff($tag_values, array_keys($found));
      }

      // link tags with the current item
      foreach ($tag_values as $tag_id) {
         $tag_item->add([
            'plugin_tag_tags_id' => $tag_id,
            'items_id' => $item->getID(),
            'itemtype' => $item->getType()
         ]);
      }

      return true;
   }

   /**
    * Delete all tags associated to an item
    *
    * @param  CommonDBTM $item
    * @return boolean
    */
   static function purgeItem(CommonDBTM $item) {
      $tagitem = new self();
      return $tagitem->deleteByCriteria([
         "items_id" => $item->getID(),
         "itemtype" => $item->getType(),
      ]);
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {

      $itemtypes = array_keys($ma->items);
      $itemtype = array_shift($itemtypes);

      switch ($ma->getAction()) {
         case 'addTag':
         case 'removeTag':
            PluginTagTag::showTagDropdown(['itemtype' => $itemtype]);
            echo Html::submit(_sx('button', 'Save'));
            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   static function processMassiveActionsForOneItemtype(MassiveAction $ma,
                                                       CommonDBTM $item,
                                                       array $ids) {
      $input = $ma->getInput();
      switch ($ma->getAction()) {
         case "addTag":
            foreach ($ma->items as $itemtype => $items) {
               $object = new $itemtype;
               foreach ($items as $items_id) {
                  $object->fields['id'] = $items_id;
                  $object->input        = $input;
                  if (self::updateItem($object, ['delete_old' => false])) {
                     $ma->itemDone($item->getType(), $items_id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $items_id, MassiveAction::ACTION_KO);
                     $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                  }
               }
            }
            break;
         case "removeTag":
            $tagitem = new self;
            foreach ($ma->items as $itemtype => $items) {
               $object = new $itemtype;
               foreach ($items as $items_id) {
                  if ($tagitem->deleteByCriteria([
                     'items_id'           => $items_id,
                     'itemtype'           => $itemtype,
                     'plugin_tag_tags_id' => $input['_plugin_tag_tag_values'],
                  ])) {
                     $ma->itemDone($item->getType(), $items_id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $items_id, MassiveAction::ACTION_KO);
                     $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                  }
               }
            }
            break;
      }
   }

}
