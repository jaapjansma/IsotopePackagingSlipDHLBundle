<?php
/**
 * Copyright (C) 2026  Jaap Jansma (jaap.jansma@civicoop.org)
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

namespace Krabo\IsotopePackagingSlipDHLBundle\DHL;

use Krabo\IsotopePackagingSlipDHLBundle\DHL\EndPoints\ServicePoints;
use Mvdnbrk\DhlParcel\Endpoints\Authentication;
use Mvdnbrk\DhlParcel\Endpoints\Labels;
use Mvdnbrk\DhlParcel\Endpoints\Shipments;
use Mvdnbrk\DhlParcel\Endpoints\TrackTrace;

class Client extends \Mvdnbrk\DhlParcel\Client {

  public function initializeEndpoints(): void
  {
    $this->authentication = new Authentication($this);
    $this->labels = new Labels($this);
    $this->servicePoints = new ServicePoints($this);
    $this->shipments = new Shipments($this);
    $this->tracktrace = new TrackTrace($this);
  }

}