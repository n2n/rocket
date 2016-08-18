<?php
namespace rocket\script\config\model;

use n2n\persistence\orm\EntityModelManager;
use n2n\io\IoUtils;
use n2n\dispatch\val\ValEnum;
use n2n\core\TypeNotFoundException;
use n2n\dispatch\val\ValNotEmpty;
use n2n\core\MessageCode;
use n2n\dispatch\map\BindingErrors;
use n2n\reflection\ReflectionUtils;
use n2n\N2N;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use rocket\script\core\ScriptManager;
use n2n\dispatch\Dispatchable;
use rocket\script\core\ScriptConfig;
use rocket\core\model\Rocket;

class ScriptAddForm implements Dispatchable {
	private $scriptManager;
	protected $id;
	protected $label;
	protected $pluralLabel;
	protected $moduleNamespace;
	protected $type;
	protected $controllerClassName;
	protected $entityClassName;
	
	public function __construct(ScriptManager $scriptManager) {
		$this->scriptManager = $scriptManager;
	}
	
	private static function _annotations(AnnotationSet $as) {
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function getPluralLabel() {
		return $this->pluralLabel;
	}
	
	public function setPluralLabel($pluralLabel) {
		$this->pluralLabel = $pluralLabel;
	}
	
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	public function setModuleNamespace($moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
	}
	
	public function getModuleNamespaceOptions($includeNull = true) {
		$options = array();
		if ($includeNull) {
			$options[null] = null;
		}
		foreach (N2N::getModules() as $module) {
			$options[$module->getNamespace()] = $module->getNamespace();
		}
		return $options;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setType($type) {
		$this->type = $type;
	}
	
	public function getTypeOptions() {
		return array(ScriptConfig::SCRIPT_TYPE_ENTITY => ScriptConfig::SCRIPT_TYPE_ENTITY,
				ScriptConfig::SCRIPT_TYPE_CUSTOM => ScriptConfig::SCRIPT_TYPE_CUSTOM);
	}
	
	public function setControllerClassName($controllerClassName) {
		$this->controllerClassName = $controllerClassName;
	}
	
	public function getControllerClassName() {
		return $this->controllerClassName;
	}
	
	public function setEntityClassName($entityClassName) {
		$this->entityClassName = $entityClassName;
	}
	
	public function getEntityClassName() {
		return $this->entityClassName;
	}
	
	private function _validation(BindingConstraints $bc, Rocket $rocket) {
		$scriptManager = $rocket->getScriptManager();
		$bc->val('id', new ValNotEmpty());
		$bc->addClosureValidator(function($id, BindingErrors $bindingErrors) {
			if (IoUtils::hasSpecialChars($id)) {
				$bindingErrors->addError('id', new MessageCode('conf_script_err_invalid_id'));
			}
		});
		$bc->val('moduleNamespace', new ValEnum(array_keys($this->getModuleNamespaceOptions(false))));
		$bc->val('type', new ValEnum($this->getTypeOptions()));
		$bc->addClosureValidator(function($type, $entityClassName, BindingErrors $bindingErrors) use ($scriptManager) {
			if ($type != ScriptConfig::SCRIPT_TYPE_ENTITY) return;
			
			if (empty($entityClassName)) {
				$bindingErrors->addError('entityClassName', new MessageCode('script_err_no_entity_class_given'));
				return;
			}
			
			$entityModel = null;
			try {
				$entityModel = EntityModelManager::getInstance()->getEntityModelByClass(
						ReflectionUtils::createReflectionClass($entityClassName));
			} catch (TypeNotFoundException $e) {
				$bindingErrors->addError('entityClassName', new MessageCode('script_err_class_not_found', 
						array('class' => $entityClassName)));
				return;
			} catch (\InvalidArgumentException $e) {
				$bindingErrors->addError('entityClassName', new MessageCode('script_err_invalid_entity_class', 
						array('class' => $entityClassName)));
				return;
			}
			
			if ($scriptManager->containsEntityScriptClass($entityModel->getClass())) {
				$bindingErrors->addError('entityClassName', new MessageCode('script_err_entity_script_for_entity_already_defined',
						array('entity' => $entityModel->getClass()->getName())));
			}
			
			if ($entityModel->hasSuperEntityModel() 
					&& !$scriptManager->containsEntityScriptClass($entityModel->getSuperEntityModel()->getClass())) {
				$bindingErrors->addError('entityClassName', new MessageCode('script_no_entity_script_for_parent_entity_found_err',
						array('parent_entity' => $entityModel->getSuperEntityModel()->getClass()->getName())));
			}
		});
		
		$bc->addClosureValidator(function($type, $controllerClassName, BindingErrors $bindingErrors) use ($scriptManager) {
			if ($type != ScriptConfig::SCRIPT_TYPE_CUSTOM) return;
			
			if (empty($controllerClassName)) {
				$bindingErrors->addError('controllerClassName', new MessageCode('script_err_no_controller_class_given'));
				return;
			}
				
			$controllerClass = null;
			try {
				$controllerClass = ReflectionUtils::createReflectionClass($controllerClassName);
			} catch (TypeNotFoundException $e) {
				$bindingErrors->addError('controllerClassName', new MessageCode('script_err_class_not_found',
						array('class' => $controllerClassName)));
				return;
			} 
			
			if (!$controllerClass->implementsInterface('n2n\http\Controller')) {
				$bindingErrors->addError('entityClassName', new MessageCode('common_invalid_class_type_err',
						array('class' => $controllerClassName, 'type' => 'n2n\http\Controller')));
				return;
			}			
		});
	} 
	
	public function save() {
		$script = null;
		if ($this->type == ScriptConfig::SCRIPT_TYPE_ENTITY) {
			$script = $this->scriptManager->createEntityScript(N2N::getModule($this->moduleNamespace), $this->id, 
					$this->label, $this->pluralLabel, EntityModelManager::getInstance()->getEntityModelByClass(
							ReflectionUtils::createReflectionClass($this->entityClassName)));
		} else {
			$script = $this->scriptManager->createCustomScript(N2N::getModule($this->moduleNamespace), $this->id, 
					$this->label, ReflectionUtils::createReflectionClass($this->controllerClassName));
		}
		
		$this->scriptManager->flush();
		return $script;
	}
}