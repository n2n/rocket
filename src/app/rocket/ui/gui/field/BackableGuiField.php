<?php

namespace rocket\ui\gui\field;

interface BackableGuiField extends GuiField {

	function getModel(): ?GuiFieldModel;

	function setModel(?GuiFieldModel $model): static;

}