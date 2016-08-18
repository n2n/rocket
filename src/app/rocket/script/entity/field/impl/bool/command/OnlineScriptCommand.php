<?php
namespace rocket\script\entity\field\impl\bool\command;

use rocket\script\entity\field\impl\bool\OnlineScriptField;
use n2n\core\DynamicTextCollection;
use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlView;
use rocket\script\entity\command\control\EntryControlComponent;
use n2n\l10n\Locale;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\impl\ScriptCommandAdapter;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

class OnlineScriptCommand extends ScriptCommandAdapter implements EntryControlComponent {
	const CONTROL_KEY = 'online_status';
	const ID_BASE = 'online-status';
	
	private $onlineScriptField;
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Online Status';
	}
	
	public function setOnlineScriptField(OnlineScriptField $onlineScriptField) {
		$this->onlineScriptField = $onlineScriptField;
	}
		
	public function createController(ScriptState $scriptState) {
		$controller = new OnlineController($scriptState);
		$controller->setOnlineScriptField($this->onlineScriptField);
		return $controller;
	}
	
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view) {
		$request = $view->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		
		$label = '';
		$tooltip = '';
		$icon = '';
		$type = null;
		$path = '';
		$ref = $scriptState->getControllerContext()->toPathExt(true);
		if ($this->onlineScriptField->read($scriptSelection->getEntity())) {
			$label = $dtc->translate('script_impl_online_label');
			$tooltip = $dtc->translate('script_impl_set_offline_tooltip');
			$iconType = IconType::ICON_CHECK_CIRCLE;
			$type = ControlButton::TYPE_SUCCESS;
			$path = $request->getControllerContextPath($scriptState->getControllerContext(), 
					array($this->getId(), OnlineController::ACTION_OFFLINE, $scriptSelection->getId()), 
					array('ref' => $ref));
		} else {
			$label = $dtc->translate('script_impl_offline_label');
			$tooltip = $dtc->translate('script_impl_set_online_tooltip');
			$iconType = IconType::ICON_MINUS_CIRCLE;
			$type = ControlButton::TYPE_DANGER;
			$path = $request->getControllerContextPath($scriptState->getControllerContext(), 
					array($this->getId(), $scriptSelection->getId()), 
					array('ref' => $ref));
		}
	
		return array(self::CONTROL_KEY => new ControlButton($path, $label, $tooltip, false, $type, $iconType));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::CONTROL_KEY => $dtc->translate('script_cmd_online_set_label'));
	}
}