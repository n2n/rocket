import { SiEntryIdentifier } from '../content/si-qualifier';
import { SiGenericEntryBuildup } from './si-generic-entry-buildup';

export class SiGenericEntry {
	public treeLevel: number|null = null;
	public bulky = false;
	public readOnly = true;

	constructor(public identifier: SiEntryIdentifier, public selectedTypeId: string,
			public entryBuildupsMap = new Map<string, SiGenericEntryBuildup>()) {
	}
}
