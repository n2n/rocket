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
namespace rocket\user\model;

use n2n\web\dispatch\Dispatchable;
use rocket\user\bo\EiPrivilegeGrant;
use n2n\reflection\annotation\AnnoInit;
use n2n\impl\web\dispatch\map\val\ValEnum;
use rocket\spec\security\PrivilegeDefinition;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\web\dispatch\annotation\AnnoDispObject;
use rocket\ei\manage\critmod\filter\EiEntryFilterDefinition;
use rocket\ei\manage\critmod\filter\impl\form\FilterGroupForm;
use n2n\web\dispatch\map\bind\BindingDefinition;
use rocket\ei\EiCommandPath;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\Attributes;

class EiPrivilegeGrantForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('eiCommandPathStrs', 'eiPropPrivilegeMagForm'));
		$ai->p('restrictionFilterGroupForm', new AnnoDispObject(function (EiPrivilegeGrantForm $that) {
			return new FilterGroupForm($that->eiPrivilegesGrant->readRestrictionFilterPropSettingGroup(), 
					$that->restrictionFilterDefinition);
		}));
	}
	
	private $eiPrivilegesGrant;
	private $privilegeDefinition;
	private $restrictionFilterDefinition;
	
	private $eiPropPrivilegeMagForm;
	private $restrictionFilterGroupForm; 
	
	public function __construct(EiPrivilegeGrant $eiPrivilegeGrant, PrivilegeDefinition $privilegeDefinition,
			EiEntryFilterDefinition $restrictionFilterDefinition) {
		$this->eiPrivilegesGrant = $eiPrivilegeGrant;
		$this->privilegeDefinition = $privilegeDefinition;

		$magCollection = $privilegeDefinition->createEiPropPrivilegeMagCollection(
				$eiPrivilegeGrant->readEiPropPrivilegeAttributes());
		if (!$magCollection->isEmpty()) {
			$this->eiPropPrivilegeMagForm = new MagForm($magCollection);
		}
		
		$this->restrictionFilterDefinition = $restrictionFilterDefinition;
		if ($eiPrivilegeGrant->isRestricted()) {
			$this->restrictionFilterGroupForm = new FilterGroupForm(
					$eiPrivilegeGrant->readRestrictionFilterPropSettingGroup(), $this->restrictionFilterDefinition);
		}
	}
	
	public function getEiPrivilegeGrant() {
		return $this->eiPrivilegesGrant;
	}
	
	public function getEiCommandPathStrs() {
		$commandPathStrs = $this->eiPrivilegesGrant->getEiCommandPathStrs();
		return array_combine($commandPathStrs, $commandPathStrs);
	}
	
	public function setEiCommandPathStrs(array $eiCommandPathStrs) {
		$this->eiPrivilegesGrant->setEiCommandPathStrs(array_values($eiCommandPathStrs));
	}
	
	public function isEiPropPrivilegeMagFormAvailable(): bool {
		return $this->eiPropPrivilegeMagForm !== null;
	}
	
	public function getEiPropPrivilegeMagForm() {
		return $this->eiPropPrivilegeMagForm;
	}
	
	public function setEiPropPrivilegeMagForm(MagDispatchable $eiPropPrivilegeMagForm = null) {
		$this->eiPropPrivilegeMagForm = $eiPropPrivilegeMagForm;
	
		if ($eiPropPrivilegeMagForm === null) {
			$this->eiPrivilegesGrant->writeEiPropPrivilegeAttributes(new Attributes());
			return;
		}
		
		$this->eiPrivilegesGrant->writeEiPropPrivilegeAttributes(
				$this->privilegeDefinition->buildEiPropPrivilegeAttributes(
						$eiPropPrivilegeMagForm->getMagCollection()));
		
	}
	
	public function isRestricted(): bool {
		return $this->eiPrivilegesGrant->isRestricted();
	}
	
	public function setRestricted(bool $restricted) {
		$this->eiPrivilegesGrant->setRestricted($restricted && $this->areRestrictionsAvailable());
	}
	
	public function getRestrictionFilterGroupForm() {
		return $this->restrictionFilterGroupForm;
	}
	
	public function setRestrictionFilterGroupForm(FilterGroupForm $restrictionFilterGroupForm = null) {
		$this->restrictionFilterGroupForm = $restrictionFilterGroupForm;

		if ($restrictionFilterGroupForm === null) {
			$this->eiPrivilegesGrant->setRestricted(false);
		} else {
			$this->eiPrivilegesGrant->setRestricted(true);
			$this->eiPrivilegesGrant->writeRestrictionFilterData($restrictionFilterGroupForm->buildFilterPropSettingGroup());
		}
	}
	
	private function buildPrivileges(array &$privileges, array $eiCommandPrivileges, EiCommandPath $baseEiCommandPath)  {
		foreach ($eiCommandPrivileges as $commandPathStr => $eiCommandPrivilege) {
			$commandPath = $baseEiCommandPath->ext($commandPathStr);
			
			$privileges[] = (string) $commandPath;
				
			$this->buildPrivileges($privileges, $eiCommandPrivilege->getSubEiCommandPrivileges(), $commandPath);
		}
	}
	
	private function _validation(BindingDefinition $bd) {
		$commandPathStrs = array();
		$this->buildPrivileges($commandPathStrs, $this->privilegeDefinition->getEiCommandPrivileges(), 
				new EiCommandPath(array()));
		$bd->val('eiCommandPathStrs', new ValEnum($commandPathStrs));
	}
}
