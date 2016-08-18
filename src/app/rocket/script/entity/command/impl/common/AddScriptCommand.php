<?php
namespace rocket\script\entity\command\impl\common;

use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\ScriptState;
use n2n\l10n\Locale;
use rocket\script\entity\command\impl\common\controller\AddController;
use rocket\script\entity\command\control\OverallControlComponent;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;
use rocket\script\entity\command\PrivilegedScriptCommand;

class AddScriptCommand extends IndependentScriptCommandAdapter implements OverallControlComponent, PrivilegedScriptCommand {
	const ID_BASE = 'rocket-add';
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Add (Rocket)';
	}

	public function getPrivilegeLabel(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return $dtc->translate('common_add_label');
	}
	
	public function createController(ScriptState $scriptState) {
		return new AddController();
	}
	
	public function getOverallControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket');
		
		return array('add' => $dtc->translate('common_add_label'));
	}
	
	public function createOverallControlButtons(ScriptState $scriptState, HtmlView $htmlView) {
		$request = $htmlView->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
		
		$url = $request->getControllerContextPath($scriptState->getControllerContext(),
				self::ID_BASE);
		$name = $dtc->translate('common_add_label');
		$tooltip = $dtc->translate('script_impl_add_entry_tooltip', array('entry' => $scriptState->getScriptMask()->getLabel()));
		
		return array('add' => new ControlButton($url, $name, $tooltip, true, ControlButton::TYPE_SUCCESS,
				IconType::ICON_PLUS_CIRCLE));
	}
}