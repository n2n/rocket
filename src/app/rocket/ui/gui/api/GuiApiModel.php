<?php

namespace rocket\ui\gui;

use rocket\ui\gui\err\UnknownGuiElementException;

interface GuiApiModel {

	/**
	 * @throws UnknownGuiElementException
	 */
	function lookupGuiMask(string $maskId): GuiMask;

	/**
	 * @throws UnknownGuiElementException
	 */
	function lookupGuiValueBoundary(string $maskId, string $entryId): GuiValueBoundary;

	/**
	 * @throws UnknownGuiElementException
	 */
	function createGuiValueBoundary(string $maskId): GuiValueBoundary;

	/**
	 * @throws UnknownGuiElementException
	 * @return GuiValueBoundary[]
	 */
	function lookupGuiValueBoundaries(string $maskId, int $offset, int $num, ?string $quickSearchStr): array;
}