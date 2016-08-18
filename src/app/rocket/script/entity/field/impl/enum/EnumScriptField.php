<?php
namespace rocket\script\entity\field\impl\enum;

use n2n\dispatch\option\impl\EnumOption;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use rocket\script\entity\filter\item\SimpleFilterItem;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\dispatch\option\impl\OptionCollectionArrayOption;
use n2n\dispatch\option\impl\StringOption;
use rocket\script\entity\filter\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\CriteriaComparator;
use n2n\ui\html\HtmlView;
use rocket\script\entity\field\FilterableScriptField;
use rocket\script\entity\field\SortableScriptField;
use rocket\script\entity\field\QuickSearchableScriptField;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\property\DefaultProperty;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\preview\PreviewableScriptField;
use rocket\script\entity\field\HighlightableScriptField;
use rocket\script\entity\preview\PreviewModel;
use n2n\dispatch\PropertyPath;
use n2n\persistence\orm\Entity;
use n2n\l10n\Locale;
use n2n\core\N2nContext;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\filter\item\EnumFilterItem;
use rocket\script\entity\filter\item\SimpleSortItem;
use rocket\user\model\RestrictionScriptField;
use rocket\script\entity\filter\item\EnumSelectorItem;
use rocket\script\entity\field\impl\ManageInfo;

class EnumScriptField extends TranslatableScriptFieldAdapter implements FilterableScriptField, RestrictionScriptField,
		SortableScriptField, QuickSearchableScriptField, HighlightableScriptField, PreviewableScriptField {
	
	CONST OPTION_OPTIONS_KEY = 'options';
	
	public function getTypeName() {
		return 'Enum';
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function getOptions() {
		$options = array();
		foreach ((array) $this->getAttributes()->get(self::OPTION_OPTIONS_KEY) as $option) {
			$options[$option['value']] = $option['label'];
		}
		return $options;
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$options = $this->getOptions();
		if (null !== ($ssPrivilegeConstraint = $scriptSelectionMapping->getSelectionPrivilegeConstraint())) {
			foreach ($options as $value => $label) {
				if (!$ssPrivilegeConstraint->acceptsValue($this->id, $value)) {
					unset($options[$value]);
				}
			}
		}
		return new EnumOption($this->getLabel(), $options, null, 
				$this->isRequired($scriptSelectionMapping, $manageInfo));
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$optionCollection->addOption('options', new OptionCollectionArrayOption('Options', function() {
			$optionCollection = new OptionCollectionImpl();
			$optionCollection->addOption('value', new StringOption('Value'));
			$optionCollection->addOption('label', new StringOption('Label'));
			return $optionCollection;
		}));
		return $optionCollection;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo)  {
		$html = $view->getHtmlBuilder();
		$options = $this->getOptions();
		$value = $scriptSelectionMapping->getValue($this->id);
		if (isset($options[$value])) {
			return $html->getEsc($options[$value]);
		}
		return $html->getEsc($value);
	}
	
	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new EnumFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale()), $this->getOptions());
	}
	
	public function createRestrictionSelectorItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new EnumSelectorItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale()), $this->getOptions());
	}
	
	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}
	
	public function createQuickSearchComparatorConstraint($str) {
		return new SimpleComparatorConstraint($this->getEntityProperty()->getName(), '%' . $str . '%', CriteriaComparator::OPERATOR_LIKE);
	}

	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath, HtmlView $view,\Closure $createCustomUiElementCallback = null) {
		return $view->getFormHtmlBuilder()->getSelect($propertyPath, $this->getOptions(), array('class' => 'rocket-preview-inpage-component'));
	}

	public function createKnownString(Entity $entity, Locale $locale) {
		return $this->read($entity);
	}
}
