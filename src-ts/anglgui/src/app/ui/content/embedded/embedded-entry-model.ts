
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiZone } from 'src/app/si/model/structure/si-zone';

export interface EmbeddedEntryModel {

	getSiZone(): SiZone;

	getApiUrl(): string;

	getMin(): number;

	getMax(): number|null;

	getValues(): SiEmbeddedEntry[];

	registerSiStructure(siStructure: SiStructure): void;

	unregisterSiStructure(siStructure: SiStructure): void;
}
