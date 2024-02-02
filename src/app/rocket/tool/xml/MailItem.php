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

class MailItem {
	
	private \DateTime $dateTime;
	private string $to = '';
	private string $from = '';
	private string $cc = '';
	private string $bcc = '';
	private string $replyTo = '';
	private array $attachments = [];
	private string $message = '';
	private string $subject = '';

	public function __construct(\DateTime $dateTime) {
		$this->dateTime = $dateTime;
	}
	
	public function getDateTime(): \DateTime {
		return $this->dateTime;
	}
	public function getTo(): string {
		return $this->to;
	}

	public function setTo(string $to): void {
		$this->to .= $to;
	}

	public function getFrom(): string {
		return $this->from;
	}

	public function setFrom(string $from): void {
		$this->from .= $from;
	}

	public function getCc(): string {
		return $this->cc;
	}

	public function setCc($cc): void {
		$this->cc .= $cc;
	}

	public function getBcc(): string {
		return $this->bcc;
	}

	public function setBcc(string $bcc): void {
		$this->bcc .= $bcc;
	}

	public function getReplyTo(): string {
		return $this->replyTo;
	}

	public function setReplyTo(string $replyTo): void {
		$this->replyTo .= $replyTo;
	}
	
	public function hasReplyTo(): bool {
		return (bool) trim($this->replyTo);
	}

	public function getAttachments(): array {
		return $this->attachments;
	}

	public function setAttachments(array $attachments) {
		$this->attachments = $attachments;
	}

	public function setDateTime(\DateTime $dateTime): void {
		$this->dateTime = $dateTime;
	}

	public function addAttachment(MailAttachmentItem $attachment): void {
		$this->attachments[] = $attachment;
	}
	
	public function getMessage(): string {
		return $this->message;
	}

	public function setMessage(string $message): void {
		$this->message .= $message;
	}

	public function getSubject(): string {
		return $this->subject;
	}

	public function setSubject(string $subject): void {
		$this->subject .= $subject;
	}
	
	public function hasAttachments(): bool {
		return (count($this->attachments) > 0);
	}
}
