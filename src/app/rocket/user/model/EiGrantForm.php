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
use rocket\user\bo\EiPrivilegeGrant;
use rocket\user\bo\EiGrant;
use rocket\spec\security\PrivilegeDefinition;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\spec\ei\manage\critmod\filter\EiEntryFilterDefinition;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;

class EiGrantForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('fullAccess'));
		$ai->p('eiPrivilegeGrantForms', new AnnoDispObjectArray( 
				function (EiGrantForm $that) {
					return new EiPrivilegeGrantForm(new EiPrivilegeGrant(), $that->privilegeDefinition,
							$that->eiEntryFilterDefinition);
				}));
	}
	
	private $eiGrant;
	private $privilegeDefinition;
	private $eiEntryFilterDefinition;
	
	private $accessDenyMagForm;
	private $eiPrivilegeGrantForms = array();
	
	public function __construct(EiGrant $eiGrant, PrivilegeDefinition $privilegeDefinition, 
			EiEntryFilterDefinition $eiEntryFilterDefinition) {
		$this->eiGrant = $eiGrant;
		$this->privilegeDefinition = $privilegeDefinition;
		$this->eiEntryFilterDefinition = $eiEntryFilterDefinition;
		
		foreach ($eiGrant->getEiPrivilegeGrants() as $eiPrivilegeGrant) {
			$this->eiPrivilegeGrantForms[] = new EiPrivilegeGrantForm($eiPrivilegeGrant, $this->privilegeDefinition, 
					$eiEntryFilterDefinition);
		}
	}
	
	public function getEiGrant(): EiGrant {
		return $this->eiGrant; 
	}
	
	public function getPrivilegeDefinition(): PrivilegeDefinition {
		return $this->privilegeDefinition;
	}

	public function areRestrictionsAvailable(): bool {
		return !$this->eiEntryFilterDefinition->isEmpty();
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
	
	public function getEiPrivilegeGrantForms() {
		return $this->eiPrivilegeGrantForms;
	}
	
	public function setEiPrivilegeGrantForms(array $userPrivilegeGrantForms) {
		$this->eiPrivilegeGrantForms = $userPrivilegeGrantForms;
	}
	
	private function _validation(BindingDefinition $bd, N2nLocale $n2nLocale) {
// 		if ($this->accessDenyMagForm === null) {
// 			$bc->ignore('accessDenyMagForm');
// 		}
	}
	
	public function save() {
		$this->eiGrant->setFull(false);
		
		$privilegesGrants = new \ArrayObject();
		foreach ($this->eiPrivilegeGrantForms as $grantForm) {
			$grant = $grantForm->getEiPrivilegeGrant();
			$grant->setEiGrant($this->eiGrant);
			$privilegesGrants[] = $grant;
		}
		$this->eiGrant->setEiPrivilegeGrants($privilegesGrants);
	}
}
