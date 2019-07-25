
import { BulkyEntrySiComp } from "src/app/si/model/structure/impl/bulky-entry-si-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { CompactEntrySiComp } from "src/app/si/model/structure/impl/compact-entry-si-content";
import { SiEntry } from "src/app/si/model/content/si-entry";

export class SiEmbeddedEntry {

	constructor (public comp: BulkyEntrySiComp, public summaryComp: CompactEntrySiComp|null) {
	}
	
	get entry(): SiEntry {
		return this.comp.entry;
	}
}