import { Component, OnInit, Injector, OnDestroy } from '@angular/core';
import { EmbeddedEntryInModel } from 'src/app/ui/content/field/embedded-entry-in-model';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { PopupSiLayer } from 'src/app/si/model/structure/si-layer';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { SiButton } from 'src/app/si/model/control/si-button';
import { SimpleSiControl } from 'src/app/si/model/control/impl/simple-si-control';
import { SiService } from 'src/app/si/model/si.service';
import { EmbeddedAddPasteObtainer } from './embedded-add-paste-obtainer';
import { Embe } from './embe';

@Component({
  selector: 'rocket-embedded-entry-in-field',
  templateUrl: './embedded-entry-in-field.component.html',
  styleUrls: ['./embedded-entry-in-field.component.css']
})
export class EmbeddedEntryInFieldComponent implements OnInit, OnDestroy {

	model: EmbeddedEntryInModel;

	private popupSiLayer: PopupSiLayer|null = null;

	
	obtainer: EmbeddedAddPasteObtainer;

	constructor(private translationService: TranslationService, private injector: Injector) {}

	ngOnInit() {
		this.obtainer = new EmbeddedAddPasteObtainer(this.injector.get(SiService), this.model.getApiUrl(),
				this.model.getSiZone(), this.reduced);

		this.readEmbes();
		this.fillWithPlaceholerEmbes();
	}

	ngOnDestroy() {
		this.clearEmbes();
	}

	get reduced(): boolean {
		return this.model.isReduced();
	}


	drop(event: CdkDragDrop<string[]>) {
		this.changeEmbePosition(event.previousIndex, event.currentIndex);
		this.writeEmbes();
	}

	add(siEmbeddedEntry: SiEmbeddedEntry) {
		this.initEmbe(this.createEmbe(), siEmbeddedEntry);
		this.writeEmbes();
	}

	addBefore(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry) {
		this.initEmbe(this.createEmbe(), siEmbeddedEntry);
		this.changeEmbePosition(this.embes.length - 1, this.embes.indexOf(embe));
		this.writeEmbes();
	}

	place(siEmbeddedEntry: SiEmbeddedEntry, embe: Embe) {
		this.initEmbe(embe, siEmbeddedEntry);
		this.writeEmbes();
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