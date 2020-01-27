import { Observable, BehaviorSubject, Subscription } from 'rxjs';
import { UiZone } from './ui-zone';
import { UiStructureModel } from './ui-structure-model';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiZoneError } from './ui-zone-error';

export class UiStructure {
	private children: UiStructure[] = [];
	private visibleSubject = new BehaviorSubject<boolean>(true);
	private toolbarChildren$ = new BehaviorSubject<UiStructure[]>([]);
	private contentChildren: UiStructure[] = [];
	private disabledSubject = new BehaviorSubject<boolean>(false);
	private disabledSubscription: Subscription;

	private disposedSubject = new BehaviorSubject<boolean>(false);

	constructor(readonly parent: UiStructure|null, private _zone: UiZone|null, public type: UiStructureType|null = null,
			public label: string|null = null, private _model: UiStructureModel|null = null) {
		if (parent) {
			parent.registerChild(this);
		}

		if (!!this._zone === !!parent) {
			throw new IllegalSiStateError('Either zone or parent must be given but not both.');
		}
	}

	getRoot(): UiStructure {
		let root: UiStructure = this;

		while (root.parent) {
			root = root.parent;
		}

		return root;
	}

	containsDescendant(uiStructure: UiStructure): boolean {
		let curUiStructure = uiStructure;
		while (curUiStructure.parent) {
			if (curUiStructure.parent === this) {
				return true;
			}

			curUiStructure = curUiStructure.parent;
		}

		return false;
	}

	createToolbarChild(model: UiStructureModel): UiStructure {
		const toolbarChild = new UiStructure(this, null, null, null, model);

		const toolbarChildrean = this.toolbarChildren$.getValue();
		toolbarChildrean.push(toolbarChild);
		this.toolbarChildren$.next(toolbarChildrean);

		return toolbarChild;
	}

	createContentChild(type: UiStructureType|null = null, label: string|null = null,
			model: UiStructureModel|null = null): UiStructure {
		const contentChild = new UiStructure(this, null, type, label, model);
		this.contentChildren.push(contentChild);
		return contentChild;
	}

	getToolbarChildren(): UiStructure[] {
		return Array.from(this.toolbarChildren$.getValue());
	}

	getToolbarChildren$(): Observable<UiStructure[]> {
		return this.toolbarChildren$;
	}

	getContentChildren(): UiStructure[] {
		return Array.from(this.contentChildren);
	}

	getChildren(): UiStructure[] {
		return Array.from(this.children);
	}

	getZone(): UiZone {
		return this._zone ? this._zone : this.parent.getZone();
	}

	get disposed(): boolean {
		return this.disposedSubject.getValue();
	}

	private ensureNotDisposed() {
		if (!this.disposed) {
			return;
		}
		throw new IllegalSiStateError('UiStructure already disposed.');
	}

	get model(): UiStructureModel|null {
// 		this.ensureNotDisposed();
		return this._model;
	}

	set model(model: UiStructureModel|null) {
		this.ensureNotDisposed();

		if (this._model === model) {
			return;
		}

		this.clear();

		if (!model) {
			return;
		}

		this._model = model;
		this.disabledSubscription = model.getDisabled$().subscribe(this.disabledSubject);
		model.init(this);

// 		if (this.disabledSubject.getValue()) {
// 			this.disabledSubject.next(false);
// 		}
	}

	private clear() {
		this.toolbarChildren$.next([]);

		for (const child of [...this.children]) {
			child.dispose();
		}

		if (this.children.length !== 0) {
			throw new IllegalSiStateError('Leftover children!');
		}

		if (this._model) {
			this._model.destroy();
			this._model = null;
			this.disabledSubscription.unsubscribe();
			this.disabledSubscription = null;
		}
	}

	get disposed$(): Observable<boolean> {
		return this.disposedSubject;
	}

	dispose() {
		if (this.disposed) {
			return;
		}

		this.disposedSubject.next(true);
		this.disposedSubject.complete();

		this.clear();

		this.toolbarChildren$.complete();

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
		let i = this.children.indexOf(child);
		if (i === -1) {
			throw new IllegalSiStateError('Unknown child.');
		}

		this.children.splice(i, 1);

		const toolbarChildren = this.toolbarChildren$.getValue();
		i = toolbarChildren.indexOf(child);
		if (i > -1) {
			toolbarChildren.splice(i, 1);
		}
		this.toolbarChildren$.next(toolbarChildren);

		i = this.contentChildren.indexOf(child);
		if (i > -1) {
			this.contentChildren.splice(i, 1);
		}
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

	get disabled(): boolean {
		return this.disabledSubject.getValue();
	}

	get disabled$(): Observable<boolean> {
		return this.disabledSubject;
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
