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
namespace rocket\spec\ei\manage\generic;

use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\item\CriteriaItem;
use n2n\l10n\Lstr;
use rocket\spec\ei\EiFieldPath;

interface GenericEiProperty {
	
	/**
	 * @return Lstr
	 */
	public function getLabelLstr(): Lstr;
	
	/**
	 * @return EiFieldPath
	 */
	public function getEiFieldPath(): EiFieldPath;
	
	/**
	 * @param CriteriaProperty $alias
	 * @return CriteriaItem
	 */
	public function buildCriteriaItem(CriteriaProperty $alias): CriteriaItem;
	
// 	/**
// 	 * @param EiMapping $eiMapping
// 	 * @return mixed
// 	 */
// 	public function buildEntityValue(EiMapping $eiMapping);

	/**
	 * @param mixed $mappableValue
	 * @return mixed
	 */
	public function mappableValueToEntityValue($mappableValue);
	
	/**
	 * @param mixed $entityValue
	 * @return mixed
	 */
	public function entityValueToMappableValue($entityValue);
}
