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
namespace rocket\si;

use n2n\web\http\payload\impl\JsonPayload;
use rocket\si\content\SiComp;
use n2n\util\type\ArgUtils;
use rocket\si\control\SiControl;

class SiPayloadFactory extends JsonPayload {
	
	/**
	 * @param SiComp $siZone
	 * @return \n2n\web\http\payload\impl\JsonPayload
	 */
	static function createFromContent(SiComp $content) {
		return new JsonPayload(self::createDataFromContent($content));
	}
	
	/**
	 * @param SiComp $content
	 * @return array
	 */
	static function createDataFromContent(SiComp $content) {
		return [
			'title' => 'Some Title',
			'breadcrumbs' => [],
			'comp' => [
				'type' => $content->getTypeName(),
				'data' => $content->getData()
			],
			'controls' => []
		];
	}
	
	/**
	 * @param array $comps
	 * @return array
	 */
	static function createDataFromContents(array $comps) {
		ArgUtils::valArray($comps, SiComp::class);
		
		$json = [];
		foreach ($comps as $key => $content) {
			$json[$key] = self::createDataFromContent($content);
		}
		return $json;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return array
	 */
	static function createDataFromControls(array $controls) {
		ArgUtils::valArray($controls, SiControl::class);
		
		$controlsArr = array();
		foreach ($controls as $id => $control) {
			$controlsArr[$id] = [
				'type' => $control->getType(),
				'data' => $control->getData()
			];
		}
		return $controlsArr;
	}
	
}