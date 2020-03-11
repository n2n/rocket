import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { SiEmbeddedEntry } from './si-embedded-entry';

export class SiGenericEmbeddedEntry {
	public origSiEmbeddedEntry: SiEmbeddedEntry|null = null;

	constructor(public genericEntry: SiGenericEntry, public summaryGenericEntry: SiGenericEntry|null = null) {
	}
}
