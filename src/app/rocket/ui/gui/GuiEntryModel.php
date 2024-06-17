<?php

namespace rocket\ui\gui;

use n2n\l10n\Message;

interface GuiEntryModel {

	/**
	 * @return Message[]
	 */
	function getMessages(): array;

	function handleInput(): bool;
}