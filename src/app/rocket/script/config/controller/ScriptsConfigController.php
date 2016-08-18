<?php
namespace rocket\script\config\controller;

use rocket\script\entity\EntityScript;
use rocket\script\core\UnknownScriptException;
use n2n\http\PageNotFoundException;
use n2n\http\ParamPath;
use rocket\script\config\model\ScriptAddForm;
use rocket\script\config\model\EntityScriptForm;
use rocket\core\model\Rocket;
use n2n\http\ControllerAdapter;
use rocket\script\config\model\ScriptListModel;
use rocket\script\core\extr\CustomScriptExtraction;
use rocket\script\core\ScriptManager;
use n2n\core\NotYetImplementedException;
use rocket\script\core\extr\EntityScriptExtraction;
use n2n\persistence\DbhPool;
use rocket\script\core\ScriptElementStore;
use rocket\script\config\model\ConfigTemplateModel;
use rocket\core\model\RocketState;
use rocket\core\model\Breadcrumb;
use n2n\core\DynamicTextCollection;

class ScriptsConfigController extends ControllerAdapter {
	private $rocketState;
	
	private function _init(RocketState $rocketState, DynamicTextCollection $dtc) {
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
	}
	
	public function index(Rocket $rocket) {
		$this->applyBreadcrumbs();
		$this->forward('script\config\view\scriptList.html', 
				array('scriptListModel' => new ScriptListModel($rocket->getScriptManager())));
	}
	
	public function doAdd(Rocket $rocket) {
		$scriptManager = $rocket->getScriptManager();
		$scriptAddForm = new ScriptAddForm($scriptManager);
		if (is_object($script = $this->dispatch($scriptAddForm, 'save'))) {
			if ($script instanceof EntityScript) {
				$this->redirectToController(array('edit', $script->getId()));
			} else {
				$this->redirectToController();
			}
			return;
		}
		
		$this->applyBreadcrumbs(array('add'), $this->dtc->translate('script_add_label'));
		$this->forward('script\config\view\scriptAdd.html', array('scriptForm' => $scriptAddForm));
	}
	
	public function doEdit(ParamPath $scriptId, Rocket $rocket, DbhPool $dbhPool) {
		$scriptManager = $rocket->getScriptManager();	
		$extraction = null;
		try {
			$extraction = $scriptManager->extractScript((string) $scriptId);
		} catch (UnknownScriptException $e) {
			throw new PageNotFoundException('script not found', 0, $e);
		}
		
		$this->applyBreadcrumbs(array('edit', $extraction->getId()), 
				$this->dtc->translate('script_edit_breadcrumb', 
						array('script' => $extraction->getLabel())));
		
		if ($extraction instanceof CustomScriptExtraction) {
			$this->editCustomScript($extraction, $scriptManager);
		} else {
			$this->editEntityScript($extraction, $scriptManager, 
					$rocket->getScriptElementStore(), $dbhPool);
		}
	}
	
	private function editCustomScript(CustomScriptExtraction $extraction, ScriptManager $scriptManager) {
		throw new NotYetImplementedException();
	}
	
	private function editEntityScript(EntityScriptExtraction $extraction, ScriptManager $scriptManager, 
			ScriptElementStore $scriptElementStore, DbhPool $dbhPool) {
		$entityScriptForm = new EntityScriptForm($extraction, $scriptManager, $scriptElementStore, $dbhPool);
		if ($this->dispatch($entityScriptForm, 'save')) {
			$this->refresh();
			return;
		} else if ($this->dispatch($entityScriptForm, 'saveAndGoToOverview')) {
			$this->redirectToController();
			return;
		} else if ($this->dispatch($entityScriptForm, 'saveAndConfig')) {
			$this->redirectToController(array('entityscript', $extraction->getId()));
			return;
		}
		$this->forward('script\config\view\entityScriptEdit.html', 
				array('entityScriptForm' => $entityScriptForm, 
						'configTemplateModel' => new ConfigTemplateModel($extraction, 
								$scriptManager->extractScripts(), false)));
	}
	
	public function doEntityScript(array $contextCmds, array $cmds) {
		$this->applyBreadcrumbs();
		
		array_push($contextCmds, array_shift($cmds));
		$configController = new EntityScriptConfigController();
		$configController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
// 	public function doConfig(ParamPath $scriptId, Rocket $rocket) {	
// 		$scriptManager = $rocket->getScriptManager();		
// 		$entityScript = null;
// 		try {
// 			$entityScript = $scriptManager->getEntityScriptById((string) $scriptId);
// 		} catch (UnknownScriptException $e) {
// 			throw new PageNotFoundException('script not found', 0, $e);
// 		}
		
// 		$entityScriptExtConfigForm = new EntityScriptConfigForm($entityScript, 
// 				$this->getRequest()->getLocale());
// 		if ($this->dispatch($entityScriptExtConfigForm, 'save')) {
// 			$this->redirectToPath();
// 			return;
// 		} else if ($this->dispatch($entityScriptExtConfigForm, 'saveAndBack')) {
// 			$this->redirectToController(array('edit', $scriptId));
// 			return;
// 		}
		
// 		$this->forward('script\config\view\entityScriptConfig.html',
// 				array('entityScriptExtConfigForm' => $entityScriptExtConfigForm));
// 	}
	
	public function doCleanUp(Rocket $rocket) {		
		$scriptManager = $rocket->getScriptManager();	
		$scriptManager->cleanUp();
		
		$this->redirectToController();
	}
	
	private function getScriptById(Rocket $rocket, $scriptId) {
		$scriptManager = $rocket->getScriptManager();	
		try {
			return $scriptManager->getScriptById((string) $scriptId);
		} catch (UnknownScriptException $e) {
			throw new PageNotFoundException('script not found', 0, $e);
		}
	}
	
	public function doSeal(Rocket $rocket, ParamPath $scriptId) {
		$scriptManager = $rocket->getScriptManager();	
		$scriptManager->sealScriptById((string) $scriptId);
		$scriptManager->flush();
		
		$this->redirectToController();
	}
	
	public function doUnseal(Rocket $rocket, ParamPath $scriptId) {
		$scriptManager = $rocket->getScriptManager();	
		$scriptManager->unsealScriptById((string) $scriptId);
		$scriptManager->flush();
		
		$this->redirectToController();
	}
	
	public function doDelete(Rocket $rocket, ParamPath $scriptId) {
		$scriptManager = $rocket->getScriptManager();	
		$scriptManager->removeScriptById((string) $scriptId);
		$scriptManager->flush();
		
		$this->redirectToController();
	}
	
	private function applyBreadcrumbs(array $pathExt = null, $label = null) {
		$this->rocketState->addBreadcrumb(new Breadcrumb($this->getRequest()
						->getControllerContextPath($this->getControllerContext()), 
				$this->dtc->translate('script_title')));
		
		if ($pathExt !== null) {
			$this->rocketState->addBreadcrumb(new Breadcrumb($this->getRequest()
					->getControllerContextPath($this->getControllerContext()), $label));
		}
	}
}