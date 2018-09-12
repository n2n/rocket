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
namespace rocket\impl\ei\component\prop\adapter;

use rocket\ei\manage\entry\impl\Readable;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\entry\impl\SimpleEiField;
use n2n\l10n\N2nLocale;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\manage\EiObject;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\gui\DisplayDefinition;
use n2n\reflection\ArgUtils;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\GuiPropFork;

abstract class PropertyDisplayableEiPropAdapter extends ObjectPropertyEiPropAdapter implements StatelessDisplayable, 
		FieldEiProp, GuiEiProp, GuiProp, Readable {
	private $displaySettings;

	public function getDisplaySettings(): DisplaySettings {
		if ($this->displaySettings === null) {
			$this->displaySettings = new DisplaySettings(ViewMode::all());
		}

		return $this->displaySettings;
	}
	
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		$viewMode = $eiu->gui()->getViewMode();
		if (!$this->getDisplaySettings()->isViewModeCompatible($viewMode)) {
			return null;
		}
		
		$groupType = $this->getDisplayItemType($eiu);
		ArgUtils::valEnumReturn($groupType, DisplayItem::getTypes(), $this, 'getGroupType');
		
		return new DisplayDefinition($this->getDisplayLabel(), $groupType,
				$this->getDisplaySettings()->isViewModeDefaultDisplayed($viewMode));
	}
	
	protected function getDisplayItemType(Eiu $eiu) {
		return DisplayItem::TYPE_ITEM;
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerDisplaySettings($this->getDisplaySettings());
		return $eiPropConfigurator;
	}
	
	public function isEiField(): bool {
		return true;
	}
	
	public function buildEiField(Eiu $eiu) {
		return new SimpleEiField($eiu->entry()->getEiObject(), 
				$this->getObjectPropertyAccessProxy()->getConstraint()->getLenientCopy(), 
				$this);
	}
	
	public function buildEiFieldFork(EiObject $eiObject, EiField $eiField = null) {
		return null;
	}
	
// 	public function isEiEntryFilterable(): bool {
// 		return false;
// 	}
	
// 	public function createSecurityFilterProp(N2nContext $n2nContext): SecurityFilterProp {
// 		throw new IllegalStateException('EiProp cannot provide an SecurityFilterProp: ' . $this);
// 	}
	
// 	public function getTypeConstraint() {
// 		$typeConstraint = $this->getPropertyAccessProxy()->getConstraint();
// 		if ($typeConstraint === null) return null;
// 		return $typeConstraint->getLenientCopy();
// 	}
	
	public function read(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			return $eiObject->getDraft()->getDraftValueMap()->getValue($this);
		}

		$objectPropertyAccessProxy = $this->getObjectPropertyAccessProxy();
		if ($objectPropertyAccessProxy === null) {
			return null;
		}

		return $objectPropertyAccessProxy->getValue($eiObject->getEiEntityObj()->getEntityObj());
	}
	
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return $this;
	}
	
	public function getDisplayLabel(): string {
		return $this->getLabelLstr();
	}
	
	public function buildGuiField(Eiu $eiu): ?GuiField {
		return new StatelessDisplayElement($this, $eiu);
	}
	
	public function getUiOutputLabel(Eiu $eiu) {
		return $this->getLabelLstr();
	}
	
	public function getOutputHtmlContainerAttrs(Eiu $eiu) {
		$eiTypeExtension = $this->eiMask->isExtension() ? $this->eiMask->getExtension() : null;
		return array('class' => 'rocket-ei-spec-' . $this->eiMask->getEiType()->getId()
						. ($eiTypeExtension !== null ? ' rocket-ei-mask-' . $eiTypeExtension->getId() : '') 
						. ' rocket-ei-field-' . $this->getId(), 
				'title' => $this->displaySettings->getHelpText());
	}
	
	public function isStringRepresentable(): bool {
		return false;
	}
	
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): ?string {
		throw new UnsupportedOperationException('EiProp ' . $this->id . ' not summarizable.');
	}
}
