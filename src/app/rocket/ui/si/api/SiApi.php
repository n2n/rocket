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
namespace rocket\ui\si\api;

use n2n\core\container\N2nContext;
use rocket\ui\si\err\UnknownSiElementException;
use rocket\ui\si\api\response\SiInputResult;
use rocket\ui\si\api\request\SiInput;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\api\response\SiFieldCallResponse;
use n2n\web\http\UploadDefinition;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\api\request\SiValRequest;
use rocket\ui\si\api\request\SiApiCall;
use rocket\ui\si\api\response\SiApiCallResponse;
use rocket\ui\si\api\response\SiGetResponse;
use rocket\ui\si\api\request\SiGetRequest;
use rocket\ui\si\api\response\SiGetInstructionResult;
use rocket\ui\si\meta\SiMask;
use rocket\ui\si\api\response\SiValResponse;
use rocket\ui\si\api\response\SiValInstructionResult;
use rocket\ui\si\api\response\SiValGetInstructionResult;
use rocket\ui\si\api\request\SiSortCall;
use rocket\ui\si\api\response\SiCallResponse;

class SiApi {

	function __construct(private SiApiModel $model) {

	}

	/**
	 * @param UploadDefinition[] $uploadDefinitions
	 * @throws CorruptedSiDataException
	 * @throws UnknownSiElementException
	 */
	function handleCall(SiApiCall $call, array $uploadDefinitions, N2nContext $n2nContext): SiApiCallResponse {
		$apiCallResponse = new SiApiCallResponse();

		$inputResult = null;
		if (null !== ($input = $call->getInput())) {
			$inputResult = $this->handleInput($input);
			if (!$inputResult->isValid()) {
				$apiCallResponse->setInputResult($inputResult);
				return $apiCallResponse;
			}
		}

		if (null !== ($controlCall = $call->getControlCall())) {
			$apiCallResponse->setCallResponse($this->handleControlCall($controlCall, $n2nContext));
		}

		if (null !== ($fieldCall = $call->getFieldCall())) {
			$apiCallResponse->setFieldCallResponse($this->handleFieldCall($fieldCall, $uploadDefinitions, $n2nContext));
		}

		if (null !== ($getRequest = $call->getGetRequest())) {
			$apiCallResponse->setGetResponse($this->handleGetRequest($getRequest));
		}

		if (null !== ($valRequest = $call->getValRequest())) {
			$apiCallResponse->setValResponse($this->handleValRequest($valRequest));
		}

		if (null !== ($asfas = $call->getSortCall()))
	}

	/**
	 * @throws UnknownSiElementException
	 * @throws CorruptedSiDataException
	 */
	private function handleInput(SiInput $siInput): SiInputResult {
		$valueBoundaries = [];
		$valid = true;
		foreach ($siInput->getValueBoundaryInputs() as $valueBoundaryInput) {
			$entryInput = $valueBoundaryInput->getEntryInput();
			$valueBoundaries[] = $valueBoundary = $this->model
					->lookupSiValueBoundary($valueBoundaryInput->getSelectedMaskId(), $entryInput->getEntryId(), null);
			$entryInputValid = $valueBoundary->handleInput($valueBoundaryInput);
			if (!$entryInputValid) {
				$valid = false;
			}

			$valueBoundaries[] = $valueBoundary;
		}

		return new SiInputResult($valueBoundaries, $valid);
	}

	/**
	 * @throws UnknownSiElementException
	 */
	private function handleControlCall(SiControlCall $controlCall, N2nContext $n2nContext): SiCallResponse {

		$entryId = $controlCall->getEntryId();
		if ($entryId !== null) {
			return $this->model->lookupSiEntryControl($controlCall->getMaskId(), $entryId,
					$controlCall->getControlName())->handleCall($n2nContext);
		}

		return $this->model->lookupSiMaskControl($controlCall->getMaskId(), $controlCall->getControlName())
				->handleCall($n2nContext);
	}


	/**
	 * @throws UnknownSiElementException
	 * @throws CorruptedSiDataException
	 */
	private function handleFieldCall(SiFieldCall $fieldCall, array $uploadDefinitions, N2nContext $n2nContext): SiFieldCallResponse {
		$entryId = $fieldCall->getEntryId();
		$data = $fieldCall->getData();
//		if ($entryId !== null) {
//			return new SiFieldCallResponse(
//					$this->model->lookupSiField($fieldCall->getMaskId(), $entryId, $fieldCall->getFieldName())
//							->handleCall($data, $uploadDefinitions, $n2nContext);
//		}

		return new SiFieldCallResponse($this->model->lookupSiField($fieldCall->getMaskId(), $fieldCall->getEntryId(), $fieldCall->getFieldName())
				->handleCall($data, $uploadDefinitions, $n2nContext));
	}

	/**
	 * @throws UnknownSiElementException
	 */
	private function handleGetRequest(SiGetRequest $getRequest): SiGetResponse {
		$siGetResponse = new SiGetResponse();

		foreach ($getRequest->getInstructions() as $key => $getInstruction) {
			$siGetResult = new SiGetInstructionResult();

			$maskId = $getInstruction->getMaskId();
//			$allowedMaskIds = $getInstruction->getAllowedMaskIds();
			$allowedFieldNames = $getInstruction->getAllowedFieldNames();

			if (null !== ($partialContentInstruction = $getInstruction->getPartialContentInstruction())) {
				$siPartialContent = $this->model->lookupSiPartialContent($maskId, $partialContentInstruction->getFrom(),
						$partialContentInstruction->getNum(), $partialContentInstruction->getQuickSearchStr(),
						$allowedFieldNames);

				$siGetResult->setPartialContent($siPartialContent);
			}

			if (null !== ($entryId = $getInstruction->getEntryId())) {
				$siGetResult->setValueBoundary($this->model->lookupSiValueBoundary($maskId, $entryId, $allowedFieldNames));
			}

			if ($getInstruction->isNewEntryRequested()) {
				$siGetResult->setValueBoundary($this->model->lookupSiValueBoundary($maskId, null, $allowedFieldNames));
			}

			if ($getInstruction->isDeclarationRequested()) {
				$masks = $this->lookupMasksOf($siGetResult->getAllValueBoundaries());
				$siGetResult->setDeclaration(new SiDeclaration($masks));
			}

			$siGetResponse->putInstructionResult($key, $siGetResult);
		}

		return $siGetResponse;
	}


	/**
	 * @param SiValueBoundary[] $valueBoundaries
	 * @return SiMask[]
	 * @throws UnknownSiElementException
	 */
	private function lookupMasksOf(array $valueBoundaries): array {
		$maskIds = [];
		foreach ($valueBoundaries as $valueBoundary) {
			foreach ($valueBoundary->getMaskIds() as $maskId) {
				$maskIds[$maskId] = $maskId;
			}
		}

		$masks = [];
		foreach ($maskIds as $maskId) {
			$masks[] = $this->model->lookupSiMask($maskId);
		}
		return $masks;
	}

	/**
	 * @throws CorruptedSiDataException
	 * @throws UnknownSiElementException
	 */
	private function handleValRequest(SiValRequest $valRequest, N2nContext $n2nContext): SiValResponse {
		$valResponse = new SiValResponse();

		foreach ($valRequest->getInstructions() as $key => $valInstruction) {
			$valueBoundaryInput = $valInstruction->getValueBoundaryInput();

			$valueBoundary = $this->model->lookupSiValueBoundary($valueBoundaryInput->getSelectedMaskId(),
					$valueBoundaryInput->getEntryInput()->getEntryId(), null);

			$valInstructionResult = new SiValInstructionResult($valueBoundary->handleInput($valueBoundaryInput, $n2nContext));

			foreach ($valInstruction->getGetInstructions() as $getInstruction) {
				$valGetInstructionResult = new SiValGetInstructionResult();
				$copyValueBoundary = $this->model->copySiValueBoundary($valueBoundary, $getInstruction->getMaskId());
				$valGetInstructionResult->setValueBoundary($copyValueBoundary);

				if ($getInstruction->isDeclarationRequested()) {
					$masks = $this->lookupMasksOf([$copyValueBoundary]);
					$valGetInstructionResult->setDeclaration(new SiDeclaration($masks));
				}

				$valInstructionResult->putGetResult($key, $valGetInstructionResult);
			}

			$valResponse->putInstructionResult($key, $valInstructionResult);
		}

		return $valResponse;
	}

	function handleSortCall(SiSortCall $sortCall): SiCallResponse {
		$maskId = $sortCall->getMaskId();
		$entryIds = $sortCall->getEntryIds();

		if (null !== ($afterEntryId = $sortCall->getAfterEntryId())) {
			return $this->model->insertSiEntriesAfter($maskId, $entryIds, $afterEntryId);
		}

		if (null !== ($beforeEntryId = $sortCall->getBeforeEntryId())) {
			return $this->model->insertSiEntriesBefore($maskId, $entryIds, $beforeEntryId);
		}

		if (null !== ($parentEntryId = $sortCall->getParentEntryId())) {
			return $this->model->insertSiEntriesAsChildren($maskId, $entryIds, $parentEntryId);
		}

		return new SiCallResponse();
	}
}