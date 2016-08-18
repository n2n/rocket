<?php

namespace rocket\script\config\controller;

use n2n\http\PageNotFoundException;
use rocket\script\core\UnknownScriptException;
use rocket\script\config\model\EntityScriptConfigForm;
use n2n\http\ControllerAdapter;
use rocket\core\model\Rocket;
use rocket\script\config\model\ScriptMaskForm;
use rocket\script\entity\mask\IndependentScriptMask;
use rocket\script\core\extr\ScriptMaskExtraction;
use rocket\script\entity\UnknownScriptMaskException;
use rocket\script\entity\FilterModelFactory;
use rocket\script\entity\filter\FilterForm;
use n2n\core\MessageContainer;
use rocket\script\config\model\ConfigTemplateModel;
use rocket\core\model\Breadcrumb;
use rocket\core\model\RocketState;
use n2n\core\DynamicTextCollection;
use rocket\script\entity\filter\SortForm;

class EntityScriptConfigController extends ControllerAdapter {
	private $scriptManager;
	private $rocketState;
	private $dtc;
	
	private function _init(Rocket $rocket, RocketState $rocketState, DynamicTextCollection $dtc) {
		$this->scriptManager = $rocket->getScriptManager();
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
	}
	
	public function index($id, MessageContainer $mc) {
		$lenientResult = null;
		try {
			$lenientResult = $this->scriptManager->getLenientEntityScriptById($id);
		} catch (UnknownScriptException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
		$entityScript = $lenientResult->getEntityScript();
		$mc->addAll($lenientResult->getErrorMessages());
		$configForm = new EntityScriptConfigForm($entityScript, $this->getRequest()->getLocale(), $this->getN2nContext());
		if ($this->dispatch($configForm, 'save')) {
			$this->scriptManager->flush();

			$this->refresh();
			return;
		} else if ($this->dispatch($configForm, 'saveAndGoToOverview')) {
			$this->scriptManager->flush();

			$this->redirectToController(null, null, null,
					'rocket\script\config\controller\ScriptsConfigController');
			return;
		} else if ($this->dispatch($configForm, 'saveAndBack')) {
			$this->scriptManager->flush();
			$this->redirectToController(array('edit', $entityScript->getId()), null, null, 
					'rocket\script\config\controller\ScriptsConfigController');
			return;
		}
		
		$this->applyBreadcrumb($entityScript);
		
		$this->forward('script\config\view\entityScriptConfig.html', array('configForm' => $configForm,
				'configTemplateModel' => new ConfigTemplateModel($id, $this->scriptManager->extractScripts(), true)));
	}
	
	public function doMask($entityScriptId, $maskId = null) {
		$entityScript = null;
		try {
			$entityScript = $this->scriptManager->getEntityScriptById($entityScriptId);
		} catch (UnknownScriptException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
		
		$mask = null;
		if ($maskId === null) {
			$mask = new IndependentScriptMask($entityScript, new ScriptMaskExtraction());
		} else {
			try {
				$mask = $entityScript->getMaskById($maskId);
			} catch (UnknownScriptMaskException $e) {
				throw new PageNotFoundException(null, null, $e);
			}
		}
		
		$maskForm = new ScriptMaskForm($mask, 
				FilterForm::createFromFilterModel(
						FilterModelFactory::createFilterModel($mask->getEntityScript(), $this->getN2nContext())), 
				SortForm::createFromSortModel(
						FilterModelFactory::createSortModel($mask->getEntityScript(), $this->getN2nContext())));
		if ($this->dispatch($maskForm, 'save')) {
			$entityScript->getMaskSet()->add($mask);
			$this->scriptManager->flush();
			
			$this->refresh();
			return;
		}
		
		$this->applyBreadcrumb($entityScript, ($maskId === null ? $this->dtc->translate('script_add_mask_label')
				: $this->dtc->translate('script_edit_mask_breadcrumb', array('mask' => $mask->getId()))));
		
		$this->forward('script\config\view\maskEdit.html', array('maskForm' => $maskForm));
	}
	
	private function applyBreadcrumb($entityScript, $detailLabel = null) {
		$this->rocketState->addBreadcrumb(new Breadcrumb(
				$this->getRequest()->getControllerContextPath($this->getControllerContext(), array($entityScript->getId())), 
				$this->dtc->translate('script_config_breadcrumb', array('script' => $entityScript->getLabel()))));
		
		if ($detailLabel !== null) {
			$this->rocketState->addBreadcrumb(new Breadcrumb($this->getRequest()->getPath(), $detailLabel));
		}
	}
}