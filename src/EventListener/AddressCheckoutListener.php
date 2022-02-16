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

use Krabo\IsotopeShippingAddressCheckoutBundle\Event\Events;
use Krabo\IsotopeShippingAddressCheckoutBundle\Event\FilterAddressEvent;
use Krabo\IsotopeShippingAddressCheckoutBundle\Event\FilterAddressFieldsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddressCheckoutListener implements EventSubscriberInterface {

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * For instance:
   *
   *  * ['eventName' => 'methodName']
   *  * ['eventName' => ['methodName', $priority]]
   *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
   *
   * The code must not depend on runtime state as it will only be called at
   * compile time. All logic depending on runtime state must be put into the
   * individual methods handling the events.
   *
   * @return array<string, mixed> The event names to listen to
   */
  public static function getSubscribedEvents() {
    return [
      Events::FILTER_BILLING_ADDRESS => 'onFilterBillingAddress',
      Events::FILTER_SHIPPING_ADDRESS => 'onFilterBillingAddress',
      Events::FILTER_SHIPPING_ADDRESS_FIELDS => 'onFilterShippingAddressFields',
    ];
  }

  public function onFilterBillingAddress(FilterAddressEvent $event) {
    $filteredAddresses = array_filter($event->getAddresses(), function($address, $idx) {
      if (!empty($address->dhl_servicepoint_id)) {
        return FALSE;
      }
      return TRUE;
    }, ARRAY_FILTER_USE_BOTH);
    $event->setAddresses($filteredAddresses);
  }

  public function onFilterShippingAddressFields(FilterAddressFieldsEvent $event) {
    $fields = $event->getFields();
    foreach($fields as $index => $field) {
      if ($field['value'] == 'dhl_servicepoint_id') {
        unset($fields[$index]);
      }
    }
    $event->setFields($fields);
  }

}