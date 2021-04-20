<?php

/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * JSS Pro API interface class
 * @since 1.0.0
 */
 class PluginJamfAPIPro {
    /** PluginJamfConnection object representing the connection to a JSS server */
    private static $connection;

    /**
     * Get data from a JSS Pro API endpoint.
     * @since 1.0.0
     * @param string  $endpoint The API endpoint.
     * @param bool    $raw If true, data is returned as JSON instead of decoded into an array.
     * @return mixed JSON string or associative array depending on the value of $raw.
     */
    private static function get(string $endpoint, $raw = false)
    {
        if (!self::$connection) {
            self::$connection = new PluginJamfConnection();
        }
        $url = (self::$connection)->getAPIUrl($endpoint, true);
        $curl = curl_init($url);
        // Set the username and password in an authentication header
        self::$connection->setCurlAuth($curl);
        self::$connection->setCurlSecurity($curl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
           'Content-Type: application/json',
           'Accept: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        if (!$response) {
           return null;
        }
        return ($raw ? $response : json_decode($response, true));
    }

    /**
     * Get data from the lobby endpoint. This should only contain the JSS version.
     * @return array Associative array of the data from the lobby endpoint.
     */
    public static function getLobby()
    {
       return self::get('/');
    }

    /**
     * Get an array of all mobile devices. The returned data includes only some fields.
     * To get all data for a mobile device, use PluginJamfProAPI::getMobileDevice().
     * @since 1.0.0
     * @return array Array of mobile devices and some basic fields for each.
     */
    public static function getAllMobileDevices()
    {
       if (!self::$connection) {
          self::$connection = new PluginJamfConnection();
       }
       $connection = self::$connection;
       if (version_compare($connection->getServerVersion(), '10.14.0', '>=')) {
          return self::get('/v1/mobile-devices');
       }

       return self::get('/inventory/obj/mobileDevice');
    }

    /**
     * Get data for a specific mobile device by its id.
     * @param int $id The ID of the device.
     * @param bool $detailed If true, all fields are returned. Otherwise, only a basic subset of fields are returned.
     * @return array Associative array of fields for the specified device.
     */
    public static function getMobileDevice(int $id, bool $detailed = false)
    {
       if (!self::$connection) {
          self::$connection = new PluginJamfConnection();
       }
       $connection = self::$connection;
       if (version_compare($connection->getServerVersion(), '10.14.0', '>=')) {
          $endpoint = $endpoint = "/v1/mobile-devices/{$id}".($detailed ? '/detail' : '');
       } else {
          $endpoint = "/inventory/obj/mobileDevice/{$id}".($detailed ? '/detail' : '');
       }
       return self::get($endpoint);
    }
 }