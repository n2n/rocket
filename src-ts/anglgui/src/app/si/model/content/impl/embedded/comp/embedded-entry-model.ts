import { SiEmbeddedEntry } from '../model/si-embedded-entry';


export interface EmbeddedEntryModel {

	getMin(): number;

	getMax(): number|null;

	getValues(): SiEmbeddedEntry[];
}
