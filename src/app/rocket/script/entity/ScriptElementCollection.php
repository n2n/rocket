<?php

namespace rocket\script\entity;

use n2n\io\IoUtils;
use n2n\reflection\ArgumentUtils;

class ScriptElementCollection implements \IteratorAggregate, \Countable {
	private $elements = array();
	private $elementName;
	private $entityScript;
	private $genericType;
	private $superCollection;
	private $subCollections = array();
	
	public function __construct($elementName, EntityScript $entityScript, $genericType) {
		$this->elementName = $elementName;
		$this->entityScript = $entityScript;
		$this->genericType = $genericType;
	}
	
	public function setSuperCollection(ScriptElementCollection $superCollection = null) {
		$this->superCollection = $superCollection;
	}
	
	public function getSuperCollection() {
		return $this->superCollection;
	}
	/**
	 * @param ScriptElement $scriptElement
	 */
	public function add(ScriptElement $scriptElement) {
		ArgumentUtils::validateType($scriptElement, $this->genericType);
		if (null === $scriptElement->getId()) {
			$scriptElement->setId($this->makeUniqueId($scriptElement->getIdBase()));
		}
		$scriptElement->setEntityScript($this->entityScript);
		$this->elements[$scriptElement->getId()] = $scriptElement;
	}
	
	public function getById($id) {
		if (isset($this->elements[$id])) {
			return $this->elements[$id];
		}
		
		if ($this->superCollection !== null) {
			return $this->superCollection->getById($id);
		}
		
		throw new UnknownScriptElementException('No ' . $this->elementName . ' with id \'' . (string) $id 
				. '\' found in script \'' . $this->entityScript->getId() . '\'.');
	}
	
	private function makeUniqueId($idBase) {
		$idBase = IoUtils::stripSpecialChars($idBase);
		if (mb_strlen($idBase) && !$this->containsId($idBase, true, true)) {
			return $idBase;			
		}
		
		for ($ext = 1; true; $ext++) {
			$id = $idBase . $ext;
			if (!$this->containsId($id, true, true)) {
				return $id;
			}
		}
	}
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsId($id, $checkInherited = true, $checkSub = false) {
		if (isset($this->elements[$id])) return true;
		
		if ($this->superCollection !== null && $checkInherited && 
				$this->superCollection->containsId($id, true, false)) {
			return true;
		}
		
		if ($checkSub) {
			foreach ($this->subCollections as $subCollection) {
				if ($subCollection->containsId($id, false, true)) {
					return true;			
				}
			}
		}
		
		return false;
	}
	/* (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new \ArrayIterator($this->toArray());
	}
	
	public function toArray($independentOnly = false) {
		$elements = $this->elements;

		if ($this->superCollection !== null) {
			$elements = $this->superCollection->toArray() + $elements;
		}
		
		if ($independentOnly) {
			$elements = $this->filterIndependents($elements);
		}
		
		return $elements;
	}
	
	public function filter($levelOnly = false, $independentOnly = false) {
		if ($levelOnly) {
			return $this->filterLevel($independentOnly);
		}
		
		return $this->toArray($independentOnly);
	}
	
	public function filterInherited($independentOnly = false) {
		if ($this->superCollection === null) return array();
		if (!$independentOnly) return $this->superCollection->toArray();

		return $this->filterIndependents($this->superCollection->toArray());
	}
	/**
	 * @param boolean $independentOnly
	 * @return \rocket\script\entity\ScriptElement[] 
	 */
	public function filterLevel($independentOnly = false) {
		if (!$independentOnly) return $this->elements;
		return $this->filterIndependents($this->elements);
	}
	
	private function filterIndependents(array $elements) {
		$independentElements = array();
		foreach ($elements as $key => $element) {
			if ($element instanceof IndependentScriptElement) {
				$independentElements[$key] = $element;
			}
		}
		
		return $independentElements;
	}
	/* (non-PHPdoc)
	 * @see Countable::count()
	 */
	public function count() {
		return sizeof($this->elements);	
	}
	
	public function clear($independentOnly) {
		$this->clearLevel($independentOnly);
		
		if ($this->superCollection !== null) {
			$this->superCollection->clear($independentOnly);
		}
	}
	
	public function clearLevel($independentOnly = false) {
		if (!$independentOnly) {
			$this->elements = array();
			return;
		}
		
		foreach ($this->filterIndependents($this->elements) as $id => $element) {
			unset($this->elements[$id]);
		}
	}
	
	public function removeById($id) {
		if (isset($this->elements[$id])) {
			unset($this->elements[$id]);
		}
		
		if ($this->superCollection !== null) {
			$this->superCollection->removeById($id);
		}
	}
	
	public function remove(ScriptElement $scriptElement) {
		$this->removeById($scriptElement->getId());
	}
	
	public function combineAll($independentOnly = false) {
		return $this->filterInherited($independentOnly) 
				+ $this->combineLevelAndSub($independentOnly);
	}
	
	protected function combineLevelAndSub($independentOnly = false) {
		$elements = $this->filterLevel($independentOnly);
		foreach ($this->subCollections as $subCollection) {
			$elements += $subCollection->combineLevelAndSub($independentOnly);
		}
		return $elements;
	}
}