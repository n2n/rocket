<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use rocket\attribute\impl\EiPropBool;
use rocket\attribute\impl\EiPropEnum;
use rocket\attribute\impl\EiPropDecimal;
use rocket\attribute\impl\EiPropString;

#[EiType]
#[EiPreset(EiPresetMode::READ, excludeProps: ['exclProp'])]
class AnnotatedReadPresetTestObj {

	public int $id;

	public string $exclProp;

	#[EiPropBool(onGuiProps: ['on1', 'on2'], offGuiProps: ['off1', 'off2'])]
	public bool $pubBoolTest = false;

	#[EiPropEnum(['big' => 'Big', 'super-big' => 'Super Big'], emptyLabel: 'No selection',
			guiPropsMap: ['big' => ['big1', 'big2'], 'super-big' => ['superBig1', 'superBig2']])]
	public ?string $pubEnumTest = null;

	#[EiPropDecimal(decimalPlaces: 3)]
	public float $pubDecimalTest = 0;

	#[EiPropString(multiline: true)]
	public string $pubStringTest = 'string';
}

