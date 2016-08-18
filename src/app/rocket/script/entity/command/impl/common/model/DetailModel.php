<?php
namespace rocket\script\entity\command\impl\common\model;

use rocket\script\entity\manage\model\EntryInfo;
use rocket\script\entity\manage\EntryManager;
use n2n\core\NotYetImplementedException;

class DetailModel implements EntryCommandModel {
	private $entryManager;
	private $entryInfo;
	
	public function __construct(EntryManager $entryManager, EntryInfo $entryInfo) {
		$this->entryManager = $entryManager;
		$this->entryInfo = $entryInfo;
	}
	/**
	 * @return EntryInfo
	 */
	public function getEntryInfo() {
		return $this->entryInfo;
	}
	
	public function publish() {
		throw new NotYetImplementedException();
		if (!$this->scriptSelection->hasDraft()) return false;
		
		$id =  $this->scriptSelection->getId();
		$originalEntry = $this->scriptSelection->getOriginalEntity();
		$draft = $this->scriptSelection->getDraft();
		$draftedEntry = $draft->getDraftedEntity();
		
		$draft->setPublished(true);
		$this->historyModel->saveDraft($draft);
		
		$entityModel = $this->getEntityScript()->getEntityModel();
		$entityModel->copy($draftedEntry, $originalEntry);
		$this->em->merge($originalEntry);
		
		if (is_null($this->translationModel)) return true;
		
		$entityTranslationModel = $this->entityScript->getTranslationModel();
		foreach ($this->translationModel->getTranslationsByElementId($draft->getId(), $draftedEntry) as $translation) {
			$entityTranslationModel->saveTranslation($translation->copy($id));
		}
		
		return true;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\impl\common\model\EntryCommandModel::getEntryModel()
	 */
	public function getEntryModel() {
		return $this->entryInfo;
	}

}