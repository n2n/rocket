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
use rocket\user\bo\EiGrantPrivilege;
use n2n\reflection\annotation\AnnoInit;
use n2n\impl\web\dispatch\map\val\ValEnum;
use rocket\spec\security\PrivilegeDefinition;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\web\dispatch\annotation\AnnoDispObject;
use rocket\ei\manage\security\filter\SecurityFilterDefinition;
use rocket\ei\util\filter\form\FilterGroupForm;
use n2n\web\dispatch\map\bind\BindingDefinition;
use rocket\ei\EiCommandPath;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\Attributes;
use rocket\ei\manage\critmod\filter\FilterDefinition;

class EiGrantPrivilegeForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('eiCommandPathStrs', 'eiPropPrivilegeMagForm'));
		$ai->p('restrictionFilterGroupForm', new AnnoDispObject(function (EiGrantPrivilegeForm $that) {
			return new FilterGroupForm($that->eiPrivilegesGrant->readRestrictionFilterPropSettingGroup(), 
					$that->restrictionFilterDefinition);
		}));
	}
	
// 	private $eiuEngine;
	/**
	 * @var EiGrantPrivilege
	 */
	private $eiPrivilegesGrant;
	/**
	 * @var PrivilegeDefinition
	 */
	private $privilegeDefinition;
	/**
	 * @var SecurityFilterDefinition
	 */
	private $restrictionFilterDefinition;
	
	private $eiPropPrivilegeMagForm;
	private $restrictionFilterGroupForm; 
	
	public function __construct(EiGrantPrivilege $eiGrantPrivilege, PrivilegeDefinition $privilegeDefinition,
			FilterDefinition $restrictionFilterDefinition) {
// 		$this->eiuEngine = $eiuEngine;
		$this->eiPrivilegesGrant = $eiGrantPrivilege;
		$this->privilegeDefinition = $privilegeDefinition;
		
		$magCollection = $privilegeDefinition->createEiPropPrivilegeMagCollection(
				$eiGrantPrivilege->readEiPropPrivilegeAttributes());
		if (!$magCollection->isEmpty()) {
			$this->eiPropPrivilegeMagForm = new MagForm($magCollection);
		}
		
		$this->restrictionFilterDefinition = $restrictionFilterDefinition;
		if ($eiGrantPrivilege->isRestricted()) {
			$this->restrictionFilterGroupForm = new FilterGroupForm(
					$eiGrantPrivilege->readRestrictionFilterPropSettingGroup(), 
					$this->restrictionFilterDefinition);
		}
	}
	
	public function getEiGrantPrivilege() {
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
