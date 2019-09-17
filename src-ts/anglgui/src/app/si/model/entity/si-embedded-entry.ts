
import { BulkyEntrySiComp } from 'src/app/si/model/entity/impl/basic/bulky-entry-si-comp';
import { CompactEntrySiComp } from 'src/app/si/model/entity/impl/basic/compact-entry-si-comp';
import { SiEntry } from 'src/app/si/model/entity/si-entry';

export class SiEmbeddedEntry {

	constructor(public comp: BulkyEntrySiComp, public summaryComp: CompactEntrySiComp|null) {
	}

	get entry(): SiEntry {
		return this.comp.entry;
	}

	set entry(entry: SiEntry) {
		this.comp.entry = entry;
		this.comp.recheck();
	}
}
