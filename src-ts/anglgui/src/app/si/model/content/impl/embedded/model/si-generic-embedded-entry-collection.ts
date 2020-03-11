import { SiField } from '../../../si-field';
import { SiGenericEmbeddedEntry } from './si-generic-embedded-entry';

export class SiGenericEmbeddedEntryCollection {

	constructor(public origSiField: SiField, public siGenericEmbeddedEntries: Array<SiGenericEmbeddedEntry>) {
	}
}
