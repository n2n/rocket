<?php
namespace rocket\script\entity\command\impl\numeric;

use n2n\core\DynamicTextCollection;
use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlView;
use rocket\script\entity\field\impl\numeric\OrderScriptField;
use n2n\http\Request;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\impl\numeric\controller\OrderController;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\impl\ScriptCommandAdapter;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

class OrderScriptCommand extends ScriptCommandAdapter implements EntryControlComponent {
	const ID_BASE = 'order';
	const CONTROL_UP_KEY = 'up';
	const CONTROL_DOWN_KEY = 'down';
	
	private $orderScriptField;
		
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Move';
	}
	
	public function setOrderScriptField(OrderScriptField $orderScriptField) {
		$this->orderScriptField = $orderScriptField;
	}
		
	public function createController(ScriptState $scriptState) {
		$controller = new OrderController($scriptState);
		$controller->setOrderScriptField($this->orderScriptField);
		return $controller;
	}
	
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView) {
		// Don't show command buttons in edit and detail view
		if ($scriptState->hasScriptSelection()) return array();
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$request = $htmlView->getRequest();
		$request instanceof Request;
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
	
		$previewType = null;
		if ($this->getEntityScript()->isEditablePreviewAvailable()) {
			$previewType = $scriptState->getPreviewType();
		}
	
		return array(self::CONTROL_UP_KEY => new ControlButton(
						$request->getControllerContextPath($scriptState->getControllerContext(), 
								array($this->getId(), 'up', $scriptSelection->getId())),
						$dtc->translate('script_cmd_tree_move_up_label'), $dtc->translate('script_cmd_tree_move_up_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_ARROW_UP),
				self::CONTROL_DOWN_KEY => new ControlButton(
						$request->getControllerContextPath($scriptState->getControllerContext(), 
								array($this->getId(), 'down', $scriptSelection->getId())),
						$dtc->translate('script_cmd_tree_move_down_label'), $dtc->translate('script_cmd_tree_move_down_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_ARROW_DOWN));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(\n2n\l10n\Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::CONTROL_UP_KEY => $dtc->translate('script_cmd_tree_move_up_label'),
				self::CONTROL_DOWN_KEY => $dtc->translate('script_cmd_tree_move_down_label'));
	}
}