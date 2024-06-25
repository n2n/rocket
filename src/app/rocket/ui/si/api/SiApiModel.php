<?php

namespace rocket\ui\si\api;

use rocket\ui\si\meta\SiMask;
use rocket\ui\si\control\SiControl;
use rocket\ui\si\err\UnknownSiElementException;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\content\SiPartialContent;
use rocket\ui\si\content\SiField;
use rocket\ui\si\api\response\SiCallResponse;

interface SiApiModel {

	/**
	 * @param string $maskId
	 * @return SiMask
	 * @throws UnknownSiElementException
	 */
	function lookupSiMask(string $maskId): SiMask;

	/**
	 * @param string $contextMaskId
	 * @param string $controlName
	 * @return SiControl
	 * @throws UnknownSiElementException
	 */
	function lookupSiMaskControl(string $contextMaskId, string $controlName): SiControl;

	/**
	 * @param string $contextMaskId
	 * @param string $entryId
	 * @param string $controlName
	 * @return SiControl
	 * @throws UnknownSiElementException
	 */
	function lookupSiEntryControl(string $contextMaskId, string $entryId, string $controlName): SiControl;

	/**
	 * @param string $contextMaskId
	 * @param string $entryId
	 * @param string $fieldName
	 * @return SiField
	 * @throws UnknownSiElementException
	 */
	function lookupSiField(string $contextMaskId, string $entryId, string $fieldName): SiField;

	/**
	 * @param string $maskId
	 * @param string|null $entryId
	 * @param array|null $allowedFieldNames
	 * @return SiValueBoundary
	 * @throws UnknownSiElementException
	 */
	function lookupSiValueBoundary(string $maskId, ?string $entryId, ?array $allowedFieldNames): SiValueBoundary;

	/**
	 * @param string $maskId
	 * @param int $from
	 * @param int $num
	 * @param string|null $quickSearchStr
	 * @param string[]|null $allowedFieldNames
	 * @return SiPartialContent
	 */
	function lookupSiPartialContent(string $maskId, int $from, int $num, ?string $quickSearchStr,
			?array $allowedFieldNames): SiPartialContent;

	/**
	 * @param SiValueBoundary $boundary
	 * @param string $maskId
	 * @return SiValueBoundary
	 * @throws UnknownSiElementException
	 */
	function copySiValueBoundary(SiValueBoundary $boundary, string $maskId): SiValueBoundary;

	/**
	 * @throws UnknownSiElementException
	 */
	function insertSiEntriesAfter(string $maskId, string $entryIds, string $afterEntryId): SiCallResponse;

	/**
	 * @throws UnknownSiElementException
	 */
	function insertSiEntriesBefore(string $maskId, string $entryIds, string $beforeEntryId): SiCallResponse;

	/**
	 * @throws UnknownSiElementException
	 */
	function insertSiEntriesAsChildren(string $maskId, string $entryIds, string $parentId): SiCallResponse;

}