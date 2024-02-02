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
namespace rocket\tool\mail\model;

use rocket\tool\xml\ItemCountSaxHandler;
use rocket\tool\xml\MailItemSaxHandler;
use n2n\util\type\ArgUtils;
use rocket\tool\xml\SaxParser;
use n2n\core\N2N;
use n2n\core\VarStore;
use n2n\log4php\appender\nn6\AdminMailCenter;
use rocket\tool\mail\controller\MailArchiveBatchController;
use n2n\io\managed\impl\CommonFile;
use n2n\io\managed\impl\FsFileSource;
use n2n\util\io\fs\FsPath;

class MailCenter {
	const NUM_MAILS_PER_PAGE = 30;
	const ATTACHMENT_INDEX_DEFAULT = 'default';
	
	private ?FsPath $mailXmlFilePath = null;
	private VarStore $varStore;
	
	private $currentPageNum = 1;
	
	private $numItemsTotal = 0;
	private ?array $currentItems;
	
	public function __construct(VarStore $varStore, FsPath $mailXmlFilePath = null) {
		$this->varStore = $varStore;
		$this->mailXmlFilePath = $mailXmlFilePath;
	}

	public function getCurrentPageNum(): int {
		return $this->currentPageNum;
	}
	
	public function setCurrentPageNum($currentPageNum): void {
		ArgUtils::assertTrue(is_numeric($currentPageNum) && $currentPageNum > 0 
				&& $currentPageNum <= $this->getNumPages());
		$this->currentPageNum = $currentPageNum;
	}
	
	public function getNumItemsTotal(): int {
		if (0 === $this->numItemsTotal && $this->isFilePathAvailable()) {
			$parser = new SaxParser();
			$itemCountSaxHandler = new ItemCountSaxHandler();
			$parser->parse($this->mailXmlFilePath, $itemCountSaxHandler);
			$this->numItemsTotal = $itemCountSaxHandler->getNumber();
		}
		return $this->numItemsTotal;
	}
	
	/**
	 * @return \rocket\tool\xml\MailItem[]
	 */
	public function getCurrentItems(): ?array {
		if (null === $this->currentItems && $this->isFilePathAvailable()) {
			
			$limit = ($this->currentPageNum - 1) * self::NUM_MAILS_PER_PAGE;
			foreach ($this->getAllItems() as $key => $item) {
				if ($limit > $key) continue;
				if ($key >= ($limit + self::NUM_MAILS_PER_PAGE)) break;  
				$this->currentItems[$key] = $item; 
			}
		}
		return $this->currentItems;
	}
	
	public function getNumPages(): int {
		if (!$this->isFilePathAvailable()) return 0;
		return ceil(($this->getNumItemsTotal() / self::NUM_MAILS_PER_PAGE));
	}
	
	public function getAttachment(int $itemIndex, int $attachmentIndex = null): ?CommonFile {
		$items = $this->getAllItems();
		if (!isset($items[$itemIndex]))	return null;
		$attachments = $items[$itemIndex]->getAttachments();
		$fsPath = new FsPath($attachments[$attachmentIndex]->getPath());
		return new CommonFile(new FsFileSource($fsPath), $fsPath->getFileName());
	}
	
	public function getMailFileNames(): array {
		$mailFileNames = array();
		foreach((array) self::requestMailLogDir($this->varStore)->getChildren('*.xml') as $mailXml) {
			$mailFileNames[MailArchiveBatchController::removeFileExtension($mailXml->getName())] = $mailXml->getName();
		}
		ksort($mailFileNames);
		return $mailFileNames;
	}

	public static function requestMailLogFile(VarStore $varStore, $fileName): FsPath {
		return new FsPath($varStore->requestFileFsPath(VarStore::CATEGORY_LOG, N2N::NS,
				AdminMailCenter::LOG_FOLDER, $fileName, true, false));
	}

	public static function requestMailLogDir(VarStore $varStore): FsPath {
		return new FsPath($varStore->requestDirFsPath(VarStore::CATEGORY_LOG, N2N::NS,
				AdminMailCenter::LOG_FOLDER, true));
	}
	
	private function getAllItems(): array {
		$parser = new SaxParser();
		$mailItemSaxHandler = new MailItemSaxHandler();
		$parser->parse($this->mailXmlFilePath, $mailItemSaxHandler);
		return array_reverse($mailItemSaxHandler->getItems());
	}
	
	private function isFilePathAvailable(): bool {
		return (null !== $this->mailXmlFilePath && $this->mailXmlFilePath->isFile());
	}
}
