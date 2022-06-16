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
use Krabo\IsotopePackagingSlipDHLBundle\DHL\Resources\TrackTrace as TrackTraceResource;

class TrackTrace extends BaseEndpoint {

  /**
   * Get Track & Trace information.
   *
   * @param  string  $value
   * @return \Mvdnbrk\DhlParcel\Resources\TrackTrace
   */
  public function get(string $value)
  {
    $response = $this->performApiCall(
      'GET',
      'track-trace'.$this->buildQueryString(['key' => $value])
    );

    return new TrackTraceResource(
      collect(collect($response)->first())->all()
    );
  }

}