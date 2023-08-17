<?php

namespace testmdl\embedded\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use n2n\persistence\orm\attribute\Embedded;

#[EiType]
#[EiPreset(EiPresetMode::READ, editProps: ['optEditEmbeddable', 'reqEditEmbeddable', 'reqEditEmbeddable.someProp'])]
class EmbeddingContainerTestObj {
	public int $id;
	#[Embedded(columnPrefix: 'opt_edit_')]
	public ?EmbeddableTestObj $optEditEmbeddable = null;
	#[Embedded(columnPrefix: 'req_edit_')]
	public EmbeddableTestObj $reqEditEmbeddable;

	function __construct() {
		$this->reqEditEmbeddable = new EmbeddableTestObj();
	}
}