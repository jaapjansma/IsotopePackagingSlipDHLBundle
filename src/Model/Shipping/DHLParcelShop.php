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

namespace Krabo\IsotopePackagingSlipDHLBundle\Model\Shipping;

use Isotope\Model\Shipping;
use Isotope\Model\Shipping\Flat;

class DHLParcelShop extends Flat {

  public static function getParcelShopShippingMethod():? Shipping {
    $arrColumns[] = "type = 'isopackagingslip_dhl_parcel_shop'";
    $arrColumns[] = "enabled = '1'";
    /** @var Shipping[] $objModules */
    $objModules = Shipping::findBy($arrColumns, NULL);
    if (NULL !== $objModules) {
      foreach ($objModules as $objModule) {
        if (!$objModule->isAvailable()) {
          continue;
        }
        return $objModule;
      }
    }
    return null;
  }

}