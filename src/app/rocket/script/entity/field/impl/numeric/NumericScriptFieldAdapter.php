<?php
namespace rocket\script\entity\field\impl\numeric;

use rocket\script\entity\preview\PreviewModel;
use rocket\script\entity\filter\SimpleComparatorConstraint;
use rocket\script\entity\field\QuickSearchableScriptField;
use rocket\script\entity\filter\item\TextFilterItem;
use rocket\script\entity\field\SortableScriptField;
use n2n\persistence\orm\criteria\CriteriaComparator;
use rocket\script\entity\field\FilterableScriptField;
use n2n\dispatch\PropertyPath;
use rocket\script\entity\preview\PreviewableScriptField;
use n2n\l10n\Locale;
use rocket\script\entity\manage\ScriptState;
use n2n\persistence\orm\Entity;
use n2n\ui\html\HtmlView;
use n2n\dispatch\option\impl\IntegerOption;
use n2n\persistence\orm\property\DefaultProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\field\HighlightableScriptField;
use rocket\script\entity\filter\item\SimpleFilterItem;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use n2n\core\N2nContext;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\filter\item\SimpleSortItem;
use rocket\script\entity\field\impl\ManageInfo;

abstract class NumericScriptFieldAdapter extends TranslatableScriptFieldAdapter implements HighlightableScriptField, PreviewableScriptField, 
		FilterableScriptField, SortableScriptField, QuickSearchableScriptField {
	const OPTION_MIN_VALUE_KEY = 'minValue';
	const OPTION_MIN_VALUE_DEFAULT = 0;
	const OPTION_MAX_VALUE_KEY = 'maxValue';
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function getMinValue() {
		return $this->getAttributes()->get(self::OPTION_MIN_VALUE_KEY, self::OPTION_MIN_VALUE_DEFAULT);
	}
	
	public function setMinValue($minValue) {
		$this->getAttributes()->set(self::OPTION_MIN_VALUE_KEY, $minValue);
	}
	
	public function getMaxValue() {
		return $this->getAttributes()->get(self::OPTION_MAX_VALUE_KEY);
	}
	
	public function setMaxValue($maxValue) {
		$this->getAttributes()->set(self::OPTION_MAX_VALUE_KEY, $maxValue);
	}
	
	public function createOptionCollection() {
		$optionForm = parent::createOptionCollection();
		$optionForm->addOption(self::OPTION_MIN_VALUE_KEY, new IntegerOption('Min Value', self::OPTION_MIN_VALUE_DEFAULT));
		$optionForm->addOption(self::OPTION_MAX_VALUE_KEY, new IntegerOption('Max Value'));
		return $optionForm;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo)  {
		$html = $view->getHtmlBuilder();
		return $html->getEsc($scriptSelectionMapping->getValue($this->id));
	}
	
	public function createPreviewUiComponent(ScriptState $scriptState = null, HtmlView $view, $value) {
		return $view->getHtmlBuilder()->getEsc($value);
	}
	
	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new TextFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale()));
	}
	
	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}
	
	public function createQuickSearchComparatorConstraint($str) {
		return new SimpleComparatorConstraint($this->getEntityProperty()->getName(), '%' . $str . '%', CriteriaComparator::OPERATOR_LIKE);
	}

	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
		return $view->getFormHtmlBuilder()->getInputField($propertyPath, 
				array('class' => 'rocket-preview-inpage-component'));
	}

	public function createKnownString(Entity $entity, Locale $locale) {
		return $this->read($entity);
	}
}