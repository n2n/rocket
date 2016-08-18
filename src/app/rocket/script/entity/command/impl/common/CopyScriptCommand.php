<?php
namespace rocket\script\entity\command\impl\common;

use rocket\script\entity\manage\ScriptState;
use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlView;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\impl\common\controller\PathUtils;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\impl\common\controller\CopyController;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;

class CopyScriptCommand extends IndependentScriptCommandAdapter implements EntryControlComponent {
	const ID_BASE = 'rocket-copy';
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Copy (Rocket)';
	}
	
	public function createController(ScriptState $scriptState) {
		$copyController = new CopyController($scriptState);
		$copyController->setEntityScript($this->getEntityScript());
		return $copyController;
	}
	
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView) {
		$request = $htmlView->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
		
		$url = $request->getControllerContextPath($scriptState->getControllerContext(), 
						PathUtils::createDetailPathExtFromScriptSelection(self::ID_BASE, $scriptSelection, $scriptState->getPreviewType()));
		$name = $dtc->translate('script_cmd_copy_label');
		$tooltip = $dtc->translate('script_cmd_copy_tooltip');
		
		return array(self::ID_BASE => new ControlButton($url, $name, $tooltip, true, ControlButton::TYPE_SUCCESS,
				IconType::ICON_COPY));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(\n2n\l10n\Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
		return array(self::ID_BASE => $dtc->translate('script_cmd_copy_label'));
	}

}