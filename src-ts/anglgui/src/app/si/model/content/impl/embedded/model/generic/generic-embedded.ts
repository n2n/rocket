import { SiGenericValueBoundary } from 'src/app/si/model/generic/si-generic-value-boundary';
import { SiEntryQualifier } from '../../../../si-entry-qualifier';
import { SiField } from '../../../../si-field';
import { SiEmbeddedEntry } from '../si-embedded-entry';

export class SiGenericEmbeddedEntryCollection {
	constructor(public siGenericEmbeddedEntries: Array<SiGenericEmbeddedEntry>) {
	}
}

export class SiGenericEmbeddedEntry {
	constructor(public genericEntry: SiGenericValueBoundary, public summaryGenericEntry: SiGenericValueBoundary|null = null) {
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


