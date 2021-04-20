<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMetabaseDashboard extends CommonDBTM {

   /**
    * {@inheritDoc}
    * @see CommonGLPI::getTypeName()
    */
   static function getTypeName($nb = 0) {

      return __('Metabase dashboard', 'metabase');
   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::getTabNameForItem()
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      switch ($item->getType()) {
         case "Central":

            if (PluginMetabaseProfileright::canProfileViewDashboards($_SESSION['glpiactiveprofile']['id'])) {
               return self::createTabEntry(self::getTypeName());
            }

            break;
      }
      return '';
   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::displayTabContentForItem()
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case "Central":

            if (PluginMetabaseProfileright::canProfileViewDashboards($_SESSION['glpiactiveprofile']['id'])) {
               self::showForCentral($item, $withtemplate);
            }

            break;
      }

      return true;
   }

   /**
    * Display central tab.
    *
    * @param Central $item
    * @param number $withtemplate
    *
    * @return void
    */
   static function showForCentral(Central $item, $withtemplate = 0, $is_helpdesk = false) {

      $apiclient = new PluginMetabaseAPIClient();

      $currentUuid = isset($_GET['uuid']) ? $_GET['uuid'] : null;

      $dashboards = $apiclient->getDashboards();
      if (is_array($dashboards)) {
         $dashboards = array_filter(
            $dashboards,
            function ($dashboard) {
               $isEmbeddingEnabled = $dashboard['enable_embedding'];
               $canView = PluginMetabaseProfileright::canProfileViewDashboard(
                     $_SESSION['glpiactiveprofile']['id'],
                     $dashboard['id']
               );

               return $isEmbeddingEnabled && $canView;
            }
         );
      }

      if (empty($dashboards)) {
         return;
      }

      if (null === $currentUuid) {
         $firstDashboard = current($dashboards);
         $currentUuid = $firstDashboard['id'];
      }

      Dropdown::showFromArray(
         'current_dashboard',
         array_combine(array_column($dashboards, 'id'), array_column($dashboards, 'name')),
         [
            'on_change' => ($is_helpdesk) ? 'location.href = location.origin+location.pathname+"?uuid="+$(this).val()' : 'reloadTab("uuid=" + $(this).val());',
            'value'     => $currentUuid
         ]
      );

      $config = PluginMetabaseConfig::getConfig();

      $signer = new Lcobucci\JWT\Signer\Hmac\Sha256();
      $token = (new Lcobucci\JWT\Builder())
          ->set('resource', [
              'dashboard' => (int) $currentUuid
          ])
          ->set('params', new stdClass())
          ->sign($signer, $config['embedded_token'])
          ->getToken();

      $url = rtrim($config['metabase_url'], '/');
      echo "<iframe src='$url/embed/dashboard/{$token}#bordered=false'
                    id='metabase_iframe'
                    allowtransparency></iframe>";
   }
}
