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
 * Icon scraper
 * Inspired by https://github.com/mpclarkson/icon-scraper
 */
class PluginWebresourcesScraper
{
   public const ICONTYPE_APPLETOUCH = 'apple-touch-icon';
   public const ICONTYPE_FAVICON = 'favicon';

   private static function retrieveHeader(string $url)
   {
      self::setContext();

      $headers = @get_headers($url, true);
      if ($headers === false) {
         return false;
      }
      $headers = array_change_key_case($headers);

      // Flatten redirects
      if (isset($headers['location']) && is_array($headers['location'])) {
         $headers['location'] = array_filter($headers['location'], static function ($header) {
            return strpos($header, '://') !== false;
         });

         $headers['location'] = end($headers['location']);
      }

      return $headers;
   }

   private static function setContext()
   {
      stream_context_set_default([
            'http' => [
               'method' => 'GET',
               'timeout' => 10,
               'header' => "User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:20.0; Favicon; +https://github.com/cconard96/glpi-webresources-plugin) Gecko/20100101 Firefox/32.0\r\n",
            ]
         ]
      );
   }

   /**
    * @param string $url
    * @param string $path
    * @return bool|string
    */
   private static function baseUrl(string $url, string $path = null)
   {
      $return = '';

      if (!$parsed_url = parse_url($url)) {
         return false;
      }

      // Scheme
      $scheme = isset($parsed_url['scheme']) ? strtolower($parsed_url['scheme']) : null;
      if ($scheme !== 'http' && $scheme !== 'https') {
         return false;
      }
      $return .= "{$scheme}://";

      // Hostname
      if (!isset($parsed_url['host'])) {
         return false;
      }

      $return .= $parsed_url['host'];

      // Port
      if (isset($parsed_url['port'])) {
         $return .= ":{$parsed_url['port']}";
      }

      // Path
      if ($path && isset($parsed_url['path'])) {
         $return .= $parsed_url['path'];
      }
      $return .= '/';

      return $return;
   }

   private static function info(string $url)
   {
      if (empty($url) || $url === false) {
         return false;
      }

      $headers = self::retrieveHeader($url);
      if ($headers === false) {
         return false;
      }

      $status_lines = array_filter($headers, static function ($key) {
         return is_int($key);
      }, ARRAY_FILTER_USE_KEY);

      $exploded = explode(' ', end($status_lines));

      if (!array_key_exists(1, $exploded)) {
         return false;
      }

      [, $status] = $exploded;

      if (isset($headers['location'])) {
         $url = $headers['location'];
      }

      return ['status' => $status, 'url' => $url];
   }

   /**
    * @param string $url
    * @return bool|string
    */
   private static function resolveUrl(string $url)
   {
      $base_url = self::baseUrl($url);
      if ($base_url === false) {
         return false;
      }
      $info = self::info($base_url);
      if (isset($info['url'])) {
         if (strpos($info['url'], '/') === 0) {
            $base_url .= substr($info['url'], 1);
         } else {
            $base_url = $info['url'];
         }
      } else {
         $base_url = false;
      }
      return rtrim($base_url, '/');
   }

   /**
    * @param string $url
    * @return array Array of icons or an empty array
    */
   public static function get(string $url = ''): array
   {
      $resolved_url = self::resolveUrl($url);

      if (empty($resolved_url) || $resolved_url === false) {
         return [];
      }

      $html = @file_get_contents("{$resolved_url}/");
      return self::getIconsFromDOM($html, $url);
   }

   private static function getIconsFromDOM($html, $url): array
   {
      preg_match('!<head.*?>.*</head>!ims', $html, $match);

      $good_html = !(empty($match) || count($match) === 0);

      $icons = [];

      if ($good_html && extension_loaded('dom')) {
         $head = $match[0];
         $dom = new DOMDocument();

         if (@$dom->loadHTML($head)) {
            $links = $dom->getElementsByTagName('link');

            foreach ($links as $link) {

               if ($link->hasAttribute('rel') && $href = $link->getAttribute('href')) {

                  $attribute = $link->getAttribute('rel');

                  // Make sure the href is an absolute URL.
                  if ($href && filter_var($href, FILTER_VALIDATE_URL) === false) {
                     if (strpos($href, '/') === 0) {
                        $href = substr($href, 1);
                     }
                     $href = $url . '/' . $href;
                  }

                  if (self::info($href)['status'] != '200') {
                     continue;
                  }

                  $size = $link->hasAttribute('sizes') ? $link->getAttribute('sizes') : [];
                  $size = !is_array($size) ? explode('x', $size) : $size;

                  $type = false;

                  if (strtolower($attribute) === self::ICONTYPE_APPLETOUCH) {
                     $type = self::ICONTYPE_APPLETOUCH;
                  } else if (stripos($attribute, 'icon') !== FALSE) {
                     $type = self::ICONTYPE_FAVICON;
                  }

                  if (!empty($type) && filter_var($href, FILTER_VALIDATE_URL)) {
                     $icons[] = [
                        'type' => $type,
                        'href' => $href,
                        'size' => $size
                     ];
                  }
               }
            }
         }
      }

      if (!empty($icons)) {
         // Sort by width
         usort($icons, static function ($icon1, $icon2) {
            $width1 = empty($icon1['size']) ? 0 : $icon1['size'][0];
            $width2 = empty($icon2['size']) ? 0 : $icon2['size'][0];
            return $width1 - $width2;
         });
      } else {
         $icons = self::getFavicon($url);
      }

      return $icons;
   }

   public static function getMultiple(array $urls): array
   {
      if (empty($urls)) {
         return [];
      }
      // Reset numeric keys
      $urls = array_values($urls);
      $results = [];
      $url_map = [];
      foreach ($urls as &$url) {
         $results = [
            $url => []
         ];
         $original_url = $url;
         $url = self::resolveUrl($url);
         $url_map[$url] = $original_url;
      }
      unset($url);

      $good_urls = array_filter($urls, static function ($u) {
         return !empty($u);
      });

      $main_curl = curl_multi_init();
      $child_curls = [];

      $url_count = count($good_urls);
      for ($i = 0; $i < $url_count; $i++) {
         $child_curls[$i] = curl_init($good_urls[$i]);
         curl_setopt($child_curls[$i], CURLOPT_RETURNTRANSFER, true);
         curl_setopt($child_curls[$i], CURLOPT_TIMEOUT, 5);
         curl_setopt($child_curls[$i], CURLOPT_CONNECTTIMEOUT, 5);
         curl_multi_add_handle($main_curl, $child_curls[$i]);
      }

      do {
         curl_multi_exec($main_curl, $running);
      } while ($running > 0);

      $html_contents = [];
      for ($i = 0; $i < $url_count; $i++) {
         if (curl_getinfo($child_curls[$i],  CURLINFO_HTTP_CODE) !== 200) {
            continue;
         }
         $html_contents[$i] = curl_multi_getcontent($child_curls[$i]);
      }
      curl_multi_close($main_curl);

      foreach ($html_contents as $i => $html) {
         $url = $good_urls[$i];
         $results[$url_map[$url]] = self::getIconsFromDOM($html, $url);
         if (empty($results[$url_map[$url]])) {
            if (PluginWebresourcesConfig::getConfig()['use_duckduckgo']) {
               $results[$url_map[$url]][] = self::getFaviconFromDuckDuckGo($url);
            }
         }
         if (empty($results[$url_map[$url]])) {
            if (PluginWebresourcesConfig::getConfig()['use_google']) {
               $results[$url_map[$url]][] = self::getFaviconFromGoogle($url);
            }
         }
      }

      return $results;
   }

   private static function getFavicon(string $url): array
   {

      // Try /favicon.ico first.
      $info = self::info("{$url}/favicon.ico");
      if ($info['status'] == '200') {
         $favicon = $info['url'];
      }

      // Make sure the favicon is an absolute URL.
      if (isset($favicon) && filter_var($favicon, FILTER_VALIDATE_URL) === false) {
         $favicon = $url . '/' . $favicon;
      }

      if (isset($favicon)) {
         return [
            'type' => self::ICONTYPE_FAVICON,
            'href' => $favicon,
            'size' => []
         ];
      }

      return [];
   }

   private static function getFaviconFromDuckDuckGo(string $url): array
   {
      $host = parse_url($url,PHP_URL_HOST);
      $info = self::info("https://icons.duckduckgo.com/ip3/{$host}.ico");
      if ($info['status'] == '200') {
         $favicon = $info['url'];
      }

      // Make sure the favicon is an absolute URL.
      if (isset($favicon) && filter_var($favicon, FILTER_VALIDATE_URL) === false) {
         $favicon = $url . '/' . $favicon;
      }

      if (isset($favicon)) {
         return [
            'type' => self::ICONTYPE_FAVICON,
            'href' => $favicon,
            'size' => []
         ];
      }

      return [];
   }

   private static function getFaviconFromGoogle(string $url): array
   {
      $host = parse_url($url,PHP_URL_HOST);
      $info = self::info("https://www.google.com/s2/favicons?domain={$host}");
      if ($info['status'] == '200') {
         $favicon = $info['url'];
      }

      // Make sure the favicon is an absolute URL.
      if (isset($favicon) && filter_var($favicon, FILTER_VALIDATE_URL) === false) {
         $favicon = $url . '/' . $favicon;
      }

      if (isset($favicon)) {
         return [
            [
               'type' => self::ICONTYPE_FAVICON,
               'href' => $favicon,
               'size' => []
            ]
         ];
      }

      return [];
   }
}