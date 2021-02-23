import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SimpleSiControl } from 'src/app/si/model/control/impl/model/simple-si-control';
import { TranslationService } from 'src/app/util/i18n/translation.service';

import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { Observable, Subscription, merge } from 'rxjs';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { EmbeddedEntryComponent } from '../comp/embedded-entry/embedded-entry.component';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { EmbeOutCollection, EmbeOutSource } from './embe/embe-collection';
import { EmbeddedEntriesOutModel } from '../comp/embedded-entries-out-model';
import { EmbeddedEntriesOutComponent } from '../comp/embedded-entries-out/embedded-entries-out.component';
import { EmbeddedEntriesSummaryOutComponent } from '../comp/embedded-entries-summary-out/embedded-entries-summary-out.component';
import { Embe } from './embe/embe';
import { EmbeddedEntriesOutConfig } from './embe/embedded-entries-config';
import { Message } from 'src/app/util/i18n/message';
import { UiStructureError } from 'src/app/ui/structure/model/ui-structure-error';
import { map } from 'rxjs/operators';
import { EmbeStructure, EmbeStructureCollection } from './embe/embe-structure';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';

export class EmbeddedEntriesOutUiStructureModel extends UiStructureModelAdapter implements EmbeddedEntriesOutModel {
	private embeOutUiStructureManager: EmbeOutUiStructureManager|null = null;
	private embeStructureCollection: EmbeStructureCollection|null = null;
	private subscription: Subscription|null = null;
	private structureErrorCollection = new BehaviorCollection<UiStructureError>();

	constructor(public frame: SiFrame, private embeOutCol: EmbeOutCollection, private config: EmbeddedEntriesOutConfig,
			private translationService: TranslationService, disabledSubject: Observable<boolean>|null = null) {
		super();
		this.disabled$ = disabledSubject;
	}

	getEmbeOutCollection(): EmbeOutCollection {
		return this.embeOutCol;
	}

	private getEmbeOutUiStructureManager(): EmbeOutUiStructureManager {
		IllegalStateError.assertTrue(!!this.embeOutUiStructureManager);
		return this.embeOutUiStructureManager;
	}

	open(embeStructure: EmbeStructure) {
		IllegalStateError.assertTrue(this.config.reduced);
		this.getEmbeOutUiStructureManager().open(embeStructure.embe);
	}

	openAll() {
		this.getEmbeOutUiStructureManager().openAll();
	}

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.embeStructureCollection = new EmbeStructureCollection(this.config.reduced, uiStructure, this.embeOutCol);
		this.embeStructureCollection.refresh();
		this.subscription = merge(this.embeOutCol.source.getMessages$(), this.embeStructureCollection.reducedErrorsChanged$).subscribe(() => {
			this.updateReducedStructureErrors();
		});

		if (!this.config.reduced) {
			this.uiContent = new TypeUiContent(EmbeddedEntriesOutComponent, (ref) => {
				ref.instance.model = this;
			});
			return;
		}

		this.embeOutUiStructureManager = new EmbeOutUiStructureManager(uiStructure, this, this.translationService);
		this.uiContent = new TypeUiContent(EmbeddedEntriesSummaryOutComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	getAsideContents(): UiContent[] {
		return [];
	}

	getEmbeStructures(): EmbeStructure[] {
		return this.embeStructureCollection.embeStructures;
	}

	private updateReducedStructureErrors() {
		const structureErrors = new Array<UiStructureError>();

		structureErrors.push(...this.embeOutCol.source.getMessages().map(message => ({ message })));

		for (const embeStructure of this.embeStructureCollection.embeStructures) {
			structureErrors.push(...embeStructure.embe.uiStructureModel.getStructureErrors().map((se) => {
				return {
					message: se.message,
					marked: (marked: boolean) => {
						embeStructure.uiStructure.marked = marked;
					},
					focus: () => {
						this.open(embeStructure);
						if (se.focus) {
							se.focus();
						}
					}
				};
			}));
		}

		this.structureErrorCollection.set(structureErrors);
	}

	getMessages(): Message[] {
		return this.embeOutCol.source.getMessages();
	}

	getStructureErrors(): UiStructureError[] {
		return this.structureErrorCollection.get();
	}

	getStructureErrors$(): Observable<UiStructureError[]> {
		return this.structureErrorCollection.get$();
	}

	// getMessages$(): Observable<Message[]> {
	// 	return this.embeOutSource.getMessages$();
	// }

	// getZoneErrors(): UiZoneError[] {
	// 	const errors = new Array<UiZoneError>();

	// 	for (const embe of this.embeOutCol.embes) {
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
	// 					IllegalStateError.assertTrue(!!this.embeOutUiStructureManager);

	// 					this.embeOutUiStructureManager.open(embe);

	// 					if (zoneError.focus) {
	// 						zoneError.focus();
	// 					}
	// 				}
	// 			});
	// 		}
	// 	}

	// 	return errors;
	// }
}

class EmbeOutUiStructureManager {

	private popupUiLayer: PopupUiLayer|null = null;

	constructor(private uiStructure: UiStructure, private model: EmbeddedEntriesOutModel, private translationService: TranslationService) {

	}

	private createEmbeUsm(embe: Embe): UiStructureModel {
		const model = new SimpleUiStructureModel();
		model.initCallback = (uiStructure) => {
			const child = uiStructure.createChild();
			child.model = embe.uiStructureModel;

			model.content = new TypeUiContent(EmbeddedEntryComponent, (ref) => {
				ref.instance.embeStructure = new EmbeStructure(embe, uiStructure);
			});
		};
		return model;
	}

	open(embe: Embe) {
		if (this.popupUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		const zone = this.popupUiLayer.pushRoute(null, null).zone;

		zone.model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: this.createEmbeUsm(embe),
			mainCommandContents: this.createPopupControls()
					.map(siControl => siControl.createUiContent(zone))
		};

		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
		});
	}

	openAll() {
		if (this.popupUiLayer) {
			return;
		}

		const uiZone = this.uiStructure.getZone();

		this.popupUiLayer = uiZone.layer.container.createLayer();
		this.popupUiLayer.onDispose(() => {
			this.popupUiLayer = null;
		});

		const zone = this.popupUiLayer.pushRoute(null, null).zone;

		const popupUiStructureModel = new SimpleUiStructureModel();

		popupUiStructureModel.initCallback = () => {
			popupUiStructureModel.content = new TypeUiContent(EmbeddedEntriesOutComponent, (ref) => {
				ref.instance.model = this.model;
			});
		};

		zone.model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: popupUiStructureModel,
			mainCommandContents: this.createPopupControls()
					.map(siControl => siControl.createUiContent(zone))
		};
	}

	private createPopupControls(): SiControl[] {
		return [
			new SimpleSiControl(
					new SiButton(this.translationService.translate('common_close_label'), 'btn btn-secondary', 'fas fa-trash'),
					() => {
						this.popupUiLayer.dispose();
					})
		];
	}
}
