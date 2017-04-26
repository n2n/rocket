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
namespace rocket\spec\ei\component\command;

use rocket\spec\ei\component\EiComponentCollection;
use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\EiType;
use rocket\spec\ei\EiEngine;
use rocket\spec\ei\manage\control\EntryNavPoint;
use rocket\spec\ei\component\UnknownEiComponentException;
use n2n\util\uri\Path;
use n2n\reflection\ArgUtils;
use n2n\util\uri\Url;

class EiCommandCollection extends EiComponentCollection {
	
	/**
	 * @param EiType $eiType
	 */
	public function __construct(EiEngine $eiEngine) {
		parent::__construct('EiCommand', 'rocket\spec\ei\component\command\EiCommand');
		$this->setEiEngine($eiEngine);
	}

	public function getById(string $id): EiCommand {
		return $this->getEiComponentById($id);
	}
	
	public function add(EiCommand $eiCommand) {
		$this->addEiComponent($eiCommand);
	}
	
	public function hasGenericOverview() {
		return null !== $this->getGenericOverviewEiCommand(false);
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\spec\ei\component\command\GenericOverviewEiCommand
	 */
	public function getGenericOverviewEiCommand(bool $required = false) {
		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommand) {
			if ($eiCommand instanceof GenericOverviewEiCommand  && $eiCommand->isOverviewAvaialble()) {
				return $eiCommand;
			}
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiEngine->getEiThing() . ' provides no ' 
				. GenericOverviewEiCommand::class . '.');
	}
	
	public function getGenericOverviewUrlExt(bool $required = false) {
		$genericOverviewEiCommand = $this->getGenericOverviewEiCommand($required);
		if (null === $genericOverviewEiCommand) return null;
		
		$urlExt = $genericOverviewEiCommand->getOverviewUrlExt();
		ArgUtils::valTypeReturn($urlExt, Url::class, $genericOverviewEiCommand, 'getOverviewUrlExt', true);
		ArgUtils::assertTrueReturn($urlExt === null || $urlExt->isRelative(), $genericOverviewEiCommand, 
				'getOverviewUrlExt', 'Returned Url must be relative.');
			
		return (new Path(array($genericOverviewEiCommand->getId())))->toUrl()->ext($urlExt);
	}
	
	public function hasGenericDetail(EntryNavPoint $entryNavPoint) {
		return null !== $this->getGenericDetailEiCommand($entryNavPoint, false);
	}
	
	/**
	 * @param EntryNavPoint $entryNavPoint
	 * @param bool $required
	 * @return GenericDetailEiCommand
	 */
	public function getGenericDetailEiCommand(EntryNavPoint $entryNavPoint, bool $required = false) {
		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommand) {
			if ($eiCommand instanceof GenericDetailEiCommand && $eiCommand->isDetailAvailable($entryNavPoint)) {
				return $eiCommand;
			}
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiEngine->getEiThing() . ' provides no ' 
				. GenericDetailEiCommand::class . ' for ' . $entryNavPoint);
	}
	
	public function getGenericDetailUrlExt(EntryNavPoint $entryNavPoint, bool $required = false) {
		$genericDetailEiCommand = $this->getGenericDetailEiCommand($entryNavPoint, $required);
		if (null === $genericDetailEiCommand) return null;
	
		$urlExt = $genericDetailEiCommand->getDetailUrlExt($entryNavPoint);
		ArgUtils::valTypeReturn($urlExt, Url::class, $genericDetailEiCommand, 'getDetailUrlExt', true);
		ArgUtils::assertTrueReturn($urlExt === null || $urlExt->isRelative(), $genericDetailEiCommand, 
				'getDetailUrlExt', 'Returned Url must be relative.');
			
		return (new Path(array($genericDetailEiCommand->getId())))->toUrl()->ext($urlExt);
	}
	
	/**
	 * @param unknown $entryNavPoint
	 * @param bool $required
	 * @return GenericEditEiCommand
	 */
	public function getGenericEditEiCommand(EntryNavPoint $entryNavPoint, bool $required = false) {
		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommand) {
			if ($eiCommand instanceof GenericEditEiCommand && $eiCommand->isEditAvailable($entryNavPoint)) {
				return $eiCommand;
			}
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiEngine->getEiThing() . ' provides no ' 
				. GenericEditEiCommand::class . ' for ' . $entryNavPoint);
	}
	
	public function getGenericEditUrlExt(EntryNavPoint $entryNavPoint, bool $required = false) {
		$genericEditEiCommand = $this->getGenericEditEiCommand($entryNavPoint, $required);
		if (null === $genericEditEiCommand) return null;
	
		$urlExt = $genericEditEiCommand->getEditUrlExt($entryNavPoint);
		ArgUtils::valTypeReturn($urlExt, Url::class, $genericEditEiCommand, 'getEditUrlExt', true);
		ArgUtils::assertTrueReturn($urlExt === null || $urlExt->isRelative(), $genericEditEiCommand, 'getEditUrlExt',
				'Returned Url must be relative.');
			
		return (new Path(array($genericEditEiCommand->getId())))->toUrl()->ext($urlExt);
	}
	
	/**
	 * @param bool $draft
	 * @param bool $required
	 * @return GenericAddEiCommand
	 */
	private function getGenericAddEiCommand(bool $draft, bool $required = false) {
		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommand) {
			if ($eiCommand instanceof GenericAddEiCommand && $eiCommand->isAddAvailable($draft)) {
				return $eiCommand;
			}
		}
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiEngine->getEiThing() . ' provides no ' 
				. GenericEditEiCommand::class . ' for ' . ($draft ? 'draft entry' : 'live entry'));
	}
	
	public function getGenericAddUrlExt(bool $draft, bool $required = false) {
		$genericAddEiCommand = $this->getGenericEditEiCommand($draft, $required);
		if (null === $genericAddEiCommand) return null;
	
		$urlExt = $genericAddEiCommand->getAddUrlExt($draft);
		ArgUtils::valTypeReturn($urlExt, Url::class, $genericAddEiCommand, 'getAddUrlExt', true);
		ArgUtils::assertTrueReturn($urlExt === null || $urlExt->isRelative(), $genericAddEiCommand, 
				'getAddUrlExt', 'Returned Url must be relative.');
			
		return (new Path(array($genericAddEiCommand->getId())))->toUrl()->ext($urlExt);
	}
}
