<?php
namespace rocket\tool\xml;

use n2n\util\DateUtils;
use n2n\log4php\appender\nn6\AdminMailCenter;

class MailItemSaxHandler implements SaxHandler {
	
	private $itemCounter = 0;
	private $items;
	
	private $limit;
	private $num;
	/**
	 * @var \rocket\tool\xml\MailItem
	 */
	private $currentItem = null;
	private $currentAttachmentItem = null;
	private $level = 0;
	private $currentTagName;
	/**
	 *
	 * @param int $limit
	 * @param int $num
	 * @param string $selectorSeverity
	*/
	public function __construct($limit = null, $num = null) {
		$this->limit = $limit;
		$this->num = $num;
	}

	public function startElement($tagName, array $attributes) {
		$this->currentTagName = null;
		$this->level++;
		if ($this->level == 2 && $tagName == 'item') {
			$this->itemCounter++;
			
			if (!isset($attributes['datetime'])) return;

			if (is_numeric($this->num) && $this->num <= sizeof($this->items)) {
				return;
			}
			if (is_numeric($this->limit) && $this->itemCounter <= $this->limit) {
				return;
			}
				
			$this->currentItem = new MailItem(DateUtils::createDateTime($attributes['datetime']));
		} else if (isset($this->currentItem) && $this->level > 2) {
			$this->currentTagName = $tagName;
			if ($tagName == AdminMailCenter::TAG_NAME_ATTACHMENT) {
				$this->currentAttachmentItem = new MailAttachmentItem();
				$this->currentItem->addAttachment($this->currentAttachmentItem);
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see NN6XmlSaxHandler::cdata()
	 */
	public function cdata($cdata) {
		if (null === $this->currentTagName) return;
		switch ($this->currentTagName) {
			case AdminMailCenter::TAG_NAME_MESSAGE:
				$this->currentItem->setMessage($cdata);
				break;
			case AdminMailCenter::TAG_NAME_SUBJECT:
				$this->currentItem->setSubject($cdata);
				break;
			case AdminMailCenter::TAG_NAME_TO:
				$this->currentItem->setTo($cdata);
				break;
			case AdminMailCenter::TAG_NAME_FROM:
				$this->currentItem->setFrom($cdata);
				break;
			case AdminMailCenter::TAG_NAME_CC:
				$this->currentItem->setCc($cdata);
				break;
			case AdminMailCenter::TAG_NAME_BCC:
				$this->currentItem->setBcc($cdata);
				break;
			case AdminMailCenter::TAG_NAME_REPLY_TO:
				$this->currentItem->setReplyTo($cdata);
				break;
			case AdminMailCenter::TAG_NAME_NAME:
				$cdata = preg_replace('/\s/', '', $cdata);
				$this->currentAttachmentItem->setName($cdata);
				break;
			case AdminMailCenter::TAG_NAME_PATH:
				$cdata = preg_replace('/\s/', '', $cdata);
				$this->currentAttachmentItem->setPath($cdata);
				break;
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see NN6XmlSaxHandler::endElement()
	 */
	public function endElement($tag) {
		$this->level--;
		if (!($this->level == 1 && $tag == 'item')) return;
		$this->items[] = $this->currentItem;
		$this->currentItem = null;
	}
	/**
	 *
	 * @return \rocket\tool\xml\MailItem[]
	 */
	public function getItems() {
		return $this->items;
	}

	private function areArrayKeysGenerated(array $arr) {
		foreach (array_keys($arr) as $key => $value) {
			if (!($key === $value)) return false;
		}
		return true;
	}
}