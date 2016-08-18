<?php
namespace rocket\tool\mail\model;

use rocket\tool\xml\ItemCountSaxHandler;
use rocket\tool\xml\MailItemSaxHandler;
use n2n\reflection\ArgumentUtils;
use n2n\io\fs\AbstractPath;
use rocket\tool\xml\SaxParser;
use n2n\N2N;
use n2n\core\VarStore;
use n2n\log4php\appender\nn6\AdminMailCenter;
use rocket\tool\mail\controller\MailArchiveBatchController;
use n2n\io\fs\File;

class MailCenter {
	const NUM_MAILS_PER_PAGE = 15;
	const ATTACHMENT_INDEX_DEFAULT = 'default';
	
	private $mailXmlFilePath;
	
	private $currentPageNum = 1;
	
	private $numItemsTotal = 0;
	private $currentItems;
	
	public function __construct(AbstractPath $mailXmlFilePath = null) {
		$this->mailXmlFilePath = $mailXmlFilePath;
	}
	
	public function getCurrentPageNum() {
		return $this->currentPageNum;
	}
	
	public function setCurrentPageNum($currentPageNum) {
		ArgumentUtils::assertTrue(is_numeric($currentPageNum) && $currentPageNum > 0 
				&& $currentPageNum <= $this->getNumPages());
		$this->currentPageNum = $currentPageNum;
	}
	
	public function getNumItemsTotal() {
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
	public function getCurrentItems() {
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
	
	public function getNumPages() {
		if (!$this->isFilePathAvailable()) return 0;
		return ceil(($this->getNumItemsTotal() / self::NUM_MAILS_PER_PAGE));
	}
	
	public function getAttachment($itemIndex, $attachmentIndex = null) {
		$items = $this->getAllItems();
		if (!isset($items[$itemIndex]))	return null;
		$attachments = $items[$itemIndex]->getAttachments();
		return new File($attachments[$attachmentIndex]->getPath());
	}
	
	public static function getMailFileNames() {
		$mailFileNames = array();
		foreach((array) self::requestMailLogDir()->getChildren('*.xml') as $mailXml) {
			$mailFileNames[MailArchiveBatchController::removeFileExtension($mailXml->getName())] = $mailXml->getName();
		}
		ksort($mailFileNames);
		return $mailFileNames;
	}
	
	public static function requestMailLogFile($fileName, $required = true) {
		return N2N::getVarStore()->requestFilePath(VarStore::CATEGORY_LOG, N2N::N2N_NAMESPACE,
				AdminMailCenter::LOG_FOLDER, $fileName, true, false, $required);
	}
	/**
	 * @return \n2n\io\fs\AbstractPath
	 */
	public static function requestMailLogDir() {
		return N2N::getVarStore()->requestDirectoryPath(VarStore::CATEGORY_LOG, N2N::N2N_NAMESPACE,
				AdminMailCenter::LOG_FOLDER, true);
	}
	
	private function getAllItems() {
		$parser = new SaxParser();
		$mailItemSaxHandler = new MailItemSaxHandler();
		$parser->parse($this->mailXmlFilePath, $mailItemSaxHandler);
		
		if (count($items = $mailItemSaxHandler->getItems()) > 0) {
			return array_reverse($items);
		}
 		
		return array();
	}
	
	private function isFilePathAvailable() {
		return (null !== $this->mailXmlFilePath && $this->mailXmlFilePath->isFile());
	}
}