<?php
namespace rocket\script\entity\field\impl\file\command\controller;

use rocket\core\model\Breadcrumb;
use n2n\core\DynamicTextCollection;
use n2n\io\fs\img\UnsupportedImageTypeException;
use n2n\io\fs\img\ImageFile;
use rocket\core\model\RocketState;
use rocket\script\core\ManageState;
use n2n\http\PageNotFoundException;
use rocket\script\entity\command\impl\common\controller\EntryControllingUtils;
use rocket\script\entity\field\impl\file\FileScriptField;
use n2n\http\ControllerAdapter;
use rocket\script\entity\command\impl\common\controller\PathUtils;
use rocket\script\entity\field\impl\file\command\model\ThumbModel;
use rocket\script\entity\manage\EntryManageUtils;

class ThumbController extends ControllerAdapter {
	private $fileScriptField;
	private $rocketState;
	private $utils;
	private $dtc;
	
	private function _init(ManageState $manageState, RocketState $rocketState, DynamicTextCollection $dtc) {
		$this->rocketState = $rocketState;
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
		$this->dtc = $dtc;
	}
	
	public function setFileScriptField(FileScriptField $fileScriptField) {
		$this->fileScriptField = $fileScriptField;
		$this->entityScript = $fileScriptField->getEntityScript();
	}
	
	public function index($id) {
		$scriptSelection = $this->utils->createScriptSelectionFromEntityId($id);
		
		if (!$this->entityScript->isObjectValid($scriptSelection->getEntity())) {
			throw new PageNotFoundException();
		}
		
		$this->utils->applyToScriptState($scriptSelection);
		$file = $this->fileScriptField->getPropertyAccessProxy()->getValue($scriptSelection->getEntity());
		
		if ($file === null) {
			throw new PageNotFoundException();
		}
		
		$imageFile = null;
		try {
			$imageFile = new ImageFile($file);
		} catch (UnsupportedImageTypeException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$thumbModel = new ThumbModel($imageFile, $this->fileScriptField);
		
		if ($this->dispatch($thumbModel, 'save')) {
			$this->refresh();
			return;
		}
		
		$this->applyBreadcrumbs();
				
		$this->forward('script\entity\field\impl\file\command\view\thumb.html', array('thumbModel' => $thumbModel));
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
			$this->rocketState->addBreadcrumb($scriptState->createDetailBreadcrumb($request));
		}
		
// 		if ($scriptSelection->hasDraft()) {			
// 			$breadcrumbPath = $request->getControllerContextPath($scriptState->getControllerContext(),
// 					$this->entityScript->getEntryDetailPathExt($scriptSelection->toNavPoint(
// 							$scriptState->getPreviewType())->copy(false, true)));
// 			$breadcrumbLabel = $scriptSelection->getDraft()->getName();
// 			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
// 		}
		
// 		if ($scriptSelection->hasTranslation()) {
// 			$breadcrumbPath = $request->getControllerContextPath($scriptState->getControllerContext(),
// 					$this->entityScript->getEntryDetailPathExt($scriptSelection->toNavPoint(
// 							$scriptState->getPreviewType())->copy(true, true)));
// 			$breadcrumbLabel = $this->dtc->translate('script_impl_translation_detail_bradcrumb' ,
// 			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
// 		}
		
		$breadcrumbPath = $request->getControllerContextPath($scriptState->getControllerContext(),
				PathUtils::createPathExtFromScriptNavPoint($scriptCommandId, $scriptState->toNavPoint()));
		$breadcrumbLabel = $this->fileScriptField->getLabel();
		$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
	}
}


