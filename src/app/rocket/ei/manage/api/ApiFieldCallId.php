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
namespace rocket\ei\manage\api;

use n2n\util\type\attrs\DataSet;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\spec\TypePath;

class ApiFieldCallId implements \JsonSerializable {
	private $guiFieldPath;
	private $eiTypePath;
	private $viewMode;
	private $pid;
	
	public function __construct(GuiFieldPath $guiFieldPath, TypePath $eiTypePath, int $viewMode, ?string $pid) {
		$this->guiFieldPath = $guiFieldPath;
		$this->eiTypePath = $eiTypePath;
		$this->viewMode = $viewMode;
		$this->pid = $pid;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\control\GuiControlPath
	 */
	function getGuiFieldPath() {
		return $this->guiFieldPath;
	}
	
	/**
	 * @return string|null
	 */
	function getPid() {
		return $this->pid;
	}
	
	/**
	 * @return \rocket\spec\TypePath
	 */
	function getEiTypePath() {
		return $this->eiTypePath;
	}
	
	/**
	 * @return int
	 */
	function getViewMode() {
		return $this->viewMode;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'guiFieldPath' => (string) $this->guiFieldPath,
			'eiTypePath' => (string) $this->eiTypePath,
			'viewMode' => $this->viewMode,
			'pid' => $this->pid
		];
	}
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return ApiFieldCallId
	 */
	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			return new ApiFieldCallId(
					GuiFieldPath::create($ds->reqString('guiFieldPath')),
					TypePath::create($ds->reqString('eiTypePath')),
					$ds->reqInt('viewMode'),
					$ds->optString('pid'));
		} catch (\n2n\util\type\attrs\DataSetException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}