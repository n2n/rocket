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
namespace rocket\spec;

use rocket\core\model\LaunchPad;
use n2n\core\container\N2nContext;
use n2n\web\http\controller\ControllerContext;
use n2n\web\http\controller\Controller;
use rocket\core\model\TransactionApproveAttempt;
use rocket\custom\CustomType;

class CustomLaunchPad implements LaunchPad {
	private $id;
	private $customSpec;
	private $label;
	
	public function __construct(string $id, CustomType $customSpec, string $label = null) {
		$this->id = $id;
		$this->customSpec = $customSpec;
		$this->label = $label;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\LaunchPad::getId()
	 */
	public function getId(): string {
		return $this->id;
	}
	
	public function getLabel(): string {
		if ($this->label === null) {
			return $this->customSpec->getControllerClass();
		}
		return $this->label;
	}
	
	public function isAccessible(N2nContext $n2nContext): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\LaunchPad::determinePathExt($n2nContext)
	 */
	public function determinePathExt(N2nContext $n2nContext) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\core\model\LaunchPad::lookupController($n2nContext, $delegateControllerContext)
	 */
	public function lookupController(N2nContext $n2nContext, ControllerContext $delegateControllerContext): Controller {
		return $n2nContext->lookup($this->customSpec->getControllerLookupId());
	}
	
	public function approveTransaction(N2nContext $n2nContext): TransactionApproveAttempt {
		return new TransactionApproveAttempt(array());
	}
}
