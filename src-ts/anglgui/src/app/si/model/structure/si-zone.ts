
import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiLayer } from "src/app/si/model/structure/si-layer";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { Subject, Subscription } from "rxjs";
import { SiStructure } from "src/app/si/model/structure/si-structure";

export class SiZone {
	public structure = new SiStructure();
	public _content: SiContent|null;
	private disposeSubject = new Subject<void>();
	
	constructor(readonly id: number, readonly url: string|null, readonly layer: SiLayer) {
		
	}
	
		
	dispose() {
		this.disposeSubject.next();
		this.disposeSubject.complete();
	}
	
	onDispose(callback: () => any): Subscription {
		return this.disposeSubject.subscribe(callback);
	}
}
