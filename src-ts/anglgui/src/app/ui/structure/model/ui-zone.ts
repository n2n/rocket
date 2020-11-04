import { Subject, Subscription } from 'rxjs';
import { UiStructureModel } from './ui-structure-model';
import { UiLayer } from './ui-layer';
import { UiContent } from './ui-content';
import { UiNavPoint } from '../../util/model/ui-nav-point';
import { UiStructure } from './ui-structure';

export class UiZone {
	// public content: SiGui|null;
	private disposeSubject = new Subject<void>();
	private _model: UiZoneModel|null = null;
	readonly uiStructure: UiStructure;

	constructor(readonly url: string|null, readonly layer: UiLayer) {
		this.uiStructure = new UiStructure(null, this, null, null);
	}

	get active(): boolean {
		return this.layer.currentRoute.zone === this;
	}

	set model(model: UiZoneModel|null) {
		if (this._model === model) {
			return;
		}

		this.resetModel();

		if (model) {
			this._model = model;
			this.uiStructure.model = model.structureModel;
		}
	}

	get model(): UiZoneModel|null {
		return this._model;
	}

	private resetModel() {
		this._model = null;
		this.uiStructure.model = null;
	}

	dispose() {
		this.resetModel();

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
	partialCommandContents?: UiContent[];
	mainCommandContents?: UiContent[];
}

export interface UiBreadcrumb {
	navPoint: UiNavPoint;
	name: string;
}
