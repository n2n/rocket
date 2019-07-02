
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiContainer } from "src/app/si/model/structure/si-container";
import { Subject, Observable, Subscription } from "rxjs";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";

export interface SiLayer {
	readonly container: SiContainer;
	readonly main: boolean;
	readonly currentZone: SiZone|null;
}

abstract class SiLayerAdapter implements SiLayer {
	protected zones: Array<SiZone> = [];
	protected currentZoneIndex: number|null = null;

	constructor(readonly container: SiContainer) {
	}
	
	readonly abstract main: boolean;
	
	get currentZone(): SiZone|null {
		if (this.currentZoneIndex === null) {
			return null;
		}
		
		if (this.zones[this.currentZoneIndex]) {
			return this.zones[this.currentZoneIndex];
		}
		
		throw new IllegalSiStateError('Layer contains invalid current zone');
	}
	
	protected getZoneById(id: number): SiZone|null {
		return this.zones.find(zone => zone.id == id) || null; 
	}
	
	protected getZoneIndexById(id: number): number|null {
		return this.zones.findIndex(zone => zone.id == id) || null; 
	}
	
	protected createZone(id: number, url: string|null): SiZone {
		if (!!this.getZoneById(id)) {
			throw new IllegalSiStateError('Zone with id ' + id + ' already exists. Url: ' + url);
		}
		
		if (this.currentZoneIndex !== null) {
			this.clearZoneAfterIndex(this.currentZoneIndex);
		}
		
		
		const zone = new SiZone(id, url, <any> this);
		this.currentZoneIndex = this.zones.push(zone) - 1;
		zone.onDispose(() =>  {
			this.removeZone(zone);
		});
		return zone;
	}
	
	private clearZoneAfterIndex(currentZoneIndex: number) {
		for (const zone of this.zones.slice(currentZoneIndex + 1)) {
			zone.dispose();
		}
	}
	
	private removeZone(zone: SiZone) {
		const i = this.zones.indexOf(zone)
		if (i > -1) {
			this.zones.splice(i, 1);
			return;
		}
		
		throw new IllegalSiStateError('Zone to remove doesn\'t exist on layer.');		
	}
}


export class MainSiLayer extends SiLayerAdapter {
	readonly main = true;
	
	constructor(container: SiContainer) {
		super(container);
	}
	
	pushZone(id: number, url: string): SiZone {
		return this.createZone(id, url);
	}
    
    popZone(id: number, verifyUrl: string): boolean {
    	const index = this.getZoneIndexById(id);
    	if (!index || this.zones[index].url != verifyUrl) {
    		// @todo temporary test to monitor angular routing behaviour
    		throw new IllegalSiStateError('Zone pop url verify missmatch for id ' + id + ': ' 
    				+ this.zones[<number> index].url + ' != ' + verifyUrl);
//    		return false;
    	}
    	
    	this.currentZoneIndex = index;
		return true;
    }
}

export class PopupSiLayer extends SiLayerAdapter {
    private disposeSubject = new Subject<void>();
	readonly main = false;
	
	constructor(container: SiContainer) {
		super(container);
	}
    
    pushZone(url: string|null): SiZone {
		return this.createZone(this.zones.length, url);
    }
    
	dispose() {
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