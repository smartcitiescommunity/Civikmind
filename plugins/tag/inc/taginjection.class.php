<?php

class PluginTagTagInjection extends PluginTagTag
      implements PluginDatainjectionInjectionInterface {

   static function getTable($classname = null) {
      $parenttype = get_parent_class();
      return $parenttype::getTable();
   }

   static function getTypeName($nb = 0) {
      return parent::getTypeName(1);
   }

   function isPrimaryType() {
      return true;
   }

   function connectedTo() {
      //Note : Interesting to have GLPI core object (who can have a tag) here
      return [];
   }

   /**
    * @see plugins/datainjection/inc/PluginDatainjectionInjectionInterface::getOptions()
   **/
   function getOptions($primary_type = '') {

      $tab = Search::getOptions(get_parent_class($this));

      //Remove some options because some fields cannot be imported
      $options['ignore_fields'] = [3, 4, 6]; //id, entity, type_menu;
      $options['displaytype']   = ["dropdown" => [12]];

      return PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);
   }

   /**
    * @see plugins/datainjection/inc/PluginDatainjectionInjectionInterface::addOrUpdateObject()
   **/
   function addOrUpdateObject($values = [], $options = []) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      $results = $lib->getInjectionResults();

      // Update field for add a default value
      if ($results['status'] == PluginDatainjectionCommonInjectionLib::SUCCESS) {
          $item = new parent();
          $item->update(['id'        => $results[get_parent_class()],
                         'type_menu' => '0']); //default value
      }

      return $results;
   }
}
