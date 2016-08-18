<?php
namespace rocket\script\entity\field\impl;

use n2n\dispatch\option\impl\BooleanOption;
use n2n\core\DynamicTextCollection;
use n2n\dispatch\option\OptionCollection;
use rocket\script\entity\field\DisplayableScriptField;
use n2n\persistence\orm\Entity;
use n2n\dispatch\option\impl\StringOption;
use n2n\util\Attributes;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\mapping\Readable;
use rocket\script\entity\field\ReadableScriptField;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

abstract class DisplayableScriptFieldAdapter extends PropertyScriptFieldAdapter implements DisplayableScriptField, 
		StatelessDisplayable, ReadableScriptField, Readable {
	const OPTION_DISPLAY_IN_LIST_VIEW_KEY = 'displayInListView';
	const OPTION_DISPLAY_IN_DETAIL_VIEW_KEY = 'displayInDetailView';
	const OPTION_DISPLAY_IN_EDIT_VIEW_KEY = 'displayInEditView';
	const OPTION_DISPLAY_IN_ADD_VIEW_KEY = 'displayInAddView';
	const OPTION_HELPTEXT_KEY = 'helpText';
	
	protected $displayInListViewDefault = true;
	protected $displayInDetailViewDefault = true;
	protected $displayInEditViewDefault = true;
	protected $displayInAddViewDefault = true;
	
	/**
	 * @return bool
	 */
	public function isDisplayInListViewEnabled() {
		return $this->attributes->get(self::OPTION_DISPLAY_IN_LIST_VIEW_KEY, $this->displayInListViewDefault);
	}
	
	public function setDisplayInListViewEnabled($displayInListViewEnabled) {
		$this->attributes->set(self::OPTION_DISPLAY_IN_LIST_VIEW_KEY, (bool) $displayInListViewEnabled);
	}
	/**
	 * @return bool
	 */
	public function isDisplayInDetailViewEnabled() {
		return $this->attributes->get(self::OPTION_DISPLAY_IN_DETAIL_VIEW_KEY, $this->displayInDetailViewDefault);
	}
	
	public function setDisplayInDetailViewEnabled($displayInDetailViewEnabled) {
		$this->attributes->set(self::OPTION_DISPLAY_IN_DETAIL_VIEW_KEY, (boolean) $displayInDetailViewEnabled);
	}
	/**
	 * @return bool
	 */
	public function isDisplayInEditViewEnabled() {
		return $this->attributes->get(self::OPTION_DISPLAY_IN_EDIT_VIEW_KEY, $this->displayInEditViewDefault);
	}
	
	public function setDisplayInEditViewEnabled($displayInEditViewEnabled) {
		$this->attributes->set(self::OPTION_DISPLAY_IN_EDIT_VIEW_KEY, (boolean) $displayInEditViewEnabled);
	}
	
	public function isDisplayInAddViewEnabled() {
		if (null !== ($displayInAddView = $this->attributes->get(self::OPTION_DISPLAY_IN_ADD_VIEW_KEY))) {
			return $displayInAddView;
		}
		
		return $this->isDisplayInEditViewEnabled();
	}
	
	public function setDisplayInAddViewEnabled($displayInAddViewEnabled) {
		$this->attributes->set(self::OPTION_DISPLAY_IN_ADD_VIEW_KEY, (boolean) $displayInAddViewEnabled);
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$this->applyDisplayOptions($optionCollection);
		return $optionCollection;
	}
	
	protected function applyDisplayOptions(OptionCollection $optionCollection, $addListOption = true, 
			$addDetailOption = true, $addEditOption = true, $addAddOption = true, $addHelpText = true) {
		$dtc = new DynamicTextCollection('rocket');
		
		if ($addListOption) {
			$optionCollection->addOption(self::OPTION_DISPLAY_IN_LIST_VIEW_KEY,
					new BooleanOption($dtc->translate('script_impl_display_in_overview_label'), $this->displayInListViewDefault));
		}
		
		if ($addDetailOption) {
			$optionCollection->addOption(self::OPTION_DISPLAY_IN_DETAIL_VIEW_KEY,
					new BooleanOption($dtc->translate('script_impl_display_in_detail_view_label'), $this->displayInDetailViewDefault));
		}
		
		if ($addEditOption) {
			$optionCollection->addOption(self::OPTION_DISPLAY_IN_EDIT_VIEW_KEY,
					new BooleanOption($dtc->translate('script_impl_display_in_edit_view_label'), $this->displayInEditViewDefault));
		}
		
		if ($addAddOption) {
			$optionCollection->addOption(self::OPTION_DISPLAY_IN_ADD_VIEW_KEY,
					new BooleanOption($dtc->translate('script_impl_display_in_add_view_label'), $this->displayInAddViewDefault));
		}
		
		if ($addHelpText) {
			$optionCollection->addOption(self::OPTION_HELPTEXT_KEY,
					new StringOption($dtc->translate('script_impl_help_text_label')));
		}
	}
	
	public function getReadable() {
		return $this;
	}
	
	public function getTypeConstraints() {
		$typeConstraint = $this->getPropertyAccessProxy()->getConstraints();
		if ($typeConstraint === null) return null;
		return $typeConstraint->getLenientCopy();
	}
	
	public function read(Entity $entity) {
		return $this->getPropertyAccessProxy()->getValue($entity);
	}
	
	public function createDisplayable(ScriptState $scriptState, Attributes $maskAttributes) {
		return new StatelessDisplayableDecorator($this, $scriptState, $maskAttributes, $this);
	}

	public function getDisplayLabel(ManageInfo $manageInfo) {
		return $this->getLabel();
	}
	
	public function getHtmlContainerAttrs(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$attrs = array('class' => 'rocket-script-' . $this->entityScript->getId() . ' rocket-field-' . $this->getId());
		if (null !== ($helpText = $this->getAttributes()->get(self::OPTION_HELPTEXT_KEY))) {
			$attrs['title'] = $helpText; 
		}
		return $attrs;
	}
}