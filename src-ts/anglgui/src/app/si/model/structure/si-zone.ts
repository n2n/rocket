
import { SiLayer } from 'src/app/si/model/structure/si-layer';
import { Subject, Subscription } from 'rxjs';
import { SiStructure } from 'src/app/si/model/structure/si-structure';

export class SiZone {
	public structure = new SiStructure();
	// public content: SiComp|null;
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
