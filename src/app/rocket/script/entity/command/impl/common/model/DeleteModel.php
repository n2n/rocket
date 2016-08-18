<?php
namespace rocket\script\entity\command\impl\common\model;

class DeleteModel extends CommandEntryModelAdapter {
			
	public function delete() {
		if ($this->scriptSelection->hasDraft()) {
			$this->draftModel->removeDraft($this->scriptSelection->getDraft());
			return;
		}
		
		$this->scriptState->triggerOnRemoveObject($this->em, $this->scriptSelection);
		$this->em->remove($this->scriptSelection->getOriginalEntity());
	}
}