<?php
namespace rocket\script\entity\manage\model;


use rocket\script\entity\manage\model\EntryModel;

class TreeListItem {
	private $level;
	private $entryModel;
	
	public function __construct($level, EntryModel $entryModel) {
		$this->level = $level;
		$this->entryModel = $entryModel;
	}
	
	public function getLevel() {
		return $this->level;
	}
	
	public function getEntryModel() {
		return $this->entryModel;
	}
}