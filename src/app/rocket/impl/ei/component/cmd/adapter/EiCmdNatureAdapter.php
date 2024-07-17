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
namespace rocket\impl\ei\component\cmd\adapter;

use rocket\op\ei\component\command\EiCmdNature;
use rocket\impl\ei\component\EiComponentNatureAdapter;
use rocket\op\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use rocket\ui\gui\GuiCommand;
use n2n\l10n\Lstr;
use n2n\util\StringUtils;
use rocket\ui\si\control\SiNavPoint;
use rocket\op\ei\manage\gui\EiGuiCommand;

abstract class EiCmdNatureAdapter extends EiComponentNatureAdapter implements EiCmdNature, EiGuiCommand {
	
	public function getLabelLstr(): Lstr {
		return StringUtils::pretty($this->getIdBase());
	}
	
	public function isPrivileged(): bool {
		return true;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\command\EiCmdNature::lookupController()
	 */
	public function lookupController(Eiu $eiu): ?Controller {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\EiComponentNature::equals()
	 */
	public function equals(mixed $obj): bool {
		return $obj instanceof EiCmdNature && parent::equals($obj);
	}

	function buildOverviewNavPoint(Eiu $eiu): ?SiNavPoint {
		return null;
	}

	function buildEditNavPoint(Eiu $eiu): ?SiNavPoint {
		return null;
	}

	function buildDetailNavPoint(Eiu $eiu): ?SiNavPoint {
		return null;
	}

	function buildAddNavPoint(Eiu $eiu): ?SiNavPoint {
		return null;
	}

	public function buildEiGuiCommand(Eiu $eiu): ?EiGuiCommand {
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\command\EiCmdNature::createSelectionGuiControls()
	 */
	public function createSelectionGuiControls(Eiu $eiu): array {
		return [];
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\command\EiCmdNature::createEntryGuiControls()
	 */
	public function createEntryGuiControls(Eiu $eiu): array {
		return [];
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\command\EiCmdNature::createOverallControls()
	 */
	public function createGeneralGuiControls(Eiu $eiu): array {
		return [];
	}
}


// class StatelessGuiCommand implements GuiCommand {
// 	private $eiu;
// 	private $adapter;
	
// 	function __construct(EiCommandAdapter $adapter, Eiu $eiu) {
// 		$this->eiu = $eiu;
// 		$this->adapter = $adapter;
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\op\ei\component\cmd\EiCommand::createSelectionGuiControls()
// 	 */
// 	public function createSelectionGuiControls(): array {
// 		return $this->adapter->createSelectionGuiControls($this->eiu);
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\op\ei\component\cmd\EiCommand::createEntryGuiControls()
// 	 */
// 	public function createEntryGuiControls(Eiu $eiu): array {
// 		return $this->adapter->createEntryGuiControls($eiu);
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\op\ei\component\cmd\EiCommand::createOverallControls()
// 	 */
// 	public function createGeneralGuiControls(): array {
// 		return $this->adapter->createGeneralGuiControls($this->eiu);
// 	}
// }