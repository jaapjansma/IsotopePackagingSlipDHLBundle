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

namespace Krabo\IsotopePackagingSlipDHLBundle\DHL\Resources;

class TrackTrace extends \Mvdnbrk\DhlParcel\Resources\TrackTrace {

  /**
   * @var string
   */
  public $barcode;

  /**
   * @var bool
   */
  public $isDelivered;

  /**
   * Create a new Track Trace Collection instance.
   *
   * @param  array  $attributes
   * @return void
   */
  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);

    $this->isDelivered = collect($attributes)->has('deliveredAt');
    if (!$this->isDelivered) {
      foreach($attributes['view']->phaseDisplay as $phase) {
        if ($phase->phase == 'DELIVERED') {
          $this->isDelivered = TRUE;
          break;
        }
      }
    }
  }

}