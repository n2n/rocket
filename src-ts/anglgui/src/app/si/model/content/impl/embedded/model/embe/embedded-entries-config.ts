export interface EmbeddedEntriesOutConfig {
	reduced: boolean;
}


export interface EmbeddedEntriesInConfig {
	bulkyMaskId: string;
	summaryMaskId: string|null;
	min: number;
	max: number|null;
	nonNewRemovable: boolean;
	sortable: boolean;
	allowedMaskIds: string[]|null;
}
