import { SiEmbeddedEntry } from '../model/si-embedded-entry';


export interface EmbeddedEntryModel {

	getApiUrl(): string;

	getMin(): number;

	getMax(): number|null;

	getValues(): SiEmbeddedEntry[];
}
