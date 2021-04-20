<?php
include ("../../../inc/includes.php");

Html::header(
   __('Metabase collections', 'metabase'),
   $_SERVER['PHP_SELF'],
   'config',
   'config',
   'collections'
);

Session::checkRight('config', READ);

echo '<div class="metabase_config">';
echo '<h1>' . __('Reports and dashboards specifications', 'metabase') . '</h1>';
$metabaseConfig = new PluginMetabaseConfig();
$apiclient = new PluginMetabaseAPIClient;
if ($metabaseConfig::isValid() && $apiclient->getGlpiDatabase() && $apiclient->checkSession()) {
   $collections = $apiclient->getCollections();
   if ($collections !== false
       && count($collections)) {

      if (!isset($_SESSION['metabase']['tables'])
          || !isset($_SESSION['metabase']['fields'])
          || !count($_SESSION['metabase']['tables'])
          || !count($_SESSION['metabase']['fields'])) {
         $current_config = PluginMetabaseConfig::getConfig();
         PluginMetabaseConfig::loadTablesAndFields($current_config['glpi_db_id']);
      }

      echo "<h3>".__("Extract questions from metabase:", 'metabase')."</h3>";
      echo "<ul class='metabase_collection_list'>";
      foreach ($collections as $collection) {
         $collection_cards = $apiclient->getCards($collection['id']);
         if ($collection_cards !== false
             && count($collection_cards)) {
            echo "<li><label>".$collection['name']."</label>";
            echo "<ul class='extract_list'>";
            foreach ($collection_cards as $card) {
               if ($card['query_type'] === "native") {
                  echo "<li><a href='#'
                               class='extract'
                               data-id='".$card['id']."' data-type='question'>".
                           $card['name'].
                       "</a></li>";
               }
            }
            echo "</ul>";
            echo "</li>";
         }
      }
      echo "</ul>";
   }

   $dashboards = $apiclient->getDashboards();
   if ($dashboards !== false
       && count($dashboards)) {
      PluginMetabaseConfig::loadReports();
      echo "<h3>".__("Extract dashboards from metabase:", 'metabase')."</h3>";
      echo "<ul class='extract_list extract_dashboards'>";
      foreach ($dashboards as $dashboard) {
         echo "<li><a href='#'
                      class='extract'
                      data-id='".$dashboard['id']."' data-type='dashboard'>".
                  $dashboard['name'].
              "</a></li>";
      }
      echo "</ul>";
   }
} else {
   echo '<p>' . __('Unable to access Metabase data. Please check plugin configuration.', 'metabase') . '</p>';
}

echo '</div>';

Html::footer();
