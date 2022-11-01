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

namespace Krabo\IsotopePackagingSlipDHLBundle\EventListener;

use Isotope\Model\ProductCollection;

class ProductCollectionListener {

  /**
   * Copy the DHL Service point ID.
   *
   * @param \Isotope\Model\ProductCollection $objCollection
   * @param \Isotope\Model\ProductCollection $objSource
   * @param $arrItemIds
   *
   * @return void
   */
  public function createFromProductCollection(ProductCollection $objCollection, ProductCollection $objSource, $arrItemIds) {
    if ($shippingAddress = $objSource->getShippingAddress()) {
      if ($shippingAddress->dhl_servicepoint_id) {
        $objCollection->getShippingAddress()->dhl_servicepoint_id = $shippingAddress->dhl_servicepoint_id;
        $objCollection->getShippingAddress()->save();
      }
    }
  }

  /**
   * Add the DHL Tracker Code
   *
   * @param \Isotope\Model\ProductCollection\Order $order
   * @param $arrTokens
   *
   * @return mixed
   */
  public function getOrderNotificationTokens(ProductCollection\Order $order, &$arrTokens) {
    $sql = "
      SELECT `dhl_tracker_code`, `dhl_tracker_link`
      FROM `tl_isotope_packaging_slip`
      INNER JOIN `tl_isotope_packaging_slip_product_collection` ON `tl_isotope_packaging_slip_product_collection`.`pid` = `tl_isotope_packaging_slip`.`id`
      WHERE `tl_isotope_packaging_slip_product_collection`.`document_number` = ?
      AND (`dhl_tracker_code` != '' OR `dhl_tracker_link` != '')
      ORDER BY `tl_isotope_packaging_slip`.`tstamp` DESC
      LIMIT 0, 1
    ";
    $result = \Database::getInstance()->prepare($sql)->execute($order->document_number);
    if ($result) {
      $arrTokens['dhl_tracker_code'] = $result->dhl_tracker_code;
      $arrTokens['dhl_tracker_link'] = $result->dhl_tracker_link;
    }
    return $arrTokens;
  }

}