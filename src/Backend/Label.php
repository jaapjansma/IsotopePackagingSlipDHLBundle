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

namespace Krabo\IsotopePackagingSlipDHLBundle\Backend;

use Contao\Backend;
use Contao\System;
use Haste\Util\StringUtil;
use Krabo\IsotopePackagingSlipBundle\Model\IsotopePackagingSlipModel;
use Krabo\IsotopePackagingSlipDHLBundle\Factory\DHLConnectionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Label extends Backend {

  /**
   * @var DHLConnectionFactoryInterface
   */
  protected DHLConnectionFactoryInterface $connectionFactory;

  protected function __construct() {
    parent::__construct();
    $this->connectionFactory = System::getContainer()->get('krabo.isotope-packaging-slip-dhl.factory');
  }

  /**
   * Pass an order to the document
   *
   * @param \DataContainer $dc
   *
   * @throws \Exception
   * @return string
   */
  public function printLabel(\DataContainer $dc) {
    $packagingSlip = IsotopePackagingSlipModel::findByPk($dc->id);
    if (empty($packagingSlip->dhl_id)) {
      throw new \Exception('Could not find DHL Package');
    }
    $filename = $this->prepareFileName($packagingSlip->document_number).'.pdf';
    $client = $this->connectionFactory->getClient();
    $requestHeaders = [
      'Authorization' => 'Bearer '.$client->authentication->getAccessToken()->token,
      'Accept' => 'application/pdf'
    ];
    $response = $client->performHttpCall('GET', 'labels/'.$packagingSlip->dhl_id, null, $requestHeaders);
    if ($response->getStatusCode() == 200) {
      $pdf = $response->getBody()->getContents();
      $this->sendPdfToBrowser($pdf, $filename);
    }
  }

  /**
   * Pass an order to the document
   *
   * @param \DataContainer $dc
   *
   * @throws \Exception
   * @return string
   */
  public function printLabels() {
    /** @var Session $objSession */
    $objSession = System::getContainer()->get('session');
    // Get current IDs from session
    $session = $objSession->all();
    $ids = $session['CURRENT']['IDS'];
    $dhl_ids = [];
    $packagingSlips = IsotopePackagingSlipModel::findMultipleByIds($ids);
    foreach($packagingSlips as $packagingSlip) {
      if (!empty($packagingSlip->dhl_id)) {
        $dhl_ids[] = $packagingSlip->dhl_id;
      }
    }

    $filename = 'dhl.pdf';
    $client = $this->connectionFactory->getClient();
    $requestHeaders = [
      'Authorization' => 'Bearer '.$client->authentication->getAccessToken()->token,
      'Accept' => 'application/pdf'
    ];
    $requestBody = [
      'shipmentIds' => $dhl_ids
    ];
    $response = $client->performHttpCall('POST', 'labels/multi', json_encode($requestBody), $requestHeaders);
    if ($response->getStatusCode() == 200) {
      $pdf = $response->getBody()->getContents();
      $this->sendPdfToBrowser($pdf, $filename);
    }
  }

  /**
   * Send PDF to the browser for download
   * @param $pdf
   * @param $filename
   *
   * @return void
   */
  protected function sendPdfToBrowser($pdf, $filename) {
    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Content-Type: application/pdf');

    if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
      // don't use length if server using compression
      header('Content-Length: ' . strlen($pdf));
    }

    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo $pdf;
    exit();
  }

  /**
   * Prepare file name
   *
   * @param string $strName   File name
   *
   * @return string Sanitized file name
   */
  protected function prepareFileName($strName)
  {
    // Replace simple tokens
    $strName = $this->sanitizeFileName(StringUtil::recursiveReplaceTokensAndTags($strName, StringUtil::NO_TAGS | StringUtil::NO_BREAKS | StringUtil::NO_ENTITIES));
    return $strName;
  }

  /**
   * Sanitize file name
   *
   * @param string $strName              File name
   * @param bool   $blnPreserveUppercase Preserve uppercase (true by default)
   *
   * @return string Sanitized file name
   */
  protected function sanitizeFileName($strName, $blnPreserveUppercase = true)
  {
    return standardize(ampersand($strName, false), $blnPreserveUppercase);
  }

}