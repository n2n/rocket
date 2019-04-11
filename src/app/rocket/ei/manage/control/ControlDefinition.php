<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\control;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\util\Eiu;
use n2n\util\type\ArgUtils;
use rocket\ei\IdPath;
use n2n\util\type\TypeUtils;
use n2n\util\StringUtils;
use rocket\ei\manage\entry\EiEntry;
use rocket\si\control\SiControl;

class ControlDefinition {
	
	/**
	 * @var ControlCommand[] key = id
	 */
	private $controlCommands;
	
	/**
	 * @param ControlCommand[]
	 */
	function __construct(array $controlCommands) {
		$this->controlCommands = $controlCommands;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param EiControlPath $eiControlPath
	 * @return EntryEiControl
	 * @throwsn UnknownEiControlException
	 */
	function createEntryEiControl(EiFrame $eiFrame, EiEntry $eiEntry, EiControlPath $eiControlPath): EntryEiControl {
		if ($eiControlPath->size() != 2) {
			throw new UnknownEiControlException('Unknown EiControlPath ' . $eiControlPath);
		}
		
		$eiu = new Eiu($eiFrame, $eiEntry);
		$cmdId = $eiControlPath->getFirstId();
		$controlId = $eiControlPath->getLastId();
		
		foreach ($this->controlCommands as $id => $controlCommand) {
			if ($cmdId != $id) {
				continue;
			}
			
			$eiControls = $this->extractEntryEiControls($controlCommand, $id, $eiu);
			if ($eiControls[$controlId]) {
				return $eiControls[$controlId];
			}
		}
		
		throw new UnknownEiControlException('Unknown EiControlPath ' . $eiControlPath);
	}
	
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @return SiControl[]
	 */
	function createEntrySiControls(EiFrame $eiFrame, EiEntry $eiEntry): array {
		$eiu = new Eiu($eiFrame, $eiEntry);
		
		$siControls = [];
		foreach ($this->controlCommands as $id => $controlCommand) {
			foreach ($this->extractEntryEiControls($controlCommand, $id, $eiu) as $entryEiControl) {
				$eiControlPath = new EiControlPath([$id, $entryEiControl->getId()]);
				
				$siControls[] = $entryEiControl->toSiControl($eiControlPath);
			}
		}
		return $siControls;
	}
	
	private function extractEntryEiControls(ControlCommand $controlCommand, string $controlCommandId, Eiu $eiu) {
		$entryEiControls = $controlCommand->createEntryEiControls($eiu);
		ArgUtils::valArrayReturn($entryEiControls, $controlCommand, 'createEntryEiControls', EntryEiControl::class);
		
		return $this->mapEiControls($entryEiControls, $controlCommand, EntryEiControl::class);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param EiControlPath $eiControlPath
	 * @return GeneralEiControl
	 * @throwsn UnknownEiControlException
	 */
	function createGeneralEiControl(EiFrame $eiFrame,  EiControlPath $eiControlPath) {
		if ($eiControlPath->size() != 2) {
			throw new UnknownEiControlException('Unknown EiControlPath ' . $eiControlPath);
		}
		
		$eiu = new Eiu($eiFrame);
		$cmdId = $eiControlPath->getFirstId();
		$controlId = $eiControlPath->getLastId();
		
		foreach ($this->controlCommands as $id => $controlCommand) {
			if ($cmdId != $id) {
				continue;
			}
			
			$eiControls = $this->extractEntryEiControls($controlCommand, $id, $eiu);
			if ($eiControls[$controlId]) {
				return $eiControls[$controlId];
			}
		}
		
		throw new UnknownEiControlException('Unknown EiControlPath ' . $eiControlPath);
	}
	
	
	/**
	 * @param EiFrame $eiFrame
	 * @return SiControl[]
	 */
	function createEntrySiControls(EiFrame $eiFrame): array {
		$eiu = new Eiu($eiFrame);
		
		$siControls = [];
		foreach ($this->controlCommands as $id => $controlCommand) {
			foreach ($this->extractGeneralEiControls($controlCommand, $id, $eiu) as $generalEiControl) {
				$eiControlPath = new EiControlPath([$id, $generalEiControl->getId()]);
				
				$siControls[] = $generalEiControl->toSiControl($eiControlPath);
			}
		}
		return $siControls;
	}
	
	/**
	 * @param ControlCommand $controlCommand
	 * @param string $controlCommandId
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\control\GeneralEiControl[]
	 */
	private function extractGeneralEiControls(ControlCommand $controlCommand, string $controlCommandId, Eiu $eiu) {
		$generalEiControls = $controlCommand->createGeneralEiControls($eiu);
		ArgUtils::valArrayReturn($generalEiControls, $controlCommand, 'extractGeneralEiControls', GeneralEiControl::class);
		
		return $this->mapEiControls($generalEiControls, $controlCommand, GeneralEiControl::class);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiControlPath $eiControlPath
	 * @return GeneralEiControl
	 * @throwsn UnknownEiControlException
	 */
	function createSelectionEiControl(EiFrame $eiFrame,  EiControlPath $eiControlPath) {
		if ($eiControlPath->size() != 2) {
			throw new UnknownEiControlException('Unknown EiControlPath ' . $eiControlPath);
		}
		
		$eiu = new Eiu($eiFrame);
		$cmdId = $eiControlPath->getFirstId();
		$controlId = $eiControlPath->getLastId();
		
		foreach ($this->controlCommands as $id => $controlCommand) {
			if ($cmdId != $id) {
				continue;
			}
			
			$eiControls = $this->extractSelectionEiControls($controlCommand, $id, $eiu);
			if ($eiControls[$controlId]) {
				return $eiControls[$controlId];
			}
		}
		
		throw new UnknownEiControlException('Unknown EiControlPath ' . $eiControlPath);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @return SiControl[]
	 */
	function createSelectionSiControls(EiFrame $eiFrame): array {
		$eiu = new Eiu($eiFrame);
		
		$siControls = [];
		foreach ($this->controlCommands as $id => $controlCommand) {
			foreach ($this->extractSelectionEiControls($controlCommand, $id, $eiu) as $selectionEiControl) {
				$eiControlPath = new EiControlPath([$id, $selectionEiControl->getId()]);
				
				$siControls[] = $selectionEiControl->toSiControl($eiControlPath);
			}
		}
		return $siControls;
	}
	
	/**
	 * @param ControlCommand $controlCommand
	 * @param string $controlCommandId
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\control\GeneralEiControl[]
	 */
	private function extractSelectionEiControls(ControlCommand $controlCommand, string $controlCommandId, Eiu $eiu) {
		$selectionEiControls = $controlCommand->createSelectionEiControls($eiu);
		ArgUtils::valArrayReturn($selectionEiControls, $controlCommand, 'createSelectionEiControls', SelectionEiControl::class);
		
		return $this->mapEiControls($selectionEiControls, $controlCommand, SelectionEiControl::class);
	}
	
	/**
	 * @param EiControl[] $eiControls
	 * @return EiControl[]
	 */
	private function mapEiControls($eiControls, $controlCommand, $eiControlClassName) {
		$mappedEiControls = [];
		
		foreach ($eiControls as $eiControl) {
			$id = $eiControl->getId();
			
			if (!IdPath::isIdValid($id)) {
				throw new \InvalidArgumentException(StringUtils::strOf($controlCommand) . ' returns ' 
						. $eiControlClassName . ' with illegal id: ' . $id);
			}
			
			if (isset($mappedEiControls[$id])) {
				throw new \InvalidArgumentException(StringUtils::strOf($controlCommand) . ' returns multiple ' 
						. $eiControlClassName . ' objects with id: ' . $id);
			}
			
			$mappedEiControls[$id] = $eiControl;
		}
		
		return $mappedEiControls;
	}
	
}