<?php
namespace rocket\script\entity\field\impl\file\command;

use rocket\script\entity\field\impl\file\FileScriptField;
use rocket\script\entity\command\impl\ScriptCommandAdapter;
use rocket\script\entity\field\impl\file\command\controller\ThumbController;
use rocket\script\entity\manage\ScriptState;


class ThumbScriptCommand extends ScriptCommandAdapter {
	const ID_BASE = 'thumb';
	
	private $fileScriptField;
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Thumb';
	}
	
	public function setFileScriptField(FileScriptField $fileScriptField) {
		$this->fileScriptField = $fileScriptField;
	}
		
	public function createController(ScriptState $scriptState) {
		$detailController = new ThumbController();
		$detailController->setFileScriptField($this->fileScriptField);
		return $detailController;
	}
}