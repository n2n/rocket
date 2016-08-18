<?php
namespace rocket\script\entity\command\impl\tree;

use rocket\script\entity\command\impl\tree\controller\TreeAddController;
use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\ScriptState;
use n2n\l10n\Locale;
use rocket\script\entity\command\control\OverallControlComponent;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\command\PrivilegedScriptCommand;

class TreeAddScriptCommand extends IndependentScriptCommandAdapter implements OverallControlComponent, 
		EntryControlComponent, PrivilegedScriptCommand {
	const ID_BASE = 'tree-add';
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getOverviewPathExt() {
		return '/' . $this->getId();
	}
	
	public function getTypeName() {
		return 'Tree Add (Rocket)';
	}
	
	public function createController(ScriptState $scriptState) {
		$treeAddController = new TreeAddController();
		TreeUtils::initializeController($this, $treeAddController);
		return $treeAddController;
	}
	
	public function getOverallControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::ID_BASE => $dtc->translate('script_impl_add_child_label'));
	}
	
	public function createOverallControlButtons(ScriptState $scriptState, HtmlView $htmlView) {
		$request = $htmlView->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
		
		$url = $request->getControllerContextPath($scriptState->getControllerContext(), $this->getId());
		$name = $dtc->translate('script_impl_add_root_label');
		$tooltip = $dtc->translate('script_impl_add_root_tooltip');
		
		return array(new ControlButton($url, $name, $tooltip, true, ControlButton::TYPE_SUCCESS,
				IconType::ICON_PLUS_CIRCLE));
	}
	
	public function getEntryControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::ID_BASE => $dtc->translate('script_impl_add_child_label'));
	}

	public function getPrivilegeLabel(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return $dtc->translate('script_cmd_tree_add_privilege_label');
	}
	
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView) {
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$request = $htmlView->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
	
		return array(new ControlButton(
				$request->getControllerContextPath($scriptState->getControllerContext(),
						array($this->getId(), $scriptSelection->getId())),
				$dtc->translate('script_impl_add_child_label'),  $dtc->translate('script_impl_add_child_tooltip'),
				true, ControlButton::TYPE_DEFAULT, IconType::ICON_PLUS_CIRCLE));
	}
	
}