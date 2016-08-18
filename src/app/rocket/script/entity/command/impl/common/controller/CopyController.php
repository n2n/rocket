<?php
namespace rocket\script\entity\command\impl\common\controller;

use n2n\core\DynamicTextCollection;
use rocket\script\entity\EntityScript;
use rocket\script\core\ManageState;
use n2n\http\ControllerAdapter;
use n2n\reflection\ReflectionContext;
use rocket\script\entity\EntityChangeEvent;
use rocket\script\entity\manage\display\Editable;
use n2n\http\NoHttpRefererGivenException;
use rocket\script\entity\field\PropertyScriptField;

class CopyController extends ControllerAdapter {
	/**
	 * @var \rocket\script\entity\EntityScript
	 */
	private $entityScript;
	private $dtc;
	private $utils;
	
	private function _init(DynamicTextCollection $dtc, ManageState $manageState) {
		$this->dtc = $dtc;
		$this->utils = new EntryControllingUtils($this->entityScript, $manageState);
	}
	
	public function setEntityScript(EntityScript $entityScript) {
		$this->entityScript = $entityScript;
	}
	
	public function index($id) {
		$scriptState = $this->utils->getScriptState();
		$scriptSelection = $scriptState->getScriptSelection();

		$em = $scriptState->getEntityManager();;
		$currentObject = $em->find($this->entityScript->getEntityModel()->getClass(), $id);
		$newObject = ReflectionContext::createObject($this->entityScript->getEntityModel()->getClass());
		foreach ($this->entityScript->getFieldCollection()->toArray() as $scriptField) {
			if (!($scriptField instanceof Editable) || $scriptField->isReadOnly() || !($scriptField instanceof PropertyScriptField)) continue;
			$accessProxy = $scriptField->getPropertyAccessProxy();
			$accessProxy->setValue($newObject, $scriptField->getEntityProperty()->copy($accessProxy->getValue($currentObject)));
		}
		$scriptState->triggerOnNewObject($em, $newObject);
		
		$this->entityScript->notifyObjectMod(EntityChangeEvent::TYPE_ON_INSERT, $newObject);
		$em->persist($newObject);
		$this->entityScript->notifyObjectMod(EntityChangeEvent::TYPE_INSERTED, $newObject);
		
		try {
			$this->redirectToReferer();
		} catch (NoHttpRefererGivenException $e) {
			$this->redirectToController($this->entityScript->getEntryDetailPathExt($scriptSelection->toNavPoint()),
					null, null, $scriptState->getControllerContext());
			return;
		}
	}
}