
import { BulkyEntrySiContent } from "src/app/si/model/structure/impl/bulky-entry-si-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { CompactEntrySiContent } from "src/app/si/model/structure/impl/compact-entry-si-content";
import { SiEntry } from "src/app/si/model/content/si-entry";

export class SiEmbeddedEntry {
	private _content: BulkyEntrySiContent;
	private _structure: SiStructure;

	private _summaryContent: CompactEntrySiContent|null;
	private _summaryStructure: SiStructure|null;

	constructor (content: BulkyEntrySiContent, summaryContent: CompactEntrySiContent|null) {
		this.content = content;
		this.summaryContent = summaryContent;
		
	}
	
	get entry(): SiEntry {
		return this._content.entry;
	}
	
	set content(content: BulkyEntrySiContent) {
		this._content = content;
		this._structure = new SiStructure(null, null, content)
	}
			
	get structure(): SiStructure {
		return this._structure;
	}
	
	set summaryContent(summaryContent: CompactEntrySiContent|null) {
		this._summaryContent = summaryContent;
		if (summaryContent) {
			this._summaryStructure = new SiStructure(null, null, summaryContent);
		} else {
			this._summaryStructure = null;
		}
	}
	
	get summaryStructure(): SiStructure|null {
		return this._summaryStructure;
	}
}