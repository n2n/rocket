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
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\spec\ei\manage\util\model;

// use rocket\spec\ei\manage\mapping\EiEntry;
// use rocket\spec\ei\manage\gui\EiEntryGui;
// use rocket\spec\ei\manage\model\EntryModel;
// use rocket\spec\ei\mask\EiMask;

// class EntryInfo implements EntryModel {
// 	private $eiMask;
// 	private $eiEntryGui;
// 	private $eiEntry;
	
// 	public function __construct(EiMask $eiMask, EiEntryGui $eiEntryGui,
// 			EiEntry $eiEntry) {
// 		$this->eiMask = $eiMask;
// 		$this->eiEntryGui = $eiEntryGui;
// 		$this->eiEntry = $eiEntry;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \rocket\spec\ei\manage\model\ManageModel::getGuiDefinition()
// 	*/
// 	public function getEiMask() {
// 		return $this->eiMask;
// 	}
	
// 	public function getEiEntryGui() {
// 		return $this->eiEntryGui;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \rocket\spec\ei\manage\model\EntryModel::getEiEntry()
// 	 */
// 	public function getEiEntry() {
// 		return $this->eiEntry;
// 	}
	
// // 	public function hasListEntryModel() {
// // 		return $this->listEntryModel !== null;
// // 	} 
	
// // 	public function getListEntryModel() {
// // 		return $this->listEntryModel;
// // 	}
	
// // 	public function setListEntryModel(ListEntryModel $listEntryModel)  {
// // 		$this->listEntryModel = $listEntryModel;
// // 	}
// }
