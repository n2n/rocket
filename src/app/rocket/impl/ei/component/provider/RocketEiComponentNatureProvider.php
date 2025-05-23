<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */

namespace rocket\impl\ei\component\provider;

use rocket\op\spec\setup\EiSetupPhase;
use rocket\op\spec\setup\EiTypeSetup;
use rocket\op\spec\setup\EiPresetProp;
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
use rocket\op\spec\setup\EiComponentNatureProvider;
use rocket\impl\ei\component\prop\numeric\IntegerEiPropNature;
use rocket\impl\ei\component\mod\callback\CallbackEiModNature;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\magic\MagicContext;
use n2n\context\attribute\Inject;
use rocket\attribute\impl\EiModCallback;
use rocket\impl\ei\component\mod\callback\StaticCallbackEiModNature;
use n2n\util\magic\MagicLookupFailedException;
use n2n\util\StringUtils;
use rocket\impl\ei\component\prop\string\cke\CkeEiPropNature;
use n2n\io\managed\File;
use rocket\impl\ei\component\prop\file\FileEiPropNature;
use rocket\impl\ei\component\prop\adapter\PropertyEiPropNature;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\impl\ei\component\prop\relation\ManyToManySelectEiPropNature;
use rocket\impl\ei\component\prop\relation\OneToManySelectEiPropNature;
use rocket\impl\ei\component\prop\relation\OneToOneSelectEiPropNature;
use rocket\impl\ei\component\prop\relation\ManyToOneSelectEiPropNature;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\TypeConstraints;
use n2n\context\attribute\ThreadScoped;
use rocket\impl\ei\component\prop\meta\AddonEiPropNature;
use n2n\persistence\orm\property\ClassSetup;
use rocket\attribute\impl\Addon;
use rocket\impl\ei\component\prop\meta\SiCrumbGroupFactory;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\impl\ei\component\prop\translation\Translatable;
use rocket\impl\ei\component\prop\translation\TranslationEiPropNature;
use n2n\persistence\orm\attribute\OneToMany;
use n2n\reflection\attribute\Attribute;
use n2n\persistence\orm\CascadeType;
use rocket\op\ei\component\prop\EiProp;
use n2n\reflection\attribute\PropertyAttribute;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use rocket\attribute\impl\EiPropBool;
use rocket\impl\ei\component\prop\enum\EnumEiPropNature;
use rocket\attribute\impl\EiPropEnum;
use rocket\op\ei\component\InvalidEiConfigurationException;
use n2n\reflection\property\AccessProxy;

#[ThreadScoped]
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

			foreach ($eiTypeSetup->getEiTypeClassSetups() as $eiTypeClassSetup) {
				$eiPropNatureProvider = new EiPropNatureProvider($eiTypeSetup, $eiTypeClassSetup, $this->magicContext);
				$eiPropNatureProvider->provideAnnotateds();
			}
			return;
		}

		foreach ($eiTypeSetup->getEiTypeClassSetups() as $eiTypeClassSetup) {
			$eiPropNatureProvider = new EiPropNatureProvider($eiTypeSetup, $eiTypeClassSetup, $this->magicContext);
			foreach ($eiTypeClassSetup->getUnassignedEiPresetProps() as $eiPresetProp) {
				if ($eiSetupPhase === EiSetupPhase::GOOD_MATCHES) {
					$eiPropNatureProvider->provideRelation($eiPresetProp)
							|| $eiPropNatureProvider->provideEmbedded($eiPresetProp)
							|| $eiPropNatureProvider->provideCommon($eiPresetProp);
				} else {
					$eiPropNatureProvider->provideFallback($eiPresetProp);
				}
			}
		}
	}

	private function provideCmdNatures(EiTypeSetup $eiTypeSetup) {
		$attributeSet = $eiTypeSetup->getAttributeSet();

		$readCmdsMode = $eiTypeSetup->getEiPresetMode()?->isReadCmdsMode();
		$editCmdsMode = $eiTypeSetup->getEiPresetMode()?->isEditCmdsMode();

		$eiCmdOverviewAttribute = $attributeSet->getClassAttribute(EiCmdOverview::class);
		if ($eiCmdOverviewAttribute !== null) {
			$pageSize = $eiCmdOverviewAttribute->getInstance()->pageSize;
			$eiTypeSetup->addEiCmdNature((new OverviewEiCmdNature())->setPageSize($pageSize));
		} else if ($readCmdsMode || $editCmdsMode) {
			$eiTypeSetup->addEiCmdNature(new OverviewEiCmdNature());
		}

		if ($readCmdsMode || $editCmdsMode || $attributeSet->hasClassAttribute(EiCmdDetail::class)) {
			$eiTypeSetup->addEiCmdNature(new DetailEiCmdNature());
		}

		if ($editCmdsMode || $attributeSet->hasClassAttribute(EiCmdEdit::class)) {
			$eiTypeSetup->addEiCmdNature(new EditEiCmdNature());
		}

		$eiCmdAddAttribute = $attributeSet->getClassAttribute(EiCmdAdd::class);
		if ($eiCmdAddAttribute !== null) {
			$eiCmdAdd = $eiCmdAddAttribute->getInstance();
			$eiTypeSetup->addEiCmdNature((new AddEiCmdNature())
					->setDuplicatingAllowed($eiCmdAdd->duplicatingAllowed)
					->setControlLabel($eiCmdAdd->controlLabel));
		} else if ($editCmdsMode) {
			$eiTypeSetup->addEiCmdNature(new AddEiCmdNature());
		}

		if ($editCmdsMode || $attributeSet->hasClassAttribute(EiCmdDelete::class)) {
			$eiTypeSetup->addEiCmdNature(new DeleteEiCmdNature());
		}
	}

	private function provideModNatures(EiTypeSetup $eiTypeSetup) {
		$eiTypeSetup->addEiModNature(new StaticCallbackEiModNature($eiTypeSetup->getClass()));

		$eiModsAttribute = $eiTypeSetup->getAttributeSet()->getClassAttribute(EiModCallback::class);
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
}