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

namespace Krabo\IsotopePackagingSlipDHLBundle\Model\Address;

use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Template;
use Symfony\Component\Routing\Route;

class DHLParcelShop {

  /**
   * Frontend template instance
   * @var Template|\stdClass
   */
  protected $pickupTemplate;

  /**
   * Create template
   */
  public function __construct()
  {
    $this->pickupTemplate = new Template('iso_checkout_dhlpickup');
  }

  public function getOptionsForDHLParcelShop($arrFields, $blnValidate) {
    $router = \System::getContainer()->get('router');
    $billingAddress = Isotope::getCart()->getBillingAddress();
    $shippingAddress = Isotope::getCart()->getShippingAddress();
    $this->pickupTemplate->headline = $GLOBALS['TL_LANG']['MSC']['shipping_dhl_pickup'];
    $this->pickupTemplate->message  = $GLOBALS['TL_LANG']['MSC']['shipping_dhl_pickup_message'];
    $this->pickupTemplate->selectParcelShopUrl = $router->generate('isotopepackagingslipdhl_selectparcelshop');
    $this->pickupTemplate->postal_code = $billingAddress->postal;
    $this->pickupTemplate->country = $billingAddress->country;
    $this->pickupTemplate->dhl_servicepoint_id = $shippingAddress->dhl_servicepoint_id;
    $this->pickupTemplate->dhlpickup_info = '';
    if ($shippingAddress->dhl_servicepoint_id) {
      $this->pickupTemplate->dhlpickup_info = $shippingAddress->company . '<br>' . $shippingAddress->street_1 . ' ' . $shippingAddress->housenumber . '<br>' . $shippingAddress->postal . '<br>' . $shippingAddress->city;
      $this->pickupTemplate->dhlpickup_servicepoint_name = substr($shippingAddress->company, strlen($GLOBALS['TL_LANG']['MSC']['shipping_dhl_pickup'].' '));
      $this->pickupTemplate->dhlpickup_servicepoint_street = $shippingAddress->street_1;
      $this->pickupTemplate->dhlpickup_servicepoint_housenumber = $shippingAddress->housenumber;
      $this->pickupTemplate->dhlpickup_servicepoint_postal = $shippingAddress->postal;
      $this->pickupTemplate->dhlpickup_servicepoint_city = $shippingAddress->city;
    }


    $arrOptions[] = [
      'value'     => 'dhlpickup-',
      'label'     => $this->pickupTemplate->parse(),
      'default'   => $shippingAddress->dhl_servicepoint_id ? true : false,
    ];
    return $arrOptions;
  }

  public function getAddressForOption($varValue, $blnValidate) {
    if (stripos($varValue, 'dhlpickup-') === 0 && \Input::post('dhlpickup_servicepoint_id')) {
      $objAddress = Address::createForProductCollection(Isotope::getCart(), Isotope::getConfig()->getShippingFields(), false, false);
      $objAddress->company = $GLOBALS['TL_LANG']['MSC']['shipping_dhl_pickup'].' '. \Input::post('dhlpickup_servicepoint_name');
      $objAddress->street_1 = \Input::post('dhlpickup_servicepoint_street');
      $objAddress->housenumber = \Input::post('dhlpickup_servicepoint_housenumber');
      $objAddress->postal = \Input::post('dhlpickup_servicepoint_postal');
      $objAddress->city = \Input::post('dhlpickup_servicepoint_city');
      $objAddress->dhl_servicepoint_id = \Input::post('dhlpickup_servicepoint_id');
      $objAddress->country = 'nl';
      if ($blnValidate) {
        $objAddress->save();
        Isotope::getCart()->setShippingAddress($objAddress);
        Isotope::getCart()->save();
      }
      return $objAddress;
    } elseif (stripos($varValue, 'dhlpickup-') === 0 && Isotope::getCart()->getShippingAddress() && Isotope::getCart()->getShippingAddress()->dhl_servicepoint_id) {
      return Isotope::getCart()->getShippingAddress();
    }
    return null;
  }

}