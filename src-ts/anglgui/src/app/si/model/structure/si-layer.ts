
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiContainer } from "src/app/si/model/structure/si-container";
import { Subject, Observable, Subscription } from "rxjs";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export class SiLayer {
    private zoneMap = new Map<string, SiZone>();
	private zones: Array<SiZone> = [];
    private disposeSubject = new Subject<void>();
    
	constructor(readonly container: SiContainer, readonly main: boolean) {
	}
	
	pushZone(url: string): SiZone {
		let zone = this.zoneMap.get(url)
		
		if (!zone) {
			zone = new SiZone(url, this);
			this.zones.push(zone);
		}
		
		return zone;
	}
	
	get currentZone(): SiZone {
		return this.zones[this.zones.length - 1];
	}
	
	dispose() {
		if (this.main) {
			throw new IllegalSiStateError('Main layer can not be disposed.');
		}
		
		for (const zone of this.zones) {
			zone.dispose();
		}
		
		this.disposeSubject.next();
		this.disposeSubject.complete();
	}
	
	get disposed() {
		return this.disposeSubject.closed;
	}
	
	onDispose(callback: () => any): Subscription {
		return this.disposeSubject.subscribe(callback);
	}
}