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
namespace rocket\op\ei\manage\api;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamPost;
use rocket\op\ei\manage\ManageState;
use n2n\web\http\BadRequestException;
use rocket\ui\si\api\request\SiApiCall;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\web\http\StatusException;
use rocket\op\ei\manage\gui\EiGuiApiModel;
use rocket\ui\si\api\SiApi;
use rocket\ui\si\err\SiException;
use n2n\util\ex\ExUtils;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\api\GuiSiApiModel;

class ApiController extends ControllerAdapter {
//	const API_CONTROL_SECTION = 'execcontrol';
//	const API_FIELD_SECTION = 'callfield';
//	const API_GET_SECTION = 'get';
//	const API_VAL_SECTION = 'val';
//	const API_SORT_SECTION = 'sort';


	private EiFrame $eiFrame;


	function prepare(ManageState $manageState): void {
		ExUtils::try(fn () => $this->eiFrame = $manageState->peakEiFrame());
	}

	/**
	 * @param ParamPost $call
	 * @return void
	 * @throws BadRequestException
	 * @throws StatusException
	 */
	function index(ParamPost $call): void {
		try {
			$siApiCall = SiApiCall::parse($call->parseJson());
		} catch (CorruptedSiDataException $e) {
			throw new BadRequestException(previous: $e);
		}

		$siApi = new SiApi(new GuiSiApiModel(new EiGuiApiModel($this->eiFrame)));

		try {
			$siApiCallResponse = $siApi->handleCall($siApiCall, $this->getRequest()->getUploadDefinitions(),
					$this->getN2nContext());
		} catch (SiException $e) {
			throw new BadRequestException(previous: $e);
		}

		$this->sendJson($siApiCallResponse);
	}
	
//	static function getApiSections() {
//		return [self::API_CONTROL_SECTION, self::API_FIELD_SECTION, self::API_GET_SECTION, self::API_VAL_SECTION, self::API_SORT_SECTION];
//	}
//
//	private function parseApiControlCallId(Param $paramQuery) {
//		try {
//			return ApiControlCallId::parse($paramQuery->parseJson());
//		} catch (\InvalidArgumentException $e) {
//			throw new BadRequestException(null, null, $e);
//		}
//	}
//
//	private function parseApiFieldCallId(Param $paramQuery) {
//		try {
//			return ApiFieldCallId::parse($paramQuery->parseJson());
//		} catch (\InvalidArgumentException $e) {
//			throw new BadRequestException(null, null, $e);
//		}
//	}
//
//	/**
//	 * @param Param $paramQuery
//	 * @param bool $new
//	 * @return number
//	 */
//	private function parseViewMode(Param $paramQuery, bool $new) {
//		$httpData = $paramQuery->parseJsonToHttpData();
//		return ViewMode::determine($httpData->reqBool('bulky'), $httpData->reqBool('readOnly'), $new);
//	}
//
//	/**
//	 * @param Param $param
//	 * @return \rocket\ui\si\api\SiGetRequest
//	 *@throws BadRequestException
//	 */
//	private function parseGetRequest(Param $param) {
//		try {
//			return SiGetRequest::parse($param->parseJson());
//		} catch (\InvalidArgumentException $e) {
//			throw new BadRequestException(null, null, $e);
//		}
//	}
//
//	/**
//	 * @param Param $param
//	 * @return \rocket\ui\si\api\SiValRequest
//	 *@throws BadRequestException
//	 */
//	private function parseValRequest(Param $param) {
//		try {
//			return SiValRequest::parse($param->parseJson());
//		} catch (\InvalidArgumentException $e) {
//			throw new BadRequestException(null, null, $e);
//		}
//	}
//
//	function postDoGet(ParamBody $param) {
//		$siGetRequest = $this->parseGetRequest($param);
//		$siGetResponse = new SiGetResponse();
//
//		foreach ($siGetRequest->getInstructions() as $key => $instruction) {
//			$process = new GetInstructionProcess($instruction, $this->eiFrame);
//			$siGetResponse->putResult($key, $process->exec());
//		}
//
//		$this->sendJson($siGetResponse);
//	}
//
//	function postDoVal(ParamBody $param) {
//		$siValRequest = $this->parseValRequest($param);
//		$siValResponse = new SiValResponse();
//
//		foreach ($siValRequest->getInstructions() as $key => $instruction) {
//			$process = new ValInstructionProcess($instruction, $this->eiFrame);
//			$siValResponse->putResult($key, $process->exec());
//		}
//
//		$this->sendJson($siValResponse);
//	}
//
//	/**
//	 * @throws StatusException
//	 */
//	function doHandle(ParamPost $call): void {
//
//
//
//	}
//
//	function doExecControl(ParamPost $style, ParamPost $apiCallId, ParamPost $entryInputMaps = null): void {
//		$siApiCallId = $this->parseApiControlCallId($apiCallId);
//
//		$callProcess = new ApiControlProcess($this->eiFrame);
//		$viewMode = null;
//		if (null !== ($pid = $siApiCallId->getPid())) {
//			$eiEntry = $callProcess->determineEiEntry($pid);
//			$viewMode = $this->parseViewMode($style, false);
//			$callProcess->determineEiGuiMaskDeclaration($viewMode, $eiEntry->getEiType()->getId());
//		} else if (null !== ($newEiTypeType = $siApiCallId->getNewEiTypeId())) {
//			$callProcess->determineNewEiEntry($newEiTypeType);
//			$viewMode = $this->parseViewMode($style, true);
//			$callProcess->determineEiGuiMaskDeclaration($viewMode, $newEiTypeType);
//		} else {
//			$viewMode = $this->parseViewMode($style, false);
//			$callProcess->determineEiGuiMaskDeclaration($viewMode, $siApiCallId->getEiTypeId());
//		}
//
//		$callProcess->determineGuiControl($siApiCallId->getGuiControlPath());
//
//		if ($entryInputMaps !== null
//				&& null !== ($siInputError = $callProcess->handleInput($entryInputMaps->parseJson()))) {
//			$this->sendJson(SiCallResult::fromInputError($siInputError));
//			return;
//		}
//
//		$this->sendJson(SiCallResult::fromCallResponse($callProcess->callGuiControl(),
//				($entryInputMaps !== null ? $callProcess->createSiInputResult() : null)));
//	}
//
//	function doCallField(ParamPost $style, ParamPost $apiCallId, ParamPost $data): void {
//		$siApiCallId = $this->parseApiFieldCallId($apiCallId);
//
//		$callProcess = new ApiControlProcess($this->eiFrame);
//		$viewMode = null;
//		if (null !== ($pid = $siApiCallId->getPid())) {
//			$callProcess->determineEiEntry($pid);
//			$viewMode = $this->parseViewMode($style, false);
//		} else {
//			$callProcess->determineNewEiEntry($siApiCallId->getEiTypeId());
//			$viewMode = $this->parseViewMode($style, true);
//		}
//
//		$callProcess->determineEiGuiMaskDeclaration($viewMode, $siApiCallId->getEiTypeId());
//		$callProcess->determineGuiField($siApiCallId->getDefPropPath());
//
//		$this->sendJson(['data' => $callProcess->callSiField($data->parseJson(), $this->getRequest()->getUploadDefinitions()) ]);
//	}
//
//	function postDoSort(ParamBody $paramBody) {
//		$httpData = $paramBody->parseJsonToHttpData();
//
//		$sortProcess = new ApiSortProcess($this->eiFrame);
//
//		$sortProcess->determineEiObjects($httpData->reqArray('ids', 'string'));
//
//		if (null !== ($afterId = $httpData->optString('afterId'))) {
//			$this->sendJson($sortProcess->insertAfter($afterId));
//			return;
//		}
//
//		if (null !== ($beforeId = $httpData->optString('beforeId'))) {
//			$this->sendJson($sortProcess->insertBefore($beforeId));
//			return;
//		}
//
//		if (null !== ($parentId = $httpData->optString('parentId'))) {
//			$this->sendJson($sortProcess->insertAsChildOf($parentId));
//			return;
//		}
//
//		throw new BadRequestException();
//	}
}
