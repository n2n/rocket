import { EmbeddedEntriesInModel } from '../comp/embedded-entries-in-model';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesSummaryInComponent } from '../comp/embedded-entries-summary-in/embedded-entries-summary-in.component';
import { EmbeddedEntriesInComponent } from '../comp/embedded-entries-in/embedded-entries-in.component';
import { EmbeddedAddPasteObtainer } from './embedded-add-paste-obtainer';
import { AddPasteObtainer } from '../comp/add-paste-obtainer';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SimpleSiControl } from 'src/app/si/model/control/impl/model/simple-si-control';
import { TranslationService } from 'src/app/util/i18n/translation.service';

import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { Observable, Subscription } from 'rxjs';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { EmbeInCollection } from './embe/embe-collection';
import { Embe } from './embe/embe';
import { EmbeddedEntriesInConfig } from './embe/embedded-entries-config';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { EmbeStructureCollection, EmbeStructure } from './embe/embe-structure';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';
import { UiStructureError } from 'src/app/ui/structure/model/ui-structure-error';
import { Message } from 'src/app/util/i18n/message';
import { map } from 'rxjs/operators';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { UiContainer } from 'src/app/ui/structure/model/ui-container';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { ButtonControlUiContent } from 'src/app/si/model/control/impl/comp/button-control-ui-content';

export class EmbeddedEntriesInUiStructureModel extends UiStructureModelAdapter implements EmbeddedEntriesInModel {
	private embeInUiZoneManager: EmbeInUiZoneManager|null = null;
	private embeStructureCollection: EmbeStructureCollection|null = null;
	private subscription: Subscription|null = null;
	private structureErrorCollection = new BehaviorCollection<UiStructureError>();
	private errorState = {
		messages: new Array<Message>(),
		zoneErrors: new Array<UiZoneError>()
	};

	constructor(private obtainer: EmbeddedEntryObtainer, public frame: SiFrame,
			private embeInCol: EmbeInCollection, private config: EmbeddedEntriesInConfig,
			private translationService: TranslationService, disabled$: Observable<boolean>|null = null) {
		super();
		this.disabled$ = disabled$;
	}

	// getValues(): SiEmbeddedEntry[] {
	// 	return this.values;
	// }

	// setValues(values: SiEmbeddedEntry[]) {
	// 	this.values = values;
	// }

	// getEmbeInCollection(): EmbeInCollection {
	// 	return this.embeInCol;
	// }


	getMin(): number {
		return this.config.min;
	}

	getMax(): number|null {
		return this.config.max;
	}

	isSummaryRequired(): boolean {
		return this.config.reduced;
	}

	isNonNewRemovable(): boolean {
		return this.config.nonNewRemovable;
	}

	isSortable(): boolean {
		return this.config.sortable;
	}

	// getFrame(): SiFrame {
	// 	return this.frame;
	// }

	getAllowedSiTypeIds(): string[]|null {
		return this.config.allowedTypeIds;
	}

	getAddPasteObtainer(): AddPasteObtainer {
		return new EmbeddedAddPasteObtainer(this.obtainer);
	}

	private getEmbeInUiStructureManager(): EmbeInUiZoneManager {
		IllegalStateError.assertTrue(!!this.embeInUiZoneManager);
		return this.embeInUiZoneManager;
	}

	private getEmbeStructureCollection(): EmbeStructureCollection {
		IllegalStateError.assertTrue(!!this.embeStructureCollection);
		return this.embeStructureCollection;
	}

	getEmbeStructures(): EmbeStructure[] {
		return this.embeStructureCollection.embeStructures;
	}

	getStructures$(): Observable<UiStructure[]> {
		return this.embeStructureCollection.embeStructures$.pipe(map(es => es.map(e => e.uiStructure)));
	}

	switch(previousIndex: number, currentIndex: number): void {
		this.embeInCol.changeEmbePosition(previousIndex, currentIndex);
		this.embeInCol.writeEmbes();
		this.getEmbeStructureCollection().refresh();
	}

	add(siEmbeddedEntry: SiEmbeddedEntry): void {
		this.embeInCol.createEmbe(siEmbeddedEntry);
		this.embeInCol.writeEmbes();
		this.getEmbeStructureCollection().refresh();
	}

	addBefore(siEmbeddedEntry: SiEmbeddedEntry, embeStructure: EmbeStructure): void {
		this.embeInCol.createEmbe(siEmbeddedEntry);
		this.embeInCol.changeEmbePosition(this.embeInCol.embes.length - 1, this.embeInCol.embes.indexOf(embeStructure.embe));
		this.embeInCol.writeEmbes();
		this.getEmbeStructureCollection().refresh();
	}

	// place(siEmbeddedEntry: SiEmbeddedEntry, embe: Embe) {
	// 	embe.siEmbeddedEntry = siEmbeddedEntry;
	// 	this.embeCol.writeEmbes();
	// }

	remove(embeStructure: EmbeStructure): void {
		if (this.embeInCol.embes.length > this.getMin()) {
			this.embeInCol.removeEmbe(embeStructure.embe);
			this.embeInCol.writeEmbes();
			this.getEmbeStructureCollection().refresh();
			return;
		}

		embeStructure.embe.siEmbeddedEntry = null;
		this.getEmbeStructureCollection().refresh();

		this.obtainer.obtainNew().then(siEmbeddedEntry => {
			embeStructure.embe.siEmbeddedEntry = siEmbeddedEntry;
			this.getEmbeStructureCollection().refresh();
		});
	}

	open(embeStructure: EmbeStructure): void {
		IllegalStateError.assertTrue(this.config.reduced);
		this.getEmbeInUiStructureManager().open(embeStructure.embe).then((changed) => {
			if (!changed) {
				this.embeStructureCollection.refresh();
			}
		});
	}

	openAll() {
		IllegalStateError.assertTrue(this.config.reduced);
		this.getEmbeInUiStructureManager().openAll().then((changed) => {
			console.log(changed);
			if (!changed) {
				this.embeStructureCollection.refresh();
				console.log('refresh');
			}
		});
	}

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.embeStructureCollection = new EmbeStructureCollection(this.config.reduced, this.embeInCol);
		this.embeStructureCollection.refresh();
		this.subscription = new Subscription();
		this.subscription.add(this.embeInCol.source.getMessages$().subscribe((messages) => {
					this.errorState.messages = messages;
					this.updateReducedStructureErrors();
				}));
		this.subscription.add(this.embeStructureCollection.reducedZoneErrors$.subscribe((zoneErrors) => {
					this.errorState.zoneErrors = zoneErrors;
					this.updateReducedStructureErrors();
				}));

		if (!this.config.reduced) {
			this.uiContent = new TypeUiContent(EmbeddedEntriesInComponent, (ref) => {
				ref.instance.model = this;
			});
			return;
		}

		this.embeInUiZoneManager = new EmbeInUiZoneManager(() => uiStructure.getZone().layer.container, this.embeInCol,
				this.frame, this.obtainer, this.config, this.translationService, this.disabled$);
		this.uiContent = new TypeUiContent(EmbeddedEntriesSummaryInComponent, (ref) => {
			ref.instance.model = this;
		});


		const button = new SiButton(this.translationService.translate('common_edit_all_label'), 'btn btn-warning', 'fa fa-pencil-alt');
		button.important = true;
		button.iconImportant = true;

		const openAllUiContent = new ButtonControlUiContent({
			getUiZone: () => uiStructure.getZone(),
			getSiButton: () => button,
			isLoading: () => false,
			isDisabled: () => false,
			exec: () => this.openAll()
		});

		this.toolbarStructureModelsSubject.next([new SimpleUiStructureModel(openAllUiContent)]);
	}

	unbind(): void {
		super.unbind();

		this.embeInUiZoneManager = null;
		this.subscription.unsubscribe();
		this.subscription = null;
		this.embeStructureCollection.clear();
		this.embeStructureCollection = null;
		this.toolbarStructureModelsSubject.next([]);
	}

	getAsideContents(): UiContent[] {
		return [];
	}

	// getZoneErrors(): UiZoneError[] {
	// 	const errors = new Array<UiZoneError>();

	// 	for (const embe of this.embeInCol.embes) {
	// 		if (!embe.uiStructureModel) {
	// 			continue;
	// 		}

	// 		if (!this.config.reduced) {
	// 			errors.push(...embe.uiStructureModel.getZoneErrors());
	// 			continue;
	// 		}

	// 		for (const zoneError of embe.uiStructureModel.getZoneErrors()) {
	// 			errors.push({
	// 				message: zoneError.message,
	// 				marked: (marked) => {
	// 					this.reqBoundUiStructure().marked = marked;
	// 				},
	// 				focus: () => {
	// 					IllegalStateError.assertTrue(!!this.embeInUiStructureManager);

	// 					this.embeInUiStructureManager.open(embe);

	// 					if (zoneError.focus) {
	// 						zoneError.focus();
	// 					}
	// 				}
	// 			});
	// 		}
	// 	}

	// 	return errors;
	// }

	private updateReducedStructureErrors() {
		const structureErrors = new Array<UiStructureError>();

		structureErrors.push(...this.errorState.messages.map(message => ({ message })));

		for (const embeStructure of this.embeStructureCollection.embeStructures) {
			structureErrors.push(...embeStructure.embe.uiStructure.getZoneErrors().map((ze) => ({
				message: ze.message,
				marked: (marked) => { embeStructure.uiStructure.marked = marked; },
				focus: () => {
					this.getEmbeInUiStructureManager().open(embeStructure.embe);
					ze.focus();
				}
			})));
		}

		this.structureErrorCollection.set(structureErrors);
	}

	getMessages(): Message[] {
		return this.embeInCol.source.getMessages();
	}

	getStructureErrors(): UiStructureError[] {
		return this.structureErrorCollection.get();
	}

	getStructureErrors$(): Observable<UiStructureError[]> {
		return this.structureErrorCollection.get$();
	}

}

class EmbeInUiZoneManager {

	private popupUiLayer: PopupUiLayer|null = null;

	constructor(private getUiContainer: () => UiContainer, private embeCol: EmbeInCollection, private siFrame: SiFrame,
			private obtainer: EmbeddedEntryObtainer, private config: EmbeddedEntriesInConfig,
			private translationService: TranslationService, private disabled$: Observable<boolean>|null = null) {

	}

	// private createEmbeUsm(embe: Embe): UiStructureModel {
	// 	return embe.uiStructureModel;

	// 	// const model = new SimpleUiStructureModel();
	// 	// model.initCallback = () => {
	// 	// 	const child = new UiStructure(null);
	// 	// 	child.model = embe.uiStructureModel;

	// 	// 	model.content = new TypeUiContent(EmbeddedEntryComponent, (ref) => {
	// 	// 		ref.instance.embeStructure = new EmbeStructure(embe, child);
	// 	// 	});
	// 	// };
	// 	// return model;
	// }

	open(embe: Embe): Promise<boolean> {
		if (this.popupUiLayer) {
			return;
		}

		let bakEntry = embe.siEmbeddedEntry.entry.createResetPoint();

		this.popupUiLayer = this.getUiContainer().createLayer();
		const zone = this.popupUiLayer.pushRoute(null, null).zone;

		zone.title = 'Some Title';
		zone.breadcrumbs = [];
		zone.structure = embe.uiStructure;
		zone.mainCommandContents = this.createPopupControls(() => { bakEntry = null; })
					.map(siControl => siControl.createUiContent(() => zone));

		const promise = new Promise<boolean>((resolve) => {
			this.popupUiLayer.onDispose(() => {
				this.popupUiLayer = null;

				if (bakEntry) {
					embe.siEmbeddedEntry.entry.resetToPoint(bakEntry);
					resolve(false);
				} else {
					this.obtainer.val([embe.siEmbeddedEntry]);
					resolve(true);
				}
			});
		});

		return promise;
	}

	openAll(): Promise<boolean> {
		if (this.popupUiLayer) {
			return;
		}

		let bakEmbeddedEntries: SiEmbeddedEntry[]|null = [...this.embeCol.embes.map(embe => embe.siEmbeddedEntry)];
		const bakEntries = this.embeCol.createEntriesResetPoints();

		this.popupUiLayer = this.getUiContainer().createLayer();

		const zone = this.popupUiLayer.pushRoute(null, null).zone;

		const popupUiStructureModel = new EmbeddedEntriesInUiStructureModel(this.obtainer, this.siFrame, this.embeCol,
				{
					reduced: false,
					min: this.config.min,
					max: this.config.max,
					nonNewRemovable: this.config.nonNewRemovable,
					sortable: this.config.sortable,
					allowedTypeIds: this.config.allowedTypeIds
				}, this.translationService, this.disabled$);

		const structure = new UiStructure(UiStructureType.SIMPLE_GROUP, null, popupUiStructureModel);

		const promise = new Promise<boolean>((resolve) => {
			this.popupUiLayer.onDispose(() => {
				this.popupUiLayer = null;
				structure.dispose();

				if (bakEmbeddedEntries) {
					this.resetEmbeCol(bakEmbeddedEntries, bakEntries);
					resolve(false);
					return;
				}

				this.obtainer.val(this.embeCol.embes.map(embe => embe.siEmbeddedEntry));
				resolve(true);
			});
		});

		zone.title = 'Some Title';
		zone.breadcrumbs = [];
		zone.structure = structure;
		zone.mainCommandContents = this.createPopupControls(() => { bakEmbeddedEntries = null; })
					.map(siControl => siControl.createUiContent(() => zone));

		return promise;
	}

	private createPopupControls(applyCallback: () => any): SiControl[] {
		return [
			new SimpleSiControl(
					new SiButton(this.translationService.translate('common_apply_label'), 'btn btn-success', 'fas fa-save'),
					() => {
						applyCallback();
						this.popupUiLayer.dispose();
					}),
			new SimpleSiControl(
					new SiButton(this.translationService.translate('common_discard_label'), 'btn btn-secondary', 'fas fa-trash'),
					() => {
						this.popupUiLayer.dispose();
					})
		];
	}

	private resetEmbeCol(bakEmbeddedEntries: SiEmbeddedEntry[], bakEntries: SiGenericEntry[]) {
		this.embeCol.removeEmbes();

		bakEmbeddedEntries.forEach((emeddedEntry, i) => {
			emeddedEntry.entry.resetToPoint(bakEntries[i]);

			this.embeCol.createEmbe(emeddedEntry);
		});

		this.embeCol.writeEmbes();
	}
}
