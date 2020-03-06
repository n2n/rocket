import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';

export class SiGenericEmbeddedEntry {
	constructor(public genericEntry: SiGenericEntry, public summaryGenericEntry: SiGenericEntry|null = null) {
	}
}
