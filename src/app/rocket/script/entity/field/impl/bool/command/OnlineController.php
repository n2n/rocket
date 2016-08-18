<?php
namespace rocket\script\entity\field\impl\bool\command;

use rocket\script\entity\field\impl\bool\OnlineScriptField;
use rocket\script\core\ManageState;
use n2n\http\ControllerAdapter;
use rocket\script\entity\manage\EntryManageUtils;
use rocket\script\entity\manage\mapping\MappingValidationResult;
use n2n\http\PageNotFoundException;
use n2n\http\ParamGet;

class OnlineController extends ControllerAdapter {
	const ACTION_OFFLINE = 'offline';
	
	private $onlineScriptField;
	private $utils;
	/**
	 * @var \rocket\script\entity\EntityScript
	 */
	private $entityScript;
	
	private function _init(ManageState $manageState) {
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
	}
	
	public function setOnlineScriptField(OnlineScriptField $onlineScriptField) {
		$this->onlineScriptField = $onlineScriptField;
		$this->entityScript = $onlineScriptField->getEntityScript();
	}
	
	public function index($id, $httpLocaleId = null, ParamGet $ref = null) {
		$this->setStatus(true, $id, $httpLocaleId, $ref);
	}
	
	public function doOffline($id, $httpLocaleId = null, ParamGet $ref = null) {
		$this->setStatus(false, $id, $httpLocaleId, $ref);
	}
	
	private function setStatus($status, $id, $httpLocaleId = null, ParamGet $ref = null) {
		$scriptSelection = null;
		try {
			$scriptSelection = $this->utils->createScriptSelectionFromEntityId($id, $httpLocaleId = null);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}
		
		$scriptFieldId = $this->onlineScriptField->getId();
		$scriptSelectionMapping = $this->utils->createScriptSelectionMapping($scriptSelection);
		$scriptSelectionMapping->setValue($scriptFieldId, $status);
		$scriptSelectionMapping->save(new MappingValidationResult());
		if (null !== $ref) {
			$this->redirectToContext($ref);
		} else {
			$this->redirect($this->utils->getScriptState()->getOverviewPath($this->getRequest()));
		}
	}
}