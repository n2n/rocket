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

namespace rocket\ui\gui\field\impl;

use n2n\util\type\ArgUtils;
use n2n\core\container\N2nContext;
use n2n\bind\build\impl\Bind;
use n2n\l10n\Message;
use rocket\ui\si\content\BackableSiField;
use n2n\util\ex\ExUtils;
use n2n\bind\mapper\impl\Mappers;
use n2n\l10n\N2nLocale;

abstract class InGuiFieldAdapter extends GuiFieldAdapter {

	/**
	 * @var Message[]
	 */
	private array $messageStrs = [];
//	/**
//	 * @var Message[]
//	 */
//	private array $prepareForSaveMessages = [];

	private mixed $internalValue = null;

//	private mixed $preparedValue = null;
//	private array $prepareForSaveMappers = [];

	protected function __construct(private BackableSiField $siField) {
		ArgUtils::assertTrue(!$this->siField->isReadOnly(), 'SiField must not be readOnly.');
		parent::__construct($this->siField);
	}

	function handleInput(mixed $value, N2nContext $n2nContext): bool {
		$bindTask = Bind::values($value)
				->map(...$this->createInputMappers($n2nContext))
				->toClosure(fn ($v) => $this->setInternalValue($v));

		$bindResult = ExUtils::try(fn () => $bindTask->exec($n2nContext));

		if (!$bindResult->isValid()) {
			$this->messageStrs = array_map(fn (Message $m) => $m->t($n2nContext->getN2nLocale()),
					$bindResult->getErrorMap()->getAllMessages());
			return false;
		}

		return $this->model?->handleInput($this->getInternalValue(), $n2nContext) ?? true;
	}

	function flush(N2nContext $n2nContext): void {
		$this->model?->save($n2nContext);
	}

	/**
	 * @param N2nContext $n2nContext
	 * @return Mappers[]
	 */
	protected abstract function createInputMappers(N2nContext $n2nContext): array;

	function getMessageStrs(): array {
		if (!empty($this->messageStrs)) {
			return $this->messageStrs;
//		} else if (!empty($this->prepareForSaveMessages)) {
//			return $this->prepareForSaveMessages;
		}

		return parent::getMessageStrs();
	}

	protected function setInternalValue(mixed $internalValue): void {
		$this->messageStrs = [];
		$this->internalValue = $internalValue;
	}

	protected function getInternalValue(): mixed {
		return $this->internalValue;
	}

//	function setPrepareForSaveMappers(Mapper ...$mappers): static {
//		$this->prepareForSaveMappers = $mappers;
//		return $this;
//	}
//
//	function setSaveCallback(\Closure $saveCallback): static {
//		$this->saveCallback = $saveCallback;
//		return $this;
//	}

//	function prepareForSave(N2nContext $n2nContext): bool {
//		if (empty($this->prepareForSaveMappers)) {
//			$this->preparedValue = $this->internalValue;
//			return true;
//		}
//
//		$bindTask = Bind::values($this->internalValue)->map(...$this->prepareForSaveMappers)
//				->toValue($this->internalValue);
//
//		try {
//			$bindResult = $bindTask->exec($n2nContext);
//		} catch (BindException $e) {
//			throw new IllegalStateException('Prepare for save Mappers for GuiField ' . get_class($this)
//					. ' failed:' . $e->getMessage(), previous: $e);
//		}
//
//		if ($bindResult->isValid()) {
//			$this->preparedValue = $bindResult->get();
//			$this->prepareForSaveMessages = [];
//			return true;
//		}
//
//		$this->prepareForSaveMessages = $bindResult->getErrorMap()->getAllMessages();
//		return false;
//	}

	function save(N2nContext $n2nContext): void {
		$this->model?->save($n2nContext);
	}

}