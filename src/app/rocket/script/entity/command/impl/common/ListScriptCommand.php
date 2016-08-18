<?php
namespace rocket\script\entity\command\impl\common;

use rocket\script\entity\command\impl\common\controller\ListController;
use rocket\script\entity\command\OverviewScriptCommand;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;
use rocket\script\entity\manage\ScriptState;
use n2n\dispatch\option\impl\IntegerOption;

class ListScriptCommand extends IndependentScriptCommandAdapter implements OverviewScriptCommand {
	const ID_BASE = 'overview';
	const OPTION_LIST_SIZE_KEY = 'numEntries';
	const OPTION_LIST_SIZE_DEFAULT = 30;
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getOverviewPathExt() {
		return $this->getId();
	}
	
	public function getTypeName() {
		return 'Overview (Rocket)';
	}
	
	public function createController(ScriptState $scriptState) {
		return new ListController($this->getAttributes()->get(self::OPTION_LIST_SIZE_KEY, 
				self::OPTION_LIST_SIZE_DEFAULT));
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$optionCollection->addOption(self::OPTION_LIST_SIZE_KEY, 
				new IntegerOption('Num Entries', self::OPTION_LIST_SIZE_DEFAULT));
		return $optionCollection;
	}
}