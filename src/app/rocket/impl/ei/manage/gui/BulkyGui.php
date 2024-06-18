<?php

namespace rocket\impl\ei\manage\gui;

use rocket\ui\gui\Gui;
use rocket\ui\si\content\SiGui;
use rocket\ui\si\input\SiInput;
use rocket\ui\si\input\CorruptedSiInputDataException;
use rocket\ui\si\content\SiValueBoundary;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use rocket\ui\si\content\impl\basic\BulkyEntrySiGui;
use rocket\ui\si\control\SiCallResponse;
use n2n\util\ex\IllegalStateException;
use rocket\ui\gui\GuiValueBoundary;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\content\SiZoneCall;
use rocket\op\ei\manage\api\SiCallResult;
use rocket\ui\si\input\SiInputError;
use n2n\core\container\N2nContext;use rocket\ui\gui\control\GuiControlMap;

class BulkyGui implements Gui {

	private SiGui $siGui;
	/**
	 * @var SiValueBoundary[]
	 */
	private array $inputSiValueBoundaries = [];
	private array $inputEiEntries = [];

	function __construct(private ?SiFrame $siFrame, private readonly SiDeclaration $siDeclaration,
			private readonly GuiValueBoundary $guiValueBoundary, private readonly ?GuiControlMap $generalGuiControlMap,
			private readonly bool $entrySiControlsIncluded) {

		$this->siGui = new BulkyEntrySiGui($this->siFrame, $this->siDeclaration,
				$this->guiValueBoundary->getSiValueBoundary());
		$this->siGui->setEntryControlsIncluded($this->entrySiControlsIncluded);
		$siControls = $this->generalGuiControlMap?->getGuiControls() ?? [];
		$this->siGui->setControls($siControls);
	}

	function getInputSiValueBoundaries(): array {
		return $this->inputSiValueBoundaries;
	}

	function handleSiGuiOperation(?SiInput $siInput, SiZoneCall $siGuiCall): SiCallResult {
		$siInput->getEntryInputs();
		$this->guiValueBoundary->handleSiEntryInput();

	}

	function handleSiInput(SiInput $siInput, N2nContext $n2nContext): ?SiInputError {
		$entryInputs = $siInput->getEntryInputs();
		if (count($entryInputs) > 1) {
			throw new CorruptedSiInputDataException('BulkyEiGui can not handle multiple SiEntryInputs.');
		}

		$this->inputSiValueBoundaries = [];
		$this->inputEiEntries = [];

		foreach ($entryInputs as $siEntryInput) {
			if (!$this->guiValueBoundary->getSiValueBoundary()->handleEntryInput($siEntryInput)
					|| !$this->guiValueBoundary->save($n2nContext)) {
				return new SiInputError([$this->guiValueBoundary->getSiValueBoundary()]);
			}

			if ($this->guiValueBoundary->getSelectedEiEntry()->validate()) {
				$this->inputSiValueBoundaries = [$this->guiValueBoundary->getSiValueBoundary()];
				$this->inputEiEntries = [$this->guiValueBoundary->getSelectedEiEntry()];
				return null;
			}

			return new SiInputError([$this->guiValueBoundary->getSiValueBoundary($n2nLocale)]);
		}

		throw new IllegalStateException();
	}

	/**
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiCall(ZoneApiControlCallId $zoneControlCallId): SiCallResponse {
		return $this->zoneGuiControlsMap->handleSiCall($zoneControlCallId, $this->eiFrame, $this->eiGuiDeclaration,
				$this->inputEiEntries);
	}


	function getSiGui(): SiGui {
		return $this->siGui;
	}

}