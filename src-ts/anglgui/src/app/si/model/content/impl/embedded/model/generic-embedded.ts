import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiField } from '../../../si-field';
import { SiEntryQualifier } from '../../../si-qualifier';

export class SiGenericEmbeddedEntryCollection {
	constructor(public siGenericEmbeddedEntries: Array<SiGenericEmbeddedEntry>) {
	}
}

export class SiGenericEmbeddedEntry {
	constructor(public genericEntry: SiGenericEntry, public summaryGenericEntry: SiGenericEntry|null = null) {
	}

	get selectedTypeId(): string|null {
		return this.genericEntry.selectedTypeId;
	}

	get entryQualifier(): SiEntryQualifier {
		return this.genericEntry.entryQualifier;
	}
}

export class SiEmbeddedEntryResetPointCollection {
	constructor(public origSiField: SiField,
			public genercEntryResetPoints: SiEmbeddedEntryResetPoint[]) {
	}
}

export interface SiEmbeddedEntryResetPoint {
	origSiEmbeddedEntry: SiEmbeddedEntry;
	genericEmbeddedEntry: SiGenericEmbeddedEntry;
}


