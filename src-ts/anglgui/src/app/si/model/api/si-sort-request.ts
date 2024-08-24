
export interface SiSortRequest {
	maskId: string;
	entryIds: string[];
	afterEntryId?: string;
	beforeEntryId?: string;
	parentEntryId?: string;
}
