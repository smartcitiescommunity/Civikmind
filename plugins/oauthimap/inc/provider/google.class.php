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

namespace GlpiPlugin\Oauthimap\Provider;

use GlpiPlugin\Oauthimap\Oauth\OwnerDetails;
use League\OAuth2\Client\Token\AccessToken;

class Google extends \League\OAuth2\Client\Provider\Google implements ProviderInterface {

   public static function getName(): string {
      return 'Google';
   }

   public static function getIcon(): string {
      return 'fa-google';
   }

   public function getOwnerDetails(AccessToken $token): ?OwnerDetails {
      /* @var \League\OAuth2\Client\Provider\GoogleUser $owner */
      $owner = $this->getResourceOwner($token);

      $owner_details = new OwnerDetails();
      $owner_details->email     = $owner->getEmail();
      $owner_details->firstname = $owner->getFirstName();
      $owner_details->lastname  = $owner->getLastName();

      return $owner_details;
   }
}
