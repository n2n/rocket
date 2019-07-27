
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiType } from "src/app/si/model/content/si-type";

export interface EmbeddedEntryInModel {

	getSiZone(): SiZone;

	getApiUrl(): string;

	isNonNewRemovable(): boolean;

	isSortable(): boolean;

	getMin(): number;
	
	getMax(): number|null;

	isReduced(): boolean;
	
	getPastCategory(): string|null
	
	getAllowedSiTypes(): SiType[]|null;

	getValues(): SiEmbeddedEntry[];

	setValues(values: SiEmbeddedEntry[]): void;

	registerSiStructure(siStructure: SiStructure): void;

	unregisterSiStructure(siStructure: SiStructure): void;
}
