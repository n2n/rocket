<?php
namespace rocket\script\entity\field\impl\file\command;

use rocket\script\entity\command\impl\ScriptCommandAdapter;
use rocket\script\entity\manage\ScriptState;
use n2n\l10n\Locale;
use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlView;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\field\impl\file\MultiUploadFileScriptField;
use rocket\script\entity\field\impl\file\command\controller\MultiUploadScriptController;
use rocket\script\entity\command\control\OverallControlComponent;

class MultiUploadScriptCommand extends ScriptCommandAdapter implements OverallControlComponent {

	const MULTI_UPLOAD_KEY = 'multi-upload';
	/**
	 * @var \rocket\script\entity\field\impl\file\MultiUploadFileScriptField
	 */
	private $scriptField;
	
	public function setScriptField(MultiUploadFileScriptField $scriptField) {
		$this->scriptField = $scriptField;
	}

	public function createController(ScriptState $scriptState) {
		$controller = new MultiUploadScriptController();
		$controller->setScriptField($this->scriptField);
		return $controller;
	}
	
	public function getOverallControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket');
		return array(self::MULTI_UPLOAD_KEY => $dtc->translate('script_impl_multi_upload_label'));
	}

	public function createOverallControlButtons(ScriptState $scriptState, HtmlView $view) {
		$request = $view->getRequest();
		$dtc = new DynamicTextCollection('rocket');
		
		$url = $request->getControllerContextPath($scriptState->getControllerContext(), array($this->getId()));
		
		$name = $dtc->translate('script_impl_multi_upload_label');
		$tooltip = $dtc->translate('script_impl_multi_upload_tooltip');
		
		return array(self::MULTI_UPLOAD_KEY => new ControlButton($url, $name, $tooltip, true, ControlButton::TYPE_DEFAULT,
				IconType::ICON_UPLOAD));
	}

}