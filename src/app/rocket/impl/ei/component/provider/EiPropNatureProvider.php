<?php

namespace rocket\impl\ei\component\provider;

use rocket\op\spec\setup\EiTypeSetup;
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
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\reflection\attribute\PropertyAttribute;
use n2n\persistence\orm\CascadeType;
use n2n\util\ex\err\ConfigurationError;
use rocket\op\ei\component\InvalidEiConfigurationException;
use rocket\op\spec\setup\EiPresetProp;
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
use rocket\attribute\impl\EiPropOnlineStatus;
use rocket\impl\ei\component\prop\bool\OnlineEiPropNature;
use rocket\impl\ei\component\prop\string\PathPartEiPropNature;
use rocket\attribute\impl\EiPropPathPart;
use rocket\attribute\impl\EiPropOneToManyEmbedded;
use rocket\attribute\impl\EiPropOneToOneEmbedded;
use n2n\util\uri\Url;
use rocket\impl\ei\component\prop\string\UrlEiPropNature;
use rocket\impl\ei\component\prop\relation\EmbeddedOneToManyEiPropNature;
use rocket\impl\ei\component\prop\relation\EmbeddedOneToOneEiPropNature;
use rocket\impl\ei\component\prop\numeric\OrderEiPropNature;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\op\ei\component\prop\EiPropNature;
use rocket\impl\ei\component\prop\adapter\config\LabelConfig;
use rocket\attribute\EiLabel;
use rocket\impl\ei\component\prop\ci\model\ContentItem;
use rocket\impl\ei\component\prop\ci\ContentItemsEiPropNature;
use rocket\attribute\impl\EiPropOrder;
use rocket\attribute\impl\EiPropString;
use n2n\util\type\CastUtils;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\util\EnumUtils;
use rocket\op\spec\setup\EiTypeClassSetup;
use rocket\impl\ei\component\prop\embedded\EmbeddedEiPropNature;
use n2n\persistence\orm\property\EntityProperty;
use rocket\attribute\impl\EiPropCke;
use rocket\impl\ei\component\prop\string\cke\ui\CkeConfig;
use rocket\impl\ei\component\prop\string\cke\ui\Cke;
use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use n2n\util\magic\MagicContext;
use n2n\util\magic\MagicLookupFailedException;

class EiPropNatureProvider {

	function __construct(private EiTypeSetup $eiTypeSetup, private EiTypeClassSetup $eiTypeClassSetup,
			private MagicContext $magicContext) {
	}

	private function eiPropPath(string $propertyName) {
		return $this->eiTypeClassSetup->createEiPropPath($propertyName);
	}

	function provideAnnotateds(): void {
		foreach ($this->eiTypeClassSetup->getAttributeSet()->getPropertyAttributesByName(EiPropBool::class)
				 as $eiPropBoolAttribute) {
			$eiPropBool = $eiPropBoolAttribute->getInstance();
			$propertyName = $eiPropBoolAttribute->getProperty()->getName();

			$propertyAccessProxy = $this->getPropertyAccessProxy($eiPropBoolAttribute, $eiPropBool->readOnly);
			$nature = new BooleanEiPropNature($propertyAccessProxy);
			$nature->setEntityProperty($this->eiTypeClassSetup->getEntityProperty($propertyName));
			$this->configureLabel($propertyAccessProxy, $nature->getLabelConfig(),
					$this->eiTypeClassSetup->getPropertyLabel($propertyName));
			$this->configureEditiable($eiPropBool->constant, $eiPropBool->readOnly, $eiPropBool->mandatory,
					$nature->getPropertyAccessProxy(), $nature->getEntityProperty(), $nature->getEditConfig());

			$nature->setOnAssociatedDefPropPaths($eiPropBool->onAssociatedDefPropPaths);
			$nature->setOffAssociatedDefPropPaths($eiPropBool->offAssociatedDefPropPaths);

			$this->eiTypeClassSetup->addEiPropNature($this->eiPropPath($propertyName), $nature);
		}

		foreach ($this->eiTypeClassSetup->getAttributeSet()->getPropertyAttributesByName(EiPropEnum::class)
				 as $eiPropEnumAttribute) {
			$eiPropEnum = $eiPropEnumAttribute->getInstance();
			$propertyName = $eiPropEnumAttribute->getProperty()->getName();
			$propertyAccessProxy = $this->getPropertyAccessProxy($eiPropEnumAttribute, $eiPropEnum->readOnly);

			$enum = null;
			if ($enumTypeName = EnumUtils::extractEnumTypeName($eiPropEnumAttribute->getProperty()->getType())) {
				$enum = IllegalStateException::try(fn () => new \ReflectionEnum($enumTypeName));
			}

			$nature = new EnumEiPropNature($propertyAccessProxy, $enum);
			$this->configureLabel($propertyAccessProxy, $nature->getLabelConfig(),
					$this->eiTypeClassSetup->getPropertyLabel($propertyName));
			$nature->setEntityProperty($this->eiTypeClassSetup->getEntityProperty($propertyName));
			try {
				$nature->setOptions($eiPropEnum->options);
			} catch (\InvalidArgumentException $e) {
				throw $this->eiTypeSetup->createPropertyAttributeError($eiPropEnumAttribute, $e);
			}
			$nature->setEmptyLabel($eiPropEnum->emptyLabel);
			$nature->setAssociatedDefPropPathMap($eiPropEnum->associatedDefPropPathMap);

			$this->configureEditiable($eiPropEnum->constant, $eiPropEnum->readOnly, $eiPropEnum->mandatory,
					$nature->getPropertyAccessProxy(), $nature->getEntityProperty(), $nature->getEditConfig());

			$this->eiTypeClassSetup->addEiPropNature($this->eiPropPath($propertyName), $nature);
		}

		foreach ($this->eiTypeClassSetup->getAttributeSet()->getPropertyAttributesByName(EiPropDecimal::class)
				 as $attribute) {
			$eiPropDecimal = $attribute->getInstance();
			$propertyName = $attribute->getProperty()->getName();
			$propertyAccessProxy = $this->getPropertyAccessProxy($attribute, $eiPropDecimal->readOnly);

			$nature = new DecimalEiPropNature($propertyAccessProxy);
			$nature->setEntityProperty($this->eiTypeClassSetup->getEntityProperty($propertyName));
			$this->configureLabel($propertyAccessProxy, $nature->getLabelConfig(),
					$this->eiTypeClassSetup->getPropertyLabel($propertyName));
			$nature->setDecimalPlaces($eiPropDecimal->decimalPlaces);

			$this->configureEditiable($eiPropDecimal->constant, $eiPropDecimal->readOnly, $eiPropDecimal->mandatory,
					$nature->getPropertyAccessProxy(), $nature->getEntityProperty(), $nature->getEditConfig());
			$this->configureAddons($propertyAccessProxy, $nature);

			$this->eiTypeClassSetup->addEiPropNature($this->eiPropPath($propertyName), $nature);
		}

		foreach ($this->eiTypeClassSetup->getAttributeSet()->getPropertyAttributesByName(EiPropOnlineStatus::class)
				 as $eiPropOnlineAttribute) {
			$propertyName = $eiPropOnlineAttribute->getProperty()->getName();
			$propertyAccessProxy = $this->getPropertyAccessProxy($eiPropOnlineAttribute, false);

			$nature = new OnlineEiPropNature($propertyAccessProxy);
			$nature->setEntityProperty($this->eiTypeClassSetup->getEntityProperty($propertyName));
			$this->configureLabel($propertyAccessProxy, $nature->getLabelConfig(),
					$this->eiTypeClassSetup->getPropertyLabel($propertyName));

			$this->eiTypeClassSetup->addEiPropNature($this->eiPropPath($propertyName), $nature);
		}

		foreach ($this->eiTypeClassSetup->getAttributeSet()->getPropertyAttributesByName(EiPropOrder::class)
				 as $eiPropOrderAttribute) {
			$propertyName = $eiPropOrderAttribute->getProperty()->getName();
			$propertyAccessProxy = $this->getPropertyAccessProxy($eiPropOrderAttribute, false);

			$nature = new OrderEiPropNature($propertyAccessProxy);
			$nature->setEntityProperty($this->eiTypeClassSetup->getEntityProperty($propertyName));
			$this->configureLabel($propertyAccessProxy, $nature->getLabelConfig(),
					$this->eiTypeClassSetup->getPropertyLabel($propertyName));

			$this->eiTypeClassSetup->addEiPropNature($this->eiPropPath($propertyName), $nature);
		}

		foreach ($this->eiTypeClassSetup->getAttributeSet()->getPropertyAttributesByName(EiPropPathPart::class)
				 as $attribute) {
			$eiPropPathPart = $attribute->getInstance();
			$propertyName = $attribute->getProperty()->getName();
			$propertyAccessProxy = $this->getPropertyAccessProxy($attribute, $eiPropPathPart->readOnly);

			$nature = new PathPartEiPropNature($propertyAccessProxy,
					$this->eiTypeClassSetup->getEntityProperty($propertyName, true));
			$this->configureLabel($propertyAccessProxy, $nature->getLabelConfig(),
					$this->eiTypeClassSetup->getPropertyLabel($propertyName));
			$nature->setBaseEiPropPath($eiPropPathPart->baseEiPropPath);
			$nature->setUniquePerEiPropPath($eiPropPathPart->uniquePerEiPropPath);

			$this->configureEditiable($eiPropPathPart->constant, $eiPropPathPart->readOnly, $eiPropPathPart->mandatory,
					$propertyAccessProxy, $nature->getEntityProperty(), $nature->getEditConfig());
			$this->configureAddons($propertyAccessProxy, $nature);

			$this->eiTypeClassSetup->addEiPropNature($this->eiPropPath($propertyName), $nature);
		}

		foreach ($this->eiTypeClassSetup->getAttributeSet()->getPropertyAttributesByName(EiPropString::class)
				 as $attribute) {
			$eiPropString = $attribute->getInstance();
			$propertyName = $attribute->getProperty()->getName();
			$propertyAccessProxy = $this->getPropertyAccessProxy($attribute, $eiPropString->readOnly);

			$nature = new StringEiPropNature($propertyAccessProxy);
			$nature->setEntityProperty($this->eiTypeClassSetup->getEntityProperty($propertyName, false));
			$this->configureLabel($propertyAccessProxy, $nature->getLabelConfig(),
					$this->eiTypeClassSetup->getPropertyLabel($propertyName));
			$nature->setMultiline($eiPropString->multiline);

			$this->configureEditiable($eiPropString->constant, $eiPropString->readOnly, $eiPropString->mandatory,
					$propertyAccessProxy, $nature->getEntityProperty(), $nature->getEditConfig());
			$this->configureAddons($propertyAccessProxy, $nature);

			$this->eiTypeClassSetup->addEiPropNature($this->eiPropPath($propertyName), $nature);
		}

		foreach ($this->eiTypeClassSetup->getAttributeSet()->getPropertyAttributesByName(EiPropCke::class)
				 as $attribute) {
			$eiPropCke = $attribute->getInstance();
			assert($eiPropCke instanceof EiPropCke);
			$propertyName = $attribute->getProperty()->getName();
			$propertyAccessProxy = $this->getPropertyAccessProxy($attribute, $eiPropCke->readOnly);

			$nature = new CkeEiPropNature($propertyAccessProxy);
			$nature->setEntityProperty($this->eiTypeClassSetup->getEntityProperty($propertyName, false));
			$this->configureLabel($propertyAccessProxy, $nature->getLabelConfig(),
					$this->eiTypeClassSetup->getPropertyLabel($propertyName));
			$this->configureEditiable($eiPropCke->constant, $eiPropCke->readOnly, $eiPropCke->mandatory,
					$propertyAccessProxy, $nature->getEntityProperty(), $nature->getEditConfig());
			$this->configureAddons($propertyAccessProxy, $nature);
			$nature->setCkeConfig(new CkeConfig($eiPropCke->mode, $eiPropCke->tableEnabled, $eiPropCke->bbcodeEnabled,
					$this->lookup($eiPropCke->cssConfig, CkeCssConfig::class, $attribute),
					array_map(
							fn (string $n) => $this->lookup($n, CkeLinkProvider::class, $attribute),
							$eiPropCke->linkProviders)));

			$this->eiTypeClassSetup->addEiPropNature($this->eiPropPath($propertyName), $nature);
		}

	}

	function provideEmbedded(EiPresetProp $eiPresetProp): bool {
		$entityProperty = $eiPresetProp->getEntityProperty();

		if ($entityProperty === null || !$entityProperty->hasEmbeddedEntityPropertyCollection()) {
			return false;
		}

		$accessProxy = $eiPresetProp->getPropertyAccessProxy();
//		$nullAllowed = $accessProxy->isWritable()
//				? $accessProxy->getSetterConstraint()->allowsNull()
//				: $accessProxy->getGetterConstraint()->allowsNull();
//		$propertyName = $accessProxy->getPropertyName();

		$eiPropNature = new EmbeddedEiPropNature($entityProperty, $accessProxy);

		$this->configureLabel($eiPresetProp->getPropertyAccessProxy(), $eiPropNature->getLabelConfig(),
				$eiPresetProp->getLabel());

		$this->eiTypeClassSetup->addEiPropNature($eiPresetProp->getEiPropPath(), $eiPropNature);

		return true;
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
		$nullAllowed = $accessProxy->isWritable()
				? $accessProxy->getSetterConstraint()->allowsNull()
				: $accessProxy->getGetterConstraint()->allowsNull();
		$propertyName = $accessProxy->getPropertyName();

		switch ($entityProperty->getType()) {
			case RelationEntityProperty::TYPE_MANY_TO_MANY:
				$relationEiProp = new ManyToManySelectEiPropNature($entityProperty, $accessProxy);
				break;
			case RelationEntityProperty::TYPE_ONE_TO_MANY:
				assert($entityProperty instanceof ToManyEntityProperty);
				$targetClass = $entityProperty->getTargetEntityModel()->getClass();

				if ($targetClass->implementsInterface(Translatable::class)) {
					$relationEiProp = new TranslationEiPropNature($entityProperty, $accessProxy);
					$this->checkCascadeAllAndOrphanRemoval($relationEiProp, $this->eiTypeClassSetup->getAttributeSet()
							->getPropertyAttribute($eiPresetProp->getEiPropPath(), OneToMany::class));
				} else if ($targetClass->getName() === ContentItem::class) {
					$relationEiProp = new ContentItemsEiPropNature($entityProperty, $accessProxy);
					$this->checkCascadeAllAndOrphanRemoval($relationEiProp, $this->eiTypeClassSetup->getAttributeSet()
							->getPropertyAttribute($eiPresetProp->getEiPropPath(), OneToMany::class));
				} else {
					$relationEiProp = new OneToManySelectEiPropNature($entityProperty, $accessProxy);
					$oneToManyEmbeddedAttribute = $this->eiTypeClassSetup->getAttributeSet()
							->getPropertyAttribute($propertyName, EiPropOneToManyEmbedded::class);
					if ($oneToManyEmbeddedAttribute !== null) {
						$oneToManyEmbedded = $oneToManyEmbeddedAttribute->getInstance();
						$relationEiProp = new EmbeddedOneToManyEiPropNature($entityProperty, $accessProxy);
						$relationEiProp->getRelationModel()->setReduced($oneToManyEmbedded->reduced);
						$relationEiProp->getRelationModel()->setTargetOrderEiPropPath(
								$oneToManyEmbedded->targetOrderEiPropPath);
					}
				}
				break;
			case RelationEntityProperty::TYPE_MANY_TO_ONE:
				$relationEiProp = new ManyToOneSelectEiPropNature($entityProperty, $accessProxy);
				$relationEiProp->getRelationModel()->setMandatory(!$nullAllowed);
				break;
			case RelationEntityProperty::TYPE_ONE_TO_ONE:
				assert($entityProperty instanceof ToOneEntityProperty);

				$relationEiProp = new OneToOneSelectEiPropNature($entityProperty, $accessProxy);
				$relationEiProp->getRelationModel()->setMandatory(!$nullAllowed);

				$oneToOneEmbeddedAttribute = $this->eiTypeClassSetup->getAttributeSet()
						->getPropertyAttribute($propertyName, EiPropOneToOneEmbedded::class);
				if ($oneToOneEmbeddedAttribute !== null) {
					$oneToOneEmbedded = $oneToOneEmbeddedAttribute->getInstance();
					$relationEiProp = new EmbeddedOneToOneEiPropNature($entityProperty, $accessProxy);
					$relationEiProp->getRelationModel()->setReduced($oneToOneEmbedded->reduced);
				}
				break;
			default:
				throw new IllegalStateException();
		}
		$relationEiProp->getRelationModel()->setReadOnly(!$eiPresetProp->isEditable());

		$this->configureLabel($eiPresetProp->getPropertyAccessProxy(), $relationEiProp->getLabelConfig(),
				$eiPresetProp->getLabel());

		$this->eiTypeClassSetup->addEiPropNature($eiPresetProp->getEiPropPath(), $relationEiProp);

		return true;
	}

	function provideCommon(EiPresetProp $eiPresetProp): bool {
		$nullAllowed = false;
		foreach (NatureProviderUtils::compileTypeNames($eiPresetProp, $nullAllowed) as $typeName) {
			if ($this->provideAdvPropNaturesByType($eiPresetProp, $typeName, $nullAllowed)
					|| $this->providePrimitivePropNaturesByType($eiPresetProp, $typeName, $nullAllowed)) {
				return true;
			}
		}

		return false;
	}

	private function provideAdvPropNaturesByType(EiPresetProp $eiPresetProp, string $typeName, bool $nullAllowed): bool {
		$editConfig = null;

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
			case Url::class:
				$nature = new UrlEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			case 'bool':
				if ($eiPresetProp->getEiPropPath() !== 'online' || !$eiPresetProp->isEditable()) {
					return false;
				}

				$nature = new OnlineEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			case 'int':
				if ($eiPresetProp->getEiPropPath() !== 'orderIndex' || !$eiPresetProp->isEditable()) {
					return false;
				}

				$nature = new OrderEiPropNature($eiPresetProp->getPropertyAccessProxy());
				break;
			case 'string':
				if ($eiPresetProp->getEiPropPath() === 'pathPart') {
					$nature = new PathPartEiPropNature($eiPresetProp->getPropertyAccessProxy(), $eiPresetProp->getEntityProperty());
				} else if (StringUtils::endsWith('Html', $eiPresetProp->getEiPropPath())) {
					$nature = new CkeEiPropNature($eiPresetProp->getPropertyAccessProxy());
					$nature->setMaxlength(255);
				} else {
					return false;
				}
				break;
			default:
				if (EnumUtils::isEnumType($typeName)) {
					$nature = new EnumEiPropNature($eiPresetProp->getPropertyAccessProxy(),
							IllegalStateException::try(fn () => new \ReflectionEnum($typeName)));

				} else {
					return false;
				}
		}

		$nature->setEntityProperty($eiPresetProp->getEntityProperty());
		$this->configureLabel($eiPresetProp->getPropertyAccessProxy(), $nature->getLabelConfig(),
				$eiPresetProp->getLabel());
		$this->configureEditiable(null, !$eiPresetProp->isEditable(), !$nullAllowed, $eiPresetProp->getPropertyAccessProxy(),
				$eiPresetProp->getEntityProperty(), $nature->getEditConfig());

		$this->eiTypeClassSetup->addEiPropNature($eiPresetProp->getEiPropPath(), $nature);

		return true;
	}

	private function providePrimitivePropNaturesByType(EiPresetProp $eiPresetProp, string $typeName, bool $nullAllowed): bool {
		$editConfig = null;

		switch ($typeName) {
			case 'string':
				$nature = new StringEiPropNature($eiPresetProp->getPropertyAccessProxy());
				$nature->setMaxlength(255);
				$editConfig = $nature->getEditConfig();
				break;
			case 'int':
				$nature = new IntegerEiPropNature($eiPresetProp->getPropertyAccessProxy());
				$editConfig = $nature->getEditConfig();
				break;
			case 'bool':
				$nature = new BooleanEiPropNature($eiPresetProp->getPropertyAccessProxy());
				$editConfig = $nature->getEditConfig();
				break;
			case 'float':
				$nature = new DecimalEiPropNature($eiPresetProp->getPropertyAccessProxy());
				$editConfig = $nature->getEditConfig();
				break;
			default:
				return false;
		}

		$nature->setEntityProperty($eiPresetProp->getEntityProperty());
		$this->configureLabel($eiPresetProp->getPropertyAccessProxy(), $nature->getLabelConfig(),
				$eiPresetProp->getLabel());
		$this->configureEditiable(null, !$eiPresetProp->isEditable(), !$nullAllowed, $eiPresetProp->getPropertyAccessProxy(),
				$eiPresetProp->getEntityProperty(), $editConfig);
		$this->configureAddons($eiPresetProp->getPropertyAccessProxy(), $nature);

		$this->eiTypeClassSetup->addEiPropNature($eiPresetProp->getEiPropPath(), $nature);

		return true;
	}

	function provideFallback(EiPresetProp $eiPresetProp): bool {
		if ($eiPresetProp->isEditable()) {
			return false;
		}

		$nature = new StringDisplayEiPropNature($eiPresetProp->getPropertyAccessProxy());
		$this->configureLabel($eiPresetProp->getPropertyAccessProxy(), $nature->getLabelConfig(),
				$eiPresetProp->getLabel());

		$this->eiTypeClassSetup->addEiPropNature($eiPresetProp->getEiPropPath(), $nature);
		return true;
	}

	private function getPropertyAccessProxy(PropertyAttribute $attribute, ?bool $readOnly): PropertyAccessProxy {
		try {
			return $this->eiTypeClassSetup->getPropertyAccessProxy($attribute->getProperty()->getName(),
					$readOnly === false);
		} catch (\ReflectionException $e) {
			throw $this->eiTypeSetup->createPropertyAttributeError($attribute, $e);
		}
	}

	private function configureLabel(PropertyAccessProxy $propertyAccessProxy, LabelConfig $labelConfig,
			?string $eiPresetLabel): void {
		$label = $eiPresetLabel;
		$helpText = null;

		$property = $propertyAccessProxy->getProperty();
		if ($property !== null
				&& null !== ($labelAttribute = $this->eiTypeClassSetup->getAttributeSet()
						->getPropertyAttribute($property->getName(), EiLabel::class))) {
			$eiLabel = $labelAttribute->getInstance();
			$label = $eiLabel->label ?? $label;
			$helpText = $eiLabel->helpText;
		}

		$labelConfig->setLabel($label);
		$labelConfig->setHelpText($helpText);
	}

	private function configureEditiable(?bool $constant, ?bool $readOnly, ?bool $mandatory, AccessProxy $accessProxy,
			?EntityProperty $entityProperty, EditConfig $nature): void {

		$idDef = $this->eiTypeClassSetup->getIdDef();
		$isId = $entityProperty !== null && $entityProperty === $idDef->getEntityProperty();

		$nature->setConstant($isId || ($constant ?? false));
		$nature->setReadOnly(($isId && $idDef->isGenerated()) || ($readOnly ?? !$accessProxy->isWritable()));
		$nature->setMandatory(!($isId && $idDef->isGenerated())
				&& ($mandatory ?? !$accessProxy->getSetterConstraint()->allowsNull()));
	}

	private function configureAddons(PropertyAccessProxy $propertyAccessProxy, AddonEiPropNature $nature): void {
		$property = $propertyAccessProxy->getProperty();
		if ($property == null) {
			return;
		}

		$addonAttribute = $this->eiTypeClassSetup->getAttributeSet()->getPropertyAttribute($property->getName(), Addon::class);
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

	private function lookup(?string $id, ?string $requiredType, PropertyAttribute $attribute): mixed {
		if ($id === null) {
			return null;
		}

		try {
			$obj = $this->magicContext->lookup($id);
		} catch (MagicLookupFailedException $e) {
			throw $this->eiTypeSetup->createPropertyAttributeError($attribute, $e);
		}

		if ($requiredType === null || is_a($obj, $requiredType)) {
			return $obj;
		}

		throw $this->eiTypeSetup->createPropertyAttributeError($attribute, message: get_class($obj)
				. ' does not implement ' . $requiredType);
	}

}