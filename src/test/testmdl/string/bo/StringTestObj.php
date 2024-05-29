<?php

namespace testmdl\string\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;
use rocket\attribute\impl\EiPropString;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;

#[EiType]
#[EiPreset(EiPresetMode::EDIT_CMDS, editProps: ['holeradio', 'mandatoryHoleradio', 'holeradioObj', 'mandatoryHoleradioObj'])]
class StringTestObj {

	public int $id;
	public ?string $holeradio = null;
	public string $mandatoryHoleradio = 'holeradio';
	#[EiPropString(multiline: true, constant: true, readOnly: true, mandatory: true)]
	public $annoHoleradio = null;

	public ?StrObjMock $holeradioObj = null;
	public StrObjMock $mandatoryHoleradioObj;
	#[EiPropString(multiline: true, constant: true, readOnly: true, mandatory: true)]
	public ?StrObjMock $annoHoleradioObj = null;

	function __construct() {
		$this->mandatoryHoleradioObj = new StrObjMock('asd');
	}
}