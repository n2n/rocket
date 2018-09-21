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

use n2n\core\container\N2nContext;
use n2n\web\http\controller\ControllerAdapter;
use rocket\tool\mail\model\MailCenter;
use n2n\log4php\appender\nn6\AdminMailCenter;
use n2n\web\http\PageNotFoundException;
use n2n\io\InvalidPathException;
use n2n\core\N2N;
use n2n\l10n\DynamicTextCollection;

class MailCenterController extends ControllerAdapter {
	const ACTION_ARCHIVE = 'archive';
	const ACTION_ATTACHMENT = 'attachment';
	
	public function index($currentPageNum = null) {
		$mailXmlFilePath = null;
		try {
			$mailXmlFilePath = MailCenter::requestMailLogFile(AdminMailCenter::DEFAULT_MAIL_FILE_NAME);
		} catch (InvalidPathException $e) { }
		
		$mailCenter = new MailCenter($mailXmlFilePath);
		if (null !== $currentPageNum) {
			$mailCenter->setCurrentPageNum($currentPageNum);
		}
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
	
	public function doAttachment($fileName, $mailIndex, $attachmentIndex, $attachmentFileName, N2nContext $n2nContext) {
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
			$dtc = new DynamicTextCollection('rocket', $n2nContext->getN2nLocale());
			N2N::getMessageContainer()->addInfo($dtc->translate('tool_mail_center_notification_attachment_deleted'));
			$this->redirectToReferer();
			return;
		} 
		
		$this->sendFile($attachment);
	}
	
}
