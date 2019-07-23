
import { BulkyEntrySiContent } from "src/app/si/model/structure/impl/bulky-entry-si-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { CompactEntrySiContent } from "src/app/si/model/structure/impl/compact-entry-si-content";
import { SiEntry } from "src/app/si/model/content/si-entry";

export class SiEmbeddedEntry {

	constructor (public content: BulkyEntrySiContent, public summaryContent: CompactEntrySiContent|null) {
	}
	
	get entry(): SiEntry {
		return this.content.entry;
	}
}