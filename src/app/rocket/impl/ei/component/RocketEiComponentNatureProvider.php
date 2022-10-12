<?php

namespace rocket\impl\ei\component;

use rocket\spec\setup\EiSetupPhase;
use rocket\spec\setup\EiTypeSetup;
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
			return;
		}

		foreach ($eiTypeSetup->getUnassignedEiPresetProps() as $eiPresetProp) {
			$nullAllowed = false;
			foreach ($this->compileTypeNames($eiPresetProp, $nullAllowed) as $typeName) {
				$eiPropNature = null;
				if ($eiSetupPhase === EiSetupPhase::GOOD_MATCHES) {
					$eiPropNature = $this->findGoodPresetMatch($eiPresetProp, $typeName, $nullAllowed, $eiTypeSetup);
				} else {
					$eiPropNature = $this->findSuitablePresetMatch($eiPresetProp, $typeName, $nullAllowed, $eiTypeSetup);
				}

				if ($eiPropNature !== null) {
					$eiTypeSetup->addEiPropNature($eiPresetProp->getName(), $eiPropNature);
				}
			}
		}
	}

	private function compileTypeNames(EiPresetProp $eiPresetProp, bool &$nullAllowed) {
		$accessProxy = $eiPresetProp->getPropertyAccessProxy();

		$namedTypeConstraints = [];
		if ($accessProxy->isWritable()) {
			array_push($namedTypeConstraints,
					...$accessProxy->getSetterConstraint()->getNamedTypeConstraints());
		}
		if (!$eiPresetProp->isEditable()) {
			array_push($namedTypeConstraints,
					...$accessProxy->getGetterConstraint()->getNamedTypeConstraints());
		}

		if (null !== ($type = $accessProxy->getProperty()?->getType())) {
			array_push($namedTypeConstraints,
					...TypeConstraints::type($type)->getNamedTypeConstraints());
		}

		$typeNames = [];
		$nullAllowed = false;
		foreach ($namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->allowsNull()) {
				$nullAllowed = true;
			}

			if ($namedTypeConstraint->isMixed()) {
				continue;
			}

			$typeName = $namedTypeConstraint->getTypeName();
			$typeNames[$typeName] = $typeName;
		}


		return $typeNames;
	}

	private function provideCmdNatures(EiTypeSetup $eiTypeSetup) {
		$attributeSet = $eiTypeSetup->getAttributeSet();

		$readCmdsMode = $eiTypeSetup->getEiPresetMode()?->isReadCmdsMode();
		$editCmdsMode = $eiTypeSetup->getEiPresetMode()?->isEditCmdsMode();

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

	private function findGoodPresetMatch(EiPresetProp $eiPresetProp, string $typeName, bool $nullAllowed) {
		if ($typeName === 'string' && StringUtils::endsWith('Html', $eiPresetProp->getName())) {
			$ckeEiPropNature = new CkeEiPropNature();
			$ckeEiPropNature->setMandatory(!$nullAllowed);
			$ckeEiPropNature->setReadOnly(!$eiPresetProp->isEditable());
			$ckeEiPropNature->setMaxlength(255);
			$this->assignProperties($eiPresetProp, $ckeEiPropNature);
			return $ckeEiPropNature;
		}

		// temporary hack
		if ($eiPresetProp->getEntityProperty() instanceof RelationEntityProperty) {
			/**
			 * @var RelationEntityProperty $relationEntityProperty;
			 */
			$relationEntityProperty = $eiPresetProp->getEntityProperty();

			switch ($relationEntityProperty->getType()) {
				case RelationEntityProperty::TYPE_MANY_TO_MANY:
					$relationEiProp = new ManyToManySelectEiPropNature();
					break;
				case RelationEntityProperty::TYPE_ONE_TO_MANY:
					$relationEiProp = new OneToManySelectEiPropNature();
					break;
				case RelationEntityProperty::TYPE_MANY_TO_ONE:
					$relationEiProp = new ManyToOneSelectEiPropNature();
					$relationEiProp->getRelationModel()->setMandatory(!$nullAllowed);
					break;
				case RelationEntityProperty::TYPE_ONE_TO_ONE:
					$relationEiProp = new OneToOneSelectEiPropNature();
					$relationEiProp->getRelationModel()->setMandatory(!$nullAllowed);
					break;
				default:
					throw new IllegalStateException();
			}
			$relationEiProp->getRelationModel()->setReadOnly(!$eiPresetProp->isEditable());
			$this->assignProperties($eiPresetProp, $relationEiProp);

			return $relationEiProp;
		}

		return null;
	}

	private function findSuitablePresetMatch(EiPresetProp $eiPresetProp, string $typeName, bool $nullAllowed,
			EiTypeSetup $eiTypeSetup) {
		switch ($typeName) {
			case File::class:
				$nature = new FileEiPropNature();
				$nature->setMandatory(!$nullAllowed);
				$nature->setReadOnly(!$eiPresetProp->isEditable());
				$this->assignProperties($nature);
				$this->assignAddons($eiTypeSetup, $eiPresetProp->getPropertyAccessProxy(), $nature);
				return $nature;
			case 'string':
				$nature = new StringEiPropNature();
				$nature->setMandatory(!$nullAllowed);
				$nature->setReadOnly(!$eiPresetProp->isEditable());
				$nature->setMaxlength(255);
				$this->assignProperties($eiPresetProp, $nature);
				$this->assignAddons($eiTypeSetup, $eiPresetProp->getPropertyAccessProxy(), $nature);
				return $nature;
			case 'int':
				$nature = new IntegerEiPropNature();
				$nature->setMandatory(!$nullAllowed);
				$nature->setReadOnly(!$eiPresetProp->isEditable());
				$this->assignProperties($eiPresetProp, $nature);
				$this->assignAddons($eiTypeSetup, $eiPresetProp->getPropertyAccessProxy(), $nature);
				return $nature;
			case 'bool':
				$nature = new BooleanEiPropNature();
				$nature->setMandatory(!$nullAllowed);
				$nature->setReadOnly(!$eiPresetProp->isEditable());
				$this->assignProperties($eiPresetProp, $nature);
				$this->assignAddons($eiTypeSetup, $eiPresetProp->getPropertyAccessProxy(), $nature);
				return $nature;
		}
	}

	private function assignProperties(EiPresetProp $eiPresetProp, PropertyEiPropNature $nature) {
		$nature->setEntityProperty($eiPresetProp->getEntityProperty());
		$nature->setPropertyAccessProxy($eiPresetProp->getPropertyAccessProxy());
	}

	private function assignAddons(EiTypeSetup $eiTypeSetup, PropertyAccessProxy $propertyAccessProxy, AddonEiPropNature $nature) {
		$property = $propertyAccessProxy->getProperty();
		if ($property == null) {
			return;
		}

		$addonAttribute = $eiTypeSetup->getAttributeSet()->getPropertyAttribute($property->getName(), Addon::class);
		if ($addonAttribute === null) {
			return;
		}

		$addon = $addonAttribute->getInstance();

		$nature->setPrefixSiCrumbGroups(SiCrumbGroupFactory::parseCrumbGroups($addon->prefixes));
		$nature->setSuffixSiCrumbGroups(SiCrumbGroupFactory::parseCrumbGroups($addon->suffixes));
	}
}