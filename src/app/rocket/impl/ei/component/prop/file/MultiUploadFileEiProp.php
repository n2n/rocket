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
namespace rocket\impl\ei\component\prop\file;

use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\impl\ei\component\prop\string\StringEiProp;
use rocket\ei\component\EiSetupProcess;
use rocket\impl\ei\component\prop\file\command\MultiUploadEiCommand;
use n2n\util\config\Attributes;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use n2n\io\orm\FileEntityProperty;

class MultiUploadFileEiProp extends FileEiProp {
	
	const PROP_NAME_REFERENCED_NAME_PROPERTY_ID = 'referencedNamePropertyId';
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof FileEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('n2n\io\managed\File',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function setup(EiSetupProcess $setupProcess) {
		parent::setup($setupProcess);
		$command = new MultiUploadEiCommand(new Attributes());
		$command->setEiProp($this);
		$this->getEiType()->getEiCommandCollection()->add($command);
	}
	
	public function getReferencedNamePropertyId() {
		return $this->getAttributes()->get(self::PROP_NAME_REFERENCED_NAME_PROPERTY_ID);
	}
	
	public function createMagCollection() {
		$magCollection = parent::createMagCollection();
		$magCollection->addMag(self::PROP_NAME_REFERENCED_NAME_PROPERTY_ID, 
				new EnumMag('Referenced Name Property', $this->determineNamePropertyOptions()));
		return $magCollection;
	}
	
	private function determineNamePropertyOptions() {
		$options = array();
		foreach ($this->getEiType()->getEiPropCollection() as $field) {
			if (!($field instanceof StringEiProp)) continue;
			$options[$field->getId()] = $field->getPropertyName();  
		}
		return $options;
	}
}
