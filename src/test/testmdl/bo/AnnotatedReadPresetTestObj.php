<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\ei\util\Eiu;
use rocket\attribute\impl\EiPropBool;
use rocket\attribute\impl\EiPropEnum;
use rocket\attribute\impl\EiPropDecimal;

#[EiType]
#[EiPreset(EiPresetMode::READ)]
class AnnotatedReadPresetTestObj {

	public int $id;

	#[EiPropBool(onGuiProps: ['on1', 'on2'], offGuiProps: ['off1', 'off2'])]
	public bool $pubBoolTest = false;

	#[EiPropEnum(['big' => 'Big', 'super-big' => 'Super Big'], emptyLabel: 'No selection',
			guiPropsMap: ['big' => ['big1', 'big2'], 'super-big' => ['superBig1', 'superBig2']])]
	public ?string $pubEnumTest = null;

	#[EiPropDecimal(decimalPlaces: 3)]
	public float $pubDecimalTest = 0;
}
