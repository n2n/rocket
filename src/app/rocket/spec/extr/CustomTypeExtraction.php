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
namespace rocket\spec\extr;

use rocket\custom\CustomType;

class CustomTypeExtraction extends TypeExtraction {
	private $controllerClassName;
	
	public function getControllerClassName() {
		return $this->controllerClassName;
	}

	public function setControllerClassName($customControllerClassName) {
		$this->controllerClassName = $customControllerClassName;
	}
	
// 	public function createScript(Spec $spec) {
// 		$constrollerClass = null;
// 		try {
// 			$controllerClass = ReflectionUtils::createReflectionClass(
// 					$this->getControllerClassName());
// 			if (!$controllerClass->implementsInterface('n2n\web\http\controller\Controller')) {
// 				throw Spec::createInvalidSpecConfigurationException($this->getId(), null, 
// 						$constrollerClass->getName() . ' must implement n2n\web\http\controller\Controller');
// 			}
// 		} catch (TypeNotFoundException $e) {
// 			throw Spec::createInvalidSpecConfigurationException($this->getId(), $e);
// 		}
	
// 		return new CustomType($this->getId(), $this->getModuleNamespace(), $controllerClass);
// 	}
	
	public function toTypeString(): string {
		return 'CustomType (id: ' . $this->getId() . ', module: ' . $this->getModuleNamespace() . ')';
	}
	
	public static function createFromCustomType(CustomType $script) {
		$extraction = new CustomTypeExtraction($script->getId(), $script->getModuleNamespace());
		$extraction->setLabel($script->getLabel());
		$extraction->setControllerClassName($script->getControllerClass()->getName());
		return $extraction;
	}
}
