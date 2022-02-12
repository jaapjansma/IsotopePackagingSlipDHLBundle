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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

  /**
   * Generates the configuration tree builder.
   *
   * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree
   *   builder
   */
  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder('isotope-packaging-slip-dhl');
    $treeBuilder->getRootNode()
      ->children()
      ->scalarNode('user_id')->isRequired()->end()
      ->scalarNode('key')->isRequired()->end()
      ->scalarNode('account_id')->end()
      ->scalarNode('shipper_company_name')->end()
      ->scalarNode('shipper_street')->end()
      ->scalarNode('shipper_housenumber')->end()
      ->scalarNode('shipper_additional_address_line')->end()
      ->scalarNode('shipper_postal_code')->end()
      ->scalarNode('shipper_city')->end()
      ->scalarNode('shipper_country_code')->end()
      ->scalarNode('hscode')->end()
      ->scalarNode('google_maps_api_key')->end()
      ->end();
    return $treeBuilder;
  }


}