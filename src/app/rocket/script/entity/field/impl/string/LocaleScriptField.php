<?php
namespace rocket\script\entity\field\impl\string;

use rocket\script\entity\preview\PreviewModel;
use rocket\script\entity\filter\item\TextFilterItem;
use n2n\dispatch\PropertyPath;
use n2n\persistence\orm\property\EntityProperty;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\ScriptState;
use n2n\N2N;
use n2n\dispatch\option\impl\EnumOption;
use n2n\persistence\orm\property\DefaultProperty;
use rocket\script\entity\field\QuickSearchableScriptField;
use rocket\script\entity\field\SortableScriptField;
use rocket\script\entity\field\FilterableScriptField;
use rocket\script\entity\preview\PreviewableScriptField;
use rocket\script\entity\filter\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\CriteriaComparator;
use rocket\script\entity\filter\item\SimpleFilterItem;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use rocket\script\entity\filter\item\SimpleSortItem;
use rocket\script\entity\field\impl\ManageInfo;

class LocaleScriptField extends TranslatableScriptFieldAdapter implements PreviewableScriptField, FilterableScriptField, SortableScriptField, QuickSearchableScriptField {

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function getTypeName() {
		return 'Locale Id';
	}

	public function createUiOutputField(ScriptState $scriptState, ScriptSelection $scriptSelection, HtmlView $htmlView)  {
		$html = $htmlView->getHtmlBuilder();
		return $html->getEsc($this->getPropertyAccessProxy()->getValue($scriptSelection->getEntity()));
	}

	private function getOptions() {
		$options = array();
		
		if ($this->isOptional()) {
			$options[null] = null;
		}
		
		foreach (N2N::getLocales() as $locale) {
			$options[$locale->getId()] = $locale->getName($locale->getName());
		}
		
		return $options;
	}
		
	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
		
		return $view->getFormHtmlBuilder()->getSelect($propertyPath, $this->getOptions(), array('class' => 'rocket-preview-inpage-component'));
	}

	public function createFilterItem(ScriptState $scriptState) {
		return new TextFilterItem($this->getEntityProperty()->getName(), 
				SimpleFilterItem::createOperatorOptions($scriptState->getLocale()));
	}
	
	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}
	
	public function createQuickSearchComparatorConstraint($str) {
		return new SimpleComparatorConstraint($this->getEntityProperty()->getName(), '%' . $str . '%', CriteriaComparator::OPERATOR_LIKE);
	}

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) { throw new NotYetImplementedException();
		$options = $this->getOptions();
		$keys = array_keys($options);
		return new EnumOption($this->getLabel(), $options,
				$keys[0], $this->isRequired($scriptSelectionMapping, $manageInfo));
	}
}