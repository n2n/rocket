import { Component, OnInit, Injector, OnDestroy } from '@angular/core';
import { PopupSiLayer } from 'src/app/si/model/structure/si-layer';
import { EmbeddedAddPasteObtainer } from '../../embedded-add-paste-obtainer';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { EmbedInCollection } from '../../embe-collection';
import { EmbeddedEntryInModel } from '../../embedded-entry-in-model';
import { SiService } from 'src/app/si/model/si.service';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { Embe } from '../../embe';

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
		this.popupSiLayer.onDispose(() => {
			this.popupSiLayer = null;
		});

		this.popupSiLayer.pushZone(null).structure = embe.siStructure;
	}

	openAll() {
		if (this.popupSiLayer) {
			return;
		}

		const siZone = this.model.getSiZone();

		this.popupSiLayer = siZone.layer.container.createLayer();
		this.popupSiLayer.onDispose(() => {
			this.popupSiLayer = null;
		});


		// for (this.emb) {
		// 	this.popupSiLayer.pushZone(null).structure = embe.siStructure;
		// }
	}

	apply() {
		if (this.popupSiLayer) {
			this.popupSiLayer.dispose();
		}
	}

	cancel() {

	}

}
