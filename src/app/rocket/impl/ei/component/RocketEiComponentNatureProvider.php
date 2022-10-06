<?php

namespace rocket\impl\ei\component;

use rocket\spec\setup\EiSetupPhase;
use rocket\spec\setup\EiTypeSetup;
use n2n\util\type\NamedTypeConstraint;
use rocket\spec\setup\EiPresetProp;
use rocket\impl\ei\component\prop\string\StringEiPropNatureNature;
use rocket\impl\ei\component\prop\bool\BooleanEiPropNature;
use rocket\impl\ei\component\cmd\common\OverviewEiCmdNature;
use rocket\impl\ei\component\cmd\common\DetailEiCmdNature;
use rocket\impl\ei\component\cmd\common\EditEiCmdNature;
use rocket\attribute\impl\EiCmdOverview;
use rocket\attribute\impl\EiCmdAdd;
use rocket\attribute\impl\EiCmdEdit;
use rocket\attribute\impl\EiCmdDetail;
use rocket\impl\ei\component\cmd\common\DeleteEiCmdNature;
use rocket\impl\ei\component\cmd\common\AddEiCmdNature;
use rocket\attribute\impl\EiCmdDelete;
use rocket\spec\setup\EiComponentNatureProvider;

class RocketEiComponentNatureProvider implements EiComponentNatureProvider {

	/**
	 * @inheritDoc
	 */
	public function provide(EiTypeSetup $eiTypeSetup, EiSetupPhase $eiSetupPhase): void {
		if ($eiSetupPhase === EiSetupPhase::PERFECT_MATCHES) {
			$this->provideCmdNatures($eiTypeSetup);
			return;
		}

		foreach ($eiTypeSetup->getUnassignedEiPresetProps() as $eiPresetProp) {
			$namedTypeConstraints = $eiPresetProp->getObjectPropertyAccessProxy()->getConstraint()
					->getNamedTypeConstraints();

			if (count($namedTypeConstraints) !== 1) {
				continue;
			}

			$eiPropNature = null;
			if ($eiSetupPhase === EiSetupPhase::GOOD_MATCHES) {
				$eiPropNature = $this->findGoodPresetMatch($eiPresetProp, $namedTypeConstraints[0]);
			} else {
				$eiPropNature = $this->findSuitablePresetMatch($eiPresetProp, $namedTypeConstraints[0]);
			}

			if ($eiPropNature !== null) {
				$eiTypeSetup->addEiPropNature($eiPresetProp->getName(), $eiPropNature);
			}
		}

	}

	private function provideCmdNatures(EiTypeSetup $eiTypeSetup) {
		$attributeSet = $eiTypeSetup->getAttributeSet();

		$readCmdsMode = $eiTypeSetup->getEiPresetMode()?->hasReadCmds();
		$editCmdsMode = $eiTypeSetup->getEiPresetMode()?->hasEditCmds();

		$eiCmdOverviewAttribute = $attributeSet->getClassAttribute(EiCmdOverview::class);
		if ($eiCmdOverviewAttribute !== null) {
			$pageSize = $eiCmdOverviewAttribute->getInstance()->pageSize;
			$eiTypeSetup->addEiCmdNature((new OverviewEiCmdNature())->setPageSize($pageSize));
		} else if ($readCmdsMode) {
			$eiTypeSetup->addEiCmdNature(new OverviewEiCmdNature());
		}

		if ($readCmdsMode || $attributeSet->hasClassAttribute(EiCmdDetail::class)) {
			$eiTypeSetup->addEiCmdNature(new DetailEiCmdNature());
		}

		if ($editCmdsMode || $attributeSet->hasClassAttribute(EiCmdEdit::class)) {
			$eiTypeSetup->addEiCmdNature(new EditEiCmdNature());
		}

		$eiCmdAddAttribute = $attributeSet->getClassAttribute(EiCmdAdd::class);
		if ($eiCmdAddAttribute !== null) {
			$duplicatingAllowed = $eiCmdAddAttribute->getInstance()->duplicatingAllowed;
			$eiTypeSetup->addEiCmdNature((new AddEiCmdNature())->setDuplicatingAllowed($duplicatingAllowed));
		} else if ($editCmdsMode) {
			$eiTypeSetup->addEiCmdNature(new AddEiCmdNature());
		}

		if ($editCmdsMode || $attributeSet->hasClassAttribute(EiCmdDelete::class)) {
			$eiTypeSetup->addEiCmdNature(new DeleteEiCmdNature());
		}
	}

	private function findGoodPresetMatch(EiPresetProp $eiPresetProp, NamedTypeConstraint $namedTypeConstraint) {

	}

	private function findSuitablePresetMatch(EiPresetProp $eiPresetProp, NamedTypeConstraint $namedTypeConstraint) {
		switch ($namedTypeConstraint->getTypeName()) {
			case 'string':
				$stringEiProp = new StringEiPropNatureNature();
				$stringEiProp->setMandatory(!$namedTypeConstraint->allowsNull());
				$stringEiProp->setReadOnly(!$eiPresetProp->isEditable());
				$stringEiProp->setMaxlength(255);
				return $stringEiProp;
			case 'bool':
				$booleanEiProp = new BooleanEiPropNature();
				$booleanEiProp->setMandatory(!$namedTypeConstraint->allowsNull());
				$booleanEiProp->setReadOnly(!$eiPresetProp->isEditable());
				return $booleanEiProp;
		}
	}
}