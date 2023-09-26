<?php

namespace testmdl\string\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;
use rocket\attribute\impl\EiPropCke;
use rocket\impl\ei\component\prop\string\cke\ui\CkeMode;
use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;

#[EiType]

class CkeTestObj {

	private int $id;
	#[EiPropCke(CkeMode::NORMAL, true, true, cssConfig: CkeCssConfigMock::class,
			linkProviders: [CkeLinkProviderMock::class])]
	public ?string $ckeStr1 = null;

}