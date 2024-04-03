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

namespace Krabo\IsotopePackagingSlipDHLBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class IsotopePackagingSlipDHLExtension extends Extension  {

  /**
   * Loads a specific configuration.
   *
   * @throws \InvalidArgumentException When provided tag is not defined in this
   *   extension
   */
  public function load(array $configs, ContainerBuilder $container) {
    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('services.yml');

    $container->setParameter('krabo.isotope-packaging-slip-dhl.user_id', $config['user_id']);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.key', $config['key']);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.account_id', null);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.hscode', null);
    if (isset($config['account_id'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.account_id', $config['account_id']);
    }
    if (isset($config['hscode'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.hscode', $config['hscode']);
    }
    if (isset($config['google_maps_api_key'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.google_maps_api_key', $config['google_maps_api_key']);
    }
    if (isset($config['email_pickup_not_available'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.email_pickup_not_available', $config['email_pickup_not_available']);
    }


    $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_company_name', null);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_street', null);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_housenumber', null);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_additional_address_line', null);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_postal_code', null);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_city', null);
    $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_country_code', null);

    if (isset($config['shipper_company_name'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_company_name', $config['shipper_company_name']);
    }
    if (isset($config['shipper_street'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_street', $config['shipper_street']);
    }
    if (isset($config['shipper_housenumber'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_housenumber', $config['shipper_housenumber']);
    }
    if (isset($config['shipper_additional_address_line'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_additional_address_line', $config['shipper_additional_address_line']);
    }
    if (isset($config['shipper_postal_code'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_postal_code', $config['shipper_postal_code']);
    }
    if (isset($config['shipper_city'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_city', $config['shipper_city']);
    }
    if (isset($config['shipper_country_code'])) {
      $container->setParameter('krabo.isotope-packaging-slip-dhl.shipper_country_code', $config['shipper_country_code']);
    }
  }


}