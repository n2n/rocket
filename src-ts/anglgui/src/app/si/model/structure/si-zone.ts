
import { SiLayer } from 'src/app/si/model/structure/si-layer';
import { Subject, Subscription } from 'rxjs';
import { SiStructureModel } from './si-structure-model';
import { SiControl } from '../control/si-control';

export class SiZone {
	// public content: SiComp|null;
	private disposeSubject = new Subject<void>();
	public model: SiZoneModel|null = null;

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

export interface SiZoneModel {
	title: string;
	breadcrumbs: SiBreadcrumb[];
	structureModel: SiStructureModel;
	controls: SiControl[];
}

export interface SiBreadcrumb {
	url: string;
	name: string;
}
