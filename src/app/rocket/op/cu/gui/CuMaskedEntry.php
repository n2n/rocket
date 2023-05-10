<?php

namespace rocket\op\cu\gui;

use rocket\si\meta\SiMaskDeclaration;
use rocket\si\content\SiValueBoundary;
use rocket\si\meta\SiStyle;
use rocket\op\ei\manage\gui\control\GuiControl;
use n2n\util\HashUtils;
use rocket\si\meta\SiMask;
use rocket\si\meta\SiMaskQualifier;
use rocket\si\meta\SiMaskIdentifier;
use rocket\si\control\SiIconType;
use rocket\si\content\SiEntryIdentifier;
use rocket\si\content\SiEntry;
use n2n\util\uri\Url;
use rocket\si\content\SiGui;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\si\meta\SiDeclaration;
use rocket\si\input\SiEntryInput;
use n2n\core\container\N2nContext;
use rocket\si\input\SiInputError;
use rocket\op\cu\util\Cuu;
use rocket\si\input\CorruptedSiInputDataException;

class CuMaskedEntry {


	private SiMaskDeclaration $siMaskDeclaration;
	private CuStructure $eifSiStructure;
	private CuGuiEntry $cuGuiEntry;


	/**
	 * @var array<GuiControl>
	 */
	private array $guiControls = [];

	function __construct(private string $maskId, string $typeId, string $name,
			$iconClass = SiIconType::ICON_ROCKET) {

		$this->siMaskDeclaration = new SiMaskDeclaration(
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

	function getSiMaskDeclaration(): SiMaskDeclaration {
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