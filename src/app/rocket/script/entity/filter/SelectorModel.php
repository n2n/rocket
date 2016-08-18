<?php

namespace rocket\script\entity\filter;

use rocket\script\entity\filter\item\SelectorItem;
use rocket\script\entity\filter\data\FilterData;
use rocket\script\entity\filter\data\FilterDataElement;
use rocket\script\entity\filter\data\FilterDataUsage;
use rocket\script\entity\filter\data\FilterDataGroup;
use n2n\reflection\ArgumentUtils;
use n2n\reflection\IllegalArgumentException;
use n2n\core\Message;

class SelectorModel {
	private $selectorItems = array();
	
	public function putSelectorItem($id, SelectorItem $filterItem) {
		$this->selectorItems[$id] = $filterItem;
	}
	
	public function getSelectorItems() {
		return $this->selectorItems;
	}
	
	public static function createFromSelectorItems(array $selectorItems) {
		$selectorModel = new SelectorModel();
		$selectorModel->setSelectorItems($selectorItems);
		return $selectorModel;
	}
	
	public function setSelectorItems(array $selectorItems) {
		ArgumentUtils::validateArrayType($selectorItems, 'rocket\script\entity\filter\item\SelectorItem');
		$this->selectorItems = $selectorItems;
	}
	
	public function createSelector(FilterData $filterData) {
		$selector = new Selector();
		foreach ($filterData->getElements() as $element) {
			$this->applySelectorConstraint($selector, $element);
		}
		
		if ($selector->isEmpty()) return null;
		return $selector;
	}

	private function applySelectorConstraint(Selector $selector, FilterDataElement $element) {
		if ($element instanceof FilterDataUsage) {
			$itemId = $element->getItemId();
			if (isset($this->selectorItems[$itemId])) {
				$selectorConstraint = $this->selectorItems[$itemId]->createSelectorConstraint($element->getAttributes());
				ArgumentUtils::validateReturnType($selectorConstraint,
						'rocket\script\entity\filter\SelectorConstraint',
						$this->selectorItems[$itemId], 'createSelectorConstraint');
				$selector->addSelectorConstraint($itemId, $selectorConstraint);
				return $selectorConstraint;
			}
		} else if ($element instanceof FilterDataGroup) {
			$groupSelector = new Selector();
			$groupSelector->setUseAnd($element->isAndUsed());
			foreach ($element->getAll() as $childElement) {
				$this->applySelectorConstraint($groupSelector, $childElement);
			}
			
			$selector->addGroupSelector($groupSelector);
		}
		
		return null;
	}
}

class Selector {
	private $useAnd = true;
	private $selectorConstraintGroups = array();
	private $groupSelectors = array();
	
	public function setUseAnd($useAnd) {
		$this->useAnd = $useAnd;
	}
	
	public function addSelectorConstraint($id, SelectorConstraint $selectorConstraint) {
		if (!isset($this->selectorConstraintGroups[$id])) {
			$this->selectorConstraintGroups[$id] = array();
		}
		$this->selectorConstraintGroups[$id][] = $selectorConstraint;
	}
	
	public function addGroupSelector(Selector $groupSelector) {
		$this->groupSelectors[] = $groupSelector;
	}
	
	public function isEmpty() {
		return empty($this->selectorConstraintGroups) && empty($this->groupSelectors);
	}
	
	public function validateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
		if ($this->useAnd) {
			return $this->andValidateValues($values, $validationResult);
		} else {
			return $this->orValidateValues($values, $validationResult);
		}
	}
	
	private function andValidateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
		$matches = true;
		foreach ($this->selectorConstraintGroups as $id => $selectorConstraintGroup) {
			foreach ($selectorConstraintGroup as $selectorContraint) {
				if (!$values->offsetExists($id)) {
					throw new IllegalArgumentException('No value for id ' . $id);
				}
		
				$errorMessage = $selectorContraint->validate($values[$id]);
				if (null !== $errorMessage) {
					$validationResult->addError($id, $errorMessage);
					$matches = false;
				}
			}
		}
		
		foreach ($this->groupSelectors as $groupSelector) {
			if (!$groupSelector->validateValues($values, $validationResult)) {
				$matches = false;
			}
		}
		
		return $matches;
	}
	
	private function orValidateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
		$matches = true;
		$selectorValidationResult = new SelectorValidationResult();
		foreach ($this->selectorConstraintGroups as $id => $selectorConstraintGroup) {
			foreach ($selectorConstraintGroup as $selectorContraint) {
				if (!$values->offsetExists($id)) {
					throw new IllegalArgumentException('No value for id ' . $id);
				}
		
				$errorMessage = $selectorContraint->validate($values[$id]);
				if (null === $errorMessage) return true;
				
				$selectorValidationResult->addError($id, $errorMessage);
				$matches = false;
			}
		}
		
		foreach ($this->groupSelectors as $groupSelector) {
			if ($groupSelector->validateValues($values, $validationResult)) {
				return true;
			}
			$matches = false;
		}
		
		$validationResult->addError(null, new OrSelectorMessage($selectorValidationResult->getMessages()));
		
		return $matches;
	}
	
	public function acceptsValues(\ArrayAccess $values) {
		if ($this->useAnd) {
			return $this->andAcceptsValues($values);
		} else {
			return $this->orAcceptValues($values);
		}
	}
	
	private function andAcceptsValues(\ArrayAccess $values) {
		foreach ($this->selectorConstraintGroups as $id => $selectorConstraintGroup) {
			foreach ($selectorConstraintGroup as $selectorContraint) {
				if (!$values->offsetExists($id)) {
					throw new IllegalArgumentException('No value for id ' . $id);
				}
				
				if (!$selectorContraint->matches($values[$id])) return false;
			}
		}

		foreach ($this->groupSelectors as $groupSelector) {
			if (!$groupSelector->acceptsValues($values)) return false;
		}
		
		return true;
		
	}
	
	private function orAcceptValues(\ArrayAccess $values) {
		if ($this->isEmpty()) return true;
		
		foreach ($this->selectorConstraintGroups as $id => $selectorConstraintGroup) {
			foreach ($selectorConstraintGroup as $selectorContraint) {
				if (!$values->offsetExists($id)) {
					throw new IllegalArgumentException('No value for id: ' . $id);
				}
		
				if ($selectorContraint->matches($values[$id])) return true;
			}
		}
		
		foreach ($this->groupSelectors as $groupSelector) {
			if ($groupSelector->acceptsValues($values)) return true;
		}
		
		return false;
	}
	
	public function acceptsValue($id, $value) {
		if ($this->useAnd) {
			return $this->andAcceptsValue($id, $value);
		} else {
			return $this->orAcceptsValue($id, $value);
		}
	}
	
	private function andAcceptsValue($id, $value) {
		if (isset($this->selectorConstraintGroups[$id])) {
			foreach ($this->selectorConstraintGroups[$id] as $selectorContraint) {
				if (!$selectorContraint->matches($value)) return false;
			}
		}
		
		foreach ($this->groupSelectors as $groupSelector) {
			if (!$groupSelector->acceptsValue($id, $value)) return false;
		}
		
		return true;
	}
	
	private function orAcceptsValue($id, $value) {
		if ($this->isEmpty()) return true;
		
		foreach ($this->groupSelectors as $groupSelector) {
			if ($groupSelector->acceptsValue($id, $value)) return true;
		}
		
		if (isset($this->selectorConstraintGroups[$id])) {
			foreach ($this->selectorConstraintGroups[$id] as $selectorContraint) {
				if ($selectorContraint->matches($value)) return true;
			}
			
			if (1 == sizeof($this->selectorConstraintGroups)) {
				return false;
			}
		}
		
		return true;
	}
}

class OrSelectorMessage extends Message {
	public function __construct() {
		parent::__construct('No access to values');
	}
}