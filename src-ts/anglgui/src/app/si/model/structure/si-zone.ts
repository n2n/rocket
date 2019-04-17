
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiZone {

	private _content: SiZoneContent|null;

	set content(content: SiZoneContent) {
		if (this._content) {
			throw new IllegalSiStateError('SiZoneContent already initialized.');
		}
		
		this._content = content;
	}
	
	hasContent(): boolean {
		return !!this._content;
	}
	
	get content(): SiZoneContent {
		if (!this._content) {
			throw new IllegalSiStateError('SiZoneContent not yet initialized.');
		}
		
		return this._content;
	}
}