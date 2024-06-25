<?php

namespace rocket\ui\si\api\request;

use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataMap;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\input\SiEntryInput;

class SiValueBoundaryInput {

	/**
	 * Ent
	 * @var string[]|null
	 */
	private ?array $maskIds = null;

	function __construct(private readonly string $selectedMaskId, private readonly SiEntryInput $entryInput) {

	}

	function getSelectedMaskId(): string {
		return $this->selectedMaskId;
	}

	function setMaskIds(?array $maskIds): static {
		ArgUtils::valArray($maskIds, 'string');
		$this->maskIds = $maskIds;
		return $this;
	}

	function getMaskIds(): ?array {
		return $this->maskIds;
	}

	function getEntryInput(): SiEntryInput {
		return $this->entryInput;
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiValueBoundaryInput {
		$dataMap = new DataMap($data);

		try {
			$siValueBoundary = new SiValueBoundaryInput($dataMap->reqString('selectedMaskId'),
					SiEntryInput::parse($dataMap->reqArray('entryInput')));
			$siValueBoundary->setMaskIds($dataMap->reqArray('maskIds', 'string', true));
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException('SiValueBoundaryInput', previous: $e);
		}
	}

	function jsonSerialize(): mixed {
		return [
			'selectedMaskId' => $this->selectedMaskId,
			'maskIds' => $this->maskIds,
			'entryInput' => $this->entryInput
		];
	}

}