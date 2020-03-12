import { Component, OnInit, OnDestroy } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesInModel } from '../embedded-entry-in-model';
import { EmbedInCollection } from '../embe-collection';
import { PopupUiLayer } from 'src/app/ui/structure/model/ui-layer';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { Embe } from '../embe';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { EmbeddedEntriesInComponent } from '../embedded-entries-in/embedded-entries-in.component';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SimpleSiControl } from 'src/app/si/model/control/impl/model/simple-si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';


@Component({
	selector: 'rocket-embedded-entries-summary-in',
	templateUrl: './embedded-entries-summary-in.component.html',
	styleUrls: ['./embedded-entries-summary-in.component.css']
})
export class EmbeddedEntriesSummaryInComponent implements OnInit, OnDestroy {
	uiStructure: UiStructure;
	model: EmbeddedEntriesInModel;

	private embeCol: EmbedInCollection;
	private popupUiLayer: PopupUiLayer|null = null;
	obtainer: AddPasteObtainer;

	constructor(private translationService: TranslationService) {
	}

	ngOnInit() {
		this.embeCol = new EmbedInCollection(this.uiStructure, this.model, true);
		this.obtainer = this.model.getObtainer();

		this.embeCol.readEmbes();
	}

	ngOnDestroy() {
		this.embeCol.clearEmbes();
	}

	drop(event: CdkDragDrop<string[]>) {
		this.embeCol.changeEmbePosition(event.previousIndex, event.currentIndex);
		this.embeCol.writeEmbes();
	}

	add(siEmbeddedEntry: SiEmbeddedEntry) {
		this.embeCol.initEmbe(this.embeCol.createEmbe(), siEmbeddedEntry);
		this.embeCol.writeEmbes();
	}

	addBefore(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry) {
		this.embeCol.initEmbe(this.embeCol.createEmbe(), siEmbeddedEntry);
		this.embeCol.changeEmbePosition(this.embeCol.embes.length - 1, this.embeCol.embes.indexOf(embe));
		this.embeCol.writeEmbes();
	}

	place(siEmbeddedEntry: SiEmbeddedEntry, embe: Embe) {
		this.embeCol.initEmbe(embe, siEmbeddedEntry);
		this.embeCol.writeEmbes();
	}

	get embes(): Embe[] {
		return this.embeCol.embes;
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
			structureModel: embe.siEmbeddedEntry.comp.createUiStructureModel(),
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

	copy(embe: Embe) {

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

		const popupUiStructure = new SimpleUiStructureModel();

		popupUiStructure.initCallback = (uiStructure) => {
			popupUiStructure.content = new TypeUiContent(EmbeddedEntriesInComponent, (ref) => {
				ref.instance.model = this.model;
				ref.instance.uiStructure = uiStructure;
			});
		};

		zone.model = {
			title: 'Some Title',
			breadcrumbs: [],
			structureModel: popupUiStructure,
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
		this.embeCol.clearEmbes();

		bakEmbes.forEach((embe, i) => {
			embe.siEmbeddedEntry.entry.resetToPoint(bakEntries[i]);

			this.embeCol.initEmbe(this.embeCol.createEmbe(), embe.siEmbeddedEntry);
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
