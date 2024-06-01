<?php

namespace rocket\impl\ei\manage\gui;

use rocket\op\ei\manage\gui\EiGui;
use rocket\op\ei\manage\gui\EiGuiEntry;
use rocket\si\content\SiGui;
use n2n\util\uri\Url;
use rocket\op\ei\manage\gui\EiGuiValueBoundary;
use rocket\op\ei\manage\gui\EiGuiDeclaration;
use rocket\si\input\SiInput;
use rocket\si\input\SiInputError;
use n2n\core\container\N2nContext;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\SiValueBoundary;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\api\ZoneGuiControlsMap;
use rocket\op\ei\manage\gui\control\GuiControlMap;
use rocket\si\control\SiCallResponse;
use n2n\util\ex\IllegalStateException;

class BulkyEiGui implements EiGui {

	/**
	 * @var SiValueBoundary[]
	 */
	private array $inputSiValueBoundaries = [];
	private array $inputEiEntries = [];

	function __construct(private EiFrame $eiFrame, private readonly EiGuiDeclaration $eiGuiDeclaration,
			private readonly EiGuiValueBoundary $eiGuiValueBoundary, private readonly ?GuiControlMap $generalGuiControlMap,
			private readonly ZoneGuiControlsMap $zoneGuiControlsMap, private readonly bool $entrySiControlsIncluded) {
	}

	function getInputSiValueBoundaries(): array {
		return $this->inputSiValueBoundaries;
	}

	function handleSiInput(SiInput $siInput): ?SiInputError {
		$entryInputs = $siInput->getEntryInputs();
		if (count($entryInputs) > 1) {
			throw new CorruptedSiInputDataException('BulkyEiGui can not handle multiple SiEntryInputs.');
		}

		$n2nLocale = $this->eiFrame->getN2nContext()->getN2nLocale();

		$this->inputSiValueBoundaries = [];
		$this->inputEiEntries = [];

		foreach ($entryInputs as $siEntryInput) {
			$valid = $this->eiGuiValueBoundary->handleSiEntryInput($siEntryInput);
			if (!$valid) {
				return new SiInputError([$this->eiGuiValueBoundary->createSiValueBoundary($n2nLocale)]);
			}

			$this->eiGuiValueBoundary->save();

			if ($this->eiGuiValueBoundary->getSelectedEiEntry()->validate()) {
				$this->inputSiValueBoundaries = [$this->eiGuiValueBoundary->createSiValueBoundary($n2nLocale)];
				$this->inputEiEntries = [$this->eiGuiValueBoundary->getSelectedEiEntry()];
				return null;
			}

			return new SiInputError([$this->eiGuiValueBoundary->createSiValueBoundary($n2nLocale)]);
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


	function toSiGui(): SiGui {
		$n2nLocale = $this->eiFrame->getN2nContext()->getN2nLocale();

		$siGui = new BulkyEntrySiGui($this->eiFrame->createSiFrame(),
				$this->eiGuiDeclaration->createSiDeclaration($this->eiFrame->getN2nContext()->getN2nLocale()),
				$this->eiGuiValueBoundary->createSiValueBoundary($n2nLocale));

		$siGui->setEntryControlsIncluded($this->entrySiControlsIncluded);

		$siControls = $this->generalGuiControlMap?->createSiControls() ?? [];

		$siGui->setControls(array_merge($this->zoneGuiControlsMap->createSiControls(), $siControls));

		return $siGui;
	}

}