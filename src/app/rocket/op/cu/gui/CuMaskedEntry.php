<?php

namespace rocket\op\cu\gui;

use rocket\ui\si\meta\SiMask;
use rocket\ui\gui\control\GuiControl;
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\ui\si\control\SiIconType;
use rocket\ui\si\content\SiEntry;
use rocket\ui\si\input\SiEntryInput;
use n2n\core\container\N2nContext;
use rocket\ui\si\input\CorruptedSiInputDataException;

class CuMaskedEntry {


	private SiMask $siMaskDeclaration;
	private CuStructure $eifSiStructure;
	private CuGuiEntry $cuGuiEntry;


	/**
	 * @var array<GuiControl>
	 */
	private array $guiControls = [];

	function __construct(private string $maskId, string $typeId, string $name,
			$iconClass = SiIconType::ICON_ROCKET) {

		$this->siMaskDeclaration = new SiMask(
				new SiMask(new SiMaskQualifier(new SiMaskIdentifier($maskId, $typeId), $name, $iconClass)),
				[]);
		$this->cuGuiEntry = new CuGuiEntry();

		$this->eifSiStructure = new CuStructure($this->cuGuiEntry, $this->siMaskDeclaration, null);
	}

	function getMaskId(): string {
		return $this->maskId;
	}

	function structure(): CuStructure {
		return $this->eifSiStructure;
	}

	function getSiMask(): SiMask {
		return $this->siMaskDeclaration;
	}

	function getCuEntry(): CuGuiEntry {
		return $this->cuGuiEntry;
	}

	function getSiEntry(): SiEntry {
		return $this->cuGuiEntry->getSiEntry();
	}

	/**
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput, N2nContext $n2nContext): bool {
		return $this->cuGuiEntry->handleSiEntryInput($siEntryInput, $n2nContext);
	}
}