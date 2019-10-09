
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { BulkyEntrySiComp } from 'src/app/si/model/comp/impl/model/bulky-entry-si-comp';
import { CompactEntrySiComp } from 'src/app/si/model/comp/impl/model/compact-entry-si-comp';

export class SiEmbeddedEntry {

	constructor(public comp: BulkyEntrySiComp, public summaryComp: CompactEntrySiComp|null) {
	}

	get entry(): SiEntry {
		return this.comp.entry;
	}

	set entry(entry: SiEntry) {
		this.comp.entry = entry;
	}
}
