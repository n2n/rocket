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
namespace rocket\ui\si\content\impl\relation;

use rocket\ui\si\content\impl\basic\BulkyEntrySiGui;
use rocket\ui\si\content\impl\basic\CompactEntrySiGui;
use rocket\ui\si\SiPayloadFactory;
use rocket\ui\si\api\request\SiValueBoundaryInput;
use rocket\ui\si\content\SiEntryQualifier;
use n2n\core\container\N2nContext;

class SiEmbeddedEntry  {
	/**
	 * @var BulkyEntrySiGui
	 */
	private $content;
	/**
	 * @var CompactEntrySiGui|null
	 */
	private $summaryContent;
	
	/**
	 * @param BulkyEntrySiGui $content
	 * @param CompactEntrySiGui|null $summaryContent
	 */
	function __construct(BulkyEntrySiGui $content, ?CompactEntrySiGui $summaryContent = null) {
		$this->content = $content;
		$this->summaryContent = $summaryContent;
	}
	
	/**
	 * @return BulkyEntrySiGui
	 */
	function getContent() {
		return $this->content;
	}
	
	/**
	 * @param BulkyEntrySiGui $content
	 */
	function setContent(BulkyEntrySiGui $content) {
		$this->content = $content;
	}
	
	/**
	 * @return \rocket\ui\si\content\impl\basic\CompactEntrySiGui|null
	 */
	function getSummaryContent() {
		return $this->summaryContent;
	}
	
	/**
	 * @param \rocket\ui\si\content\impl\basic\CompactEntrySiGui|null $summaryContent
	 */
	function setSummaryContent(CompactEntrySiGui $summaryContent) {
		$this->summaryContent = $summaryContent;
	}

	function handleInput(SiEntryQualifier $qualifier): SiValueBoundaryInput {

	}

	function toJsonStruct(N2nContext $n2nContext): mixed {
		return [
			'content' => SiPayloadFactory::buildDataFromComp($this->content, $n2nContext),
			'summaryContent' => SiPayloadFactory::buildDataFromComp($this->summaryContent, $n2nContext)
		];
	}

}
