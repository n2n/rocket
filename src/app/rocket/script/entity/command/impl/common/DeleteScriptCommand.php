<?php
namespace rocket\script\entity\command\impl\common;

use n2n\N2N;
use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\ScriptState;
use n2n\l10n\Locale;
use rocket\script\entity\command\control\PartialControlComponent;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\impl\common\controller\DeleteController;
use rocket\script\entity\command\impl\common\controller\PathUtils;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\command\PrivilegedScriptCommand;

class DeleteScriptCommand extends IndependentScriptCommandAdapter implements PartialControlComponent, 
		EntryControlComponent, PrivilegedScriptCommand {
	const ID_BASE = 'delete';
	const CONTROL_BUTTON_KEY = 'delete'; 
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Delete';
	}
		
	public function createController(ScriptState $scriptState) {
		return new DeleteController();
	}
	
	public function createEntryControlButtons(ScriptState $scriptState, 
			ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView) {
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
			$name = $dtc->translate('script_cmd_delete_draft_label');
			$tooltip = $dtc->translate('script_cmd_delete_draft_tooltip', 
					array('last_mod' => $htmlView->getL10nDateTime($draft->getLastMod())));
			$confirmMessage = $dtc->translate('script_cmd_delete_draft_confirm_message', 
					array('last_mod' => $htmlView->getL10nDateTime($draft->getLastMod())));
		} else {
			$pathExt = PathUtils::createPathExt($this->getId(), $scriptSelection->getId());
			$knownString = $this->getEntityScript()->createKnownString($scriptSelection->getOriginalEntity(), $request->getLocale());
			$name = $dtc->translate('common_delete_label');
			$tooltip = $dtc->translate('script_impl_delete_entry_tooltip', array('entry' => $scriptState->getScriptMask()->getLabel()));
			$confirmMessage = $dtc->translate('script_impl_delete_entry_confirm', array('entry' => $knownString));
		}
		
		$controlButton = new ControlButton(
				$request->getControllerContextPath($scriptState->getControllerContext(), $pathExt, 
						array('previewtype' => $scriptState->getPreviewType())),
				$name, $tooltip, false, ControlButton::TYPE_DEFAULT, IconType::ICON_TIMES);
		$controlButton->setConfirmMessage($confirmMessage);
		$controlButton->setConfirmOkButtonLabel($dtc->translate('common_yes_label'));
		$controlButton->setConfirmCancelButtonLabel($dtc->translate('common_no_label'));
		
		return array(self::CONTROL_BUTTON_KEY => $controlButton);
	}
	
	public function getEntryControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket');
		
		return array(self::CONTROL_BUTTON_KEY => $dtc->translate('script_cmd_delete_draft_label'));
	}
	
	public function createPartialControlButtons(ScriptState $scriptState, HtmlView $htmlView) {
		$dtc = new DynamicTextCollection('rocket', $htmlView->getRequest()->getLocale());
		$scriptCommandButton = new ControlButton(null, $dtc->translate('script_cmd_partial_delete_label'), 
				$dtc->translate('script_cmd_partial_delete_tooltip'), false, ControlButton::TYPE_DEFAULT,
				IconType::ICON_TIMES_SIGN);
		$scriptCommandButton->setConfirmMessage($dtc->translate('script_cmd_partial_delete_confirm_message'));
		$scriptCommandButton->setConfirmOkButtonLabel($dtc->translate('common_yes_label'));
		$scriptCommandButton->setConfirmCancelButtonLabel($dtc->translate('common_no_label'));
		
		return array(self::CONTROL_BUTTON_KEY => $scriptCommandButton);
	}
	
	public function getPartialControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket');
		
		return array(self::CONTROL_BUTTON_KEY => $dtc->translate('script_cmd_partial_delete_label'));
	}
	
	public function processEntries(ScriptState $scriptState, array $entries) {
		$scriptManager = N2N::getModelContext()->lookup('rocket\script\core\ScriptManager');
		$entityScript = $this->getEntityScript();
		$em = $scriptState->getEntityManager();
		
		foreach ($entries as $entry) {
// 			$scriptManager->notifyOnDelete($entry);
			$em->remove($entry);
// 			$scriptManager->notifyDelete($entry);
		}
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\PrivilegedScriptCommand::getPrivilegeLabel()
	 */
	public function getPrivilegeLabel(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return $dtc->translate('common_delete_label'); 
	}
	
// 	public static function createPathExt($entityId, $draftId = null) {
// 		if (isset($draftId)) {
// 			return self::createHistoryPathExt($draftId);
// 		}
	
// 		return new Path(array($this->getId(), $entityId));
// 	}
	
// 	public static function createHistoryPathExt($draftId) {
// 		return new Path(array($this->getId(), 'draft', $draftId));
// 	}
}