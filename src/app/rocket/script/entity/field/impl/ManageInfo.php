<?php

namespace rocket\script\entity\field\impl;

use rocket\script\entity\manage\ScriptState;
use n2n\util\Attributes;
use rocket\script\entity\manage\model\EntryModel;
use rocket\script\entity\manage\model\EditEntryModel;

class ManageInfo {
	private $scriptState;
	private $maskAttributes;
	private $entryModel;
	
	public function __construct(ScriptState $scriptState, Attributes $maskAttributes, EntryModel $entryModel = null) {
		$this->scriptState = $scriptState;
		$this->maskAttributes = $maskAttributes;
		$this->entryModel = $entryModel;
	}
	
	/**
	 * @return \rocket\script\entity\manage\ScriptState
	 */
	public function getScriptState() {
		return $this->scriptState;
	}
	
	public function getMaskAttributes() {
		return $this->maskAttributes;
	}
	
	public function getEntryModel() {
		return $this->entryModel;
	}
	
	public function hasEditModel() {
		return $this->entryModel instanceof EditEntryModel;
	}
	
	public function hasListModel() {
		return $this->entryModel->hasListEntryModel();
	}
}