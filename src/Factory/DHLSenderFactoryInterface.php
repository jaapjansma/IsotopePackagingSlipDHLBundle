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

namespace Krabo\IsotopePackagingSlipDHLBundle\Factory;

use Krabo\IsotopePackagingSlipBundle\Model\PackagingSlipModel;
use Mvdnbrk\DhlParcel\Resources\Recipient;

interface DHLSenderFactoryInterface {

  /**
   * Returns the sender
   * 
   * @return \Mvdnbrk\DhlParcel\Resources\Recipient
   */
  public function getSender(): Recipient;

  /**
   * Returns the reason for custom declaration
   *
   * @param PackagingSlipModel $packagingSlipModel
   * @return string
   */
  public function getCustomDeclarationReason(PackagingSlipModel $packagingSlipModel): string;

  /**
   * @return string
   */
  public function getHsCode(): string;

}