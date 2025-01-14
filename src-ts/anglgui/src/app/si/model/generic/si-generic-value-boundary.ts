import { SiEntryQualifier } from '../content/si-entry-qualifier';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';

import { SiGenericEntry } from './si-generic-entry-buildup';

export class SiGenericValueBoundary {
	// public treeLevel: number|null = null;

	private entriesMap = new Map<string, SiGenericEntry>()

	constructor(public selectedTypeId: string|null, entries: SiGenericEntry[]) {
		entries.forEach((e) => this.putEntry(e));
	}

	get selected(): boolean {
		return this.selectedTypeId !== null;
	}

	putEntry(genericEntry: SiGenericEntry): void {
		this.entriesMap.set(genericEntry.maskId, genericEntry);
	}

	get entries(): SiGenericEntry[] {
		return Array.from(this.entriesMap.values());
	}

	get selectedEntryQualifier(): SiEntryQualifier {
		IllegalSiStateError.assertTrue(this.entriesMap.has(this.selectedTypeId!));
		return this.entriesMap.get(this.selectedTypeId!)!.entryQualifier;
	}
}
