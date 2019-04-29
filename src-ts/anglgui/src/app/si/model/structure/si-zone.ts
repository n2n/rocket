
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiLayer } from "src/app/si/model/structure/si-layer";
import { SiStructure } from "src/app/si/model/structure/si-structure";

export class SiZone {
	public _content: SiZoneContent|null;
	
	constructor(readonly url: string, readonly layer: SiLayer) {
		
	}
	
	hasContent(): boolean {
		return !!this._content;
	}
	
	removeContent() {
		this._content;
	}
	
	get content(): SiZoneContent {
		if (this._content) {
			return this._content;
		}
		
		throw new IllegalSiStateError('SiZoneContent not assinged.');
	}
	
	set content(content: SiZoneContent) {
		this._content = content;
	}
}
