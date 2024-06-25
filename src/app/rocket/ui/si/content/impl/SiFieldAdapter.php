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
namespace rocket\ui\si\content\impl;

use n2n\util\ex\IllegalStateException;
use rocket\ui\si\content\SiField;
use rocket\ui\si\content\SiFieldModel;
use rocket\ui\si\content\BackableSiField;
use n2n\core\container\N2nContext;

abstract class SiFieldAdapter implements SiField, BackableSiField {
//	use SiFieldErrorTrait;

	private ?SiFieldModel $model = null;

	function setModel(?SiFieldModel $model): static {
		$this->model = $model;
		return $this;
	}

	function getModel(): ?SiFieldModel {
		return $this->model;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::isCallable()
	 */
	function isCallable(): bool {
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @param array $data
	 * @param array $uploadDefinitions
	 * @param N2nContext $n2nContext
	 * @see \rocket\ui\si\content\SiField::handleCall()
	 */
	function handleCall(array $data, array $uploadDefinitions, N2nContext $n2nContext): array {
		throw new IllegalStateException(get_class($this) . ' is not callable.');
	}

	function getData(): array {
		return ['messages' => $this->model->getMessages() ?? []];
	}


}
