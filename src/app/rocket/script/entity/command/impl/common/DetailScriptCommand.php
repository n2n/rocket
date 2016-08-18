<?php
namespace rocket\script\entity\command\impl\common;

use rocket\script\entity\manage\ScriptNavPoint;
use rocket\script\entity\manage\ScriptState;
use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlView;
use n2n\l10n\Locale;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\command\impl\common\controller\DetailController;
use rocket\script\entity\command\control\ControlButton;
use rocket\script\entity\command\impl\common\controller\PathUtils;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\command\EntryDetailScriptCommand;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;
use rocket\script\entity\command\PrivilegedScriptCommand;
use rocket\script\entity\command\PrivilegeExtendableScriptCommand;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

class DetailScriptCommand extends IndependentScriptCommandAdapter implements EntryControlComponent, EntryDetailScriptCommand, 
		PrivilegedScriptCommand, PrivilegeExtendableScriptCommand {
	const ID_BASE = 'detail';
	const CONTROL_DETAIL_KEY = 'detail'; 
	const CONTROL_PUBLISH_KEY = 'publish';
	const PRIVILEGE_EXT_PUBLISH = 'publish';
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'Detail';
	}
		
	public function createController(ScriptState $scriptState) {
		return new DetailController();
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::CONTROL_DETAIL_KEY => $dtc->translate('script_impl_detail_label'), 
				self::CONTROL_PUBLISH_KEY => $dtc->translate('script_cmd_detail_publish_draft_label'));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\control\EntryControlComponent::createEntryControlButtons()
	 */
	public function createEntryControlButtons(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping, HtmlView $htmlView) {
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$request = $htmlView->getRequest();
		$dtc = new DynamicTextCollection('rocket', $request->getLocale());
		
		$previewType = null;
		if ($this->getEntityScript()->isPreviewAvailable()) {
			$previewType = $scriptState->getPreviewType();
		}
		
		$controlButtons = array();
		
		if (!$this->equals($scriptState->getExecutedScriptCommand())) {
			$pathExt = null;
			if ($scriptState->hasScriptSelection()) {
				$pathExt = PathUtils::createPathExtFromScriptNavPoint($this->getId(), $scriptSelection->toNavPoint($previewType));
			} else {
				$pathExt = PathUtils::createDraftPathExt($this->getId(), $scriptSelection->getId());
			}
			
			$controlButtons[self::CONTROL_DETAIL_KEY] = new ControlButton(
					$request->getControllerContextPath($scriptState->getControllerContext(), $pathExt),
					$dtc->translate('script_impl_detail_label'), null,
					true, ControlButton::TYPE_DEFAULT, IconType::ICON_FILE);
		}

		if ($scriptSelection->hasDraft() && $scriptSelection->isPrivilegeGranted($this, self::PRIVILEGE_EXT_PUBLISH)) {
			$translationLocaleHttpShort = null;
			if ($scriptSelection->hasTranslation()) {
				$translationLocaleHttpShort = $scriptSelection->getTranslationLocale()->toHttpId();
			}
			$controlButton = new ControlButton(
					$request->getControllerContextPath($scriptState->getControllerContext(), 
							array($this->getId(), 'historypublish', $scriptSelection->getId(), 
									$scriptSelection->getDraft()->getId(), $translationLocaleHttpShort),
							array('previewtype' => $scriptState->getPreviewType())),
					$dtc->translate('script_cmd_detail_publish_draft_label'), $dtc->translate('script_cmd_detail_publish_draft_tooltip'),
					true, ControlButton::TYPE_INFO, IconType::ICON_FILE);

			$controlButton->setConfirmMessage($dtc->translate('script_cmd_deatil_publish_draft_confirm_message'));
			$controlButton->setConfirmOkButtonLabel($dtc->translate('common_yes_label'));
			$controlButton->setConfirmCancelButtonLabel($dtc->translate('common_no_label'));
			
			$controlButtons[self::CONTROL_PUBLISH_KEY] = $controlButton;
		}
	
		return $controlButtons;
	}
	
	public function getEntryDetailPathExt(ScriptNavPoint $scriptNavPoint) {
		if (!$this->getEntityScript()->isPreviewAvailable()) {
			$scriptNavPoint = $scriptNavPoint->copy(false, false, true);
		}
		
		return PathUtils::createPathExtFromScriptNavPoint($this->getId(), $scriptNavPoint);
	}
	
	public function getPrivilegeLabel(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return $dtc->translate('script_impl_detail_label'); 
	}
	
	public function getPrivilegeExtOptions(Locale $locale) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		return array(self::PRIVILEGE_EXT_PUBLISH => $dtc->translate('script_impl_detail_publish_privilege_label'));
	}
}