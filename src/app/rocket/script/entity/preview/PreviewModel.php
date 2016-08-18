<?php
namespace rocket\script\entity\preview;

use n2n\core\IllegalStateException;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\PropertyPath;
use rocket\script\entity\manage\model\EntryModel;

class PreviewModel {
	private $entryModel;
	private $basePropertyPath;
	private $mainDispatchable;
	
	public function __construct(EntryModel $entryModel, PropertyPath $basePropertyPath = null, Dispatchable $mainDispatchable = null) {
		$this->entryModel = $entryModel;
		$this->basePropertyPath = $basePropertyPath;
		
		$this->mainDispatchable = $mainDispatchable;
	}
	
	public function hasMainDispatchable() {
		return isset($this->mainDispatchable);
	}
	
	public function getMainDispatchable() {
		return $this->mainDispatchable;
	}
	
	public function createPropertyPath($propertyName) {
		if (!$this->isEditable()) {
			throw new IllegalStateException('EntryModel not editable.');
		}
		return $this->entryModel->createPropertyPath($propertyName, $this->basePropertyPath);
	}
	
	public function isEditable() {
		return $this->entryModel instanceof EditEntryModel;
	}
	
	public function getScriptState() {
		return $this->entryModel->getScriptState();
	}
	
	public function getCurrentEntity() {
		return $this->entryModel->getScriptSelectionMapping()->getScriptSelection()->getEntity();
	}
	
	public function getEntryModel() {
		return $this->entryModel;
	}
}