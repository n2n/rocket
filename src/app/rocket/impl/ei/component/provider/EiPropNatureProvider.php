<?php

namespace rocket\impl\ei\component\provider;

use rocket\spec\setup\EiTypeSetup;
use rocket\attribute\impl\EiPropBool;
use rocket\impl\ei\component\prop\bool\BooleanEiPropNature;
use rocket\attribute\impl\EiPropEnum;
use rocket\impl\ei\component\prop\enum\EnumEiPropNature;
use rocket\attribute\impl\EiPropDecimal;
use rocket\impl\ei\component\prop\numeric\DecimalEiPropNature;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\impl\ei\component\prop\meta\AddonEiPropNature;
use rocket\attribute\impl\Addon;
use rocket\impl\ei\component\prop\meta\SiCrumbGroupFactory;
use n2n\reflection\property\AccessProxy;
use rocket\impl\ei\component\prop\adapter\EditableEiPropNature;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\reflection\attribute\PropertyAttribute;
use n2n\persistence\orm\CascadeType;
use n2n\util\ex\err\ConfigurationError;
use rocket\ei\component\InvalidEiConfigurationException;
use rocket\spec\setup\EiPresetProp;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\impl\ei\component\prop\relation\ManyToManySelectEiPropNature;
use rocket\impl\ei\component\prop\translation\Translatable;
use rocket\impl\ei\component\prop\translation\TranslationEiPropNature;
use n2n\persistence\orm\attribute\OneToMany;
use rocket\impl\ei\component\prop\relation\OneToManySelectEiPropNature;
use rocket\impl\ei\component\prop\relation\ManyToOneSelectEiPropNature;
use rocket\impl\ei\component\prop\relation\OneToOneSelectEiPropNature;
use n2n\util\ex\IllegalStateException;
use n2n\io\managed\File;
use rocket\impl\ei\component\prop\file\FileEiPropNature;
use n2n\util\StringUtils;
use rocket\impl\ei\component\prop\string\cke\CkeEiPropNature;
use rocket\impl\ei\component\prop\string\StringEiPropNature;
use rocket\impl\ei\component\prop\numeric\IntegerEiPropNature;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\l10n\N2NLocaleEiPropNature;
use rocket\impl\ei\component\prop\date\DateTimeEiPropNature;
use rocket\impl\ei\component\prop\string\StringDisplayEiPropNature;

class EiPropNatureProvider {

	function __construct(private EiTypeSetup $eiTypeSetup) {
	}

	function provideAnnotateds() {
		foreach ($this->eiTypeSetup->getAttributeSet()->getPropertyAttributesByName(EiPropBool::class)
				 as $eiPropBoolAttribute) {
			$eiPropBool = $eiPropBoolAttribute->getInstance();
			$propertyName = $eiPropBoolAttribute->getProperty()->getName();

			$nature = new BooleanEiPropNature($this->getPropertyAccessProxy($eiPropBoolAttribute, $eiPropBool->readOnly));
			$nature->setEntityProperty($this->eiTypeSetup->getEntityProperty($propertyName));
			$this->configureEditiable($eiPropBool->constant, $eiPropBool->readOnly, $eiPropBool->mandatory,
					$nature->getPropertyAccessProxy(), $nature);

			$nature->setOnAssociatedDefPropPaths($eiPropBool->onAssociatedDefPropPaths);
			$nature->setOffAssociatedDefPropPaths($eiPropBool->offAssociatedDefPropPaths);

			$this->eiTypeSetup->addEiPropNature($propertyName, $nature);
		}

		foreach ($this->eiTypeSetup->getAttributeSet()->getPropertyAttributesByName(EiPropEnum::class)
				 as $eiPropEnumAttribute) {
			$eiPropEnum = $eiPropEnumAttribute->getInstance();
			$propertyName = $eiPropEnumAttribute->getProperty()->getName();

			$nature = new EnumEiPropNature($this->getPropertyAccessProxy($eiPropEnumAttribute, $eiPropEnum->readOnly));
			$nature->setEntityProperty($this->eiTypeSetup->getEntityProperty($propertyName));
			$nature->setAssociatedDefPropPathMap($eiPropEnum->associatedDefPropPathMap);

			$this->configureEditiable($eiPropEnum->constant, $eiPropEnum->readOnly, $eiPropEnum->mandatory,
					$nature->getPropertyAccessProxy(), $nature);

			$this->eiTypeSetup->addEiPropNature($propertyName, $nature);
		}

		foreach ($this->eiTypeSetup->getAttributeSet()->getPropertyAttributesByName(EiPropDecimal::class)
				 as $eiPropDecimalAttribute) {
			$eiPropDecimal = $eiPropDecimalAttribute->getInstance();
			$propertyName = $eiPropDecimalAttribute->getProperty()->getName();

			$nature = new DecimalEiPropNature($this->getPropertyAccessProxy($eiPropDecimalAttribute, $eiPropDecimal->readOnly));
			$nature->setEntityProperty($this->eiTypeSetup->getEntityProperty($propertyName));
			$nature->setDecimalPlaces($eiPropDecimal->decimalPlaces);

			$this->configureEditiable($eiPropDecimal->constant, $eiPropDecimal->readOnly, $eiPropDecimal->mandatory,
					$nature->getPropertyAccessProxy(), $nature);
			$this->configureAddons($nature->getPropertyAccessProxy(), $nature);

			$this->eiTypeSetup->addEiPropNature($propertyName, $nature);
		}
	}

	function provideRelation(EiPresetProp $eiPresetProp): bool{
		// temporary hack
		if (!($eiPresetProp->getEntityProperty() instanceof RelationEntityProperty)) {
			return false;
		}

		/**
		 * @var RelationEntityProperty $entityProperty;
		 */
		$entityProperty = $eiPresetProp->getEntityProperty();
		$accessProxy = $eiPresetProp->getPropertyAccessProxy();
		$nullAllowed = $accessProxy->getSetterConstraint()->allowsNull();

		switch ($entityProperty->getType()) {
			case RelationEntityProperty::TYPE_MANY_TO_MANY:
				$relationEiProp = new ManyToManySelectEiPropNature($entityProperty, $accessProxy);
				break;
			case RelationEntityProperty::TYPE_ONE_TO_MANY:
				if ($entityProperty->getTargetEntityModel()->getClass()->implementsInterface(Translatable::class)) {
					$relationEiProp = new TranslationEiPropNature($entityProperty, $accessProxy);
					$this->checkCascadeAllAndOrphanRemoval($relationEiProp, $this->eiTypeSetup->getAttributeSet()
							->getPropertyAttribute($eiPresetProp->getName(), OneToMany::class));
				} else {
					$relationEiProp = new OneToManySelectEiPropNature($entityProperty, $accessProxy);
				}
				break;
			case RelationEntityProperty::TYPE_MANY_TO_ONE:
				$relationEiProp = new ManyToOneSelectEiPropNature($entityProperty, $accessProxy);
				$relationEiProp->getRelationModel()->setMandatory(!$nullAllowed);
				break;
			case RelationEntityProperty::TYPE_ONE_TO_ONE:
				$relationEiProp = new OneToOneSelectEiPropNature($entityProperty, $accessProxy);
				$relationEiProp->getRelationModel()->setMandatory(!$nullAllowed);
				break;
			default:
				throw new IllegalStateException();
		}
		$relationEiProp->getRelationModel()->setReadOnly(!$eiPresetProp->isEditable());
		$relationEiProp->setLabel($eiPresetProp->getLabel());

		$this->eiTypeSetup->addEiPropNature($eiPresetProp->getName(), $relationEiProp);

		return true;
	}

	function provideCommon(EiPresetProp $eiPresetProp): bool {
		$nullAllowed = false;
		foreach (NatureProviderUtils::compileTypeNames($eiPresetProp, $nullAllowed) as $typeName) {
			if ($this->provideObjectPropNaturesByType($eiPresetProp, $typeName, $nullAllowed)
					|| $this->providePrimitivePropNaturesByType($eiPresetProp, $typeName, $nullAllowed)) {
				return true;
			}
		}

		return false;
	}

	private function provideObjectPropNaturesByType(EiPresetProp $eiPresetProp, string $typeName, bool $nullAllowed): bool {
		switch ($typeName) {
			case File::class:
				$nature = new FileEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			case N2nLocale::class:
				$nature = new N2NLocaleEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			case \DateTime::class:
				$nature = new DateTimeEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			default:
				return false;
		}

		$nature->setLabel($eiPresetProp->getLabel());
		$nature->setEntityProperty($eiPresetProp->getEntityProperty());
		$this->configureEditiable(null, !$eiPresetProp->isEditable(), !$nullAllowed, $eiPresetProp->getPropertyAccessProxy(),
				$nature);

		$this->eiTypeSetup->addEiPropNature($eiPresetProp->getName(), $nature);

		return true;
	}

	private function providePrimitivePropNaturesByType(EiPresetProp $eiPresetProp, string $typeName, bool $nullAllowed): bool {
		switch ($typeName) {

			case 'string':
				$nature = (StringUtils::endsWith('Html', $eiPresetProp->getName())
						? new CkeEiPropNature($eiPresetProp->getPropertyAccessProxy())
						: new StringEiPropNature($eiPresetProp->getPropertyAccessProxy()));
				$nature->setMaxlength(255);
				break;
			case 'int':
				$nature = new IntegerEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			case 'bool':
				$nature = new BooleanEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			case 'float':
				$nature = new DecimalEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			default:
				return false;
		}

		$nature->setLabel($eiPresetProp->getLabel());
		$nature->setEntityProperty($eiPresetProp->getEntityProperty());
		$this->configureEditiable(null, !$eiPresetProp->isEditable(), !$nullAllowed, $eiPresetProp->getPropertyAccessProxy(),
				$nature);
		$this->configureAddons($eiPresetProp->getPropertyAccessProxy(), $nature);

		$this->eiTypeSetup->addEiPropNature($eiPresetProp->getName(), $nature);

		return true;
	}

	function provideFallback(EiPresetProp $eiPresetProp): bool {
		if ($eiPresetProp->isEditable()) {
			return false;
		}

		$this->eiTypeSetup->addEiPropNature($eiPresetProp->getName(),
				new StringDisplayEiPropNature($eiPresetProp->getPropertyAccessProxy()));
		return true;
	}

	private function getPropertyAccessProxy(PropertyAttribute $attribute, ?bool $readOnly): PropertyAccessProxy {
		try {
			return $this->eiTypeSetup->getPropertyAccessProxy($attribute->getProperty()->getName(), $readOnly ?? false);
		} catch (\ReflectionException $e) {
			throw $this->eiTypeSetup->createPropertyAttributeError($attribute, $e);
		}
	}

	private function configureEditiable(?bool $constant, ?bool $readOnly, ?bool $mandatory, AccessProxy $accessProxy,
			EditableEiPropNature $nature) {
		$nature->setConstant($constant ?? false);
		$nature->setReadOnly($readOnly ?? !$accessProxy->isWritable());
		$nature->setMandatory($mandatory ?? $accessProxy->getSetterConstraint()->allowsNull());
	}

	private function configureAddons(PropertyAccessProxy $propertyAccessProxy, AddonEiPropNature $nature): void {
		$property = $propertyAccessProxy->getProperty();
		if ($property == null) {
			return;
		}

		$addonAttribute = $this->eiTypeSetup->getAttributeSet()->getPropertyAttribute($property->getName(), Addon::class);
		if ($addonAttribute === null) {
			return;
		}

		$addon = $addonAttribute->getInstance();

		$nature->setPrefixSiCrumbGroups(SiCrumbGroupFactory::parseCrumbGroups($addon->prefixes));
		$nature->setSuffixSiCrumbGroups(SiCrumbGroupFactory::parseCrumbGroups($addon->suffixes));
	}

	private function checkCascadeAllAndOrphanRemoval(RelationEiProp $eiProp, ?PropertyAttribute $attribute): void {
		$relationEntityProperty = $eiProp->getRelationEntityProperty();

		$relation = $relationEntityProperty->getRelation();

		if ($relation->getCascadeType() === CascadeType::ALL && $relation->isOrphanRemoval()) {
			return;
		}

		$message = get_class($eiProp) . ' requires CascadeType::ALL and orphanRemoval=true for'
				. $relationEntityProperty->toPropertyString();

		if ($attribute !== null) {
			throw $this->eiTypeSetup->createPropertyAttributeError($attribute, null, $message);
		}

		throw new InvalidEiConfigurationException($message);
	}
}