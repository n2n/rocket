<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use n2n\core\container\N2nContext;
use n2n\bind\mapper\impl\Mappers;
use rocket\ui\si\content\impl\SiFields;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\content\impl\relation\EmbeddedEntryPanelsOutSiField;
use rocket\ui\gui\field\impl\OutGuiFieldAdapter;

class EmbeddedEntryPanelsOutGuiField extends OutGuiFieldAdapter {

	/**
	 * @var GuiPanel[] $guiPanels;
	 */
	private array $guiPanels;
	private EmbeddedEntryPanelsOutSiField $siField;

	function __construct(SiFrame $siFrame) {
		$this->siField = SiFields::embeddedEntryPanelsOut($siFrame);
		parent::__construct($this->siField);
	}

	function setPanels(array $panels): static {
		$this->guiPanels = $panels;
		$this->siField->setPanels(array_map(fn (GuiPanel $p) => $p->getSiPanel(), $panels));
		return $this;
	}

	function putGuiPanel(GuiPanel $guiPanel): static {
		$this->guiPanels[$guiPanel->getSiPanel()->getName()] = $guiPanel;
		$this->siField->putPanel($guiPanel->getSiPanel());
		return $this;
	}

}