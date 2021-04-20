<?php

/*
 -------------------------------------------------------------------------
 Web Resources Plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/glpi-webresources-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Web Resources Plugin for GLPI.
 Web Resources Plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Web Resources Plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Web Resources Plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Web Resources Dashboard
 */
class PluginWebresourcesDashboard extends CommonGLPI {

   public static $rightname = 'plugin_webresources_resource';

   public static function getTypeName($nb = 0)
   {
      return PluginWebresourcesResource::getTypeName(Session::getPluralNumber());
   }

   public static function getIcon()
   {
      return 'fab fa-chrome';
   }

   public static function getMenuContent()
   {
      $menu = parent::getMenuContent();
      $menu['links']['search'] = PluginWebresourcesResource::getSearchURL(false);
      if (PluginWebresourcesResource::canCreate()) {
         $menu['links']['add'] = PluginWebresourcesResource::getFormURL(false);
      }
      return $menu;
   }

   /**
    * @param bool $skip_rights
    * @return array
    * @since 1.3.0
    */
   public static function getDashboardContexts(bool $skip_rights = false)
   {
      $contexts = [
         'personal'  => __('My resources', 'webresources')
      ];

      if ($skip_rights || Supplier::canView()) {
         $contexts['suppliers'] = Supplier::getTypeName(Session::getPluralNumber());
      }

      if ($skip_rights || Entity::canView()) {
         $contexts['entities'] = Entity::getTypeName(Session::getPluralNumber());
      }

      if (Plugin::isPluginActive('webapplications')) {
         // Need the plugin because the migration to the core did not transfer the url or management url :(
         $contexts['appliances'] = Appliance::getTypeName(Session::getPluralNumber());
      }
      return $contexts;
   }

   private static function getPersonalResources()
   {
      global $DB;

      $iterator = $DB->request([
            'FROM'   => PluginWebresourcesResource::getTable()
         ] + PluginWebresourcesResource::getVisibilityCriteria(true));
      $resources = [];
      while($data = $iterator->next()) {
         $resources[$data['plugin_webresources_categories_id']][] = $data;
      }

      return $resources;
   }

   private static function getSupplierResources(bool $skip_rights = false)
   {
      global $DB;

      $types_iterator = $DB->request([
         'SELECT' => ['id', 'name'],
         'FROM'   => SupplierType::getTable()
      ]);
      $types = [
         0  => __('Uncategorized', 'webresources')
      ];
      while ($data = $types_iterator->next()) {
         $types[$data['id']] = $data['name'];
      }
      $iterator = $DB->request([
         'SELECT' => [Supplier::getTable().'.id AS id', 'name', 'website', 'suppliertypes_id'],
         'FROM'   => Supplier::getTable()
      ] + getEntitiesRestrictCriteria());
      $resources = [];
      if (!$skip_rights && !Supplier::canView()) {
         return $resources;
      }

      while($data = $iterator->next()) {
         if (!empty($data['website'])) {
            $ico_iterator = $DB->request([
               'SELECT' => ['icon', 'color'],
               'FROM'   => 'glpi_plugin_webresources_autoicons',
               'WHERE'  => [
                  'itemtype'  => Supplier::class,
                  'items_id'  => $data['id']
               ]
            ]);
            if (count($ico_iterator)) {
               $icodata = $ico_iterator->next();
               $ico = empty($icodata['icon']) ? Supplier::getIcon() : $icodata['icon']; // '@auto'
               $color = $icodata['color'];
            } else {
               $ico = Supplier::getIcon(); // '@auto'
               $color = '#000000';
            }
            $suppliertype_name = $types[$data['suppliertypes_id']];
            $resources[$suppliertype_name][] = [
               'name' => $data['name'],
               'link' => $data['website'],
               'color' => $color,
               'icon' => $ico
            ];
         }
      }

      return $resources;
   }

   private static function getApplianceResources(bool $skip_rights = false)
   {
      global $DB;

      $types_iterator = $DB->request([
         'SELECT' => ['id', 'name'],
         'FROM'   => ApplianceType::getTable()
      ]);
      $types = [
         0  => __('Uncategorized', 'webresources')
      ];
      while ($data = $types_iterator->next()) {
         $types[$data['id']] = $data['name'];
      }
      $resources = [];
      if (!$skip_rights && !Appliance::canView()) {
         return $resources;
      }

      if (!Plugin::isPluginActive('webapplications')) {
         return $resources;
      }
      $iterator = $DB->request([
         'SELECT' => ['name', 'address', 'backoffice', 'appliancetypes_id'],
         'FROM'   => PluginWebapplicationsAppliance::getTable(),
         'JOIN'   => [
            Appliance::getTable() => [
               'ON'  => [
                  Appliance::getTable()                        => 'id',
                  PluginWebapplicationsAppliance::getTable()   => 'appliances_id'
               ]
            ]
         ]
      ] + getEntitiesRestrictCriteria());
      $resources = [];
      while($data = $iterator->next()) {
         $appliancetype_name = $types[$data['appliancetypes_id']];
         if (!empty($data['address'])) {
            $resources[$appliancetype_name][] = [
               'name' => $data['name'],
               'link' => $data['address'],
               'color' => '#000000',
               'icon' => Appliance::getIcon()
            ];
         }
         if (!empty($data['backoffice'])) {
            $resources[$appliancetype_name][] = [
               'name' => $data['name'] . ' (Management)',
               'link' => $data['backoffice'],
               'color' => '#000000',
               'icon' => Appliance::getIcon()
            ];
         }
      }

      return $resources;
   }

   private static function getEntityResources(bool $skip_rights = false)
   {
      global $DB;

      $category = Entity::getTypeName(Session::getPluralNumber());
      $iterator = $DB->request([
            'SELECT' => [Entity::getTable().'.id AS id', 'completename', 'website'],
            'FROM'   => Entity::getTable()
         ] + getEntitiesRestrictCriteria());
      $resources = [
         $category => []
      ];
      if (!$skip_rights && !Entity::canView()) {
         return $resources;
      }

      while($data = $iterator->next()) {
         if (!empty($data['website'])) {
            $ico_iterator = $DB->request([
               'SELECT' => ['icon', 'color'],
               'FROM'   => 'glpi_plugin_webresources_autoicons',
               'WHERE'  => [
                  'itemtype'  => Entity::class,
                  'items_id'  => $data['id']
               ]
            ]);
            if (count($ico_iterator)) {
               $icodata = $ico_iterator->next();
               $ico = empty($icodata['icon']) ? Entity::getIcon() : $icodata['icon'];
               $color = $icodata['color'];
            } else {
               $ico = Entity::getIcon();
               $color = '#000000';
            }
            $resources[$category][] = [
               'name'   => $data['completename'],
               'link'   => $data['website'],
               'color'  => $color,
               'icon'   => $ico
            ];
         }
      }

      return $resources;
   }

   private static function populateAutoIcons(&$resources, bool $regen_icons = false): void
   {
      global $GLPI_CACHE;

      // Fetch and Cache auto-generated icons
      $cache = $regen_icons ? null : $GLPI_CACHE->get('webresources.autoico');
      $cache = $cache === null ? [] : json_decode($cache, true);

      $to_fetch = [];
      foreach ($resources as $cat_id => $cat_resources) {
         foreach ($cat_resources as $res_k => $resource) {
            if ($resource['icon'] !== '@auto') {
               continue;
            }

            $key = md5($resource['link']);
            if ($regen_icons || !isset($cache[$key])) {
               $to_fetch[] = $resource['link'];
            }
         }
      }

      // Experimental Feature for baking generated icons to a json file. Not present in release unless manually created.
      if (file_exists('../resources/favicon_map.json')) {
         $baked_favicon_map = json_decode(file_get_contents('../resources/favicon_map.json'), true);
         foreach ($to_fetch as $i => $url) {
            $key = md5($url);
            $host = parse_url($url, PHP_URL_HOST);
            foreach ($baked_favicon_map as $h => $ico) {
               $pattern = "/$h/iu";
               if (preg_match($pattern, $host)) {
                  $cache[$key] = $ico;
                  unset($to_fetch[$i]);
                  continue;
               }
            }
         }
      }
      $fetched = PluginWebresourcesScraper::getMultiple($to_fetch);
      foreach ($fetched as $url => $ico) {
         $key = md5($url);
         $fetched_ico = reset($ico);
         if (!is_array($fetched_ico)) {
            continue;
         }
         $cache[$key] = $fetched_ico['href'];
      }
      foreach ($resources as $cat_id => $cat_resources) {
         foreach ($cat_resources as $res_k => $resource) {
            if ($resource['icon'] !== '@auto') {
               continue;
            }
            $key = md5($resource['link']);
            $resources[$cat_id][$res_k]['icon'] = $cache[$key] ?? null;
         }
      }

      // Save Cache
      $GLPI_CACHE->set('webresources.autoico', json_encode($cache));
   }

   /**
    * @param string $context
    * @param bool $regen_icons If true, automatic icons (Dynamic resources based on other items like Suppliers) are regenerated
    * @param bool $skip_rights
    * @return string
    * @since 1.3.0
    */
   public static function getDashboardContent(string $context = 'personal', bool $regen_icons = false, bool $skip_rights = false): string
   {
      global $DB;

      switch ($context)
      {
         case 'suppliers':
            $default_icon = Supplier::getIcon();
            $resources = self::getSupplierResources($skip_rights);
            $dashboard_header = Supplier::getTypeName(Session::getPluralNumber());
            break;
         case 'appliances':
            $default_icon = Appliance::getIcon();
            $resources = self::getApplianceResources($skip_rights);
            $dashboard_header = Appliance::getTypeName(Session::getPluralNumber());
            break;
         case 'entities':
            $default_icon = Entity::getIcon();
            $resources = self::getEntityResources($skip_rights);
            $dashboard_header = Entity::getTypeName(Session::getPluralNumber());
            break;
         case 'personal':
         default:
            $default_icon = 'fab fa-chrome';
            $resources = self::getPersonalResources();
            $dashboard_header = PluginWebresourcesResource::getTypeName(Session::getPluralNumber());
      }

      $categories = [];
      if ($context === 'personal') {
         $cat_iterator = $DB->request([
            'SELECT' => ['id', 'name'],
            'FROM' => PluginWebresourcesCategory::getTable(),
         ]);
         while ($cat = $cat_iterator->next()) {
            $categories[$cat['id']] = $cat['name'];
         }
      }

      self::populateAutoIcons($resources, $regen_icons);

      ob_start();
      echo '<div><div class="webresources-header">'.$dashboard_header.'</div>';
      echo '<div class="webresources-categories">';
      foreach ($resources as $cat_id => $cat_resources) {
         echo '<div id="webresources-category-'.$cat_id.'" class="webresources-category">';
         if (is_numeric($cat_id)) {
            $cat_name = $cat_id === 0 ? __('Uncategorized', 'webresources') : $categories[$cat_id];
         } else {
            $cat_name = $cat_id;
         }
         echo '<div class="webresources-category-header">'.$cat_name.'</div>';
         echo '<div class="webresources-items">';
         foreach ($cat_resources as $resource) {
            echo self::getResourceAsIcon($resource, $default_icon);
         }
         echo '</div></div>';
      }
      echo '</div></div>';
      return ob_get_clean();
   }

   private static function getResourceAsIcon(array $resource, string $default_icon = 'fab fa-chrome')
   {
      $html = '';
      $html .= '<div class="webresources-item">';
      $html .= '<a href="'.$resource['link'].'" target="_blank">';
      $html .= '<div class="webresources-item-icon">';
      $icon_type = PluginWebresourcesToolbox::isValidWebUrl($resource['icon']) ? 'image' : 'icon';
      if ($icon_type === 'image') {
         $html .= '<img src="' . $resource['icon'] . '" title="' . $resource['name'] . '" alt="' . $resource['name'] . '" style="' . ($icon_type === 'image' ? 'display: block' : 'display: none') . '" onerror="onWRImageLoadError(this);" data-fallback="'.$default_icon.'"/>';
      }

      if ($icon_type === 'image' || ($icon_type === 'icon' && empty($resource['icon']))) {
         $resource['icon'] = $default_icon;
      }
      $html .= '<i style="color: '.$resource['color'].';'.($icon_type === 'icon' ? 'display: block' : 'display: none').'" class="' . $resource['icon'] . '" title="' . $resource['name'] . '" alt="' . $resource['name'] . '"></i>';

      $html .= '</div>';
      $html .= '<div class="webresources-item-title">'.$resource['name'].'</div>';
      $html .= '</a>';
      $html .= '</div>';
      return $html;
   }

   /**
    * @param string $context
    * @since 1.0.0
    * @since 1.3.0 Accept context param. Moved content to getDashboardContent to allow easy loading over AJAX calls
    */
   public static function showDashboard(string $context = 'personal')
   {
      $available_contexts = self::getDashboardContexts();
      if (!array_key_exists($context, $available_contexts)) {
         $context = 'personal';
      }
      echo '<div class="webresources-toolbar">';
      Dropdown::showFromArray('context', $available_contexts, [
         'value'  => $context
      ]);
      echo Html::input('search', [
         'placeholder'  => __('Search')
      ]);
      echo '</div>';
      echo '<div id="webresources-content">';
      echo self::getDashboardContent($context);
      echo '</div>';

      $plugin_root = Plugin::getWebDir('webresources');
      $js = <<<JS
function onWRImageLoadError(img) {
   const img_obj = $(img);
   if (img_obj.is(":visible")) {
      img_obj.hide();
      const i = img_obj.parent().find('i');
      i.show();
      const fallback = img_obj.data('fallback') ?? 'fab fa-chrome';
      i.attr('class', fallback);
   }
}
$(document).ready(function() {
   $('.webresources-toolbar select[name="context"]').on('change', function(v) {
      const new_context = v.target.value;
      $.ajax({
         url: ("{$plugin_root}/ajax/refreshDashboard.php"),
         data: {context: new_context}
      }).success(function(data) {
         $("#webresources-content").empty();
         $("#webresources-content").append(data);
         
         const updateURLParameter = function(url, param, paramVal){
            let newAdditionalURL = "";
            let tempArray = url.split("?");
            let baseURL = tempArray[0];
            let additionalURL = tempArray[1];
            let temp = "";
            if (additionalURL) {
               tempArray = additionalURL.split("&");
               for (let i=0; i<tempArray.length; i++){
                  if(tempArray[i].split('=')[0] != param){
                     newAdditionalURL += temp + tempArray[i];
                     temp = "&";
                  }
               }
            }
            const rows_txt = temp + "" + param + "=" + paramVal;
            return baseURL + "?" + newAdditionalURL + rows_txt;
         }
         window.history.replaceState('', '', updateURLParameter(window.location.href, "context", new_context));
         applySearchFilters($('.webresources-toolbar input[name="search"]').get(0));
      });
   });
   const applySearchFilters = function(search_el) {
      const items = $('.webresources-item');
      const search_filter = search_el.value.toLowerCase();
      items.each(function(i, v) {
         if (v.textContent.toLowerCase().includes(search_filter)) {
            $(v).show();
         } else {
            $(v).hide();
         }
      });
      const categories = $('.webresources-category');
      categories.each(function(i, v) {
         const cat = $(v);
         if (cat.find('.webresources-item').filter(function(i2, f) {
            return $(f).css('display') !== 'none';
         }).length === 0) {
            cat.hide();
         } else {
            cat.show();
         }
      });
   }
   $('.webresources-toolbar input[name="search"]').on('keyup', function() {
      applySearchFilters(this);
   });
});
JS;
      echo Html::scriptBlock($js);

   }
}