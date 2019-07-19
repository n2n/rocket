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
use rocket\si\structure\SiContent;
use n2n\util\type\ArgUtils;
use rocket\si\control\SiControl;

class SiPayloadFactory extends JsonPayload {
	
	/**
	 * @param SiContent $siZone
	 * @return \n2n\web\http\payload\impl\JsonPayload
	 */
	static function createFromContent(SiContent $content) {
		return new JsonPayload(self::createDataFromContent($content));
	}
	
	/**
	 * @param SiContent $content
	 * @return array
	 */
	static function createDataFromContent(SiContent $content) {
		return [
			'type' => $content->getTypeName(),
			'data' => $content->getData()
		];
	}
	
	/**
	 * @param array $contents
	 * @return array
	 */
	static function createDataFromContents(array $contents) {
		ArgUtils::valArray($contents, SiContent::class);
		
		$json = [];
		foreach ($contents as $key => $content) {
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