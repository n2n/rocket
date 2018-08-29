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
namespace rocket\ei\manage\critmod\filter\data;

use n2n\util\config\Attributes;

class FilterPropSetting {
	const ATTR_ITEM_ID_KEY = 'itemId';
	const ATTR_ATTRS_KEY = 'attrs';

	private $filterFieldId;
	private $attributes;

	public function __construct(string $filterFieldId = null, Attributes $attributes) {
		$this->filterFieldId = $filterFieldId;
		$this->attributes = $attributes;
	}

	public function setFilterFieldId(string $filterFieldId) {
		$this->filterFieldId = $filterFieldId;
	}

	public function getFilterFieldId() {
		return $this->filterFieldId;
	}

	public function setAttributes(Attributes $attributes) {
		$this->attributes = $attributes;
	}

	public function getAttributes(): Attributes {
		return $this->attributes;
	}

	public function toAttrs(): array {
		return array(
				self::ATTR_ITEM_ID_KEY => $this->filterFieldId,
				self::ATTR_ATTRS_KEY => $this->attributes->toArray());
	}

	public static function create(Attributes $attributes): FilterPropSetting {
		return new FilterPropSetting($attributes->getString(self::ATTR_ITEM_ID_KEY),
				new Attributes($attributes->getArray(self::ATTR_ATTRS_KEY, false, array())));
	}
}
