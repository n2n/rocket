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
namespace rocket\ei\manage\api;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamPost;
use rocket\ei\manage\ManageState;
use n2n\web\http\BadRequestException;
use n2n\web\http\controller\Param;
use n2n\web\http\controller\ParamBody;
use rocket\si\api\SiGetRequest;
use rocket\si\api\SiGetResponse;
use rocket\si\api\SiValRequest;
use rocket\si\api\SiValResponse;

class ApiController extends ControllerAdapter {
	private $eiFrame;
	
	function prepare(ManageState $manageState) {
		$this->eiFrame = $manageState->peakEiFrame();
	}
	
	function index() {
		echo 'very apisch';
	}

	private function parseApiControlCallId(Param $paramQuery) {
		try {
			return ApiControlCallId::parse($paramQuery->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	private function parseApiFieldCallId(Param $paramQuery) {
		try {
			return ApiFieldCallId::parse($paramQuery->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	/**
	 * @param Param $param
	 * @throws BadRequestException
	 * @return \rocket\si\api\SiGetRequest
	 */
	private function parseGetRequest(Param $param) {
		try {
			return SiGetRequest::createFromData($param->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	/**
	 * @param Param $param
	 * @throws BadRequestException
	 * @return \rocket\si\api\SiValRequest
	 */
	private function parseValRequest(Param $param) {
		try {
			return SiValRequest::createFromData($param->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	function postDoGet(ParamBody $param) {
		$siGetRequest = $this->parseGetRequest($param);
		$siGetResponse = new SiGetResponse();
		
		foreach ($siGetRequest->getInstructions() as $key => $instruction) {
			$process = new GetInstructionProcess($instruction, $this->eiFrame);
			$siGetResponse->putResult($key, $process->exec());
		}
		
		$this->sendJson($siGetResponse);
	}

	function postDoVal(ParamBody $param) {
		$siValRequest = $this->parseValRequest($param);
		$siValResponse = new SiValResponse();
		
		foreach ($siValRequest->getInstructions() as $key => $instruction) {
			$process = new ValInstructionProcess($instruction, $this->eiFrame);
			$siValResponse->putResult($key, $process->exec());
		}
		
		$this->sendJson($siValResponse);
	}
	
	function doExecControl(ParamPost $apiCallId, ParamPost $entryInputMaps = null) {
		$siApiCallId = $this->parseApiControlCallId($apiCallId);
		
		$callProcess = new ApiControlProcess($this->eiFrame);
		
		if (null !== ($pid = $siApiCallId->getPid())) {
			$callProcess->determineEiEntry($pid);
		} else if (null !== ($newEiTypeType = $siApiCallId->getNewEiTypeId())) {
			$callProcess->determineNewEiEntry($newEiTypeType);
		}
			
		$callProcess->setupEiGuiFrame($siApiCallId->getViewMode(), $siApiCallId->getEiTypeId());
		$callProcess->determineGuiControl($siApiCallId->getGuiControlPath());
		
		if ($entryInputMaps !== null
				&& null !== ($siResult = $callProcess->handleInput($entryInputMaps->parseJson()))) {
			$this->sendJson($siResult);
			return;
		}
		
		$this->sendJson($callProcess->callGuiControl());
	}
	
	function doCallField(ParamPost $apiCallId, ParamPost $data) {
		$siApiCallId = $this->parseApiFieldCallId($apiCallId);
		
		$callProcess = new ApiControlProcess($this->eiFrame);
		if (null !== ($pid = $siApiCallId->getPid())) {
			$callProcess->determineEiEntry($pid);
		} else {
			$callProcess->determineNewEiEntry($siApiCallId->getEiTypeId());
		}
		
		$callProcess->setupEiGuiFrame($siApiCallId->getViewMode(), $siApiCallId->getEiTypeId());
		$callProcess->determineGuiField($siApiCallId->getDefPropPath());
		
		$this->sendJson(['data' => $callProcess->callSiField($data->parseJson(), $this->getRequest()->getUploadDefinitions()) ]);
	}
	
	function doExecSelectionControl(ParamPost $siEntryIds, ParamPost $apiCallId, ParamPost $bulky) {
		
	}
	
	function doLoadSiEntries(ParamPost $pids) {
		
	}
}
