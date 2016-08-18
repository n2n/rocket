<?php
namespace rocket\script\entity\command\impl\tree;

use rocket\script\entity\command\impl\tree\controller\TreeDeleteController;
use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\ScriptState;
use n2n\l10n\Locale;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;
use rocket\script\entity\command\impl\common\controller\PathUtils;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

class TreeDeleteScriptCommand extends IndependentScriptCommandAdapter implements EntryControlComponent {
	const ID_BASE = 'tree-delete';
	const CONTROL_BUTTON_KEY = 'delete'; 
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Tree Delete';
	}
		
	public function createController(ScriptState $scriptState) {
		$treeDeleteController = new TreeDeleteController();
		TreeUtils::initializeController($this, $treeDeleteController);
		return $treeDeleteController;
	}
	
	public function getEntryControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::ID_BASE => $dtc->translate('script_impl_delete_branch_label'));
	}
	
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView) {
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$request = $htmlView->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
	
		
		$pathExt = null;
		$name = null;
		$tooltip = null;
		$confirmMessage = null;
		if ($scriptSelection->hasDraft()) {
			$draft = $scriptSelection->getDraft();
			$pathExt = PathUtils::createDraftPathExt($this->getId(), $scriptSelection->getId(), $draft->getId());
			$name = $dtc->translate('script_cmd_tree_delete_draft_label');
			$tooltip = $dtc->translate('script_cmd_tree_delete_draft_tooltip',
					array('last_mod' => $htmlView->getL10nDateTime($draft->getLastMod())));
			$confirmMessage = $dtc->translate('script_cmd_tree_delete_draft_confirm_message',
					array('last_mod' => $htmlView->getL10nDateTime($draft->getLastMod())));
		}  else {
			$pathExt = PathUtils::createPathExt($this->getId(), $scriptSelection->getId());
			$knownString = $this->getEntityScript()->createKnownString($scriptSelection->getOriginalEntity(), $request->getLocale());
			$name = $dtc->translate('script_impl_delete_branch_label');
			$tooltip = $dtc->translate('script_impl_delete_branch_tooltip', array('entry' => $knownString));
			$confirmMessage = $dtc->translate('script_cmd_tree_delete_confirm_message', array('entry' => $knownString));
		}
		
		
		
		$scriptCommandButton = new ControlButton(
				$request->getControllerContextPath($scriptState->getControllerContext(), $pathExt, array('previewtype' => $scriptState->getPreviewType())),
				$name, $tooltip, false, ControlButton::TYPE_DEFAULT,
				IconType::ICON_TIMES);
		$scriptCommandButton->setConfirmMessage($confirmMessage);
		$scriptCommandButton->setConfirmOkButtonLabel($dtc->translate('common_yes_label'));
		$scriptCommandButton->setConfirmCancelButtonLabel($dtc->translate('common_no_label'));
		
		return array(self::CONTROL_BUTTON_KEY => $scriptCommandButton);
	}
}