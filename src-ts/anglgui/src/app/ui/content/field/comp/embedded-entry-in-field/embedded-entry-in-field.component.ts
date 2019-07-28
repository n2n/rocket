import { Component, OnInit, Injector } from '@angular/core';
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

@Component({
  selector: 'rocket-embedded-entry-in-field',
  templateUrl: './embedded-entry-in-field.component.html',
  styleUrls: ['./embedded-entry-in-field.component.css']
})
export class EmbeddedEntryInFieldComponent implements OnInit {

	model: EmbeddedEntryInModel;

	private popupSiLayer: PopupSiLayer|null = null;

	public embes: Embe[] = [];
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
	
	private unregisterEmbe(embe: Embe) {
		if (embe.siStructure) {
			this.model.unregisterSiStructure(embe.siStructure);
		}
		
		if (embe.summarySiStructure) {
			this.model.unregisterSiStructure(embe.siStructure)
		}
	}
	
	private clearEmbes() {
		let embe: Embe;
		while (embe = this.embes.pop()) {
			this.unregisterEmbe(embe);
		}
	}
	
	private createEmbe(): Embe {
		const embe = new Embe();
		this.embes.push(embe);
		return embe;
	}
	
	private removeEmbe(embe: Embe) {
		const i = this.embes.indexOf(embe);
		if (i < 0) {
			throw new Error('Unknown Embe');
		}
		
		this.embes.splice(i, 1);
		this.unregisterEmbe(embe);
	}
	
	private changeEmbePosition(oldIndex: number, newIndex: number) {
		const moveEmbe = this.embes[oldIndex];
		
		if (oldIndex < newIndex) {
			for (let i = oldIndex; i < newIndex; i++) {
				this.embes[i] = this.embes[i + 1];
			}
		}
		
		if (oldIndex < newIndex) {
			for (let i = oldIndex; i > newIndex; i--) {
				this.embes[i] = this.embes[i - 1];
			}
		}
		
		this.embes[newIndex] = moveEmbe;
	}
	
	private readEmbes() {
		this.clearEmbes();
		
		for (const siEmbeddedEntry of this.model.getValues()) {
			this.initEmbe(this.createEmbe(), siEmbeddedEntry);
		}
	}
	
	private writeEmbes() {
		const values: SiEmbeddedEntry[] = [];
	
		for (const embe of this.embes) {
			if (embe.isPlaceholder()) {
				continue;
			}
			
			values.push(embe.siEmbeddedEntry);
		}
		
		this.model.setValues(values);
	}
	
	private fillWithPlaceholerEmbes() {
		if (!this.model.getAllowedSiTypes()) {
			return;
		}
		
		const min = this.model.getMin();
		while (this.embes.length < min) {
			this.createEmbe();
		}
	}
	
	get reduced(): boolean {
		return this.model.isReduced();
	}
	
	private initEmbe(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry) {
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

		embe.siEmbeddedEntry = siEmbeddedEntry;
		embe.siStructure = siStructure;
		embe.summarySiStructure = summarySiStructure;
		
		this.model.registerSiStructure(siStructure);
		this.model.registerSiStructure(summarySiStructure);
		
		return embe;
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

	apply() {
		if (this.popupSiLayer) {
			this.popupSiLayer.dispose();
		}
	}

	cancel() {

	}
}

class Embe {
	constructor(public siEmbeddedEntry: SiEmbeddedEntry|null = null,
			public siStructure: SiStructure|null = null,
			public summarySiStructure: SiStructure|null = null) {
	}

	isPlaceholder(): boolean {
		return !this.siEmbeddedEntry;
	}
	
	get siEntry(): SiEntry {
		return this.siEmbeddedEntry.entry;
	}
}

