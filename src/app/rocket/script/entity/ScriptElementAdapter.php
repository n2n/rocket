<?php

namespace rocket\script\entity;

use n2n\reflection\ReflectionUtils;

abstract class ScriptElementAdapter implements ScriptElement {
	protected $entityScript;
	protected $id;
	/**
	 * @return EntityScript
	 */
	public function getEntityScript() {
		return $this->entityScript;
	}
	
	public function setEntityScript(EntityScript $entityScript) {
		$this->entityScript = $entityScript;
	}
	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getIdBase() {
		return ReflectionUtils::buildTypeAcronym(get_class($this));
	}
	
	public function equals($obj) {
		return $obj instanceof ScriptElement && $this->id == $obj->getId();
	}
}