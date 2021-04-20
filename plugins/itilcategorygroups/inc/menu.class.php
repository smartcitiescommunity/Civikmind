<?php

class PluginItilcategorygroupsMenu extends CommonGLPI {

   static function getTypeName($nb = 0) {
      return __('Link ItilCategory - Groups', 'itilcategorygroups');
   }

   static function getMenuName() {
      return __('ItilCategory Groups', 'itilcategorygroups');
   }

   static function getMenuContent() {
      global $CFG_GLPI;
      $menu          = [];
      $menu['title'] = self::getMenuName();
      $menu['page']  = '/' . Plugin::getWebDir('itilcategorygroups', false) . '/front/category.php';
      $menu['icon']  = PluginItilcategorygroupsCategory::getIcon();

      if (Session::haveRight('config', READ)) {

         $menu['options']['model']['title'] = PluginItilcategorygroupsMenu::getTypeName();
         $menu['options']['model']['page'] = Toolbox::getItemTypeSearchUrl('PluginItilcategorygroupsCategory', false);
         $menu['options']['model']['links']['search'] = Toolbox::getItemTypeSearchUrl('PluginItilcategorygroupsCategory', false);

         if (Session::haveRight('config', UPDATE)) {
            $menu['options']['model']['links']['add'] = Toolbox::getItemTypeFormUrl('PluginItilcategorygroupsCategory', false);
         }

      }

      return $menu;
   }

}