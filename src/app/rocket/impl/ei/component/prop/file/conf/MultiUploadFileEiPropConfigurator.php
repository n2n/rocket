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
namespace rocket\impl\ei\component\prop\file\conf;

use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\LenientAttributeReader;
use rocket\impl\ei\component\prop\file\MultiUploadFileEiProp;
use rocket\ei\component\EiSetup;
use rocket\impl\ei\component\prop\file\command\MultiUploadEiCommand;
use rocket\ei\EiPropPath;
use n2n\util\config\AttributesException;
use n2n\impl\web\dispatch\mag\model\EnumMag;

class MultiUploadFileEiPropConfigurator extends FileEiPropConfigurator {
	const ATTR_AUTO_NAME_PROP = 'autoNameProp';
	
	/**
	 * @var MultiUploadFileEiProp
	 */
	private $multiUploadFileEiProp;
	
	/**
	 * @param MultiUploadFileEiProp $multiUploadFileEiProp
	 */
	public function __construct(MultiUploadFileEiProp $multiUploadFileEiProp) {
		parent::__construct($multiUploadFileEiProp);
	
		$this->multiUploadFileEiProp = $multiUploadFileEiProp;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\file\conf\FileEiPropConfigurator::createMagDispatchable()
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magForm = parent::createMagDispatchable($n2nContext);
		$magCollection = $magForm->getMagCollection();

		$lar = new LenientAttributeReader($this->attributes);
		$eiu = $this->eiu($n2nContext);
		
		if (!$eiu->mask()->isEngineReady()) {
			return $magForm;
		}
		
		$options = $eiu->engine()->getGenericEiPropertyOptions();
		$magCollection->addMag(self::ATTR_AUTO_NAME_PROP,
				new EnumMag('Auto name prop', $options, $lar->getScalar(self::ATTR_AUTO_NAME_PROP)));
		
		return $magForm;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\file\conf\FileEiPropConfigurator::setup()
	 */
	public function setup(EiSetup $eiSetup) {
		$eiSetup->eiu()->mask()->addEiCommand(new MultiUploadEiCommand($this->multiUploadFileEiProp));
		
		if (null !== ($eiPropPathStr = $this->attributes->getString(self::ATTR_AUTO_NAME_PROP, false))) {
			try {
				$this->multiUploadFileEiProp->setAutoNameEiPropPath(EiPropPath::create($eiPropPathStr));
			} catch (\InvalidArgumentException $e) {
				throw $eiSetup->createException('Inavlid property ' . self::ATTR_AUTO_NAME_PROP);
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\file\conf\FileEiPropConfigurator::saveMagDispatchable()
	 */
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		$curEiPropPath = null;
		try {
			if (null !== ($val = $this->attributes->getString(self::ATTR_AUTO_NAME_PROP, false))) {
				$curEiPropPath = EiPropPath::create($val);
			}
		} catch (AttributesException $e) {
		} catch (\InvalidArgumentException $e) {
		}
		
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		if ($magDispatchable->getMagCollection()->containsPropertyName(self::ATTR_AUTO_NAME_PROP)) {
			$this->attributes->appendAll($magDispatchable->getMagCollection()
					->readValues(array(self::ATTR_AUTO_NAME_PROP), true), true);
		} else if ($curEiPropPath !== null) {
			$this->attributes->set(self::ATTR_AUTO_NAME_PROP, (string) $curEiPropPath);
		}
	}
}