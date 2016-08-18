<?php
namespace rocket\script\entity\field\impl\string;

use rocket\script\entity\filter\SimpleComparatorConstraint;
use rocket\script\entity\field\QuickSearchableScriptField;
use rocket\script\entity\filter\item\TextFilterItem;
use rocket\script\entity\field\SortableScriptField;
use n2n\persistence\orm\criteria\CriteriaComparator;
use rocket\script\entity\field\FilterableScriptField;
use rocket\script\entity\manage\ScriptState;
use n2n\dispatch\option\impl\IntegerOption;
use n2n\persistence\orm\property\DefaultProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\filter\item\SimpleFilterItem;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use n2n\core\N2nContext;
use rocket\script\entity\filter\item\SimpleSortItem;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\ui\html\HtmlView;

abstract class AlphanumericScriptField extends TranslatableScriptFieldAdapter implements FilterableScriptField, 
		SortableScriptField, QuickSearchableScriptField {
	const OPTION_MAXLENGTH_KEY = 'maxlength';
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function getMaxlength() {
		return $this->getAttributes()->get(self::OPTION_MAXLENGTH_KEY);
	}

	public function setMaxlength($maxlength) {
		$this->getAttributes()->set(self::OPTION_MAXLENGTH_KEY, $maxlength);
	}

	public function createOptionCollection() {
		$optionForm = parent::createOptionCollection();
		$optionForm->addOption(self::OPTION_MAXLENGTH_KEY, new IntegerOption('Maxlength', null, false, 0));
		return $optionForm;
	}

	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo)  {
		return $view->getHtmlBuilder()->getEsc($scriptSelectionMapping->getValue($this->id));
	}

	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new TextFilterItem($this->getEntityProperty()->getName(), $this->getLabel(), 
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale()));
	}
	
	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}
	
	public function createQuickSearchComparatorConstraint($str) {
		return new SimpleComparatorConstraint($this->getEntityProperty()->getName(), 
				'%' . $str . '%', CriteriaComparator::OPERATOR_LIKE);
	}
}