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
namespace rocket\attribute;

use rocket\ei\mask\model\DisplayStructure;
use rocket\ei\manage\DefPropPath;
use rocket\si\meta\SiStructureType;
use n2n\util\StringUtils;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EiDisplayScheme {

	public ?DisplayStructure $compactDisplayStructure;
	public ?DisplayStructure $bulkyDisplayStructure;
	public ?DisplayStructure $bulkyDetailDisplayStructure;
	public ?DisplayStructure $bulkyAddDisplayStructure;
	public ?DisplayStructure $bulkyEditDisplayStructure;

	function __construct(
			array $compact = null, array $compactDetail = null, array $compactAdd = null, array $compactEdit = null,
			array $bulky = null, array $bulkyDetail = null, array $bulkyAdd = null, array $bulkyEdit = null) {
		$this->compactDisplayStructure = $this->parseDisplayStructure($compact);
		$this->bulkyDisplayStructure = $this->parseDisplayStructure($bulky);
		$this->bulkyDetailDisplayStructure = $this->parseDisplayStructure($bulkyDetail);
		$this->bulkyAddDisplayStructure = $this->parseDisplayStructure($bulkyAdd);
		$this->bulkyEditDisplayStructure = $this->parseDisplayStructure($bulkyEdit);
	}

	/**
	 * @param array|null $items
	 * @return DisplayStructure|null
	 */
	private function parseDisplayStructure(?array $items) {
		if ($items === null) {
			return null;
		}

		$diplsayStructure = new DisplayStructure();

		foreach ($items as $key => $value) {
			if (is_array($value)) {
				$parseResult = $this->parseTypeLabel($key, SiStructureType::SIMPLE_GROUP);
				$diplsayStructure->addDisplayStructure($this->parseDisplayStructure($value),
						$parseResult['type'], $parseResult['label']);
				continue;
			}

			if (is_int($key)) {
				$diplsayStructure->addDefPropPath(DefPropPath::create($value), SiStructureType::ITEM);
				continue;
			}

			$parseResult = $this->parseTypeLabel(StringUtils::strOf($value), SiStructureType::ITEM);
			$diplsayStructure->addDefPropPath(DefPropPath::create($key), $parseResult['type'], $parseResult['label']);
		}

		return $diplsayStructure;
	}

	private function parseTypeLabel(string|int $expr, string $default) {
		if (is_int($expr)) {
			return ['label' => null, 'type' => $default];
		}

		if (!preg_match('/^([^:]+)\s*:\s*(.*)$/', $expr, $matches)
				|| !in_array($matches[1], SiStructureType::all())) {
			return ['label' => $expr, 'type' => $default];
		}

		return ['label' => $matches[2] === '' ? null : $matches[2], 'type' => $matches[1]];
	}
}