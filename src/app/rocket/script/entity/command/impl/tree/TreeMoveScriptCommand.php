<?php
namespace rocket\script\entity\command\impl\tree;

use rocket\script\entity\manage\ScriptState;
use n2n\ui\html\HtmlView;
use n2n\core\DynamicTextCollection;
use rocket\script\entity\command\impl\tree\controller\TreeMoveController;
use n2n\l10n\Locale;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

class TreeMoveScriptCommand extends IndependentScriptCommandAdapter implements EntryControlComponent {
	const ID_BASE = 'tree-move';
	const CONTROL_MOVE_KEY = 'moveControlButton';
	const CONTROL_MOVE_UP_KEY = 'moveUp';
	const CONTROL_MOVE_DOWN_KEY = 'moveDown';
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getOverviewPathExt() {
		return $this->getId();
	}
	
	public function getTypeName() {
		return 'Tree Move (Rocket)';
	}
	
	public function createController(ScriptState $scriptState) {
		$treeMoveController = new TreeMoveController($scriptState);
		TreeUtils::initializeController($this, $treeMoveController);
		return $treeMoveController;
	}
	
	public function getEntryControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::CONTROL_MOVE_KEY => $dtc->translate('script_cmd_tree_move_label'),
				self::CONTROL_MOVE_UP_KEY => $dtc->translate('script_cmd_tree_move_up_label'),
				self::CONTROL_MOVE_DOWN_KEY => $dtc->translate('script_cmd_tree_move_down_label'));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\control\EntryControlComponent::createEntryControlButtons()
	 */
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView) {
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		
		if ($scriptState->hasScriptSelection() || $scriptSelection->hasDraft() || $scriptSelection->hasTranslation()) {
			return array();
		}
		
		$request = $htmlView->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
		
		$moveControlButton = new ControlButton(
				$request->getControllerContextPath($scriptState->getControllerContext(),
						array($this->getId(), 'move', $scriptSelection->getId())),
				$dtc->translate('script_cmd_tree_move_label'), $dtc->translate('script_cmd_tree_move_tooltip'),
				true, ControlButton::TYPE_DEFAULT, IconType::ICON_SITEMAP);
		
		$moveUpControlButton = new ControlButton(
				$request->getControllerContextPath($scriptState->getControllerContext(),
						array($this->getId(), 'moveup', $scriptSelection->getId())),
				$dtc->translate('script_cmd_tree_move_up_label'), $dtc->translate('script_cmd_tree_move_up_tooltip'),
				true, ControlButton::TYPE_INFO, IconType::ICON_ARROW_UP);
		
		$moveDownControlButton = new ControlButton(
				$request->getControllerContextPath($scriptState->getControllerContext(),
						array($this->getId(), 'movedown', $scriptSelection->getId())),
				$dtc->translate('script_cmd_tree_move_down_label'), $dtc->translate('script_cmd_tree_move_down_tooltip'),
				true, ControlButton::TYPE_INFO, IconType::ICON_ARROW_DOWN);
		
		return array(self::CONTROL_MOVE_KEY => $moveControlButton, self::CONTROL_MOVE_UP_KEY => $moveUpControlButton, 
				self::CONTROL_MOVE_DOWN_KEY => $moveDownControlButton);
	}
}