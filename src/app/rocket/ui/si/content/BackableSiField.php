<?php

namespace rocket\ui\si\content;

interface BackableSiField extends SiField {

	function setModel(SiFieldModel $fieldModel): static;

}