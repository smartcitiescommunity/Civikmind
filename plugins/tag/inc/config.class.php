<?php
class PluginTagConfig extends CommonDBTM {

   static protected $notable = true;

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate && $item->getType() === 'Config') {
         return __('Tag Management', 'tag');
      }
      return '';
   }

   public function showForm() {
      global $CFG_GLPI;
      if (!Session::haveRight('config', UPDATE)) {
         return false;
      }
      $config = Config::getConfigurationValues('plugin:Tag');

      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL('Config')."\" method='post'>";
      echo "<input type='hidden' name='config_class' value='".__CLASS__."'>";
      echo "<input type='hidden' name='config_context' value='plugin:Tag'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . __('Tag Management', 'tag') . '</th></thead>';
      echo '<td>' . __('Tags location', 'tag') . '</td><td>';
      Dropdown::showFromArray('tags_location', [
         __('Top'),
         __('Bottom')
      ],
      [
         'value'  => $config['tags_location'] ?? 0
      ]);
      echo '</td></tr>';
      echo '</table>';

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save'). '">';
      echo '</td></tr>';
      echo '</table>';
      echo '</div>';
      Html::closeForm();
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Config') {
         $config = new self();
         $config->showForm();
      }
   }

   public static function uninstall() {
      $config = Config::getConfigurationValues('plugin:Tag');
      Config::deleteConfigurationValues('plugin:Tag', array_keys($config));
      return true;
   }

}