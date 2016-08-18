<?php
namespace rocket\script\entity\command\impl\common\model;

use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\map\BindingConstraints;
use n2n\reflection\annotation\AnnotationSet;
use rocket\script\entity\manage\model\EntryForm;
use rocket\script\entity\manage\EntryManager;
use rocket\script\entity\manage\mapping\MappingValidationResult;
use n2n\core\MessageContainer;

class AddModel implements Dispatchable, EntryCommandModel  {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('entryForm')));
		$as->m('create', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $entryManager;
	private $entryForm;
	
	public function __construct(EntryManager $entryManager, EntryForm $entryForm) {
		$this->entryManager = $entryManager;
		$this->entryForm = $entryForm;
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
	
	private function _validation(BindingConstraints $bc) {
	}
		
	public function create(MessageContainer $messageContainer) {
		$scriptSelectionMapping = $this->entryForm->buildScriptSelectionMapping();
		
		$this->entryManager->create($scriptSelectionMapping);

		$mappingValidationResult = new MappingValidationResult();
		if (!$scriptSelectionMapping->save($mappingValidationResult)) {
			$messageContainer->addAll($mappingValidationResult->getMessages());
			return false;
		}
		
		// @todo think!!!
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$this->entryManager->getScriptState()->getEntityManager()->persist(
				$scriptSelection->getEntity());
		$this->entryManager->getScriptState()->getEntityManager()->flush();
		$scriptSelection->setId($this->entryManager->getScriptState()
				->getContextEntityScript()->extractId($scriptSelection->getEntity()));
		
		return $scriptSelection;
	}
}