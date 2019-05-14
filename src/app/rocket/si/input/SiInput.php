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
namespace rocket\si\input;

use n2n\util\type\ArgUtils;
use n2n\l10n\Message;

class SiInput {
	/**
	 * @var SiEntryInput[] $entryInputs
	 */
	protected $entryInputs = [];
	
	/**
	 * @return SiEntryInput[];
	 */
	function getEntryInputs() {
		return $this->entryInputs;
	}
	
	function putEntryInput(SiEntryInput $entryInput) {
		$this->entryInputs[] = $entryInput;
	}
}

class SiEntryInput {
	
}

class SiInputError {
	
}

class SiEntryInputError {
	
}

class SiFieldInput {
	private $data;
	private $errors;
	
	/**
	 * @param array $data
	 * @param Message[] $errors
	 */
	function __construct(array $data, array $errors) {
		ArgUtils::valArray($errors, Message::class);
		$this->data = $data;
		$this->errors = $errors;
		
	}
	
	/**
	 * @return array
	 */
	function getData() {
		return $this->data;
	}
	
	/**
	 * @return Message[]
	 */
	function getErrors() {
		return $this->errors;
	}
}