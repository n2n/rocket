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
namespace rocket\user\bo;

use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\util\StringUtils;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use rocket\ei\manage\critmod\filter\data\FilterPropSettingGroup;
use n2n\util\config\Attributes;
use n2n\util\config\AttributesException;
use n2n\reflection\ArgUtils;
use rocket\ei\EiCommandPath;
use rocket\ei\manage\security\privilege\data\PrivilegeSetting;

class EiGrantPrivilege extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_ei_grant_privileges'));
		$ai->p('eiGrant', new AnnoManyToOne(EiGrant::getClass()));
	}
	
	private $id;
	private $eiGrant;
	private $eiPrivilegeJson = '{}';
	private $restricted = false;
	private $restrictionGroupJson = '[]';
	
	public function getEiGrant() {
		return $this->eiGrant;
	}

	public function setEiGrant(EiGrant $eiGrant) {
		$this->eiGrant = $eiGrant;
	}

	public function getEiCommandPathStrs() {
		return StringUtils::jsonDecode($this->eiCommandPrivilegeJson, true);
	}
	
	public function setEiCommandPathStrs(array $commnadPathStrs) {
		ArgUtils::valArray($commnadPathStrs, 'string');
		$this->eiCommandPrivilegeJson = StringUtils::jsonEncode($commnadPathStrs);
	}
	
	public function getEiCommandPaths() {
		$eiCommandPaths = array();
		foreach ($this->getEiCommandPathStrs() as $eiCommandPathStr) {
			$eiCommandPaths[$eiCommandPathStr] = EiCommandPath::create($eiCommandPathStr);
		}
		return $eiCommandPaths;
	}
	
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @return boolean
	 */
	public function acceptsEiCommandPath(EiCommandPath $eiCommandPath) {
		foreach ($this->getEiCommandPaths() as $privilegeCommandPath) {
			if ($privilegeCommandPath->startsWith($eiCommandPath)) return true;
		}
		return false;
	}

	/**
	 * @return PrivilegeSetting
	 */
	public function readPrivilegeSetting(): PrivilegeSetting {
		return PrivilegeSetting::create(new Attributes(StringUtils::jsonDecode($this->eiPrivilegeJson, true)));
	}
	
	public function writePrivilegeSetting(PrivilegeSetting $privilegeSetting) {
		$this->eiPrivilegeJson = StringUtils::jsonEncode($privilegeSetting->toAttrs());
	}
	
	public function isRestricted(): bool {
		return (bool) $this->restricted;
	}
	
	public function setRestricted(bool $restricted) {
		$this->restricted = $restricted;
	}
	
	public function readRestrictionFilterPropSettingGroup(): FilterPropSettingGroup {
		try {
			return FilterPropSettingGroup::create(new Attributes(StringUtils::jsonDecode($this->restrictionGroupJson, true)));
		} catch (AttributesException $e) {
			return new FilterPropSettingGroup();
		}
	}
	
	public function writeRestrictionFilterData(FilterPropSettingGroup $restrictionFilterPropSettingGroup) {
		$this->restrictionGroupJson = StringUtils::jsonEncode($restrictionFilterPropSettingGroup->toAttrs());
	}
}