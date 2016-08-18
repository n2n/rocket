<?php

namespace rocket\script\config\model;

use rocket\script\core\extr\ScriptExtraction;
use rocket\script\core\extr\EntityScriptExtraction;
use n2n\reflection\ReflectionUtils;
use n2n\core\TypeNotFoundException;
use rocket\script\core\extr\CustomScriptExtraction;

class ConfigTemplateModel {
	private $activeScriptExtractionId;
	private $scriptExtractions;
	private $entityScriptConfigMode;
	private $titleKey;
	private $rootNavItems = array();
	private $navItems;
	
	/**
	 * @param unknown $pathExt
	 */
	public function __construct($activeScriptExtractionId, array $scriptExtractions, $entityScriptConfgMode) {
		$this->activeScriptExtractionId = $activeScriptExtractionId;
		$this->scriptExtractions = $scriptExtractions;
		$this->entityScriptConfigMode = $entityScriptConfgMode;
		foreach ($scriptExtractions as $scriptExtraction) {
			if ($entityScriptConfgMode && !($scriptExtraction instanceof EntityScriptExtraction)) continue;
			$this->createNavItem($scriptExtraction, $scriptExtractions);
		}
	}
	
	public function getTitleKey() {
		return $this->titleKey;
	}
	
	private function createNavItem(ScriptExtraction $extraction) {
		$id = $extraction->getId();
		if (isset($this->navItems[$id])) {
			return $this->navItems[$id];
		}
		
		$this->navItems[$id] = $navItem = new ConfigNavItem($extraction->getLabel(), $extraction->getModule()->getNamespace());
		
		if ($extraction->getId() == $this->activeScriptExtractionId) {
			$navItem->setActive(true);
		}
		
		if ($extraction instanceof CustomScriptExtraction) {
			$navItem->setPathExt(array('edit', $extraction->getId()));
		} else if ($extraction instanceof EntityScriptExtraction) {
			if ($this->entityScriptConfigMode) {
				$navItem->setPathExt(array($extraction->getId()));
			} else {
				$navItem->setPathExt(array('edit', $extraction->getId()));
			}

			if (null !== ($parentExtraction = $this->findParentExtraction($extraction))) {
				$this->createNavItem($parentExtraction)
						->addChild($navItem);
				return $navItem;
			}
		}
				
		return $this->rootNavItems[$id] = $navItem;
	}
	
	private function findParentExtraction(EntityScriptExtraction $extraction) {
		try {
			$entityClass = ReflectionUtils::createReflectionClass($extraction->getEntityClassName());
			$parentClass = $entityClass->getParentClass();
			if ($parentClass) {
				foreach ($this->scriptExtractions as $parentScriptExtraction) {
					if (!($parentScriptExtraction instanceof EntityScriptExtraction) 
							|| $parentClass->getName() != $parentScriptExtraction->getEntityClassName()) continue;
		
					return $parentScriptExtraction;
				}
			}
		} catch (TypeNotFoundException $e) { }
	}
	
	
	public function getNavItems() {
		return $this->rootNavItems;
	}
}

class ConfigNavItem {
	private $pathExt;
	private $active;
	private $label;
	private $children;
	
	public function __construct($label, $moduleNamespace) {
		$this->label = $label;
		$this->moduleNamespace = $moduleNamespace;
	}
	
	public function getPathExt() {
		return $this->pathExt;
	}

	public function setPathExt($pathExt) {
		$this->pathExt = $pathExt;
	}
	
	public function setActive($active) {
		$this->active = $active;
	}
	
	public function isActive() {
		return $this->active;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	public function setModuleNamespace($moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
	}

	public function hasChildren() {
		return (boolean) sizeof($this->children);
	}
	
	public function getChildren() {
		return $this->children;
	}
	
	public function addChild(ConfigNavItem $child) {
		$this->children[] = $child;
	}
}