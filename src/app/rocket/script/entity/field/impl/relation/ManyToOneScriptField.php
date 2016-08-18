<?php
namespace rocket\script\entity\field\impl\relation;

use n2n\util\Attributes;
use n2n\persistence\orm\property\ManyToOneProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\core\SetupProcess;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\relation\option\ToOneOption;
use rocket\script\entity\manage\EntryManageUtils;
use rocket\script\entity\field\impl\relation\model\SimpleScriptFieldRelation;
use rocket\script\entity\field\impl\ManageInfo;

class ManyToOneScriptField extends SimpleToOneScriptFieldAdapter implements RelationScriptField {

	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		
		$this->initilaize(new SimpleScriptFieldRelation($this, true, false));
	}
	
	public function getTypeName() {
		return 'ManyToOne';
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof ManyToOneProperty;
	}
		
	public function setup(SetupProcess $setupProcess) {
		$this->fieldRelation->setup($setupProcess);
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$scriptState = $manageInfo->getScriptState();
		$embeddedAddActivated = $this->fieldRelation->isEmbeddedAddActivated($scriptState);
		$targetUtils = new EntryManageUtils($this->fieldRelation->createTargetPseudoScriptState(
				$scriptState, $scriptSelectionMapping->getScriptSelection(), $embeddedAddActivated));

		$toOneOption = new ToOneOption($this->getId(), $this->getLabel(), $scriptSelectionMapping, $targetUtils,
				$this->isRequired($scriptSelectionMapping, $manageInfo));
		$toOneOption->setTargetScriptSelection($this->createTargetScriptSelection($scriptSelectionMapping));
		$toOneOption->setEmbeddedAddEnabled($embeddedAddActivated);
		
		$targetEntities = $this->lookupSelectableEntites($scriptState->getEntityManager());
		
		$toOneOption->setSelectableEntities($this->filterAccessableTargetEntities(
				$targetEntities, $scriptSelectionMapping->getSelectionPrivilegeConstraint()));		
		
		return $toOneOption;
	}
}