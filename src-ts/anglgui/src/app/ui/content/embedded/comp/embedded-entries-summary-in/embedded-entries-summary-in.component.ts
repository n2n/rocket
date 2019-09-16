import { Component, OnInit, Injector, OnDestroy } from '@angular/core';
import { PopupSiLayer } from 'src/app/si/model/structure/si-layer';
import { EmbeddedAddPasteObtainer } from '../../embedded-add-paste-obtainer';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { EmbedInCollection } from '../../embe-collection';
import { EmbeddedEntryInModel } from '../../embedded-entry-in-model';
import { SiService } from 'src/app/si/model/si.service';
import { SiEmbeddedEntry } from 'src/app/si/model/entity/si-embedded-entry';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { Embe } from '../../embe';
import { SimpleSiControl } from 'src/app/si/model/control/impl/simple-si-control';
import { SiButton } from 'src/app/si/model/control/si-button';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SimpleSiStructureModel } from 'src/app/si/model/structure/impl/simple-si-structure-model';
import { EmbeddedEntriesInComponent } from '../embedded-entries-in/embedded-entries-in.component';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';

@Component({
  selector: 'rocket-embedded-entries-summary-in',
  templateUrl: './embedded-entries-summary-in.component.html',
  styleUrls: ['./embedded-entries-summary-in.component.css']
})
export class EmbeddedEntriesSummaryInComponent implements OnInit, OnDestroy {

	model: EmbeddedEntryInModel;

	private embeCol: EmbedInCollection;
	private popupSiLayer: PopupSiLayer|null = null;
	obtainer: EmbeddedAddPasteObtainer;

	constructor(private translationService: TranslationService, private injector: Injector) {
	}

	ngOnInit() {
		this.embeCol = new EmbedInCollection(this.model);
		this.obtainer = new EmbeddedAddPasteObtainer(this.injector.get(SiService), this.model.getApiUrl(),
				this.model.getSiZone(), this.model.isSummaryRequired());

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
		if (this.popupSiLayer) {
			return;
		}

		const siZone = this.model.getSiZone();

		this.popupSiLayer = siZone.layer.container.createLayer();
		this.popupSiLayer.pushZone(null).structure = embe.siStructure;

		let bakEntry = embe.siEmbeddedEntry.entry.copy();

		this.popupSiLayer.onDispose(() => {
			this.popupSiLayer = null;
			if (bakEntry) {
				embe.siEmbeddedEntry.entry = bakEntry;
			} else {
				this.obtainer.val([embe.siEmbeddedEntry]);
			}
		});

		embe.siStructure.controls = this.createPopupControls(() => { bakEntry = null; });
	}

	openAll() {
		if (this.popupSiLayer) {
			return;
		}

		const siZone = this.model.getSiZone();

		let bakEmbes: Embe[]|null = [...this.embeCol.embes];
		const bakEntries = this.embeCol.copyEntries();

		this.popupSiLayer = siZone.layer.container.createLayer();
		this.popupSiLayer.onDispose(() => {
			this.popupSiLayer = null;

			if (bakEmbes) {
				this.resetEmbeCol(bakEmbes, bakEntries);
				return;
			}

			this.obtainer.val(this.embeCol.embes.map(embe => embe.siEmbeddedEntry));
			this.embeCol.writeEmbes();
		});

		const siStructure = this.popupSiLayer.pushZone(null).structure;
		siStructure.model = new SimpleSiStructureModel(
				new TypeSiContent(EmbeddedEntriesInComponent, (ref) => { ref.instance.model = this.model; }));

		siStructure.controls = this.createPopupControls(() => { bakEmbes = null; });
	}

	private createPopupControls(applyCallback: () => any): SiControl[] {
		return [
			new SimpleSiControl(
					new SiButton(this.translationService.t('common_apply_label'), 'btn btn-success', 'fas fa-save'),
					() => {
						applyCallback();
						this.popupSiLayer.dispose();
					}),
			new SimpleSiControl(
					new SiButton(this.translationService.t('common_discard_label'), 'btn btn-secondary', 'fas fa-trash'),
					() => {
						this.popupSiLayer.dispose();
					})
		];
	}

	private resetEmbeCol(bakEmbes: Embe[], bakEntries: SiEntry[]) {
		this.embeCol.clearEmbes();

		bakEmbes.forEach((embe, i) => {
			embe.siEmbeddedEntry.entry = bakEntries[i];

			this.embeCol.initEmbe(this.embeCol.createEmbe(), embe.siEmbeddedEntry);
		});
	}

	apply() {
		if (this.popupSiLayer) {
			this.popupSiLayer.dispose();
		}
	}

	cancel() {

	}



}