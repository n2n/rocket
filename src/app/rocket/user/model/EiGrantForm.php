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

use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\Dispatchable;
use n2n\l10n\N2nLocale;
use rocket\user\bo\EiGrantPrivilege;
use rocket\user\bo\EiGrant;
use rocket\spec\security\PrivilegeDefinition;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\ei\manage\security\filter\SecurityFilterDefinition;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;

class EiGrantForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('fullAccess'));
		$ai->p('eiGrantPrivilegeForms', new AnnoDispObjectArray( 
				function (EiGrantForm $that) {
					return new EiGrantPrivilegeForm(new EiGrantPrivilege(), $that->privilegeDefinition,
							$that->securityFilterDefinition);
				}));
	}
	
	private $eiGrant;
	private $privilegeDefinition;
	private $securityFilterDefinition;
	
	private $accessDenyMagForm;
	private $eiGrantPrivilegeForms = array();
	
	public function __construct(EiGrant $eiGrant, PrivilegeDefinition $privilegeDefinition, 
			SecurityFilterDefinition $securityFilterDefinition) {
		$this->eiGrant = $eiGrant;
		$this->privilegeDefinition = $privilegeDefinition;
		$this->securityFilterDefinition = $securityFilterDefinition;
		
		foreach ($eiGrant->getEiGrantPrivileges() as $eiGrantPrivilege) {
			$this->eiGrantPrivilegeForms[] = new EiGrantPrivilegeForm($eiGrantPrivilege, $this->privilegeDefinition, 
					$securityFilterDefinition);
		}
	}
	
	public function getEiGrant(): EiGrant {
		return $this->eiGrant; 
	}
	
	public function getPrivilegeDefinition(): PrivilegeDefinition {
		return $this->privilegeDefinition;
	}

	public function areRestrictionsAvailable(): bool {
		return !$this->securityFilterDefinition->isEmpty();
	}
	
	public function isNew() {
		return $this->eiGrant->getId() === null;
	}
	
	public function isUsed(): bool {
		return $this->used;
	}
	
	public function setUsed($used) {
		$this->used = (boolean) $used;
	}
	
	public function isFullAccess() {
		return $this->eiGrant->isFull();
	}
	
	public function setFullAccess($fullAccess) {
		$this->eiGrant->setFull((boolean) $fullAccess);
	}
	
	public function getEiGrantPrivilegeForms() {
		return $this->eiGrantPrivilegeForms;
	}
	
	public function setEiGrantPrivilegeForms(array $userPrivilegeGrantForms) {
		$this->eiGrantPrivilegeForms = $userPrivilegeGrantForms;
	}
	
	private function _validation(BindingDefinition $bd, N2nLocale $n2nLocale) {
// 		if ($this->accessDenyMagForm === null) {
// 			$bc->ignore('accessDenyMagForm');
// 		}
	}
	
	public function save() {
		$this->eiGrant->setFull(false);
		
		$privilegesGrants = new \ArrayObject();
		foreach ($this->eiGrantPrivilegeForms as $grantForm) {
			$grant = $grantForm->getEiGrantPrivilege();
			$grant->setEiGrant($this->eiGrant);
			$privilegesGrants[] = $grant;
		}
		$this->eiGrant->setEiGrantPrivileges($privilegesGrants);
	}
}
