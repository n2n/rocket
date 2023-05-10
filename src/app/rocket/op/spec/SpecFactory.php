<?php

namespace rocket\op\spec;

use rocket\op\spec\setup\SpecConfigLoader;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\op\spec\setup\EiTypeFactory;
use rocket\op\ei\EiType;
use n2n\reflection\ReflectionContext;
use rocket\attribute\MenuItem;
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
			return $spec->getMenuGroup($groupKey);
		}

		$menuGroup = new MenuGroup($groupName);
		$spec->putMenuGroup($groupKey, $menuGroup);
		return $menuGroup;
	}

	private function checkForMenuItem(EiType $eiType) {
		$menuItemAttribute = ReflectionContext::getAttributeSet($eiType->getClass())
				->getClassAttribute(MenuItem::class);

		if ($menuItemAttribute === null) {
			return null;
		}

		$spec = $eiType->getSpec();

		/**
		 * @var MenuItem $menuItem
		 */
		$menuItem = $menuItemAttribute->getInstance();
		$launchPad = new EiLaunchPad($eiType->getId(), fn() => $eiType->getEiMask(), $menuItem->name);

		$groupKey = $menuItem->groupKey;
		$groupName = $menuItem->groupName;

		if ($groupKey !== null) {
			$menuGroup = $this->getOrCreateMenuGroup($spec, $groupKey, $groupName ?? StringUtils::pretty($groupKey));

			if ($groupName !== null) {
				$menuGroup->setLabel($groupName);
			}

			$menuGroup->addLaunchPad($launchPad);
			$spec->addLaunchPad($launchPad);
			return;
		}

		if ($groupName === null) {
			$groupName = 'Content';
		}

		$menuGroup = $this->getOrCreateMenuGroup($spec, $groupName, $groupName);
		$menuGroup->addLaunchPad($launchPad);
		$spec->addLaunchPad($launchPad);
	}
}