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

namespace Krabo\IsotopePackagingSlipDHLBundle\Cron;

use Contao\CoreBundle\ServiceAnnotation\CronJob;
use Contao\Database;
use Isotope\Model\Shipping;
use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;
use Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLConnectionFactoryInterface;
use Mvdnbrk\DhlParcel\Exceptions\DhlParcelException;

/**
 * @CronJob("minutely")
 */
class CreateParcelCron {

  /**
   * @var \Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLConnectionFactoryInterface
   */
  protected $connectionFactory;

  /**
   * @param \Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLConnectionFactoryInterface $connectionFactory
   */
  public function __construct(DHLConnectionFactoryInterface $connectionFactory) {
    $this->connectionFactory = $connectionFactory;
  }

  public function __invoke(): void {
    $db = Database::getInstance();
    $tableName = IsotopePackagingSlipModel::getTable();
    $shippingTableName = Shipping::getTable();
    $results = $db->prepare("
      SELECT `" . $tableName . "`.`id` 
      FROM `" . $tableName . "`
      INNER JOIN `" . $shippingTableName . "` ON `" . $tableName . "`.`shipping_id` = `" . $shippingTableName . "`.`id`
      WHERE (`" . $shippingTableName . "`.`type` = 'isopackagingslip_dhl' OR `" . $shippingTableName . "`.`type` = 'isopackagingslip_dhl_parcel_shop')
      AND `" . $tableName . "`.`dhl_id` = ''
      AND `" . $tableName . "`.`status` = '" . IsotopePackagingSlipModel::STATUS_SHIPPED . "'
      ORDER BY `id` ASC
      LIMIT 0, 25")->execute();
    while ($results->next()) {
      $packagingSlip = IsotopePackagingSlipModel::findByPk($results->id);
      try {
        $this->connectionFactory->createParcel($packagingSlip);
      } catch (DhlParcelException $e) {
        // Do nothing
      }
    }
  }

}