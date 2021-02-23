import { Observable, BehaviorSubject, Subscription, Subject } from 'rxjs';
import { UiZone } from './ui-zone';
import { UiStructureModel, UiStructureModelMode } from './ui-structure-model';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiZoneError } from './ui-zone-error';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { UiStructureError } from './ui-structure-error';

export class UiStructure {
	private _model: UiStructureModel|null;
	private children: Array<{ structure: UiStructure, subscription: Subscription }> = [];
	private visibleSubject = new BehaviorSubject<boolean>(true);
	private markedSubject = new BehaviorSubject<boolean>(false);
	private toolbarChildrenSubject = new BehaviorSubject<UiStructure[]>([]);
	private contentChildrenSubject = new BehaviorSubject<UiStructure[]>([]);
	private disabledSubject = new BehaviorSubject<boolean>(false);
	private disabledSubscription: Subscription;
	private focusedSubject = new Subject<void>();
	private messagesSubscription: Subscription;
	private zoneErrorsCollection = new BehaviorCollection<UiZoneError>();

	private disposedSubject = new BehaviorSubject<boolean>(false);
	private _level: number|null = null;

	// compact = false;

	constructor(readonly parent: UiStructure|null, private _zone: UiZone|null, public type: UiStructureType|null = null,
			public label: string|null = null, model: UiStructureModel|null = null) {
		if (parent) {
			parent.registerChild(this);
			// this.compact = parent.compact;
		}

		if (!!this._zone === !!parent) {
			throw new IllegalSiStateError('Either zone or parent must be given but not both.');
		}

		this.model = model;
	}

	getRoot(): UiStructure {
		let root: UiStructure = this;

		while (root.parent) {
			root = root.parent;
		}

		return root;
	}

	get level(): number {
		if (this._level !== null) {
			return this._level;
		}

		this._level = 0;

		let cur: UiStructure = this;
		while (cur.parent) {
			cur = cur.parent;
			this._level++;
		}

		return this._level;
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

	isItemCollection(): boolean {
		return this.type === UiStructureType.ITEM && !!this.model
				&& this.model.getMode() === UiStructureModelMode.ITEM_COLLECTION;

	}

	isDoubleItem(): boolean {
		return this.type === UiStructureType.ITEM && this.parent.isItemCollection();
	}

	createToolbarChild(model: UiStructureModel): UiStructure {
		const toolbarChild = new UiStructure(this, null, null, null, model);

		const toolbarChildrean = this.toolbarChildrenSubject.getValue();
		toolbarChildrean.push(toolbarChild);
		this.toolbarChildrenSubject.next(toolbarChildrean);

		return toolbarChild;
	}

	createContentChild(type: UiStructureType|null = null, label: string|null = null,
			model: UiStructureModel|null = null): UiStructure {
		const contentChild = new UiStructure(this, null, type, label, model);

		const contentChildrean = this.contentChildrenSubject.getValue();
		contentChildrean.push(contentChild);
		this.contentChildrenSubject.next(contentChildrean);

		return contentChild;
	}

	createChild(type: UiStructureType|null = null, label: string|null = null,
			model: UiStructureModel|null = null): UiStructure {
		return new UiStructure(this, null, type, label, model);
	}

	hasToolbarChildren(): boolean {
		return this.getToolbarChildren().length > 0;
	}

	getToolbarChildren(): UiStructure[] {
		return Array.from(this.toolbarChildrenSubject.getValue());
	}

	getToolbarChildren$(): Observable<UiStructure[]> {
		return this.toolbarChildrenSubject;
	}

	hasContentChildren(): boolean {
		return this.getContentChildren().length > 0;
	}

	getContentChildren(): UiStructure[] {
		return Array.from(this.contentChildrenSubject.getValue());
	}

	getContentChildren$(): Observable<UiStructure[]> {
		return this.contentChildrenSubject.asObservable();
	}

	getChildren(): UiStructure[] {
		return this.children.map(c => c.structure);
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
		model.bind(this);
		this.disabledSubscription = model.getDisabled$().subscribe(d => this.disabledSubject.next(d));
		this.messagesSubscription = model.getStructureErrors$().subscribe(() => this.compileZoneErrors());

// 		if (this.disabledSubject.getValue()) {
// 			this.disabledSubject.next(false);
// 		}
	}

	private clear() {
		this.toolbarChildrenSubject.next([]);

		for (const child of [...this.children]) {
			child.structure.dispose();
		}

		if (this.children.length !== 0) {
			throw new IllegalSiStateError('Leftover children!');
		}

		if (this._model) {
			this._model.unbind();
			this._model = null;
			this.disabledSubscription.unsubscribe();
			this.disabledSubscription = null;
			this.messagesSubscription.unsubscribe();
			this.messagesSubscription = null;
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

		this.visibleSubject.complete();
		this.markedSubject.complete();
		this.toolbarChildrenSubject.complete();
		this.focusedSubject.complete();

		this.zoneErrorsCollection.dispose();

		if (this.parent) {
			this.parent.unregisterChild(this);
		}
	}

	protected registerChild(child: UiStructure) {
		this.ensureNotDisposed();

		const i = this.children.findIndex(c => c.structure === child);
		if (i !== -1 || this === child) {
			throw new IllegalSiStateError('Child already exists or is same as parent.');
		}

		this.children.push({
			structure: child,
			subscription: child.getZoneErrors$().subscribe(() => {
				this.compileZoneErrors();
			})
		});
	}

	protected unregisterChild(child: UiStructure) {
		let i = this.children.findIndex(c => c.structure === child);
		if (i === -1) {
			throw new IllegalSiStateError('Unknown child.');
		}

		this.children.splice(i, 1);

		const toolbarChildren = this.toolbarChildrenSubject.getValue();
		i = toolbarChildren.indexOf(child);
		if (i > -1) {
			toolbarChildren.splice(i, 1);
		}
		this.toolbarChildrenSubject.next(toolbarChildren);

		const contentChildren = this.contentChildrenSubject.getValue();
		i = contentChildren.indexOf(child);
		if (i > -1) {
			contentChildren.splice(i, 1);
		}
		this.contentChildrenSubject.next(contentChildren);
	}

	get marked(): boolean {
		return this.markedSubject.getValue();
	}

	set marked(marked: boolean) {
		this.markedSubject.next(marked);
	}

	get visible(): boolean {
		return this.visibleSubject.getValue();
	}

	set visible(visible: boolean) {
		this.visibleSubject.next(visible);
	}

	get visible$(): Observable<boolean> {
		return this.visibleSubject.asObservable();
	}

	get disabled(): boolean {
		return this.disabledSubject.getValue();
	}

	get disabled$(): Observable<boolean> {
		return this.disabledSubject.asObservable();
	}

	get focused$(): Observable<void> {
		return this.focusedSubject.asObservable();
	}

	focus(): void {
		this.focusedSubject.next();
	}

	getZoneErrors$(): Observable<UiZoneError[]> {
		return this.zoneErrorsCollection.get$();
	}

	getZoneErrors(): UiZoneError[] {
		this.ensureNotDisposed();
		return this.zoneErrorsCollection.get();
	}

	private compileZoneErrors() {
		const errors: UiZoneError[] = [];

		if (this.model) {
			errors.push(...this.model.getStructureErrors().map(se => this.createZoneError(se)));
		}

		for (const child of this.children) {
			errors.push(...child.structure.getZoneErrors());
		}

		this.zoneErrorsCollection.set(errors);
	}

	private createZoneError(structureError: UiStructureError): UiZoneError {
		return {
			message: structureError.message,
			marked: structureError.marked  || ((marked) => {
				this.marked = marked;
			}),
			focus: (() => {
				this.visible = true;
				if (structureError.focus) {
					structureError.focus();
				}
			})
		};
	}
}
