<?php
namespace rocket\script\entity\field\impl\bool;

use n2n\persistence\orm\property\DefaultProperty;
use rocket\script\entity\filter\item\TextFilterItem;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\field\SortableScriptField;
use rocket\script\entity\field\FilterableScriptField;
use n2n\ui\html\HtmlView;
use n2n\dispatch\option\impl\BooleanOption;
use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlElement;
use rocket\script\entity\filter\item\SimpleFilterItem;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use n2n\core\N2nContext;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\filter\item\SimpleSortItem;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\dispatch\option\impl\OptionCollectionImpl;

class BooleanScriptField extends TranslatableScriptFieldAdapter implements FilterableScriptField, SortableScriptField {

	public function getTypeName() {
		return 'Boolean';
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return false;	
	}
	
	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		$this->applyDisplayOptions($optionCollection);
		$this->applyDraftOptions($optionCollection);
		$this->applyEditOptions($optionCollection, true, true, false);
		$this->applyTranslationOptions($optionCollection);
		
		return $optionCollection;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo)  {
		$value = $this->getPropertyAccessProxy()->getValue($scriptSelectionMapping->getScriptSelection()->getEntity());
		if ($value) {
			return new HtmlElement('i', array('class' => 'fa fa-check'), '');
		}
		return new HtmlElement('i', array('class' => 'fa fa-check-empty'), '');
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new BooleanOption($this->getLabel(), true,
				$this->isRequired($scriptSelectionMapping, $manageInfo), array('placeholder' => $this->getLabel()));
	}

	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new TextFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale()));
	}

	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}
}