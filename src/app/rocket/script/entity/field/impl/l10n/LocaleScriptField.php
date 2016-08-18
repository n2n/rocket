<?php
namespace rocket\script\entity\field\impl\l10n;

use rocket\script\entity\preview\PreviewModel;
use n2n\dispatch\PropertyPath;
use n2n\l10n\Locale;
use rocket\script\entity\manage\ScriptState;
use n2n\persistence\orm\Entity;
use n2n\ui\html\HtmlView;
use rocket\script\entity\preview\PreviewableScriptField;
use rocket\script\entity\field\HighlightableScriptField;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\property\LocaleProperty;
use rocket\script\entity\field\FilterableScriptField;
use rocket\script\entity\field\SortableScriptField;
use n2n\N2N;
use n2n\dispatch\option\impl\EnumOption;
use rocket\script\entity\filter\item\SimpleFilterItem;
use n2n\util\Attributes;
use n2n\persistence\orm\criteria\CriteriaComparator;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use n2n\core\N2nContext;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\filter\item\SimpleSortItem;
use rocket\script\entity\field\impl\ManageInfo;
use rocket\script\core\SetupProcess;
use rocket\script\entity\modificator\impl\l10n\LocaleScriptModificator;
use n2n\dispatch\option\impl\StringArrayOption;
use n2n\dispatch\option\impl\BooleanOption;

class LocaleScriptField extends TranslatableScriptFieldAdapter implements FilterableScriptField, 
		SortableScriptField, HighlightableScriptField, PreviewableScriptField {
	
	const OPTION_TAKE_LOCALES_FROM_CONFIGURATION_KEY = 'takeLocalesFromConfig';
	const OPTION_CUSTOM_LOCALES_KEY = 'customLocales';
	
	public function getTypeName() {
		return 'Locale';
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		if ($this->isMultiLingual()) return;
		$setupProcess->getEntityScript()->getModificatorCollection()
				->add(new LocaleScriptModificator($this));
	}
	
	public function takeLocalesFromConfig() {
		return $this->attributes->get(self::OPTION_TAKE_LOCALES_FROM_CONFIGURATION_KEY, true);
	}
	
	public function getCustomLocales() {
		return $this->attributes->get(self::OPTION_CUSTOM_LOCALES_KEY, array());
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$optionCollection->addOption(self::OPTION_TAKE_LOCALES_FROM_CONFIGURATION_KEY, 
				new BooleanOption('Take Locales from Configuration (app.ini)', true, false, array(), 
						array('class' => 'rocket-impl-take-locale-from-config')));
		$optionCollection->addOption(self::OPTION_CUSTOM_LOCALES_KEY, 
				new StringArrayOption('Custom Locales'));
		return $optionCollection;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo)  {
		$value = $scriptSelectionMapping->getValue($this->getId());
		if (null === ($locale = Locale::create($value))) return null;
		return $this->generateDisplayNameForLocale($locale, $view->getRequest()->getLocale());
	}
	
	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
		return $view->getFormHtmlBuilder()->getSelect($propertyPath, 
				$this->obtainLocaleArray($view->getRequest()->getLocale()));
	}

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new EnumOption($this->getLabel(), $this->obtainLocaleArray($manageInfo->getScriptState()->getLocale()), 
				Locale::getDefault()->getId(), $this->isRequired($scriptSelectionMapping, $manageInfo));
	}
	
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$scriptSelectionMapping->setValue($this->id, Locale::create($attributes->get($this->id)));
	}
	
	public function propertyValueToOptionAttributeValue(ScriptSelectionMapping $scriptSelectionMapping, 
			Attributes $attributes, ManageInfo $manageInfo) {
		$propertyValue = $scriptSelectionMapping->getValue($this->id);
		$attributeValue = null;
		if ($propertyValue instanceof Locale) {
			$attributeValue = $propertyValue->getId(); 
		}
		$attributes->set($this->id, $attributeValue);
	}

	public function createKnownString(Entity $entity, Locale $locale) {
		$value = $this->getPropertyAccessProxy()->getValue($entity);
		if (null === ($parsedLocale = Locale::create($value))) return $value;
		return $this->generateDisplayNameForLocale($parsedLocale, $locale);
	}

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof LocaleProperty;
	}

	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}

	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new LocaleFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale(), 
						array(CriteriaComparator::OPERATOR_EQUAL, CriteriaComparator::OPERATOR_NOT_EQUAL)),
				$this->obtainLocaleArray($n2nContext->getLocale()));
	}
	
	private function obtainLocaleArray(Locale $displayLocale) {
		$locales = array();
		foreach ($this->obtainValidLocales() as $locale) {
			$locales[$locale->getId()] = $this->generateDisplayNameForLocale($locale, $displayLocale);
		}
		return $locales;
	}
	
	private function generateDisplayNameForLocale(Locale $locale, $displayLocale = null) {
		return $locale->getId() . ' (' .  $locale->getName($displayLocale) . ')';
	}
	
	public function isDisplayInAddViewEnabled() {
		return $this->isMultiLingual() && parent::isDisplayInAddViewEnabled();
	}
	
	public function isDisplayInDetailViewEnabled() {
		return $this->isMultiLingual() && parent::isDisplayInDetailViewEnabled();
	}
	
	public function isDisplayInEditViewEnabled() {
		return $this->isMultiLingual() && parent::isDisplayInEditViewEnabled();
	}
	
	public function isDisplayInListViewEnabled() {
		return $this->isMultiLingual() && parent::isDisplayInListViewEnabled();
	}
	
	public function isMandatory() {
		return $this->isMultiLingual() && parent::isMandatory();
	}
	
	public function isMultiLingual() {
		return count($this->obtainValidLocales()) > 1;
	}
	
	private function obtainValidLocales() {
		if ($this->takeLocalesFromConfig()) {
			return N2N::getLocales();
		}
		$locales = array();
		foreach ($this->getCustomLocales() as $localeId) {
			$locales[] = new Locale($localeId);
		}
		return $locales;
	}
}