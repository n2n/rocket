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
namespace rocket\ei\manage;

use rocket\ei\manage\gui\control\GuiControlPath;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\ViewMode;
use n2n\util\type\attrs\DataSet;

class SiApiCallId implements \JsonSerializable {
	private $guiControlPath;
	private $viewMode;
	
	public function __construct(GuiControlPath $guiControlPath, int $viewMode) {
		$this->guiControlPath = $guiControlPath;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\control\GuiControlPath
	 */
	function getGuiControlPath() {
		return $this->guiControlPath;
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
	public function jsonSerialize() {
		return [
			'guiControlPath' => (string) $this->guiControlPath,
			'viewMode' => $this->viewMode
		];
	}
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return SiApiCallId
	 */
	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			return new SiApiCallId(
					GuiControlPath::create($ds->reqString('guiControlPath')),
					$ds->reqInt('viewMode'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}