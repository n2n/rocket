import { Observable, BehaviorSubject } from 'rxjs';
import { UiZone } from './ui-zone';
import { UiStructureModel } from './ui-structure-model';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiZoneError } from './ui-zone-error';
import { UiContent } from './ui-content';

export class UiStructure {
	private children: UiStructure[] = [];
	private visibleSubject = new BehaviorSubject<boolean>(true);
	toolbackUiContents: UiContent[] = [];

	private disposed = false;

	constructor(readonly parent: UiStructure|null, private _zone: UiZone|null, public type: UiStructureType|null = null,
			public label: string|null = null, private _model: UiStructureModel|null = null) {
		if (parent) {
			parent.registerChild(this);
		}

		if (!!this._zone === !!parent) {
			throw new IllegalSiStateError('Either zone or parent must be given but not both.');
		}
	}

	createChild(type: UiStructureType|null = null, label: string|null = null,
			model: UiStructureModel|null = null): UiStructure {
		return new UiStructure(this, null, type, label, model);
	}

	getChildren(): UiStructure[] {
		return Array.from(this.children);
	}

	getZone(): UiZone {
		return this._zone ? this._zone : this.parent.getZone();
	}

	private ensureNotDisposed() {
		if (!this.disposed) {
			return;
		}

		throw new IllegalSiStateError('UiStructure already disposed.');
	}

	get model(): UiStructureModel|null {
		this.ensureNotDisposed();
		return this._model;
	}

	set model(model: UiStructureModel|null) {
		this.ensureNotDisposed();

		if (this._model === model) {
			return;
		}

		this.clear();
		this._model = model;
	}

	private clear() {
		for (const child of [...this.children]) {
			child.dispose();
		}

		if (this.children.length !== 0) {
			throw new IllegalSiStateError('Leftover children!');
		}
	}

	dispose() {
		if (this.disposed) {
			return;
		}

		this.disposed = true;

		this.clear();

		if (this.parent) {
			this.parent.unregisterChild(this);
		}
	}

	protected registerChild(child: UiStructure) {
		this.ensureNotDisposed();

		const i = this.children.indexOf(child);
		if (i !== -1 || this === child) {
			throw new IllegalSiStateError('Child already exists or is same as parent.');
		}

		this.children.push(child);
	}

	protected unregisterChild(child: UiStructure) {
		const i = this.children.indexOf(child);
		if (i === -1) {
			throw new IllegalSiStateError('Unknown child.');
		}

		this.children.splice(i, 1);
	}

	get visible(): boolean {
		return this.visibleSubject.getValue();
	}

	set visible(visible: boolean) {
		this.visibleSubject.next(visible);
	}

	get visible$(): Observable<boolean> {
		return this.visibleSubject;
	}

	getZoneErrors(): UiZoneError[] {
		this.ensureNotDisposed();

		const errors: UiZoneError[] = [];

		if (this.model) {
			errors.push(...this.assembleZoneErrors(this));
		}

		for (const child of this.children) {
			errors.push(...child.getZoneErrors());
		}

		return errors;
	}

	private assembleZoneErrors(structure: UiStructure): UiZoneError[] {
		return structure.model.getMessages().map((message) => {
			return { message, structure };
		});
	}
}
