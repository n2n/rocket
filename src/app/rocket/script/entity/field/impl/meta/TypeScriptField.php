<?php
namespace rocket\script\entity\field\impl\meta;

use rocket\script\entity\field\impl\IndependentScriptFieldAdapter;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\DisplayableScriptField;
use n2n\core\DynamicTextCollection;
use n2n\dispatch\option\impl\BooleanOption;
use rocket\script\entity\manage\ScriptState;
use n2n\util\Attributes;
use rocket\script\entity\manage\display\Displayable;
use rocket\script\core\CompatibilityTest;
use rocket\script\entity\manage\model\EntryModel;

class TypeScriptField extends IndependentScriptFieldAdapter implements DisplayableScriptField, Displayable {
	const OPTION_DISPLAY_IN_LIST_VIEW_KEY = 'displayInListView';
	const OPTION_DISPLAY_IN_LIST_VIEW_DEFAULT = true;
	const OPTION_DISPLAY_IN_DETAIL_VIEW_KEY = 'displayInDetailView';
	const OPTION_DISPLAY_IN_DETAIL_VIEW_DEFAULT = true; 
	
	public function createDisplayable(ScriptState $scriptState, Attributes $maskAttributes) {
		return $this;
	}

	public function getDisplayLabel() {
		return $this->getLabel();
	}
	
	public function createUiOutputField(EntryModel $entryModel, HtmlView $view) {
		return $view->getHtmlBuilder()->getEsc($entryModel->getScriptSelectionMapping()->determineEntityScript()->getLabel());
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		
		$dtc = new DynamicTextCollection('rocket');
		
		$optionCollection->addOption(self::OPTION_DISPLAY_IN_LIST_VIEW_KEY,
				new BooleanOption($dtc->translate('script_impl_display_in_overview_label'), 
						self::OPTION_DISPLAY_IN_LIST_VIEW_DEFAULT));
	
		$optionCollection->addOption(self::OPTION_DISPLAY_IN_DETAIL_VIEW_KEY,
				new BooleanOption($dtc->translate('script_impl_display_in_detail_view_label'),
						self::OPTION_DISPLAY_IN_DETAIL_VIEW_DEFAULT));
		
		return $optionCollection;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\display\Displayable::getHtmlContainerAttrs($scriptState, $scriptSelectionMapping, $maskAttributes)
	 */
	public function getHtmlContainerAttrs(EntryModel $entryModel) {
		return array('class' => 'rocket-script-' . $this->entityScript->getId() . ' rocket-field-' . $this->getId());
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\DisplayableScriptField::isDisplayInListViewEnabled()
	 */
	public function isDisplayInListViewEnabled() {
		return $this->attributes->get(self::OPTION_DISPLAY_IN_LIST_VIEW_KEY, 
				self::OPTION_DISPLAY_IN_LIST_VIEW_DEFAULT);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\DisplayableScriptField::isDisplayInDetailViewEnabled()
	 */
	public function isDisplayInDetailViewEnabled() {
		return $this->attributes->get(self::OPTION_DISPLAY_IN_DETAIL_VIEW_KEY, 
				self::OPTION_DISPLAY_IN_DETAIL_VIEW_DEFAULT);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\DisplayableScriptField::isDisplayInEditViewEnabled()
	 */
	public function isDisplayInEditViewEnabled() {
		return false;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\DisplayableScriptField::isDisplayInAddViewEnabled()
	 */
	public function isDisplayInAddViewEnabled() {
		return false;		
	}
}