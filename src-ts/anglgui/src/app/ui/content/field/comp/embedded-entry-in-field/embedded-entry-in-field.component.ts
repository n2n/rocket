import { Component, OnInit } from '@angular/core';
import { EmbeddedEntryInModel } from "src/app/ui/content/field/embedded-entry-in-model";
import { SiEmbeddedEntry } from "src/app/si/model/content/si-embedded-entry";
import { PopupSiLayer } from "src/app/si/model/structure/si-layer";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { CdkDragDrop } from "@angular/cdk/drag-drop";

@Component({
  selector: 'rocket-embedded-entry-in-field',
  templateUrl: './embedded-entry-in-field.component.html',
  styleUrls: ['./embedded-entry-in-field.component.css']
})
export class EmbeddedEntryInFieldComponent implements OnInit {

	model: EmbeddedEntryInModel;

	private popupSiLayer: PopupSiLayer|null = null;

	private embes: Embe[] = []

	constructor() {}

	ngOnInit() {
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
		let embe = this.embes.find(embe => embe.siEmbeddedEntry == siEmbeddedEntry);
		if (embe) {
			embe.siStructure.model = siEmbeddedEntry.content;
			if (this.reduced) {
				embe.summarySiStructure.model = siEmbeddedEntry.summaryContent;
			}
			return embe;
		}
		
		const siStructure = new SiStructure(null, null, siEmbeddedEntry.content);
		const summarySiStructure = (this.reduced ? new SiStructure(null, null, siEmbeddedEntry.summaryContent) : null);
		embe = new Embe(siEmbeddedEntry, siStructure, summarySiStructure)
		this.model.registerSiStructure(siStructure);
		this.model.registerSiStructure(summarySiStructure);
		this.embes.push(embe);
		return embe;
	}
	
	drop(event: CdkDragDrop<string[]>) {
		console.log(event);
//		moveItemInArray(this.movies, event.previousIndex, event.currentIndex);
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
	
}


class Embe {
	constructor (public siEmbeddedEntry: SiEmbeddedEntry,
			public siStructure: SiStructure,
			public summarySiStructure: SiStructure|null) {
	}
	
	get siEntry(): SiEntry {
		return this.siEmbeddedEntry.entry;
	}
}