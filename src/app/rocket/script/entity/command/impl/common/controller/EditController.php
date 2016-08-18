<?php
namespace rocket\script\entity\command\impl\common\controller;

use rocket\script\entity\manage\EntryViewInfo;
use rocket\core\model\Breadcrumb;
use rocket\core\model\RocketState;
use n2n\core\DynamicTextCollection;
use rocket\script\core\ManageState;
use rocket\script\entity\command\impl\common\model\EditModel;
use n2n\http\ControllerAdapter;
use n2n\l10n\DateTimeFormat;
use rocket\script\entity\command\impl\common\EditScriptCommand;
use rocket\script\entity\adaptive\draft\Draft;
use rocket\script\entity\manage\EntryManageUtils;
use n2n\http\PageNotFoundException;
use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;

class EditController extends ControllerAdapter {
	private $rocketState;
	private $dtc;
	private $utils;
	private $editCommand;
	
	private function _init(ManageState $manageState, RocketState $rocketState, DynamicTextCollection $dtc) {
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
// 		$this->utils = new EntryControllingUtils($manageState->peakScriptState());
	}
	
	public function setEditCommand(EditScriptCommand $editCommand) {
		$this->editCommand = $editCommand;
	}
	
	public function index($entityId, $httpLocaleId = null) {
		$scriptSelection = null;
		try {
			$scriptSelection = $this->utils->createScriptSelectionFromEntityId($entityId, $httpLocaleId);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}
		
		$scriptSelectionMapping = $this->utils->createScriptSelectionMapping($scriptSelection);
		$entryManager = $this->utils->createEntryManager($scriptSelectionMapping);
		$entryForm = $this->utils->createEntryForm($scriptSelectionMapping);
		$editModel = new EditModel($entryManager, $entryForm);

		if (null != ($redirectUrl = $this->dispatchEditModel($editModel))) {
			$this->redirect($redirectUrl);
			return;
		}
		
		$this->applyBreadcrumbs();
		
		$this->forward('script\entity\command\impl\common\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => new EntryCommandViewModel($editModel, true)));
	}
	
	public function doPreview($previewType, $id, $httpLocaleId = null) {
		$scriptState = $this->utils->getScriptState();
		$editModel = $this->utils->createEditModel($id, $httpLocaleId, true, $this->editCommand);
		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), $this->getRequest(), 
				$this->getResponse(), $previewType, $editModel);
		$currentPreviewType = $previewController->getPreviewType();
				
		$this->applyBreadcrumbs($scriptState);
		
		$this->forward('script\entity\command\impl\common\view\editPreview.html', array('commandEditEntryModel' => $editModel,
				'iframeSrc' => $this->getRequest()->getControllerContextPath($this->getControllerContext(),
						array('previewsrc', $currentPreviewType, $id, $httpLocaleId)),
				'entryViewInfo' => new EntryViewInfo($editModel, $editModel->getEntryForm(), $previewController)));
	}
	
	public function doPreviewSrc(array $contextCmds, 
			array $cmds, $previewType, $id, $httpLocaleId = null) {
		$editModel = $this->utils->createEditModel($id, $httpLocaleId, true, $this->editCommand);

		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), $this->getRequest(), 
				$this->getResponse(), $previewType, $editModel);

		if (null != ($redirectUrl = $this->dispatchEditModel($editModel, false, true))) {
			$previewController->getPreviewModel()->setRedirectUrl($redirectUrl);
		}
		
		$previewController->execute(array(), array_merge($contextCmds, $cmds), $this->getN2nContext());
	}
	
	public function doDraft($entityId, $draftId = null, $httpLocaleId = null) {
		$scriptSelection = null;
		try {
			$scriptSelection = $this->utils->createScriptSelectionFromDraftId($entityId, $draftId, $httpLocaleId);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}
		
		$scriptSelectionMapping = $this->utils->createScriptSelectionMapping($scriptSelection);
		$entryManager = $this->utils->createEntryManager($scriptSelectionMapping);
		$entryForm = $this->utils->createEntryForm($scriptSelectionMapping);
		$editModel = new EditModel($entryManager, $entryForm);
		
		if (null != ($redirectUrl = $this->dispatchEditModel($editModel))) {
			$this->redirect($redirectUrl);
			return;
		}
		
		$this->applyBreadcrumbs();

		$this->forward('script\entity\command\impl\common\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => new EntryCommandViewModel($editModel, true)));
	}
	
	public function doDraftPreview($previewType, $id, $draftId, $httpLocaleId = null) {
		$scriptState = $this->utils->getScriptState();
		$editModel = $this->utils->createDraftEditModel($id, $draftId, $httpLocaleId, $this->editCommand);
		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), $this->getRequest(), 
				$this->getResponse(), $previewType);
		$currentPreviewType = $previewController->getPreviewType();
		
		$this->applyBreadcrumbs($scriptState);
		
		$this->forward('script\entity\command\impl\common\view\editPreview.html', array('commandEditEntryModel' => $editModel, 
				'iframeSrc' => $this->getRequest()->getControllerContextPath($this->getControllerContext(), 
						array('draftpreviewsrc', $currentPreviewType, $id, $draftId, $httpLocaleId)),
				'entryViewInfo' => new EntryViewInfo($editModel, $editModel->getEntryForm(), $previewController)));
	}
	
	public function doDraftPreviewSrc(array $contextCmds, array $cmds, $previewType, $id, $draftId, $httpLocaleId = null) {
		$editModel = $this->utils->createDraftEditModel($id, $draftId, $httpLocaleId, $this->editCommand);
		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), 
				$this->getRequest(), $this->getResponse(), $previewType, $editModel);
	
		if (null != ($redirectUrl = $this->dispatchEditModel($editModel, true, true, $this->editCommand))) {
			$previewController->getPreviewModel()->setRedirectUrl($redirectUrl);
		}
		
		$previewController->execute(array(), array_merge($contextCmds, $cmds), $this->getN2nContext());
	}
	
	private function applyBreadcrumbs() {
		$scriptState = $this->utils->getScriptState();
		$scriptSelection = $scriptState->getScriptSelection();
		$request = $this->getRequest();
		$previewType = $scriptState->getPreviewType();
		$scriptCommandId = $scriptState->getExecutedScriptCommand()->getId();
		
		if (!$scriptState->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb(
					$scriptState->createOverviewBreadcrumb($this->getRequest()));
		}
		
		$this->rocketState->addBreadcrumb($scriptState->createDetailBreadcrumb($request));
		
		if ($scriptSelection->hasDraft()) {	
			$breadcrumbPath = $request->getControllerContextPath($scriptState->getControllerContext(),
					$scriptState->getContextEntityScript()->getEntryDetailPathExt(
							$scriptSelection->toNavPoint($scriptState->getPreviewType())->copy(false, true)));
			$dtf = DateTimeFormat::createDateTimeInstance($this->getRequest()->getLocale());
			$breadcrumbLabel = $this->dtc->translate('script_cmd_detail_draft_breadcrumb', 
					array('last_mod' => $dtf->format($scriptSelection->getDraft()->getLastMod())));
			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
		}
		
		$breadcrumbPath = $request->getControllerContextPath($scriptState->getControllerContext(),
				PathUtils::createPathExtFromScriptNavPoint($scriptCommandId, $scriptState->toNavPoint()));
		if ($scriptSelection->hasTranslation()) {
			$breadcrumbLabel = $this->dtc->translate('script_impl_edit_translation_breadcrumb',
					array('locale' => $scriptSelection->getTranslationLocale()->getName($request->getLocale())));
		} else {
			$breadcrumbLabel = $this->dtc->translate('script_impl_edit_entry_breadcrumb');
		}
		
		$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
	}	
	
	private function dispatchEditModel(EditModel $editModel) {
		$scriptState = $this->utils->getScriptState();
		$scriptNavPoint = null;
		
		$dispReturn = $this->dispatch($editModel, 'save');
		if ($dispReturn instanceof Draft) {
			$scriptNavPoint = $scriptState->toNavPoint($dispReturn->getId());
		} else if ($dispReturn) {
			$scriptNavPoint = $scriptState->toNavPoint()->copy(true);
		} else {
			return null;
		}
		
		return $scriptState->getDetailPath($this->getRequest(), $scriptNavPoint);
	}
}