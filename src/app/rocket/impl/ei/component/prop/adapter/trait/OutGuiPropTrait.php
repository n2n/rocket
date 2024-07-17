<?php

namespace rocket\impl\ei\component\prop\adapter\trait;

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\ui\gui\field\BackableGuiField;
use n2n\util\ex\UnsupportedOperationException;

trait OutGuiPropTrait {
	use DisplayConfigTrait;

	function buildGuiProp(Eiu $eiu): ?EiGuiProp {
		$displayConfig = $this->getDisplayConfig();
		return $eiu->factory()
				->newGuiProp(fn (Eiu $eiu) => $this->buildOutGuiField($eiu))
				->setDefaultDisplayed($displayConfig->isViewModeDefaultDisplayed($eiu->guiDefinition()->getViewMode()))
				->setSiStructureType($displayConfig->getSiStructureType())
				->toEiGuiProp();
	}

//	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
//		return $this->createOutGuiField($eiu);
//	}

	protected function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
		throw new UnsupportedOperationException ( get_class ($this) . ' must implement either'
				. ' buildOutGuiField(Eiu $eiu): ?BackableGuiField or'
				. ' buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField.' );
	}
}