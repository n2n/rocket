<?php

namespace rocket\impl\ei\component\prop\adapter\trait;

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\ui\gui\field\BackableGuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ui\gui\field\GuiField;

trait InGuiPropTrait {
	use OutGuiPropTrait, EditConfigTrait;

	function buildGuiProp(Eiu $eiu): ?EiGuiProp {
		$displayConfig = $this->getDisplayConfig();
		return $eiu->factory()
				->newGuiProp(fn (Eiu $eiu, bool $readOnly) => $this->buildGuiField($eiu, $readOnly))
				->setDefaultDisplayed($displayConfig->isViewModeDefaultDisplayed($eiu->guiDefinition()->getViewMode()))
				->setSiStructureType($displayConfig->getSiStructureType())
				->toEiGuiProp();
	}

	protected function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($readOnly || $this->isReadOnly() ||  $eiu->guiDefinition()->isReadOnly()
				|| ($eiu->entry()->isNew() && $this->isConstant())) {
			$guiField = $this->buildOutGuiField($eiu);
		} else {
			$guiField = $this->buildInGuiField($eiu);
		}

		if ($guiField->getModel() === null) {
			$guiField->setModel($eiu->field()->asGuiFieldModel());
		}

		return $guiField;
	}

//	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
//		return $this->createOutGuiField($eiu);
//	}

	protected function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		throw new UnsupportedOperationException ( get_class ($this) . ' must implement either'
				. ' buildInGuiField(Eiu $eiu): ?BackableGuiField or'
				. ' buildGuiProp(Eiu $eiu): ?EiGuiProp.' );
	}
}