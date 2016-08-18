<?php
namespace rocket\script\entity\command\impl\tree\model;

use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use n2n\persistence\orm\NestedSetUtils;
use rocket\script\entity\command\impl\common\model\AddModel;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\model\EntryForm;
use rocket\script\entity\manage\EntryManager;
use rocket\script\entity\manage\mapping\MappingValidationResult;
use n2n\core\MessageContainer;
use n2n\persistence\orm\EntityModel;

class TreeAddModel extends AddModel {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('entryForm')));
		$as->m('create', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $entryManager;
	private $entryForm;
	private $rootIdPropertyName;
	private $treeEntityModel;
	private $leftPropertyName;
	private $rightPropertyName;
	private $parentScriptSelection;
	
	public function __construct(EntryManager $entryManager, EntryForm $entryForm, 
			EntityModel $treeEntityModel, $rootIdPropertyName, $leftPropertyName, $rightPropertyName) {
		$this->entryManager = $entryManager;
		$this->entryForm = $entryForm;
		
		$this->treeEntityModel = $treeEntityModel;
		$this->rootIdPropertyName = $rootIdPropertyName;
		$this->leftPropertyName = $leftPropertyName;
		$this->rightPropertyName = $rightPropertyName;
	}	
	
	public function setParentEntity(ScriptSelection $parentScriptSelection) {
		return $this->parentScriptSelection = $parentScriptSelection;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\impl\common\model\EntryCommandModel::getEntryModel()
	 */
	public function getEntryModel() {
		return $this->entryForm->getMainEntryFormPart();
	}
	
	public function getEntryForm() {
		return $this->entryForm;
	}
	
	public function setEntryForm(EntryForm $entryForm) {
		$this->entryForm = $entryForm;
	}
	
	public function create(MessageContainer $messageContainer) {
		$parentEntity = null;
		if ($this->parentScriptSelection !== null) {
			$parentEntity = $this->parentScriptSelection->getOriginalEntity();
		}
		
		$scriptSelectionMapping = $this->entryForm->buildScriptSelectionMapping();
		
		$this->entryManager->create($scriptSelectionMapping);

		$mappingValidationResult = new MappingValidationResult();
		if (!$scriptSelectionMapping->save($mappingValidationResult)) {
			$messageContainer->addAll($mappingValidationResult->getMessages());
			return false;
		}
		
		$entity = $scriptSelectionMapping->getScriptSelection()->getEntity();
		$scriptState = $this->getEntryModel()->getScriptState();
		$em = $scriptState->getEntityManager();
		
		$nestedSetUtils = new NestedSetUtils($em, $this->treeEntityModel->getClass());
		$nestedSetUtils->setRootIdPropertyName($this->rootIdPropertyName);
		$nestedSetUtils->setLeftPropertyName($this->leftPropertyName);
		$nestedSetUtils->setRightPropertyName($this->rightPropertyName);
		$nestedSetUtils->createNestedSetItem($entity, $parentEntity);
		
		$em->flush();

		return new ScriptSelection($scriptState->getContextEntityScript()->extractId($entity), $entity);
	}
}