<?php
///*
// * Copyright (c) 2012-2016, Hofmänner New Media.
// * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
// *
// * This file is part of the n2n module ROCKET.
// *
// * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
// * GNU Lesser General Public License as published by the Free Software Foundation, either
// * version 2.1 of the License, or (at your option) any later version.
// *
// * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
// * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
// *
// * The following people participated in this project:
// *
// * Andreas von Burg...........:	Architect, Lead Developer, Concept
// * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
// * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
// */
//namespace rocket\impl\ei\component\prop\adapter\gui;
//
//use n2n\util\ex\IllegalStateException;
//use rocket\ui\gui\field\GuiField;
//use rocket\ui\si\content\SiField;
//use rocket\ui\gui\field\GuiFieldMap;
//use n2n\reflection\magic\MagicMethodInvoker;
//use n2n\util\magic\TaskResult;
//use n2n\util\ex\ExUtils;
//use rocket\ui\si\content\SiFieldModel;
//use n2n\util\type\TypeConstraints;
//use n2n\l10n\Message;
//use n2n\core\container\N2nContext;
//
//class ClosureGuiField implements GuiField, SiFieldModel {
//
//	private ?MagicMethodInvoker $messagesMmi = null;
//	private ?MagicMethodInvoker $readMmi = null;
//
//	private ?MagicMethodInvoker $saveMmi = null;
//	private mixed $lastReadSiFieldValue = null;
//	/**
//	 * @var array<Message>
//	 */
//	private array $lastReadMessages = [];
//
//
//	public function __construct(private SiField $siField, ?\Closure $messagesClosure,
//			?\Closure $readClosure, ?\Closure $saveClosure) {
//
//		if ($siField->isReadOnly() && $saveClosure !== null) {
//			throw new \InvalidArgumentException('SiField is not writable. No save closure allowed.');
//		}
//
//		$this->siField->setModel($this);
//
//		if ($messagesClosure !== null) {
//			$this->messagesMmi = new MagicMethodInvoker();
//			$this->messagesMmi->setClassParamObject(SiField::class, $siField);
//			$this->messagesMmi->setClassParamObject(get_class($siField), $siField);
//			$this->messagesMmi->setReturnTypeConstraint(TypeConstraints::array(false, 'string'));
//			$this->messagesMmi->setMethod(ExUtils::try(fn () => new \ReflectionFunction($messagesClosure)));
//		}
//
//		if ($readClosure !== null) {
//			$this->readMmi = new MagicMethodInvoker();
//			$this->readMmi->setClassParamObject(SiField::class, $siField);
//			$this->readMmi->setClassParamObject(get_class($siField), $siField);
//			$this->readMmi->setReturnTypeConstraint(TypeConstraints::namedType(TaskResult::class));
//			$this->readMmi->setMethod(ExUtils::try(fn () => new \ReflectionFunction($readClosure)));
//		}
//
//		if ($saveClosure !== null) {
//			$this->saveMmi = new MagicMethodInvoker();
//			$this->saveMmi->setClassParamObject(SiField::class, $siField);
//			$this->saveMmi->setClassParamObject(get_class($siField), $siField);
//			$this->saveMmi->setMethod(ExUtils::try(fn () => new \ReflectionFunction($saveClosure)));
//		}
//	}
//
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\ui\gui\field\GuiField::getSiField()
//	 */
//	function getSiField(): SiField {
//		return $this->siField;
//	}
//
//	function handleInput(mixed $value, N2nContext $n2nContext): bool {
//		$this->lastReadSiFieldValue = null;
//		$this->lastReadMessages = [];
//
//		if ($this->readMmi === null) {
//			return true;
//		}
//
//		$taskResult = $this->readMmi->invoke();
//		if (!$taskResult->hasErrors()) {
//			$this->lastReadSiFieldValue = $taskResult->get();
//			return true;
//		}
//
//		$this->lastReadMessages = $taskResult->getErrorMap()->getAllMessages();
//		return false;
//	}
//
//	function getMessageStrs(): array {
//		$messageStrs = [];
//		foreach ($this->lastReadMessages as $message) {
//			$messageStrs[] = $message->t(\n2n\l10n\N2nLocale::getAdmin());
//		}
//
//		if (empty($messageStrs) && $this->messagesMmi !== null) {
//			$messageStrs = $this->messagesMmi->invoke();
//		}
//
//		return $messageStrs;
//	}
//
////	function prepareForSave(N2nContext $n2nContext): bool {
////		return true;
////	}
//
//	function getValue(): mixed {
//		return $this->lastReadSiFieldValue;
//	}
//
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\ui\gui\field\GuiField::save()
//	 */
//	public function save(N2nContext $n2nContext): void {
//		if ($this->siField->isReadOnly()) {
//			throw new IllegalStateException('Can not save ready only GuiField');
//		}
//
//		$this->saveMmi?->invoke(null, null, [$this->lastReadSiFieldValue]);
//	}
//
//// 	function getContextSiFields(): array {
//// 		return [];
//// 	}
//
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\ui\gui\field\GuiField::getForkGuiFieldMap()
//	 */
//	function getForkGuiFieldMap(): ?GuiFieldMap {
//		return null;
//	}
//}
