<?php
namespace rocket\script\entity\field\impl\meta;

use n2n\ui\html\HtmlView;
use n2n\persistence\orm\property\DefaultProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use rocket\script\core\SetupProcess;
use n2n\N2N;
use n2n\util\Attributes;
use n2n\persistence\orm\NestedSetUtils;
use rocket\script\entity\field\impl\string\UrlPartScriptField;
use n2n\core\DynamicTextCollection;
use n2n\dispatch\option\impl\EnumOption;
use n2n\l10n\Locale;
use n2n\core\N2nContext;
use n2n\persistence\orm\OrmUtils;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\SimpleSelectorConstraint;
use rocket\script\entity\manage\display\Editable;
use rocket\script\entity\field\impl\ManageInfo;
use rocket\script\entity\command\impl\tree\field\TreeRootIdScriptField;
use rocket\script\entity\command\impl\tree\field\TreeLeftScriptField;
use rocket\script\entity\command\impl\tree\field\TreeRightScriptField;

class SubsystemScriptField extends TranslatableScriptFieldAdapter {
	
	private $subsystems;
	private $scriptManager;
	
	public function setup(SetupProcess $setupProcess) {
		$this->scriptManager = $setupProcess->getScriptManager();
		$subsystemConfigs = N2N::getAppConfig()->http()->getSubystemConfigs();
		$this->subsystems = (count($subsystemConfigs) > 0) ? 
				array(null => 'Alle Subsysteme') : array();
		
		foreach ($subsystemConfigs as $subsystemConfig) {
			$displayName = $subsystemConfig->getName();
			$displayName .= ' (' .$subsystemConfig->getHostName();
			if (null !== $contextPath = $subsystemConfig->getContextPath()) {
				$displayName .= '/' . $subsystemConfig->getContextPath();
			}
			$displayName .= ')';
			$this->subsystems[$subsystemConfig->getName()] = $displayName;
		}
	}
	
	public function getTypeName() {
		return 'Subsystem';
	}
	
	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		if (empty($this->subsystems)) return false;
		return parent::isRequired($scriptSelectionMapping, $manageInfo);
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function isDisplayInAddViewEnabled() {
		if (empty($this->subsystems)) return false;
		return parent::isDisplayInAddViewEnabled();
	}
	
	public function isDisplayInEditViewEnabled() {
		if (empty($this->subsystems)) return false;
		return parent::isDisplayInEditViewEnabled();
	}
	
	public function isDisplayInDetailViewEnabled() {
		if (empty($this->subsystems)) return false;
		return parent::isDisplayInDetailViewEnabled();
	}
	
	public function isDisplayInListViewEnabled() {
		if (empty($this->subsystems)) return false;
		return parent::isDisplayInListViewEnabled();
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo) {
		$html = $view->getHtmlBuilder();
		$subsystemName = $this->read($scriptSelectionMapping->getScriptSelection()->getEntity());
		if ($manageInfo->hasListModel() || !isset($this->subsystems[$subsystemName])) {
			return $html->getEsc($subsystemName);
		}
		return $html->getEsc($this->subsystems[$subsystemName]);
	}
	
	public function optionAttributeValueToPropertyValue(Attributes $attributes,
			ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {

		$newValue = $attributes->get($this->id);
		$oldValue = $scriptSelectionMapping->getValue($this->id);
	
		$scriptSelectionMapping->setValue($this->id, $attributes->get($this->id));
		$dependantUrlpartScriptFields = $this->determineDependantUrlPartScriptFields();
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();

		if (count($dependantUrlpartScriptFields) === 0 || $scriptSelection->isNew() 
				|| $oldValue === $newValue) return;
		
		if ($this->isTreeScript()) {
			//set Subsystem for Childelements and 
			$accessProxy = $this->getEntityProperty()->getAccessProxy();
			$currentEntity = $scriptSelection->getCurrentEntity();
			$topEntityScript = $this->getEntityScript()->getTopEntityScript();
			
			$em = $manageInfo->getScriptState()->getEntityManager();
			$nestedSetUtils = new NestedSetUtils($em, $this->getEntityScript()->getEntityModel()->getClass());
			foreach ($nestedSetUtils->fetchNestedSetItems($currentEntity, true) as $nsItem) {
				$object = $nsItem->getObject();
				if ($accessProxy->getValue($object) !== $oldValue) {
					continue;
				}
				$accessProxy->setValue($object, $newValue);
				foreach ($dependantUrlpartScriptFields as $scriptField) {
					$urlPartAccessProxy = $scriptField->getPropertyAccessProxy();
					$oldUrlPartValue = $urlPartAccessProxy->getValue($object);
					$objAttributes = $this->createAttributesForObject($object);
					$objAttributes->set($this->getPropertyName(), $newValue);
					$newUrlpartValue = $scriptField->determineUrlPart($em, $oldUrlPartValue, $oldUrlPartValue, $objAttributes, 
							new ScriptSelection(OrmUtils::extractId($object), $object));
					$scriptField->getPropertyAccessProxy()->setValue($object, $newUrlpartValue);
				}
				$em->flush();
			}
		}
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$attrs = array();
		if (!$scriptSelectionMapping->getScriptSelection()->isNew()) {
			$attrs['class'] = 'rocket-critical-input';
			$dtc = new DynamicTextCollection(array('page', 'rocket'));
			$attrs['data-confirm-message'] = $dtc->translate('script_field_subsystem_unlock_confirm_message');
			$attrs['data-edit-label'] =  $dtc->translate('common_edit_label');
			$attrs['data-cancel-label'] =  $dtc->translate('common_cancel_label');
		} else {
			$scriptState = $manageInfo->getScriptState();
			$cmds = $scriptState->getControllerContext()->getCmds();
			if (null !== ($entity = $scriptState->getEntityManager()->find(
					$this->getEntityScript()->getEntityModel()->getClass(), end($cmds)))) {
				$scriptSelectionMapping->setValue($this->getId(), $this->getPropertyAccessProxy()->getValue($entity));
			}
		}
		return new EnumOption($this->getLabel(), $this->subsystems, null, 
				$this->isRequired($scriptSelectionMapping, $manageInfo), $attrs);
	}
	
	public function createRestrictionOptionCollection(Locale $locale, N2nContext $n2nContext) {
		$optionCollection = new OptionCollectionImpl();
		$optionCollection->addOption('restrictedSubsystemName', new EnumOption('SubsystemName', 
				$this->subsystems));
		return $optionCollection;
	}
	
	public function createRestrictionSelectionConstraint(Attributes $restrictionAttributes, ScriptState $scriptState) {
		$restrictedSubsystemName = $restrictionAttributes->get('restrictedSubsystemName');
		if ($restrictedSubsystemName === null) return null;
	
		$targetEntityModel = $this->targetEntityScript->getEntityModel();
		return new SimpleSelectorConstraint($this->getEntityProperty(),
				$scriptState->getEntityManager()->find(
						$targetEntityModel->getClass(), $restrictedSubsystemName),
				function ($value1, $value2) use ($targetEntityModel) {
					return OrmUtils::areObjectsEqual($value1, $value2, $targetEntityModel);
				});
	}
	
	private function createAttributesForObject($object) {
		$attributes = new Attributes();
		foreach ($this->getEntityScript()->getTopEntityScript()->getFieldCollection() as $scriptField) {
			if ($scriptField instanceof Editable) {
				$accessProxy = $scriptField->getPropertyAccessProxy();
				$attributes->set($scriptField->getPropertyName(), $accessProxy->getValue($object));
			} 
		}
		return $attributes;
	}
	
	private function isTreeScript() {
		foreach ($this->getEntityScript()->getFieldCollection() as $field) {
			if ($field instanceof TreeRootIdScriptField) return true;
			if ($field instanceof TreeLeftScriptField) return true;
			if ($field instanceof TreeRightScriptField) return true;
		}
		return false;
	}
	
	private function determineDependantUrlPartScriptFields() {
		$dependantUrlPartScriptFields = array();
		foreach ($this->getEntityScript()->getTopEntityScript()->getFieldCollection() as $scriptField) {
			if (!$scriptField instanceof UrlPartScriptField ||
					$this->getPropertyName() !== $scriptField->getUniquePerPropertyName()) continue;
			$dependantUrlPartScriptFields[] = $scriptField;
		}
		return $dependantUrlPartScriptFields;
	}
}