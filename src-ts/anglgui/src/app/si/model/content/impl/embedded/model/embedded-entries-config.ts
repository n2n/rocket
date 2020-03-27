
export interface EmbeddedEntriesConfig {
	min: number;
	max: number|null;
	reduced: boolean;
	nonNewRemovable: boolean;
	sortable: boolean;
	allowedSiTypeIds: string[]|null;
}
