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

use rocket\ei\component\command\EiCmdNature;
use rocket\impl\ei\component\EiComponentNatureAdapter;
use rocket\ei\component\command\EiCommand;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use rocket\ei\component\command\GuiEiCmd;
use rocket\ei\manage\gui\GuiCommand;
use n2n\l10n\Lstr;
use n2n\util\StringUtils;

abstract class EiCmdNatureAdapter extends EiComponentNatureAdapter implements EiCmdNature, GuiEiCmd, GuiCommand {
	private $wrapper;
	
	public function setWrapper(EiCommand $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	public function getWrapper(): EiCommand {
		if ($this->wrapper !== null) {
			return $this->wrapper;
		}
		
		throw new IllegalStateException(get_class($this) . ' is not assigned to a Wrapper.');
	}
	
	public function getId() {
		return (string) $this->wrapper->getEiCommandPath();
	}
	
	public function getLabelLstr(): Lstr {
		return StringUtils::pretty($this->getIdBase());
	}
	
	public function isPrivileged(): bool {
		return true;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCmdNature::lookupController()
	 */
	public function lookupController(Eiu $eiu): ?Controller {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponentNature::__toString()
	 */
	public function __toString(): string {
		return (new \ReflectionClass($this))->getShortName()
				. ' (id: ' . ($this->wrapper ? $this->wrapper->getEiCommandPath() : 'unknown') . ')';
	}	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponentNature::equals()
	 */
	public function equals($obj) {
		return $obj instanceof EiCmdNature && parent::equals($obj);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\GuiEiCmd::buildGuiCommand()
	 */
	public function buildGuiCommand(Eiu $eiu): ?GuiCommand {
		return $this;
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCmdNature::createSelectionGuiControls()
	 */
	public function createSelectionGuiControls(Eiu $eiu): array {
		return [];
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCmdNature::createEntryGuiControls()
	 */
	public function createEntryGuiControls(Eiu $eiu): array {
		return [];
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCmdNature::createOverallControls()
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
// 	 * @see \rocket\ei\component\cmd\EiCommand::createSelectionGuiControls()
// 	 */
// 	public function createSelectionGuiControls(): array {
// 		return $this->adapter->createSelectionGuiControls($this->eiu);
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\cmd\EiCommand::createEntryGuiControls()
// 	 */
// 	public function createEntryGuiControls(Eiu $eiu): array {
// 		return $this->adapter->createEntryGuiControls($eiu);
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\cmd\EiCommand::createOverallControls()
// 	 */
// 	public function createGeneralGuiControls(): array {
// 		return $this->adapter->createGeneralGuiControls($this->eiu);
// 	}
// }