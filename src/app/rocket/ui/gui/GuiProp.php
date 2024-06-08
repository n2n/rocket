<?php
namespace rocket\ui\gui;

use n2n\l10n\Lstr;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiStructureType;

class GuiProp {

	public function __construct(private readonly Lstr $labelLstr, private readonly ?Lstr $helpTextLstr = null) {
	}

	function getLabelLstr(): Lstr {
		return $this->labelLstr;
	}

	function getHelpTextLstr(): Lstr {
		return $this->helpTextLstr;
	}
}
