
export enum SiFieldType {
	STRING_OUT = 'string-out',
	STRING_IN = 'string-in',
	FILE_OUT = 'file-out',
	FILE_IN = 'file-in',
	LINK_OUT = 'link-out',
	QUALIFIER_SELECT_IN = 'qualifier-select-in',
	EMBEDDED_ENTRY_IN = 'embedded-entry-in'
}

export enum SiControlType {
	REF = 'ref',
	API_CALL = 'api-call'
}

export enum SiContentType {
	ENTRIES_LIST = 'entries-list',
	BULKY_ENTRY = 'bulky-entry',
	COMPACT_ENTRY = 'compact-entry'
}
