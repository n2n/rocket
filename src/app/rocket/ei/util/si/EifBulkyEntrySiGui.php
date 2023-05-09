<?php

namespace rocket\ei\util\si;

use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\si\meta\SiDeclaration;
use rocket\si\meta\SiMaskDeclaration;
use rocket\si\content\SiGui;
use rocket\si\meta\SiStyle;
use rocket\si\meta\SiMask;
use rocket\si\meta\SiMaskQualifier;
use rocket\si\content\SiValueBoundary;
use rocket\si\content\SiEntryIdentifier;
use n2n\util\HashUtils;
use rocket\si\meta\SiMaskIdentifier;
use rocket\si\content\SiEntry;
use rocket\ei\util\EiuAnalyst;
use rocket\si\control\SiIconType;
use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\manage\api\ZoneApiControlCallId;
use n2n\util\uri\Url;
use n2n\util\ex\IllegalStateException;
use rocket\cu\gui\CuStructure;

class EifBulkyEntrySiGui implements EifSiGui {

	private SiMaskDeclaration $maskDeclaration;
	private CuStructure $eifSiStructure;
	private SiValueBoundary $entry;

	private SiStyle $style;

	/**
	 * @var array<GuiControl>
	 */
	private array $guiControls = [];

	function __construct(private EiuAnalyst $eiuAnalyst, bool $readOnly = false) {

		$maskId = 'mask-' . HashUtils::base36Uniqid(false);
		$typeId = 'type-' . HashUtils::base36Uniqid(false);

		$this->style = new SiStyle(true, $readOnly);
		$this->maskDeclaration = new SiMaskDeclaration(
				new SiMask(new SiMaskQualifier(new SiMaskIdentifier($maskId, $typeId), 'Some Name', SiIconType::ICON_ROCKET)),
				[]);
		$this->entry = new SiValueBoundary(new SiEntryIdentifier($typeId, null, null), $this->style);
		$this->entry->putEntry($maskId, new SiEntry($maskId, null));
		$this->entry->setSelectedMaskId($maskId);

		$this->eifSiStructure = new CuStructure($this->entry->getSelectedEntry(), $this->maskDeclaration, null);
	}

	function structure(): CuStructure {
		return $this->eifSiStructure;
	}

	function addControl(GuiControl $control): static {
		$this->guiControls[$control->getId()] = $control;
		return $this;
	}

	function toSiGui(Url $zoneApiUrl = null): SiGui {
		IllegalStateException::assertTrue(empty($this->guiControls) || $zoneApiUrl !== null,
				'Zone api url not available, but controls of this gui requires one.');

		$controls = array_map(
				fn ($c) => $c->toSiControl($zoneApiUrl, ZoneApiControlCallId::create($c)),
				$this->guiControls);

		$siGui = new BulkyEntrySiGui(null, new SiDeclaration($this->style, [$this->maskDeclaration]), $this->entry);
		$siGui->setControls($controls);

		return $siGui;
	}
}