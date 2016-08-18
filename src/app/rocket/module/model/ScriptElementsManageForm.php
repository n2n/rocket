<?php
namespace rocket\module\model;

use rocket\script\core\ScriptCommandGroup;
use n2n\core\TypeNotFoundException;
use n2n\core\MessageCode;
use n2n\dispatch\PropertyPathPart;
use n2n\dispatch\map\BindingErrors;
use n2n\reflection\ReflectionUtils;
use n2n\dispatch\map\BindingConstraints;
use n2n\core\Module;
use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use rocket\script\core\ScriptElementStore;
use n2n\dispatch\Dispatchable;
      
class ScriptElementsManageForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->p('scriptCommandGroupModels', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY, 
				array('dynamic' => 'rocket\module\model\ScriptCommandGroupModel'));
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $module;
	private	$componentsStorage;
	
	protected $scriptFieldClassNames = array();
	protected $scriptCommandClassNames = array();
	protected $scriptCommandGroupModels = array();
	protected $scriptModificatorClassNames = array();
	protected $scriptListenerClassNames = array();
	
	public function __construct(Module $module, ScriptElementStore $componentsStorage) {
		$this->module = $module;
		$this->componentsStorage = $componentsStorage;
		
		foreach ($this->componentsStorage->getFieldClassesByModule($module) as $fieldClass) {
			$this->scriptFieldClassNames[] = $fieldClass->getName();
		}
		
		foreach ($this->componentsStorage->getCommandClassesByModule($module) as $commandClass) {
			$this->scriptCommandClassNames[] = $commandClass->getName();
		}
		
		foreach ($this->componentsStorage->getCommandGroupsByModule($module) as $name => $group) {
			$this->scriptCommandGroupModels[] = ScriptCommandGroupModel::createFromScriptcommandGroup($group);
		}
		
		foreach ($this->componentsStorage->getConstraintClassesByModule($module) as $constraintClass) {
			$this->scriptModificatorClassNames[] = $constraintClass->getName();
		}
		
		foreach ($this->componentsStorage->getListenerClassesByModule($module) as $listenerClass) {
			$this->scriptListenerClassNames[] = $listenerClass->getName();
		}
	}
	
	public function getModule() {
		return $this->module;
	}
	
	public function getScriptFieldClassNames() {
		return $this->scriptFieldClassNames;
	}
	
	public function setScriptFieldClassNames(array $scriptFieldClassNames) {
		$this->scriptFieldClassNames = $scriptFieldClassNames;
	}
	
	public function getScriptCommandClassNames() {
		return $this->scriptCommandClassNames;
	}
	
	public function setScriptCommandClassNames(array $scriptCommandClassNames) {
		$this->scriptCommandClassNames = $scriptCommandClassNames;
	}
	
	public function getScriptCommandGroupModels() {
		return $this->scriptCommandGroupModels;
	}
	
	public function setScriptCommandGroupModels(array $scriptCommandGroupModels) {
		$this->scriptCommandGroupModels = $scriptCommandGroupModels;
	}
	
	public function getScriptModificatorClassNames() {
		return $this->scriptModificatorClassNames;
	}
	
	public function setScriptModificatorClassNames(array $scriptModificatorClassNames) {
		$this->scriptModificatorClassNames = $scriptModificatorClassNames;
	}
	
	public function getScriptListenerClassNames() {
		return $this->scriptListenerClassNames;
	}
	
	public function setScriptListenerClassNames(array $scriptListenerClassNames) {
		$this->scriptListenerClassNames = $scriptListenerClassNames;
	}
	
	private function _validation(BindingConstraints $bc) {
		$bc->addClosureValidator(function(array $scriptFieldClassNames, BindingErrors $bindingErrors) {
			foreach ($scriptFieldClassNames as $key => $scriptFieldClassName) {
				if (empty($scriptFieldClassName)) continue;
				try {
					$class = ReflectionUtils::createReflectionClass($scriptFieldClassName);
					if (!$class->implementsInterface('rocket\script\entity\field\IndependentScriptField')) {
						$bindingErrors->addError(new PropertyPathPart('scriptFieldClassNames', true, $key),
								new MessageCode('common_invalid_class_type_err', array('class' => $scriptFieldClassName, 
										'type' => 'rocket\script\entity\field\IndependentScriptField')));
					}
				} catch (TypeNotFoundException $e) {
					$bindingErrors->addError(new PropertyPathPart('scriptFieldClassNames', true, $key),
							new MessageCode('common_class_not_found_err', array('class' => $scriptFieldClassName)));
				}
			}
		});
		
		
		$bc->addClosureValidator(function(array $scriptCommandClassNames, BindingErrors $bindingErrors) {
			foreach ($scriptCommandClassNames as $key => $scriptCommandClassName) {
				if (empty($scriptCommandClassName)) continue;
				try {
					$class = ReflectionUtils::createReflectionClass($scriptCommandClassName);
					if (!$class->implementsInterface('rocket\script\entity\command\IndependentScriptCommand')) {
						$bindingErrors->addError(new PropertyPathPart('scriptCommandClassNames', true, $key),
								new MessageCode('common_invalid_class_type_err', array('class' => $scriptCommandClassName, 
										'type' => 'rocket\script\entity\command\IndependentScriptCommand')));
					}
				} catch (TypeNotFoundException $e) {
					$bindingErrors->addError(new PropertyPathPart('scriptCommandClassNames', true, $key),
							new MessageCode('common_class_not_found_err', array('class' => $scriptCommandClassName)));
				}
			}
		});
		
		$bc->addClosureValidator(function(array $scriptModificatorClassNames, BindingErrors $bindingErrors) {
			foreach ($scriptModificatorClassNames as $key => $scriptModificatorClassName) {
				if (empty($scriptModificatorClassName)) continue;
				try {
					$class = ReflectionUtils::createReflectionClass($scriptModificatorClassName);
					if (!$class->implementsInterface('rocket\script\entity\modificator\IndependentScriptModificator')) {
						$bindingErrors->addError(new PropertyPathPart('scriptModificatorClassNames', true, $key),
								new MessageCode('common_invalid_class_type_err', array('class' => $scriptModificatorClassName,
										'type' => 'rocket\script\entity\modificator\IndependentScriptModificator')));
					}
				} catch (TypeNotFoundException $e) {
					$bindingErrors->addError(new PropertyPathPart('scriptModificatorClassNames', true, $key),
							new MessageCode('common_class_not_found_err', array('class' => $scriptModificatorClassName)));
				}
			}
		});
		
		$bc->addClosureValidator(function(array $scriptListenerClassNames, BindingErrors $bindingErrors) {
			foreach ($scriptListenerClassNames as $key => $scriptListenerClassName) {
				if (empty($scriptListenerClassName)) continue;
				try {
					$class = ReflectionUtils::createReflectionClass($scriptListenerClassName);
					if (!$class->implementsInterface('rocket\script\entity\listener\IndependentScriptListener')) {
						$bindingErrors->addError(new PropertyPathPart('scriptListenerClassNames', true, $key),
								new MessageCode('common_invalid_class_type_err', array('class' => $scriptListenerClassName,
										'type' => 'rocket\script\entity\listener\IndependentScriptListener')));
					}
				} catch (TypeNotFoundException $e) {
					$bindingErrors->addError(new PropertyPathPart('scriptListenerClassNames', true, $key),
							new MessageCode('common_class_not_found_err', array('class' => $scriptListenerClassName)));
				}
			}
		});
	}

	public function save() {
		$this->componentsStorage->removeFieldClassesByModule($this->module);
		foreach ($this->scriptFieldClassNames as $key => $scriptFieldClassName) {
			if (empty($scriptFieldClassName)) continue;
			$this->componentsStorage->addFieldClass($this->module, 
					ReflectionUtils::createReflectionClass($scriptFieldClassName));
		}
		
		$scriptCommandClasses = array();
		$this->componentsStorage->removeCommandClassesByModule($this->module);
		foreach ($this->scriptCommandClassNames as $key => $scriptCommandClassName) {
			if (empty($scriptCommandClassName)) continue;
			$scriptCommandClass = ReflectionUtils::createReflectionClass($scriptCommandClassName);
			$this->componentsStorage->addCommandClass($this->module, $scriptCommandClass);
			$scriptCommandClasses[$scriptCommandClassName] = $scriptCommandClass;
		}
		
		$this->componentsStorage->removeCommandGroupsByModule($this->module);
		foreach ($this->scriptCommandGroupModels as $scriptCommandGroupModel) {
			if (!strlen($scriptCommandGroupModel->getName())) continue;
			
			$this->componentsStorage->addCommandGroup($this->module, 
					$scriptCommandGroupModel->toScriptCommandGroup($scriptCommandClasses));
		}
		
		$this->componentsStorage->removeConstraintClassesByModule($this->module);
		foreach ($this->scriptModificatorClassNames as $key => $scriptModificatorClassName) {
			if (empty($scriptModificatorClassName)) continue;
			$this->componentsStorage->addConstraintClass($this->module, 
					ReflectionUtils::createReflectionClass($scriptModificatorClassName));
		}
		
		$this->componentsStorage->removeListenerClassesByModule($this->module);
		foreach ($this->scriptListenerClassNames as $key => $scriptListenerClassName) {
			if (empty($scriptModificatorClassName)) continue;
			$this->componentsStorage->addListenerClass($this->module, 
					ReflectionUtils::createReflectionClass($scriptListenerClassName));
		}
			
		$this->componentsStorage->flush($this->module);
	}
}
/**
 * Inner Class
 *
 */
class ScriptCommandGroupModel implements Dispatchable {
	protected $name;
	protected $scriptCommandClassNames = array();
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setScriptCommandClassNames(array $scriptCommandClassNames) {
		$this->scriptCommandClassNames = $scriptCommandClassNames;
	}
	
	public function getScriptCommandClassNames() {
		return $this->scriptCommandClassNames;
	}
	
	private function _validation(BindingConstraints $bc) { }
	
	public static function createFromScriptCommandGroup(ScriptCommandGroup $scriptCommandGroup) {
		$groupModel = new ScriptCommandGroupModel();
		$groupModel->setName($scriptCommandGroup->getName());
		
		$scriptCommandClassNames = array();
		foreach ($scriptCommandGroup->getCommandClasses() as $scriptCommandClass) {
			$scriptCommandClassNames[] = $scriptCommandClass->getName();
		}
		$groupModel->setScriptCommandClassNames($scriptCommandClassNames);
		return $groupModel;
	}
	
	public function toScriptCommandGroup(array $availableScriptCommandClasses) {
		$scriptCommandGroup = new ScriptCommandGroup($this->name);
		foreach ($this->scriptCommandClassNames as $scriptCommandClassName) {
			if (!isset($availableScriptCommandClasses[$scriptCommandClassName])) continue;
			$scriptCommandGroup->addCommandClass($availableScriptCommandClasses[$scriptCommandClassName]);
		}
		return $scriptCommandGroup;
	}
}