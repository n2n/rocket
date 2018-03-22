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

use rocket\impl\ei\component\prop\file\FileEiProp;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\LenientAttributeReader;
use rocket\impl\ei\component\prop\enum\EnumEiProp;

class MultiUploadFileEiPropConfigurator extends FileEiPropConfigurator {
	const ATTR_AUTO_NAME_PROP = 'autoNameProp';
	
	private $fileEiProp;
	
	public function __construct(FileEiProp $fileEiProp) {
		parent::__construct($fileEiProp);
	
		$this->fileEiProp = $fileEiProp;
		$this->autoRegister();
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magForm = parent::createMagDispatchable($n2nContext);
		$magCollection = $magForm->getMagCollection();

		$lar = new LenientAttributeReader($this->attributes);
		$eiu = $this->eiu($n2nContext);
		
		$options = $eiu->engine()->getGenericEiPropertyOptions();
		$magCollection->addMag(self::ATTR_AUTO_NAME_PROP,
				new EnumEiProp('Auto name prop', $options, $lar->get(self::ATTR_AUTO_NAME_PROP)));
		
		return $magForm;
	}
	
// 	public function initAutoEiPropAttributes(Column $column = null) {
// 		parent::initAutoEiPropAttributes($column);
		
// 		if (false !== stripos($this->requireEntityProperty()->getName(), 'image')) {
// 			$this->attributes->set(self::ATTR_ALLOWED_EXTENSIONS_KEY, array('png', 'jpg', 'jpeg', 'gif'));
// 			$this->attributes->set(self::ATTR_DIMENSION_IMPORT_MODE_KEY, FileEiProp::DIM_IMPORT_MODE_ALL);
// 		}
// 	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$this->attributes->appendAll($magDispatchable->getMagCollection()
				->readValues(array(self::ATTR_AUTO_NAME_PROP), true), true);
	}
}