<?php

namespace rocket\impl\ei\component;

use rocket\spec\setup\EiSetupPhase;
use rocket\spec\setup\EiTypeSetup;
use n2n\util\type\NamedTypeConstraint;
use rocket\spec\setup\EiPresetProp;
use rocket\impl\ei\component\prop\string\StringEiPropNature;
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
use n2n\util\type\TypeConstraint;
use rocket\attribute\EiPreset;
use rocket\impl\ei\component\prop\numeric\IntegerEiPropNature;
use rocket\impl\ei\component\mod\callback\CallbackEiModNature;
use rocket\attribute\impl\EiSetup;
use rocket\impl\ei\component\mod\callback\CallbackFinder;
use n2n\util\magic\MagicObjectUnavailableException;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\magic\MagicContext;
use n2n\context\attribute\Inject;
use rocket\attribute\impl\EiMods;
use rocket\impl\ei\component\mod\callback\StaticCallbackEiModNature;
use n2n\util\magic\MagicLookupFailedException;

class RocketEiComponentNatureProvider implements EiComponentNatureProvider {

	#[Inject]
	private MagicContext $magicContext;

	/**
	 * @inheritDoc
	 */
	public function provide(EiTypeSetup $eiTypeSetup, EiSetupPhase $eiSetupPhase): void {
		if ($eiSetupPhase === EiSetupPhase::PERFECT_MATCHES) {
			$this->provideCmdNatures($eiTypeSetup);
			$this->provideModNatures($eiTypeSetup);
			return;
		}

		foreach ($eiTypeSetup->getUnassignedEiPresetProps() as $eiPresetProp) {
			$nullAllowed = false;
			foreach ($this->compileTypeNames($eiPresetProp, $nullAllowed) as $typeName) {
				$eiPropNature = null;
				if ($eiSetupPhase === EiSetupPhase::GOOD_MATCHES) {
					$eiPropNature = $this->findGoodPresetMatch($eiPresetProp, $typeName, $nullAllowed);
				} else {
					$eiPropNature = $this->findSuitablePresetMatch($eiPresetProp, $typeName, $nullAllowed);
				}

				if ($eiPropNature !== null) {
					$eiTypeSetup->addEiPropNature($eiPresetProp->getName(), $eiPropNature);
				}
			}
		}

	}

	private function compileTypeNames(EiPresetProp $eiPresetProp, bool &$nullAllowed) {
		$accessProxy = $eiPresetProp->getObjectPropertyAccessProxy();

		$namedTypeConstraints = [];
		if ($accessProxy->isWritable()) {
			array_push($namedTypeConstraints,
					...$accessProxy->getSetterConstraint()->getNamedTypeConstraints());
		}
		if (!$eiPresetProp->isEditable()) {
			array_push($namedTypeConstraints,
					...$accessProxy->getGetterConstraint()->getNamedTypeConstraints());
		}

		$typeNames = [];
		$nullAllowed = false;
		foreach ($namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->isMixed()) {
				continue;
			}

			if ($namedTypeConstraint->allowsNull()) {
				$nullAllowed = true;
			}

			$typeName = $namedTypeConstraint->getTypeName();
			$typeNames[$typeName] = $typeName;
		}

		return $typeNames;
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

	private function provideModNatures(EiTypeSetup $eiTypeSetup) {
		$eiTypeSetup->addEiModNature(new StaticCallbackEiModNature($eiTypeSetup->getClass()));

		$eiModsAttribute = $eiTypeSetup->getAttributeSet()->getClassAttribute(EiMods::class);
		if ($eiModsAttribute === null) {
			return;
		}

		foreach ($eiModsAttribute->getInstance()->lookupIds as $lookupId) {
			try {
				$eiTypeSetup->addEiModNature(new CallbackEiModNature($this->magicContext->lookup($lookupId)));
			} catch (MagicLookupFailedException $e) {
				throw new ConfigurationError('Invalid lookup id \'' . $lookupId . '\' annotated. Reason: '
								. $e->getMessage(),
						$eiModsAttribute->getFile(), $eiModsAttribute->getLine(), previous: $e);
			}
		}
	}

	private function findGoodPresetMatch(EiPresetProp $eiPresetProp, string $typeName, bool $nullAllowed) {

	}

	private function findSuitablePresetMatch(EiPresetProp $eiPresetProp, string $typeName, bool $nullAllowed) {
		switch ($typeName) {
			case 'string':
				$stringEiProp = new StringEiPropNature();
				$stringEiProp->setMandatory(!$nullAllowed);
				$stringEiProp->setReadOnly(!$eiPresetProp->isEditable());
				$stringEiProp->setMaxlength(255);
				return $stringEiProp;
			case 'int':
				$intEiProp = new IntegerEiPropNature();
				$intEiProp->setMandatory(!$nullAllowed);
				$intEiProp->setReadOnly(!$eiPresetProp->isEditable());
				return $intEiProp;
			case 'bool':
				$booleanEiProp = new BooleanEiPropNature();
				$booleanEiProp->setMandatory(!$nullAllowed);
				$booleanEiProp->setReadOnly(!$eiPresetProp->isEditable());
				return $booleanEiProp;
		}
	}
}