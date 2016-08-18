<?php
namespace rocket\script\entity\field\impl\string;

use n2n\persistence\orm\criteria\CriteriaProperty;

use n2n\io\IoUtils;
use n2n\dispatch\option\impl\EnumOption;
use rocket\script\core\SetupProcess;
use rocket\script\entity\filter\SimpleComparatorConstraint;
use rocket\script\entity\filter\item\TextFilterItem;
use n2n\persistence\orm\criteria\CriteriaComparator;
use n2n\dispatch\option\impl\BooleanOption;
use n2n\l10n\Locale;
use rocket\script\entity\manage\ScriptState;
use n2n\dispatch\option\impl\StringOption;
use n2n\persistence\orm\Entity;
use n2n\ui\html\HtmlView;
use n2n\util\Attributes;
use n2n\persistence\orm\property\DefaultProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\field\HighlightableScriptField;
use rocket\script\entity\filter\item\SimpleFilterItem;
use rocket\script\entity\field\EntityPropertyScriptField;
use n2n\core\DynamicTextCollection;
use n2n\core\N2nContext;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\filter\item\SimpleSortItem;
use n2n\persistence\orm\EntityManager;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\field\impl\ManageInfo;
use rocket\script\entity\modificator\impl\string\UrlPartScriptModificator;
use n2n\dispatch\option\impl\OptionCollectionImpl;

class UrlPartScriptField extends AlphanumericScriptField implements HighlightableScriptField  {
	const URL_COUNT_SEPERATOR = '-';
	const OPTION_BASE_FIELD_KEY = 'baseField';
	const OPTION_ALLOW_EMPTY_KEY = 'allowEmpty';
	const OPTION_UNIQUE_PER_KEY = 'uniquePerPropertyName';
	const OPTION_CRITICAL_KEY = 'critical';
	const OPTION_CRITICAL_MESSAGE_KEY = 'showCriticalMessage';
	
	private $urlScriptCommand;
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		$this->optionRequiredDefault = false;
		$this->displayInAddViewDefault = false;
		$this->displayInListViewDefault = false;
	}
	
	public function getTypeName() {
		return 'Url Part';
	}
	
	public function isMandatory() {
		return false;
	}
	
	public function getUniquePerPropertyName() {
		return $this->attributes->get(self::OPTION_UNIQUE_PER_KEY);
	}
	
	public function getBaseFieldPropertyName() {
		return $this->attributes->get(self::OPTION_BASE_FIELD_KEY);
	}
	
	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		$this->applyDisplayOptions($optionCollection);
		$this->applyDraftOptions($optionCollection);
		$this->applyEditOptions($optionCollection, true, false, false);
		$this->applyTranslationOptions($optionCollection);
		$optionCollection->addOption(self::OPTION_BASE_FIELD_KEY, new EnumOption('Base Field', $this->getPossibleBaseFields(), $this->getFirstPossibleBaseField()));
		$optionCollection->addOption(self::OPTION_ALLOW_EMPTY_KEY, new BooleanOption('Leerwert erlaubt', false));
		$optionCollection->addOption(self::OPTION_UNIQUE_PER_KEY, new EnumOption('Unique per', $this->generateUniquePerEnumOptions()));
		$optionCollection->addOption(self::OPTION_CRITICAL_KEY, new BooleanOption('Is critical', true));
		$optionCollection->addOption(self::OPTION_CRITICAL_MESSAGE_KEY, new BooleanOption('Show message if critical', false));
		return $optionCollection;
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		
		if (!$this->isDisplayInAddViewEnabled() 
				|| !$this->isDisplayInEditViewEnabled()) {
			$this->getEntityScript()->getModificatorCollection()->add(new UrlPartScriptModificator($this));
		}
		
	}

	public function isCompatibleWith(EntityProperty $entityProperty) {
		//@todo ensure that the field is unique
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo)  {
		return $view->getHtmlBuilder()->getEsc($this->read($scriptSelectionMapping->getScriptSelection()->getEntity()));
	}

	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$attrs = array();
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		if (!$scriptSelection->isNew() && $this->getAttributes()->get(self::OPTION_CRITICAL_KEY) === true) {
			$attrs['class'] = 'rocket-critical-input';
			if ($this->getAttributes()->get(self::OPTION_CRITICAL_MESSAGE_KEY)) {
				$dtc = new DynamicTextCollection('rocket');
				$attrs['data-confirm-message'] = $dtc->translate('script_field_url_unlock_confirm_message');
				$attrs['data-edit-label'] =  $dtc->translate('common_edit_label');
				$attrs['data-cancel-label'] =  $dtc->translate('common_cancel_label');
			}
		}
		return new StringOption($this->getLabel(), null,
				$this->isRequired($scriptSelectionMapping, $manageInfo), $this->attributes->get('maxlength'), false, $attrs);
	}
	
	public function createKnownString(Entity $entity, Locale $locale) {
		return $this->getPropertyAccessProxy()->getValue($entity);
	}
	
	public function createQuickSearchComparatorConstraint($str) {
		return new SimpleComparatorConstraint($this->getEntityProperty()->getName(), '%' . $str . '%', CriteriaComparator::OPERATOR_LIKE);
	}
	
	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new TextFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale()));
	}
	
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$scriptSelectionMapping->setValue($this->id, $this->determineUrlPart($manageInfo->getScriptState()->getEntityManager(), 
				$attributes->get($this->id), $scriptSelectionMapping->getValue($this->id), $attributes, 
				$scriptSelectionMapping->getScriptSelection())); 
	}
	
	private function getFirstPossibleBaseField() {
		foreach (array_keys($this->getPossibleBaseFields()) as $basefieldName) {
			return $basefieldName;
		}
		return null;
	}
	
	private function getPossibleBaseFields() {
		$baseFields = array();
		foreach ($this->getEntityScript()->getFieldCollection()->combineAll() as $scriptField) {
			if ($scriptField instanceof StringScriptField) {
				$baseFields[$scriptField->getEntityProperty()->getName()] = $scriptField->getLabel();
			}
		}
		return $baseFields;
	}
	
	private function generateUniquePerEnumOptions() {
		$uniquePerFields = array();
		foreach ($this->getEntityScript()->getFieldCollection()->combineAll() as $scriptField) {
			if (!($scriptField instanceof EntityPropertyScriptField)) continue;
			$uniquePerFields[$scriptField->getEntityProperty()->getName()] = $scriptField->getLabel();
		}
		return $uniquePerFields;
	}
	
	public function determineUrlPart(EntityManager $em, $newValue, $oldValue, Attributes $attributes, ScriptSelection $scriptSelection) {
		$entityModel = $this->getEntityScript()->getEntityModel();
		$scriptClass = $entityModel->getClass();
		$idPropertyName = $entityModel->getIdProperty()->getName();
		$uniquePerPropertyName = $this->getAttributes()->get(self::OPTION_UNIQUE_PER_KEY);
	
		if (!mb_strlen($newValue)) {
			if ($this->getAttributes()->get(self::OPTION_ALLOW_EMPTY_KEY)) {
				$criteria = $em->createCriteria($scriptClass, 'o');
				$criteria->where()->andMatch(new CriteriaProperty(array('o', $this->getEntityProperty()->getName())),
						CriteriaComparator::OPERATOR_EQUAL, null);
				if (!$scriptSelection->isNew()) {
					$criteria->where()->andMatch(new CriteriaProperty(array('o', $idPropertyName)),
							CriteriaComparator::OPERATOR_NOT_EQUAL, $scriptSelection->getId());
				}
				if (null !== $uniquePerPropertyName) {
					$criteria->where()->andMatch(new CriteriaProperty(array('o', $uniquePerPropertyName)),
							CriteriaComparator::OPERATOR_EQUAL, $attributes->get($uniquePerPropertyName));
				}
				$criteria->select('COUNT(o)');
					
				if (0 == $criteria->fetchSingle()) {
					return null;
				}
			}
			if (null !== ($baseFieldName = $this->getAttributes()->get(self::OPTION_BASE_FIELD_KEY))) {
				$baseValue = $attributes->get($baseFieldName);
			}
		} else {
			$baseValue = $newValue;
		}
		$baseValue = mb_strtolower(IoUtils::stripSpecialChars($baseValue, true));
		$newValue = $baseValue;
		//ensure that the url is unique
		for ($counter = 0; true; $counter++) {
			if ($counter > 0) {
				$newValue = $baseValue . self::URL_COUNT_SEPERATOR . $counter;
			}
				
			$counterCriteria = $em->createCriteria($scriptClass, 'o');
			$counterCriteria->where()->andMatch(new CriteriaProperty(array('o', $this->getEntityProperty()->getName())),
					CriteriaComparator::OPERATOR_EQUAL, $newValue);
				
			if (!$scriptSelection->isNew()) {
				$counterCriteria->where()->andMatch(new CriteriaProperty(array('o', $idPropertyName)),
						CriteriaComparator::OPERATOR_NOT_EQUAL, $scriptSelection->getId());
			}
			if (null !== $uniquePerPropertyName) {
				$counterCriteria->where()->andMatch(new CriteriaProperty(array('o', $uniquePerPropertyName)),
						CriteriaComparator::OPERATOR_EQUAL, $attributes->get($uniquePerPropertyName));
			}
				
			$counterCriteria->select('COUNT(o)');
			if (0 == $counterCriteria->fetchSingle()) {
				return $newValue;
			}
		}
		return null;
	}
}