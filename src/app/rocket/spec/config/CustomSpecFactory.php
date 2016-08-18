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
namespace rocket\spec\config;

use rocket\spec\config\extr\CustomSpecExtraction;
use n2n\core\TypeNotFoundException;
use n2n\reflection\ReflectionUtils;

class CustomSpecFactory {
	/**
	 * @param CustomSpecExtraction $customSpecExtraction
	 * @return \rocket\spec\custom\CustomSpec
	 * @throws InvalidSpecConfigurationException
	 */
	public static function create(CustomSpecExtraction $customSpecExtraction): CustomSpec {
		$constrollerClass = null;
		try {
			$controllerClass = ReflectionUtils::createReflectionClass($customSpecExtraction->getControllerClassName());
		} catch (TypeNotFoundException $e) {
			throw $this->createControllerException($customSpecExtraction, null, $e);
		}
		
		if (!$controllerClass->implementsInterface('n2n\web\http\controller\Controller')) {
			throw self::createControllerException($customSpecExtraction, $constrollerClass->getName()
					. ' must implement n2n\web\http\controller\Controller');
		}
		
		return new CustomSpec($customSpecExtraction->getId(), $customSpecExtraction->getModuleNamespace(), $controllerClass);
	}
	
	private static function createControllerException(CustomSpecExtraction $customSpecExtraction, string $reason = null, 
			\Exception $e = null): \Exception {
		return new InvalidSpecConfigurationException('Invalid Controller defined for ' 
				. $customSpecExtraction->toSpecString() . ($reason !== null ? ' Reason: ' . $reason : ''), 0, $e);
	}
}
