<?php
namespace rocket\script\entity\command\impl\common\model;

use n2n\http\Request;
use rocket\script\entity\command\impl\common\controller\PathUtils;
use rocket\script\controller\preview\PreviewController;

class EntryCommandViewModel {
	private $entryCommandModel;
	private $displayDefinition;
	private $scriptState;
	private $scriptSelection;
	private $editActivated;
	private $title;
	private $previewActivated;
	
	public function __construct(EntryCommandModel $entryCommandModel, $editActivated, $previewActivated = false) {
		$this->entryCommandModel = $entryCommandModel;
		$entryModel = $entryCommandModel->getEntryModel();
		$this->displayDefinition = $entryModel->getDisplayDefinition();
		$scriptSelectionMapping = $entryModel->getScriptSelectionMapping();
		$this->scriptState = $entryModel->getScriptState();
		$this->scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$this->editActivated = $editActivated;
		$this->previewActivated = $previewActivated;
	}
	
	public function getTitle() {
		if ($this->title !== null) return $this->title;
			
		if (!$this->scriptSelection->isNew()) {
			return $this->title = $this->displayDefinition->getScriptMask()->createKnownString(
					$this->scriptSelection->getEntity(), $this->scriptState->getLocale());
		} 
		
		return $this->title = $this->displayDefinition->getScriptMask()->getLabel();
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function isEditActivated() {
		return $this->editActivated;
	}
	
	public function isTranslationEnabled() {
		return $this->displayDefinition->getScriptMask()->isTranslationEnabled();	
	} 
	
	public function getLangNavPoints() {
		$entityScript = $this->displayDefinition->getEntityScript();
		$currentTranslationLocale = $this->scriptSelection->getTranslationLocale();

		$navPoints = array();
		$mainTranslationLocale = $entityScript->getMainTranslationLocale();
		$navPoints[] = array(
				'pathExt' => PathUtils::createPathExtFromScriptNavPoint(null,
						$this->scriptState->toNavPoint()->copy(false, true, false)),
				'label' => $mainTranslationLocale->getName($this->scriptState->getLocale()),
				'active' => null === $currentTranslationLocale);
	
		foreach ($entityScript->getTranslationLocales() as $translationLocale) {
			$navPoints[] = array(
					'pathExt' => PathUtils::createPathExtFromScriptNavPoint(null,
							$this->scriptState->toNavPoint(null, $translationLocale)),
					'label' => $translationLocale->getName($this->scriptState->getLocale()),
					'active'=> $translationLocale->equals($currentTranslationLocale));
		}

		return $navPoints;
	}
	
	public function isDraftEnabled() {
		return $this->displayDefinition->getScriptMask()->isDraftEnabled();
	}
	
	public function hasPreviewSwitch() {
		if ($this->scriptSelection->isNew()) return false;
		$entityScript = $this->displayDefinition->getEntityScript();

		return (!$this->editActivated && $entityScript->isPreviewAvailable())
				|| ($this->editActivated && $entityScript->isEditablePreviewAvailable());
	}
	
	public function isPreviewActivated() {
		return $this->previewActivated;
	}
	
	public function getInfoPathExt() {
		return PathUtils::createPathExtFromScriptNavPoint(null, $this->scriptState->toNavPoint()->copy(false, false, true));
	}
	
	public function getPreviewPathExt() {
		$previewType = $this->scriptState->getPreviewType();
		if (is_null($previewType)) $previewType = PreviewController::PREVIEW_TYPE_DEFAULT;

		return PathUtils::createPathExtFromScriptNavPoint(null, $this->scriptState->toNavPoint(null, null, $previewType));
	}
	
	public function getCancelPath(Request $request) {
		if ($this->scriptSelection->isNew()) {
			return $this->scriptState->getOverviewPath($request);
		}
		
		return $this->scriptState->getDetailPath($request, 
				$this->scriptSelection->toNavPoint($this->scriptState->getPreviewType()));	
	}
	
	public function createEntryView() {
		return $this->displayDefinition->getScriptMask()->createEntryView(
				$this->entryCommandModel->getEntryModel());
	}
}
// class EntryViewInfo {
// 	private $scriptState;
// 	private $commandEntryModel;
// 	private $entryModel;
// 	private $scriptSelection;
// 	private $contextEntityScript;
// 	private $exactEntityScript;
// 	private $previewController;
// 	private $title;
	
// 	public function __construct(CommandEntryModel $commandEntryModel = null, EntryModel $entryModel, PreviewController $previewController = null, $title = null) {
// 		$this->scriptState = $entryModel->getScriptState();
// 		$this->commandEntryModel = $commandEntryModel;
// 		$this->entryModel = $entryModel;
// 		$this->scriptSelection = $this->entryModel->getScriptSelection();
		
// 		$this->contextEntityScript = $this->scriptState->getContextEntityScript();
// 		$this->exactEntityScript = $this->entryModel->getEntityScript();
		
// 		$this->previewController = $previewController;
		
// 		if (isset($title)) {
// 			$this->title = $title;
// 		} else {
// 			$this->title = $this->exactEntityScript->createKnownString($this->scriptSelection->getEntity(),
// 					$this->scriptState->getLocale());
// 		}
// 	}
	
// 	public function getTitle() {
// 		return $this->title;
// 	}
	
// 	public function getScriptState()  {
// 		return $this->scriptState;
// 	}
	
// 	public function getScriptSelection() {
// 		return $this->scriptSelection;
// 	}
	
// 	public function isInEditMode() {
// 		return $this->entryModel instanceof EditEntryModel;
// 	}
	
// 	public function isNew() {
// 		return $this->entryModel instanceof EditEntryModel && $this->entryModel->isNew();
// 	}
	

	
// 	public function getLangNavPoints() {
// 		$currentTranslationLocale = $this->scriptSelection->getTranslationLocale();
		
// 		$navPoints = array();
		
// 		$this->ensureCommandEntryModel();
		
// 		$mainTranslationLocale = $this->commandEntryModel->getMainTranslationLocale();
// 		$navPoints[] = array(
// 				'pathExt' => PathUtils::createPathExtFromScriptNavPoint(null, 
// 						$this->scriptState->toNavPoint()->copy(false, true, false)),
// 				'label' => $mainTranslationLocale->getName($this->scriptState->getLocale()),
// 				'active' => null === $currentTranslationLocale);

// 		foreach ($this->commandEntryModel->getTranslationLocales() as $translationLocale) {
// 			$navPoints[] = array(
// 					'pathExt' => PathUtils::createPathExtFromScriptNavPoint(null, 
// 							$this->scriptState->toNavPoint(null, $translationLocale)),
// 					'label' => $translationLocale->getName($this->scriptState->getLocale()),
// 					'active'=> $translationLocale->equals($currentTranslationLocale));
// 		}
		
// 		return $navPoints;
// 	}
	
// 	public function getLiveEntryPathExt() {
// 		$previewType = $this->scriptState->getPreviewType();
// 		return PathUtils::createPathExtFromScriptNavPoint(null, $this->scriptState->toNavPoint()->copy(true));
// 	}
	
// 	private function ensureCommandEntryModel() {
// 		if (!isset($this->commandEntryModel)) {
// 			throw IllegalStateException::createDefault();
// 		}
// 	}
	
// 	public function buildPathToDraft(Draft $draft) {
// 		return PathUtils::createPathExtFromScriptNavPoint(null, $this->scriptState->toNavPoint($draft->getId()));
// 	}
	
// 	public function getCurrentDraft() {
// 		$this->ensureCommandEntryModel();
// 		return $this->commandEntryModel->getCurrentDraft();
// 	}
	
// 	public function getHistoricizedDrafts() {
// 		$this->ensureCommandEntryModel();
		
// 		return $this->commandEntryModel->getHistoricizedDrafts();
// 	}
	
// 	public function isInPeview() {
// 		return isset($this->previewController);
// 	}
	
// 	public function hasPreviewTypeNav() {
// 		return isset($this->previewController) && sizeof((array) $this->previewController->getPreviewTypeOptions());
// 	}
	
// 	public function getPreviewTypeNavInfos() {
// 		if (is_null($this->previewController)) return array();
		
// 		$currentPreviewType = $this->scriptState->getPreviewType();
// 		$navPoints = array();
// 		foreach ((array) $this->previewController->getPreviewTypeOptions() as $previewType => $label) {
// 			$navPoints[(string) $previewType] = array('label' => $label,
// 					'pathExt' => PathUtils::createPathExtFromScriptNavPoint(null, $this->scriptState->toNavPoint(null, null, $previewType)),
// 					'active' => ($previewType == $currentPreviewType));
// 		}
// 		return $navPoints;
// 	}
	
// // 	public function get
// }