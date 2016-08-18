<?php
namespace rocket\script\entity\command\impl\common;

use rocket\script\entity\manage\ScriptState;
use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlView;
use n2n\l10n\Locale;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\impl\common\controller\EditController;
use rocket\script\entity\command\impl\common\controller\PathUtils;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use rocket\script\entity\command\PrivilegeExtendableScriptCommand;
use rocket\script\entity\command\PrivilegedScriptCommand;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

class EditScriptCommand extends IndependentScriptCommandAdapter implements EntryControlComponent, 
		PrivilegedScriptCommand, PrivilegeExtendableScriptCommand {
	const ID_BASE = 'edit';
	const CONTROL_KEY = 'edit';
	const PRIVILEGE_EXT_PUBLISH = 'publish';
	
// 	public function __construct(Attributes $attributes) {
// 		parent::__construct($attributes);
// 	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Edit';
	}
		
	public function createController(ScriptState $scriptState) {
		$editController = new EditController();
		$editController->setEditCommand($this);
		return $editController;
	}
	
	public function getEntryControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::CONTROL_KEY => $dtc->translate('common_edit_label'));
	}
	
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView) {
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		if ($this->equals($scriptState->getExecutedScriptCommand())) {
			return array();
		}
		
		$request = $htmlView->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
		
		$previewType = null;
		if ($this->getEntityScript()->isEditablePreviewAvailable()) {
			$previewType = $scriptState->getPreviewType();
		}
				
		$label = null;
		$tooltip = null;
		if ($scriptSelection->hasDraft()) {
			$label = $dtc->translate('script_cmd_edit_draft_label');
			$tooltip = $dtc->translate('script_cmd_edit_draft_tooltip');
		} else {
			$label = $dtc->translate('common_edit_label');
			$tooltip = $dtc->translate('script_impl_edit_entry_tooltip', 
					array('entry' => $scriptState->getScriptMask()->getLabel()));
		}

		$pathExt = null;
		if ($scriptState->hasScriptSelection()) {
			$pathExt = PathUtils::createPathExtFromScriptNavPoint($this->getId(), $scriptSelection->toNavPoint($previewType));
		} else {
			$pathExt = PathUtils::createDraftPathExt($this->getId(), $scriptSelection->getId(), null, null, $previewType);
		}
		
		return array(self::CONTROL_KEY => new ControlButton(
				$request->getControllerContextPath($scriptState->getControllerContext(), $pathExt),
				$label, $tooltip, true, ControlButton::TYPE_WARNING, IconType::ICON_PENCIL));
	}
	
	public function getPrivilegeLabel(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return $dtc->translate('common_edit_label'); 
	}
	
	public function getPrivilegeExtOptions(Locale $locale) {
		if (!$this->getEntityScript()->isDraftEnabled()) return array();
		
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::PRIVILEGE_EXT_PUBLISH => $dtc->translate('script_cmd_edit_privilege_publish_label'));
	}
}