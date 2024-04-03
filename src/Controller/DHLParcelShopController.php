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

namespace Krabo\IsotopePackagingSlipDHLBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DHLParcelShopController implements ContainerAwareInterface {
  use ContainerAwareTrait;

  protected ContaoFramework $framework;

  public function __construct(ContaoFramework $framework) {
    $this->framework = $framework;
    $this->framework->initialize();
  }

  /**
   * @Route("/isotopepackagingslipdhl/selectparcelshop/{selectedServicepointId}", name="isotopepackagingslipdhl_selectparcelshop")
   */
  public function selectParcelShop(string $selectedServicepointId=''): Response
  {
    System::loadLanguageFile('default');
    $template = new FrontendTemplate('isotopepackagingslipdhl_selectparcellshop');
    if ($this->container->hasParameter('krabo.isotope-packaging-slip-dhl.google_maps_api_key')) {
      $template->googleMapsApiKey = $this->container->getParameter('krabo.isotope-packaging-slip-dhl.google_maps_api_key');
    }
    $template->selectedServicepointId = $selectedServicepointId;
    return new Response($template->parse());
  }

}