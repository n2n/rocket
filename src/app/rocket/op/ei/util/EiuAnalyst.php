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
namespace rocket\op\ei\util;

use rocket\op\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\EiObject;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\EiEntityObj;
use rocket\op\ei\manage\LiveEiObject;
use rocket\op\ei\manage\draft\Draft;
use rocket\op\ei\manage\DraftEiObject;
use rocket\op\ei\manage\ManageState;
use rocket\ui\gui\EiGuiValueBoundary;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\EiType;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\gui\EiGuiDefinition;
use rocket\ui\gui\EiGuiValueBoundaryAssembler;
use rocket\op\ei\EiEngine;
use rocket\op\spec\Spec;
use rocket\op\ei\EiTypeExtension;
use rocket\core\model\Rocket;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\util\spec\EiuContext;
use rocket\op\ei\util\spec\EiuEngine;
use rocket\op\ei\util\spec\EiuMask;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\util\gui\EiuGuiEntry;
use rocket\op\ei\util\gui\EiuGuiDefinition;
use rocket\op\ei\util\entry\EiuField;
use rocket\op\ei\util\spec\EiuCmd;
use rocket\op\ei\util\spec\EiuProp;
use rocket\op\ei\manage\entry\EiFieldMap;
use rocket\op\ei\util\entry\EiuFieldMap;
use rocket\op\ei\util\entry\EiuObject;
use n2n\util\type\TypeUtils;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\spec\UnknownTypeException;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\util\gui\EiuGuiField;
use rocket\ui\gui\GuiEntry;
use rocket\ui\gui\EiGuiDeclaration;
use rocket\op\ei\util\gui\EiuGuiDeclaration;
use rocket\op\ei\component\prop\EiProp;
use rocket\op\ei\component\command\EiCmd;
use rocket\op\ei\component\modificator\EiMod;
use rocket\op\ei\manage\EiLaunch;
use n2n\util\ex\NotYetImplementedException;
use rocket\op\ei\UnknownEiTypeException;
use InvalidArgumentException;
use rocket\op\util\OpuCtrl;
use rocket\op\ei\util\gui\EiuGuiValueBoundary;

class EiuAnalyst {
	const EI_FRAME_TYPES = array(EiFrame::class, EiuFrame::class, N2nContext::class);
	const EI_ENTRY_TYPES = array(EiObject::class, EiEntry::class, EiEntityObj::class, Draft::class, 
			EiGuiValueBoundary::class, EiuEntry::class, EiuGuiEntry::class);
	const EI_GUI_TYPES = array(EiGuiDefinition::class, EiuGuiDefinition::class, EiGuiValueBoundary::class, EiuGuiEntry::class);
	const EI_ENTRY_GUI_TYPES = array(EiGuiValueBoundary::class, EiuGuiEntry::class);
	const EI_TYPES = array(EiFrame::class, N2nContext::class, EiObject::class, EiEntry::class, EiEntityObj::class, 
			Draft::class, EiGuiDefinition::class, EiuGuiDefinition::class, EiGuiValueBoundary::class, EiGuiValueBoundary::class, EiProp::class,
			EiPropPath::class, EiuFrame::class, EiuEntry::class, EiuGuiEntry::class, EiuField::class, Eiu::class);
	const EI_FIELD_TYPES = array(EiProp::class, EiPropPath::class, EiuField::class);
	const EI_CMD_TYPES = [EiCmd::class, EiuCmd::class, EiCmdPath::class];
	
	protected $n2nContext;
	protected $eiType;
	protected $eiLaunch;
	protected ?EiFrame $eiFrame = null;
	protected $eiObject;
	protected $eiEntry;
	protected $eiGuiDeclaration;
	protected $eiGuiDefinition;
	protected $eiGui;
	protected $eiGuiEntry;
	protected $eiGuiValueBoundary;
	protected $eiGuiValueBoundaryAssembler;
	protected $eiPropPath;
	protected $defPropPath;
	protected $eiCmdPath;
	protected $eiEngine;
	protected $spec;
	protected $eiMask;
	
	protected $eiuContext;
	protected $eiuEngine;
	protected $eiuFrame;
	protected $eiuObject;
	protected $eiuEntry;
	protected $eiuFieldMap;
	protected $eiFieldMap;
//	protected $eiuGui;
	protected $eiuGuiDeclaration ;
	protected $eiuGuiDefinition;
	protected $eiuGuiEntry;
	protected $eiuGuiEntryTypeDef;
	protected $eiuGuiEntryAssembler;
	protected $eiuGuiField;
	protected $eiuField;
	protected $eiuMask;
	protected $eiuType;
	protected $eiuProp;
	protected $eiuCmd;
	protected $eiuCommand;

	private ?array $unappliedEiArgs = null;
	
	public function applyEiArgs(...$eiArgs) {
		$this->unappliedEiArgs = $eiArgs;
	}

	protected function ensureAppied() {
		if ($this->unappliedEiArgs === null) {
			return;
		}

		$eiArgs = $this->unappliedEiArgs;
		$this->unappliedEiArgs = null;

		$remainingEiArgs = array();
		
		foreach ($eiArgs as $key => $eiArg) {
			if ($eiArg === null) {
				continue;
			}
			
			if ($eiArg instanceof N2nContext) {
				$this->n2nContext = $eiArg;
				continue;
			}

			if ($eiArg instanceof EiLaunch) {
				$this->eiLaunch = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiFrame) {
				$this->assignEiFrame($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiuFrame) {
				$this->assignEiFrame($eiArg->getEiFrame());
				continue;
			}
			
			if ($eiArg instanceof EiProp) {
				$this->eiPropPath = EiPropPath::from($eiArg);
				$this->assignEiMask($eiArg->getEiPropCollection()->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiPropPath) {
				$this->eiPropPath = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof DefPropPath) {
				$this->defPropPath = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiCmd) {
				$this->eiCmdPath = EiCmdPath::from($eiArg);
				$this->assignEiMask($eiArg->getEiCommandCollection()->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiCmdPath) {
				$this->eiCmdPath = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiMod) {
				$this->assignEiMask($eiArg->getEiModCollection()->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiEngine) {
				$this->assignEiEngine($eiArg);
				continue;
			}
			
			if ($eiArg instanceof Spec) {
				$this->spec = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiGuiDeclaration) {
				$this->assignEiGuiDeclaration($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiGuiDefinition) {
				$this->assignEiGuiDefinition($eiArg);
				continue;
			}
			
			if ($eiArg instanceof GuiEntry) {
				$this->assignEiGuiEntry($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiGuiValueBoundary) {
				$this->assignEiGuiValueBoundary($eiArg);
				continue;
			}
			
//			if ($eiArg instanceof EiGui) {
//				$this->assignEiGui($eiArg);
//				continue;
//			}
			
			if ($eiArg instanceof EiuGuiField) {
				throw new NotYetImplementedException();
//				$this->assignEiuGuiField($eiArg);
//				continue;
			}
			
			if ($eiArg instanceof EiGuiValueBoundaryAssembler) {
				$this->assignEiGuiValueBoundaryAssembler($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiMask) {
				$this->assignEiMask($eiArg);
				continue;
			}
			
//			if ($eiArg instanceof EiComponent) {
//				$this->assignEiMask($eiArg->getCollection()->getMask());
//				continue;
//			}
			
			if ($eiArg instanceof EiType) {
				$this->assignEiType($eiArg, true);
				continue;
			}
			
			if ($eiArg instanceof EiTypeExtension) {
				$this->assignEiMask($eiArg->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiObject) {
				$this->assignEiObject($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiEntry) {
				$this->assignEiEntry($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiFieldMap) {
				$this->assignEiFieldMap($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiEntityObj) {
				$this->assignEiObject(new LiveEiObject($eiArg));
				continue;
			}
			
			if ($eiArg instanceof Draft) {
				$this->assignEiObject(new DraftEiObject($eiArg));
				continue;
			}
			
			if ($eiArg instanceof EiuField) {
				throw new NotYetImplementedException();
//				$this->assignEiuField($eiArg);
//				continue;
			}
			
			if ($eiArg instanceof EiuMask) {
				$this->assignEiMask($eiArg->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiuGuiValueBoundary) {
				$this->assignEiGuiValueBoundary($eiArg->getEiGuiValueBoundary());
				continue;
			}
			
			if ($eiArg instanceof EiuGuiEntry) {
				$this->assignEiGuiEntry($eiArg->getEiGuiEntry());
				continue;
			}
			
			if ($eiArg instanceof EiuGuiDeclaration ) {
				$this->assignEiGuiDeclaration($eiArg->getEiGuiDeclaration());
				continue;
			}
			
			if ($eiArg instanceof EiuGuiDefinition) {
				$this->assignEiGuiDefinition($eiArg->getEiGuiDefinition());
				continue;
			}
			
//			if ($eiArg instanceof EiuGui) {
//				$this->assignEiGui($eiArg->getEiGui());
//				continue;
//			}
			
			if ($eiArg instanceof EiuObject) {
				$this->assignEiObject($eiArg->getEiObject());
				continue;
			}
			
			if ($eiArg instanceof EiuEntry) {
				$this->assignEiEntry($eiArg->getEiEntry());
				continue;
			}
			
			if ($eiArg instanceof EiuFieldMap) {
				$this->assignEiFieldMap($eiArg->getEiFieldMap());
				continue;
			}
			
			if ($eiArg instanceof EiuProp) {
				throw new NotYetImplementedException();
//				$this->assignEiProp($eiArg->getEiProp());
//				continue;
			}
			
			if ($eiArg instanceof EiuCmd) {
				throw new NotYetImplementedException();
//				$this->assignEiCommand($eiArg->getEiCmd());
//				continue;
			}
			
			if ($eiArg instanceof EiuEngine) {
				$this->assignEiEngine($eiArg->getEiEngine());
				continue;
			}
			
// 			if ($eiArg instanceof EiuContext) {
// 				$this->assignEiuContext($eiArg);
// 				continue;
// 			}
			
			if ($eiArg instanceof OpuCtrl) {
				$eiArg = $eiArg->eiu();
			}
			
			if ($eiArg instanceof Eiu) {
				$eiuAnalyst = $eiArg->getEiuAnalyst();
				$eiuAnalyst->ensureAppied();
				
				if ($eiuAnalyst->n2nContext !== null) {
					$this->n2nContext = $eiuAnalyst->n2nContext;
				}
				if ($eiuAnalyst->eiType !== null) {
					$this->eiType = $eiuAnalyst->eiType;
				}
				if ($eiuAnalyst->eiLaunch !== null) {
					$this->eiLaunch = $eiuAnalyst->eiLaunch;
				}
				if ($eiuAnalyst->eiFrame !== null) {
					$this->eiFrame = $eiuAnalyst->eiFrame;
				}
				if ($eiuAnalyst->eiObject !== null) {
					$this->eiObject = $eiuAnalyst->eiObject;
				}
				if ($eiuAnalyst->eiEntry !== null) {
					$this->eiEntry = $eiuAnalyst->eiEntry;
				}
				if ($eiuAnalyst->eiFieldMap !== null) {
					$this->eiFieldMap = $eiuAnalyst->eiFieldMap;
				}
				if ($eiuAnalyst->eiGuiDeclaration !== null) {
					$this->eiGuiDeclaration = $eiuAnalyst->eiGuiDeclaration;
				}
				if ($eiuAnalyst->eiGuiDefinition !== null) {
					$this->eiGuiDefinition = $eiuAnalyst->eiGuiDefinition;
				}
				if ($eiuAnalyst->eiGuiValueBoundary !== null) {
					$this->eiGuiValueBoundary = $eiuAnalyst->eiGuiValueBoundary;
				}
				if ($eiuAnalyst->eiGuiValueBoundaryAssembler !== null) {
					$this->eiGuiValueBoundaryAssembler = $eiuAnalyst->eiGuiValueBoundaryAssembler;
				}
				if ($eiuAnalyst->eiPropPath !== null) {
					$this->eiPropPath = $eiuAnalyst->eiPropPath;
				}
				if ($eiuAnalyst->defPropPath !== null) {
					$this->defPropPath = $eiuAnalyst->defPropPath;
				}
				if ($eiuAnalyst->eiCmdPath !== null) {
					$this->eiCmdPath = $eiuAnalyst->eiCmdPath;
				}
				if ($eiuAnalyst->eiEngine !== null) {
					$this->eiEngine = $eiuAnalyst->eiEngine;
				}
				if ($eiuAnalyst->spec !== null) {
					$this->spec = $eiuAnalyst->spec;
				}
				if ($eiuAnalyst->eiMask !== null) {
					$this->eiMask = $eiuAnalyst->eiMask;
				}
				
				continue;
			}
			
			$remainingEiArgs[$key + 1] = $eiArg;
		}
		
		if (empty($remainingEiArgs)) return;
		
		$eiType = null;
		$eiObjectTypes = self::EI_TYPES;
		if ($this->eiMask !== null) {
			$eiType = $this->eiMask->getEiType();
		} else if ($this->eiFrame !== null) {
			$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
			$eiObjectTypes[] = $eiType->getClass()->getName();
		}
		
		foreach ($remainingEiArgs as $argNo => $eiArg) {
			if ($eiType !== null && is_object($eiArg)) {
				try {
					$this->eiObject = LiveEiObject::create($eiType, $eiArg);
					continue;
				} catch (\InvalidArgumentException $e) {
					return null;
				}
			}
			
			ArgUtils::valType($eiArg, $eiObjectTypes, true, 'eiArg#' . $argNo);
		}	
	}
	
	/**
	 * @param EiuFrame $eiuFrame
	 */
	private function assignEiuFrame($eiuFrame) {
		if ($this->eiuFrame === $eiuFrame) {
			return;
		}
		
		$this->assignEiFrame($eiuFrame->getEiFrame());
		$this->eiuFrame = $eiuFrame;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 */
	private function assignEiFrame($eiFrame) {
		if ($this->eiFrame === $eiFrame) {
			return;
		}
		
		ArgUtils::assertTrue($this->eiFrame === null, 'EiFrame is not compatible.');
		
		$this->eiFrame = $eiFrame;
		$this->n2nContext = $eiFrame->getN2nContext();
				
		$this->assignEiType($eiFrame->getContextEiEngine()->getEiMask()->getEiType(), true);
	}
	
// 	/**
// 	 * @param EiuEngine $eiuEngine
// 	 */
// 	private function assignEiuEngine($eiuEngine) {
// 		if ($this->eiuEngine === $eiuEngine) {
// 			return;
// 		}
		
// 		$this->assignEiEngine($eiuEngine->getEiEngine());
// 		$this->eiuEngine = $eiuEngine;
// 	}
	
	/**
	 * @param EiEngine $eiEngine
	 */
	private function assignEiEngine($eiEngine) {
		if ($this->eiEngine === $eiEngine) {
			return;
		}
		
		$this->eiuEngine = null;
		$this->eiEngine = $eiEngine;
		
		$this->assignEiMask($eiEngine->getEiMask());
	}
	
// 	/**
// 	 * @param EiuProp $eiuProp
// 	 */
// 	private function assignEiuProp($eiuProp) {
// 		if ($this->eiuProp === $eiuProp) {
// 			return;
// 		}
		
// 		$this->assignEiuEngine($eiuProp->getEiuEngine());
// 		$this->eiPropPath = $eiuProp->getEiPropPath();
// 		$this->eiuProp = $eiuProp;
// 	}
		
// 	/**
// 	 * @param EiuCommand $eiuCommand
// 	 */
// 	private function assignEiuCommand($eiuCommand) {
// 		if ($this->eiuCommand === $eiuCommand) {
// 			return;
// 		}
		
// 		$this->assignEiuEngine($eiuCommand->getEiuEngine());
// 		$this->eiCmdPath = $eiuProp->getEiCmdPath();
// 		$this->eiuCommand = $eiuCommand;
// 	}
	
	
// 	/**
// 	 * @param EiuMask $eiuMask
// 	 */
// 	private function assignEiuMask($eiuMask) {
// 		if ($this->eiuMask === $eiuMask) {
// 			return;
// 		}
		
// 		$this->assignEiMask($eiuMask->getEiMask());
// 		$this->eiuMask = $eiuMask;
// 	}
	
	/**
	 * @param EiMask $eiMask
	 */
	private function assignEiMask($eiMask) {
		if ($this->eiMask === $eiMask) {
			return;
		}
		
		$this->eiuMask = null;
		$this->eiMask = $eiMask;
		
		$this->assignEiType($eiMask->getEiType(), false);
		
		if ($eiMask->hasEiEngine()) {
			$this->assignEiEngine($eiMask->getEiEngine());
		}
	}
	
	/**
	 * @param EiType $eiType
	 */
	private function assignEiType($eiType, bool $contextOnly) {
		if ($this->eiType === $eiType) {
			return;
		}
		
		if ($this->eiType === null || $eiType->isA($this->eiType)) {
			$this->eiType = $eiType;
			return;
		}
		
		if ($this->eiType->isA($eiType)) {
			return;
		}
		
		throw new \InvalidArgumentException('Incompatible EiTypes ' . $this->eiType->getId() . ' / ' 
				. $eiType->getId());
	}
	
// 	/**
// 	 * @param EiuGuiDefinition $eiuGuiDefinitionLayout
// 	 */
// 	private function assignEiuGui($eiuGuiDefinitionLayout) {
// 		if ($this->eiuGuiDefinitionLayout === $eiuGuiDefinitionLayout) {
// 			return;
// 		}
		
// 		$this->assignEiGui($EiGui);
// 		$this->eiuGuiDefinitionLayout = $eiuGuiDefinitionLayout;
// 	}
	
//	/**
//	 * @param EiGui $EiGui
//	 */
//	private function assignEiGui($eiGui) {
//		if ($this->eiGui === $eiGui) {
//			return;
//		}
//
//		$this->assignEiGuiDeclaration($eiGui->getEiGuiDeclaration());
//
//		$this->eiuGui = null;
//		$this->eiGui = $eiGui;
//
//// 		$eiGuiValueBoundaries = $eiGui->getEiGuiValueBoundaries();
//// 		if (count($eiGuiValueBoundaries) == 1) {
//// 			$this->assignEiGuiValueBoundary(current($eiGuiValueBoundaries));
//// 		}
//	}
	
// 	/**
// 	 * @param EiuGuiDefinition $eiuGuiDefinition
// 	 */
// 	private function assignEiuGuiDefinition($eiuGuiDefinition) {
// 		if ($this->eiuGuiDefinition === $eiuGuiDefinition) {
// 			return;
// 		}
		
// 		$this->assignEiGuiDefinition($eiuGuiDefinition->getEiGuiDefinition());
// 		$this->eiuGuiDefinition = $eiuGuiDefinition;
// 	}
	
	/**
	 * @param EiGuiDeclaration $eiGuiDeclaration
	 */
	private function assignEiGuiDeclaration($eiGuiDeclaration) {
		if ($this->eiGuiDeclaration === $eiGuiDeclaration) {
			return;
		}

		$this->eiGuiDeclaration = $eiGuiDeclaration;
		
		
		// 		$eiGuiValueBoundaries = $eiGuiDefinition->getEiGuiValueBoundaries();
		// 		if (count($eiGuiValueBoundaries) == 1) {
		// 			$this->assignEiGuiValueBoundary(current($eiGuiValueBoundaries));
		// 		}
	}
	
	/**
	 * @param EiGuiDefinition $eiGuiDefinition
	 */
	private function assignEiGuiDefinition($eiGuiDefinition) {
		if ($this->eiGuiDefinition === $eiGuiDefinition) {
			return;
		}

		if ($this->eiFrame !== null && !$this->eiFrame->getContextEiEngine()->getEiMask()->matchesTypePath($eiGuiDefinition->getEiTypePath(), false, true)) {
			throw new EiuPerimeterException('EiGuiDefinition is not compatible with EiFrame');
		}

		$this->eiuGuiDefinition = null;
		$this->eiGuiDefinition = $eiGuiDefinition;

		$this->assignEiMask($eiGuiDefinition->getEiMask());
		
// 		$eiGuiValueBoundaries = $eiGuiDefinition->getEiGuiValueBoundaries();
// 		if (count($eiGuiValueBoundaries) == 1) {
// 			$this->assignEiGuiValueBoundary(current($eiGuiValueBoundaries));
// 		}
	}
	
// 	/**
// 	 * @param EiuGuiEntry $eiuGuiEntry
// 	 */
// 	private function assignEiuGuiEntry($eiuGuiEntry) {
// 		if ($this->eiuGuiEntry === $eiuGuiEntry) {
// 			return;
// 		}
		
// 		$this->assignEiuGuiDefinition($eiuGuiEntry->guiFrame());
// 		$this->assignEiGuiValueBoundary($eiuGuiEntry->getEiGuiValueBoundary());
// 		$this->eiuGuiEntry = $eiuGuiEntry;
// 	}

	/**
	 * @param GuiEntry $eiGuiEntry
	 */
	private function assignEiGuiEntry($eiGuiEntry) {
		if ($this->eiGuiEntry === $eiGuiEntry) {
			return;
		}
		
		IllegalStateException::assertTrue($this->eiGuiEntry === null);
		
		$this->eiuGuiEntryTypeDef = null;
		$this->eiGuiEntry = $eiGuiEntry;
		
		$this->assignEiEntry($eiGuiEntry->getEiEntry());
	}
	
	/**
	 * @param EiGuiValueBoundary $eiGuiValueBoundary
	 */
	private function assignEiGuiValueBoundary($eiGuiValueBoundary) {
		if ($this->eiGuiValueBoundary === $eiGuiValueBoundary) {
			return;
		}
		
		$this->eiuGuiEntry = null;
		$this->eiGuiValueBoundary = $eiGuiValueBoundary;
		
		if ($eiGuiValueBoundary->isEiGuiEntrySelected()) {
			$this->assignEiGuiEntry($eiGuiValueBoundary->getSelectedEiGuiEntry());
		}
		
		$this->assignEiGuiDeclaration($eiGuiValueBoundary->getEiGuiDeclaration());
	}
	
// 	/**
// 	 * @param EiuGuiEntryAssembler $eiuGuiEntryAssembler
// 	 */
// 	private function assignEiuGuiDefinitionAssembler($eiuGuiEntryAssembler) {
// 		if ($this->eiuGuiEntryAssembler === $eiuGuiEntryAssembler) {
// 			return;
// 		}
		
// 		$this->assignEiGuiValueBoundaryAssembler($eiuGuiEntryAssembler->getEiGuiValueBoundaryAssembler());
// // 		$this->assignEiuGuiEntry($eiuGuiEntryAssembler->getEiuGuiEntry());
// 		$this->eiuGuiEntryAssembler = $eiuGuiEntryAssembler;
// 	}
	
	/**
	 * @param EiGuiValueBoundaryAssembler $eiGuiValueBoundaryAssembler
	 */
	private function assignEiGuiValueBoundaryAssembler($eiGuiValueBoundaryAssembler) {
		if ($this->eiGuiValueBoundaryAssembler === $eiGuiValueBoundaryAssembler) {
			return;
		}
		
		$this->eiuGuiEntryAssembler = null;
		$this->eiGuiValueBoundaryAssembler = $eiGuiValueBoundaryAssembler;
		
		$this->assignEiGuiValueBoundary($eiGuiValueBoundaryAssembler->getEiGuiValueBoundary());
	}
	
// 	/**
// 	 * @param EiuField $eiuField
// 	 */
// 	private function assignEiuField($eiuField) {
// 		if ($this->eiuField === $eiuField) {
// 			return;
// 		}
		
// 		$this->assignEiuEntry($eiuField->getEiuEntry());
		
// 		$this->eiuField = $eiuField;
// 		$this->eiPropPath = $eiuField->getEiPropPath();
// 	}
	
// 	/**
// 	 * @param EiuGuiField $eiuGuiDefinitionField
// 	 */
// 	private function assignEiuGuiField($eiuGuiDefinitionField) {
// 		if ($this->eiuGuiDefinitionField === $eiuGuiDefinitionField) {
// 			return;
// 		}
		
// 		$this->assignEiuGuiEntry($eiuGuiDefinitionField->getEiuGuiEntry());
		
// 		$this->eiuField = $eiuField;
// 		$this->eiPropPath = $eiuField->getEiPropPath();
// 	}
	
// 	/**
// 	 * @param EiuEntry $eiuEntry
// 	 */
// 	private function assignEiuObject($eiuObject) {
// 		if ($this->eiuObject === $eiuObject) {
// 			return;
// 		}
		
// 		$this->assignEiObject($eiuObject->getEiObject());
		
// 		$this->eiuObject = $eiuObject;
// 	}
	
// 	/**
// 	 * @param EiuEntry $eiuEntry
// 	 */
// 	private function assignEiuEntry($eiuEntry) {
// 		if ($this->eiuEntry === $eiuEntry) {
// 			return;
// 		}
		
// 		if (null !== ($eiEntry = $eiuEntry->getEiEntry(false))) {
// 			$this->assignEiEntry($eiEntry);
// 		} else {
// 			$this->assignEiObject($eiuEntry->object()->getEiObject());
// 		}
		
// 		if (null !== ($eiuFrame = $eiuEntry->getEiuFrame(false))) {
// 			$this->assignEiuFrame($eiuFrame);
// 		}
		
// 		$this->eiuEntry = $eiuEntry;
// 	}
	
// 	private function assignEiuFieldMap($eiuFieldMap) {
// 		if ($this->eiuFieldMap === $eiuFieldMap) {
// 			return;
// 		}
		
// 		$this->assignEiFieldMap($eiuFieldMap->getEiFieldMap());
		
// 		$this->eiuFieldMap = $eiuFieldMap;
// 	}
	
	/**
	 * @param EiEntry $eiObject
	 */
	private function assignEiEntry($eiEntry) {
		if ($this->eiEntry === $eiEntry) {
			return;
		}
		
		$this->eiuEntry = null;
		$this->eiEntry = $eiEntry;
		
		$this->assignEiObject($eiEntry->getEiObject());
		$this->assignEiFieldMap($eiEntry->getEiFieldMap());
		$this->assignEiMask($eiEntry->getEiMask());
	}
	
	private function assignEiFieldMap($eiFieldMap) {
		if ($this->eiFieldMap === $eiFieldMap) {
			return;
		}
		
		$this->eiuFieldMap = null;
		$this->eiFieldMap = $eiFieldMap;
	}
	
	/**
	 * @param EiObject $eiObject
	 */
	private function assignEiObject($eiObject) {
		if ($this->eiObject === $eiObject) {
			return;
		}
		
		$this->eiuObject = null;
		$this->eiuEntry = null;
		$this->eiObject = $eiObject;
		
		$this->assignEiType($eiObject->getEiEntityObj()->getEiType(), false);
	}
	
// 	public function getEiFrame(bool $required) {
// 		if (!$required || $this->eiFrame !== null) {
// 			return $this->eiGuiValueBoundary;
// 		}
	
// 		throw new EiuPerimeterException(
// 				'Could not determine EiuFrame because non of the following types were provided as eiArgs: '
// 						. implode(', ', self::EI_FRAME_TYPES));
// 	}
	
	/**
	 *
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\manage\frame\EiFrame|null
	 */
	public function getEiFrame(bool $required): ?EiFrame {
		$this->ensureAppied();

		if (!$required || $this->eiFrame !== null) {
			return $this->eiFrame;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiFrame because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FRAME_TYPES));
	}
	
	/**
	 * @return NULL|\rocket\op\ei\manage\entry\EiEntry
	 */
	public function getEiEntry() {
		$this->ensureAppied();

		return $this->eiEntry;
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\manage\EiObject|NULL
	 */
	public function getEiObject(bool $required) {
		$this->ensureAppied();

		if (!$required || $this->eiObject !== null) {
			return $this->eiObject;
		}
	
		throw new EiuPerimeterException(
				'Could not determine EiObject because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\ui\gui\Gui
	 *@throws EiuPerimeterException
	 */
	public function getEiGui(bool $required) {
		$this->ensureAppied();

		if (!$required || $this->eiGui !== null) {
			return $this->eiGui;
		}
	
		throw new EiuPerimeterException(
				'Could not determine EiGui because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @return EiGuiValueBoundary
	 *@throws EiuPerimeterException
	 */
	public function getEiGuiValueBoundary(bool $required) {
		$this->ensureAppied();

		if (!$required || $this->eiGuiValueBoundary !== null) {
			return $this->eiGuiValueBoundary;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiGuiValueBoundary because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_ENTRY_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\ui\gui\EiGuiValueBoundaryAssembler
	 * @throws EiuPerimeterException
	 */
	public function getEiGuiValueBoundaryAssembler(bool $required) {
		$this->ensureAppied();

		if (!$required || $this->eiGuiValueBoundaryAssembler !== null) {
			return $this->eiGuiValueBoundaryAssembler;
		}
		
		throw new EiuPerimeterException('Could not determine EiGuiValueBoundaryAssembler.');
	}
	
	public function getEiPropPath(bool $required) {
		$this->ensureAppied();

		if (!$required || $this->eiPropPath !== null) {
			return $this->eiPropPath;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiPropPath because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FIELD_TYPES));
	}
	
	public function getEiCmdPath(bool $required): ?EiCmdPath {
		$this->ensureAppied();

		if ($this->eiCmdPath !== null) {
			return $this->eiCmdPath;
		}

		if ($this->eiFrame !== null && $this->eiFrame->hasEiExecution()) {
			return $this->eiFrame->getEiExecution()->getEiCmd()->getEiCmdPath();
		}

		if (!$required) {
			return null;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiCmdPath because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FIELD_TYPES));
	}

	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\manage\DefPropPath
	 */
	public function getDefPropPath(bool $required) {
		$this->ensureAppied();

		if (!$required || $this->defPropPath !== null) {
			return $this->defPropPath;
		}
		
		throw new EiuPerimeterException(
				'Could not determine DefPropPath because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FIELD_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiEngine
	 */
	public function getEiEngine(bool $required) {
		$this->ensureAppied();

		if (!$required || $this->eiEngine !== null) {
			return $this->eiEngine;
		}
		
		throw new EiuPerimeterException('Could not determine EiEngine.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return Spec
	 */
	public function getSpec(bool $required) {
		$this->ensureAppied();

		if ($this->spec !== null) {
			return $this->spec;
		}

		if ($this->eiType !== null) {
			return $this->spec = $this->eiType->getSpec();
		}

		if ($this->n2nContext !== null) {
			return $this->n2nContext->lookup(Rocket::class)->getSpec();
		}
		
		throw new EiuPerimeterException('Could not determine Spec.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return N2nContext
	 */
	public function getN2nContext(bool $required) {
		$this->ensureAppied();

		if ($this->n2nContext === null && $this->eiLaunch !== null) {
			return $this->n2nContext = $this->eiLaunch->getN2nContext();
		}

		if (!$required || $this->n2nContext !== null) {
			return $this->n2nContext;
		}
		
		throw new EiuPerimeterException('Could not determine N2nContext.');
	}
	
	/**
	 * @return ManageState
	 */
	function getManageState() {
		$this->ensureAppied();

		return $this->getN2nContext(true)->lookup(ManageState::class);
	}

	function getEiLaunch(bool $required): ?EiLaunch {
		$this->ensureAppied();

		if ($this->eiLaunch !== null) {
			return $this->eiLaunch;
		}

		return $this->eiLaunch = $this->getEiFrame($required)?->getEiLaunch();
	}

	public function getEiuContext(bool $required) {
		$this->ensureAppied();

		if ($this->eiuContext !== null) {
			return $this->eiuContext;
		}
		
		$spec = null;
		try {
			$spec = $this->getSpec($required);
		} catch (EiuPerimeterException $e) {
			throw new EiuPerimeterException('Could not determine EiuContext.', 0, $e);
		}
		
		if ($spec === null) return null;
		
		return $this->eiuContext = new EiuContext($spec, $this);
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuEngine
	 */
	public function getEiuEngine(bool $required) {
		$this->ensureAppied();

		if ($this->eiuEngine !== null) {
			return $this->eiuEngine;
		}
		
		if ($this->eiEngine === null && $this->eiMask !== null && ($required || $this->eiMask->hasEiEngine())) {
			$this->eiEngine = $this->eiMask->getEiEngine();
		}
		
		if ($this->eiEngine !== null) {
			return $this->eiuEngine = new EiuEngine($this->eiEngine, $this->eiuMask, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('Can not create EiuEngine.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\mask\EiMask|NULL
	 */
	function getEiMask(bool $required) {
		$this->ensureAppied();

		if ($this->eiMask !== null) {
			return $this->eiMask;
		}
		
		if ($this->eiFrame !== null) {
			return $this->eiFrame->getContextEiEngine()->getEiMask();
		}
		
		if (!$required) {
			return null;
		}
		
		throw new EiuPerimeterException('EiMask not avaialble');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuMask
	 */
	public function getEiuMask(bool $required) {
		$this->ensureAppied();

		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		if (null !== ($eiMask = $this->getEiMask(false))) {
			return $this->eiuMask = new EiuMask($eiMask, $this->eiuEngine, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('EiuMask not avaialble');
	}

	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuFrame|null
	 */
	public function getEiuFrame(bool $required): ?EiuFrame {
		$this->ensureAppied();

		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		if ($this->eiFrame !== null) {
			return $this->eiuFrame = new EiuFrame($this->eiFrame, $this);
		}
		
// 		if ($this->n2nContext !== null) {
// 			try {
// 				return $this->eiuFrame = new EiuFrame($this->n2nContext->lookup(ManageState::class)->peakEiFrame(), $this);
// 			} catch (ManageException $e) {
// 				if (!$required) return null;
				
// 				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
// 			}
// 		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No EiuFrame avialble.');
	}
	
	public function getEiuObject(bool $required) {
		$this->ensureAppied();

		if ($this->eiuObject !== null) {
			return $this->eiuObject;
		}
		
		if ($this->eiObject !== null) {
			return $this->eiuObject = new EiuObject($this->eiObject, $this); 
		}	
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No EiuObject avialble.');
	}
	
	public function getEiuEntry(bool $required) {
		$this->ensureAppied();

		if ($this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		$eiuFrame = $this->getEiuFrame(false);
		
		if ($eiuFrame !== null) {
			if ($this->eiEntry !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiEntry, $this->getEiuObject(true), null, $this);
			}
			if ($this->eiObject !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiObject);
			}
		} else {
			if ($this->eiEntry !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiEntry, $this->getEiuObject(true), null, $this);
			}
// 			if ($this->eiObject !== null) {
// 				return $this->eiuEntry = new EiuEntry(null, $this->getEiuObject(true), null, $this);
// 			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No EiuEntry available.');
	}
	
	public function getEiuFieldMap(bool $required) {
		$this->ensureAppied();

		if ($this->eiuFieldMap !== null) {
			return $this->eiuFieldMap;
		}
		
		if ($this->eiFieldMap !== null) {
			return $this->eiuFieldMap = new EiuFieldMap($this->eiFieldMap, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('New EiuFieldMap available.');
	}
	
	public function getEiuProp(bool $required) {
		$this->ensureAppied();

		if ($this->eiuProp !== null) {
			return $this->eiuProp;
		}
		
		return $this->eiuProp = new EiuProp($this->getEiPropPath(true), $this->getEiuMask(true), $this);
	}

	public function getEiuCmd(bool $required) {
		$this->ensureAppied();

		if ($this->eiuCmd !== null) {
			return $this->eiuCmd;
		}

		return $this->eiuCmd = new EiuCmd($this->getEiCmdPath(true), $this->getEiuMask(true));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuGuiEntry
	 */
	public function getEiuGuiEntry(bool $required): ?EiuGuiEntry {
		$this->ensureAppied();

		if ($this->eiuGuiEntry !== null) {
			return $this->eiuGuiEntry;
		}
		
		if ($this->eiGuiEntry !== null) {
			return $this->eiuGuiEntry = new EiuGuiEntry($this->eiGuiEntry, $this->eiuEntry,
					$this->eiuGuiDefinition, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuGuiEntry because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_GUI_TYPES));
	}

//	/**
//	 * @param bool $required
//	 * @throws EiuPerimeterException
//	 * @return \rocket\op\ei\util\gui\EiuGuiEntryTypeDef
//	 */
//	public function getEiuGuiEntryTypeDef(bool $required) {
//		$this->ensureAppied();
//
//		if ($this->eiuGuiEntryTypeDef !== null) {
//			return $this->eiuGuiEntryTypeDef;
//		}
//
//		if ($this->eiGuiEntry !== null) {
//			return $this->eiuGuiEntryTypeDef = new EiuGuiEntryTypeDef($this->eiGuiEntry, $this->getEiuGuiEntry(false), $this);
//		}
//
//		if (!$required) return null;
//
//		throw new EiuPerimeterException(
//				'Can not create EiuGuiEntry because non of the following types were provided as eiArgs: '
//				. implode(', ', self::EI_ENTRY_GUI_TYPES));
//	}
//
//	/**
//	 * @param bool $required
//	 * @throws EiuPerimeterException
//	 * @return EiuGui
//	 */
//	public function getEiuGui(bool $required) {
//		$this->ensureAppied();
//
//		if ($this->eiuGui !== null) {
//			return $this->eiuGui;
//		}
//
//		if ($this->eiGui !== null) {
//			return $this->eiuGui = new EiuGui($this->eiGui, $this->eiuGuiDeclaration , $this);
//		}
//
//		if (!$required) return null;
//
//		throw new EiuPerimeterException('Can not create EiuGui because non of the following types were provided as eiArgs: '
//				. implode(', ', self::EI_GUI_TYPES));
//	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuGuiDeclaration 
	 */
	public function getEiuGuiDeclaration (bool $required) {
		$this->ensureAppied();

		if ($this->eiuGuiDeclaration  !== null) {
			return $this->eiuGuiDeclaration ;
		}
		
		if ($this->eiGuiDeclaration !== null) {
			return $this->eiGuiDeclaration = new EiuGuiDeclaration ($this->eiGuiDeclaration, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuGuiDeclaration  because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\util\gui\EiuGuiDefinition
	 */
	public function getEiuGuiDefinition(bool $required) {
		$this->ensureAppied();

		if ($this->eiuGuiDefinition !== null) {
			return $this->eiuGuiDefinition;
		}

		if ($this->eiGuiDefinition !== null) {
			return $this->eiuGuiDefinition = new EiuGuiDefinition($this->eiGuiDefinition, $this);
		}

		if (!$required) return null;

		throw new EiuPerimeterException(
				'Can not create EiuGuiDefinition because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	public function getEiuField(bool $required) {
		$this->ensureAppied();

		if ($this->eiuField !== null) {
			return $this->eiuField;
		}
	
		$eiuEntry = $this->getEiuEntry(false);
		if ($eiuEntry !== null) {
			if ($this->eiPropPath !== null) {
				return $this->eiuField = $eiuEntry->field($this->eiPropPath);
			}
		} else {
			if ($this->eiPropPath !== null) {
				throw new NotYetImplementedException();
//				return $this->eiuField = new EiuField($this->eiPropPath);
			}
		}
	
		if (!$required) return null;
	
		throw new EiuPerimeterException(
				'Can not create EiuField because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_FIELD_TYPES));
	}
	
	public function getEiuGuiField(bool $required) {
		$this->ensureAppied();

		if ($this->eiuGuiField !== null) {
			return $this->eiuGuiField;
		}
		
		$eiuGuiEntry = $this->getEiuGuiEntry(false);
		if ($eiuGuiEntry !== null) {
			if ($this->defPropPath !== null) {
				return $this->eiuGuiField = $eiuGuiEntry->field($this->defPropPath);
			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuField because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FIELD_TYPES));
	}
	
//	public static function buildEiuFrameFormEiArg($eiArg, ?string $argName = null, bool $required = false) {
//		if ($eiArg instanceof EiuFrame) {
//			return $eiArg;
//		}
//
//		if ($eiArg === null && !$required) {
//			return null;
//		}
//
//		if ($eiArg instanceof EiFrame) {
//			return new EiuFrame($eiArg, new EiuAnalyst());
//		}
//
//		if ($eiArg instanceof N2nContext) {
//			try {
//				return new EiuFrame($eiArg->lookup(ManageState::class)->preakEiFrame(), new EiuAnalyst());
//			} catch (ManageException $e) {
//				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
//			}
//		}
//
//		if ($eiArg instanceof OpuCtrl) {
//			return $eiArg->frame();
//		}
//
//		if ($eiArg instanceof EiuEntry) {
//			return $eiArg->getEiuFrame($required);
//		}
//
//		if ($eiArg instanceof Eiu) {
//			return $eiArg->frame();
//		}
//
//		ArgUtils::valType($eiArg, self::EI_FRAME_TYPES, !$required, $argName);
//	}

	function looseCopy(): EiuAnalyst {
		$eiuAnalyst = new EiuAnalyst();
		$eiuAnalyst->applyEiArgs($this->n2nContext);
		return $eiuAnalyst;
	}

	static function fromEiArgs(...$eiArgs): EiuAnalyst {
		$eiuAnalyst = new EiuAnalyst();
		$eiuAnalyst->applyEiArgs(...$eiArgs);
		return $eiuAnalyst;
	}
	
	/**
	 * @param mixed $eiArg
	 * @param EiuFrame $eiuFrame
	 * @param string $argName
	 * @param bool $required
	 * @return \rocket\op\ei\util\entry\EiuEntry|NULL
	 */
	public static function buildEiuEntryFromEiArg($eiArg, ?EiuFrame $eiuFrame = null, ?string $argName = null, bool $required = false) {
		if ($eiArg instanceof EiuEntry) {
			return $eiArg;
		}
		
		if ($eiArg !== null) {
			$eiEntry = null;
			$eiObject = self::determineEiObject($eiArg, $eiEntry);
			return (new Eiu($eiObject, $eiEntry, $eiuFrame))->entry();
		}
			
		if (!$required) {
			return null;
		}
		
		ArgUtils::valType($eiArg, self::EI_ENTRY_TYPES);
		throw new IllegalStateException();
	}
	
	/**
	 * @param mixed $eiObjectObj
	 * @return \rocket\op\ei\manage\EiObject|null
	 */
	public static function determineEiObject($eiObjectArg, &$eiEntry = null, &$eiGuiValueBoundary = null) {
		if ($eiObjectArg instanceof EiObject) {
			return $eiObjectArg;
		} 
			
		if ($eiObjectArg instanceof EiEntry) {
			$eiEntry = $eiObjectArg;
			return $eiObjectArg->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiEntityObj) {
			return new LiveEiObject($eiObjectArg);
		}
		
		if ($eiObjectArg instanceof Draft) {
			return new DraftEiObject($eiObjectArg);
		}
		
		if ($eiObjectArg instanceof EiuObject) {
			return $eiObjectArg->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiuEntry) {
			$eiEntry = $eiObjectArg->getEiEntry(false);
			return $eiObjectArg->object()->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiuGuiEntry) {
			return $eiObjectArg->getEiGuiEntry()->getEiEntry()->getEiObject();
		}
		
		if ($eiObjectArg instanceof Eiu && null !== ($eiuEntry = $eiObjectArg->entry(false))) {
			return $eiuEntry->object()->getEiObject();
		}
		
		return null;
// 		if (!$required) return null;
		
// 		throw new EiuPerimeterException('Can not determine EiObject of passed argument type ' 
// 				. TypeUtils::getTypeInfo($eiObjectArg) . '. Following types are allowed: '
// 				. implode(', ', array_merge(self::EI_FRAME_TYPES, self::EI_ENTRY_TYPES)));
	}
	
	/**
	 * 
	 * @param mixed $eiTypeObj
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\EiType|NULL
	 */
	public static function determineEiType($eiTypeArg, bool $required = false) {
		if (null !== ($eiObject = self::determineEiObject($eiTypeArg))) {
			return $eiObject->getEiEntityObj()->getEiType();
		}
		
		if ($eiTypeArg instanceof EiType) {
			return $eiTypeArg;
		}
		
		if ($eiTypeArg instanceof EiMask) {
			return $eiTypeArg->getEiEngine()->getEiMask()->getEiType();
		}
		
		if ($eiTypeArg instanceof EiFrame) {
			return $eiTypeArg->getContextEiEngine()->getEiMask()->getEiType();
		}
		
		if ($eiTypeArg instanceof Eiu && $eiuFrame = $eiTypeArg->frame(false)) {
			return $eiuFrame->getContextEiType();
		}
		
		if ($eiTypeArg instanceof EiuFrame) {
			return $eiTypeArg->getContextEiType();
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('Can not determine EiType of passed argument type ' 
				. TypeUtils::getTypeInfo($eiTypeArg) . '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, self::EI_ENTRY_TYPES)));
	}
	
	/**
	 * @param mixed $eiTypeArg
	 * @param Spec $spec
	 * @param string $argName
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\EiType
	 */
	public static function lookupEiTypeFromEiArg($eiTypeArg, Spec $spec, ?string $argName = null) {
		try {
			if ($eiTypeArg instanceof \ReflectionClass) {
				return $spec->getEiTypeByClass($eiTypeArg);
			}
			
			if (!is_scalar($eiTypeArg)) {
				return self::buildEiTypeFromEiArg($eiTypeArg, $argName, true);
			}
			
			if (!$spec->containsEiTypeId($eiTypeArg) && $spec->containsEiTypeClassName($eiTypeArg)) {
				return $spec->getEiTypeByClassName($eiTypeArg);
			}
			
			return $spec->getEiTypeByClassName($eiTypeArg);
		} catch (UnknownTypeException $e) {
			throw new EiuPerimeterException('Can not determine EiType of passed argument ' . $argName . ': '
					. \n2n\util\StringUtils::strOf($eiTypeArg, true), 0, $e);
		}
	}
	
	
	public static function buildEiTypesFromEiArg(?array $eiTypeArg, ?string $argName = null, bool $required = true) {
		if ($eiTypeArg === null) {
			return null;
		}
		
		return array_map(
				function ($eiTypeArg) use ($argName) { 
					return self::buildEiTypeFromEiArg($eiTypeArg, $argName, true);
				}, 
				$eiTypeArg);
	}
	
	public static function buildEiTypeFromEiArg($eiTypeArg, ?string $argName = null, bool $required = true) {
		if ($eiTypeArg === null && !$required) {
			return null;
		}
		
		if (null !== ($eiType = self::determineEiType($eiTypeArg))) {
			return $eiType;
		}
		
		throw new EiuPerimeterException('Can not determine EiType of passed argument ' . $argName 
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, self::EI_ENTRY_TYPES)) . '; '
				. TypeUtils::getTypeInfo($eiTypeArg) . ' given.');
	}

	/**
	 * @param array $eiEntryArgs
	 * @param string|null $argName
	 * @return EiEntry[]
	 */
	static function buildEiEntriesFromEiArg(array $eiEntryArgs, ?string $argName = null): array {
		$eiEntries = [];
		foreach ($eiEntryArgs as $eiEntryArg) {
			$eiEntries[] = self::buildEiEntryFromEiArg($eiEntryArg, $argName, true);
		}
		return $eiEntries;
	}

	/**
	 * @param mixed $eiEntryArg
	 * @param string $argName
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\op\ei\manage\entry\EiEntry
	 */
	public static function buildEiEntryFromEiArg($eiEntryArg, ?string $argName = null, bool $required = true) {
		if ($eiEntryArg instanceof EiEntry) {
			return $eiEntryArg;
		}
		
		if ($eiEntryArg instanceof EiuEntry) {
			return $eiEntryArg->getEiEntry($required);
		}
		
		throw new EiuPerimeterException('Can not determine EiEntry of passed argument ' . $argName
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_ENTRY_TYPES)) . '; '
				. TypeUtils::getTypeInfo($eiEntryArg) . ' given.');
	}
	
	/**
	 * 
	 * @param mixed $eiGuiValueBoundaryArg
	 * @param string $argName
	 * @param bool $required
	 * @return EiGuiValueBoundary
	 *@throws EiuPerimeterException
	 */
	public static function buildEiGuiValueBoundaryFromEiArg($eiGuiValueBoundaryArg, ?string $argName = null, bool $required = true) {
		if ($eiGuiValueBoundaryArg instanceof EiGuiValueBoundary) {
			return $eiGuiValueBoundaryArg;
		}
		
//		if ($eiGuiValueBoundaryArg instanceof EiuGuiEntry) {
//			return $eiGuiValueBoundaryArg->getEiGuiValueBoundary();
//		}
		
		if ($eiGuiValueBoundaryArg instanceof EiuGuiDefinition) {
			$eiGuiValueBoundaryArg = $eiGuiValueBoundaryArg->getEiGuiDefinition();
		}
		
//		if ($eiGuiValueBoundaryArg instanceof EiGuiDefinition) {
//			$eiGuiValueBoundaries = $eiGuiValueBoundaryArg->getEiGuiValueBoundaries();
//			if (1 == count($eiGuiValueBoundaries)) {
//				return current($eiGuiValueBoundaries);
//			}
//
//			throw new EiuPerimeterException('Can not determine EiGuiValueBoundary of passed EiGuiDefinition ' . $argName);
//		}
		
		throw new EiuPerimeterException('Can not determine EiGuiValueBoundary of passed argument ' . $argName
				. '. Following types are allowed: ' . implode(', ', array_merge(self::EI_ENTRY_GUI_TYPES)) . '; '
				. TypeUtils::getTypeInfo($eiGuiValueBoundaryArg) . ' given.');
	}
	
	public static function buildEiGuiDefinitionFromEiArg($eiGuiDefinitionArg, ?string $argName = null, bool $required = true) {
		if ($eiGuiDefinitionArg instanceof EiGuiDefinition) {
			return $eiGuiDefinitionArg;
		}
	
		if ($eiGuiDefinitionArg instanceof EiuGuiDefinition) {
			return $eiGuiDefinitionArg->getEiGuiDefinition();
		}
		
		if ($eiGuiDefinitionArg instanceof EiGuiValueBoundary) {
			return $eiGuiDefinitionArg->getEiGuiDeclaration();
		}
	
		if ($eiGuiDefinitionArg instanceof EiuGuiEntry) {
			return $eiGuiDefinitionArg->getEiGuiEntry()->getEiGuiDefinition();
		}
		
		if ($eiGuiDefinitionArg instanceof Eiu && null !== ($eiuGuiDefinition = $eiGuiDefinitionArg->guiDefinition(false))) {
			return $eiuGuiDefinition->getEiGuiDefinition();
		}
	
		throw new EiuPerimeterException('Can not determine EiGuiDefinition of passed argument ' . $argName
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_GUI_TYPES)) . '; '
				. TypeUtils::getTypeInfo($eiGuiDefinitionArg) . ' given.');
	}
	
	public static function buildEiObjectFromEiArg($eiObjectObj, ?string $argName = null, EiType|Spec|null $eiTypeOrSpec = null,
			bool $required = true, &$eiEntry = null, &$eiGuiDefinitionArg = null) {
		if (!$required && $eiObjectObj === null) {
			return null;
		}
		
		$eiGuiValueBoundary = null;
		if (null !== ($eiObject = self::determineEiObject($eiObjectObj, $eiEntry, $eiGuiValueBoundary))) {
			return $eiObject;
		}
		
		$eiObjectTypes = self::EI_ENTRY_TYPES;

		if ($eiTypeOrSpec instanceof Spec) {
			try {
				$eiTypeOrSpec = $eiTypeOrSpec->getEiTypeOfObject($eiObjectObj);
			} catch (UnknownEiTypeException $e) {
				throw new InvalidArgumentException('Argument of type ' . get_class($eiObjectObj)
						. ' could not narrated to a EiObject.');
			}
		}

		if ($eiTypeOrSpec !== null) {
			$eiObjectTypes[] = $eiTypeOrSpec->getClass()->getName();
			try {
				return LiveEiObject::create($eiTypeOrSpec, $eiObjectObj);
			} catch (\InvalidArgumentException $e) {
			}
		}
		
		ArgUtils::valType($eiObjectObj, $eiObjectTypes, !$required, $argName);
		throw new IllegalStateException();
	}
	
	public static function buildEiFrameFromEiArg($eiFrameObj, ?string $argName = null, bool $required = true) {
		if (!$required && $eiFrameObj === null) {
			return null;
		}
				
		if ($eiFrameObj instanceof EiFrame) {
			return $eiFrameObj;
		}
		
		if ($eiFrameObj instanceof EiuFrame) {
			return $eiFrameObj->getEiFrame();
		}
				
		ArgUtils::valType($eiFrameObj, [EiFrame::class, EiuFrame::class], !$required, $argName);
		throw new \LogicException();
	}
}