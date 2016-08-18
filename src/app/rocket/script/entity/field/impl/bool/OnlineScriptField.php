<?php
namespace rocket\script\entity\field\impl\bool;

use rocket\script\core\SetupProcess;
use rocket\script\entity\field\impl\bool\command\OnlineScriptCommand;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\util\Attributes;

class OnlineScriptField extends BooleanScriptField {
	private $onlineScriptCommand;
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		$this->displayInListViewDefault = false;
	}
	
	public function getTypeName() {
		return 'Online Status';
	}
	
	public function isDisplayInListViewEnabled() {
		return false;
	}
	
	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		$this->applyDisplayOptions($optionCollection, false);
		$this->applyDraftOptions($optionCollection);
		$this->applyEditOptions($optionCollection, true, true, false);
		$this->applyTranslationOptions($optionCollection);
		return $optionCollection;
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		$this->onlineScriptCommand = new OnlineScriptCommand($this->getEntityScript());
		$this->onlineScriptCommand->setOnlineScriptField($this);
		$entityScript = $this->getEntityScript();
		$entityScript->getCommandCollection()->add($this->onlineScriptCommand, true);
	}
}