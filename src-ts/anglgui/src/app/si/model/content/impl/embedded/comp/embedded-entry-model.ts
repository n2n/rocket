
import { SiEmbeddedEntry } from 'src/app/si/model/entity/impl/embedded/si-embedded-entry';

export interface EmbeddedEntryModel {

	getApiUrl(): string;

	getMin(): number;

	getMax(): number|null;

	getValues(): SiEmbeddedEntry[];
}
