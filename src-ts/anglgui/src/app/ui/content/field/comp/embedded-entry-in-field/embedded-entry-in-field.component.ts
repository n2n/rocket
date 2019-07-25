import { Component, OnInit } from '@angular/core';
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
import { EmbeddedAddPastOptainer } from './embedded-add-paste-optainer';

@Component({
  selector: 'rocket-embedded-entry-in-field',
  templateUrl: './embedded-entry-in-field.component.html',
  styleUrls: ['./embedded-entry-in-field.component.css']
})
export class EmbeddedEntryInFieldComponent implements OnInit {

	model: EmbeddedEntryInModel;
	siService: SiService;

	private popupSiLayer: PopupSiLayer|null = null;

	private embes: Embe[] = [];

	constructor(private translationService: TranslationService) {}

	ngOnInit() {
		new EmbeddedAddPastOptainer(siService, this.model.getApi);
	}

	get visibleEmbes(): Embe[] {
		const embes = [];
		for (const siEmbeddedEntry of this.model.getValues()) {
			embes.push(this.reqEmbe(siEmbeddedEntry));
		}
		return embes;
	}

	get reduced(): boolean {
		return this.model.isReduced();
	}

	private reqEmbe(siEmbeddedEntry: SiEmbeddedEntry): Embe {
		let embe = this.embes.find(embe => embe.siEmbeddedEntry === siEmbeddedEntry);
		if (embe) {
			embe.siStructure.model = siEmbeddedEntry.comp;
			if (this.reduced) {
				embe.summarySiStructure.model = siEmbeddedEntry.summaryComp;
			}
			return embe;
		}

		const siStructure = new SiStructure(null, null, siEmbeddedEntry.comp);
		const summarySiStructure = (this.reduced ? new SiStructure(null, null, siEmbeddedEntry.summaryComp) : null);

		if (this.reduced) {
			siEmbeddedEntry.comp.controls = [
				new SimpleSiControl(
						new SiButton(this.translationService.t('common_save_label'), 'btn btn-success', 'fas fa-save'),
						() => { this.apply(); }),
				new SimpleSiControl(
						new SiButton(this.translationService.t('common_save_label'), 'btn btn-success', 'fas fa-trash-restore-alt'),
						() => { this.cancel(); })
			];
		}

		embe = new Embe(siEmbeddedEntry, siStructure, summarySiStructure);
		this.model.registerSiStructure(siStructure);
		this.model.registerSiStructure(summarySiStructure);
		this.embes.push(embe);
		return embe;
	}

	drop(event: CdkDragDrop<string[]>) {
		console.log(event);
// 		moveItemInArray(this.movies, event.previousIndex, event.currentIndex);
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

	apply() {
		if (this.popupSiLayer) {
			this.popupSiLayer.dispose();
		}
	}

	cancel() {

	}
}


class Embe {
	constructor(public siEmbeddedEntry: SiEmbeddedEntry,
			public siStructure: SiStructure,
			public summarySiStructure: SiStructure|null) {
	}

	get siEntry(): SiEntry {
		return this.siEmbeddedEntry.entry;
	}
}

