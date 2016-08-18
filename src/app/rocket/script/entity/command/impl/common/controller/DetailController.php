<?php
namespace rocket\script\entity\command\impl\common\controller;

use rocket\script\entity\manage\EntryViewInfo;
use n2n\core\DynamicTextCollection;
use rocket\core\model\RocketState;
use rocket\core\model\Breadcrumb;
use rocket\script\core\ManageState;
use n2n\http\ControllerAdapter;
use n2n\persistence\DbhPool;
use n2n\l10n\DateTimeFormat;
use rocket\script\entity\manage\EntryManageUtils;
use n2n\http\PageNotFoundException;
use rocket\script\entity\command\impl\common\model\DetailModel;
use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;

class DetailController extends ControllerAdapter {
	private $rocketState;
	private $dtc;
	private $utils;
	
	private function _init(ManageState $manageState, RocketState $rocketState, DynamicTextCollection $dtc, DbhPool $dbhPool) {
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
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
		$entryInfo = $this->utils->createEntryInfo($scriptSelectionMapping);
		$detailModel = new DetailModel($entryManager, $entryInfo);
		
		$this->applyBreadcrumbs();
			
		$this->forward('script\entity\command\impl\common\view\detail.html', 
				array('detailModel' => $detailModel, 
						'entryCommandViewModel' => new EntryCommandViewModel($detailModel, false)));
	}
	
	public function doPreview($previewType, $entityId, $httpLocaleId = null) {
		$scriptSelection = null;
		try {
			$scriptSelection = $this->utils->createScriptSelectionFromEntityId($entityId, $httpLocaleId);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}
		
		$scriptSelectionMapping = $this->utils->createScriptSelectionMapping($scriptSelection);
		$entryManager = $this->utils->createEntryManager($scriptSelectionMapping);
		$entryInfo = $this->utils->createEntryInfo($scriptSelectionMapping);
		$detailModel = new DetailModel($entryManager, $entryInfo);
		$previewController = null; 
		try {
			$previewController = $this->utils->createPreviewController($entryInfo, $previewType);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException('not found', 0, $e);
		}
		
		$this->applyBreadcrumbs();
		
		$this->forward('script\entity\command\impl\common\view\detailPreview.html', array( 
				'iframeSrc' => $this->getRequest()->getControllerContextPath($this->getControllerContext(), 
						array('previewsrc', $previewController->getPreviewType(), $entityId, $httpLocaleId)),
				'detailModel' => $detailModel, 
				'entryCommandViewModel' => new EntryCommandViewModel($detailModel, false, true)));
	}
	
	public function doPreviewSrc(array $cmds, array $contextCmds, $previewType, $entityId, $httpLocaleId = null) {
	$scriptSelection = null;
		try {
			$scriptSelection = $this->utils->createScriptSelectionFromEntityId($entityId, $httpLocaleId);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}
		
		$scriptSelectionMapping = $this->utils->createScriptSelectionMapping($scriptSelection);
		$entryManager = $this->utils->createEntryManager($scriptSelectionMapping);
		$entryInfo = $this->utils->createEntryInfo($scriptSelectionMapping);
		$detailModel = new DetailModel($entryManager, $entryInfo);
		$previewController = null; 
		try {
			$previewController = $this->utils->createPreviewController($entryInfo, $previewType);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException('not found', 0, $e);
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
		$entryInfo = $this->utils->createEntryInfo($scriptSelectionMapping);
		$detailModel = new DetailModel($entryManager, $entryInfo);
		
		$this->applyBreadcrumbs();

		$this->forward('script\entity\command\impl\common\view\detail.html',
				array('detailModel' => $detailModel,
						'entryCommandViewModel' => new EntryCommandViewModel($detailModel, false)));
	}
	
// 	public function doHistoryPublish($id, $draftId, $httpLocaleId = null, ParamGet $previewtype = null) {
// 		$detailModel = $this->utils->createHistoryDetailModel($id, $draftId, $httpLocaleId);
// 		$detailModel->publish();
		
// 		$this->redirectToController(PathUtils::createDetailPathExtFromScriptNavPoint(null, 
// 				$detailModel->getScriptSelection()->toNavPoint($previewtype)->copy(true)));
// 	}
	
	public function doDraftPreview($previewType, $id, $draftId = null, $httpLocaleId = null) {
		$draftModel = null;
		if (isset($draftId)) {
			$detailModel = $this->utils->createDraftDetailModel($id, $draftId, $httpLocaleId);
		} else {
			$detailModel = $this->utils->createDetailModel($id, $httpLocaleId, false);
		}
		$previewController = $this->utils->createPreviewController($detailModel->getEntryInfo(), $this->getRequest(), 
				$this->getResponse(), $previewType);
		$currentPreviewType = $previewController->getPreviewType();
		
// 		if (false != ($redirectUrl = $this->dispatchDetailModel($detailModel))) {
// 			$this->redirect($redirectUrl);
// 			return;
// 		}
		
		$this->applyBreadcrumbs();
		
		$this->forward('script\entity\command\impl\common\view\detailPreview.html', array('commandEntryModel' => $detailModel, 
				'iframeSrc' => $this->getRequest()->getControllerContextPath($this->getControllerContext(), 
						array('draftpreviewsrc', $currentPreviewType, $id, $draftId, $httpLocaleId)),
				'entryViewInfo' => new EntryViewInfo($detailModel, $detailModel->getEntryInfo(), $previewController)));
	}
	
	public function doDraftPreviewSrc(array $contextCmds, $previewType, $id, $draftId = null, $httpLocaleId = null) {
		$draftModel = null;
		if (isset($draftId)) {
			$detailModel = $this->utils->createDraftDetailModel($id, $draftId, $httpLocaleId);
		} else {
			$detailModel = $this->utils->createDetailModel($id, $httpLocaleId, false);
		}
		$previewController = $this->utils->createPreviewController($detailModel->getEntryInfo(), $this->getRequest(), 
				$this->getResponse(), $previewType);
	
		$previewController->execute(array(), $contextCmds, $this->getN2nContext());
	}
	
	private function applyBreadcrumbs() {
		$scriptState = $this->utils->getScriptState();
		$scriptSelection = $scriptState->getScriptSelection();
		$request = $this->getRequest();
		$scriptCommandId = $scriptState->getExecutedScriptCommand()->getId();
		
		if (!$scriptState->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb(
					$scriptState->createOverviewBreadcrumb($this->getRequest()));
		}
		
		if (!$scriptState->isDetailDisabled()) {
			$breadcrumbPath = $request->getControllerContextPath($this->getControllerContext(),
					PathUtils::createPathExtFromScriptNavPoint(null, 
							$scriptSelection->toNavPoint()->copy(true, true, false)));
			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $scriptState->getDetailBreadcrumbLabel()));
		}
		
		if ($scriptSelection->hasDraft()) {
			$breadcrumbPath = $request->getControllerContextPath($this->getControllerContext(),
					PathUtils::createPathExtFromScriptNavPoint(null, $scriptSelection->toNavPoint()->copy(false, true, false)));
			$dtf = DateTimeFormat::createDateTimeInstance($this->getRequest()->getLocale());
			$breadcrumbLabel = $this->dtc->translate('script_cmd_detail_draft_breadcrumb',
					array('last_mod' => $dtf->format($scriptSelection->getDraft()->getLastMod())));
			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
		}		
		
		if ($scriptSelection->hasTranslation()) {
			$breadcrumbPath = $request->getControllerContextPath($this->getControllerContext(),
					PathUtils::createPathExtFromScriptNavPoint(null, $scriptSelection->toNavPoint()));
			$breadcrumbLabel = $this->dtc->translate('script_impl_translation_detail_bradcrumb' ,
					array('locale' => $scriptSelection->getTranslationLocale()->getName($request->getLocale())));
			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
		}
	}
}