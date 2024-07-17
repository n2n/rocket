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

namespace rocket\op\spec;

use rocket\op\spec\setup\SpecConfigLoader;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\op\spec\setup\EiTypeFactory;
use rocket\op\ei\EiType;
use n2n\reflection\ReflectionContext;
use rocket\attribute\EiMenuItem;
use rocket\op\ei\EiLaunchPad;
use rocket\op\launch\MenuGroup;
use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;

class SpecFactory {
	private EiTypeFactory $eiTypeFactory;

	/**
	 * @param SpecConfigLoader $specConfigLoader
	 * @param EntityModelManager $entityModelManager
	 */
	public function __construct(SpecConfigLoader $specConfigLoader, EntityModelManager $entityModelManager) {
		$this->eiTypeFactory = new EiTypeFactory($specConfigLoader, $entityModelManager);
	}

	function create(Spec $spec = null, array $entityClasses = null) {
		ArgUtils::valArray($entityClasses, \ReflectionClass::class, true);

		$spec = $spec ?? new Spec();
		foreach ($entityClasses ?? $this->eiTypeFactory->getEntityModelManager()->getEntityClasses() as $entityClass) {
			$eiType = $this->eiTypeFactory->build($entityClass, $spec, false);
			if ($eiType === null) {
				continue;
			}

			$spec->addEiType($eiType);
			$this->checkForMenuItem($eiType);
		}

		return $spec;
	}

	private function getOrCreateMenuGroup(Spec $spec, string $groupKey, string $groupName) {
		if ($spec->containsMenuGroupKey($groupKey)) {
			return $spec->getMenuGroup($groupKey)->setLabel($groupName);
		}

		$menuGroup = new MenuGroup($groupName);
		$spec->putMenuGroup($groupKey, $menuGroup);
		return $menuGroup;
	}

	private function checkForMenuItem(EiType $eiType) {
		$menuItemAttribute = ReflectionContext::getAttributeSet($eiType->getClass())
				->getClassAttribute(EiMenuItem::class);

		if ($menuItemAttribute === null) {
			return null;
		}

		$spec = $eiType->getSpec();

		/**
		 * @var EiMenuItem $menuItem
		 */
		$menuItem = $menuItemAttribute->getInstance();
		$launchPad = new EiLaunchPad($eiType->getId(), fn() => $eiType->getEiMask(), $menuItem->name,
				$menuItem->transactionalEmEnabled, $menuItem->persistenceUnitName);
		$launchPad->setOrderIndex($menuItem->orderIndex ?? $launchPad->getOrderIndex());

		$groupKey = $menuItem->groupKey;
		$groupName = $menuItem->groupName;

		if ($groupKey !== null) {
			$menuGroup = $this->getOrCreateMenuGroup($spec, $groupKey, $groupName ?? StringUtils::pretty($groupKey));
		} else {
			if ($groupName === null) {
				$groupName = 'Content';
			}

			$menuGroup = $this->getOrCreateMenuGroup($spec, $groupName, $groupName);
		}

		$menuGroup->setOrderIndex($menuItem->groupOrderIndex ?? $menuGroup->getOrderIndex());
		$menuGroup->addLaunchPad($launchPad);
		$spec->addLaunchPad($launchPad);
	}
}