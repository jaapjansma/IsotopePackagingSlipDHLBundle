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

use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;
use Krabo\IsotopePackagingSlipBundle\Model\PackagingSlipModel;
use Krabo\IsotopePackagingSlipDHLBundle\DHL\Resources\Parcel;
use Mvdnbrk\DhlParcel\Client;
use Mvdnbrk\DhlParcel\Endpoints\Shipments;
use Krabo\IsotopePackagingSlipDHLBundle\DHL\EndPoints\TrackTrace;
use Mvdnbrk\DhlParcel\Exceptions\DhlParcelException;
use Mvdnbrk\DhlParcel\Resources\Recipient;
use Mvdnbrk\DhlParcel\Resources\Shipment;

class DHLFactory implements DHLConnectionFactoryInterface, DHLSenderFactoryInterface {

  protected string $userId;

  protected string $apiKey;

  protected ?string $acoountId = null;

  protected ?Client $client = null;

  protected Recipient $shipper;

  protected string $hsCode;

  public function __construct(string $userId, string $apiKey, ?string $accountId=null) {
    $this->shipper = new Recipient();
    $this->userId = $userId;
    $this->apiKey = $apiKey;
    if ($accountId) {
      $this->acoountId = $accountId;
    }
  }

  /**
   * @param $companyName
   *
   * @return $this
   */
  public function setShipperCompanyName($companyName) {
    $this->shipper->company_name = $companyName;
    return $this;
  }

  /**
   * @param $street
   *
   * @return $this
   */
  public function setShipperStreet($street) {
    $this->shipper->street = $street;
    return $this;
  }

  /**
   * @param $additionalAddressLine
   *
   * @return $this
   */
  public function setShipperAdditionalAddressLine($additionalAddressLine) {
    $this->shipper->additional_address_line = $additionalAddressLine;
    return $this;
  }

  /**
   * @param $number
   *
   * @return $this
   */
  public function setShipperHouseNumber($number) {
    $this->shipper->number = $number;
    return $this;
  }

  /**
   * @param $city
   *
   * @return $this
   */
  public function setShipperCity($city) {
    $this->shipper->city = $city;
    return $this;
  }

  /**
   * @param $postalCode
   *
   * @return $this
   */
  public function setShipperPostalCode($postalCode) {
    $this->shipper->postal_code = $postalCode;
    return $this;
  }

  /**
   * @param $countryCode
   *
   * @return $this
   */
  public function setShipperCountryCode($countryCode) {
    $this->shipper->cc = $countryCode;
    return $this;
  }

  /**
   * @param $hsCode
   *
   * @return $this
   */
  public function setHsCode(?string $hsCode) {
    $this->hsCode = $hsCode;
    return $this;
  }

  /**
   * Returns the reason for custom declaration
   *
   * @param PackagingSlipModel $packagingSlipModel
   *
   * @return string
   */
  public function getCustomDeclarationReason(PackagingSlipModel $packagingSlipModel): string {
    return $GLOBALS['TL_LANG']['isotopepackagingslipdhl']['custom_declaration_reason'];
  }

  /**
   * @return string
   */
  public function getHsCode(): string {
    return $this->hsCode;
  }


  /**
   * Returns the sender
   *
   * @return \Mvdnbrk\DhlParcel\Resources\Recipient
   */
  public function getSender(): Recipient {
    return $this->shipper;
  }

  /**
   * Returns a client
   *
   * @return \Mvdnbrk\DhlParcel\Client
   */
  public function getClient(): Client {
    if (!$this->client) {
      $this->client = new Client();
      $this->client->setUserId($this->userId);
      $this->client->setApiKey($this->apiKey);
      if ($this->acoountId) {
        $this->client->setAccountId($this->acoountId);
      }
      $this->client->initializeEndpoints();
    }
    return $this->client;
  }

  /**
   * Creates a parcel in DHL for this Packaging Slip
   *
   * @param \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel $isotopePackagingSlipModel
   *
   * @return void
   */
  public function checkParcelStatus(IsotopePackagingSlipModel $packagingSlip): void {
    if ($packagingSlip->dhl_id) {
      $trackTrace = new TrackTrace($this->getClient());
      try {
        $response = $trackTrace->get($packagingSlip->dhl_tracker_code);
        if ($response->isDelivered) {
          $packagingSlip->status = IsotopePackagingSlipModel::STATUS_DELIVERED;
        }
      } catch (DhlParcelException $ex) {
        // Do nothing.
      }
    }
    $packagingSlip->dhl_status_check_tstamp = time();
    $packagingSlip->save();
  }

  /**
   * Creates a parcel in DHL for this Packaging Slip
   *
   * @param \Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel $isotopePackagingSlipModel
   *
   * @return void
   */
  public function createParcel(IsotopePackagingSlipModel $packagingSlip): void {
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
      'options' => [
        'description' => $packagingSlip->document_number,
      ],
    ]);
    if ($packagingSlip->dhl_servicepoint_id) {
      $parcel->servicePoint($packagingSlip->dhl_servicepoint_id);
    }
    $parcel->sender = $this->getSender();
    $shipments = new Shipments($this->getClient());
    $shipment = $shipments->create($parcel);
    $this->saveShipmentInfo($packagingSlip, $shipment);
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
    $packagingSlipModel->dhl_id = $shipment->id;
    $packagingSlipModel->barcode = $shipment->barcode;
  }


}