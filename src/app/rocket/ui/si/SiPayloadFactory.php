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
namespace rocket\ui\si;

use n2n\web\http\payload\impl\JsonPayload;
use rocket\ui\si\content\SiGui;
use n2n\util\type\ArgUtils;
use rocket\ui\si\control\SiControl;
use rocket\ui\si\content\SiField;
use rocket\ui\si\meta\SiBreadcrumb;
use n2n\core\container\N2nContext;

class SiPayloadFactory extends JsonPayload {
	
	/**
	 * @param SiGui $comp
	 * @param SiControl[] $controls
	 * @return JsonPayload
	 */
	static function create(SiGui $comp, array $breadcrumbs, string $title, array $controls = []): JsonPayload {
		ArgUtils::valArray($breadcrumbs, SiBreadcrumb::class);
		ArgUtils::valArray($controls, SiControl::class);
		
		return new JsonPayload([
			'title' => $title,
			'breadcrumbs' => $breadcrumbs,
			'comp' => self::buildDataFromComp($comp),
			'controls' => self::createDataFromControls($controls)
		]);
	}

	/**
	 * @param SiGui|null $content
	 * @param N2nContext $n2nContext
	 * @return array|null
	 */
	static function buildDataFromComp(?SiGui $content, N2nContext $n2nContext): ?array {
		if ($content === null) {
			return null;
		}
		
		return [
			'type' => $content->getTypeName(),
			'data' => $content->toJsonStruct($n2nContext)
		];
	}
	
	/**
	 * @param array $comps
	 * @return array
	 */
	static function createDataFromContents(array $comps) {
		ArgUtils::valArray($comps, SiGui::class);
		
		$json = [];
		foreach ($comps as $key => $content) {
			$json[$key] = self::buildDataFromComp($content);
		}
		return $json;
	}

	/**
	 * @param SiField[] $fields
	 * @param N2nContext $n2nContext
	 * @return array
	 */
	static function createDataFromFields(array $fields, N2nContext $n2nContext): array {
		ArgUtils::valArray($fields, SiField::class);
		
		$fieldsArr = array();
		foreach ($fields as $key => $field) {
			$fieldsArr[$key] = [
				'type' => $field->getType(),
				'data' => $field->toJsonStruct($n2nContext)
			];
		}
		return $fieldsArr;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return array
	 */
	static function createDataFromControls(array $controls) {
		ArgUtils::valArray($controls, SiControl::class);
		
		$controlsArr = array();
		foreach ($controls as $control) {
			$controlsArr[] = [
				'type' => $control->getType(),
				'data' => $control->getData()
			];
		}
		return $controlsArr;
	}
	
}