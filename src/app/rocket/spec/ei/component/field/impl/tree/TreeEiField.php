// <?php
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
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\spec\ei\component\field\impl\tree;

// use n2n\impl\persistence\orm\property\ScalarEntityProperty;
// use n2n\persistence\orm\property\EntityProperty;
// use rocket\spec\ei\component\field\impl\adapter\ConfEntityPropertyEiFieldAdapter;
// use rocket\spec\ei\component\field\FilterableEiField;
// use n2n\core\container\N2nContext;
// use rocket\spec\ei\manage\EiFrame;
// use rocket\spec\ei\manage\critmod\filter\impl\field\StringFilterField;
// use rocket\spec\ei\manage\critmod\filter\impl\field\FilterFieldAdapter;
// use rocket\spec\ei\manage\mapping\Mappable;
// use rocket\spec\ei\manage\critmod\filter\FilterField;

// abstract class TreeEiField extends ConfEntityPropertyEiFieldAdapter implements FilterableEiField {
// 	public function isCompatibleWith(EntityProperty $entityProperty) {
// 		return $entityProperty instanceof ScalarEntityProperty;
// 	}
		
// 	public function buildManagedFilterField(EiFrame $eiFrame) {
// 		return $this->createGlobalFilterFields($eiFrame->getN2nContext());
// 	}
	
// 	public function buildFilterField(N2nContext $n2nContext) {
// 		return new StringFilterField($this->getEntityProperty()->getName(), $this->getLabelCode(),
// 				FilterFieldAdapter::createOperatorOptions($n2nContext->getN2nLocale()));
// 	}
	
// 	public function buildMappable(EiObject $eiObject) {
// 		return null;
// 	}
	
// 	public function buildMappableFork(EiObject $eiObject, Mappable $mappable = null) {
// 		return null;
// 	}
	
// 	public function getGuiField() {
// 		return null;
// 	}
	
// 	public function getGuiFieldFork() {
// 		return null;
// 	}
// }
