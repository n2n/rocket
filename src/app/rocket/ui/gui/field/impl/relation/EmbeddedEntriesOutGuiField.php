<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use n2n\core\container\N2nContext;
use rocket\ui\si\content\impl\relation\EmbeddedEntriesInSiField;
use rocket\ui\si\meta\SiFrame;
use n2n\bind\mapper\impl\Mappers;
use n2n\util\type\ArgUtils;
use rocket\ui\si\content\impl\relation\SiEmbeddedEntry;
use n2n\validation\validator\impl\Validators;
use rocket\ui\si\content\SiField;
use rocket\ui\gui\field\impl\OutGuiFieldAdapter;
use rocket\ui\si\content\impl\relation\EmbeddedEntriesOutSiField;

class EmbeddedEntriesOutGuiField extends OutGuiFieldAdapter {

	private EmbeddedEntriesOutSiField $siField;

	function __construct(private SiFrame $siFrame) {
		$this->siField = new EmbeddedEntriesOutSiField($this->siFrame);
		parent::__construct($this->siField);
	}

	function getSiField(): EmbeddedEntriesOutSiField {
		return $this->siField;
	}

	/**
	 * @param GuiEmbeddedEntry[] $value
	 * @return EmbeddedEntriesOutGuiField
	 */
	function setValue(array $value): static {
		ArgUtils::valArray($value, GuiEmbeddedEntry::class);
		$this->siField->setValues(array_map(
				fn (GuiEmbeddedEntry $guiEmbeddedEntry) => $guiEmbeddedEntry->getSiEmbeddedEntry(),
				$value));
		return $this;
	}
}