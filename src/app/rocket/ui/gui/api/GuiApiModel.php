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

	/**
	 * @param string $maskId
	 * @param string|null $quickSearchStr
	 * @return int
	 * @throws UnknownGuiElementException
	 */
	function countGuiValueBoundaries(string $maskId, ?string $quickSearchStr): int;

	/**
	 * @param GuiValueBoundary $guiValueBoundary
	 * @param string $maskId
	 * @return GuiValueBoundary
	 * @throws UnknownGuiElementException
	 */
	function copyGuiValueBoundary(GuiValueBoundary $guiValueBoundary, string $maskId): GuiValueBoundary;

	/**
	 * @param string $maskId
	 * @param string $entryIds
	 * @param string $afterEntryId
	 * @return GuiCallResponse
	 * @throws UnknownGuiElementException
	 */
	function insertAfter(string $maskId, string $entryIds, string $afterEntryId): GuiCallResponse;

	/**
	 * @param string $maskId
	 * @param string $entryIds
	 * @param string $beforeEntryId
	 * @return GuiCallResponse
	 * @throws UnknownGuiElementException
	 */
	function insertBefore(string $maskId, string $entryIds, string $beforeEntryId): GuiCallResponse;

	/**
	 * @param string $maskId
	 * @param string $entryIds
	 * @param string $parentId
	 * @return GuiCallResponse
	 * @throws UnknownGuiElementException
	 */
	function insertAsChildren(string $maskId, string $entryIds, string $parentId): GuiCallResponse;
}