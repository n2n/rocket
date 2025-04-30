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
namespace rocket\ui\si\content\impl\basic;

use n2n\util\uri\Url;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\content\SiPartialContent;
use rocket\ui\si\content\SiGui;
use rocket\ui\si\control\SiControl;
use n2n\util\type\ArgUtils;
use rocket\ui\si\SiPayloadFactory;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\api\request\SiInput;
use rocket\ui\si\api\request\SiZoneCall;
use rocket\op\ei\manage\api\ZoneApiControlCallId;
use rocket\ui\si\api\response\SiCallResponse;
use n2n\core\container\N2nContext;
use rocket\ui\si\api\response\SiInputResult;
use rocket\ui\si\err\CorruptedSiDataException;

class CompactExplorerSiGui implements SiGui {
	/**
	 * @var string
	 */
	private $frame;
	/**
	 * @var int|null
	 */
	private $pageSize;
	/**
	 * @var SiDeclaration
	 */
	private $declaration;
	/**
	 * @var SiPartialContent
	 */
	private $partialContent;
	/**
	 * @var SiControl[]
	 */
	private $controls = [];
	
	/**
	 * @param Url $apiUrl
	 * @param int $pageSize
	 * @param SiDeclaration|null $declaration
	 * @param SiPartialContent|null $partialContent
	 */
	public function __construct(SiFrame $frame, int $pageSize, private string $siMaskId, ?SiDeclaration $declaration = null,
			?SiPartialContent $partialContent = null) {
		$this->frame = $frame;
		$this->pageSize = $pageSize;
		$this->declaration = $declaration;
		$this->partialContent = $partialContent;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiGui::getTypeName()
	 */
	public function getTypeName(): string {
		return 'compact-explorer';
	}
	
//	/**
//	 * @return Url
//	 */
//	public function getApiUrl(): Url {
//		return $this->frame;
//	}
	
	/**
	 * @return int
	 */
	public function getPageSize() {
		return $this->pageSize;
	}
	
	/**
	 * @param int $pageSize
	 * @return CompactExplorerSiGui
	 */
	public function setPageSize(int $pageSize) {
		$this->pageSize = $pageSize;
		return $this;
	}
	
	/**
	 * @param SiPartialContent|null $partialContent
	 * @return CompactExplorerSiGui
	 */
	public function setPartialContent(?SiPartialContent $partialContent) {
		$this->partialContent = $partialContent;
		return $this;
	}
	
	/**
	 * @return \rocket\ui\si\content\SiPartialContent
	 */
	public function getPartialContent() {
		return $this->partialContent;
	}
	
	/**
	 * @param SiControl[] $controls
	 */
	function setControls(array $controls) {
		ArgUtils::valArray($controls, SiControl::class);
		$this->controls = $controls;
	}
	
	/**
	 * @return SiControl[]
	 */
	function getControls() {
		return $this->controls;
	}

	function handleSiInput(SiInput $siInput, N2nContext $n2nContext): SiInputResult {
		throw new CorruptedSiDataException('BulkyEiGui can not handle multiple SiInputs.');
	}


	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiGui::toJsonStruct()
	 */
	public function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'frame' => $this->frame,
			'pageSize' => $this->pageSize,
			'maskId' => $this->siMaskId,
			'declaration' => $this->declaration,
			'partialContent' => $this->partialContent->toDataStruct($n2nContext),
			'controls' => SiPayloadFactory::createDataFromControls($this->controls)
		];
	}

}
