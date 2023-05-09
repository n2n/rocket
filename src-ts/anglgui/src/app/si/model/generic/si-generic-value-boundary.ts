import { SiEntryIdentifier, SiEntryQualifier } from '../content/si-entry-qualifier';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';
import { SiStyle } from '../meta/si-view-mode';
import { SiGenericEntry } from './si-generic-entry-buildup';

export class SiGenericValueBoundary {
	// public treeLevel: number|null = null;
	public style: SiStyle = {
		bulky: false,
		readOnly: true
	};

	constructor(public identifier: SiEntryIdentifier, public selectedTypeId: string|null,
			public entriesMap = new Map<string, SiGenericEntry>()) {
	}

	get entryQualifier(): SiEntryQualifier {
		IllegalSiStateError.assertTrue(this.entriesMap.has(this.selectedTypeId!));
		return this.entriesMap.get(this.selectedTypeId!)!.entryQualifier;
	}
}
