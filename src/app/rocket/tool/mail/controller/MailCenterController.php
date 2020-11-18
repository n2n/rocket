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
namespace rocket\tool\mail\controller;

use n2n\mail\Mail;
use n2n\mail\Transport;
use n2n\web\http\controller\ControllerAdapter;
use rocket\tool\mail\model\MailCenter;
use n2n\log4php\appender\nn6\AdminMailCenter;
use n2n\web\http\PageNotFoundException;
use n2n\io\InvalidPathException;

class MailCenterController extends ControllerAdapter {
	const ACTION_ARCHIVE = 'archive';
	const ACTION_ATTACHMENT = 'attachment';
	
	public function index($currentPageNum = null) {
		$mailCenter = $this->createMailCenter($currentPageNum);
		$this->forward('..\view\mailCenter.html', array('mailCenter' => $mailCenter,
				'currentFileName' => AdminMailCenter::DEFAULT_MAIL_FILE_NAME));
	}
	
	public function doArchive($fileName, $currentPageNum = null) {
		try {
			$mailXmlFilePath = MailCenter::requestMailLogFile($fileName);
		} catch (InvalidPathException $e) {
			throw new PageNotFoundException();
		}
		if ($mailXmlFilePath->getExtension() !== 'xml') {
			throw new PageNotFoundException();			
		}
		
		$mailCenter = new MailCenter($mailXmlFilePath);
		if (null !== $currentPageNum) {
			$mailCenter->setCurrentPageNum($currentPageNum);
		}
		
		$this->forward('tool\mail\view\mailCenter.html', array('mailCenter' => $mailCenter, 'currentFileName' => $fileName));
	}
	
	public function doAttachment($fileName, $mailIndex, $attachmentIndex) {
		try {
			$mailXmlFilePath = MailCenter::requestMailLogFile($fileName);
		} catch (InvalidPathException $e) {
			throw new PageNotFoundException();
		}
		if ($mailXmlFilePath->getExtension() !== 'xml') {
			throw new PageNotFoundException();
		}
		$mailCenter = new MailCenter($mailXmlFilePath);
		if (null === ($attachment = $mailCenter->getAttachment($mailIndex, $attachmentIndex))) {
			throw new PageNotFoundException();
		}
		if (!$attachment->getFileSource()->getFsPath()->isFile()) {
			throw new PageNotFoundException();
		}
		
		$this->sendFile($attachment);
	}

	public function doMails(int $currentPageNum = null) {

		$mail = new Mail('attachmenttest@asdf.ch', 'attachment test', 'attachment test', 'asdf@asdf.asdf');
		$mail->addFile('C:\Users\nikol\Desktop\test.jpg', 'test.jpg');
		$mail->addFile('C:\Users\nikol\Desktop\test2.jpg', 'test2.jpg');
		Transport::send($mail);

		$mailCenter = $this->createMailCenter($currentPageNum);
		$mailItems = $this->createMailsJsonArray($mailCenter->getCurrentItems());
		$mailItems = array_values($mailItems);
		$this->sendJson($mailItems);
	}

	public function doMailsPageCount() {
		$this->sendJson($this->createMailCenter()->getNumPages());
	}

	private function createMailCenter(int $currentPageNum = null) {
		$mailXmlFilePath = null;
		try {
			$mailXmlFilePath = MailCenter::requestMailLogFile(AdminMailCenter::DEFAULT_MAIL_FILE_NAME);
		} catch (InvalidPathException $e) { }

		$mailCenter = new MailCenter($mailXmlFilePath);

		if (null !== $currentPageNum) {
			$mailCenter->setCurrentPageNum($currentPageNum);
		}

		return $mailCenter;
	}

	private function createMailsJsonArray(array $mailItems) {
		foreach ($mailItems as $i => $mailItem) {
			if (empty($mailItem->getAttachments())) continue;
			foreach ($mailItem->getAttachments() as $attachmentI => $attachment) {
				$url = $this->getControllerPath()->chPathParts(array('attachment', $attachment->getName(), $i, $attachmentI))->toUrl();
				$attachment->setPath((string) $url);
			}
		}
		return $mailItems;
	}

}
