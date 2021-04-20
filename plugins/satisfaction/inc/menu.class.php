<?php

/**
 * Class PluginSatisfactionMenu
 */
class PluginSatisfactionMenu extends CommonGLPI
{
   static $rightname = 'plugin_satisfaction';

   /**
    * @return translated
    */
   static function getMenuName() {
      return __('Satisfaction survey', 'satisfaction');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu = [];

      if (Session::haveRight('plugin_satisfaction', READ)) {
         $web_dir = '/' . Plugin::getWebDir('satisfaction', false);
         $menu['title']           = self::getMenuName();
         $menu['page']            = $web_dir."/front/survey.php";
         $menu['page']            = $web_dir."/front/survey.php";
         $menu['links']['search'] = PluginSatisfactionSurvey::getSearchURL(false);
         if (PluginSatisfactionSurvey::canCreate()) {
            $menu['links']['add'] = PluginSatisfactionSurvey::getFormURL(false);
         }
      }

      $menu['icon'] = self::getIcon();

      return $menu;
   }

   static function getIcon() {
      return "fas fa-thumbs-up";
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['admin']['types']['PluginSatisfactionMenu'])) {
         unset($_SESSION['glpimenu']['admin']['types']['PluginSatisfactionMenu']);
      }
      if (isset($_SESSION['glpimenu']['admin']['content']['pluginsatisfactionmenu'])) {
         unset($_SESSION['glpimenu']['admin']['content']['pluginsatisfactionmenu']);
      }
   }
}
