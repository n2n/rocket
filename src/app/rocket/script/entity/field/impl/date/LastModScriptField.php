<?php
namespace rocket\script\entity\field\impl\date;

use rocket\script\core\SetupProcess;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;
use rocket\script\entity\modificator\impl\date\LastModScriptModificator;
use rocket\script\entity\field\impl\DisplayableScriptFieldAdapter;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\property\DateTimeProperty;
use n2n\ui\html\HtmlView;
use n2n\l10n\DateTimeFormat;
use n2n\util\Attributes;

class LastModScriptField extends DisplayableScriptFieldAdapter {
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		$this->displayInAddViewDefault = false;
		$this->displayInEditViewDefault = false;
		$this->displayInListViewDefault = false;
	}
	
	public function getTypeName() {
		return 'Last Mod';
	}
	
	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return false;
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		$setupProcess->getEntityScript()->getModificatorCollection()->add(new LastModScriptModificator($this));
	}
	
	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		$this->applyDisplayOptions($optionCollection, true, true, false, false, false);
		return $optionCollection;
	}
	

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DateTimeProperty;
	}

	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, 
			ManageInfo $manageInfo) {
		return $view->getHtmlBuilder()->getL10nDateTime($scriptSelectionMapping->getValue($this->getId()), 
				DateTimeFormat::STYLE_MEDIUM, DateTimeFormat::STYLE_MEDIUM);
		
	}
	
}