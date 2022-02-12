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
use Krabo\IsotopePackagingSlipBundle\Event\Events;
use Krabo\IsotopePackagingSlipBundle\Event\GenerateAddressEvent;
use Krabo\IsotopePackagingSlipBundle\Event\PackagingSlipOrderEvent;
use Krabo\IsotopePackagingSlipBundle\Event\StatusChangedEvent;
use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;
use Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLConnectionFactoryInterface;
use Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLSenderFactoryInterface;
use Mvdnbrk\DhlParcel\Endpoints\Shipments;
use Krabo\IsotopePackagingSlipDHLBundle\DHL\Resources\Parcel;
use Krabo\IsotopePackagingSlipDHLBundle\DHL\EndPoints\ServicePoints;
use Mvdnbrk\DhlParcel\Resources\Shipment;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PackagingSlipListener implements EventSubscriberInterface {

  /**
   * @var \Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLConnectionFactoryInterface
   */
  protected $dhlConnection;

  /**
   * @var \Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLSenderFactoryInterface
   */
  protected $senderFactory;

  /**
   * @var \Symfony\Contracts\Cache\CacheInterface
   */
  protected $cache;

  public function __construct(DHLConnectionFactoryInterface $dhlConnection, DHLSenderFactoryInterface $senderFactory, CacheInterface $cache) {
    $this->dhlConnection = $dhlConnection;
    $this->senderFactory = $senderFactory;
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
    ];
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
      $strAddress = $GLOBALS['TL_LANG']['MSC']['shipping_dhl_pickup'] .'<br />'.$servicePoint->name;
      $strAddress .= '<br>' . $event->getGeneratedAddress();
      $event->setGeneratedAddress($strAddress);
    }
  }

  public function onCreatedFromOrder(PackagingSlipOrderEvent $event) {
    if ($shippingAddress = $event->getOrder()->getShippingAddress()) {
      if ($shippingAddress->dhl_servicepoint_id) {
        $event->getPackagingSlip()->dhl_servicepoint_id = $shippingAddress->dhl_servicepoint_id;
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
      $weight = $packagingSlip->getTotalWeight();
      $recipient = [
        'first_name' => $packagingSlip->firstname,
        'last_name' => $packagingSlip->lastname,
        'street' => $packagingSlip->street_1,
        'number' => $packagingSlip->housenumber,
        'postal_code' => $packagingSlip->postal,
        'city' => $packagingSlip->city,
        'cc' => strtoupper($packagingSlip->country),
      ];
      if ($packagingSlip->street_2 || $packagingSlip->street_3) {
        $recipient['additional_address_line'] = trim(implode(" ", [$packagingSlip->street_2, $packagingSlip->street_3]));
      }
      if ($packagingSlip->email) {
        $recipient['email'] = $packagingSlip->email;
      }
      if ($packagingSlip->phone) {
        $recipient['phoneNumber'] = $packagingSlip->phone;
      }
      $parcel = new Parcel([
        'reference' => $packagingSlip->document_number,
        'recipient' => $recipient,
        'pieces' => [
          [
            'quantity' => 1,
            'weight' => $weight,
          ],
        ],
      ]);
      if ($packagingSlip->dhl_servicepoint_id) {
        $parcel->servicePoint($packagingSlip->dhl_servicepoint_id);
      }
      $parcel->sender = $this->senderFactory->getSender();
      $shipments = new Shipments($this->dhlConnection->getClient());
      $shipment = $shipments->create($parcel);
      $this->saveShipmentInfo($packagingSlip, $shipment);
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

  /**
   * Save a shipment
   *
   * @param \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel $packagingSlipModel
   * @param \Mvdnbrk\DhlParcel\Resources\Shipment $shipment
   *
   * @return void
   */
  private function saveShipmentInfo(IsotopePackagingSlipModel $packagingSlipModel, Shipment $shipment) {
    $db = \Contao\Database::getInstance();
    $updateQuery = "UPDATE `".IsotopePackagingSlipModel::getTable()."` SET `dhl_id` = ?, `dhl_tracker_code` = ? WHERE `id` = ?";
    $updateQueryParams = [
      $shipment->id,
      $shipment->barcode,
      $packagingSlipModel->id
    ];
    $db->prepare($updateQuery)->execute($updateQueryParams);
  }


}