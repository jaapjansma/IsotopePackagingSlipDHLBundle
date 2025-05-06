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

use \Krabo\IsotopePackagingSlipDHLBundle\EventListener\ProductCollectionListener;

$GLOBALS['ISO_HOOKS']['createFromProductCollection'][] = [ProductCollectionListener::class, 'createFromProductCollection'];
$GLOBALS['ISO_HOOKS']['getOrderNotificationTokens'][] = [ProductCollectionListener::class, 'getOrderNotificationTokens'];

$GLOBALS['BE_MOD']['isotope']['tl_isotope_packaging_slip']['print_dhl_label'] = ['Krabo\IsotopePackagingSlipDHLBundle\Backend\Label', 'printLabel'];
$GLOBALS['BE_MOD']['isotope']['tl_isotope_packaging_slip']['print_dhl_labels'] = ['Krabo\IsotopePackagingSlipDHLBundle\Backend\Label', 'printLabels'];

\Isotope\Model\Shipping::registerModelType('isopackagingslip_dhl', 'Krabo\IsotopePackagingSlipDHLBundle\Model\Shipping\DHL');
\Isotope\Model\Shipping::registerModelType('isopackagingslip_dhl_parcel_shop', 'Krabo\IsotopePackagingSlipDHLBundle\Model\Shipping\DHLParcelShop');

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'dhl_tracker_code';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'dhl_tracker_link';