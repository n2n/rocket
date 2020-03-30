import { EmbeddedEntriesInModel } from '../comp/embedded-entry-in-model';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesSummaryInComponent } from '../comp/embedded-entries-summary-in/embedded-entries-summary-in.component';
import { EmbeddedEntriesInComponent } from '../comp/embedded-entries-in/embedded-entries-in.component';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { EmbeddedAddPasteObtainer } from './embedded-add-paste-obtainer';
import { AddPasteObtainer } from '../comp/add-paste-obtainer';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { EmbeInCollection, EmbeInSource } from './embe-collection';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SimpleSiControl } from 'src/app/si/model/control/impl/model/simple-si-control';
import { TranslationService } from 'src/app/util/i18n/translation.service';

import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { Embe } from './embe';
import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { Observable } from 'rxjs';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { EmbeddedEntryComponent } from '../comp/embedded-entry/embedded-entry.component';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';

export class EmbeddedEntriesInUiStructureModel extends UiStructureModelAdapter implements EmbeddedEntriesInModel {
	private embeInCol: EmbeInCollection;
	private embeInUiStructureManager: EmbeInUiStructureManager|null = null;

	constructor(private obtainer: EmbeddedEntryObtainer, public frame: SiFrame,
			embeInSource: EmbeInSource, private config: EmbeddedEntriesConfig,
			private translationService: TranslationService, disabledSubject: Observable<boolean>|null = null) {
		super();
		this.disabled$ = disabledSubject;

		const getUiStucture = () => {
			return this.reqBoundUiStructure();
		};

		this.embeInCol = new EmbeInCollection(embeInSource, getUiStucture, config);
		this.embeInCol.readEmbes();
	}

	// getValues(): SiEmbeddedEntry[] {
	// 	return this.values;
	// }

	// setValues(values: SiEmbeddedEntry[]) {
	// 	this.values = values;
	// }

	getEmbeInCollection(): EmbeInCollection {
		return this.embeInCol;
	}

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

	private getEmbeInUiStructureManager(): EmbeInUiStructureManager {
		IllegalStateError.assertTrue(!!this.embeInUiStructureManager);
		return this.embeInUiStructureManager;
	}

	open(embe: Embe) {
		this.getEmbeInUiStructureManager().open(embe);
	}

	openAll() {
		this.getEmbeInUiStructureManager().openAll();
	}

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		if (this.config.reduced) {
			this.embeInUiStructureManager = new EmbeInUiStructureManager(uiStructure, this.embeInCol, this, this.obtainer,
					this.translationService);
			this.uiContent = new TypeUiContent(EmbeddedEntriesSummaryInComponent, (ref) => {
				ref.instance.model = this;
			});
		} else {
			this.uiContent = new TypeUiContent(EmbeddedEntriesInComponent, (ref) => {
				ref.instance.model = this;
			});
		}
	}

	getAsideContents(): UiContent[] {
		return [];
	}

	getZoneErrors(): UiZoneError[] {
		const errors = new Array<UiZoneError>();

		for (const embe of this.embeInCol.embes) {
			if (!embe.uiStructureModel) {
				continue;
			}

			if (!this.config.reduced) {
				errors.push(...embe.uiStructureModel.getZoneErrors());
				continue;
			}

			for (const zoneError of embe.uiStructureModel.getZoneErrors()) {
				errors.push({
					message: zoneError.message,
					marked: (marked) => {
						this.reqBoundUiStructure().marked = marked;
					},
					focus: () => {
						IllegalStateError.assertTrue(!!this.embeInUiStructureManager);

						this.embeInUiStructureManager.open(embe);

						if (zoneError.focus) {
							zoneError.focus();
						}
					}
				});
			}
		}

		return errors;
	}
}

class EmbeInUiStructureManager {

	private popupUiLayer: PopupUiLayer|null = null;

	constructor(private uiStructure: UiStructure, private embeCol: EmbeInCollection, 
			private model: EmbeddedEntriesInModel, private obtainer: EmbeddedEntryObtainer, 
			private translationService: TranslationService) {

	}

	private createEmbeUsm(embe: Embe): UiStructureModel {
		return new SimpleUiStructureModel(new TypeUiContent(EmbeddedEntryComponent, (ref) => {
			ref.instance.embe = embe;
		}));
	}

	open(embe: Embe) {
		if (this.popupUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone();
		let bakEntry = embe.siEmbeddedEntry.entry.createResetPoint();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		const zone = this.popupUiLayer.pushZone(null);

		zone.model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: this.createEmbeUsm(embe),
			mainCommandContents: this.createPopupControls(() => { bakEntry = null; })
					.map(siControl => siControl.createUiContent(zone))
		};

		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
			if (bakEntry) {
				embe.siEmbeddedEntry.entry.resetToPoint(bakEntry);
			} else {
				this.obtainer.val([embe.siEmbeddedEntry]);
			}
		});
	}

	openAll() {
		if (this.popupUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone();

		let bakEmbes: Embe[]|null = [...this.embeCol.embes];
		const bakEntries = this.embeCol.createEntriesResetPoints();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;

			if (bakEmbes) {
				this.resetEmbeCol(bakEmbes, bakEntries);
				return;
			}

			this.obtainer.val(this.embeCol.embes.map(embe => embe.siEmbeddedEntry));
			this.embeCol.writeEmbes();
		});

		const zone = this.popupUiLayer.pushZone(null);

		const popupUiStructureModel = new SimpleUiStructureModel();

		popupUiStructureModel.initCallback = () => {
			popupUiStructureModel.content = new TypeUiContent(EmbeddedEntriesInComponent, (ref) => {
				ref.instance.model = this.model;
			});
		};

		zone.model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: popupUiStructureModel,
			mainCommandContents: this.createPopupControls(() => { bakEmbes = null; })
					.map(siControl => siControl.createUiContent(zone))
		};
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

	private resetEmbeCol(bakEmbes: Embe[], bakEntries: SiGenericEntry[]) {
		this.embeCol.removeEmbes();

		bakEmbes.forEach((embe, i) => {
			embe.siEmbeddedEntry.entry.resetToPoint(bakEntries[i]);

			this.embeCol.createEmbe(embe.siEmbeddedEntry);
		});
	}

	apply() {
		if (this.popupUiLayer) {
			this.popupUiLayer.dispose();
		}
	}

	cancel() {

	}
}
