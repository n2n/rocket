
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiLayer } from "src/app/si/model/structure/si-layer";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { Subject, Subscription } from "rxjs";
import { SiStructure } from "src/app/si/model/structure/si-structure";

export class SiZone {
	public structure = new SiStructure();
	public _content: SiZoneContent|null;
	private disposeSubject = new Subject<void>();
	
	constructor(readonly url: string, readonly layer: SiLayer) {
		
	}
	
	hasContent(): boolean {
		return !!this._content;
	}
	
	removeContent() {
		this._content = null;
	}
	
	get content(): SiZoneContent {
		if (this._content) {
			return this._content;
		}
		
		throw new IllegalSiStateError('SiZoneContent not assinged.');
	}
	
	set content(content: SiZoneContent) {
		this._content = content;
		content.applyTo(this.structure);
	}
	
	dispose() {
		this.removeContent();
		this.disposeSubject.next();
		this.disposeSubject.complete();
	}
	
	onDispose(callback: () => any): Subscription {
		return this.disposeSubject.subscribe(callback);
	}
}
