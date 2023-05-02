import { SiEntryIdentifier, SiEntryQualifier } from '../content/si-entry-qualifier';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';
import { SiStyle } from '../meta/si-view-mode';

export class SiGenericEntry {
	// public treeLevel: number|null = null;
	public style: SiStyle = {
		bulky: false,
		readOnly: true
	};

	constructor(public identifier: SiEntryIdentifier, public selectedTypeId: string|null,
			public entrysMap = new Map<string, SiGenericEntry>()) {
	}

	get entryQualifier(): SiEntryQualifier {
		IllegalSiStateError.assertTrue(this.entrysMap.has(this.selectedTypeId!));
		return this.entrysMap.get(this.selectedTypeId!)!.entryQualifier;
	}
}
