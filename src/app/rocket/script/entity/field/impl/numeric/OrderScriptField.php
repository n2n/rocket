<?php
namespace rocket\script\entity\field\impl\numeric;

use rocket\script\core\SetupProcess;
use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlView;
use n2n\dispatch\option\impl\IntegerOption;
use n2n\persistence\orm\property\DefaultProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\filter\item\TextFilterItem;
use rocket\script\entity\command\impl\numeric\OrderScriptCommand;
use rocket\script\entity\field\EntityPropertyScriptField;
use rocket\script\entity\UnknownScriptElementException;
use rocket\script\entity\filter\item\SimpleFilterItem;
use n2n\core\N2nContext;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\filter\item\SimpleSortItem;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\util\Attributes;
use rocket\script\entity\modificator\impl\numeric\OrderScriptModificator;
use n2n\dispatch\option\impl\EnumOption;
use n2n\persistence\orm\criteria\Criteria;

class OrderScriptField extends IntegerScriptField {
	protected $orderScriptCommand;
	
	const ORDER_INCREMENT = 10;
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		
		$this->displayInListViewDefault = false;
		$this->displayInEditViewDefault = false;
		$this->displayInAddViewDefault = false;
		$this->optionRequiredDefault = false;
	}
	
	public function getTypeName() {
		return 'Order Index';
	}
	/**
	 * @return \rocket\script\entity\field\EntityPropertyScriptField
	 */
	public function getReferenceField() {
		if (null != ($referenceFieldId = $this->getAttributes()->get('reference'))) {
			try {
				return $this->getEntityScript()->getFieldCollection()->getById($referenceFieldId);
			} catch (UnknownScriptElementException $e) {
				return null;
			}
		}
		return null;
	}
	
	public function createOptionCollection() {
		$optionCollection = new OptionCollectionImpl();
		
		$this->applyDisplayOptions($optionCollection, true, true, true, true, true);
		
		$this->applyDraftOptions($optionCollection);
		$this->applyTranslationOptions($optionCollection);
		$optionCollection->addOption('reference', new EnumOption('Reference Field', $this->generateReferenceEnumOptions()));
		return $optionCollection;
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		$this->orderScriptCommand = new OrderScriptCommand($this->getEntityScript());
		$this->orderScriptCommand->setOrderScriptField($this);
		$entityScript = $this->getEntityScript();
		$entityScript->getCommandCollection()->add($this->orderScriptCommand, true);
		$entityScript->getModificatorCollection()->add(new OrderScriptModificator($this));
 		if (count($entityScript->getDefaultSortDirections()) === 0) {
 			$entityScript->setDefaultSortDirections(array($this->id => Criteria::ORDER_DIRECTION_ASC));
 		}
	}

	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo)  {
		return $view->getHtmlBuilder()->getEsc($scriptSelectionMapping->getValue($this->id));
	}

	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
	}

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new IntegerOption($this->getLabel(), null,
				$this->isRequired($scriptSelectionMapping, $manageInfo), null, null,
				array('placeholder' => $this->getLabel()));
	}

	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new TextFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale()));
	}
	
	private function generateReferenceEnumOptions() {
		$referenceFields = array();
		foreach ($this->getEntityScript()->getFieldCollection()->combineAll() as $scriptField) {
			if (!($scriptField instanceof EntityPropertyScriptField)) continue;
			$referenceFields[$scriptField->getId()] = $scriptField->getLabel();
		}
		return $referenceFields;
	}
}