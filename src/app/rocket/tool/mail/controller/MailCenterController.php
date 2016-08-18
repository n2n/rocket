<?php
namespace rocket\tool\mail\controller;

use n2n\http\ControllerAdapter;
use rocket\tool\mail\model\MailCenter;
use n2n\log4php\appender\nn6\AdminMailCenter;
use n2n\http\PageNotFoundException;
use n2n\io\InvalidPathException;
use n2n\N2N;
use n2n\core\DynamicTextCollection;

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
		$this->forward('tool\mail\view\mailCenter.html', array('mailCenter' => $mailCenter, 
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
	
	public function doAttachment($fileName, $mailIndex, $attachmentIndex, $attachmentFileName) {
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
		if (!$attachment->getPath()->isFile()) {
			$dtc = new DynamicTextCollection('rocket');
			N2N::getMessageContainer()->addInfo($dtc->translate('tool_mail_center_notification_attachment_deleted'));
			$this->redirectToReferer();
			return;
		} 
		$this->getResponse()->send($attachment);
	}
	
	public function doCreateArchive(array $contextCmds, array $cmds) {
		$controller = new MailArchiveBatchController();
		$contextCmds[] = array_shift($cmds);
		$controller->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
}