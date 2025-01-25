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

class EmbeddedEntriesInGuiField extends InGuiFieldAdapter {

	private EmbeddedEntriesInSiField $siField;

	private GuiEmbeddedEntriesCollection $collection;

	function __construct(private SiFrame $siFrame, private GuiEmbeddedEntryFactory $guiEmbeddedEntriesModel) {
		$this->collection = new GuiEmbeddedEntriesCollection($guiEmbeddedEntriesModel);
		$this->siField = new EmbeddedEntriesInSiField($this->siFrame, $this->collection);
		parent::__construct($this->siField);
	}

	function getSiField(): EmbeddedEntriesInSiField {
		return $this->siField;
	}

	/**
	 * @param GuiEmbeddedEntry[] $value
	 * @return EmbeddedEntriesInGuiField
	 */
	function setValue(array $value): static {
		ArgUtils::valArray($value, SiEmbeddedEntry::class);
		$this->siField->setValue(array_map(
				fn (GuiEmbeddedEntry $guiEmbeddedEntry) => $this->collection->add($guiEmbeddedEntry),
				$value));
		return $this;
	}

	protected function createInputMappers(N2nContext $n2nContext): array {
		$mappers = [];

		if (0 < ($min = $this->siField->getMin())) {
			$mappers[] = Validators::minElements($min);
		}

		if (null !== ($max = $this->siField->getMax())) {
			$mappers[] = Validators::maxElements($max);
		}

		$mappers[] = Mappers::valueClosure(function (array $siEmbeddedEntries) {
			return array_map(fn (SiEmbeddedEntry $e) => $this->collection->siToGui($e), $siEmbeddedEntries);
		});

		return $mappers;
	}
}