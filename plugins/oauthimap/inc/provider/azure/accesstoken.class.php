<?php
/**
 -------------------------------------------------------------------------
 oauthimap plugin for GLPI
 Copyright (C) 2018-2020 by the oauthimap Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of oauthimap.

 oauthimap is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 oauthimap is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with oauthimap. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Oauthimap\Provider\Azure;

use Firebase\JWT\JWT;

/**
 * Redefine Azure access token parsing to prevent "Invalid token issuer!" issue due to
 * "https://sts.windows.net/3c2ae83b-7e79-4bc8-8d80-5baf0f272030/" in issuer value.
 */
class AccessToken extends \TheNetworg\OAuth2\Client\Token\AccessToken {

   public function __construct(array $options, $provider) {
      \League\OAuth2\Client\Token\AccessToken::__construct($options);

      if (!empty($options['id_token'])) {
         $this->idToken = $options['id_token'];

         $keys          = $provider->getJwtVerificationKeys();
         $idTokenClaims = null;
         try {
            $tks = explode('.', $this->idToken);
            // Check if the id_token contains signature
            if (3 == count($tks) && !empty($tks[2])) {
               $idTokenClaims = (array)JWT::decode($this->idToken, $keys, ['RS256']);
            } else {
               // The id_token is unsigned (coming from v1.0 endpoint) - https://msdn.microsoft.com/en-us/library/azure/dn645542.aspx

               // Since idToken is not signed, we just do OAuth2 flow without validating the id_token
               // // Validate the access_token signature first by parsing it as JWT into claims
               // $accessTokenClaims = (array)JWT::decode($options['access_token'], $keys, ['RS256']);
               // Then parse the idToken claims only without validating the signature
               $idTokenClaims = (array)JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
            }
         } catch (\Exception $e) {
            throw new \RuntimeException('Unable to parse the id_token!');
         }
         if ($provider->getClientId() != $idTokenClaims['aud']) {
            throw new \RuntimeException('The audience is invalid!');
         }
         if ($idTokenClaims['nbf'] > time() || $idTokenClaims['exp'] < time()) {
            // Additional validation is being performed in firebase/JWT itself
            throw new \RuntimeException('The id_token is invalid!');
         }

         if ('common' == $provider->tenant) {
            $provider->tenant = $idTokenClaims['tid'];
         }

         $tenant = $provider->getTenantDetails($provider->tenant);
         if (!preg_match('/' . preg_quote('/' . $idTokenClaims['tid'] . '/', '/') . '/', $tenant['issuer'])) {
            throw new \RuntimeException('Invalid token issuer!');
         }

         $this->idTokenClaims = $idTokenClaims;
      }
   }
}
