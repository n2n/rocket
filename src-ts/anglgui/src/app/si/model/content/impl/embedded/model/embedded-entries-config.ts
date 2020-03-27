import { SiTypeIdentifier } from 'src/app/si/model/meta/si-type-qualifier';

export interface EmbeddedEntriesConfig {
	min: number;
	max: number|null;
	reduced: boolean;
	nonNewRemovable: boolean;
	sortable: boolean;
	allowedSiTypeIdentifiers: SiTypeIdentifier[]|null;
}
