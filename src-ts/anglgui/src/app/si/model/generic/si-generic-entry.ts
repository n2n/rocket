import { SiEntryIdentifier, SiEntryQualifier } from '../content/si-qualifier';
import { SiGenericEntryBuildup } from './si-generic-entry-buildup';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';

export class SiGenericEntry {
	// public treeLevel: number|null = null;
	public bulky = false;
	public readOnly = true;

	constructor(public identifier: SiEntryIdentifier, public selectedTypeId: string|null,
			public entryBuildupsMap = new Map<string, SiGenericEntryBuildup>()) {
	}

	get entryQualifier(): SiEntryQualifier {
		IllegalSiStateError.assertTrue(this.entryBuildupsMap.has(this.selectedTypeId));
		return this.entryBuildupsMap.get(this.selectedTypeId).entryQualifier;
	}
}
