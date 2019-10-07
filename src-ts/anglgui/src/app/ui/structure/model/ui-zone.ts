
import { UiLayer } from 'src/app/si/model/structure/ui-layer';
import { Subject, Subscription } from 'rxjs';
import { UiStructureModel } from './ui-structure-model';
import { SiControl } from '../control/si-control';

export class UiZone {
	// public content: SiComp|null;
	private disposeSubject = new Subject<void>();
	public model: UiZoneModel|null = null;

	constructor(readonly id: number, readonly url: string|null, readonly layer: UiLayer) {
	}

	dispose() {
		this.disposeSubject.next();
		this.disposeSubject.complete();
	}

	onDispose(callback: () => any): Subscription {
		return this.disposeSubject.subscribe(callback);
	}
}

export interface UiZoneModel {
	title: string;
	breadcrumbs: UiBreadcrumb[];
	structureModel: UiStructureModel;
	controls: SiControl[];
}

export interface UiBreadcrumb {
	url: string;
	name: string;
}
