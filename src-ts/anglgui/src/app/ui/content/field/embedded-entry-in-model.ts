
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiZone } from 'src/app/si/model/structure/si-zone';

export interface EmbeddedEntryInModel {

	getSiZone(): SiZone;

	getApiUrl(): string;

	isNonNewRemovable(): boolean;

	isSortable(): boolean;

	getMax(): number;

	isReduced(): boolean;

	getValues(): SiEmbeddedEntry[];

	setValues(values: SiEmbeddedEntry[]): void;

	registerSiStructure(siStructure: SiStructure): void;

	unregisterSiStructure(siStructure: SiStructure): void;
}
