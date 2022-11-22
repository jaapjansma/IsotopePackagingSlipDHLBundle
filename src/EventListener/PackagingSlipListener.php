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

use Isotope\Model\Shipping;
use Krabo\IsotopePackagingSlipBundle\Event\CheckAvailabilityEvent;
use Krabo\IsotopePackagingSlipBundle\Event\Events;
use Krabo\IsotopePackagingSlipBundle\Event\GenerateAddressEvent;
use Krabo\IsotopePackagingSlipBundle\Event\GenerateTrackTraceTokenEvent;
use Krabo\IsotopePackagingSlipBundle\Event\PackagingSlipOrderEvent;
use Krabo\IsotopePackagingSlipBundle\Event\StatusChangedEvent;
use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;
use Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLConnectionFactoryInterface;
use Krabo\IsotopePackagingSlipDHLBundle\DHL\EndPoints\ServicePoints;
use Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLFactory;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PackagingSlipListener implements EventSubscriberInterface {

  /**
   * @var \Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLConnectionFactoryInterface
   */
  protected $dhlConnection;

  /**
   * @var \Symfony\Contracts\Cache\CacheInterface
   */
  protected $cache;

  public function __construct(DHLConnectionFactoryInterface $dhlConnection, CacheInterface $cache) {
    $this->dhlConnection = $dhlConnection;
    $this->cache = $cache;
  }

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
      Events::STATUS_CHANGED_EVENT => 'onStatusChanged',
      Events::PACKAGING_SLIP_CREATED_FROM_ORDER => 'onCreatedFromOrder',
      Events::GENERATE_ADDRESS => 'onGenerateAddress',
      Events::CHECK_AVAILABILITY => 'onCheckAvailability',
      Events::GENERATE_TRACKTRACE_TOKEN => 'onGenerateTrackTraceToken',
    ];
  }

  public function onGenerateTrackTraceToken(GenerateTrackTraceTokenEvent $event) {
    $sql = "
      SELECT `dhl_tracker_code`, `dhl_tracker_link`
      FROM `tl_isotope_packaging_slip`
      WHERE `id` = ?
      LIMIT 0, 1
    ";
    $result = \Database::getInstance()->prepare($sql)->execute($event->getPackagingSlip()->id);

    if (!empty($result->dhl_tracker_code)) {
      $event->trackAndTraceCode = $result->dhl_tracker_code;
      $link = $result->dhl_tracker_link;
      if (empty($link)) {
        $link = DHLFactory::TRACKTRACE_LINK . $result->dhl_tracker_code;
      }
      if (!empty($link)) {
        $event->trackAndTrace = '<a href="'.$link.'">'.$result->dhl_tracker_code.'</a>';
      } else {
        $event->trackAndTrace = $result->dhl_tracker_code;
      }
    }
  }

  public function onGenerateAddress(GenerateAddressEvent $event) {
    if ($event->getPackagingSlip()->dhl_servicepoint_id) {
      $servicePointId = $event->getPackagingSlip()->dhl_servicepoint_id;
      $cacheKey = 'isotopepackagingslipdhl_parcelshop_'.$servicePointId;
      $cachedServicePoint = $this->cache->get($cacheKey, function() use ($servicePointId) {
        $servicepointApi = new ServicePoints($this->dhlConnection->getClient());
        $servicePoint = $servicepointApi->getById($servicePointId);
        $item = new CacheItem();
        $item->set($servicePoint);
        return $item;
      });
      $servicePoint = $cachedServicePoint->get();
      $strAddress = $event->getGeneratedAddress();
      $strAddress = $GLOBALS['TL_LANG']['MSC']['shipping_dhl_pickup'] . "<br>\n" . $servicePoint->name . "<br>\n" . $strAddress;
      $event->setGeneratedAddress($strAddress);
    }
  }

  public function onCreatedFromOrder(PackagingSlipOrderEvent $event) {
    if ($shippingAddress = $event->getOrder()->getShippingAddress()) {
      if ($shippingAddress->dhl_servicepoint_id) {
        $order = $event->getOrder();
        $event->getPackagingSlip()->dhl_servicepoint_id = $shippingAddress->dhl_servicepoint_id;
        $event->getPackagingSlip()->company = '';
        if ($order->getBillingAddress()->company); {
          $event->getPackagingSlip()->company = $order->getBillingAddress()->company;
        }
        $event->getPackagingSlip()->housenumber = $order->getBillingAddress()->housenumber;
        $event->getPackagingSlip()->street_1 = $order->getBillingAddress()->street_1;
        $event->getPackagingSlip()->street_2 = $order->getBillingAddress()->street_2;
        $event->getPackagingSlip()->street_3 = $order->getBillingAddress()->street_3;
        $event->getPackagingSlip()->postal = $order->getBillingAddress()->postal;
        $event->getPackagingSlip()->city = $order->getBillingAddress()->city;

        $event->getPackagingSlip()->save();
      }
    }
  }

  /**
   * Create parcel when status of the packaging slip is changed to prepare for
   * shipping
   *
   * @param \Krabo\IsotopePackagingSlipBundle\Event\StatusChangedEvent $event
   *
   * @return void
   */
  public function onStatusChanged(StatusChangedEvent $event) {
    $packagingSlip = $event->getPackagingSlip();
    $isReadyForDHL = $this->isPackagingSlipReadyForDHL($packagingSlip);
    if ($event->getNewStatus() == IsotopePackagingSlipModel::STATUS_PREPARE_FOR_SHIPPING && $isReadyForDHL) {
      //$this->dhlConnection->createParcel($packagingSlip);
    }
  }

  /**
   * @param \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel $packagingSlipModel
   * @return bool
   */
  private function isPackagingSlipReadyForDHL(IsotopePackagingSlipModel $packagingSlipModel): bool {
    if (!empty($packagingSlipModel->dhl_id)) {
      return false;
    }
    $shippingMethod = Shipping::findByPk($packagingSlipModel->shipping_id);
    if (!in_array($shippingMethod->type, ['isopackagingslip_dhl'])) {
      return false;
    }
    return true;
  }

  public function onCheckAvailability(CheckAvailabilityEvent $event) {
    $packagingSlip = IsotopePackagingSlipModel::findByPk($event->packagingSlipId);
    if ($packagingSlip->dhl_servicepoint_id) {
      $client = $this->dhlConnection->getClient();
      $servicePoints = $client->servicePoints->get(['q' => $packagingSlip->dhl_servicepoint_id]);
      if (!$servicePoints->count()) {
        $event->isAvailable = '-1';
        $event->notes .= $GLOBALS['TL_LANG']['MSC']['shipping_dhl_pickup_not_available'];
      }
    }
  }


}