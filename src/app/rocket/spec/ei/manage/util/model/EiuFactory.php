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
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\EiFrame;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\ManageException;
use n2n\web\http\HttpContextNotAvailableException;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\LiveEntry;
use rocket\spec\ei\manage\LiveEiSelection;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\DraftEiSelection;
use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\manage\gui\EiSelectionGui;
use rocket\spec\ei\component\field\EiField;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\EiSpec;

class EiuFactory {
	const EI_FRAME_TYPES = array(EiFrame::class, N2nContext::class);
	const EI_ENTRY_TYPES = array(EiSelection::class, EiMapping::class, LiveEntry::class, Draft::class, 
			EntryGuiModel::class);
	const EI_GUI_TYPES = array(EntryGuiModel::class, EiSelectionGui::class);
	const EI_TYPES = array(EiFrame::class, N2nContext::class, EiSelection::class, EiMapping::class, LiveEntry::class, 
			Draft::class, EntryGuiModel::class, EiSelectionGui::class, EiField::class, EiFieldPath::class);
	const EI_FIELD_TYPES = array(EiField::class, EiFieldPath::class);
	
	private $eiFrame;
	private $n2nContext;
	private $eiSelection;
	private $eiMapping;
	private $eiSelectionGui;
	private $eiFieldPath;
	
	private $eiuFrame;
	private $eiuEntry;
	private $eiuGui;
	private $eiuField;
	
	public function applyEiArgs(...$eiArgs) {
		foreach ($eiArgs as $key => $eiArg) {
			if ($eiArg instanceof EiFrame) {
				$this->eiFrame = $eiArg;
				continue;
			}
	
			if ($eiArg instanceof N2nContext) {
				$this->n2nContext = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiField) {
				$this->eiFieldPath = EiFieldPath::from($eiArg);
				continue;
			}
				
			if ($eiArg instanceof EiFieldPath) {
				$this->eiFieldPath = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiSelectionGui) {
				$this->eiSelectionGui = $eiArg;
			}
			
			if (null !== ($eiSelection = self::determineEiSelection($eiArg, $this->eiMapping, $this->eiSelectionGui))) {
				$this->eiSelection = $eiSelection;
				continue;
			}
			
			if ($eiArg instanceof EiuField) {
				$this->eiuField = $eiArg;
				$eiArg = $eiArg->getEiuEntry(false);
			}
			
			if ($eiArg instanceof EiuGui) {
				$this->eiuGui = $eiArg;
				$this->eiSelectionGui = $eiArg->getEiSelectionGui();
				$eiArg = $eiArg->getEiuEntry(false);
			}
			
			if ($eiArg instanceof EiuEntry) {
				$this->eiSelection = $eiArg->getEiSelection();
				$this->eiMapping = $eiArg->getEiMapping(false);
				$eiArg = $eiArg->getEiuFrame(false);
			}
			
			if ($eiArg instanceof EiuFrame) {
				$this->eiuFrame = $eiArg;
				continue;
			}

			if ($eiArg instanceof EiuCtrl) {
				$this->eiuFrame = $eiArg->frame();
				continue;
			}
			
			if ($eiArg instanceof Eiu) {
				$this->eiuField = $this->eiuField ?? $eiArg->field(false);
				$this->eiuEntry = $this->eiuEntry ?? $eiArg->entry(false);
				$this->eiuFrame = $this->eiuFrame ?? $eiArg->frame(false);
				$this->eiuCtrl = $this->eiuCtrl ?? $eiArg->ctrl(false);
				continue;
			}
			
			ArgUtils::valType($eiArg, self::EI_TYPES, true, 'eiArg#' . ($key + 1));
		}
		
	}
	
// 	public function getEiFrame(bool $required) {
// 		if (!$required || $this->eiFrame !== null) {
// 			return $this->eiSelectionGui;
// 		}
	
// 		throw new EiuPerimeterException(
// 				'Could not determine EiuFrame because non of the following types were provided as eiArgs: '
// 						. implode(', ', self::EI_FRAME_TYPES));
// 	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\gui\EiSelectionGui
	 */
	public function getEiSelectionGui(bool $required) {
		if (!$required || $this->eiSelectionGui !== null) {
			return $this->eiSelectionGui;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiuGui because non of the following types were provided as eiArgs: ' 
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	public function getEiFieldPath(bool $required) {
		if (!$required || $this->eiFieldPath !== null) {
			return $this->eiFieldPath;
		}
	
		throw new EiuPerimeterException(
				'Could not create EiuField because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_FIELD_TYPES));
	}
			
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function getEiuFrame(bool $required) {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		if ($this->eiFrame !== null) {
			return $this->eiuFrame = new EiuFrame($this->eiFrame);
		} 
		
		if ($this->n2nContext !== null) {
			try {
				return new EiuFrame($this->n2nContext->lookup(ManageState::class)->peakEiFrame());
			} catch (ManageException $e) {
				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuFrame because non of the following types were provided as eiArgs: ' 
						. implode(', ', self::EI_FRAME_TYPES));
	}
	
	public function getEiuEntry(bool $required) {
		if ($this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		$eiuFrame = $this->getEiuFrame(false);
		
		if ($eiuFrame !== null) {
			if ($this->eiMapping !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiMapping, true);
			}
			
			if ($this->eiSelection !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiSelection, true);
			}
		} else {
			if ($this->eiMapping !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiMapping);
			}
				
			if ($this->eiSelection !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiSelection);
			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuFrame because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_TYPES));
	}
	

	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuGui
	 */
	public function getEiuGui(bool $required) {
		if ($this->eiuGui !== null) {
			return $this->eiuGui;
		}
		
		if ($this->eiSelectionGui !== null) {
			$eiuEntry = $this->getEiuEntry(false);
			if ($eiuEntry !== null) {
				return $this->eiuGui = $eiuEntry->assignEiuGui($this->eiSelectionGui);
			} 
			
			return $this->eiuGui = new EiuGui($this->eiSelectionGui);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuGui because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	

	public function getEiuField(bool $required) {
		if ($this->eiuField !== null) {
			return $this->eiuField;
		}
	
		$eiuEntry = $this->getEiuEntry(false);
		if ($eiuEntry !== null) {
			if ($this->eiFieldPath !== null) {
				return $this->eiuField = $eiuEntry->field($this->eiFieldPath);
			}
		} else {
			if ($this->eiFieldPath !== null) {
				return $this->eiuField = new EiuField($this->eiFieldPath);
			}
		}
	
		if (!$required) return null;
	
		throw new EiuPerimeterException(
				'Can not create EiuField because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_FIELD_TYPES));
	}
	
	public static function buildEiuFrameFormEiArg($eiArg, string $argName = null, bool $required = false) {
		if ($eiArg instanceof EiuFrame) {
			return $eiArg;
		}
		
		if ($eiArg === null && !$required) {
			return null;
		}
		
		if ($eiArg instanceof EiFrame) {
			return new EiuFrame($eiArg);
		}
		
		if ($eiArg instanceof N2nContext) {
			try {
				return new EiuFrame($eiArg->lookup(ManageState::class)->preakEiFrame());
			} catch (ManageException $e) {
				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
			}
		}
		
		if ($eiArg instanceof EiuCtrl) {
			return $eiArg->frame();
		}
		
		if ($eiArg instanceof EiuEntry) {
			return $eiArg->getEiuFrame($required);
		}
		
		ArgUtils::valType($eiArg, self::EI_FRAME_TYPES, !$required, $argName);
	}
	
	public static function buildEiuEntryFromEiArg($eiArg, EiuFrame $eiuFrame = null, string $argName = null, bool $required = false) {
		if ($eiArg instanceof EiuEntry) {
			return $eiArg;
		}
		
		if ($eiArg instanceof EiuFrame) {
			return $eiArg->getAssignedEiuEntry($required);
		}
		
		if ($eiArg !== null) {
			return new EiuEntry($eiArg, $eiuFrame);
		}
			
		if ($eiuFrame !== null) {
			return $eiuFrame->getAssignedEiuEntry($required);
		}
		
		if (!$required) {
			return null;
		}
		
		ArgUtils::valType($eiArg, self::EI_ENTRY_TYPES);
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @return rocket\spec\ei\manage\util\model\EiSelection
	 */
	public static function determineEiSelection($eiEntryObj, &$eiMapping, &$eiSelectionGui) {
		if ($eiEntryObj instanceof EiSelection) {
			return $eiEntryObj;
		} 
			
		if ($eiEntryObj instanceof EiMapping) {
			$eiMapping = $eiEntryObj;
			return $eiEntryObj->getEiSelection();
		}
		
		if ($eiEntryObj instanceof LiveEntry) {
			return new LiveEiSelection($eiEntryObj);
		}
		
		if ($eiEntryObj instanceof Draft) {
			return new DraftEiSelection($eiEntryObj);
		}
		
		if ($eiEntryObj instanceof EntryGuiModel) {
			$eiMapping = $eiEntryObj->getEiMapping();
			$eiSelectionGui = $eiEntryObj->getEiSelectionGui();
			return $eiMapping->getEiSelection();
		}
			
		return null;
	}
	
	public static function buildEiSelectionFromEiArg($eiEntryObj, string $argName = null, EiSpec $eiSpec = null, bool $required = true, &$eiMapping = null, &$viewMode = null) {
		if (!$required && $eiEntryObj === null) {
			return null;
		}
		
		if (null !== ($eiSelection = self::determineEiSelection($eiEntryObj, $eiMapping, $viewMode))) {
			return $eiSelection;
		}
		
		$eiEntryTypes = self::EI_ENTRY_TYPES;
		
		if ($eiSpec !== null) {
			$eiEntryTypes[] = $eiSpec->getEntityModel()->getClass()->getName();
			try {
				return LiveEiSelection::create($eiSpec, $eiEntryObj);
			} catch (\InvalidArgumentException $e) {
				return null;
			}
		}
		
		ArgUtils::valType($eiEntryObj, $eiEntryTypes, !$required, $argName);
	}
}
