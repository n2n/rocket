<?php

namespace rocket\ui\gui\field\impl;

use rocket\ui\gui\field\GuiField;
use n2n\util\type\ArgUtils;
use n2n\core\container\N2nContext;
use n2n\bind\build\impl\Bind;
use rocket\ui\si\content\SiFieldModel;
use n2n\l10n\Message;
use rocket\ui\si\content\BackableSiField;
use n2n\util\ex\ExUtils;
use rocket\ui\si\content\SiField;
use n2n\bind\mapper\impl\Mappers;
use n2n\bind\mapper\Mapper;
use n2n\util\magic\impl\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use n2n\bind\err\BindException;
use n2n\util\ex\IllegalStateException;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\si\input\SiFieldInput;

abstract class InGuiFieldAdapter extends GuiFieldAdapter {

	/**
	 * @var Message[]
	 */
	private array $messages = [];
	/**
	 * @var Message[]
	 */
	private array $prepareForSaveMessages = [];

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
			$this->messages = $bindResult->getErrorMap()->getAllMessages();
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

	function getMessages(): array {
		if (!empty($this->messages)) {
			return $this->messages;
		} else if (!empty($this->prepareForSaveMessages)) {
			return $this->prepareForSaveMessages;
		}

		return parent::getMessages();
	}

	protected function setInternalValue(mixed $internalValue): void {
		$this->messages = [];
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