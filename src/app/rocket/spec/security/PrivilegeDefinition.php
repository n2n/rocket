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
namespace rocket\spec\security;

use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\EiFieldPath;
use n2n\util\config\Attributes;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\config\AttributesException;

class PrivilegeDefinition {
	private $eiCommandPrivileges = array();
	
	public function isEmpty(): bool {
		return $this->eiCommandPrivileges;
	}
	
	public function checkEiCommandPathForPrivileges(EiCommandPath $eiCommandPath) {
		foreach ($this->eiCommandPrivileges as $privilegeEiCommandPathStr => $eiCommandPrivilege) {
			$privilegeEiCommandPath = EiCommandPath::create($privilegeEiCommandPathStr);
			
			if ($privilegeEiCommandPath->startsWith($eiCommandPath) 
					|| $eiCommandPath->startsWith($privilegeEiCommandPath)) {
				return true;
			}
		}
		
		return false;
	}
	
	public function putEiCommandPrivilege(EiCommandPath $commandPath, EiCommandPrivilege $eiCommandPrivilege) {
		$this->eiCommandPrivileges[(string) $commandPath] = $eiCommandPrivilege;
	}
	
	public function getEiCommandPrivileges(): array {
		return $this->eiCommandPrivileges;
	}
	
	private $eiFieldPrivileges = array();
	
	public function getEiFieldPrivileges(): array {
		return $this->eiFieldPrivileges;
	}
	
	public function putEiFieldPrivilege(EiFieldPath $eiFieldPath, EiFieldPrivilege $eiFieldPrivilege) {
		$this->eiFieldPrivileges[(string) $eiFieldPath] = $eiFieldPrivilege;
	}
	
	public function getEiFieldPrivilegeByEiFieldPath(EiFieldPath $eiFieldPath): EiFieldPrivilege {
		$eiFieldPathStr = (string) $eiFieldPath;
		if (isset($this->eiFieldPrivileges[$eiFieldPath])) {
			return $this->eiFieldPrivileges[$eiFieldPath];
		}
	
		throw new UnknownEiFieldPrivilegeException();
	}
	
	public function createEiFieldPrivilegeMagCollection(Attributes $attributes): MagCollection {
		$magCollection = new MagCollection();
		foreach ($this->eiFieldPrivileges as $eiFieldPathStr => $eiFieldPrivilege) {
			$itemAttributes = null;
			try {
				$itemAttributes = new Attributes($attributes->getArray($eiFieldPathStr, false));
			} catch (AttributesException $e) {
				$itemAttributes = new Attributes();
			}
				
			$magCollection->addMag($eiFieldPrivilege->createMag($eiFieldPathStr, $itemAttributes));
		}
		return $magCollection;
	}
	
	public function buildEiFieldPrivilegeAttributes(MagCollection $magCollection) {
		$attributes = new Attributes();
		
		foreach ($this->eiFieldPrivileges as $eiFieldPathStr => $eiFieldPrivilege) {
			if (!$magCollection->containsPropertyName($eiFieldPathStr)) continue;
			
			$attributes->set($eiFieldPathStr, $eiFieldPrivilege->buildAttributes(
					$magCollection->getMagByPropertyName($eiFieldPathStr))->toArray());
		}

		return $attributes;
	}
	
	public static function extractAttributesOfEiFieldPrivilege(EiFieldPath $eiFieldPath, 
			Attributes $eiFieldPrivilegeAttributes) {
		
		$eiFieldPathStr = (string) $eiFieldPath;
				
		if (!$eiFieldPrivilegeAttributes->contains($eiFieldPathStr)) return null;
		
		$attrs = $eiFieldPrivilegeAttributes->get($eiFieldPathStr);
		if (is_array($attrs)) return new Attributes($attrs);
		
		return null;
	}
}
