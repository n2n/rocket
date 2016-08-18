<?php

namespace rocket\script\core;

class MenuItem {
	private $id;
	private $label;
	private $scriptId;
	private $maskId;
	
	public function __construct($id) {
		$this->id = $id;	
		$this->scriptId = $id;
		$this->label = $id;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getScriptId() {
		return $this->scriptId;
	}

	public function getMaskId() {
		return $this->maskId;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function setScriptId($scriptId) {
		$this->scriptId = $scriptId;
	}

	public function setMaskId($maskId) {
		$this->maskId = $maskId;
	}
}