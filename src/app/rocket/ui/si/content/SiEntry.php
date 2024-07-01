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
namespace rocket\ui\si\content;

use rocket\ui\si\control\SiControl;
use rocket\ui\si\SiPayloadFactory;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\l10n\Message;
use n2n\core\container\N2nContext;
use n2n\util\type\ArgUtils;
use rocket\ui\si\api\request\SiEntryInput;

class SiEntry implements \JsonSerializable {

	/**
	 * @var string|null
	 */
	private ?string $idName = null;
	/**
	 * @var SiField[] $fields
	 */
	private array $fields = [];
// 	/**
// 	 * @var SiField[] $contextFields
// 	 */
// 	private $contextFields = [];
	/**
	 * @var SiControl[] $controls
	 */	
	private array $controls = [];

	/**
	 * @var Message[]
	 */
	private array $messages = [];

	private ?SiEntryModel $model = null;

	function __construct(private SiEntryQualifier $entryQualifier) {
	}

	function getQualifier(): SiEntryQualifier {
		return $this->entryQualifier;
	}

	function setModel(SiEntryModel $model): static {
		$this->model = $model;
		return $this;
	}

	/**
	 * @return SiField[]
	 */
	function getFields() {
		return $this->fields;
	}

	/**
	 * @param SiField[] $fields key is propId 
	 */
	function setFields(array $fields) {
		$this->fields = $fields;
		return $this;
	}

	function putField(string $nName, SiField $field): static {
		$this->fields[$nName] = $field;
		return $this;
	}
	
// 	/**
// 	 * @param string $id
// 	 * @param SiField[] $contextSiFields
// 	 * @return \rocket\si\content\SiEntry
// 	 */
// 	function putContextFields(string $id, array $contextSiFields) {
// 		if (empty($contextSiFields)) {
// 			unset($this->contextFields[$id]);
// 			return;
// 		}
		
// 		ArgUtils::valArray($contextSiFields, SiField::class);
// 		$this->contextFields[$id] = $contextSiFields;
// 		return $this;
// 	}
	
	/**
	 * @return SiControl[] 
	 */
	function getControls(): array {
		return $this->controls;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return SiEntry
	 */
	function setControls(array $controls): static {
		$this->controls = $controls;
		return $this;
	}

	function putControl(string $id, SiControl $control): static {
		$this->controls[$id] = $control;
		return $this;
	}

	function setMessages(array $messages): static {
		$this->messages = $messages;
		return $this;
	}

	function getMessages(): array {
		return $this->messages;
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	function handleEntryInput(SiEntryInput $entryInput, N2nContext $n2nContext): bool {
		$valid = true;
		$fields = [];
		foreach ($this->fields as $fieldName => $field) {
			if ($field->isReadOnly() || !$entryInput->containsFieldName($fieldName)) {
				continue;
			}

			try {
				if (!$field->handleInput($entryInput->getFieldInput($fieldName)->getData(), $n2nContext)) {
					$valid = false;
				}

				$fields[] = $field;
			} catch (\InvalidArgumentException|AttributesException $e) {
				throw new CorruptedSiDataException($e->getMessage(), previous: $e);
			}
		}

		if (!$valid) {
			return false;
		}

		foreach ($fields as $field) {
			$field->flush($n2nContext);
		}

		return $this->model?->handleInput($n2nContext) ?? true;
	}
	
	function jsonSerialize(): mixed {
		$fieldsArr = SiPayloadFactory::createDataFromFields($this->fields);
		$externalMessages = $this->model?->getMessages() ?? [];
		ArgUtils::valArrayReturn($externalMessages, $this->model, 'getMessages', 'string');

		return [
			'qualifier' => $this->entryQualifier,
			'fieldMap' => $fieldsArr,
			'controls' => SiPayloadFactory::createDataFromControls($this->controls),
			'messages' => [...$this->messages, ...$externalMessages]
		];
	}

}
