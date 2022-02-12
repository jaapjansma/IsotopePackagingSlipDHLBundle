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

use \Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_isotope_packaging_slip']['config']['onload_callback'][] = function(\Contao\DataContainer $dc) {
  if (Input::post('FORM_SUBMIT') == 'tl_select') {
    if (isset($_POST['printDhlLabels']))
    {
      $dc->redirect(str_replace('act=select', 'key=print_dhl_labels', Environment::get('request')));
    }
  }
};
$GLOBALS['TL_DCA']['tl_isotope_packaging_slip']['select']['buttons_callback'][] = function($arrButtons, \Contao\DataContainer $dc) {
  $arrButtons['printDhlLabels'] = '<button type="submit" name="printDhlLabels" id="printDhlLabels" class="tl_submit">' . $GLOBALS['TL_LANG']['tl_isotope_packaging_slip']['print_dhl_label'][0] . '</button>';
  return $arrButtons;
};

$GLOBALS['TL_DCA']['tl_isotope_packaging_slip']['list']['operations']['print_dhl_label'] = [
  'label'             => &$GLOBALS['TL_LANG']['tl_isotope_packaging_slip']['print_dhl_label'],
  'href'              => 'key=print_dhl_label',
  'icon'              => 'bundles/isotopepackagingslipdhl/dhl.png',
  'button_callback'   => function($row, $href, $label, $title, $icon, $attributes) {
    if (!empty($row['dhl_id'])) {
      return '<a href="' . \Contao\Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . Contao\Image::getHtml($icon, $label) . '</a> ';
    }
    return '';
  }
];

$GLOBALS['TL_DCA']['tl_isotope_packaging_slip']['fields']['dhl_id'] = [
  'search'                  => true,
  'inputType'               => 'text',
  'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
  'sql'                     => "varchar(255) NOT NULL default ''"
];
$GLOBALS['TL_DCA']['tl_isotope_packaging_slip']['fields']['dhl_tracker_code'] = [
  'search'                  => true,
  'inputType'               => 'text',
  'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50'),
  'sql'                     => "varchar(255) NOT NULL default ''"
];
$GLOBALS['TL_DCA']['tl_isotope_packaging_slip']['fields']['dhl_servicepoint_id'] = [
  'exclude'               => true,
  'search'                => true,
  'inputType'             => 'text',
  'eval'                  => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
  'sql'                   => "varchar(255) NOT NULL default ''",
];

PaletteManipulator::create()
  ->addLegend('dhl_legend', 'notes', PaletteManipulator::POSITION_AFTER)
  ->addField('dhl_id', 'dhl_legend', PaletteManipulator::POSITION_APPEND)
  ->addField('dhl_tracker_code', 'dhl_legend', PaletteManipulator::POSITION_APPEND)
  ->addField('dhl_servicepoint_id', 'dhl_legend', PaletteManipulator::POSITION_APPEND)
  ->applyToPalette('default', 'tl_isotope_packaging_slip');