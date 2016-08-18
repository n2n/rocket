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
namespace rocket\spec\ei\component\field\impl\relation\command;

use rocket\spec\ei\component\command\impl\EiCommandAdapter;
use rocket\spec\ei\manage\EiState;
use n2n\web\http\controller\ControllerAdapter;
use rocket\spec\ei\component\field\impl\relation\model\relation\EiFieldRelation;
use n2n\web\http\controller\ParamQuery;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use n2n\web\ui\view\impl\html\AjahResponse;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\map\InvalidPropertyExpressionException;
use n2n\web\http\BadRequestException;
use rocket\spec\ei\component\command\impl\common\controller\ControllingUtils;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\component\command\impl\common\controller\OverviewAjahController;
use n2n\util\uri\Url;
use rocket\spec\ei\component\command\impl\common\controller\OverviewAjahHook;
use rocket\spec\ei\component\field\impl\relation\model\mag\MappingForm;

class RelationAjahEiCommand extends EiCommandAdapter {
	private $eiFieldRelation;
	
	public function __construct(EiFieldRelation $eiFieldRelation) {
		$this->eiFieldRelation = $eiFieldRelation;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\command\EiCommand::createController()
	 */
	public function lookupController(EiState $eiState) {
		$selectController = $eiState->getN2nContext()->lookup(RelationAjahController::class);
		return $selectController;
	}
}
