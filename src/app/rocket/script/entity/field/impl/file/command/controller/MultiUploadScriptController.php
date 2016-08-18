<?php
namespace rocket\script\entity\field\impl\file\command\controller;

use n2n\http\ControllerAdapter;
use n2n\N2N;
use n2n\io\fs\UploadedFileManager;
use n2n\dispatch\PropertyPath;
use rocket\script\core\ManageState;
use rocket\core\model\RocketState;
use rocket\script\entity\manage\ScriptSelection;
use n2n\core\DynamicTextCollection;
use rocket\script\entity\command\control\EntryControlComponent;
use rocket\script\entity\field\impl\file\MultiUploadFileScriptField;
use rocket\script\entity\manage\EntryManageUtils;
use rocket\script\entity\manage\mapping\MappingValidationResult;
use n2n\core\IllegalStateException;
use rocket\core\model\Breadcrumb;

class MultiUploadScriptController extends ControllerAdapter {
	
	const ACTION_UPLOAD = 'upload';
	
	private $scriptState;
	private $rocketState;
	private $utils;
	private $scriptField;
	/**
	 * @var \rocket\script\entity\field\impl\file\MultiUploadFileScriptField
	 */
	private function _init(ManageState $manageState, RocketState $rocketState) {
		$this->scriptState = $manageState->peakScriptState();
		$this->rocketState = $rocketState;
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
	}
	
	public function setScriptField(MultiUploadFileScriptField $scriptField) {
		$this->scriptField = $scriptField;
	}
	
	public function index() {
		$tx = N2N::createTransaction(true);
		if ($this->scriptState->getExecutedScriptCommand() instanceof EntryControlComponent) {
			$this->scriptState->setScriptSelection(new ScriptSelection($galleryId, $gallery));
		}
		$this->applyBreadCrumbs();
		$tx->commit();
		$this->forward('\rocket\script\entity\field\impl\file\command\view\multiupload.html', 
				array('scriptState' => $this->scriptState));
	}
	
	public function doUpload(UploadedFileManager $ufm) {
		$file = $ufm->get(new PropertyPath(array('upl')));
		if (null === $file) return;
		$tx = N2N::createTransaction();
		$entryForm = $this->utils->createEntryForm();
		$entryManager = $this->utils->createEntryManager();
		
		$scriptSelectionMapping = $entryForm->buildScriptSelectionMapping();
		
		$scriptSelectionMapping->setValue($this->scriptField->getId(), $file);
		if (null !== ($referencedNamePropertyId = $this->scriptField->getReferencedNamePropertyId())) {
			$prettyNameParts = preg_split('/(\.|-|_)/', $file->getOriginalName());
			array_pop($prettyNameParts);
			$scriptSelectionMapping->setValue($referencedNamePropertyId, implode(' ', $prettyNameParts));
		}
		$entryManager->create($scriptSelectionMapping);
		
		$mappingValidationResult = new MappingValidationResult();
		if (!$scriptSelectionMapping->save($mappingValidationResult)) {
			//$messageContainer->addAll($mappingValidationResult->getMessages());
			throw IllegalStateException::createDefault();
		}
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$em = $this->scriptState->getEntityManager();
		$em->persist($scriptSelection->getEntity());
		$tx->commit();
    }
    
    private function applyBreadCrumbs() {
    	$dtc = new DynamicTextCollection('rocket');
    	$this->rocketState->addBreadcrumb(
    			$this->scriptState->createOverviewBreadcrumb($this->getRequest()));
    	$this->rocketState->addBreadcrumb(new Breadcrumb($this->getRequest()->getCurrentControllerContextPath(), 
    			$dtc->translate('script_impl_multi_upload_label')));
    }
}