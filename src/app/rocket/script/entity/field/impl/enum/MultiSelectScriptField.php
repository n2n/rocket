<?php
namespace rocket\script\entity\field\impl\enum;

use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\property\DefaultProperty;
use n2n\reflection\property\TypeConstraints;
use n2n\dispatch\option\impl\OptionCollectionArrayOption;
use n2n\dispatch\option\impl\StringOption;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\dispatch\option\impl\MultiSelectOption;
use n2n\ui\html\HtmlView;
use rocket\script\core\SetupProcess;
use n2n\reflection\property\ConstraintsConflictException;
use rocket\script\core\CompatibilityTest;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\dispatch\option\impl\IntegerOption;
use rocket\script\entity\field\impl\ManageInfo;

class MultiSelectScriptField extends TranslatableScriptFieldAdapter {
	const OPTION_OPTIONS = 'options';
	const OPTION_OPTIONS_LABEL = 'label';
	const OPTION_OPTIONS_VALUE = 'value';
	const OUTPUT_SEPARATOR = ', ';
	const OPTION_MIN_KEY = 'min';
	const OPTION_MAX_KEY = 'max';
	
	public function setup(SetupProcess $setupProcess) {
		try {
			$this->propertyAccessProxy->setConstraints(new TypeConstraints(null, true, false, false));
		} catch (ConstraintsConflictException $e) {
			$setupProcess->failedE($this, $e);
		}
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function checkCompatibility(CompatibilityTest $compatibilityTest) {
		if (!$this->isCompatibleWith($compatibilityTest->getEntityProperty())) {
			$compatibilityTest->entityPropertyTestFailed();
			return;
		}
	
		$propertyConstraints = $compatibilityTest->getPropertyAccessProxy()->getConstraints();
		$requiredConstraints = new TypeConstraints(null, true, false, false);
		if ($propertyConstraints !== null && !$propertyConstraints->arePassableBy($requiredConstraints)) {
			$compatibilityTest->propertyTestFailed('ScriptField can not pass Type ' . $requiredConstraints->__toString()
					. ' to property due to incompatible TypeConstraints ' . $propertyConstraints->__toString());
		}
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$optionCollection->addOption(self::OPTION_OPTIONS, new OptionCollectionArrayOption('Options', function() {
			$optionCollection = new OptionCollectionImpl();
			$optionCollection->addOption(self::OPTION_OPTIONS_LABEL, new StringOption('Label'));
			$optionCollection->addOption(self::OPTION_OPTIONS_VALUE, new StringOption('Value'));
			return $optionCollection;
		}));
		$optionCollection->addOption(self::OPTION_MIN_KEY, new IntegerOption('Min'));
		$optionCollection->addOption(self::OPTION_MAX_KEY, new IntegerOption('Max'));
		return $optionCollection;
	}
	
	public function getOptions() {
		$options = array();
		foreach ((array) $this->attributes->get(self::OPTION_OPTIONS) as $attrs) {
			if (isset($attrs[self::OPTION_OPTIONS_VALUE]) && isset($attrs[self::OPTION_OPTIONS_LABEL])) {
				$options[$attrs[self::OPTION_OPTIONS_VALUE]] = $attrs[self::OPTION_OPTIONS_LABEL];
			}
		}
		return $options;
	}
	
	public function getMin() {
		return $this->attributes->get(self::OPTION_MIN_KEY, 0);
	}
	
	public function getMax() {
		return $this->attributes->get(self::OPTION_MAX_KEY);
	}
	
	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return $this->getMin() > 0;
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new MultiSelectOption($this->getLabel(), $this->getOptions(), array(), 
				$this->getMin(), $this->getMax());
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\ScriptField::getTypeName()
	 */
	public function getTypeName() {
		return 'MultiSelect';
		
	}

	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\display\Displayable::createUiOutputField()
	 */
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view,
			ManageInfo $manageInfo) {
		return $view->getHtmlBuilder()->getEsc(
				implode(self::OUTPUT_SEPARATOR, (array)$scriptSelectionMapping->getValue($this->id)));
	}
	
}