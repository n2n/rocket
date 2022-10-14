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
namespace rocket\attribute\impl;

use rocket\ei\manage\DefPropPath;
use n2n\util\type\ArgUtils;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EiPropEnum {

	/**
	 * @var DefPropPath[][]
	 */
	public readonly array $associatedDefPropPathMap;

	/**
	 * @param string[] $options e.g. ['small' => 'Small Article', 'other-value' => 'Other Label']
	 * @param bool|null $constant
	 * @param bool|null $readOnly
	 * @param bool|null $mandatory
	 * @param string|null $emptyLabel
	 * @param array $guiPropsMap e.g. ['small' => [ 'mode' ], 'other-value' => ['additionalInfo', 'otherProperty']]
	 */
	function __construct(public readonly array $options,
			public ?bool $constant = null, public ?bool $readOnly = null, public ?bool $mandatory = null,
			public ?string $emptyLabel = null, array $guiPropsMap = []) {
		ArgUtils::valArray($this->options, 'string');
		ArgUtils::valArray($guiPropsMap, 'array');
		$this->associatedDefPropPathMap = array_map(fn ($arr) => DefPropPath::buildArray($arr), $guiPropsMap);
	}
}