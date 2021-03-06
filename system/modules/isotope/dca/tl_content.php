<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Add fields to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['iso_reader_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_reader_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio'),
);

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_list_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_list_layout'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'		  => array('tl_content_isotope', 'getListTemplates'),
	'eval'					  => array('includeBlankOption'=>true),
);


/**
 * Class tl_content_isotope
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_content_isotope extends Backend
{

	/**
	 * Get the filters and return them as array
	 * @param DataContainer
	 * @return array
	 */
	public function getFilters(DataContainer $dc)
	{
		$objAttributeSet = $this->Database->prepare("SELECT iso_attribute_set FROM tl_content WHERE id=?")
										  ->limit(1)
										  ->execute($dc->id);

		if ($objAttributeSet->numRows < 1)
		{
			return '';
		}

		$intAttributeSet = $objAttributeSet->iso_attribute_set;

		$objFilters = $this->Database->prepare("SELECT id, name FROM tl_iso_attributes WHERE fe_filter=? AND pid=?")
									 ->execute(1, (int) $intAttributeSet);

		if ($objFilters->numRows < 1)
		{
			return array();
		}

		$arrFilters = $objFilters->fetchAllAssoc();

		foreach ($arrFilters as $filter)
		{
			$arrFilterList[$filter['id']] = $filter['name'];
		}

		return $arrFilterList;
	}


	/**
	 * Return all list templates as array
	 * @param DataContainer
	 * @return array
	 */
	public function getListTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		// Get the page ID
		$objArticle = $this->Database->prepare("SELECT pid FROM tl_article WHERE id=?")
									 ->limit(1)
									 ->execute($intPid);

		// Inherit the page settings
		$objPage = $this->getPageDetails($objArticle->pid);

		// Get the theme ID
		$objLayout = $this->Database->prepare("SELECT pid FROM tl_layout WHERE id=?")
									->limit(1)
									->execute($objPage->layout);

		// Return all templates
		return $this->getTemplateGroup('iso_list_', $objLayout->pid);
	}
}

