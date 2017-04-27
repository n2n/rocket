// <?php
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
// namespace rocket\spec\ei\component\command\impl\tree;

// use rocket\spec\ei\component\command\impl\tree\field\TreeRightEiField;
// use rocket\spec\ei\component\command\impl\tree\field\TreeLeftEiField;
// use rocket\spec\ei\component\command\impl\tree\controller\TreeController;
// use rocket\spec\ei\component\command\EiCommand;
// use rocket\spec\core\IncompatibleSpecException;
// use rocket\spec\ei\EiSpec;
// use rocket\spec\ei\manage\EiFrame;

// class TreeUtils {
// 	public static function findTreeField(EiSpec $eiSpec, &$treeLeftEiField = null, 
// 				&$treeRightEiField = null, &$treeRootIdEiField = null) {

// 		foreach ($eiSpec->getEiEngine()->getEiFieldCollection()->toArray() as  $eiField) {
// 			if ($eiField instanceof TreeLeftEiField) {
// 				$treeLeftEiField = $eiField;
// 			} else if ($eiField instanceof TreeRightEiField) {
// 				$treeRightEiField = $eiField;
// 			}
// 		}
// 	}
	
// 	public static function initializeController(EiCommand $eiCommand, TreeController $treeController, EiFrame $eiFrame) {
// 		$eiSpec = $eiCommand->getEiSpec();
// 		$treeLeftEiField = null;
// 		$treeRightEiField = null;
// 		$treeRootIdEiField = null;
// 		foreach ($eiFrame->getContextEiMask()->getEiDef()->getEiFieldCollection() as  $eiField) {
// 			if ($eiField instanceof TreeLeftEiField) {
// 				$treeLeftEiField = $eiField;
// 			} else if ($eiField instanceof TreeRightEiField) {
// 				$treeRightEiField = $eiField;
// 			}
// 		}

// 		if (null === $treeLeftEiField) {
// 			throw self::createIncompatibleSpecException($eiCommand, 'rocket\spec\ei\component\command\impl\tree\field\TreeLeftEiField');
// 		}

// 		if (null === $treeRightEiField) {
// 			throw self::createIncompatibleSpecException($eiCommand, 'rocket\spec\ei\component\command\impl\tree\field\TreeRightEiField');
// 		}

// 		$treeController->initialize($treeLeftEiField, $treeRightEiField);
		
// 		$eiFrame->getN2nContext()->magicInit($treeController);
// 	}

// 	private static function createIncompatibleSpecException(EiCommand $eiCommand, $missingEiField) {
// 		return new IncompatibleSpecException('Command \'' . get_class($eiCommand) . '\' requires a field of type \''
// 				. $missingEiField . '\'.');
// 	}
// }
