<?php

namespace rocket\cu\gui;

use rocket\si\meta\SiMaskDeclaration;
use rocket\si\content\SiValueBoundary;
use rocket\si\meta\SiStyle;
use rocket\ei\manage\gui\control\GuiControl;
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
use rocket\ei\manage\api\ZoneApiControlCallId;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\si\meta\SiDeclaration;

class CuMaskedEntry {


	private SiMaskDeclaration $siMaskDeclaration;
	private CuStructure $eifSiStructure;
	private CuGuiEntry $cuGuiEntry;


	/**
	 * @var array<GuiControl>
	 */
	private array $guiControls = [];

	function __construct(string $maskId, string $typeId, string $name,
			$iconClass = SiIconType::ICON_ROCKET) {

		$this->siMaskDeclaration = new SiMaskDeclaration(
				new SiMask(new SiMaskQualifier(new SiMaskIdentifier($maskId, $typeId), $name, $iconClass)),
				[]);
		$this->cuGuiEntry = new CuGuiEntry();

		$this->eifSiStructure = new CuStructure($this->cuGuiEntry, $this->siMaskDeclaration, null);
	}

	function structure(): CuStructure {
		return $this->eifSiStructure;
	}






}