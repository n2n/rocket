<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\component\field\impl\ci\conf;

use rocket\spec\ei\EiSpec;
use rocket\core\model\Rocket;
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\field\impl\ci\model\ContentItem;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\util\config\LenientAttributeReader;
use n2n\util\config\Attributes;
use n2n\reflection\property\TypeConstraint;
use rocket\spec\ei\component\field\impl\ci\model\PanelConfig;
use n2n\util\config\AttributesException;
use n2n\util\StringUtils;

class CiConfigUtils {
	const ATTR_PANEL_NAME_KEY = 'panelName';
	const ATTR_PANEL_LABEL_KEY = 'panelLabel';
	const ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY = 'allowedContentItemIds';
	const ATTR_MIN_KEY = 'min';
	const ATTR_MAX_KEY = 'max';
	
	private $ciEiSpec;
	private $allowedContentItemOptions;

	public function __construct(EiSpec $ciEiSpec) {
		$this->ciEiSpec = $ciEiSpec;
	}

	public static function createFromN2nContext(N2nContext $n2nContext) {
		return new CiConfigUtils($n2nContext->lookup(Rocket::class)->getSpecManager()
				->getEiSpecByClass(ContentItem::getClass()));
	}

	public function getAllowedContentItemOptions() {
		if ($this->allowedContentItemOptions !== null) {
			return $this->allowedContentItemOptions;
		}

		$this->allowedContentItemOptions = array();
		foreach ($this->ciEiSpec->getAllSubEiSpecs() as $subEiSpec) {
			$this->allowedContentItemOptions[$subEiSpec->getId()] = $subEiSpec->getEiMaskCollection()
					->getOrCreateDefault()->getLabelLstr();
		}

		return $this->allowedContentItemOptions;
	}

	public function createPanelConfigMagCollection(bool $includePanelName) {
		$allowedContentItemOptions = $this->getAllowedContentItemOptions();
		
		$magCollection = new MagCollection();
		if ($includePanelName) {
			$magCollection->addMag(new StringMag(self::ATTR_PANEL_NAME_KEY, 'Name', null, true));
		}
		$magCollection->addMag(new StringMag(self::ATTR_PANEL_LABEL_KEY, 'Label', null, false));
		$magCollection->addMag(new MultiSelectMag(self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY,
				'Allowed ContentItems', $this->getAllowedContentItemOptions()));
		$magCollection->addMag(new NumericMag(self::ATTR_MIN_KEY, 'Min', 0));
		$magCollection->addMag(new NumericMag(self::ATTR_MAX_KEY, 'Max'));
		return $magCollection;
	}
	
	public function buildPanelConfigMagCollectionValues(array $panelConfigAttrs) {
		$lar = new LenientAttributeReader(new Attributes($panelConfigAttrs));
		return array(
				self::ATTR_PANEL_NAME_KEY => $lar->getString(self::ATTR_PANEL_NAME_KEY),
				self::ATTR_PANEL_LABEL_KEY => $lar->getString(self::ATTR_PANEL_LABEL_KEY),
				self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY => $lar->getArray(
						self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY, null,
						TypeConstraint::createSimple('string'), true),
				self::ATTR_MIN_KEY => $lar->getInt(self::ATTR_MIN_KEY, false, 0),
				self::ATTR_MAX_KEY => $lar->getInt(self::ATTR_MAX_KEY, false));
	}
	
	public static function buildPanelConfigAttrs(array $panelConfigMagCollectionValues) {
		if (empty($panelConfigMagCollectionValues[self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY])) {
			unset($panelConfigMagCollectionValues[self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY]);
		}
			
		if (!isset($panelConfigMagCollectionValues[self::ATTR_MAX_KEY])) {
			unset($panelConfigMagCollectionValues[self::ATTR_MAX_KEY]);
		}
		
		return $panelConfigMagCollectionValues;
	}
	
	/**
	 * @param array $panelAttrs
	 * @throws AttributesException
	 * @return \rocket\spec\ei\component\field\impl\ci\model\PanelConfig
	 */
	public static function createPanelConfig(array $panelAttrs, string $panelName = null) {
		$panelAttributes = new Attributes($panelAttrs);
		
		if ($panelName === null) {
			$panelName = $panelAttributes->getString(self::ATTR_PANEL_NAME_KEY);
		}
		
		$panelLabel = null;
		if (null === ($panelLabel = $panelAttributes->getString(self::ATTR_PANEL_LABEL_KEY, false, null, true))) {
			$panelLabel = StringUtils::pretty($panelName);
		}
		
		$allowedCiIds = $panelAttributes->getArray(self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY, false, null,
				TypeConstraint::createSimple('string'), true);
		
		return new PanelConfig($panelName, $panelLabel,
				empty($allowedCiIds) ? null : $allowedCiIds,
				$panelAttributes->getInt(self::ATTR_MIN_KEY, false, 0),
				$panelAttributes->getInt(self::ATTR_MAX_KEY, false, null, true));
	}
}
