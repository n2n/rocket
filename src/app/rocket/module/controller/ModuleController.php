<?php
namespace rocket\module\controller;

use n2n\http\PageNotFoundException;
use rocket\core\model\RocketState;
use n2n\http\ControllerAdapter;
use n2n\http\ParamPath;
use rocket\core\model\Rocket;
use n2n\N2N;
use n2n\ModuleNotFoundException;
use rocket\module\model\ScriptElementsManageForm;
use rocket\core\model\Breadcrumb;
use n2n\dispatch\option\impl\OptionForm;
use n2n\core\DynamicTextCollection;
use n2n\reflection\ReflectionUtils;

class ModuleController extends ControllerAdapter {
	private $rocketState;
	private $dtc;
	
	private function _init(RocketState $rocketState, DynamicTextCollection $dtc) {
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
	}
	
	public function index() {
		$this->applyBreadcrumb();
		
		$this->forward('module\view\moduleList.html');
	}
	
	public function doScriptElements(ParamPath $encodedNamespace, Rocket $rocket) {
		$namespace = ReflectionUtils::decodeNamespace($encodedNamespace);
		$module = null;
		try {
			$module = N2N::getModule($namespace);
		} catch (ModuleNotFoundException $e) {
			throw new PageNotFoundException('module not found', 0, $e);
		}
	
		$configForm = new ScriptElementsManageForm($module, $rocket->getScriptElementStore());
		
		if ($this->dispatch($configForm, 'save')) {
			$this->redirectToController();
			return;
		}
		
		$this->applyBreadcrumb($module, 'module_manage_script_elements_breadcrumb');
		$this->forward('module\view\scriptElementsManageForm.html', array('configForm' => $configForm));
	}
	
	public function doCustomConfig(ParamPath $encodedNamespace) {
		$namespace = ReflectionUtils::decodeNamespace($encodedNamespace);
		$module = null;
		try {
			$module = N2N::getModule($namespace);
		} catch (ModuleNotFoundException $e) {
			throw new PageNotFoundException('module not found', 0, $e);
		}
		
		$moduleConfig = $module->getModuleConfiguration();
		if (!$moduleConfig->hasDescriber()) {
			throw new PageNotFoundException();
		} 
		
		$describer = $moduleConfig->getDescriber();
		$optionCollection = $describer->createOptionCollection();
		$optionForm = new OptionForm($optionCollection, $describer->readCustomAttributes());
		if ($this->dispatch($optionForm)) {
			$describer->writeCustomAttributes($optionForm->getAttributes());
			$this->redirectToController();
			return;
		}
		
		$this->applyBreadcrumb($module, 'module_config_breadcrumb');
		$this->forward('module\view\customConfig.html', array('optionForm' => $optionForm));
	}
	
	private function applyBreadcrumb($module = null, $detailLabelKey = null) {
		$this->rocketState->addBreadcrumb(new Breadcrumb(
				$this->getRequest()->getControllerContextPath($this->getControllerContext()),
				$this->dtc->translate('module_title')));
		
		if ($module !== null && $detailLabelKey !== null) {
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$this->getRequest()->getPath(), $this->dtc->translate($detailLabelKey, array('module' => (string) $module))));
		}
	}
}