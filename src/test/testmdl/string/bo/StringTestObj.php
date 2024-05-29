<?php

namespace testmdl\string\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;
use rocket\attribute\impl\EiPropString;
use rocket\attribute\EiPreset;

#[EiType]
#[EiPreset(editProps: ['holeradio', 'mandatoryHoleradio', 'holeradioObj', 'mandatoryHoleradioObj'])]
class StringTestObj {

	private int $id;
	public ?string $holeradio = null;
	public string $mandatoryHoleradio = 'holeradio';
	#[EiPropString(multiline: true, constant: true, readOnly: true, mandatory: true)]
	public string $annoHoleradio = 'value';

	public ?StringValueObjectMock $holeradioObj = null;
	public StringValueObjectMock $mandatoryHoleradioObj;
	#[EiPropString(multiline: true, constant: true, readOnly: true, mandatory: true)]
	public StringValueObjectMock $annoHoleradioObj;

	function __construct() {
		$this->mandatoryHoleradioObj = new StringValueObjectMock('asd');
		$this->annoHoleradioObj = new StringValueObjectMock('asd');
	}
}