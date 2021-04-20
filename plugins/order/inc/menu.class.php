<?php

class PluginOrderMenu extends CommonGLPI {


   public static function getTypeName($nb = 0) {
      return __("Orders", "order");
   }


   static function getMenuContent() {
      global $CFG_GLPI;

      $menu = [
         'title' => self::getTypeName(2),
         'page'  => self::getSearchURL(false),
         'icon'  => PluginOrderOrder::getIcon(),
      ];
      if (PluginOrderConfig::canView()) {
         $menu['links']['config'] = PluginOrderConfig::getFormURL(false);
      }

      if (PluginOrderOrder::canView()) {
         $menu['options']['order'] = [
            'title' => PluginOrderOrder::getTypeName(2),
            'page'  => PluginOrderOrder::getSearchURL(false),
            'icon'  => PluginOrderOrder::getIcon(),
         ];
         if (PluginOrderOrder::canCreate()) {
            $menu['options']['order']['links'] = [
               'search' => PluginOrderOrder::getSearchURL(false),
               'add'    => "/front/setup.templates.php?itemtype=PluginOrderOrder&add=1",
            ];
         }
         $menu['options']['order']['links']['template'] = "/front/setup.templates.php?itemtype=PluginOrderOrder&add=0";
         if (PluginOrderConfig::canView()) {
            $menu['options']['order']['links']['config'] = PluginOrderConfig::getFormURL(false);
         }
      }

      if (PluginOrderBill::canView()) {
         $menu['options']['bill'] = [
            'title' => PluginOrderBill::getTypeName(2),
            'page'  => PluginOrderBill::getSearchURL(false),
            'links' => [
               'search' => PluginOrderBill::getSearchURL(false)
            ]
         ];
         if (PluginOrderBill::canCreate()) {
            $menu['options']['bill']['links']['add'] = PluginOrderBill::getFormURL(false);
         }
         if (PluginOrderConfig::canView()) {
            $menu['options']['bill']['links']['config'] = PluginOrderConfig::getFormURL(false);
         }
      }

      if (PluginOrderReference::canView()) {
         $menu['options']['references'] = [
            'title' => PluginOrderReference::getTypeName(2),
            'page'  => PluginOrderReference::getSearchURL(false),
            'links' => [
               'search' => PluginOrderReference::getSearchURL(false)
            ]
         ];
         if (PluginOrderReference::canCreate()) {
            $menu['options']['references']['links']['add'] = PluginOrderReference::getFormURL(false);
         }
         if (PluginOrderConfig::canView()) {
            $menu['options']['references']['links']['config'] = PluginOrderConfig::getFormURL(false);
         }
      }
      return $menu;
   }


   function install() {

   }


}
