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
namespace rocket\tool\xml;

use n2n\util\DateUtils;
use n2n\log4php\appender\nn6\AdminMailCenter;

class MailItemSaxHandler implements SaxHandler {
	
	private int $itemCounter = 0;
	private array $items = [];
	
	private int $limit;
	private int $num;
	private ?MailItem $currentItem = null;
	private ?MailAttachmentItem $currentAttachmentItem = null;
	private int $level = 0;
	private ?string $currentTagName;

	public function __construct(int $limit = null, int $num = null) {
		$this->limit = $limit;
		$this->num = $num;
	}

	public function startElement(string $tagName, array $attributes): void {
		$this->currentTagName = null;
		$this->level++;
		if ($this->level == 2 && $tagName == 'item') {
			$this->itemCounter++;
			
			if (!isset($attributes['datetime'])) return;

			if ($this->num <= sizeof($this->items)) {
				return;
			}
			if ($this->itemCounter <= $this->limit) {
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
	 * {@inheritDoc}
	 * @see SaxHandler::cdata
	 */
	public function cdata(string $cdata): void {
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
	 * {@inheritDoc}
	 * @see SaxHandler::endElement
	 */
	public function endElement(string $tagName): void {
		$this->level--;
		if (!($this->level == 1 && $tagName == 'item')) return;
		$this->items[] = $this->currentItem;
		$this->currentItem = null;
	}
	
	/**
	 * @return MailItem[]
	 */
	public function getItems(): array {
		return $this->items;
	}

	private function areArrayKeysGenerated(array $arr): bool {
		foreach (array_keys($arr) as $key => $value) {
			if (!($key === $value)) return false;
		}
		return true;
	}
}
