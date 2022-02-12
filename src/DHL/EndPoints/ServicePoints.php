<?php
/**
 * Copyright (C) 2022  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Krabo\IsotopePackagingSlipDHLBundle\DHL\EndPoints;

use Mvdnbrk\DhlParcel\Endpoints\BaseEndpoint;
use Mvdnbrk\DhlParcel\Resources\ServicePoint as ServicePointResource;

class ServicePoints extends BaseEndpoint {

  /**
   * Get a collection of service points.
   *
   * @param string $id
   * @param string $country
   * @return ServicePointResource
   */
  public function getById(string $id, string $country='NL')
  {
    $response = $this->performApiCall(
      'GET',
      'parcel-shop-locations/'.$country.'/'.$id,
    );

    return new ServicePointResource($response);
  }

}